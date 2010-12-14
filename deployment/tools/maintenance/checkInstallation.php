<?php
/*  Copyright 2010, ontoprise GmbH
 *
 *   The deployment tool is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The deployment tool is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @file
 * @ingroup DFMaintenance
 *
 * Checks installation for common problems.
 *
 * Usage:   php checkInstallation.php [ --onlydep | --help | --ext | --lift ]
 *
 * 	Process terminates with exit code 0 if all dependecies are fulfilled, otherwise 1.
 *
 * @author: Kai Kuehn / ontoprise / 2010
 */

global $rootDir;
$rootDir = dirname(__FILE__);
$rootDir = str_replace("\\", "/", $rootDir);
$rootDir = realpath($rootDir."/../../");

require_once($rootDir."/tools/maintenance/maintenanceTools.inc");


$mwRootDir = dirname(__FILE__);
$mwRootDir = str_replace("\\", "/", $mwRootDir);
$mwRootDir = realpath($mwRootDir."/../../..");

if (substr($mwRootDir, -1) != "/") $mwRootDir .= "/";

// parse parameters
array_shift($argv); // remove path
for( $arg = reset( $argv ); $arg !== false; $arg = next( $argv ) ) {


	if ($arg == '--ext') {
		$dfAddChecks = true;
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
	print "\n\nUsage";
	print "\n\t <no option> : Shows all common problems";
	print "\n\t --ext : Additional checks (requires working wiki in maintenance mode).";
	print "\n\t --lift : Tries to make the existing installation compatible to the deployment framework.";
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
    print "\nLift installation";
    $cChecker->liftInstallation();
    
	die();
}


if (isset($dfOnlydep)) {
	$errorFound = $cChecker->checkDependencies(DF_OUTPUT_FORMAT_TEXT);
} else {
	if (isset($dfAddChecks)) {
		$mediaWikiLocation = dirname(__FILE__) . '/../../..';
		require_once "$mediaWikiLocation/maintenance/commandLine.inc";

	}
	$errorFound = $cChecker->checkInstallation(DF_OUTPUT_FORMAT_TEXT, isset($dfAddChecks));
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