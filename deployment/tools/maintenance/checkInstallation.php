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
 * Checks installation for common problems.
 *
 * Usage:   php checkInstallation.php [ --onlydep | --nowiki | --lift | --help ]
 * 
 *  --onlydep: Show only dependency issues
 *  --nowiki: No wiki is initialized
 *  --lift: Lift installation to be able to work with DF.
 *  --help: Show help
 *
 * 	Process terminates with exit code 0 if no errors are found, otherwise 1.
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

$mwrootDir = dirname(__FILE__);
$mwrootDir = str_replace("\\", "/", $mwrootDir);
$mwrootDir = realpath($mwrootDir."/../../../");

if(file_exists($rootDir.'/settings.php'))
{
    require_once($rootDir.'/settings.php');
}

require_once($rootDir."/io/DF_PrintoutStream.php");
$dfgOut = DFPrintoutStream::getInstance(DF_OUTPUT_FORMAT_TEXT);
$dfgOut->start(DF_OUTPUT_TARGET_STDOUT);

require_once($rootDir."/tools/maintenance/maintenanceTools.inc");
require_once($rootDir.'/languages/DF_Language.php');

dffInitLanguage();

$mwRootDir = dirname(__FILE__);
$mwRootDir = str_replace("\\", "/", $mwRootDir);
$mwRootDir = realpath($mwRootDir."/../../..");

if (substr($mwRootDir, -1) != "/") $mwRootDir .= "/";

// parse parameters
array_shift($argv); // remove path
for( $arg = reset( $argv ); $arg !== false; $arg = next( $argv ) ) {


	if ($arg == '--nowiki') {
		$dfNoWiki = true;
		continue;
	} else if ($arg == '--lift') {
		$dfLift = true;
		continue;
	} else if ($arg == '--help') {
		$dfHelp = true;
		continue;
	} else if ($arg == '--onlydep') {
		$dfOnlydep = true;
		continue;
	} else {
		print "\n\nUnknown option: $arg\n";
		die();
	}

}

// show help
if (isset($dfHelp)) {
	print "\nUsage";
	print "\n\t <no option> : Shows all common problems";
	print "\n\t --nowiki : Do not initialize the wiki (less checks if wiki is broken).";
	print "\n\t --lift : Tries to make the existing installation compatible to the Wiki Administration Tool.";
	print "\n\t --onlydep : Checks only dependencies of deploy descriptors.";
	print "\n\n";
	die();
}

// initialize checker tool
$cChecker = new ConsistencyChecker($mwRootDir);

// lift installation
if (isset($dfLift)) {
	$mediaWikiLocation = dirname(__FILE__) . '/../../..';
	require_once "$mediaWikiLocation/maintenance/commandLine.inc";
	$cChecker->liftInstallation();
	$statusLog = $cChecker->getStatusLog();
	foreach($statusLog as $s) print $s;
	die();
}

// normal checking
if (isset($dfOnlydep)) {
	$errorFound = $cChecker->checkDependencies(DF_OUTPUT_FORMAT_TEXT);
} else {
	if (!isset($dfNoWiki)) {
		$mediaWikiLocation = dirname(__FILE__) . '/../../..';
		require_once "$mediaWikiLocation/maintenance/commandLine.inc";

	}
	$errorFound = $cChecker->checkInstallation(DF_OUTPUT_FORMAT_TEXT, !isset($dfNoWiki));
}

// show log
$statusLog = $cChecker->getStatusLog();
foreach($statusLog as $s) print $s;

// show error message
if ($errorFound) {
	print "\n\nErrors found! See above.\n";
} else {
	print "\n\nOK.\n";
}

// die with exit code != 0 if error
die($errorFound ? 1 : 0);
