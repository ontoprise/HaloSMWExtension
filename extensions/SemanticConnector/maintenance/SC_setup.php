<?php
/**
 * Setup database for SemanticConnector extension.
 *
 * @author: Ning
 */

if (array_key_exists('SERVER_NAME', $_SERVER) && $_SERVER['SERVER_NAME'] != NULL) {
	echo "Invalid access! A maintenance script MUST NOT accessed from remote.";
	return;
}

$mediaWikiLocation = dirname(__FILE__) . '/../../..';
require_once "$mediaWikiLocation/maintenance/commandLine.inc";
$scIP = "$mediaWikiLocation/extensions/SemanticConnector";

$help = array_key_exists("help", $options);
$delete = array_key_exists("delete", $options);

if ($help) {
	echo "\nUsage: php SC_setup.php --help | --delete \n";
	echo "Started with no parameters installs the database tables.";
	die();
}

require_once( $scIP . '/includes/SC_Storage.php' );

if ($delete) {
	SCStorage::getDatabase()->deleteDatabaseTables();
} else {
	SCStorage::getDatabase()->setup(true);
}