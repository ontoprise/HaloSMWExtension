<?php
/*  Copyright 2008, ontoprise GmbH
*  This file is part of the halo-Extension.
*
*   The halo-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The halo-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
if ( !defined( 'MEDIAWIKI' ) ) die;
global $smwgHaloIP;
require_once("$smwgHaloIP/specials/SMWSemanticNotifications/SMW_SNStorage.php");

/**
 * Instances of this class describe a semantic notification.
 * 
 * @author Thomas Schweitzer
 * 
 */
class SemanticNotification {
	
	//--- Constants ---
	// Comparison of query result sets
	const RESULT_ADDED = 0;		// the result has been added since the last time
	const RESULT_EQUAL = 1;		// the result has not changed
	const RESULT_CHANGED = 2;   // the subject of the result is the same but a
								// a value has changed
	const RESULT_REMOVED = 3;	// the result has been removed since the last time
	const COMPARE_HASHES = 4;   // compare hash code of result sets
	const COMPARE_RESULT = 5;	// compare complete result of result sets
		
	//--- Private fields ---
	private $mName;    		//string: The name of this notification
	private $mUserName;		//string: Name of the user who owns the notification
	private $mQueryText;    //string: The text of the query
	private $mQueryResult;  //string: The last result of the query
	private $mUpdateInterval; //int: The update interval
	private $mTimestamp;    //string: The timestamp of the last update
	
	/**
	 * Constructor for new SemanticNotification objects.
	 *
	 * @param string $name
	 * 		Name of the notification
	 * @param string $queryText
	 * 		The query text 
	 * @param int $updateInterval
	 * 		The update interval in days
	 */		
	function __construct($name = "", $userName = "", $queryText = "", $updateInterval = 1,
	                     $queryResult = "", $timestamp = 0) {
		$this->mName = $name;
		$this->mUserName = $userName;
		$this->mQueryText = $queryText;	                     	
		$this->mUpdateInterval = $updateInterval;	
		$this->mQueryResult = $queryResult;
		$this->mTimestamp = $timestamp;                 	
	}
	

	//--- getter/setter ---
	public function getName()           {return $this->mName;}
	public function getUserName()       {return $this->mUserName;}
	public function getQueryText()      {return $this->mQueryText;}
	public function getQueryResult()    {return $this->mQueryResult;}
	public function getUpdateInterval() {return $this->mUpdateInterval;}
	public function getTimestamp()      {return $this->mTimestamp;}

	public function setName($name)               {$this->mName = $name;}
	public function setUserName($userName)       {$this->mUserName = $userName;}
	public function setQueryText($query)         {$this->mQueryText = $query;}
	public function setQueryResult($result)      {$this->mQueryResult = $result;}
	public function setUpdateInterval($updtIntv) {$this->mUpdateInterval = $updtIntv;}
	public function setTimestamp($ts)            {$this->mTimestamp = $ts;}
	
	//--- Public methods ---
	
	
	/**
	 * Creates a new instance of a SemanticNotification object that is stored in the
	 * database with the specified name and user. 
	 *
	 * @param string $name
	 * 		The unique name of the notification.
	 * @param mixed (string/int) $userName
	 * 		The name or id of the user who owns the notification.
	 * 
	 * @return SemanticNotification
	 * 		If the notification exists in the database, a new object is created
	 * 		and initialized with the database values. Otherwise <null> is returned. 
	 */
	public static function newFromName($name, $userName) {
		return SNStorage::getDatabase()->getSN($name, $userName);
	}

	/**
	 * Deletes the semantic notification with the name <$name> of the user <$userName>
	 * from database. 
	 *
	 * @param string $name
	 * 		The unique name of the notification.
	 * @param string $userName
	 * 		The user who owns the notification.
	 */
	public static function deleteFromDB($name, $userName) {
		return SNStorage::getDatabase()->deleteSN($name, $userName);
	}
		
