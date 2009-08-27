<?php
/*  Copyright 2009, ontoprise GmbH
*  This file is part of the HaloACL-Extension.
*
*   The HaloACL-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The HaloACL-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * Maintenance script for setting up the database tables for Halo ACL
 * 
 * @author Thomas Schweitzer
 * Date: 21.04.2009
 * 
 */
if (array_key_exists('SERVER_NAME', $_SERVER) && $_SERVER['SERVER_NAME'] != NULL) {
    echo "Invalid access! A maintenance script MUST NOT accessed from remote.";
    return;
}

$mediaWikiLocation = dirname(__FILE__) . '/../../..';
require_once "$mediaWikiLocation/maintenance/commandLine.inc";
$dir = dirname(__FILE__);
$haclgIP = "$dir/../../HaloACL";

require_once("$haclgIP/includes/HACL_Storage.php");
require_once("$haclgIP/includes/HACL_GlobalFunctions.php");

$delete = array_key_exists('delete', $options);

if ($delete) {
	echo "Deleting database tables for HaloACL...";
	HACLStorage::getDatabase()->dropDatabaseTables();
	echo "done.\n";
} else {
	echo "Setting up database tables for HaloACL...";
	HACLStorage::getDatabase()->initDatabaseTables();
	echo "done.\n";
	
	// Create page "Permission denied".
	echo "Creating predefined pages...";
	
	global $haclgContLang, $wgLanguageCode;
	haclfInitContentLanguage($wgLanguageCode);
	$pd = $haclgContLang->getPermissionDeniedPage();
	$t = Title::newFromText($pd);
	$a = new Article($t);
	$a->doEdit($haclgContLang->getPermissionDeniedPageContent(),"", EDIT_NEW);
	echo "done.\n";
}