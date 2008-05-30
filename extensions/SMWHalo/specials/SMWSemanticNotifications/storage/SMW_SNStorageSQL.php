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

/**
 * This file provides the access to the MediaWiki SQL database tables that are
 * used by the web service extension.
 *
 * @author Thomas Schweitzer
 *
 */

global $smwgHaloIP;
require_once $smwgHaloIP . '/includes/SMW_DBHelper.php';

/**
 * This class encapsulates all methods that care about the database tables of
 * the semantic notification extension.
 *
 */
class SNStorageSQL {

	/**
	 * Initializes the database tables of the semantic notification extensions.
	 * These are:
	 * - table of all queries with results: 
	 *
	 */
	public function initDatabaseTables() {

		$db =& wfGetDB( DB_MASTER );

		$verbose = true;
		DBHelper::reportProgress("Setting up semantic notification ...\n",$verbose);
	
		// create SN table
		$snTable = $db->tableName('smw_sem_notification');

		DBHelper::reportProgress("   ... Creating semantic notification database \n",$verbose);
		DBHelper::setupTable($snTable, array(
				  'user_id'  	     =>  'INT(8) UNSIGNED NOT NULL' ,
				  'query_name'       =>  'VARCHAR(255) NOT NULL' ,
				  'query_text'       =>  'TEXT NOT NULL' ,
				  'query_result'     =>  'MEDIUMTEXT NOT NULL' ,
				  'update_interval'  =>  'INT(8) UNSIGNED NOT NULL' ,
				  'timestamp'        =>  'VARCHAR(14) NOT NULL'),
		 		  $db, $verbose, 'user_id, query_name');
		DBHelper::reportProgress("   ... done!\n",$verbose);
		
	}

	/**
	 * Stores a notification in the database.
	 *
	 * @param SemanticNotification $sn
	 * 		This semantic notification object is stored. The timestamp of
	 * 		the operation is set.
	 *
	 * @return bool
	 * 	  <true>, if successful
	 *    <false>, otherwise
	 *
	 */
	public function storeSN(SemanticNotification $sn) {
		$db =& wfGetDB( DB_MASTER );
		$userID = User::idFromName($sn->getUserName());
		if (!$userID) {
			// invalid user name
			return false;
		}
		
		try {
			$now = wfTimestampNow();
			$db->replace($db->tableName('smw_sem_notification'), null, array(
					  'user_id'  	    =>  $userID ,
					  'query_name'  	=>  $sn->getName() ,
					  'query_text'      =>  $sn->getQueryText(),
					  'query_result'    =>  $sn->getQueryResult(),
					  'update_interval' =>  $sn->getUpdateInterval(),
					  'timestamp'       =>  $now
			));
			$sn->setTimestamp($now);
		} catch (Exception $e) {
			echo $e->getMessage();
			return false;
		}
		return true;

	}

	/**
	 * Retrieves the definition of the notification with the name <$name> and 
	 * the user with the name <$userName> from the database.
	 *
	 * @param string $name
	 * 		The unique name of the notification.
	 * @param string $userName
	 * 		The user who owns the notification.
	 *  
	 * @return SemanticNotification
	 * 		If the notification exists in the database, a new object is created
	 * 		and initialized with the database values. Otherwise <null> is returned. 
	 */
	public function getSN($name, $userName) {
		$userID = User::idFromName($userName);
		if (!$userID) {
			// invalid user name
			return null;
		}
		
		$db =& wfGetDB( DB_SLAVE );
		$snt = $db->tableName('smw_sem_notification');
		$sql = "SELECT sn.* FROM $snt sn ".
		          "WHERE user_id = $userID AND query_name = '".$name."';";
		$sn = null;

		$res = $db->query($sql);

		if ($db->numRows($res) == 1) {
			$row = $db->fetchObject($res);
			$sn = new SemanticNotification($name, $userName, $row->query_text, 
			                               $row->update_interval,
			                               $row->query_result,
			                               $row->timestamp);
		}
		$db->freeResult($res);
		return $sn;
		
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
	public function deleteSN($name, $userName) {
		$userID = User::idFromName($userName);
		if (!$userID) {
			// invalid user name
			return null;
		}
		
		$db =& wfGetDB( DB_MASTER );
		$snt = $db->tableName('smw_sem_notification');
		$db->delete($snt, array('user_id' => $userID, 
		                        'query_name' => $name), 
		            "SNStorage::deleteSN");
		
	}
	

}
?>