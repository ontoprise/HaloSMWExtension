<?php
/**
 * @file
 * @ingroup SemanticNotifications_UI_Backend
 */

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
 * Ajax functions for the semantic notifications extension.
 *
 *
 * @author Thomas Schweitzer
 */


if (!defined('MEDIAWIKI')) die();

global $wgAjaxExportList;

$wgAjaxExportList[] = 'snf_sn_AddNotification';
$wgAjaxExportList[] = 'snf_sn_ShowPreview';
$wgAjaxExportList[] = 'snf_sn_GetAllNotifications';
$wgAjaxExportList[] = 'snf_sn_GetNotification';
$wgAjaxExportList[] = 'snf_sn_DeleteNotification';
$wgAjaxExportList[] = 'snf_sn_GetLanguageStrings';
$wgAjaxExportList[] = 'snf_sn_GetUserData';



/**
 * Adds or updates a semantic notification.
 *
 * @param string $name
 * 		Name of the notification.
 * @param string $userName
 * 		The name of the user who wants to add the notification
 * @param string $query
 * 		The query that is executed for the notification
 * @param string $updateInterval
 * 		The update interval in days
 *
 */
function snf_sn_AddNotification($name, $userName, $query, $updateInterval) {
	global $sngIP;
	require_once("$sngIP/includes/SN_SemanticNotification.php");
	require_once("$sngIP/includes/SN_SemanticNotificationManager.php");
	
	// Get the notification from the database, if it already exists.
	$sn = SemanticNotification::newFromName($name, $userName);
	
	if (!$sn) {
		// A new notification has to be created => check the limits
		$numNot = SemanticNotificationManager::getNumberOfNotificationsOfUser($userName);
		$limits = SemanticNotificationManager::getUserLimitations($userName);
		if ($numNot >= $limits['notifications']) {
			global $wgExtensionMessagesFiles;
			$wgExtensionMessagesFiles['SemanticNotification'] = $sngIP . '/includes/SN_SemanticNotificationMessages.php';
			wfLoadExtensionMessages('SemanticNotification');
			
			return wfMsg('sn_notification_limit', $limits['notifications']);
		}
		$sn = new SemanticNotification($name, $userName, $query, $updateInterval);
	} else {
		$sn->setQueryText($query);
		$sn->setUpdateInterval($updateInterval);
	}
	// get the current results of the query
	$sn->query();
	$success = $sn->store();
	return $success ? "true" : "false";
}

/**
 * Retrieves the results of a query for the preview on the special page.
 *
 * @param string $query
 * 		The query that is executed for the preview
 *
 */
function snf_sn_ShowPreview($query) {
	global $smwgIP;
	require_once($smwgIP . '/includes/SMW_QueryProcessor.php');

	$q = SMWQueryProcessor::createQuery($query, new ParserOptions());
	$errors = $q->getErrors();
	
	if (count($errors) > 0) {
		$r = '<ul>';
		foreach ($errors as $e) {
			$r .= "<li>$e</li>";
		}
		$r .= "</ul>";
		return 'false,'.$r;
	}
	
	$params = array('format' => 'table');
	$result = SMWQueryProcessor::getResultFromHookParams($query, $params, SMW_OUTPUT_HTML);
	// add target="_new" for all links
	$pattern = "|<a|i";
	$result = preg_replace($pattern, '<a target="_new"', $result);

	return 'true,'.$result;
}

/**
 * Returns a list of all notifications of the given user.
 *
 * @param string $userName
 * 		The name of the user
 * @return string
 * 		A comma separated list of all notifications of the user.
 *
 */
function snf_sn_GetAllNotifications($userName) {
	global $sngIP;
	require_once("$sngIP/includes/SN_SemanticNotificationManager.php");
	
	$notifications = SemanticNotificationManager::getNotificationsOfUser($userName);
	$notificationObjects = array();
	
	$i = 0;
	foreach ($notifications as $n) {
		$obj = new stdClass();
		$obj->label = $n;
		$obj->id = $i++;
		$obj->user = $userName;
		$notificationObjects[] = $obj;
	}
	
	return $notifications == null 
		? "[]" 
		: json_encode($notificationObjects);
}

/**
 * Tries to find the definition of the notification with the given name and user.
 *
 * @param string $name
 * 		Name of the notification.
 * @param string $userName
 * 		The name of the user who owns the notification.
 * 
 * @return string
 * 		An XML structure that contains the name, user, query text and update 
 * 		interval or 
 * 		"false", if the notification could not be found.
 * 
 */
function snf_sn_GetNotification($name, $userName) {
	global $sngIP;
	require_once("$sngIP/includes/SN_SemanticNotification.php");
	
	$sn = SemanticNotification::newFromName($name, $userName);
	
	if ($sn) {
		$obj = new stdClass();
		$obj->label = $name;
		$obj->id = 0;
		$obj->user = $userName;
		$obj->queryText = $sn->getQueryText();
		$obj->updateInterval = $sn->getUpdateInterval();
		$limits = SemanticNotificationManager::getUserLimitations($userName);
		$obj->minInterval = is_array($limits) ? $limits['min interval'] : 60;
	}
		
	return $sn == null 
		? "[]" 
		: json_encode($obj);
			
}

/**
 * Deletes the semantic notification with the given name for the given user.
 *
 * @param string $name
 * 		Name of the notification.
 * @param string $userName
 * 		The name of the user who owns the notification that will be deleted.
 */
function snf_sn_DeleteNotification($name, $userName) {
	global $sngIP;
	require_once("$sngIP/includes/SN_SemanticNotification.php");
	
	return SemanticNotification::deleteFromDB($name, $userName);
	
}

/**
 * Returns all strings of the current language as JSON object.
 * 
 * @return string
 * 		All language strings as JSON
 */
function snf_sn_GetLanguageStrings() {
	global $wgLanguageCode, $sngScriptPath;
	include_once 'SN_SemanticNotificationMessages.php';	
	
	// add some further strings
	$info = array(
		'queryInterfaceLink' => ' specialpage="'.urlencode(SpecialPage::getTitleFor('QueryInterface')->getFullURL()).'"',
		'imagepath'          => $sngScriptPath . '/skins/images',
		'sn_not_logged_in'   => wfMsg('sn_not_logged_in',
									  SpecialPage::getTitleFor('Userlogin')->getFullURL()),
		'sn_no_email'        => wfMsg('sn_no_email',
							          SpecialPage::getTitleFor('Preferences')->getFullURL(),
				                      wfMsg('mypreferences'))
	);
	return json_encode(array($info + $messages[$wgLanguageCode]));
	
}

/**
 * Returns information about the current user as JSON object.
 * 
 * @return string
 * 		User data as JSON
 */
function snf_sn_GetUserData() {
	global $wgUser;
	
	$userData = new stdClass();
	$userData->isLoggedIn = $wgUser->isLoggedIn();
	$userData->isEmailConfirmed = $wgUser->isEmailConfirmed();
	$userData->minInterval = 60;
	$userData->maxNotifications = 99999;
	$limits = SemanticNotificationManager::getUserLimitations($wgUser->getName());
	if (is_array($limits)) {
		$userData->minInterval = $limits['min interval'];
		$userData->maxNotifications = $limits['notifications'];
	}
	
	return json_encode(array($userData));
	
}


?>