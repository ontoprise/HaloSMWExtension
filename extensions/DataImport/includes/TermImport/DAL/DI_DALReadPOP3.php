<?php
/*
 * Copyright (C) Vulcan Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program.If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 * @file
 * @ingroup DITIDataAccessLayer
 * Implementation of the Data Access Layer (DAL) that is part of the term import feature.
 * This implementation reads mails from a POP3 mailbox
 * an returns its content in a form
 * appropriate for the creation of articles.
 *
 * @author Ingo Steinbauer
 */

class DALReadPOP3 implements IDAL {

	private $connection = false;
	private $mailFrom = "";
	private $mailDate = "";
	private $mailId = "";

	private $body;
	private $attachments;
	private $embeddedMails;
	private $embeddedMailIds;
	
	private $requestedProperties;
	
	private $messagesToDelete;
	private $errorMessages = array();
	private $messageContainsErrors = false;
	
	private $noCallPartNr = false; //this flag is used to determine when to skip multipart/related
	
	private $processedICalUIDs;
	
	private $terms;
	private $term;

	function __construct() {
		global $wgNamespaceAliases;
	}

	public function getSourceSpecification() {
		//todo: language file
		return
			'<?xml version="1.0"?>'."\n".
			'<DataSource xmlns="http://www.ontoprise.de/smwplus#">'."\n".
			' 	<ServerAddress display="'."Server:".'" type="text"></ServerAddress>'."\n".
			' 	<UserName display="'."User:".'" type="text"></UserName>'."\n".
			' 	<Password display="'."Password:".'" type="text"></Password>'."\n".
			'	<SSL display="'."Use SSL:".'" type="checkbox"></SSL>'."\n".
			'</DataSource>'."\n";
	}

	public function getImportSets($dataSourceSpec) {
		$connection = $this->getConnection($dataSourceSpec);
		if($connection == false){
			return wfMsg('smw_ti_pop3error');
		}

		$check = imap_check($connection);

		$messages = imap_fetch_overview($connection,"1:{$check->Nmsgs}",0);
		$names = array();
		foreach($messages as $msg){
			if(key_exists("from", $msg)){
				$name = $msg->from;
				$startPos = strpos($name, "<") + 1;
				$endPos = strpos($name, ">") - $startPos;
				$names[substr($name, $startPos, $endPos)] = true;
			}
		}
		
		imap_close($connection);
		$this->connection = false;
		
		return array_keys($names);
	}

	public function getProperties($dataSourceSpec, $importSet) {
			//removed: bc, reply_to, sender, return_path
		$properties = array('articleName', 'from', 'to', 'cc', 'date', 'subject',
			'in_reply_to','followup_to', 'references', 'message_id', 'body', 'attachments');
		
		return $properties;
	}

	public function getTermList($dataSourceSpec, $importSet, $inputPolicy) {
		$connection = $this->getConnection($dataSourceSpec);
		if($connection == false){
			return wfMsg('smw_ti_pop3error');
		}

		$check = imap_check($connection);

		$inputPolicy = DIDALHelper::parseInputPolicy($inputPolicy);
		$this->requestedProperties = array_flip($inputPolicy["properties"]);
		
		//$messages = imap_fetch_overview($connection,"1:{$check->Nmsgs}",0);
		$messages = imap_search($connection, 'ALL');
		
		$terms = new DITermCollection();
		if($messages){
			$msgNumber = 1;
			foreach($messages as $msg){
				$header = imap_header($connection, $msgNumber);
				$msgNumber += 1;
				if(key_exists("message_id", $header)){
					$impSet = $header->fromaddress;
					$startPos = strpos($impSet, "<") + 1;
					$endPos = strpos($impSet, ">") - $startPos;
					$impSet = substr($impSet, $startPos, $endPos);
				
					if(!DIDALHelper::termMatchesRules(
							$impSet, $this->replaceAngledBrackets($header->message_id), $importSet, $inputPolicy)){
						continue;
					}
				} else {
					continue;
				}
			
				$term = new DITerm();
				$term->setArticleName(
					$this->replaceAngledBrackets($header->message_id));
				$terms->addTerm($term);
			}
		}
		imap_close($connection);

		return $terms;
	}

