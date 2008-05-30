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
		
		global $wgRequest;
		$action = $wgRequest->getVal('action');
	
		if ($action == 'ajax') {
			// Do not install the extension for ajax calls
			return;
		}
		
		global $smwgHaloIP;
		require_once("$smwgHaloIP/specials/SMWSemanticNotifications/SMW_SemanticNotification.php");
		//---Test---
/*		
		$sn = new SemanticNotification("MyNotification", "Thomas", "{{#ask: something}}", 2);
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
}

?>