	/**
	 * Stores the notification in the database.
	 * 
	 * @return bool
	 * 	  <true>, if successful
	 *    <false>, otherwise
	 *
	 */
	public function store() {
		return SNStorage::getDatabase()->storeSN($this);
	}
	
	/**
	 * Executes the notification's query and compares it to the previous result.
	 * If changes are detected, a notification e-mail is assembled an sent to 
	 * the user who defined this notification.
	 * The new result is not stored in the database.
	 * 
	 * @return boolean
	 * 		true: changes detected, e-mail sent
	 * 		false: no changes
	 *
	 */
	public function sendNotificationMessage() {
		$diff = &$this->queryAndCompare();
		if ($diff === false) {
			return false;
		}
		$text  = wfMsg('smw_sn_msg_salutation', $this->mUserName, $this->mName);
		$text .= wfMsg('smw_sn_msg_query', $this->mQueryText);

		$text .= wfMsg('smw_sn_msg_changes_found');
		
		$added = $this->qac2HTML($diff, 'added');
		$tadded = (is_int($added)) 
					? (($added > 0) ? wfMsg('smw_sn_msg_numadded', $added) : '')
					: $added;
								  
		$removed = $this->qac2HTML($diff, 'removed');
		$tremoved = (is_int($removed)) 
					? (($removed > 0) ? wfMsg('smw_sn_msg_numremoved', $removed) : '')
					: $removed;
								  
		$changed = $this->qac2HTML($diff, 'changed');
		$tchanged .= (is_int($changed)) 
					? (($changed > 0) ? wfMsg('smw_sn_msg_numchanged', $changed) : '')
					: $changed;
								  
		if (is_int($added) || is_int($removed)) {
			$text .= $tadded;
			$text .= $tremoved;
			$text .= wfMsg('smw_sn_msg_limit');
		} else {
			$text .= $this->qac2HTML($diff, 'header');
			$text .= $tadded;
			$text .= $tremoved;
			$text .= $tchanged;
			$text .= $this->qac2HTML($diff, 'footer');
		}
		
		$special = SpecialPage::getTitleFor('SemanticNotifications')->getFullURL();
		$text .= wfMsg('smw_sn_msg_link', 
		               '<a href="'.$special.'">'.$special.'</a>' );
		$text = 
<<<HTML
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=ISO-8859-1">
		<title>Notification email</title>
		<style type="text/css">
			table.smwtable{
				background-color: #EEEEFF;
			}
			
			table.smwtable th{
				background-color: #EEEEFF;
				text-align: left;
			}
			
			table.smwtable td{
				background-color: #FFFFFF;
				padding: 1px;
				padding-left: 5px;
				padding-right: 5px;
				text-align: left;
				vertical-align: top;
			}
			
			table.smwtable td.smwchanged {
				border:	2px solid #ffaa00
			}
			table.smwtable tr.smwadded td{
				border:	2px solid #00ff00
			}
			table.smwtable tr.smwremoved td{
				border:	2px solid #ff0000
			}
		</style>
	</head>
	<body>
		$text
	</body>
</html>
HTML;
			
//		echo "$text";
		$u = User::newFromName($this->mUserName);
		global $wgSitename;
		$r = $this->sendMail($u, null, wfMsg('smw_sn_mail_title', $wgSitename), $text);
		
		return true;
	}
	
	/**
	 * Executes the query and stores the result in the field <mQueryResult>.
	 *
	 */
	public function query() {
		global $smwgIP, $smwgHaloIP;
		require_once("$smwgHaloIP/specials/SMWSemanticNotifications/SMW_QP_SNXML.php");
		require_once($smwgIP . '/includes/SMW_QueryProcessor.php');

		// find the variables for sorting the result		
		$q = SMWQueryProcessor::createQuery($this->mQueryText, new ParserOptions());
		$desc = $q->getDescription();
		$requests = $desc->getPrintRequests();
		$sortvars = "";
		foreach ($requests as $pr) {
			if ($pr->getMode() == SMW_PRINT_PROP) {
				$sortvars .= "," . $pr->getLabel();
			}
		}
		
		global $smwgQMaxLimit;
		SMWQueryProcessor::$formats['snxml'] = 'SMW_SN_XMLResultPrinter';
		
		$this->mQueryResult = SMWQueryProcessor::getResultFromHookParams(
								$this->mQueryText, 
								array('format' => 'snxml', 
								      'sort' => $sortvars,
									  'limit' => $smwgQMaxLimit), 
								SMW_OUTPUT_HTML);
		
	}
	