	public function getTerms($dataSourceSpec, $importSet, $inputPolicy, $conflictPolicy) {
		
		$inputPolicy = DIDALHelper::parseInputPolicy($inputPolicy);
		$this->requestedProperties = array_flip($inputPolicy["properties"]);
		$this->messagesToDelete = array();
		
		$connection = $this->getConnection($dataSourceSpec);
		if($connection == false){
			return wfMsg('smw_ti_pop3error');
		}
		
		$messages = imap_search($connection, 'ALL');
		if(is_array($messages)){
			
			$this->terms = new DITermCollection();
			foreach($messages as $msg){
				$this->messageContainsErrors = false;
				$this->term = new DITerm();
				
				if(!$this->processHeaderData($connection, $msg, $importSet, $inputPolicy)){
					continue;
				}
				
				$this->processBody($connection, $msg);
				
				if($this->messageContainsErrors != true){
					$this->messagesToDelete[$this->getMessageId($connection, $msg)][] = true;
					$this->terms->addTerm($this->term);
				} 
			} 
		}
		
		while(count($this->embeddedMails) > 0){
			$embeddedMails = $this->embeddedMails;
			$this->embeddedMails = array();
			foreach($embeddedMails as $mail){
				echo("\next embedded message");
				$this->messageContainsErrors = false;
				$this->noCallPartNr = true;
				$this->term = new DITerm();
				
				$this->doProcessHeaderData($mail["header"]);
				
				$this->handleBodyParts($connection, $mail["message"], 
					$mail["structure"], $mail["partNr"]);
					
				if($this->messageContainsErrors != true){
					$this->messagesToDelete
						[$this->getMessageId($connection, $mail["message"])][] = true;
					$this->terms->addTerm($this->term);
				} else {
					unset($this->messagesToDelete
						[$this->getMessageId($connection, $mail["message"])]);
				}
			}
		}
		
		imap_close($connection);
		
		$this->createDeleteCallback($dataSourceSpec);
		
		foreach($this->errorMessages as $message){
			$this->terms->addErrorMsg($message);
		}
		
		//echo("\r\nCollected terms:".print_r($this->terms, true));
		
		return $this->terms; 
	}

	private function processBody($connection, $msg){
		$this->processedICalUIDs = array();
		$this->noCallPartNr = false;
		$structure = imap_fetchstructure($connection, $msg);
		$this->handleBodyParts($connection, $msg, $structure, "");
	}
	
	private function handleBodyParts($connection, $msg, $structure, $basePartNr){
		echo("\nProcess next message body:");
		global $smwgDIIP;
		
		$this->body = '';
		$this->attachments = array();
		$this->embeddedMailIds = "";

		if($structure->type == 0){ //this is a simple text message
			$encoding = $structure->encoding;
			echo("\nProcess next message part: type: ".$structure->type.
				" subtype: ".$structure->subtype." partNr: 1"." encoding: ".$encoding);
			$this->handleBodyTextPart($connection, $msg, $structure, "", 1, $encoding);
		} else if ($structure->type == 1 || $structure->type == 2){ //this is a multipart message with attachments
			$encoding = $structure->encoding;
			echo("\nProcess next message part: type: ".$structure->type.
				" subtype: ".$structure->subtype." partNr: ".$basePartNr." encoding: ".$encoding);
			$this->handleBodyMultiPart($connection, $msg, $structure, $basePartNr);
		}

		$this->finalizeBodyProcessing();
	}
	
