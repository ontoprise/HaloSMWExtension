<?php
/**
 * Setup database for SMWHalo extension.
 * @file
 * @ingroup SMWHaloMaintenance
 * 
 * @defgroup SMWHaloMaintenance SMWHalo maintenance scripts
 * @ingroup SMWHalo
 * 
 * @author: Kai K�hn
 *
 * Created on: 3.07.2009
 */
if (array_key_exists('SERVER_NAME', $_SERVER) && $_SERVER['SERVER_NAME'] != NULL) {
	echo "Invalid access! A maintenance script MUST NOT accessed from remote.";
	return;
}

$mediaWikiLocation = dirname(__FILE__) . '/../../..';
require_once "$mediaWikiLocation/maintenance/commandLine.inc";


$help = array_key_exists('h', $options);
$delete = array_key_exists('delete', $options);

if ($help) {
	echo "\nUsage: php setup.php [ -h This help ]\n";
	die();
}

if ($delete) {
	print "\Drop SMWHalo.\n\n";
	smwfGetSemanticStore()->drop(true);
	
	//deal with the query results cache
	global $smwgHaloIP;
	require_once( "$smwgHaloIP/includes/QueryResultsCache/SMW_QRC_Store.php" );
	SMWQRCStore::getInstance()->getDB()->dropTables();
	
	die();
}

print "\nSetup SMWHalo.\n\n";
global $smwgHaloIP;
require_once( "$smwgHaloIP/includes/QueryResultsCache/SMW_QRC_Store.php" );
SMWQRCStore::getInstance()->getDB()->initDatabaseTables();

smwfGetSemanticStore()->setup(true);

//deal with the query results cache



