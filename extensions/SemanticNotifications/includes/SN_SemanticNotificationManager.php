<?php
/*  Copyright 2009, ontoprise GmbH
* 
*   This file is part of the SemanticNotifications-Extension.
*
*   The SemanticNotifications-Extension is free software; you can redistribute 
*   it and/or modify it under the terms of the GNU General Public License as 
*   published by the Free Software Foundation; either version 3 of the License, 
*   or (at your option) any later version.
*
*   The SemanticNotifications-Extension is distributed in the hope that it will 
*   be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
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
if ( !defined( 'MEDIAWIKI' ) ) die;
// Include the settings file for the configuration of the Web Service extension.
global $sngIP, $IP;

require_once( "$IP/includes/GlobalFunctions.php" );
require_once("$sngIP/includes/SN_SemanticNotificationSettings.php");
require_once("$sngIP/includes/SN_SNAjax.php");

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
		
		global $wgAutoloadClasses, $wgSpecialPages, $wgExtensionMessagesFiles;
		global $sngIP, $wgHooks;
		
		//--- Autoloading ---
		$wgAutoloadClasses['SemanticNotificationSpecial'] = $sngIP . '/specials/SN_SemanticNotificationSpecial.php';
		
		
		//--- Install the special page ---
		$wgSpecialPages['SemanticNotifications'] = array('SemanticNotificationSpecial');
		$wgExtensionMessagesFiles['SemanticNotification'] = $sngIP . '/includes/SN_SemanticNotificationMessages.php';

		// register AddHTMLHeader functions for special pages
		// to include javascript and css files (only on special page requests).
		global $wgContLang, $wgRequest;
		$spns_text = $wgContLang->getNsText(NS_SPECIAL);
		
	
		if (stripos($wgRequest->getRequestURL(), $spns_text.":") !== false ||
		    stripos($wgRequest->getRequestURL(), $spns_text."%3A") !== false) {
			wfLoadExtensionMessages('SemanticNotification');
			$sppagename = wfMsg('sn_special_url_name');
			if (stripos($wgRequest->getRequestURL(), $sppagename) !== false) {
	    		$wgHooks['BeforePageDisplay'][]='SemanticNotificationManager::addHTMLHeader';
			}
		}
			

		//--- Hooks ---
		global $wgHooks;
		$wgHooks['smwInitializeTables'][] = 'SemanticNotificationManager::initDatabaseTables';
		$wgHooks['QI_AddButtons'][] = 'SemanticNotificationManager::addQueryInterfaceButton';
		
		//--- Include files for ajax calls if necessary ---
		global $wgRequest;
		$action = $wgRequest->getVal('action');
		
		if ($action == 'ajax') {
			$method_prefix = smwfGetAjaxMethodPrefix();
			if ($method_prefix == '_sn') {
				require_once($sngIP . 'includes/SN_SNAjax.php');
			}
		}
		
		//load the Gardening Bots
		
		require_once("$sngIP/includes/SN_SemanticNotificationBot.php");
		
		
	}

	/**
	 * Creates the database tables that are used by the semantic notification
	 * extension.
	 * This is called from SMW's hook "smwInitializeTables" when the database
	 * is set up from SMW's administration page.
	 *
	 * @return boolean
	 * 		true : The chain of hook functions must continue. 
	 */
	public static function initDatabaseTables() {
		global $sngIP;
		require_once("$sngIP/includes/SN_Storage.php");
		SNStorage::getDatabase()->initDatabaseTables();	
		
		return true;
	}
	
	public static function addQueryInterfaceButton(&$buttons) {
		$buttons .= '<button id="qi-insert-notification-btn" '.
			           'class="btn" onclick="qihelper.insertAsNotification()" 
			           onmouseover="this.className=\'btn btnhov\'; 
			           Tip(\'' . wfMsg('sn_qi_tt_insertNotification') . '\')" 
			           onmouseout="this.className=\'btn\'" ' . 
						'specialpage="'.urlencode(SpecialPage::getTitleFor('SemanticNotifications')->getFullURL()).'">'.
						wfMsg('sn_qi_insertNotification') . 
			   '</button>';
		
		return true;
	}
	
	/**
	 * Called from MW to fill HTML Header before page is displayed.
	 */
	public static function addHTMLHeader(&$out) {

		global $wgTitle;
		if ($wgTitle->getNamespace() != NS_SPECIAL) 
			return true;
	
		global $smwgHaloScriptPath, $smwgDeployVersion, $smwgHaloIP, 
		       $wgLanguageCode, $smwgScriptPath, $sngScriptPath;
	
		$jsm = SMWResourceManager::SINGLETON();
		$specialpagename = ':'.wfMsg('sn_special_url_name');
	
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/prototype.js', "all", -1, NS_SPECIAL.$specialpagename);
		$jsm->addScriptIf($sngScriptPath . '/scripts/SN_SemanticNotifications.js', "view", -1, NS_SPECIAL.$specialpagename);

		$jsm->addCSSIf($smwgScriptPath . '/skins/SMW_custom.css', "all", -1, NS_SPECIAL.$specialpagename);
		$jsm->addCSSIf($sngScriptPath . '/skins/semanticnotification.css', "all", NS_SPECIAL, NS_SPECIAL.$specialpagename);

		self::addJSLanguageScripts($jsm, "all", -1, NS_SPECIAL.$specialpagename);
	
		$jsm->serializeScripts($out);
		$jsm->serializeCSS($out);
		
		return true;
			
	}
	
	/**
	 * Add appropriate JS language script
	 */
	public static function addJSLanguageScripts(& $jsm, $mode = "all", $namespace = -1, $pages = array()) {
		global $sngIP, $wgLanguageCode, $sngScriptPath, $wgUser;

		// content language file
		$jsm->addScriptIf($sngScriptPath . '/scripts/Language/SN_Language.js', $mode, $namespace, $pages);
		$lng = '/scripts/Language/SN_Language';
		if (!empty($wgLanguageCode)) {
			$lng .= ucfirst($wgLanguageCode).'.js';
			if (file_exists($sngIP . $lng)) {
				$jsm->addScriptIf($sngScriptPath . $lng, $mode, $namespace, $pages);
			} else {
				$jsm->addScriptIf($sngScriptPath . '/scripts/Language/SN_LanguageEn.js', $mode, $namespace, $pages);
			}
		} else {
			$jsm->addScriptIf($sngScriptPath . '/scripts/Language/SN_LanguageEn.js', $mode, $namespace, $pages);
		}
	
		// user language file
		$lng = '/scripts/Language/SN_Language';
		if (isset($wgUser)) {
			$lng .= "User".ucfirst($wgUser->getOption('language')).'.js';
			if (file_exists($sngScriptPath . $lng)) {
				$jsm->addScriptIf($sngScriptPath . $lng, $mode, $namespace, $pages);
			} else {
				$jsm->addScriptIf($sngScriptPath . '/scripts/Language/SN_LanguageUserEn.js', $mode, $namespace, $pages);
			}
		} else {
			$jsm->addScriptIf($sngScriptPath . '/scripts/Language/SN_LanguageUserEn.js', $mode, $namespace, $pages);
		}
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
		
		global $sngSemanticNotificationLimits, $wgUser;
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
			if (array_key_exists($g, $sngSemanticNotificationLimits)) {
				$l = $sngSemanticNotificationLimits[$g];
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
		global $sngIP;
		require_once("$sngIP/includes/SN_Storage.php");
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
		global $sngIP;
		require_once("$sngIP/includes/SN_Storage.php");
		return SNStorage::getDatabase()->getNumberOfNotificationsOfUser($userName);
	}
	
	/**
	 * All notifications of all users i.e. the user-id/name-pairs.
	 * 
	 * @return array<array<int,string>>
	 * 		An array of arrays where the inner array contains the tuples of
	 * 		user id and notification name.
	 * 
	 */
	public static function getAllNotifications() {
		global $sngIP;
		require_once("$sngIP/includes/SN_Storage.php");
		return SNStorage::getDatabase()->getAllNotifications();
	}
	
}

?>