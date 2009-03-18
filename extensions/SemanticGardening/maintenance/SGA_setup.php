<?php
/**
 * Setup database for Unified search extension.
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

sgafInitializeTables();

function sgafInitializeTables() {
    
    require_once('../includes/SGA_Gardening.php');
   
    SGAGardeningIssuesAccess::getGardeningIssuesAccess()->setup(true);
    SGAGardeningLog::getGardeningLogAccess()->setup(true);

    return true;
}
?>