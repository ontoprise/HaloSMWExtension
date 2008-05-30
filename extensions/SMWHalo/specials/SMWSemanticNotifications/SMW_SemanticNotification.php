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

global $smwgHaloIP;
require_once("$smwgHaloIP/specials/SMWSemanticNotifications/SMW_SNStorage.php");

/**
 * Instances of this class describe a semantic notification.
 * 
 * @author Thomas Schweitzer
 * 
 */
class SemanticNotification {
	
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
	 * @param string $userName
	 * 		The user who owns the notification.
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
		SNStorage::getDatabase()->deleteSN($name, $userName);
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
	
	
	
}

?>