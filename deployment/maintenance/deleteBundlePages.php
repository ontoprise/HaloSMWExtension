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
 * Utility scripts for handling bundles.
 * 
 * This script removes pages of a bundle which are explicitly tagged 
 * as part of the given bundle.
 * 
 * Usage: php deleteBundle.php -b <bundlename>
 *
 * @author Kai Kühn
 *
 */

if ( isset( $_SERVER ) && array_key_exists( 'REQUEST_METHOD', $_SERVER ) ) {
    die( "This script must be run from the command line\n" );
}

global $rootDir;
$rootDir = dirname(__FILE__);
$rootDir = str_replace("\\", "/", $rootDir);
$rootDir = realpath($rootDir."/../");

$mwrootDir = dirname(__FILE__);
$mwrootDir = str_replace("\\", "/", $mwrootDir);
$mwrootDir = realpath($mwrootDir."/../../");

require_once($mwrootDir.'/deployment/io/DF_BundleTools.php');
require_once($mwrootDir.'/deployment/io/DF_Log.php');
require_once($mwrootDir.'/deployment/io/DF_PrintoutStream.php');

$mediaWikiLocation = dirname(__FILE__) . '/../..';
require_once "$mediaWikiLocation/maintenance/commandLine.inc";

$dfgOut = DFPrintoutStream::getInstance(DF_OUTPUT_FORMAT_TEXT);
$dfgOut->start(DF_OUTPUT_TARGET_STDOUT);
$dfgOut->setMode(DF_OUTPUT_FORMAT_TEXT);


$args = $_SERVER['argv'];
for( $arg = reset( $args ); $arg !== false; $arg = next( $args ) ) {
    if ($arg == '-b') {
        $bundleID = next($args);
        continue;
    } 
}

if (!isset($bundleID)) {
	print "\nUsage: php deleteBundle.php -b <bundlename>\n";
	die();
}

$dfgOut->outputln("Delete pages of bundle '$bundleID'");
DFBundleTools::deletePagesOfBundle($bundleID);

