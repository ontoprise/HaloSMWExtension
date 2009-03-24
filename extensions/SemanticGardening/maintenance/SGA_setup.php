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

sgafInitializeTables();

function sgafInitializeTables() {
    
    global $sgagIP;
    require_once('../includes/SGA_Gardening.php');
    include_once( "$sgagIP/includes/findwork/SGA_SuggestStatistics.php" );
    
    SGAGardeningIssuesAccess::getGardeningIssuesAccess()->setup(true);
    SGAGardeningLog::getGardeningLogAccess()->setup(true);
    SMWSuggestStatistics::getStore()->setup(true);
    
    return true;
}
?>