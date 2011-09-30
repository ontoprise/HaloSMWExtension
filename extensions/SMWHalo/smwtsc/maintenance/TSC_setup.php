<?php 
/**
 * Setup database for SMWTSC extension.
 * @file
 * @ingroup SMWTSCMaintenance
 * 
 * @defgroup SMWTSCMaintenance SMWTSC maintenance scripts
 * @ingroup SMWTSC
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
    echo "\nUsage: php TSC_setup.php [ -h This help ]\n";
    die();
}

if ($delete) {
    print "\Drop SMWTSC.\n\n";
    TSCStorage::getDatabase()->dropDatabaseTables();
    TSCMappingStore::drop(true);
    die();
}

print "\nSetup SMWTSC.\n\n";
TSCStorage::getDatabase()->initDatabaseTables();
TSCMappingStore::setup(true);


