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
 * This is the main entry file for the semantic notification extension.
 * 
 * @author Thomas Schweitzer
 * 
 */

// Include the settings file for the configuration of the Web Service extension.
global $smwgHaloIP;

require_once("$smwgHaloIP/specials/SMWSemanticNotifications/SMW_SemanticNotificationSettings.php");
require_once("$smwgHaloIP/specials/SMWSemanticNotifications/SMW_SNAjax.php");

/**
 * This class contains the top level functionality of the Semantic Notification
 * extensions. It
 * - provides access to the database
 *
 */

class SemanticNotificationManager {

	
	/**
	 * Initialized the semantic notification extension:
	 * 
	 *
	 */
	static function initSemanticNotificationExtension() {
		
		// Install the special page
		global $wgAutoloadClasses, $wgSpecialPages, $wgExtensionMessagesFiles, $smwgHaloIP;
		$wgAutoloadClasses['SMWSemanticNotificationSpecial'] = $smwgHaloIP . '/specials/SMWSemanticNotifications/SMW_SemanticNotificationSpecial.php';
		$wgSpecialPages['SemanticNotifications'] = array('SMWSemanticNotificationSpecial');
		$wgExtensionMessagesFiles['SemanticNotification'] = $smwgHaloIP . '/specials/SMWSemanticNotifications/SMW_SemanticNotificationMessages.php';
		wfLoadExtensionMessages('SemanticNotification');
//TODO: Remove the test code below		
		//---Test---
/*
		smwf_sn_AddNotification("n1", "WikiSysop", "[[Category:Reactant]]", "1");
		SemanticNotificationManager::getUserLimitations();
		$sn = new SemanticNotification("MyNotification", "Thomas", 
		                               "[[Category:Reactant]]", 2);
		$sn->query();

 		$sn = new SemanticNotification("MyNotification", "Thomas", 
		                               "[[SNname::*]]".
									   "[[SNname::+]]".
									   "[[SNzip::*]]".
									   "[[SNzip::+]]".
									   "[[SNstreet::*]]".
									   "[[SNstreet::+]]".
									   "[[SNhousenumber::*]]".
									   "[[SNhousenumber::+]]", 2);
		$sn->query();
*/

/*		
		$sn->store();
		
		$sn = SemanticNotification::newFromName("MyNotification", "Thomas");
		var_dump($sn);
		$sn->setQueryResult("some result");
		$sn->store();
		
		SemanticNotification::deleteFromDB("MyNotification", "Thomas");
*/
		//--Test--
		
	}

	/**
	 * Creates the database tables that are used by the semantic notification
	 * extension.
	 *
	 */
	public static function initDatabaseTables() {
		global $smwgHaloIP;
		require_once("$smwgHaloIP/specials/SMWSemanticNotifications/SMW_SNStorage.php");
		SNStorage::getDatabase()->initDatabaseTables();	
	}
	
	/**
	 * Returns the semantic notification limits of the current user.
	 * 
	 * @param string $userName
	 * 		Name of the user or <null> if the values should be retrieved for the
	 * 		current user.
	 *
	 * @return array<key => int>
	 * 		An array with the following keys: 
	 * 			notifications: The maximal number of notifications.
	 * 			size: The maximal size of a result that is stored in the DB in bytes
	 * 			min interval: Minimal update interval in days
	 */
	public static function getUserLimitations($userName = null) {
		
		global $smwgSemanticNotificationLimits, $wgUser;
		$groups = null;
		if ($userName) {
			$u = User::newFromName($userName);
			if (!$u) {
				return null;
			}
			$groups = $wgUser->getGroups();
		} else {
			$groups = $wgUser->getGroups();
		}
		$groups[] = 'allUsers';
		
		$limits = array("notifications" => 0, 
                        "size" => 0, 
                        "min interval" => 100000);
		foreach ($groups as $g) {
			if ($g !== 'allUsers') {
				$g = 'group '.$g;
			}
			$l = $smwgSemanticNotificationLimits[$g];
			if ($l) {
				// find the least restrictive limits from several groups
				$limits['notifications'] = max($limits['notifications'], $l['notifications']);
				$limits['size']          = max($limits['size'], $l['size']);
				$limits['min interval']  = min($limits['min interval'], $l['min interval']);
			}
		}
		return $limits;
	}
	
	/**
	 * Returns an array of the names of all notifications of the given user.
	 *
	 * @param string $userName
	 * 		Name of the user, whose notifications are requested.
	 * 
	 * @return array<string>
	 * 		The names of all notifications of the given user.
	 * 
	 */
	public static function getNotificationsOfUser($userName) {
		global $smwgHaloIP;
		require_once("$smwgHaloIP/specials/SMWSemanticNotifications/SMW_SNStorage.php");
		return SNStorage::getDatabase()->getNotificationsOfUser($userName);
	}
	
	/**
	 * Returns the number of all notifications of the given user.
	 *
	 * @param string $userName
	 * 		Name of the user, whose notifications are requested.
	 *
	 * @return int
	 * 		Number of all notifications of the given user.
	 *
	 */
	public static function getNumberOfNotificationsOfUser($userName) {
		global $smwgHaloIP;
		require_once("$smwgHaloIP/specials/SMWSemanticNotifications/SMW_SNStorage.php");
		return SNStorage::getDatabase()->getNumberOfNotificationsOfUser($userName);
	}
		
}

?>