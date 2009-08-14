<?php
/*  Copyright 2009, ontoprise GmbH
 *  This file is part of the Data Import-Extension.
 *
 *   The Data Import-Extension is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The Data Import-Extension is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Implementation of the Data Access Layer (DAL) that is part of the term import feature.
 * This implementation reads mails from a POP3 mailbox
 * an returns its content in a form
 * appropriate for the creation of articles.
 *
 * @author Ingo Steinbauer
 */

global $smwgDIIP;
require_once($smwgDIIP . '/specials/TermImport/SMW_IDAL.php');

define('DAL_POP3_RET_ERR_START',
			'<?xml version="1.0"?>'."\n".
			'<ReturnValue xmlns="http://www.ontoprise.de/smwplus#">'."\n".
    		'<value>false</value>'."\n".
    		'<message>');

define('DAL_POP3_RET_ERR_END',
			'</message>'."\n".
    		'</ReturnValue>'."\n");


class DALReadPOP3 implements IDAL {

	private $connection = false;

	function __construct() {
	}

	public function getSourceSpecification() {
		//todo: language file
		return
			'<?xml version="1.0"?>'."\n".
			'<DataSource xmlns="http://www.ontoprise.de/smwplus#">'."\n".
			' 	<ServerAddress display="'."Server address:".'" type="text"></ServerAddress>'."\n".
		' 	<UserName display="'."User name:".'" type="text"></UserName>'."\n".
		' 	<Password display="'."Password:".'" type="text"></Password>'."\n".
		'	<SSL display="'."Use SSL encryption:".'" type="checkbox"></SSL>'."\n".
		'	<vCardMP display="'."vCard Mapping Policy:".'" type="text"></vCardMP>'."\n".
			'</DataSource>'."\n";
	}

	public function getImportSets($dataSourceSpec) {
		return
			'<?xml version="1.0"?>'."\n".
			'<ImportSets xmlns="http://www.ontoprise.de/smwplus#">'."\n".
			'</ImportSets>'."\n";
	}

	public function getProperties($dataSourceSpec, $importSet) {
		$result = "";

		//removed: bc, reply_to, sender, return_path
		$properties = array('articleName', 'from', 'to', 'cc', 'date', 'subject',
			'followup_to', 'references', 'message_id', 'body', 'attachments');
		foreach ($properties as $prop) {
			$properties .=
				'<property>'."\n".
				'	<name>'.$prop.'</name>'."\n".
				'</property>'."\n";
		}

		return
			'<?xml version="1.0"?>'."\n".
			'<Properties xmlns="http://www.ontoprise.de/smwplus#">'."\n".
		$properties.
			'</Properties>'."\n";
	}

	public function getTermList($dataSourceSpec, $importSet, $inputPolicy) {
		//todo: imput policy
		$connection = $this->getConnection($dataSourceSpec);
		if($connection == false){
			return DAL_POP3_RET_ERR_START.
			wfMsg('smw_ti_pop3error').
			DAL_POP3_RET_ERR_END;
		}

		$check = imap_check($connection);

		$messages = imap_fetch_overview($connection,"1:{$check->Nmsgs}",0);
		$result = "";
		foreach($messages as $msg){
			$result .= "<articleName>".$this->replaceAngledBrackets($msg->message_id)."</articleName>\n";
		}
		imap_close($connection);

		return
			'<?xml version="1.0"?>'."\n".
			'<terms xmlns="http://www.ontoprise.de/smwplus#">'."\n".
		$result.
			'</terms>'."\n";
	}

	public function getTerms($dataSourceSpec, $importSet, $inputPolicy) {
		//todo: consider inputpolicy
		$connection = $this->getConnection($dataSourceSpec);
		if($connection == false){
			return DAL_POP3_RET_ERR_START.
			wfMsg('smw_ti_pop3error').
			DAL_POP3_RET_ERR_END;
		}
		$messages = imap_search($connection, 'ALL');

		$vCardMP = $this->getVCardMPFromDataSource($dataSourceSpec);

		$result = "";
		$properties = "";
		if(is_array($messages)){
			foreach($messages as $msg){
				//$result .= "<term>\n";
				$result = $this->getBody($connection, $msg, $vCardMP);
				$result .= "\n".$this->getHeaderData($connection, $msg);
				$result .= "</term>\n";
			}
		}

		imap_close($connection);

		return
			'<?xml version="1.0"?>'."\n".
			'<terms xmlns="http://www.ontoprise.de/smwplus#">'."\n".
		$result.
			'</terms>'."\n";
	}