	private function handleBodyMultiPart($connection, $msg, $structure, $basePartNr){
		echo("\nprocess next body multipart");
		$partNr = 1;
		foreach($structure->parts as $part){
			$encoding = $part->encoding;
			echo("\nProcess next message part: type: ".$part->type.
				" subtype: ".$part->subtype." partNr: ".$basePartNr.$partNr." encoding ".$encoding);
			if($part->type == 0){ //text
				$this->handleBodyTextPart($connection, $msg, $part, $basePartNr, $partNr, $encoding);
			} else if($part->type == 1) { //multipart
				//this strange distinction below is necesary
				//due to the strange enumeration of php imap
				if(strtoupper($part->subtype) == "ALTERNATIVE"){
					$callPartNr = $partNr.".";
				} else if (strtoupper($part->subtype) == "RELATED"){
					if($this->noCallPartNr){
						$callPartNr = "";
						$this->noCallPartNr = false;
					} else {
						$callPartNr = $partNr.".";
					}
				} else {
					$callPartNr = "";
				}
				$this->handleBodyMultiPart($connection, $msg, $part, 
					$basePartNr.$callPartNr);
			} else if ($part->type == 2 && array_key_exists("attachments", $this->requestedProperties)){ // an attached email message
				$header = imap_rfc822_parse_headers($this->decodeBodyPart(
						imap_fetchbody($connection, $msg, $basePartNr.$partNr.".0"), 
						$encoding));
				if(trim($header->message_id) == ""){
					continue;
				}
				if($this->embeddedMailIds != ""){
					$this->embeddedMailIds .= ",";
				}
				global $wgExtraNamespaces;
				$ns="";
				if(array_key_exists(NS_TI_EMAIL, $wgExtraNamespaces)){
					$ns = $wgExtraNamespaces[NS_TI_EMAIL].":";
				}
				$this->embeddedMailIds .= $ns.$this->replaceAngledBrackets($header->message_id);
				$this->embeddedMails[] = array("header" => $header, "structure" => $part,
					"message" => $msg, "message_id" => $this->mailId, 
					"partNr" => $basePartNr.$partNr.".");
			} else if(array_key_exists("attachments", $this->requestedProperties)){ //an attachment
				$bodyStruct = imap_bodystruct($connection, $msg, $basePartNr.$partNr);
				$result = $this->handleAttachments($bodyStruct, $connection, $msg, 
					$basePartNr.$partNr, $encoding);
				if($result != "true"){
					$this->messageContainsErrors = true;
					$this->createErrorMessage($connection, $msg, $result);
				}
			}
			$partNr ++;
		}
	}
	
	private function handleBodyTextPart($connection, $msg, $part, $basePartNr, $partNr, $encoding){
		//first check if this message part is not a part of the body
		//but an attachment with the encoding text plain
		$bodyStruct = imap_bodystruct($connection, $msg, $basePartNr.$partNr);
		if ($bodyStruct->ifparameters){
			foreach ($bodyStruct->parameters as $p){
				$params[ strtolower( $p->attribute) ] = mb_decode_mimeheader($p->value);
			}
		}
		if ($bodyStruct->ifdparameters){
			foreach ($bodyStruct->dparameters as $p){
				$params[ strtolower( $p->attribute) ] = mb_decode_mimeheader($p->value);
			}
		}

		if(array_key_exists("filename", $params) && 
				array_key_exists("attachments", $this->requestedProperties)){
			$fileName = $params['filename'];
			if(!mb_check_encoding($fileName, "UTF-8")){
				$fileName = utf8_encode($fileName);
			}
		
			$result = $this->handleAttachments($bodyStruct, $connection, $msg, 
					$basePartNr.$partNr, $encoding);
			if($result != "true"){
				$this->messageContainsErrors = true;
				$this->createErrorMessage($connection, $msg, $result);
			}
		} else if ($part->ifsubtype){
			if(strtoupper($part->subtype) == "X-VCARD"){
				if(!array_key_exists("attachments", $this->requestedProperties)){
					return;
				}
				$result = $this->serialiseVCard($this->decodeBodyPart(
					imap_fetchbody($connection, $msg, $basePartNr.$partNr), 
						$encoding));
				if($result != "true"){
					$this->createErrorMessage($connection, $msg, $result);
					$this->messageContainsErrors = true;
				}
			} else if(strtoupper($part->subtype) == "CALENDAR"){
				if(!array_key_exists("attachments", $this->requestedProperties)){
					return;
				}
				$content = $this->decodeBodyPart(
					imap_fetchbody($connection, $msg, $basePartNr.$partNr), $encoding);
				$result = $this->serializeICal($content);
				if($result != "true"){
					$this->createErrorMessage($connection, $msg, $result);
					$this->messageContainsErrors = true;
				}
			} else if(array_key_exists("body", $this->requestedProperties)
					&& strtoupper($part->subtype) != "HTML"){
				
				$body = $this->decodeBodyPart(
					imap_fetchbody($connection, $msg, $basePartNr.$partNr), 
					$encoding);
				if(!mb_check_encoding($body, "UTF-8")){
					$body = utf8_encode($body);
				}
				$body = nl2br($body);
				$body = $this->replaceSpecialWikiCharacters($body);
				$this->body .= $body; 
			}
		} else if(array_key_exists("body", $this->requestedProperties)){ //text message without subtype
			$body = "<pre>".$this->decodeBodyPart(
				imap_fetchbody($connection, $msg, $basePartNr.$partNr), 
				$encoding)."</pre>";
			if(!mb_check_encoding($body, "UTF-8")){
				$body = utf8_encode($body);
			}
			$body = nl2br($body);
			$body = $this->replaceSpecialWikiCharacters($body);
			$this->body .= $body;
		}
	}

