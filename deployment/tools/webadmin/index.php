<?php
/*  Copyright 2011, ontoprise GmbH
 *
 *   The deployment tool is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The deployment tool is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @file
 * @ingroup WebAdmin
 *
 * @defgroup WebAdmin Web-administration tool
 * @ingroup DeployFramework
 *
 * Installation tool.
 *
 * @author: Kai Kühn / ontoprise / 2011
 *
 *
 */

// uncomment the lines to get detailed error information
// DO NOT use error reporting in production use.

// error_reporting(E_ALL);
// ini_set('display_errors', "On");

session_start();

$hostname = $_SERVER['HTTP_HOST'];
$path = dirname($_SERVER['PHP_SELF']);

if (!isset($_SESSION['angemeldet']) || !$_SESSION['angemeldet']) {
	header('Location: http://'.$hostname.($path == '/' ? '' : $path).'/login.php');
	exit;
}

define("DF_WEBADMIN_TOOL", 1);
define("DF_WEBADMIN_TOOL_VERSION", '{{$VERSION}} [B${env.BUILD_NUMBER}]');

$rootDir = dirname(__FILE__);
$rootDir = str_replace("\\", "/", $rootDir);
$rootDir = realpath($rootDir."/../../");

$mwrootDir = dirname(__FILE__);
$mwrootDir = str_replace("\\", "/", $mwrootDir);
$mwrootDir = realpath($mwrootDir."/../../../");


require_once($mwrootDir.'/deployment/settings.php');
$wgScriptPath=isset(DF_Config::$scriptPath) ? DF_Config::$scriptPath : "/mediawiki";


$smwgDFIP=$rootDir;

// touch the login marker
touch("$rootDir/tools/webadmin/sessiondata/userloggedin");

require_once($mwrootDir.'/deployment/languages/DF_Language.php');
require_once('includes/DF_StatusTab.php');
require_once('includes/DF_SearchTab.php');
require_once('includes/DF_MaintenanceTab.php');
require_once('includes/DF_UploadTab.php');
require_once('includes/DF_SettingsTab.php');
require_once('includes/DF_ServersTab.php');
require_once('includes/DF_LocalSettingsTab.php');
require_once('includes/DF_CommandInterface.php');
require_once($mwrootDir.'/deployment/io/DF_Log.php');
require_once($mwrootDir.'/deployment/io/DF_PrintoutStream.php');

$dfgOut = DFPrintoutStream::getInstance(DF_OUTPUT_FORMAT_HTML);

try {
	Logger::getInstance();
	Rollback::getInstance($mwrootDir);

} catch(DF_SettingError $e) {
	echo "<h1>Installation problem</h1>";
	echo $e->getMsg();
	die();
}

dffInitLanguage();
$dfgNoAsk=true;

// set server
$wgServer = '';

if( isset( $_SERVER['SERVER_NAME'] ) ) {
	$wgServerName = $_SERVER['SERVER_NAME'];
} elseif( isset( $_SERVER['HOSTNAME'] ) ) {
	$wgServerName = $_SERVER['HOSTNAME'];
} elseif( isset( $_SERVER['HTTP_HOST'] ) ) {
	$wgServerName = $_SERVER['HTTP_HOST'];
} elseif( isset( $_SERVER['SERVER_ADDR'] ) ) {
	$wgServerName = $_SERVER['SERVER_ADDR'];
} else {
	$wgServerName = 'localhost';
}

# check if server use https:
$wgProto = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https' : 'http';

$wgServer = $wgProto.'://' . $wgServerName;
# If the port is a non-standard one, add it to the URL
if(    isset( $_SERVER['SERVER_PORT'] )
&& !strpos( $wgServerName, ':' )
&& (    ( $wgProto == 'http' && $_SERVER['SERVER_PORT'] != 80 )
|| ( $wgProto == 'https' && $_SERVER['SERVER_PORT'] != 443 ) ) ) {

	$wgServer .= ":" . $_SERVER['SERVER_PORT'];
}



// check for ajax call
$mode = "";
if ( ! empty( $_GET["rs"] ) ) {
	$mode = "get";
}

if ( !empty( $_POST["rs"] ) ) {
	$mode = "post";
}

