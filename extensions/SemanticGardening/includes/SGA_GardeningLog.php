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
 * @ingroup SemanticGardening
 *
 * Created on 16.03.2007
 *
 * Abstract SGAGardeningLog interface.
 *
 * @author Kai K�hn
 */
abstract class SGAGardeningLog {

	static $g_interface;
	/**
	 * Setups GardeningLog table
	 */
	public abstract function setup($verbose);
	/**
	 * Returns the complete gardening log as a 2-dimensional array.
	 */
	public abstract function getGardeningLogAsTable();

	/**
	 * Adds a gardening task. One must specify the $botID.
	 * Returns a task id which identifies the task.
	 *
	 * @param $botID botID
	 * @return taskID
	 */
	public abstract function addGardeningTask($botID);

	/**
	 * Removes a gardening task
	 *
	 * @param $id taskID
	 */
	public abstract function removeGardeningTask($taskID);

	/**
	 * Marks a Gardening task as finished.
	 *
	 * @param $taskID taskID
	 * @param $logContent content of log as wiki markup
	 * @param $logPageTitle optional title of a gardening log page
	 */
	public abstract function markGardeningTaskAsFinished($taskID, $logContent, $logPageTitle = null);

	/**
	 * Update progress information. Allows database updates every 15s at max.
	 *
	 * @param $taskID task to update
	 * @param $value incremental update
	 */
	public abstract function updateProgress($taskID, $value);

	/**
	 * Returns last finished Gardening task of the given type
	 *
	 * @param botID type of Gardening task
	 */
	public abstract function getLastFinishedGardeningTask($botID = NULL);
	
	/**
	 * Checks if a Gardening bot of the given type is running
	 *
	 * @param $botID Bot-ID
	 * 
	 * @return boolean
	 */
	public abstract function isGardeningBotRunning($botID = NULL);
	
	/**
	 * Cleanup Gardening log.
	 */
	public abstract function cleanupGardeningLog();

	public static function getGardeningLogAccess() {
		global $sgagIP;
		if (SGAGardeningLog::$g_interface == NULL) {

			require_once($sgagIP . '/includes/storage/SGA_GardeningLogSQL.php');
			SGAGardeningLog::$g_interface = new SGAGardeningLogSQL();
			 
		}
		return SGAGardeningLog::$g_interface;
	}
}