	private function decodeBodyPart($bodyPart, $encoding){
		if ($encoding == 1){
			if(!mb_check_encoding($bodyPart, "UTF-8")){
				$bodyPart = utf8_encode($bodyPart);
			}
		} else if ($encoding == 4){
			$bodyPart = quoted_printable_decode($bodyPart);
			if(!mb_check_encoding($bodyPart, "UTF-8")){
				$bodyPart = utf8_encode($bodyPart);
			}
		}else if ($encoding == 3){
			$bodyPart = base64_decode($bodyPart);
		}
		return $bodyPart;
	}

	private function getConnection($dataSourceSpec){
		if($this->connection == false){
			if(strpos($dataSourceSpec, "DATASOURCE") > 0){
				$dataSourceSpec = str_replace('XMLNS="http://www.ontoprise.de/smwplus#"', "", $dataSourceSpec);
				$dataSourceSpec = new SimpleXMLElement(trim($dataSourceSpec));
				$serverAddress = $dataSourceSpec->xpath("//SERVERADDRESS/text()");
				$userName = $dataSourceSpec->xpath("//USERNAME/text()");
				$password = $dataSourceSpec->xpath("//PASSWORD/text()");
				$ssl = $dataSourceSpec->xpath("//SSL/text()");
			} else {
				$dataSourceSpec = str_replace('xmlns="http://www.ontoprise.de/smwplus#"', "", $dataSourceSpec);
				$dataSourceSpec = new SimpleXMLElement(trim($dataSourceSpec));
				$serverAddress = $dataSourceSpec->xpath("//ServerAddress/text()");
				$userName = $dataSourceSpec->xpath("//UserName/text()");
				$password = $dataSourceSpec->xpath("//Password/text()");
				$ssl = $dataSourceSpec->xpath("//SSL/text()");
			}
			if($serverAddress){
				$serverAddress = $serverAddress[0];
			} else {
				$serverAddress = "";
			}

			if($ssl){
				if($ssl[0] == "true" || $ssl[0] == "on"){
					$serverAddress .= ":995/pop3/ssl}INBOX";
				} else {
					$serverAddress .= ":110/pop3}INBOX";
				}
			} else {
				$serverAddress .= ":110/pop3}INBOX";
			}
			if($userName){
				$userName = $userName[0];
			} else {
				$userName = "";
			}
			if($password){
				$password = $password[0];
			} else {
				$password = "";
			}

			$this->connection = @ imap_open ("{".$serverAddress,
			$userName, $password);

			$check = @imap_check($this->connection);
			if(!$check){
				return false;
			}
		}
		return $this->connection;
	}

	private function processHeaderData($mbox, $msgNumber, $importSet, $inputPolicy){
		$header = imap_header($mbox, $msgNumber);
		return $this->doProcessHeaderData($header, true, $importSet, $inputPolicy);
	}
	