switch( $mode ) {

	case 'get':
		$func_name = isset( $_GET["rs"] ) ? $_GET["rs"] : '';
		if ( ! empty( $_GET["rsargs"] ) ) {
			$args = $_GET["rsargs"];
		} else {
			$args = array();
		}
		break;

	case 'post':
		$func_name = isset( $_POST["rs"] ) ? $_POST["rs"] : '';
		if ( ! empty( $_POST["rsargs"] ) ) {
			$args = $_POST["rsargs"];
		} else {
			$args = array();
		}
		break;
}

$dfgStatusTab = new DFStatusTab();
$dfgSearchTab = new DFSearchTab();
$dfgMaintenanceTab = new DFMaintenanceTab();
$dfgUploadTab = new DFUploadTab();
$dfgSettingsTab = new DFSettingsTab();
$dfgLocalSettingsTab = new DFLocalSettingsTab();
$dfgServersTab = new DFServersTab();

// for ajax calls
if (isset($func_name)) {
	$dfgCommandInterface = new DFCommandInterface();

	$ret = $dfgCommandInterface->dispatch($func_name, $args);
	if (is_string($ret)) echo $ret;
	die();
}

// initialize tabs
try {
	$statusTabName = $dfgStatusTab->getTabName();
	$statusTabHtml = $dfgStatusTab->getHTML();

	$searchTabName = $dfgSearchTab->getTabName();
	$searchTabHtml = $dfgSearchTab->getHTML();

	$maintenanceTabName = $dfgMaintenanceTab->getTabName();
	$maintenanceTabHtml = $dfgMaintenanceTab->getHTML();

	$dfgUploadTabName = $dfgUploadTab->getTabName();
	$dfgUploadTabHtml = $dfgUploadTab->getHTML();

	$dfgSettingsTabName = $dfgSettingsTab->getTabName();
	$dfgSettingsTabHtml = $dfgSettingsTab->getHTML();

	$dfgLocalSettingsTabName = $dfgLocalSettingsTab->getTabName();
	$dfgLocalSettingsTabHtml = $dfgLocalSettingsTab->getHTML();

	$dfgServersTabName = $dfgServersTab->getTabName();
	$dfgServersTabHtml = $dfgServersTab->getHTML();

} catch(DF_SettingError $e) {
	echo $e->getMsg();
	die();
}



if (!isset(DF_Config::$df_lang)) {
	$dfgLangCode = "En";
} else {
	$dfgLangCode = ucfirst(DF_Config::$df_lang);
}

$javascriptLang = '<script type="text/javascript" src="scripts/languages/DF_WebAdmin_User'.$dfgLangCode.'.js"></script>';
$javascriptLang .= '<script type="text/javascript" src="scripts/languages/DF_WebAdmin_Language.js"></script>';

if (isset($_GET['tab'])) {
	$selectedTab = $_GET['tab'];
} else {
	$selectedTab = 0;
}

$dfVersion = DF_WEBADMIN_TOOL_VERSION;

$html = <<<ENDS
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-gb" xml:lang="en-gb">
<head>
<link type="text/css" href="skins/ui-lightness/jquery-ui-1.8.13.custom.css" rel="stylesheet" />
<link type="text/css" href="skins/webadmin.css" rel="stylesheet" />	
<script type="text/javascript" src="scripts/jquery-1.6.1.min.js"></script>
<script type="text/javascript" src="scripts/jquery-ui-1.8.13.custom.min.js"></script>
<script type="text/javascript" src="scripts/jquery.json-2.3.js"></script>
<script type="text/javascript">

            wgServer="$wgServer";
            wgScriptPath="$wgScriptPath";
            dfgVersion="$dfVersion";
            
			$(function(){
		
				// Tabs
				$('#tabs').tabs( { selected: $selectedTab });
			});
