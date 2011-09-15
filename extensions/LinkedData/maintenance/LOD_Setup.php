<?php
/**
 * @file
 * @ingroup LOD_Maintenance
 */

/*  Copyright 2010, ontoprise GmbH
*  This file is part of the LinkedData-Extension.
*
*   The LinkedData-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The LinkedData-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * Maintenance script for setting up and deleting the database tables for LinkedData
 * 
 * @author Thomas Schweitzer
 * Date: 26.04.2010
 * 
 */
if (array_key_exists('SERVER_NAME', $_SERVER) && $_SERVER['SERVER_NAME'] != NULL) {
    echo "Invalid access! A maintenance script MUST NOT accessed from remote.";
    return;
}

$mediaWikiLocation = dirname(__FILE__) . '/../../..';
require_once "$mediaWikiLocation/maintenance/commandLine.inc";
$dir = dirname(__FILE__);
$lodgIP = "$dir/../../LinkedData";


require_once("$lodgIP/includes/LOD_Storage.php");
require_once("$lodgIP/includes/LOD_GlobalFunctions.php");

$delete = array_key_exists('delete', $options);

if ($delete) {
	echo "Deleting database tables for LinkedData...\n";
	TSCStorage::getDatabase()->dropDatabaseTables();
	echo "done.\n";
} else {
	echo "Setting up database tables for LinkedData...\n";
	TSCStorage::getDatabase()->initDatabaseTables();
	echo "done.\n";
}
