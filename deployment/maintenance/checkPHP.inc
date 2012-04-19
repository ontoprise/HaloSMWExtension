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
 * Checks installation environment for required PHP version, extensions and
 * available PHP functions.
 *
 * Can be run via webrequest or on command line.
 *
 * @author Kai Kuehn
 *
 */


$dfgTestfunctions = array();
$dfgRequiredExtensions = array();
$dfgRequiredFunctions = array();

$mwrootDir = realpath(dirname(__FILE__)."/../../");
require_once "$mwrootDir/deployment/descriptor/DF_Version.php";
require_once "$mwrootDir/deployment/settings.php";
$handle = @opendir($mwrootDir."/extensions");
if (!$handle) {
	return;
}

// include checkForWAT.php files
while ($entry = readdir($handle) ){
	if ($entry[0] == '.'){
		continue;
	}

	if (is_dir("$mwrootDir/extensions/$entry")) {
		if (file_exists("$mwrootDir/extensions/$entry/checkForWAT.php")) {
			require_once("$mwrootDir/extensions/$entry/checkForWAT.php");
		}
	}
}

if (file_exists($mwrootDir."/deployment/checkForWAT.php")) {
	require_once($mwrootDir."/deployment/checkForWAT.php");
}

/**
 * Runs the PHP checks.
 *
 * @param string $format "text" or "html"
 */
function dffDoPHPChecks($format = 'text') {

	global $mwrootDir, $dfgTestfunctions, $dfgRunFromCommandLine, $dfgRequiredExtensions,
	$dfgRequiredPHPVersions, $dfgRequiredFunctions;

	$messages = array();
	$errorsOccured = false;
	// run testfunctions
	foreach($dfgTestfunctions as $tf) {
		call_user_func($tf);
	}

	// check version
	$version = phpversion();
if (strpos($version, "-") !== false) {
        list($version, $rest) = explode("-", $version);
    }
	$currentPHPVersion = new DFVersion($version);
	global $dfgRequiredPHPVersions;
	foreach($dfgRequiredPHPVersions as $v) {
		$versions[] = new DFVersion($v);
	}
	DFVersion::sortVersions($versions);
	$highestVersion = reset($versions);

	if ($currentPHPVersion->isLower($highestVersion)) {
		$messages[] = array("You need at least PHP ".$highestVersion->toVersionString(), true);
		$errorsOccured = true;
	} else {
		$messages[] = array("PHP version $version is OK.", false);
	}

	// check PHP extensions
	foreach($dfgRequiredExtensions as $php_ext => $msgs) {
		if (!extension_loaded($php_ext)) {
			$errorsOccured = true;
			foreach($msgs as $msg) {
				$messages[] = array("$msg", true);
			}
		} else {
			$messages[] = array("$php_ext is installed.", false);
		}
	}
	// check functions
	foreach($dfgRequiredFunctions as $php_ext => $msgs) {
		if (!function_exists($php_ext)) {
			$errorsOccured = true;
			foreach($msgs as $msg) {
				$messages[] = array("$msg", true);
			}
		} else {
			$messages[] = array("$php_ext is available.", false);
		}
	}

	usort($messages, "sortMessages");
	// create output
	$ok = true;
	if ($dfgRunFromCommandLine && $format == 'text') {
		$text = "\n-- PHP check for SMW+ --";
		$text .= "\nThis tool checks if your PHP installation is suitable for the set of installed extensions of SMW+.";
		if (!$errorsOccured) {
			$text .= "\n\nEverything is fine with the PHP installation.\n";
		} else {
			$text .= "\n\nThere a problems:";
		}
		foreach($messages as $message) {
			list($m, $isError) = $message;
			if ($isError) {
				$text .= "\n*[FAILED] $m";
			} else {
				$text .= "\n*$m";
			}
		}
		$ok = false;
	} else {
		$text = "<h1>PHP check for SMW+</h1>";
		$text .= "This tool checks if your PHP installation is suitable for the set of installed extensions of SMW+.";

		$text .= "<ul>";
	
		foreach($messages as $message) {
			list($m, $isError) = $message;
			if ($isError) {
				$text .= "<li><span style=\"color: red;margin-right: 10px;font-weight:bold\">[FAILED]</span>$m</li>";
			} else {
				$text .= "<li><span style=\"color: green;margin-right: 10px;font-weight:bold\">[OK]</span>$m</li>";
			}
		}
		$text .= "</ul>";
		$ok = false;
	}
	return "$text\n";
}

function sortMessages($message) {
	list($m, $isError) = $message;
	return $isError ? -1 : 1;
}