	private function doProcessHeaderData($header, $matchRules=false, $importSet = null, $inputPolicy = null){
		global $wgExtraNamespaces;
		$ns="";
		if(array_key_exists(NS_TI_EMAIL, $wgExtraNamespaces)){
			$ns = $wgExtraNamespaces[NS_TI_EMAIL].":";
		}
		
		if($matchRules){
			if(key_exists("message_id", $header)){
				$impSet = $header->fromaddress;
				$startPos = strpos($impSet, "<") + 1;
				$endPos = strpos($impSet, ">") - $startPos;
				$impSet = substr($impSet, $startPos, $endPos);
				
				if(!DIDALHelper::termMatchesRules(
						$impSet, $this->replaceAngledBrackets($header->message_id), $importSet, $inputPolicy)){
					return false;
				}
			} else {
				return false;
			}
		}
		
		$this->term->setArticleName(
			$ns.$this->replaceAngledBrackets($header->message_id));

		//removed: bc, sender, return_path, reply_to
		$addressTypes = array('from', 'to', 'cc');
		
		foreach($addressTypes as $type){
			if(!array_key_exists($type, $this->requestedProperties)){
				continue;
			}
			
			if(key_exists($type, $header)){
				
				foreach($header->$type as $obj){
					$from = '';
					
					if(key_exists('personal', $obj)){
						$from .= mb_decode_mimeheader($obj->personal);
						
						//this is necessary for being able to later pass
						// the from attribute to the createAttachments callback
						if($type == "from"){
							$this->mailFrom = mb_decode_mimeheader($obj->personal);
						}
					}
					$from.= ",";
					
					if($type == "from"){
						$this->mailFrom .= ",";
					}
					
					if(key_exists('mailbox', $obj)){
						//this is necessary for being able to later pass
						// the from attribute to the createAttachments callback
						if($type == "from"){
							$this->mailFrom .= $obj->mailbox;
						}
						$from .= $obj->mailbox;
					}
					if(key_exists('host', $obj)){
						if($type == "from"){
							$this->mailFrom .= "@".$obj->host;
						}
						$from .= "@".$obj->host;
					}
					$this->term->addProperty($type, $from);
				}
			}
		}

		if(array_key_exists("date", $this->requestedProperties)){
			if(key_exists('date', $header)){
				$this->mailDate = $this->formatDate($header->date);
				$this->term->addProperty('date', $this->formatDate($header->date));	
			} else if(key_exists('Date', $header)){
				$this->term->addProperty('date', $this->formatDate($header->Date));
			} else if(key_exists('MailDate', $header)){
				$this->term->addProperty('date', $this->formatDate($header->MailDate));
			}
		}

		if(array_key_exists("subject", $this->requestedProperties)){
			if(key_exists('subject', $header)){
				$this->term->addProperty('subject', mb_decode_mimeheader($header->subject));
			} else if(key_exists('Subject', $header)){
				$this->term->addProperty('subject', mb_decode_mimeheader($header->Subject));
			}
		}
		
		global $wgExtraNamespaces;
		$ns="";
		if(array_key_exists(NS_TI_EMAIL, $wgExtraNamespaces)){
			$ns = $wgExtraNamespaces[NS_TI_EMAIL].":";
		}

		if(array_key_exists("in_reply_to", $this->requestedProperties)){
			if(key_exists('in_reply_to', $header)){
				$this->term->addProperty('in_reply_to', $ns.$this->replaceAngledBrackets($header->in_reply_to));
			}
		}

		if(array_key_exists("followup_to", $this->requestedProperties)){
			if(key_exists('followup_to', $header)){
				$this->term->addProperty('followup_to', $this->replaceAngledBrackets($header->followup_to));
			}
		}
		
		if(array_key_exists("references", $this->requestedProperties)){
			if(key_exists('references', $header)){
				$this->term->addProperty('references', $ns.$this->replaceAngledBrackets($header->references));
			}
		}
		
		if(array_key_exists("message_id", $this->requestedProperties)){
			if(key_exists('message_id', $header)){
				$this->mailId = $ns.$this->replaceAngledBrackets($header->message_id);
				$this->term->addProperty('message_id', $ns.$this->replaceAngledBrackets($header->message_id));
			}
		}
		
		return true;
	}

	private function replaceAngledBrackets($inputString){
		$inputString = str_replace("<", "", $inputString);
		$inputString = str_replace(">", "", $inputString);
		return $inputString;
	}

