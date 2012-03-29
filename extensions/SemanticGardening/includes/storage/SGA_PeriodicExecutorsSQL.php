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



class SGAPeriodicExecutorsSQL extends SGAPeriodicExecutors {

	/**
	 * Initializes the gardening component
	 */
	public function setup($verbose) {
		global $wgDBname, $smwgDefaultCollation;
		$db =& wfGetDB( DB_MASTER );

		// create gardening table
		$smw_gardening = $db->tableName('smw_gardening_periodic');
		$fname = 'SGAPeriodicExecutors::setup';
		 
		// create relation table
		SGADBHelper::setupTable($smw_gardening, array(
                  'id'            =>  'INT(8) UNSIGNED NOT NULL auto_increment PRIMARY KEY' ,
                  'botid'         =>  'VARCHAR(255) NOT NULL' ,
                  'params'        =>  'MEDIUMBLOB',
                  'lastrun'       =>  'DATETIME NOT NULL',
                  'duration'      =>  'INT(8) UNSIGNED NOT NULL',
                  'runonce'      =>  'ENUM(\'y\', \'n\') DEFAULT \'n\' NOT NULL'), $db, $verbose);

	}


	public function getAllRegisteredBots() {

		$fname = 'SGAPeriodicExecutors::getAllRegisteredBots';
		$db =& wfGetDB( DB_SLAVE );

		$res = $db->select( $db->tableName('smw_gardening_periodic'),
		array('id', 'botid', 'params', 'lastrun', 'duration', 'runonce'), array(),
		$fname, array('ORDER BY' => 'id DESC') );
		 
		$result = array();
		if($db->numRows( $res ) > 0) {
			$row = $db->fetchObject($res);
			while($row) {
				$result[]=array($row->id,$row->botid,$row->params,$row->lastrun,$row->duration, $row->runonce);
				$row = $db->fetchObject($res);
			}
		}
		$db->freeResult($res);
		return $result;
	}

	public function addBot($id, $params, $duration, $startDate, $startTime) {
		global $wgUser;
        
		if ($duration <= 0) {
			throw new Exception("invalid duration. Must be > 0.");
		}
		
		if ($startDate != 'none' && strtotime($startDate) === false) {
			throw new Exception("invalid start date.");
		}
		
	    if ($startTime != 'none' && strtotime($startTime) === false) {
            throw new Exception("invalid start time.");
        }
		
		$fname = 'SGAPeriodicExecutors::addBot';
		$db =& wfGetDB( DB_MASTER );
		if ($startDate == 'none' && $startTime == 'none') {
			// use current time and date
			$dateInMillis = time();
		} else if ($startDate != 'none' && $startTime == 'none') {
			// use current time and given date
			$currentTime = date("H:i:s", time());
			$dateInMillis = strtotime($startDate." ".$currentTime);

		} else if ($startDate == 'none' && $startTime != 'none') {
			// use current date and given time, except if the given time is before the current
			// in this case add one interval
			$currentDate = date("Y-m-d", time());
			$dateInMillis = strtotime($currentDate." ".$startTime);
			 
		} else  {
			// use given date and time
			$dateInMillis = strtotime($startDate." ".$startTime);

		}

		// make sure the starting point is not in the past
		$currentTime = time();
		while ($dateInMillis < $currentTime) {
			$dateInMillis += $duration;
		}
		
		$lastRun = date("Y-m-d H:i:s", $dateInMillis - $duration);
		$db->insert( $db->tableName('smw_gardening_periodic'),
		array(
                           'botid' => $id,
                           'lastrun' => $lastRun,
                           'params' => $params,
                           'duration' => $duration,
                           'runonce' =>'n'), 
		$fname );
		return $db->insertId();
	}

	public function updateLastRun($listid) {
		global $wgUser;

		$fname = 'SGAPeriodicExecutors::updateLastRun';
		$db =& wfGetDB( DB_MASTER );

		$now = date("Y-m-d H:i:s");
		$db->update($db->tableName('smw_gardening_periodic'),
		array('lastrun' => $now, 'runonce' => 'y'), array( 'id' => $listid),
		$fname );
	}

	public function removeBot($listid) {
		$fname = 'SGAPeriodicExecutors::addBot';
		$db =& wfGetDB( DB_MASTER );
		$db->delete( $db->tableName('smw_gardening_periodic'),
		array('id' => $listid),
		$fname );
	}
}