<?php
/*
 * Created on 18.10.2007
 *
 * Implementation of Gardening Log interface in SQL.
 * 
 * Author: kai
 */
 class SMWGardeningSQL extends SMW_Gardening {
 	
 	/**
 	 * Returns the complete gardening log as a 2-dimensional array.
 	 */
 	public function getGardeningLogAsTable() {
 		$this->cleanupGardeningLog();
		$fname = 'SMW::getGardeningLog';
		$db =& wfGetDB( DB_MASTER );
		
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
		             array("id = $id"), 
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
		             array('id='.$taskID),
		             $fname,array());
		if($db->numRows( $res ) == 0) {
			throw new Exception("There is no task with the id: $taskID");
		}
		$row = $db->fetchObject($res);
		$botID = $row->gardeningbot;
		        
		$title = $this->createGardeningLogFile($botID, $date, $logContent);
		$db->update( $db->tableName('smw_gardening'),
		             array('endtime' => $this->getDBDate($date),
		             	   'timestamp_end' => $db->timestamp(),
		             	   'log' => $title->getLocalURL(),
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
		$db =& wfGetDB( DB_MASTER );
			         
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
	private function cleanupGardeningLog() {
		$dbr =& wfGetDB( DB_SLAVE );
		$tblName = $dbr->tableName('smw_gardening');
		
		// Remove very old (2 days) and still running tasks. They are probably crashed. 
		// If not, they are still available via GardeningLog category.
		$twoDaysAgo  = mktime(0, 0, 0, date("m"), date("d")-2,   date("Y"));
		$date = getDate($twoDaysAgo);
		$dbr->query('DELETE FROM '.$tblName.' WHERE endtime IS NULL AND starttime < '.$dbr->addQuotes($this->getDBDate($date)));
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
