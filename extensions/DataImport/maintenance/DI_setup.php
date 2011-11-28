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

if ($delete) {
	WSStorage::getDatabase()->deleteDatabaseTables();
} else {
	WSStorage::getDatabase()->initDatabaseTables();
}
