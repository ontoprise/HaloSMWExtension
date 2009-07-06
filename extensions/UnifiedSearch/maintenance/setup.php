<?php
/**
 * Setup database for Unified search extension.
 * 
 * @author: Kai K�hn
 * 
 * Created on: 27.01.2009
 */
if (array_key_exists('SERVER_NAME', $_SERVER) && $_SERVER['SERVER_NAME'] != NULL) {
    echo "Invalid access! A maintenance script MUST NOT accessed from remote.";
    return;
}

$mediaWikiLocation = dirname(__FILE__) . '/../../..';
require_once "$mediaWikiLocation/maintenance/commandLine.inc";
require_once "$mediaWikiLocation/extensions/UnifiedSearch/includes/UnifiedSearch.php";

$delete = array_key_exists('delete', $options);
$help = array_key_exists('h', $options);
if ($help) {
    echo "\nUsage: php setup.php [ -t create only tables ] [ -h This help ]\n";
    die();
}
if ($delete) {
	wfUSDeInitializeTables();
	smwfSynsetsDeInitializeTables();
	print ("\nAll data removed successfully.\n");
	die();
}


// no param - initialize 
$onlyTables = array_key_exists('t', $options);

print "\nSetup database for Unified search.\n\n";
wfUSInitialize($onlyTables);

//create synset tables
print "\nSetup database for query expansion based on synsets.";
smwfSynsetsInitializeTables();
print "\n..done";


?>