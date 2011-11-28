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