	/**
	 * Executes the query and compares the current results with the former
	 * results. The result of the new query is stored in $this->mQueryResult and
	 * the old result is lost.
	 *
	 * @return mixed: array(key => array<SimpleXMLElement>) or bool
	 * 		There are up to four keys:
	 * 		"added" => array<SimpleXMLElement>: All rows that have been added
	 * 		"removed" => array<SimpleXMLElement>: All rows that have been removed
	 * 		"changed" => array<array<SimpleXMLElement>>: All rows that have been changed.
	 * 				Each element is an array with the keys "old" and "new" whose
	 * 				value is a "SimpleXMLElement".
	 * 		"columns" => array<string>:
	 * 				The names of the columns of the result table. This entry is
	 * 				only present, if a full comparison was possible.
	 * 	 * 		or
	 * 		<false>, if the result set has not changed
	 * 
	 */
	public function &queryAndCompare() {
		$oldResult = $this->mQueryResult;
		$this->query();
		return $this->compareResults($oldResult, $this->mQueryResult);
	}
	
	/**
	 * The result of the method "queryAndCompare" is an array with the three keys
	 * 'added', 'removed', 'changed' and their value. This method generates a
	 * HTML representation for a given key. Values can be arrays of 
	 * SimpleXMLElements or just integers. In the first case a table is generated,
	 * in the second the number is returned.
	 *
	 * @param array $qacResult
	 * 		The result of methid "queryAndCompare"
	 * @param string $type
	 * 		One of 'header', 'footer', 'added', 'removed' or 'changed'
	 * @return string
	 * 		The HTML representation of the result
	 */
	public function qac2HTML($qacResult, $type) {
		
		if ($type == 'header') {
			$html .= '<table class="smwtable">';
			
			$columns = &$qacResult['columns'];
			if ($columns) {
				$html .= '<tr>';
				foreach ($columns as $col) {
					$html .= "<th>$col</th>";
				}
				$html .= '</tr>';
			}
			return $html;
		} else if ($type == 'footer') {
			return '</table>';
		}

		$results = $qacResult[$type];
		if (!is_array($results)) {
			// The result is a number
			return (int) $results;
		}
		if (count($results) == 0) {
			return "";
		}
		
		if ($type == 'changed') {
			// Changed results consist of the old and the new value
			foreach ($results as $r) {
				$old = array();
				$new = array();
				$html .= '<tr>';
				foreach ($r['old']->cell as $c) {
					$old[] = (string) $c;
				}
				foreach ($r['new']->cell as $c) {
					$new[] = (string) $c;
				}
				$numVal = count($old);
				for ($i = 0; $i < $numVal; ++$i) {
					$o = $old[$i];
					$n = $new[$i];
					$html .= ($o == $n) 
							? '<td>'.$o."</td>"
							: '<td class="smwchanged"><b>'.$n.'</b> ('.$o.')</td>';
				}
				$html .= '</ tr>';
			}
		} else {
			// Print added or removed results
			foreach ($results as $r) {
				$html .= '<tr class="smw'.$type.'">';
				foreach ($r->cell as $c) {
					$html .= "<td>".(string) $c."</td>";
				}
				$html .= '</tr>';
			}
		}
		
		return $html;
		
	}
	
