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
sgafInitializeTables();
echo "\nDon't forget to run SemanticMediaWiki/maintenance/SMW_setup.php again now.\n";

function sgafInitializeTables() {
    
    global $sgagIP;
    require_once("$sgagIP/includes/SGA_GardeningInitialize.php");
    require_once("$sgagIP/includes/SGA_Gardening.php");
  
    SGAGardeningIssuesAccess::getGardeningIssuesAccess()->setup(true);
    SGAGardeningLog::getGardeningLogAccess()->setup(true);
     
    return true;
}
?>