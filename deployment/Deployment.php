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

define( 'DF_VERSION', '{{$VERSION}} [B${env.BUILD_NUMBER}]' );
define ('DF_WIKICONTEXT', 1);

$wgExtensionFunctions[] = 'dfgSetupExtension';
$smwgDFIP = $IP . '/deployment';

// read settings.php

if(!file_exists($smwgDFIP.'/settings.php')) {
	echo '<p style="color:#ff0000">Deployment Framework warning!</p>
	<p>Could not find deployment/settings.php!<br/>
	Please copy it from deployment/config/settings.php to deployment/settings.php!</p>';
	require_once("$smwgDFIP/config/settings.php");
} else {
	require_once("$smwgDFIP/settings.php");
}
$wgExtensionMessagesFiles['WikiAdminTool'] = $smwgDFIP . '/languages/DF_Messages.php'; // register messages (requires MW=>1.11)

if (!isset(DF_Config::$df_checkForUpdateOnLogin) || DF_Config::$df_checkForUpdateOnLogin !== false) {
	$wgHooks['UserLoginComplete'][] = 'dfgCheckUpdate';
}
$wgAjaxExportList[] = 'dff_authUser';


function dfgSetupExtension() {
	dffInitializeLanguage();
	global $wgOut, $wgAutoloadClasses, $wgSpecialPages, $wgSpecialPageGroups,$smwgDFIP, $wgExtensionCredits, $dfgOut;

	$wgAutoloadClasses['SMWCheckInstallation'] = $smwgDFIP . '/specials/SMWCheckInstallation/SMW_CheckInstallation.php';
	$wgAutoloadClasses['DFBundleTools'] = $smwgDFIP . '/io/DF_BundleTools.php';
	$wgAutoloadClasses['DFPrintoutStream'] = $smwgDFIP . '/io/DF_PrintoutStream.php';
	$wgAutoloadClasses['DF_Config'] = $smwgDFIP . '/settings.php';
	$wgAutoloadClasses['DFUserInput'] = $smwgDFIP . '/tools/smwadmin/DF_UserInput.php';
	$wgSpecialPages['CheckInstallation'] = array('SMWCheckInstallation');
	$wgSpecialPageGroups['CheckInstallation'] = 'smwplus_group';
	
    // register javascript
	dff_registerScripts();
	$wgOut->addModules(array('ext.wikiadmintool.language'));

	if (defined('SGA_GARDENING_EXTENSION_VERSION')) {
		// create one instance for registration at Gardening framework
		require_once($smwgDFIP.'/bots/SGA_ImportOntologyBot.php');
		new ImportOntologyBot();
	}

	$wgExtensionCredits['other'][] = array(
        'path' => __FILE__,
        'name' => 'Wiki Administration Tool',
        'version' => DF_VERSION,
        'author' => "Maintained by [http://smwplus.com ontoprise GmbH].",
        'url' => 'http://smwforum.ontoprise.com/smwforum/index.php/Wiki_Administration_Tool',
	    'description' => 'Eases the installation and updating of extensions.'
	    );


}


function dffInitializeLanguage() {
	global $wgLanguageCode, $dfgLang, $wgMessageCache, $wgLang, $wgLanguageCode, $smwgDFIP;
	$langCode = ucfirst($wgLanguageCode);
	$langCode = str_replace( '-', '_', ucfirst( $langCode ));
	$langClass = "DF_Language_$langCode";
	if (!file_exists("$smwgDFIP/languages/$langClass.php")) {
		$langClass = "DF_Language_En";
	}
	require_once("$smwgDFIP/languages/$langClass.php");
	$dfgLang = new $langClass();
	$wgMessageCache->addMessages($dfgLang->getLanguageArray(), $wgLang->getCode());
}

function dfgCheckUpdate(&$wgUser, &$injected_html) {
	if (!$wgUser->isAllowed('delete')) return true; // FIXME: check for other right than delete
	global $IP;
	global $rootDir;
	global $dfgOut;
	$rootDir = "$IP/deployment";
	global $dfgOut, $dfgNoAsk;
	$dfgNoAsk = true; // make sure it does not block during update check
	require_once "$IP/deployment/tools/maintenance/maintenanceTools.inc";
	$dfgOut = DFPrintoutStream::getInstance(DF_OUTPUT_FORMAT_HTML);
	$cc = new ConsistencyChecker($IP);
	$dfgOut->setVerbose(false);
	$updates = $cc->checksForUpdates();
	$dfgOut->setVerbose(true);
	if (count($updates) > 0) {
		global $wgServer, $wgScriptPath;
		$html = '<a href="'.$wgServer.$wgScriptPath.'/deployment/tools/webadmin">'.wfMsg('df_updatesavailable').'</a>';
		$injected_html = $html;
	}
	return true;
}

/**
 * Checks the credentials for the user and makes sure that it is
 * member of group 'sysop'.
 *
 * @param string $username
 * @param string $password
 *
 * @return string "wikiadmintool_authorized" or "false"
 */
function dff_authUser($username, $password) {

	// set LDAP domain (if configured)
	if (isset(DF_Config::$df_webadmin_ldap_domain) && DF_Config::$df_webadmin_ldap_domain != '') {
		$_SESSION["wsDomain"] = DF_Config::$df_webadmin_ldap_domain;
	}

	// check password
	$user = User::newFromName($username);
	$correct = $user->checkPassword($password);

	// and group membership
	$groups = $user->getGroups();
	return $correct && (in_array("sysop", $groups) ||  in_array("administrator", $groups)) ? "wikiadmintool_authorized" : "false";
}

/**
 * Registers javascript code via resource loader.
 */
function dff_registerScripts() {
	global $smwgDFIP, $wgScriptPath, $wgResourceModules;

	$moduleTemplate = array(
        'localBasePath' => $smwgDFIP,
        'remoteBasePath' => $wgScriptPath . '/deployment',
        'group' => 'ext.wikiadmintool'
        );

        $wgResourceModules['ext.wikiadmintool.language'] = $moduleTemplate + array(
        'scripts' => array(
        ),
        'styles' => array(

        ),
        'messages' => array( 'df_partofbundle' ),
        
        'dependencies' => array(

        )
        );


}
