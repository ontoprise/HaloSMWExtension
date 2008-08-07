<?php
/*
 * Created on 18.10.2007
 *
 * Implementation of Gardening Log interface in SQL.
 * 
 * Author: kai
 */
 if ( !defined( 'MEDIAWIKI' ) ) die;
 
 global $smwgHaloIP;
 require_once $smwgHaloIP . '/includes/SMW_DBHelper.php';
 
 class SMWGardeningLogSQL extends SMWGardeningLog {
 	
 	
 	/**
	 * Initializes the gardening component
	 */
	public function setup($verbose) {
			global $wgDBname, $smwgDefaultCollation;
			$db =& wfGetDB( DB_MASTER );

			// create gardening table
			$smw_gardening = $db->tableName('smw_gardening');
			$fname = 'SMW::initGardeningLog';
			
			if (!isset($smwgDefaultCollation)) {
				$collation = '';
			} else {
				$collation = 'COLLATE '.$smwgDefaultCollation;
			}
		
			// create relation table
			DBHelper::setupTable($smw_gardening, array(
				  'id'				=>	'INT(8) UNSIGNED NOT NULL auto_increment PRIMARY KEY' ,
				  'user'      		=>  'VARCHAR(255) '.$collation.' NOT NULL' ,
				  'gardeningbot'	=>	'VARCHAR(255) '.$collation.' NOT NULL' ,
				  'starttime'  		=> 	'DATETIME NOT NULL',
				  'endtime'     	=> 	'DATETIME',
				  'timestamp_start'	=>	'VARCHAR(14) '.$collation.' NOT NULL',
				  'timestamp_end' 	=>	'VARCHAR(14) '.$collation.'',
				  'useremail'   	=>  'VARCHAR(255) '.$collation.'',
				  'log'				=>	'VARCHAR(255) '.$collation.'',
				  'progress'		=>	'DOUBLE'), $db, $verbose);


			// create GardeningLog category
			DBHelper::reportProgress("Setting up GardeningLog category ...\n",$verbose);
			$gardeningLogCategoryTitle = Title::newFromText(wfMsg('smw_gardening_log_cat'), NS_CATEGORY);
 			$gardeningLogCategory = new Article($gardeningLogCategoryTitle);
 			if (!$gardeningLogCategory->exists()) {
 				$gardeningLogCategory->insertNewArticle(wfMsg('smw_gardening_log_exp'), wfMsg('smw_gardening_log_exp'), false, false);
 			}
 			DBHelper::reportProgress("   ... GardeningLog category created.\n",$verbose);


 			// fetch all user IDs and add group SMW_GARD_ALL_USERS
 			DBHelper::reportProgress("Add exsiting users to gardening groups ...\n",$verbose);
			$res = $db->select( $db->tableName('user'),
		             array('user_id'),
		             array(),
		             "SMW::initGardeningLog",array());
		    if($db->numRows( $res ) > 0) {
				while ($row = $db->fetchObject($res)) {
					$user = User::newFromId($row->user_id);
					$user->addGroup(SMW_GARD_ALL_USERS);
				}
			}
			$db->freeResult($res);

			// fetch all sysop IDs and add group SMW_GARD_GARDENERS
			$res = $db->select( $db->tableName('user_groups'),
		             array('ug_user'),
		             array('ug_group' => 'sysop'),
		             "SMW::initGardeningLog",array());
		    if($db->numRows( $res ) > 0) {
				while ($row = $db->fetchObject($res)) {
					$user = User::newFromId($row->ug_user);
					$user->addGroup(SMW_GARD_GARDENERS);
				}
			}
			$db->freeResult($res);
			DBHelper::reportProgress("   ... done!\n",$verbose);
	}
 	/**
 	 * Returns the complete gardening log as a 2-dimensional array.
 	 */
 	public function getGardeningLogAsTable() {
 		$this->cleanupGardeningLog();
		$fname = 'SMW::getGardeningLog';
		$db =& wfGetDB( DB_SLAVE );
		
		$res = $db->select( $db->tableName('smw_gardening'),
		             array('user','gardeningbot', 'starttime','endtime','log', 'progress', 'id'), array(),
		             $fname, array('ORDER BY' => 'id DESC') );
		             
		if($db->numRows( $res ) > 0)
		{
			$row = $db->fetchObject($res);
			while($row)
			{
				$result[]=array($row->user,$row->gardeningbot,$row->starttime,$row->endtime,$row->log, $row->progress, $row->id);
				$row = $db->fetchObject($res);
			}
		}
		$db->freeResult($res);
		return count($result) === 0 ? wfMsg('smw_no_gard_log') : $result;
 	}
 	
 	/**
 	 * Adds a gardening task. One must specify the $botID.
 	 * Returns a task id which identifies the task.
 	 * 
 	 * @param $botID botID
 	 * @return taskID
 	 */
 	public function addGardeningTask($botID) {
		global $wgUser;
		
		$fname = 'SMW::addGardeningTask';
		$db =& wfGetDB( DB_MASTER );
		$date = getDate();
		
		$db->insert( $db->tableName('smw_gardening'),
		             array('user' => $wgUser->getName(),
		                   'gardeningbot' => $botID,
		                   'starttime' => $this->getDBDate($date),
		                   'endtime' => null,
		                   'timestamp_start' => $db->timestamp(),
		                   'timestamp_end' => null,
		                   'log' => null,
		                   'progress' => 0,
		                   'useremail' => $wgUser->getEmail()), 
		             $fname );
		 return $db->insertId(); 
	}
	
	/**
	 * Removes a gardening task
	 * 
	 * @param $id taskID
	 */
	public function removeGardeningTask($id) {
		$fname = 'SMW::removeGardeningTask';
		$db =& wfGetDB( DB_MASTER );
		$db->delete( $db->tableName('smw_gardening'),
		             array('id' => $id), 
		             $fname );
	}
	
	/**
	 * Marks a Gardening task as finished.
	 * 
	 * @param $taskID taskID
	 * @param $logContent content of log as wiki markup
	 */
	public function markGardeningTaskAsFinished($taskID, $logContent) {
		
		$fname = 'SMW::markGardeningTaskAsFinished';
		$db =& wfGetDB( DB_MASTER );
		$date = getDate();
				
	    // get botID
		$res = $db->select( $db->tableName('smw_gardening'),
		             array('gardeningbot'),
		             array('id' => $taskID),
		             $fname,array());
		if($db->numRows( $res ) == 0) {
			throw new Exception("There is no task with the id: $taskID");
		}
		$row = $db->fetchObject($res);
		$botID = $row->gardeningbot;
		
		$title = NULL;
		if ($logContent != NULL && $logContent != '') {      
			$title = $this->createGardeningLogFile($botID, $date, $logContent);
		}
		$gardeningLogPage = Title::newFromText(wfMsg('gardeninglog'), NS_SPECIAL);
		$db->update( $db->tableName('smw_gardening'),
		             array('endtime' => $this->getDBDate($date),
		             	   'timestamp_end' => $db->timestamp(),
		             	   'log' => $title != NULL ? $title->getPrefixedDBkey() : $gardeningLogPage->getPrefixedDBkey()."?bot=".$botID,
		             	   'progress' => 1),
		             array( 'id' => $taskID), 
		             $fname );
		return $title;
	}
	
	public function updateProgress($taskID, $value) {
		$fname = 'SMW::updateProgress';
		$db =& wfGetDB( DB_MASTER );
		$db->update( $db->tableName('smw_gardening'),
		             array('progress' => $value),
		             array( 'id' => $taskID), 
		             $fname );
	}
	
	/**
	 * Returns last finished Gardening task of the given type
	 * 
	 * @param botID type of Gardening task
	 */
	public function getLastFinishedGardeningTask($botID = NULL) {
		
		$fname = 'SMW::getLastFinishedGardeningTask';
		$db =& wfGetDB( DB_SLAVE );
			         
		$res = $db->select( $db->tableName('smw_gardening'),
		             array('MAX(timestamp_end)'),
		             $botID != NULL ? array('gardeningbot='.$db->addQuotes($botID)) : array(),
		             $fname,array());
		if($db->numRows( $res ) > 0)
		{
			$row = $db->fetchObject($res);
			if ($row) {
				$c_dummy = 'MAX(timestamp_end)';
				return $row->$c_dummy;
			}
		}
		$db->freeResult($res);
		return NULL; // minimum
	}
	
	/**
	 * Initializes Gardening table.
	 */
	public function cleanupGardeningLog() {
		$dbr =& wfGetDB( DB_MASTER );
		$tblName = $dbr->tableName('smw_gardening');
		
		// Remove very old (2 days) and still running tasks. They are probably crashed. 
		// If not, they are still available via GardeningLog category.
		$twoDaysAgo  = mktime(0, 0, 0, date("m"), date("d")-2,   date("Y"));
		$date = getDate($twoDaysAgo);
		$dbr->query('DELETE FROM '.$tblName.' WHERE endtime IS NULL AND starttime < '.$dbr->addQuotes($this->getDBDate($date)));
		
		// Remove logs which are older than 1 month. (running and finished)
		$oneMonthAgo  = mktime(0, 0, 0, date("m")-1, date("d"),   date("Y"));
		$date = getDate($oneMonthAgo);
		
		// select log pages older than one month
		$res = $dbr->query('SELECT log FROM '.$tblName.' WHERE starttime < '.$dbr->addQuotes($this->getDBDate($date)));
		if($dbr->numRows( $res ) > 0)
		{
			
			while($row = $dbr->fetchObject($res))
			{
				// get name of log page and remove the article
				$log = explode("/", $row->log);
				$logTitle = Title::newFromDBkey($log[count($log)-1]);
				$logArticle = new Article($logTitle);
				if ($logArticle->exists()) {
					$logArticle->doDeleteArticle("automatic deletion");
				} 
			}
		}
		$dbr->freeResult($res);
		
		// remove log entries
		$dbr->query('DELETE FROM '.$tblName.' WHERE starttime < '.$dbr->addQuotes($this->getDBDate($date)));
	}
	
	/**
	 * Creates a log article.
	 * Returns: Title of log article.
	 */
	private  function createGardeningLogFile($botID, $date, $logContent) {
		$timeInTitle = $date["year"]."_".$date["mon"]."_".$date["mday"]."_".$date["hours"]."_".$date["minutes"]."_".$date["seconds"];
		$title = Title::newFromText($botID."_at_".$timeInTitle);
		
 		$article = new Article($title);
 		$article->insertNewArticle($logContent, "Logging of $botID at ".$this->getDBDate($date), false, false);
 		return $title;
	}
	
	private  function getDBDate($date) {
		return $date["year"]."-".$date["mon"]."-".$date["mday"]." ".$date["hours"].":".$date["minutes"].":".$date["seconds"];
	}
	
	
 	
 }
?>
