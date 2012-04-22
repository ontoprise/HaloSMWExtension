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
 * @ingroup SemanticGardeningMaintenance
 *
 * @defgroup SemanticGardeningMaintenance
 * @ingroup SemanticGardening
 *
 * Setup database for Semantic Gardening extension.
 *
 * @author: Kai K�hn
 *
 * Created on: 14.03.2009
 */

if (array_key_exists('SERVER_NAME', $_SERVER) && $_SERVER['SERVER_NAME'] != NULL) {
	echo "Invalid access! A maintenance script MUST NOT accessed from remote.";
	return;
}

$mediaWikiLocation = dirname(__FILE__) . '/../../..';
require_once "$mediaWikiLocation/maintenance/commandLine.inc";
$sgagIP = "$mediaWikiLocation/extensions/SemanticGardening";

$help = array_key_exists("help", $options);
$onlyTables = array_key_exists("onlytables", $options);
$predefpages = array_key_exists("predefpages", $options);
$delete = array_key_exists("delete", $options);
$update = array_key_exists("update", $options);

if ($help) {
	echo "\nUsage: php SGA_setup.php [ --onlytables ] [ --predefpages ] [ --delete ]\n";
	echo "Started with no parameters installs database tables as well as predefined pages.";
	die();
}
if ($onlyTables) {
	sgafInitializeTables();

}

if ($predefpages) {
	global $sgagIP;
	require_once("$sgagIP/includes/SGA_GardeningInitialize.php");
	require_once("$sgagIP/specials/Gardening/SGA_Gardening.php");
	SGAGardeningLog::getGardeningLogAccess()->createPredefinedPages(true);
}

if ($update) {
	global $sgagIP;
	require_once("$sgagIP/includes/SGA_GardeningInitialize.php");
	require_once("$sgagIP/specials/Gardening/SGA_Gardening.php");
	if (file_exists("$sgagIP/includes/bots/SGA_ExportObjectLogicBot.php")) {
		unlink("$sgagIP/includes/bots/SGA_ExportObjectLogicBot.php");
		echo "\nSGA_ImportOntologyBot.php removed.";
	}
	if (file_exists("$sgagIP/includes/bots/SGA_ImportOntologyBot.php")) {
		unlink("$sgagIP/includes/bots/SGA_ImportOntologyBot.php");
		echo "\nSGA_ImportOntologyBot.php removed.";
	}
	echo "\nThe Semantic Gardening has been successfully updated.\n";
}

if ($delete) {
	global $sgagIP;
	require_once("$sgagIP/includes/SGA_GardeningInitialize.php");
	require_once("$sgagIP/specials/Gardening/SGA_Gardening.php");
	SGAGardeningIssuesAccess::getGardeningIssuesAccess()->drop(true);
	SGAGardeningLog::getGardeningLogAccess()->drop(true);
	echo "\nThe Semantic Gardening has been successfully removed.\n";
}

if (!$onlyTables && !$predefpages && !$delete && !$update) {
	sgafInitializeTables();
	SGAGardeningLog::getGardeningLogAccess()->createPredefinedPages(true);
	echo "\nThe Semantic Gardening has been successfully installed.\n";
}

/**
 * Registers a "planned task" (on Windows) which calls SGA_periodicExecutor.php periodically.
 * Linux users are prompted to register a cron job.
 * 
 */
function registerTask() {
	global $phpInterpreter, $sgagIP;
	if (isWindows()) {
		$command = $phpInterpreter;
		$executorPHP = realpath("$sgagIP/maintenance/SGA_periodicExecutor.php");
		
		$now = date("Y-m-d"); 
		$content = file_get_contents(realpath("$sgagIP/maintenance/resources/periodic_execution.txt"));
		$content = str_replace("{{command}}", "\"$phpInterpreter\"", $content);
		$content = str_replace("{{argument}}", "\"$executorPHP\"", $content);
		$content = str_replace("{{date}}", $now, $content);
		
		$tempfile = getTempDir()."/temp_".uniqid().".txt";
		$tempFileHandle = fopen($tempfile, "w");
		fwrite($tempFileHandle, $content);
		fclose($tempFileHandle);
		print "\nDelete task: smwplus_periodic_executor";
		exec("schtasks /delete /f /tn smwplus_periodic_executor", $out, $ret);
		print "\nCreate task: smwplus_periodic_executor";
		exec("schtasks /create /tn smwplus_periodic_executor /XML \"$tempfile\"", $out, $ret);
	} else {
		print "If you want to use the periodic execution feature for Gardening bots, then please add the following line to your /etc/crontab file. Replace \$WIKIPATH by the appropriate path of course.";
		print "\n\n----------------------------------------------------------------------------------------------------";
		print "*\n5 * * * *   root    php \$WIKIPATH/extensions/SemanticGardening/maintenance/SGA_periodicExecutor.php > /dev/null 2>&1\"\n\n";
		print "\n----------------------------------------------------------------------------------------------------\n\n";
	}
}




function sgafInitializeTables() {

	global $sgagIP;
	require_once("$sgagIP/includes/SGA_GardeningInitialize.php");
	require_once("$sgagIP/specials/Gardening/SGA_Gardening.php");
	require_once("$sgagIP/includes/SGA_PeriodicExecutors.php");

	SGAGardeningIssuesAccess::getGardeningIssuesAccess()->setup(true);
	SGAGardeningLog::getGardeningLogAccess()->setup(true);
	SGAPeriodicExecutors::getPeriodicExecutors()->setup(true);
	
	if (isWindows()) {
	   exec("fsutil", $output, $ret);
	   if ($ret == 0) {
            registerTask();	   	
	   } else {
	   	   print "\n\n----------------------------------------------------------------------------------------------------";
	   	   print "\nYou must run this script with admin right to enable the Periodic Bot execution feature.";
	   	   print "\n----------------------------------------------------------------------------------------------------\n\n";
	   }
	} else {
		registerTask();
	}
	

	return true;
}

function isWindows(& $version = '') {
	static $thisBoxRunsWindows;
	static $os;

	if (! is_null($thisBoxRunsWindows)) {
		$version = $os;
		return $thisBoxRunsWindows;
	}

	ob_start();
	@phpinfo();
	$info = ob_get_contents();
	ob_end_clean();
	//Get Systemstring
	preg_match('!\nSystem(.*?)\n!is',strip_tags($info),$ma);
	//Check if it consists 'windows' as string
	preg_match('/[Ww]indows.*/',$ma[1],$os);
	$thisBoxRunsWindows= count($os) > 0;


	if ($thisBoxRunsWindows && (strpos($os[0], "6.1") !== false)) {
		$version = "Windows 7";
		$os = $version;
	} else if ($thisBoxRunsWindows) {
		$version = "Windows XP";
		$os = $version;
	}

	return $thisBoxRunsWindows;
}

function getTempDir() {
	if (isWindows()) {
		exec("echo %TEMP%", $out, $ret);
		$tmpdir = str_replace("\\", "/", reset($out));
		if (empty($tmpdir)) return 'c:\temp'; // fallback
		$parts = explode(";", $tmpdir);
		$tmpdir = reset($parts);
		return trim($tmpdir);
	} else {
		@exec('echo $TMPDIR', $out, $ret);
		if ($ret == 0) {
			$val = reset($out);
			$tmpdir = ($val == '' || $val === false) ? '/tmp' : $val;
			if (empty($tmpdir)) return '/tmp'; // fallback
			$parts = explode(":", $tmpdir);
			$tmpdir = reset($parts);
			return trim($tmpdir);
		} else {
			return '/tmp'; // fallback if echo fails for some reason
		}
	}
}
