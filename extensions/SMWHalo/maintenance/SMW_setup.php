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
	
	TSCStorage::getDatabase()->dropDatabaseTables();
	die();
}

print "\nSetup SMWHalo.\n\n";


TSCStorage::getDatabase()->initDatabaseTables();
smwfGetSemanticStore()->setup(true);