	private function getBody($connection, $msg, $vCardMP){
		global $smwgDIIP;

		$structure = imap_fetchstructure($connection, $msg);
		$body = '';
		$vCards = array();
		$attachments = array();
		if($structure->type == 0){ //this is a simple text message
			$body = $this->decodeBodyPart(imap_body($connection, $msg), $structure->encoding);
		} else if ($structure->type == 1){ //this is a multipart message with attachments
			$partNr = 1;
			foreach($structure->parts as $part){
				$encoding = $part->encoding;
				if($part->type == 0){ //text
					if($part->ifsubtype){
						if(strtoupper($part->subtype) == "X-VCARD"){
							$vCardString = $this->serialiseVCard($this->decodeBodyPart(
							imap_fetchbody($connection, $msg, $partNr), $encoding));
							$vCardString = "<vcard>".$vCardString."</vcard>";
							$vCardXML = new SimpleXMLElement(trim($vCardString));
							$fn = $vCardXML->xpath("//FN/text()");
							$fn = "".$fn[0];
							if($fn != ""){
								$vCards[$fn] = $vCardString;
							}
						} else {
							$body .= "<pre>".$this->decodeBodyPart(
							imap_fetchbody($connection, $msg, $partNr), $encoding)."</pre>";
						}
					} else { //text message without subtype
						$body .= "<pre>".$this->decodeBodyPart(
						imap_fetchbody($connection, $msg, $partNr), $encoding)."</pre>";
					}
				} else { // not a text
					$bodyStruct = imap_bodystruct($connection, $msg, $partNr);
					$params = array();
					if ($bodyStruct->ifparameters){
						foreach ($bodyStruct->parameters as $p){
							$params[ strtolower( $p->attribute ) ] = $p->value;
						}
					}
					if ($bodyStruct->ifdparameters){
						foreach ($bodyStruct->dparameters as $p){
							$params[ strtolower( $p->attribute ) ] = $p->value;
						}
					}

					//todo:deal with invalid filenames
					$fileName = ($params['filename'])? $params['filename'] : $params['name'];
					$fileFullPath = 
						$smwgDIIP.'/specials/TermImport/DAL/attachments/'.$fileName;
					$file = fopen($fileFullPath, 'a');
					fwrite($file, $this->decodeBodyPart(
						imap_fetchbody($connection, $msg, $partNr), $encoding));
					fclose($file);
					$attachments[] = $fileName;
				}
				$partNr ++;
			}
		}
		
		$vCardTerms = "";
		$firstOne = true;
		$vCardFNs = "";
		foreach($vCards as $fn => $vCard){
			if($firstOne){
				$vCardFNs = "<vcards>".$fn;
			} else {
				$vCardFNs = ",".$fn;
				$firstOne = false;
			}
			$vCardTerms .= "<term callback='handleVCardCallBack(\"".
			htmlspecialchars($vCard)."\",\"".$vCardMP."\"'></term>";
		}
		if($vCardFNs != ""){
			$vCardFNs .= "</vcards>";
		}
		
		$attachmentTerms = "";
		$firstOne = true;
		$attachmentFNs = "";
		foreach($attachments as $fn){
			if($firstOne){
				$attachmentFNs = "<attachments>".$fn;
			} else {
				$attachmentFNs = ",".$fn;
				$firstOne = false;
			}
			$attachmentTerms .= "<term callback='handleAttachmentCallBack(\"".
				htmlspecialchars($fn)."\"'></term>";
		}
		if($attachmentFNs != ""){
			$attachmentFNs .= "</attachments>";
		}
		
		return $vCardTerms.$attachmentTerms."<term>".$attachmentFNs.$vCardFNs.
			"\n<body>".htmlspecialchars($body)."</body>";
	}

	//todo: add method documentation
	private function decodeBodyPart($bodyPart, $encoding){
		if ($encoding == 4){
			$bodyPart = quoted_printable_decode($bodyPart);
		}else if ($encoding == 3){
			$bodyPart = base64_decode($bodyPart);
		}
		return $bodyPart;
	}

	private function getVCardMPFromDataSource($dataSourceSpec){
		if(strpos($dataSourceSpec, "XMLNS") > 0){
			$dataSourceSpec = str_replace('XMLNS="http://www.ontoprise.de/smwplus#"', "", $dataSourceSpec);
			$dataSourceSpec = new SimpleXMLElement(trim($dataSourceSpec));
			$vCardMP = $dataSourceSpec->xpath("//VCARDMP/text()");
		} else {
			$dataSourceSpec = str_replace('xmlns="http://www.ontoprise.de/smwplus#"', "", $dataSourceSpec);
			$dataSourceSpec = new SimpleXMLElement(trim($dataSourceSpec));
			$vCardMP = $dataSourceSpec->xpath("//vCardMP/text()");
		}
		if($vCardMP){
			return $vCardMP[0];
		} else {
			return "";
		}
	}