	/**
	 * Compares two result sets that are given in the XML representation. The 
	 * sets are parsed with the SimpleXML parser. The comparison finds added,
	 * removed and changed rows in the table of results. An element is considered
	 * as 'changed', if the subject (first column) did not change, but one of the
	 * following columns did.
	 *
	 * @param string $old
	 * 		XML representation of the old result set
	 * @param string $new
	 * 		XML representation of the new result set
	 * @return mixed: array(key => array<SimpleXMLElement>) or bool
	 * 		There are up to four keys:
	 * 		"added" => array<SimpleXMLElement>/int: 
	 * 			All rows that have been added are the number of added rows 
	 * 		"removed" => array<SimpleXMLElement>/int: 
	 * 			All rows that have been removed or the number of removed rows
	 * 		"changed" => array<array<SimpleXMLElement>>/int: 
	 * 				All rows that have been changed or the number of changed rows.
	 * 				Each element is an array with the keys "old" and "new" whose
	 * 				value is a "SimpleXMLElement".
	 * 		"columns" => array<string>:
	 * 				The names of the columns of the result table. This entry is
	 * 				only present, if a full comparison was possible.
	 * 		or
	 * 		<false>, if the result set has not changed
	 *
 	 */
	private function &compareResults(&$old, &$new) {
		
		$oldXML = $newXML = null;
		if (!empty($old)) {
			// Parse the old result set
			try {
				$oldXML = new SimpleXMLElement($old);
			} catch (Exception $e) {
				return $e->getMessage();
			}
		}
		if (!empty($new)) {
			// Parse the new result set
			try {
				$newXML = new SimpleXMLElement($new);
			} catch (Exception $e) {
				return $e->getMessage();
			}
		}
				
		$r = $this->getCompareAction($oldXML, $newXML);
		if ($r == RESULT_EQUAL) {
			return false;
		} else if ($r == COMPARE_HASHES) {
			return $this->compareHashes($oldXML, $newXML);
		} else if ($r == COMPARE_RESULT) {
			$r = $this->compareCompleteResult($oldXML, $newXML);
			$c = $newXML->table->columnnames;
			$columns = array();
			foreach ($c->col as $column) {
				$columns[] = (string) $column;
			}
			$r['columns'] = $columns;
			return $r;
		} else {
			return $r;
		}
		
	}

	/**
	 * Due to possibly limited sizes of result sets, they can be stored in tree
	 * ways: 
	 * 1. Fully stored result
	 * 2. Only hash codes stored
	 * 3. Only number of results store
	 * This method decides, how the old and new result set can be compared.
	 *
	 * @param SimpleXMLElement $oldXML
	 * 		The parsed old result set
	 * @param SimpleXMLElement $newXML
	 * 		The parsed new result set
	 * @return mixed int/array
	 * 		One of:
	 * 		RESULT_EQUAL  : The result sets are equal (identical hash code)
	 * 		COMPARE_HASHES: Compare the hash codes of both result sets
	 * 		COMPARE_RESULT: Compare the complete result of both sets
	 * 		array ('added' => num added,
	 *             'removed' => num removed,
	 *             'changed' => 0)
	 */
	private function getCompareAction(&$oldXML, &$newXML) {
		// compare the hash codes of the complete result set
		if ($oldXML && $newXML 
		    && ((string) $oldXML->table->hash == (string) $newXML->table->hash)) {
			// hash codes are identical => no change
			return RESULT_EQUAL;
		}
		
		// Get the number of results as it is stored in the table header
		$numTableOld = (int) $oldXML->table->rows;
		$numTableNew = (int) $newXML->table->rows;
		
		// Get the number of results that are actually stored in the XML structure
		$numResultOld = count($oldXML->result->row);
		$numResultNew = count($newXML->result->row);

		// are the results hashed?
		$h = $oldXML->result->row[0];
		$hashedOld = ($h['hash'] != null);
		$h = $newXML->result->row[0];
		$hashedNew = ($h['hash'] != null);
		
		if ($hashedOld && $hashedNew) {
			// both result sets are hashed
			return COMPARE_HASHES;
		}
		
		if ($numResultOld > 0 && $numResultNew > 0
		    && !$hashedOld && !$hashedNew) {
			// both result sets are fully stored
			return COMPARE_RESULT;
		}
		
		// No comparison possible => just compare the number results
		return ($numTableOld < $numTableNew) 
				? array('added' => $numTableNew-$numTableOld, 
				        'removed' => 0, 
				        'changed' => 0)
				: array('added' => 0, 
				        'removed' => $numTableOld-$numTableNew, 
				        'changed' => 0);
			
	}
	