</script>
$javascriptLang
<script type="text/javascript" src="scripts/sorttable.js"></script>
<script type="text/javascript" src="scripts/webadmin.js"></script>
</head>
ENDS
;
$wikiName = !empty(DF_Config::$df_wikiName) ? "(".DF_Config::$df_wikiName.")" : "";
$heading = $dfgLang->getLanguageString('df_webadmin');
$html .= "<body><img src=\"skins/logo.png\" style=\"float:left; margin-right: 30px\" />".
         "<div style=\"float:right\">".
         "<a id=\"df_webadmin_aboutlink\">".$dfgLang->getLanguageString('df_webadmin_about')."</a> | ".
         "<a href=\"$wgServer$wgScriptPath/index.php\" target=\"_blank\">".$dfgLang->getLanguageString('df_linktowiki')."</a> | ".
         "<a href=\"$wgServer$wgScriptPath/deployment/tools/webadmin/logout.php\">".$dfgLang->getLanguageString('df_logout')."</a>".
         "</div>".
         "<div id=\"df_header\">$heading $wikiName</div>";

$restoreWarning = $dfgLang->getLanguageString('df_restore_warning');
$restoreRemoveWarning = $dfgLang->getLanguageString('df_remove_restore_warning');
$deinstallWarning = $dfgLang->getLanguageString('df_uninstall_warning');
$globalUpdateWarning = $dfgLang->getLanguageString('df_globalupdate_warning');

$checkExtensionHeading = $dfgLang->getLanguageString('df_inspectextension_heading');
$deinstallHeading = $dfgLang->getLanguageString('df_webadmin_deinstall');
$globalUpdateHeading = $dfgLang->getLanguageString('df_webadmin_globalupdate');
$updateHeading = $dfgLang->getLanguageString('df_webadmin_update');
$restoreHeading = $dfgLang->getLanguageString('df_webadmin_maintenacetab');

$html .= <<<ENDS
<div id="tabs">

			<ul>
				<li><a href="#tabs-1">$statusTabName</a></li>
				<li><a href="#tabs-2">$searchTabName</a></li>
				<li><a href="#tabs-3">$dfgUploadTabName</a></li>
				<li><a href="#tabs-4">$maintenanceTabName</a></li>
				<li><a href="#tabs-5">$dfgSettingsTabName</a></li>
				<li><a href="#tabs-6">$dfgLocalSettingsTabName</a></li>
				
				
			</ul>
			<div id="tabs-1">$statusTabHtml</div>
			<div id="tabs-2">$searchTabHtml</div>
			<div id="tabs-3">$dfgUploadTabHtml</div>
			<div id="tabs-4">$maintenanceTabHtml</div>
			<div id="tabs-5">$dfgSettingsTabHtml</div>
			<div id="tabs-6">$dfgLocalSettingsTabHtml</div>
			
			
</div>
<div id="global-updatedialog-confirm" title="$globalUpdateHeading" style="display:none">
    <p><span style="float:left; margin:0 7px 20px 0;"></span><span id="global-updatedialog-confirm-text">$globalUpdateWarning</span></p>
</div>
<div id="updatedialog-confirm" title="$updateHeading" style="display:none">
    <p><span style="float:left; margin:0 7px 20px 0;"></span><span id="updatedialog-confirm-text"></span></p>
</div>
<div id="deinstall-dialog-confirm" title="$deinstallHeading" style="display:none">
    <p><span style="float:left; margin:0 7px 20px 0;"></span><span id="deinstall-dialog-confirm-text">$deinstallWarning</span></p>
</div>
<div id="restore-dialog-confirm" title="$restoreHeading" style="display:none">
    <p><span style="float:left; margin:0 7px 20px 0;"></span><span id="restore-dialog-confirm-text">$restoreWarning</span></p>
</div>
<div id="remove-restore-dialog-confirm" title="$restoreHeading" style="display:none">
    <p><span style="float:left; margin:0 7px 20px 0;"></span><span id="restore-dialog-confirm-text">$restoreRemoveWarning</span></p>
</div>
<div id="check-extension-dialog" title="$checkExtensionHeading" style="display:none">
    <p><span style="float:left; margin:0 7px 20px 0;"></span><span id="check-extension-dialog-text"></span></p>
</div>
<div id="df_extension_details" style="display:none"></div>
<div id="df_install_dialog" style="display:none"></div>
<div id="df_webadmin_about_dialog" style="display:none"></div>

ENDS
;
$html .= "</body>";

echo $html;

die();
