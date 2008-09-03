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
 * Ajax functions for the semantic notifications extension.
 *
 *
 * @author Thomas Schweitzer
 */


if (!defined('MEDIAWIKI')) die();

global $wgAjaxExportList;

$wgAjaxExportList[] = 'smwf_sn_AddNotification';
$wgAjaxExportList[] = 'smwf_sn_ShowPreview';
$wgAjaxExportList[] = 'smwf_sn_GetAllNotifications';
$wgAjaxExportList[] = 'smwf_sn_GetNotification';
$wgAjaxExportList[] = 'smwf_sn_DeleteNotification';



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
function smwf_sn_AddNotification($name, $userName, $query, $updateInterval) {
	global $smwgHaloIP;
	require_once("$smwgHaloIP/specials/SMWSemanticNotifications/SMW_SemanticNotification.php");
	require_once("$smwgHaloIP/specials/SMWSemanticNotifications/SMW_SemanticNotificationManager.php");
	
	// Get the notification from the database, if it already exists.
	$sn = SemanticNotification::newFromName($name, $userName);
	
	if (!$sn) {
		// A new notification has to be created => check the limits
		$numNot = SemanticNotificationManager::getNumberOfNotificationsOfUser($userName);
		$limits = SemanticNotificationManager::getUserLimitations($userName);
		if ($numNot >= $limits['notifications']) {
			global $wgExtensionMessagesFiles;
			$wgExtensionMessagesFiles['SemanticNotification'] = $smwgHaloIP . '/specials/SMWSemanticNotifications/SMW_SemanticNotificationMessages.php';
			wfLoadExtensionMessages('SemanticNotification');
			
			return wfMsg('smw_sn_notification_limit', $limits['notifications']);
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
function smwf_sn_ShowPreview($query) {
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
function smwf_sn_GetAllNotifications($userName) {
	global $smwgHaloIP;
	require_once("$smwgHaloIP/specials/SMWSemanticNotifications/SMW_SemanticNotificationManager.php");
	
	$notifications = SemanticNotificationManager::getNotificationsOfUser($userName);
	
	return $notifications == null ? "" : implode(',', $notifications);
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
function smwf_sn_GetNotification($name, $userName) {
	global $smwgHaloIP;
	require_once("$smwgHaloIP/specials/SMWSemanticNotifications/SMW_SemanticNotification.php");
	
	$sn = SemanticNotification::newFromName($name, $userName);
	$xml = "<?xml version=\"1.0\"?>\n".
			"<notification>\n".
			"\t<name>$name</name>\n".
			"\t<user>$userName</user>\n".
			"\t<query>".
				htmlentities($sn->getQueryText()).
			"</query>\n".
			"\t<updateInterval>".$sn->getUpdateInterval()."</updateInterval>".
			"</notification>";
	return $xml;
			
}

/**
 * Deletes the semantic notification with the given name for the given user.
 *
 * @param string $name
 * 		Name of the notification.
 * @param string $userName
 * 		The name of the user who owns the notification that will be deleted.
 */
function smwf_sn_DeleteNotification($name, $userName) {
	global $smwgHaloIP;
	require_once("$smwgHaloIP/specials/SMWSemanticNotifications/SMW_SemanticNotification.php");
	
	return SemanticNotification::deleteFromDB($name, $userName);
	
}


?>