	/**
	 * Compares the result sets that are given by their hash values. This function
	 * can only find out, how many results have been added or removed. However,
	 * a changed value can only be interpreted as removed and added.
	 * 
	 * @param SimpleXMLElement $oldXML
	 * 		The XML structure of the old result set.
	 * @param SimpleXMLElement $newXML
	 * 		The XML structure of the new result set.
	 *
	 * @return array<key=>int>
	 * 		The keys are:
	 * 		added   => number of added results
	 * 		removed => number of removed results
	 * 		changed => 0
	 */
	private function compareHashes(&$oldXML, &$newXML) {
		$oldRow = $oldXML->result->row[0];
		$newRow = $newXML->result->row[0];
		
		// both results have a hash code
		$oh = array();
		$nh = array();
		foreach ($oldXML->result->row as $r) {
			$oh[] = (string) $r['hash'];
		}
		foreach ($newXML->result->row as $r) {
			$nh[] = (string) $r['hash'];
		}
		sort($oh);
		sort($nh);

		$newNum = count($nh);
		$oldNum = count($oh);
		$removed = 0;
		$added = 0;
		// Compare the result sets
		for  ($in = 0, $io = 0; $in < $newNum && $io < $oldNum; ) {
			// Compare the rows of the tables
			$c = strcmp($oh[$io], $nh[$in]);
			if ($c < 0) {
				++$removed;
				++$io;
			} else if ($c == 0) {
				++$in;
				++$io;
			} else {
				++$added;
				++$in;
			}
		}
		
		$added   += $newNum - $in;
		$removed += $oldNum - $io;
		return array("added" => $added, "removed" => $removed, "changed" => 0);
	}
	
	/**
	 * Compares two fully specified result sets that are given in the parsed XML 
	 * representation.  The comparison finds added, removed and changed rows in
	 * the table of results. An element is considered as 'changed', if the 
	 * subject (first column) did not change, but one of the following columns did.
	 *
	 * @param SimpleXMLElement $oldXML
	 * 		XML representation of the old result set
	 * @param SimpleXMLElement $newXML
	 * 		XML representation of the new result set
	 * @return array(key => array<SimpleXMLElement>)
	 * 		There are three keys:
	 * 		"added" => array<SimpleXMLElement>/int: 
	 * 			All rows that have been added are the number of added rows 
	 * 		"removed" => array<SimpleXMLElement>/int: 
	 * 			All rows that have been removed or the number of removed rows
	 * 		"changed" => array<array<SimpleXMLElement>>/int: 
	 * 				All rows that have been changed or the number of changed rows.
	 * 				Each element is an array with the keys "old" and "new" whose
	 * 				value is a "SimpleXMLElement".
	 *
 	 */
	private function compareCompleteResult(&$oldXML, &$newXML) {
		$added = array();
		$removed = array();
		$changed = array();
		
		$oldNum = count($oldXML->result->row);
		$newNum = count($newXML->result->row);
		
		// Compare the result sets
		for  ($in = 0, $io = 0; $in < $newNum && $io < $oldNum; ) {
			$newCont = $newXML->result->row[$in];
			$oldCont = $oldXML->result->row[$io];
			// Compare the rows of the tables
			$c = $this->compareRow($oldCont, $newCont);
			switch ($c) {
				case RESULT_REMOVED:
					$removed[] = $oldCont;
					++$io;
					break;
				case RESULT_CHANGED:
					$changed[] = array("old" => $oldCont, "new" => $newCont);
				case RESULT_EQUAL:
					++$in;
					++$io;
					break;
				case RESULT_ADDED:
					$added[] = $newCont;
					++$in;
					break;
			}
		}
		
		// Process the rest of the old result set
		for  (; $io < $oldNum; ++$io) {
			$oldCont = $oldXML->result->row[$io];
			$removed[] = $oldCont;
		}
		// Process the rest of the new result set
		for  (; $in < $newNum; ++$in) {
			$newCont = $newXML->result->row[$in];
			$added[] = $newCont;
		}
		
		return array('added' => $added, 'changed' => $changed, 'removed' => $removed);
	}