	private function formatDate($dateString){
		$date = strtotime($dateString);
		$date = getdate($date);
		$mon = $date["mon"]<10 ? "0".$date["mon"] : $date["mon"];
		$mday = $date["mday"]<10 ? "0".$date["mday"] : $date["mday"];
		$hours = $date["hours"]<10 ? "0".$date["hours"] : $date["hours"];
		$minutes = $date["minutes"]<10 ? "0".$date["minutes"] : $date["minutes"];
		$seconds = $date["seconds"]<10 ? "0".$date["seconds"] : $date["seconds"];

		$dateString = $date["year"]."/".$mon."/".$mday." ".$hours.":".$minutes.":".$seconds;
		return $dateString;
	}

	private function serialiseVCard($vCardString){
		$vCardParser = new DIVCardForPOP3();
		$vCardParser->parse(explode("\n", $vCardString));

		$values = $vCardParser->getProperties("N");
		if($values){
			return $this->createAttachmentTerm($vCardString, $values[0].".vcf");
		}
		return false;
	}

	private function serializeICal($iCalString){
		$iCalParser = new DIICalParserForPOP3();
		$uid = $iCalParser->getUID($iCalString);
		$this->processedICalUIDs[$uid] = true;
		if(!is_null($uid)){
			return $this->createAttachmentTerm($iCalString, $uid.".ics");
		}
		return false;
	}

	public function executeCallBack($callback, $templateName, $extraCategories, $delimiter, $conflictPolicy, $termImportName){
		$method = $callback->getMethodName();
		$params = $callback->getParams();
		return $this->$method($params, $conflictPolicy, $termImportName);
	}

	private function handleDeleteCallBack($params, $conflictPolicy, $termImportName){
		$deletes = $params['deletes'];
		$dataSourceSpec = $params['dataSourceSpec'];
		
		$this->connection = null;
		$connection = $this->getConnection($dataSourceSpec);
		
		$messages = imap_search($connection, 'ALL');
		if(is_array($messages)){
			foreach($messages as $msg){
				$messageId = $this->getMessageId($connection, $msg);
				if(array_key_exists($this->replaceAngledBrackets($messageId), $deletes)){
					imap_delete($connection, $msg);
				}
			}
		}
		
		imap_expunge($connection);
		imap_close($connection);
		
		return array(true, array());
	}
	
	private function getMessageId($mbox, $msgNumber){
		$header = imap_header($mbox, $msgNumber);
		if(key_exists("message_id", $header)){
			return $header->message_id;
		} else { 
			return false;
		}
	}
	
	private function getMessageSubject($mbox, $msgNumber){
		$header = imap_header($mbox, $msgNumber);
		if(key_exists('subject', $header)){
			return mb_decode_mimeheader($header->subject);
		} else if(key_exists('Subject', $header)){
			return mb_decode_mimeheader($header->Subject);
		} else { 
			return false;
		}
	}

