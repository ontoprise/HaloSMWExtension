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

$rootDir = dirname(__FILE__);
$rootDir = str_replace("\\", "/", $rootDir);
$rootDir = realpath($rootDir."/../../");

$mwrootDir = dirname(__FILE__);
$mwrootDir = str_replace("\\", "/", $mwrootDir);
$mwrootDir = realpath($mwrootDir."/../../../");

$smwgDFIP=$rootDir;

require_once('includes/DF_StatusTab.php');
require_once('includes/DF_SearchTab.php');
require_once('includes/DF_CommandInterface.php');
require_once($mwrootDir.'/deployment/settings.php');
require_once($mwrootDir.'/deployment/io/DF_Log.php');
require_once($mwrootDir.'/deployment/io/DF_PrintoutStream.php');

$dfgOut = DFPrintoutStream::getInstance(DF_OUTPUT_FORMAT_HTML);
$wgLanguageCode="en";
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

$wgScriptPath=isset(DF_Config::$scriptPath) ? DF_Config::$scriptPath : "/mediawiki";

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
$statusTabHtml = $dfgStatusTab->getHTML();

$dfgSearchTab = new DFSearchTab();
$searchTabHtml = $dfgSearchTab->getHTML();

// for ajax calls
if (isset($func_name)) {
	$dfgCommandInterface = new DFCommandInterface();
	
	$ret = $dfgCommandInterface->dispatch($func_name, $args);
	if (is_string($ret)) echo $ret;
	die();
}

if (!isset($dfgLangCode)) {
	$dfgLangCode = "En";
} else {
	$dfgLangCode = ucfirst($dfgLangCode);
}

$javascriptLang = '<script type="text/javascript" src="scripts/languages/DF_WebAdmin_User'.$dfgLangCode.'.js"></script>';
$javascriptLang .= '<script type="text/javascript" src="scripts/languages/DF_WebAdmin_Language.js"></script>';

$html = <<<ENDS
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-gb" xml:lang="en-gb">
<head>
<link type="text/css" href="skins/ui-lightness/jquery-ui-1.8.13.custom.css" rel="stylesheet" />
<link type="text/css" href="skins/webadmin.css" rel="stylesheet" />	
<script type="text/javascript" src="scripts/jquery-1.6.1.min.js"></script>
<script type="text/javascript" src="scripts/jquery-ui-1.8.13.custom.min.js"></script>
<script type="text/javascript">

            wgServer="$wgServer";
            wgScriptPath="$wgScriptPath";
            
			$(function(){
		
				// Tabs
				$('#tabs').tabs();
			});
</script>
$javascriptLang
<script type="text/javascript" src="scripts/webadmin.js"></script>
</head>
ENDS
;
$html .= "<body><img src=\"skins/logo.png\" /><h1>This is the web administration tool of the deployment framework.</h1>";
$html .= <<<ENDS
<div id="tabs">

			<ul>
				<li><a href="#tabs-1">Status</a></li>
				<li><a href="#tabs-2">Search</a></li>
				<li><a href="#tabs-3">Content bundles</a></li>
				<li><a href="#tabs-4">Maintenance</a></li>
			</ul>
			<div id="tabs-1">$statusTabHtml</div>
			<div id="tabs-2">$searchTabHtml</div>

			<div id="tabs-3">Nam dui erat, auctor a, dignissim quis, sollicitudin eu, felis. Pellentesque nisi urna, interdum eget, sagittis et, consequat vestibulum, lacus. Mauris porttitor ullamcorper augue.</div>
			<div id="tabs-4">Nam dui erat, auctor a, dignissim quis, sollicitudin eu, felis. Pellentesque nisi urna, interdum eget, sagittis et, consequat vestibulum, lacus. Mauris porttitor ullamcorper augue.</div>
</div>
<div id="global-updatedialog-confirm" title="Empty the recycle bin?" style="display:none">
    <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><span id=\"global-updatedialog-confirm-text\">Perform global update?</span></p>
</div>
ENDS
;
$html .= "</body>";

echo $html;

die();


/**
 * Initializes the language object
 *
 * Note: Requires wiki context
 */
function dffInitLanguage() {
    global $wgLanguageCode, $dfgLang, $mwrootDir;
    $langClass = "DF_Language_$wgLanguageCode";
    if (!file_exists($mwrootDir."/deployment/languages/$langClass.php")) {
        $langClass = "DF_Language_En";
    }
    require_once($mwrootDir."/deployment/languages/$langClass.php");
    $dfgLang = new $langClass();
}