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
           $requiredPHPVersions, $dfgRequiredFunctions;
    
    $messages = array();
	// run testfunctions
	foreach($dfgTestfunctions as $tf) {
		call_user_func($tf);
	}

	// check version
	$version = phpversion();
	$currentPHPVersion = new DFVersion($version);
	global $requiredPHPVersions;
	foreach($requiredPHPVersions as $v) {
		$versions[] = new DFVersion($v);
	}
	DFVersion::sortVersions($versions);
	$highestVersion = reset($versions);

	if ($currentPHPVersion->isLower($highestVersion)) {
		$messages[] = "You need at least PHP ".$highestVersion->toVersionString();
	}

	// check PHP extensions
	foreach($dfgRequiredExtensions as $php_ext => $msgs) {
		if (!extension_loaded($php_ext)) {
			foreach($msgs as $msg) {
				$messages[] = "$msg";
			}
		}
	}
	// check functions
	foreach($dfgRequiredFunctions as $php_ext => $msgs) {
		if (!function_exists($php_ext)) {
			foreach($msgs as $msg) {
				$messages[] = "$msg";
			}
		}
	}
    
	// create output
	$ok = true;
	if ($dfgRunFromCommandLine && $format == 'text') {
		$text = "\n-- PHP check --";

		if (count($messages) == 0) {
			$text .= "\n\nEverything is fine with the PHP installation.\n";
		} else {
			$text .= "\n\nThe following problems were found:";
			foreach($messages as $m) {
				$text .= "\n*[FAILED] $m";
			}
			$ok = false;
		}
	} else {
		$text = "<h1>PHP check</h1>";

		if (count($messages) == 0) {
			$text .= '<span class="df_check_ok">Everything is fine with the PHP installation.<span>';

		} else {
			$text .= '<span class="df_check_ok">The following problems were found:<span>';
			$text .= "<ul>";
			foreach($messages as $m) {
				$text .= "<li><span class=\"df_checkinst_error\">[FAILED]</span>$m</li>";
			}
			$text .= "</ul>";
			$ok = false;
		}
	}
	return "$text\n";
}
