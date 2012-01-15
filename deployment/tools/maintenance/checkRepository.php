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
 * @ingroup DFMaintenance
 *
 * Checks if all dependencies of all extensions exist and that they have the correct version.
 *
 * Usage:   php checkRepository.php
 *
 * 	Process terminates with exit code 0 if all dependecies are fulfilled, otherwise 1.
 *
 * @author: Kai Kuehn
 */
if ( isset( $_SERVER ) && array_key_exists( 'REQUEST_METHOD', $_SERVER ) ) {
    die( "This script must be run from the command line\n" );
}

global $rootDir;
$rootDir = dirname(__FILE__);
$rootDir = str_replace("\\", "/", $rootDir);
$rootDir = realpath($rootDir."/../../");

require_once($rootDir."/io/DF_PrintoutStream.php");
require_once($rootDir."/tools/maintenance/maintenanceTools.inc");

$mwRootDir = dirname(__FILE__);
$mwRootDir = str_replace("\\", "/", $mwRootDir);
$mwRootDir = realpath($mwRootDir."/../../..");

if (substr($mwRootDir, -1) != "/") $mwRootDir .= "/";

$dfgOut = DFPrintoutStream::getInstance(DF_OUTPUT_FORMAT_TEXT);
$dfgOut->start(DF_OUTPUT_TARGET_STDOUT);

$cChecker = new ConsistencyChecker($mwRootDir);
$errorFound = $cChecker->checkDependencies(DF_OUTPUT_FORMAT_TEXT);
$statusLog = $cChecker->getStatusLog();
foreach($statusLog as $s) print $s;

if ($errorFound) {
	print "\n\nErrors found! See above.\n";
} else {
	print "\n\nOK.\n";
}
die($errorFound ? 1 : 0);
