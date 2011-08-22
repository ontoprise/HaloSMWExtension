<?php
/**
 * Setup database for Data Import extension.
 *
 * @author: Ingo Steinbauer
 *
 * Created on: 17.07.2009
 */

if (array_key_exists('SERVER_NAME', $_SERVER) && $_SERVER['SERVER_NAME'] != NULL) {
	echo "Invalid access! A maintenance script MUST NOT accessed from remote.";
	return;
}

$mediaWikiLocation = dirname(__FILE__) . '/../../..';
require_once "$mediaWikiLocation/maintenance/commandLine.inc";
global $smwgDIIP; 
$smwgDIIP = "$mediaWikiLocation/extensions/DataImport";

$help = array_key_exists("h", $options);
$delete = array_key_exists("delete", $options) || array_key_exists("d", $options);

if ($help) {
	echo "\nUsage: php DI_setup.php --h | --d \n";
	echo "Started with no parameters installs the database tables.";
	die();
}

require_once ($smwgDIIP."/specials/WebServices/SMW_WSStorage.php");
require_once ($smwgDIIP."/specials/Materialization/SMW_MaterializationStorageAccess.php");

if ($delete) {
	WSStorage::getDatabase()->deleteDatabaseTables();
	SMWMaterializationStorageAccess::getDatabase()->deleteDatabaseTables();	
} else {
	WSStorage::getDatabase()->initDatabaseTables();
	SMWMaterializationStorageAccess::getDatabase()->setup(true);
}