	private function handleAttachmentCallBack($params, $conflictPolicy, $termImportName){
		$fileName = $params['fileName'];
		$mailFrom = $params['mailFrom'];
		$mailId = $params['mailId'];
		$mailDate = $params['mailDate'];
		$extraContent = $params['extraContent'];
		
		global $smwgDIIP;
		$success = true;
		$logMsgs = array();
		$tiBot = new TermImportBot();

		global $smwgEnableRichMedia; 
		if($smwgEnableRichMedia){
			global $wgNamespaceByExtension;
			$fileNameArray = explode(".", $fileName);
			$ext = $fileNameArray[count($fileNameArray)-1];
			if(array_key_exists($ext, $wgNamespaceByExtension)){
				$ns = $wgNamespaceByExtension[$ext];
			} else {
				$ns = NS_IMAGE;
			}
		} else {
			$ns = NS_IMAGE;
		}
		
		$fileArticleTitle = Title::makeTitleSafe($ns, $fileName );
		if($fileArticleTitle == null){
			return $this->createCallBackResult(false,
				array(array('id' => SMW_GARDISSUE_CREATION_FAILED,
				'title' => wfMsg('smw_ti_import_error'))));
		}

		$termAnnotations = $tiBot->getExistingTermAnnotations($fileArticleTitle);
		if($fileArticleTitle->exists() && !$conflictPolicy){
			echo wfMsg('smw_ti_articleNotUpdated', $fileArticleTitle->getFullText())."\n";
			$article = new Article($fileArticleTitle);
			$article->doEdit(
				$article->getContent()
				."\n[[WasIgnoredDuringTermImport::".$termImportName."| ]]",
				wfMsg('smw_ti_creationComment'));
			return $this->createCallBackResult(true,
				array(array('id' => SMW_GARDISSUE_UPDATE_SKIPPED,
				'title' => $fileArticleTitle->getFullText())));
		} else if($fileArticleTitle->exists()) {
			$termAnnotations['updated'][] = $termImportName;
			$updated = true;
		} else {
			$termAnnotations['added'][] = $termImportName;
			$updated = false;
		}

		$fileNameArray = explode(".", $fileName);
		$ext = $fileNameArray[count($fileNameArray)-1];
		
		$fileFullPath = RepoGroup::singleton()->getLocalRepo()->getRootDirectory().'/'.$fileName;
		$mFileProps = File::getPropsFromPath($fileFullPath, $ext );
		$local = wfLocalFile($fileName);
		if($local == null){
			return $this->createCallBackResult(false,
			array(array('id' => SMW_GARDISSUE_CREATION_FAILED,
				'title' => $fileArticleTitle->getFullText())));
		}
		$termAnnotations = "\n\n\n"
			.$tiBot->createTermAnnotations($termAnnotations);
			
		$status = $local->upload(
			$fileFullPath, wfMsg('smw_ti_creationComment'), "",
			File::DELETE_SOURCE, $mFileProps );
		
		if(isset($status->failureCount)  && $status->failureCount > 0){
			throw new Exception("The file \"".$fileFullPath."\" could not be uploaded.");
			
			return $this->createCallBackResult(false,
			array(array('id' => SMW_GARDISSUE_CREATION_FAILED,
				'title' => $fileArticleTitle->getFullText())));
		}
		
		$content = "";
		$local->load();
		global $smwgEnableUploadConverter;
		
		if($smwgEnableUploadConverter){
			$fileContent = UploadConverter::getFileContent($local);
			$content = $fileContent;
		}

		$fileArticleTitle = Title::newFromText($fileArticleTitle->getText(), $fileArticleTitle->getNamespace());
		$article = new Article($fileArticleTitle);
		$result = $article->doEdit(
			ltrim($content.$termAnnotations), wfMsg('smw_ti_creationComment'));

		if($updated){
			return $this->createCallBackResult(true,
			array(array('id' => SMW_GARDISSUE_UPDATED_ARTICLE,
				'title' => $fileArticleTitle->getFullText())));
		} else {
			return $this->createCallBackResult(true,
			array(array('id' => SMW_GARDISSUE_ADDED_ARTICLE,
				'title' => $fileArticleTitle->getFullText())));
		}
	}

	private function createCallBackResult($success, $logMsgs){
		return array($success, $logMsgs);
	}
	
	private function createAttachmentTerm($fileContent, $fileName, $extraContent=""){
		global $smwgDIIP;
		
		//special handling for thunderbird invite.ics
		//since thunderbird sents invitation twice
		if(strtolower($fileName == 'invite.ics')){
			$iCalParser = new DIICalParserForPOP3();
			$uid = $iCalParser->getUID($fileContent);
			if(array_key_exists($uid, $this->processedICalUIDs)){
				return 'true';
			}
		}
		
		$fileFullPath =
			$smwgDIIP.'/specials/TermImport/DAL/attachments/'.$fileName;
		$fileFullPath = RepoGroup::singleton()->getLocalRepo()->getRootDirectory().'/'.$fileName;
		try {
			$file = @ fopen($fileFullPath, 'w');
			if($file){
				fwrite($file, $fileContent);
				fclose($file);
				$this->attachments[$fileName] = $extraContent;
				return "true";
			} else {
				return " creating the attachment ".$fileFullPath." failed.";
			}
		} catch (Exception $e){
			return " creating the attachment ".$fileFullPath." failed because ".$e;
		}
	}