	/**
	 * Compares a row of the old and the current result set.  
	 *
	 * @param SimpleXMLElement $oldCont
	 * 		A row of the old result table.
	 * @param SimpleXMLElement $newCont
	 * 		A row of the new result table.
	 * 
	 * @return int
	 * 		RESULT_ADDED  : the result has been added since the last time
	 * 		RESULT_EQUAL  : the result has not changed
	 * 		RESULT_CHANGED: the subject of the result is the same but a
	 *						a value has changed
	 *		RESULT_REMOVED: the result has been removed since the last time
	 * 
	 */
	private function compareRow($oldCont, $newCont) {
		// old < new  : old removed; ++old  return -1
		// old == new : identical or changed; ++old; ++new  return 0
		// old > new  : new added; ++ new  return 1
		if (count($oldCont->cell) > 1) {
			// a row with several columns
			$sameSubject = ((string) $oldCont->cell[0] == (string) $newCont->cell[0]);
			$num = count($oldCont->cell);
			for  ($i = 0; $i < $num; ++$i) {
				$cmp = strcmp($oldCont->cell[$i], $newCont->cell[$i]);
				if ($cmp < 0) {
					return $sameSubject ? RESULT_CHANGED : RESULT_REMOVED;
				} else if ($cmp > 0) {
					return $sameSubject ? RESULT_CHANGED : RESULT_ADDED;
				}
			}
			return RESULT_EQUAL;
		} else {
			// only one column
			$cmp = strcmp($oldCont->cell, $newCont->cell);
			switch ($cmp) {
				case -1:
					return RESULT_REMOVED;
				case 0:
					return RESULT_EQUAL;
				case 1:
					return RESULT_ADDED;
			}
		}
	}
	
