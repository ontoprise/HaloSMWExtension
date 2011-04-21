<?php
/**
 * @file
 * @ingroup SemanticNotifications
 */

/*  Copyright 2009, ontoprise GmbH
* 
*   This file is part of the SemanticNotifications-Extension.
*
*   The SemanticNotifications-Extension is free software; you can redistribute 
*   it and/or modify it under the terms of the GNU General Public License as 
*   published by the Free Software Foundation; either version 3 of the License, 
*   or (at your option) any later version.
*
*   The SemanticNotifications-Extension is distributed in the hope that it will 
*   be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/


/**
 * Maintenance script for setting up the database tables for SemanticNotifications
 * 
 * @author Thomas Schweitzer
 * Date: 20.12.2010
 * 
 */
if (array_key_exists('SERVER_NAME', $_SERVER) && $_SERVER['SERVER_NAME'] != NULL) {
    echo "Invalid access! A maintenance script MUST NOT accessed from remote.";
    return;
}


$mediaWikiLocation = dirname(__FILE__) . '/../../..';
require_once "$mediaWikiLocation/maintenance/commandLine.inc";
$dir = dirname(__FILE__);
$sngIP = "$dir/../../SemanticNotifications";

require_once("$sngIP/includes/SN_Storage.php");

$help = array_key_exists('help', $options) || array_key_exists('h', $options);
$delete = array_key_exists('delete', $options);

if (!$help) {
	if ( !defined( 'SGA_GARDENING_EXTENSION_VERSION' ) ) {
		die( "The extension 'Semantic Notifications' requires the extension ". 
		     "'Semantic Gardening'.\n".
		     "Please read 'extensions/SemanticNotifications/INSTALL' for further information.\n" );
	}
}


if ($help) {
	echo "Command line parameters for SN_Setup\n";
	echo "======================================\n";
	echo "no parameter: Setup the database tables for SemanticNotifications\n";
	echo "--delete: Delete all database tables of SemanticNotifications\n";
	echo "\n";
} else if ($delete) {
	echo "Deleting database tables for SemanticNotifications...";
	SNStorage::getDatabase()->dropDatabaseTables();
	echo "done.\n";
} else {
	echo "Setup program for SemanticNotifications\n";
	echo "=========================\n";
	echo "For help, please start with option --h or --help. \n\n";
	echo "Setting up database tables for SemanticNotifications...";
	SNStorage::getDatabase()->initDatabaseTables();
	echo "done.\n";
} 