	private function getConnection($dataSourceSpec){
		if($this->connection == false){
			if(strpos($dataSourceSpec, "XMLNS") > 0){
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

	private function getHeaderData($mbox, $msgNumber){
		$result = "";

		$header = imap_header($mbox, $msgNumber);

		//todo: choose better article name
		$result .= "<articleName>".
		$this->replaceAngledBrackets($header->message_id)."</articleName>\n";

		//removed: bc, reply_to, sender, return_path
		$addressTypes = array('from', 'to', 'cc');
		foreach($addressTypes as $type){
			//todo: replace , and ; by something else
			if(key_exists($type, $header)){
				$result .= "\n<".$type.">";
				$first = true;
				foreach($header->$type as $obj){
					if(!$first){
						$result.= ";";
					}
					$first = false;
					if(key_exists('personal', $obj)){
						$result .= htmlspecialchars($obj->personal);
					}
					$result.= ",";
					if(key_exists('mailbox', $obj)){
						$result .= htmlspecialchars($obj->mailbox);
					}
					if(key_exists('host', $obj)){
						$result .= "@".htmlspecialchars($obj->host);
					}
				}
				$result .= "\n</".$type.">";
			}
		}

		//todo: is a better date format possible?
		if(key_exists('date', $header)){
			$result .= "\n<date>".htmlspecialchars($this->formatDate($header->date))."</date>";
		} else if(key_exists('Date', $header)){
			$result .= "\n<date>".htmlspecialchars($this->formatDate($header->Date))."</date>";
		} else if(key_exists('MailDate', $header)){
			$result .= "\n<date>".htmlspecialchars($this->formatDate($header->MailDate))."</date>";
		}

		if(key_exists('subject', $header)){
			$result .= "\n<subject>".htmlspecialchars($header->subject)."</subject>";
		} else if(key_exists('Subject', $header)){
			$result .= "\n<subject>".htmlspecialchars($header->Subject)."</subject>";
		}

		if(key_exists('followup_to', $header)){
			$result .= "\n<followup_to>".htmlspecialchars($header->followup_to)."</followup_to>";
		}

		if(key_exists('references', $header)){
			$result .= "\n<references>".htmlspecialchars($this->replaceAngledBrackets($header->references))."</references>";
		}

		if(key_exists('message_id', $header)){
			$result .= "\n<message_id>".htmlspecialchars($this->replaceAngledBrackets($header->message_id))."</message_id>";
		}
		return $result;
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
		require_once('SMW_VCardParser.php');
		$result = "";
		$vCardString = explode("\n", $vCardString);
		$vCardParser = new VCard();
		$vCardParser->parse($vCardString);

		//todo: add further attributes?
		$attributes = array(
			'FN', 'TITLE', 'ORG', 'TEL', 'EMAIL', 'URL', 'ADR', 'BDAY', 'NOTE');

		foreach ($attributes as $attribute) {
			$values = $vCardParser->getProperties($attribute);
			if ($values) {
				$result .= "<".$attribute.">";
				$first = true;
				foreach ($values as $value) {
					if(!$first){
						$result .= "; ";
					}
					$first = false;
					$components = $value->getComponents();
					$lines = array();
					foreach ($components as $component) {
						if ($component) {
							$lines[] = $component;
						}
					}
					$result .= join(",", $lines);
					$types = $value->params['TYPE'];
					if ($types) {
						$type = join(", ", $types);
						$result .= " (" . ucwords(strtolower($type)) . ")";
					}
				}
				$result .= "</".$attribute.">";
			}
		}
		return $result;
	}

	public function executeCallBack($signature, $mappingPolicy, $conflictPolicy, $termImportName){
		return eval("return \$this->".$signature.",\$conflictPolicy, \$termImportName);");
	}

	private function handleVCardCallBack($vCard, $vCardMP, $conflictPolicy, $termImportName){
		$success = true;
		$logMsgs = array();
		$tiBot = new TermImportBot();
		$vCard = new SimpleXMLElement(htmlspecialchars_decode($vCard));

		$title = "".$vCard->FN;
		$title = Title::newFromText($title);
		if($title == null){
			return $this->createCallBackResult(false,
			array(array('id' => SMW_GARDISSUE_MISSING_ARTICLE_NAME,
				'title' => wfMsg('smw_ti_import_error'))));
		}
		
		$termAnnotations = $tiBot->getExistingTermAnnotations($title);

		if($title->exists() && !$conflictPolicy){
			echo wfMsg('smw_ti_articleNotUpdated', $title->getFullText())."\n";
			$article = new Article($title);
			$article->doEdit(
				$article->getContent()
				."\n[[WasIgnoredDuringTermImport::".$termImportName."| ]]", 
				wfMsg('smw_ti_creationComment'));
			return $this->createCallBackResult(true,
				array(array('id' => SMW_GARDISSUE_UPDATE_SKIPPED,
				'title' => $title->getFullText())));
		} else if($title->exists()){
			$termAnnotations['updated'][] = $termImportName;
			$updated = true;
		} else {
			$termAnnotations['added'][] = $termImportName;
			$updated = false;
		}
		
		$article = new Article($title);

		$mappingPolicy = Title::newFromText($vCardMP);
		if(!$mappingPolicy->exists()){
			return $this->createCallBackResult(false,
			array(array('id' => SMW_GARDISSUE_MAPPINGPOLICY_MISSING,
				'title' => $vCardMP)));
		}
		$mappingPolicy = new Article($mappingPolicy);
		$mappingPolicy = $mappingPolicy->getContent();

		$term = array();
		foreach($vCard->children() as $name => $value){
			if(!array_key_exists("".$name, $term)){
				$term["".$name] = array();
			}
			$term["".$name][] = array("value" => "".$value);
		}

		$content = $tiBot->createContent($term, $mappingPolicy);

		$termAnnotations = "\n\n\n"
			.$tiBot->createTermAnnotations($termAnnotations);
		$created = $article->doEdit(
			$content.$termAnnotations, wfMsg('smw_ti_creationComment'));
		if(!$created){
			return $this->createCallBackResult(false,
				array(array('id' => SMW_GARDISSUE_CREATION_FAILED,
				'title' => $title)));
		}
		
		echo "Article ".$title->getFullText();
		echo $updated==true ? " updated\n" : " created.\n";
		
		if($updated){
			return $this->createCallBackResult(true,
				array(array('id' => SMW_GARDISSUE_UPDATED_ARTICLE,
				'title' => $title->getFullText())));
		} else {
			return $this->createCallBackResult(true,
				array(array('id' => SMW_GARDISSUE_ADDED_ARTICLE,
				'title' => $title->getFullText())));
		}
	}

	
	private function handleAttachmentCallBack($fileName, $conflictPolicy, $termImportName){
		global $smwgDIIP;
		$success = true;
		$logMsgs = array();
		$tiBot = new TermImportBot();
		
		$fileArticleTitle = Title::makeTitleSafe( NS_IMAGE, $fileName );
		
		if($fileArticleTitle == null){
			return $this->createCallBackResult(false,
				array(array('id' => SMW_GARDISSUE_CREATION_FAILED,
				'title' => wfMsg('smw_ti_import_error'))));
		}
		
		$termAnnotations = $tiBot->getExistingTermAnnotations($fileArticleTitle);
		if($fileArticleTitle->exists() && !conflictPolicy){
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
		
		$fileNameArray = split("\.", $fileName);
		$ext = $fileNameArray[count($fileNameArray)-1];
		$fileFullPath = 
			$smwgDIIP.'/specials/TermImport/DAL/attachments/'.$fileName;						
		$mFileProps = File::getPropsFromPath($fileFullPath, $ext );
		$local = wfLocalFile($fileName);
		if($local == null){
			return $this->createCallBackResult(false,
				array(array('id' => SMW_GARDISSUE_CREATION_FAILED,
				'title' => $fileArticleTitle->getFullText())));
		} else {
			$termAnnotations = "\n\n\n"
				.$tiBot->createTermAnnotations($termAnnotations);
			
			$status = $local->upload(
				$fileFullPath, wfMsg('smw_ti_creationComment'), $termAnnotations, 
				File::DELETE_SOURCE, $mFileProps );
			if($status->failureCount > 0){
				return $this->createCallBackResult(false,
					array(array('id' => SMW_GARDISSUE_CREATION_FAILED,
					'title' => $fileArticleTitle->getFullText())));
			} else if($updated){
				$article = new Article($fileArticleTitle);
				$article->doEdit(
					$article->getContent()
					."\n[[WasUpdatedDuringTermImport::".$termImportName."| ]]"
					, wfMsg('smw_ti_creationComment'));
			}
		}
		
		echo "Article ".$fileArticleTitle->getFullText();
		echo $updated==true ? " updated\n" : " created.\n";
		
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
		$result = '<CallBackResult xmlns="http://www.ontoprise.de/smwplus#"><success>';
		$result .= $success ? 'true' : 'false';
		$result .= '</success>';
		foreach($logMsgs as $logMsg){
			$result .= '<logMessage><id>'.$logMsg['id']."</id>";
			$result .= '<title>'.$logMsg['title']."</title></logMessage>";
		}
		$result .= '</CallBackResult>';
		return $result;
	}
}







?>