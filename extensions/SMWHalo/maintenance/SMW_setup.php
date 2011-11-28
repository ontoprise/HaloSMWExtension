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
	//FIXME: SMWTSC de-initialization must be removed if SMWTSC is externlized
	TSCMappingStore::drop(true);
	TSCStorage::getDatabase()->dropDatabaseTables();
	die();
}

print "\nSetup SMWHalo.\n\n";
//FIXME: SMWTSC initialization must be removed if SMWTSC is externlized 
TSCStorage::getDatabase()->initDatabaseTables();
TSCMappingStore::setup(true);
smwfGetSemanticStore()->setup(true);




