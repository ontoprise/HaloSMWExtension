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
 * @ingroup SemanticNotifications
 *
 * This is the main entry file for the SemanticNotifications-List extension.
 * It contains mainly constants for the configuration of the extension. This 
 * file has to be included in LocalSettings.php to enable the extension. The 
 * constants defined here can be overwritten in LocalSettings.php. After that
 * the function enableSemanticNotifications() must be called.
 * 
 * @author Thomas Schweitzer
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the SemanticNotifications extension. It is not a valid entry point.\n" );
}

if ( !defined( 'SGA_GARDENING_EXTENSION_VERSION' ) ) {
	die( "The extension 'Semantic Notifications' requires the extension ". 
	     "'Semantic Gardening'.\n".
	     "Please read 'extensions/SemanticNotifications/INSTALL' for further information.\n" );
}

define('SN_SEMANTIC_NOTIFICATIONS_VERSION', '{{$VERSION}} [B{{$BUILDNUMBER}}]');

// constant for special schema properties


###
# This is the path to your installation of SemanticNotifications as seen on your
# local filesystem. Used against some PHP file path issues.
##
$sngIP = $IP . '/extensions/SemanticNotifications';
##

###
# This is the path to your installation of SemanticNotifications as seen from the
# web. Change it if required ($wgScriptPath is the path to the base directory
# of your wiki). No final slash.
##
$sngScriptPath = $wgScriptPath . '/extensions/SemanticNotifications';


// Tell the script manager, that we need prototype
//global $smgJSLibs; 
//$smgJSLibs[] = 'prototype'; 

/**
 * Switch on SemanticNotifications. This function must be called in 
 * LocalSettings.php after SN_Initialize.php was included and default values
 * that are defined there have been modified.
 * For readability, this is the only global function that does not adhere to the
 * naming conventions.
 *
 * This function installs the extension, sets up all autoloading, special pages
 * etc.
 */
function enableSemanticNotifications() {
	global $wgExtensionFunctions, $wgExtensionCredits, $wgExtensionMessagesFiles, $sngIP;

	$wgExtensionFunctions[] = 'snfSetupExtension';
    $wgExtensionMessagesFiles['SemanticNotification'] = $sngIP . '/includes/SN_SemanticNotificationMessages.php';
	       
	//--- credits (see "Special:Version") ---
	$wgExtensionCredits['other'][]= array(
		'name'=>'Semantic Notifications', 
		'version'=>SN_SEMANTIC_NOTIFICATIONS_VERSION, 
		'author'=>"Maintained by [http://smwplus.com ontoprise GmbH].", 
		'url'=>'http://smwforum.ontoprise.com/smwforum/index.php/Help:Semantic_Notifications_Extension',
		'description' => 'Receive notification emails when the result of a query changes.');
	
	return true;
}

/**
 * Do the actual initialisation of the extension. This is just a delayed init that
 * makes sure MediaWiki is set up properly before we add our stuff.
 *
 * The main things this function does are: register all hooks, set up extension 
 * credits, and init some globals that are not for configuration settings.
 */
function snfSetupExtension() {
	wfProfileIn('snfSetupExtension');

	// Initialize the Semantic Notification Extension
	global $sngIP;
	require_once($sngIP. '/includes/SN_SemanticNotificationManager.php');
	SemanticNotificationManager::initSemanticNotificationExtension();
	wfProfileOut('snfSetupExtension');
	return true;
}
