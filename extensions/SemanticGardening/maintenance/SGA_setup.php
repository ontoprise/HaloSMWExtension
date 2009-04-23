<?php
/**
 * Setup database for Semantic Gardening extension.
 * 
 * @author: Kai Kühn
 * 
 * Created on: 14.03.2009
 */

if (array_key_exists('SERVER_NAME', $_SERVER) && $_SERVER['SERVER_NAME'] != NULL) {
    echo "Invalid access! A maintenance script MUST NOT accessed from remote.";
    return;
}

$mediaWikiLocation = dirname(__FILE__) . '/../../..';
require_once "$mediaWikiLocation/maintenance/commandLine.inc";
$sgagIP = "$mediaWikiLocation/extensions/SemanticGardening";

$help = array_key_exists("help", $options);
$onlyTables = array_key_exists("onlytables", $options);
$predefpages = array_key_exists("predefpages", $options);

if ($help) {
	echo "\nUsage: php SGA_setup.php [ --onlytables ] [ --predefpages ]\n";
	echo "Started with no parameters installs database tables as well as predefined pages.";
	die();
}
if ($onlyTables) {
    sgafInitializeTables();
}

if ($predefpages) {
    SGAGardeningLog::getGardeningLogAccess()->createPredefinedPages(true);
}

if (!$onlyTables && !$predefpages) {
	 sgafInitializeTables();
	 SGAGardeningLog::getGardeningLogAccess()->createPredefinedPages(true);
     echo "\nThe Semantic Gardening has been successfully installed.\n";
}


function sgafInitializeTables() {
    
    global $sgagIP;
    require_once("$sgagIP/includes/SGA_GardeningInitialize.php");
    require_once("$sgagIP/includes/SGA_Gardening.php");
  
    SGAGardeningIssuesAccess::getGardeningIssuesAccess()->setup(true);
    SGAGardeningLog::getGardeningLogAccess()->setup(true);
    
    return true;
}
?>