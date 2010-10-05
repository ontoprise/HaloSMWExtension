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
 * Checks if all dependencies of all extensions exist and that they have the correct version.
 *
 * Usage:   php checkRepository.php
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

$first = true;
for( $arg = reset( $argv ); $arg !== false; $arg = next( $argv ) ) {

			
	if ($arg == '--repair') {
		$repair = true;
		continue;
	} else if ($arg == '--help') {
		$help = true;
		continue;
	} else if ($arg == '--onlydep') {
		$onlydep = true;
		continue;
	} else if (!$first) {
		print "\n\nUnknown option: $arg\n";
		die();
	}
	$first = false;
}

if (isset($help)) {
	print "\n\nUsage";
	print "\n\t <no option> : Shows all common problems";
	print "\n\t --repair : Tries to repair common problems.";
	print "\n\t --onlydep : Checks only dependencies of deploy descriptors.";
	print "\n\n";
	die();
}

$cChecker = new ConsistencyChecker($mwRootDir);
if (isset($onlydep)) {
	$errorFound = $cChecker->checkDependencies($repair);
} else {
	$errorFound = $cChecker->checkInstallation(isset($repair));
}

if ($errorFound) {
 print "\n\nErrors found! See above.\n";
 } else {
 print "\n\nOK.\n";
 }
 die($errorFound ? 1 : 0);