	/**
	 * This function will perform a direct (authenticated) login to
	 * a SMTP Server to use for mail relaying if 'wgSMTP' specifies an
	 * array of parameters. It requires PEAR:Mail to do that.
	 * Otherwise it just uses the standard PHP 'mail' function.
	 * This function is copied from UserMailer and modified in order to send
	 * HMTL-e-mails.
	 * 
	 * @param $user User: recipient 
	 * @param $from MailAddress: sender's email
	 * @param $subject String: email's subject.
	 * @param $body String: email's text.
	 * @param $replyto String: optional reply-to email (default: null).
	 * @return mixed True on success, a WikiError object on failure.
	 * 
	 */
	private static function sendMail( $user, $from, $subject, $body, $replyto=null ) {
		global $wgSMTP, $wgOutputEncoding, $wgErrorString, $wgEnotifImpersonal;
		global $wgEnotifMaxRecips;

		if( is_null( $from ) ) {
			global $wgPasswordSender;
			$from = new MailAddress( $wgPasswordSender );
		}

		$to = new MailAddress( $user );
		
		if ( is_array( $to ) ) {
			wfDebug( __METHOD__.': sending mail to ' . implode( ',', $to ) . "\n" );
		} else {
			wfDebug( __METHOD__.': sending mail to ' . implode( ',', array( $to->toString() ) ) . "\n" );
		}

		if (is_array( $wgSMTP )) {
			require_once( 'Mail.php' );

			$msgid = str_replace(" ", "_", microtime());
			if (function_exists('posix_getpid'))
				$msgid .= '.' . posix_getpid();

			if (is_array($to)) {
				$dest = array();
				foreach ($to as $u)
					$dest[] = $u->address;
			} else
				$dest = $to->address;

			$headers = array();
			$headers['From'] = $from->toString();

			if ($wgEnotifImpersonal)
				$headers['To'] = 'undisclosed-recipients:;';
			else
				$headers['To'] = $to->toString();

			if ( $replyto ) {
				$headers['Reply-To'] = $replyto->toString();
			}
			$headers['Subject'] = wfQuotedPrintable( $subject );
			$headers['Date'] = date( 'r' );
			$headers['MIME-Version'] = '1.0';
			$headers['Content-type'] = 'text/html; charset='.$wgOutputEncoding;
			$headers['Content-transfer-encoding'] = '8bit';
			$headers['Message-ID'] = "<$msgid@" . $wgSMTP['IDHost'] . '>'; // FIXME
			$headers['X-Mailer'] = 'MediaWiki mailer';

			// Create the mail object using the Mail::factory method
			$mail_object =& Mail::factory('smtp', $wgSMTP);
			if( PEAR::isError( $mail_object ) ) {
				wfDebug( "PEAR::Mail factory failed: " . $mail_object->getMessage() . "\n" );
				return new WikiError( $mail_object->getMessage() );
			}

			wfDebug( "Sending mail via PEAR::Mail to $dest\n" );
			$chunks = array_chunk( (array)$dest, $wgEnotifMaxRecips );
			foreach ($chunks as $chunk) {
				$e = self::sendWithPear($mail_object, $chunk, $headers, $body);
				if( WikiError::isError( $e ) )
					return $e;
			}
		} else	{
			# In the following $headers = expression we removed "Reply-To: {$from}\r\n" , because it is treated differently
			# (fifth parameter of the PHP mail function, see some lines below)

			# Line endings need to be different on Unix and Windows due to 
			# the bug described at http://trac.wordpress.org/ticket/2603
			if ( wfIsWindows() ) {
				$body = str_replace( "\n", "\r\n", $body );
				$endl = "\r\n";
			} else {
				$endl = "\n";
			}
			$headers =
				"MIME-Version: 1.0$endl" .
				"Content-type: text/html; charset={$wgOutputEncoding}$endl" .
				"Content-Transfer-Encoding: 8bit$endl" .
				"X-Mailer: MediaWiki mailer$endl".
				'From: ' . $from->toString();
			if ($replyto) {
				$headers .= "{$endl}Reply-To: " . $replyto->toString();
			}

			$wgErrorString = '';
			$html_errors = ini_get( 'html_errors' );
			ini_set( 'html_errors', '0' );
			set_error_handler( array( 'UserMailer', 'errorHandler' ) );
			wfDebug( "Sending mail via internal mail() function\n" );

			if (function_exists('mail')) {
				if (is_array($to)) {
					foreach ($to as $recip) {
						$sent = mail( $recip->toString(), wfQuotedPrintable( $subject ), $body, $headers );
					}
				} else {
					$sent = mail( $to->toString(), wfQuotedPrintable( $subject ), $body, $headers );
				}
			} else {
				$wgErrorString = 'PHP is not configured to send mail';
			}

			restore_error_handler();
			ini_set( 'html_errors', $html_errors );

			if ( $wgErrorString ) {
				wfDebug( "Error sending mail: $wgErrorString\n" );
				return new WikiError( $wgErrorString );
			} elseif (! $sent) {
				//mail function only tells if there's an error
				wfDebug( "Error sending mail\n" );
				return new WikiError( 'mailer error' );
			} else {
				return true;
			}
		}
	}

	/**
	 * Send mail using a PEAR mailer. This function is copied from UserMailer.
	 */
	private static function sendWithPear($mailer, $dest, $headers, $body)
	{
		$mailResult = $mailer->send($dest, $headers, $body);

		# Based on the result return an error string,
		if( PEAR::isError( $mailResult ) ) {
			wfDebug( "PEAR::Mail failed: " . $mailResult->getMessage() . "\n" );
			return new WikiError( $mailResult->getMessage() );
		} else {
			return true;
		}
	}
	
	
}
	
?>