	private function handleAttachments($bodyStruct, $connection, $msg, $partNr, $encoding){
		$params = array();
		if ($bodyStruct->ifparameters){
			foreach ($bodyStruct->parameters as $p){
				$params[ strtolower( $p->attribute) ] = mb_decode_mimeheader($p->value);
			}
		}
		if ($bodyStruct->ifdparameters){
			foreach ($bodyStruct->dparameters as $p){
				$params[ strtolower( $p->attribute) ] = mb_decode_mimeheader($p->value);
			}
		}

		$fileName = ($params['filename'])? $params['filename'] : $params['name'];
		if(!mb_check_encoding($fileName, "UTF-8")){
			$fileName = utf8_encode($fileName);
		}
		
		$fileContent = $this->decodeBodyPart(
			imap_fetchbody($connection, $msg, $partNr), $encoding);
		
		return $this->createAttachmentTerm($fileContent, $fileName);
	}
	
	private function createDeleteCallback($dataSourceSpec){
		if(count($this->messagesToDelete) == 0){
			return;
		}
		
		$deletes = array_keys($this->messagesToDelete);
		
		$term = new DITerm();
		$term->addCallback( new DITermImportCallback(
			"handleDeleteCallBack",
			array('deletes' => $deletes, 'dataSourceSpec' => $dataSourceSpec)));
		$term->setAnnonymousCallbackTerm(true);
		
		$this->terms->addTerm($term);
	}
	
	private function finalizeBodyProcessing(){
		global $smwgEnableRichMedia, $wgExtraNamespaces, $wgNamespaceByExtension, $wgCanonicalNamespaceNames;
		$attachmentTerms = "";
		$firstOne = true;
		$attachmentFNs = "";
		foreach($this->attachments as $fn => $extraContent){
			if($smwgEnableRichMedia){
				$fileNameArray = explode(".", $fn);
				$ext = $fileNameArray[count($fileNameArray)-1];
				if(array_key_exists($ext, $wgNamespaceByExtension)){
					$ns = $wgNamespaceByExtension[$ext];
					@$ns = $wgExtraNamespaces[$ns].":";
				} 
				if($ns == ":" || $ns == "") {
					$ns = $wgCanonicalNamespaceNames[NS_IMAGE].":";
				}
			} else {
				$ns = $wgCanonicalNamespaceNames[NS_IMAGE].":";
			}
			
			$this->term->addProperty(
				'attachments', $ns.$fn);
			
			$this->term->addCallback(new DITermImportCallback(
				'handleAttachmentCallBack',
				array(
				'fileName' => $fn, 
				'mailFrom' => $this->mailFrom, 
				'mailId' => $this->mailId,
				'mailDate' => $this->mailDate, 
				'extraContent' => $extraContent)));
		}
		
		foreach(explode(',', $this->embeddedMailIds) as $embeddedMail){
			$this->term->addProperty(
				'attachments', trim($embeddedMail));
		}
		
		if(trim($this->body) != ""){
			$this->term->addProperty('body', $this->body);
		}
	}
	
	private function createErrorMessage($connection, $msgNr, $message = ""){
		$startOfMessage = "The E-mail with the id ";
		if(strlen($message) > 0){
			$endOfMessage = " could not be imported because ".$message."."; 
		} else {
			$endOfMessage = " could not be imported.";
		}
		$messageId = $this->replaceAngledBrackets($this->getMessageId($connection, $msgNr));
		$subject = $this->getMessageSubject($connection, $msgNr);
		
		$result = $startOfMessage.$messageId." and with the subject \"".$subject."\" ".$endOfMessage;
		
		$this->errorMessages[] = $result;
	}
	
	private function replaceSpecialWikiCharacters($body){
		$body = str_replace("{","&#123;",$body);
		$body = str_replace("[","&#91;",$body);
		$body = str_replace("]","&#93;",$body);
		$body = str_replace("}","&#125;",$body);
		$body = str_replace("|","{{!}}",$body);
		return $body;
	}
}