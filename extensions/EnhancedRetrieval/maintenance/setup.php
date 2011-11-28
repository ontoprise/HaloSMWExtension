<?php
/*
 * Copyright (C) Vulcan Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program.If not, see <http://www.gnu.org/licenses/>.
 *
 */
/**
 * @file
 * @ingroup EnhancedRetrievalMaintenance
 * 
 * Setup database for Enhanced retrieval extension.
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
//require_once "$mediaWikiLocation/extensions/EnhancedRetrieval/includes/EnhancedRetrieval.php";
require_once "$mediaWikiLocation/extensions/EnhancedRetrieval/includes/FacetedSearch/storage/FS_StorageSQL.php";

$delete = array_key_exists('delete', $options);
$help = array_key_exists('h', $options);
if ($help) {
    echo "\nUsage: php setup.php [ -t create only tables ] [ -h This help ]\n";
    die();
}
if ($delete) {
//	wfUSDeInitializeTables();
//	smwfSynsetsDeInitializeTables();
	
	$fsdb = new FSStorageSQL();
	$fsdb->dropDatabaseTables();
	
	print ("\nAll data removed successfully.\n");
	die();
}

/*
// no param - initialize 
$onlyTables = array_key_exists('t', $options);

print "\nSetup database for Enhanced retrieval.\n\n";
wfUSInitialize($onlyTables);

//create synset tables
print "\nSetup database for query expansion based on synsets.";
smwfSynsetsInitializeTables();
print "\n..done";
*/
print "\nInitializing table with namespace names for Faceted Search.";
$fsdb = new FSStorageSQL();
$fsdb->initDatabaseTables();
$fsdb->updateNamespaceTable();
print "\n..done";


