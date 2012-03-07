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
 * This script creates planned tasks for starting/stopping server tasks
 * in the WAT tool. Works only for Windows!
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

require_once($mwrootDir.'/deployment/tools/smwadmin/DF_Tools.php');


// parse parameters
$installServices = false;
$help = false;
$args = $_SERVER['argv'];
for( $arg = reset( $args ); $arg !== false; $arg = next( $args ) ) {
	if ($arg == '--services') {
		$installServices = true;
		continue;
	}
	if ($arg == '--help') {
		$help = true;
		continue;
	}
}

// usage
if ($help) {
	print "\nThis script creates planned tasks for starting/stopping server tasks";
	print "\nin the WAT tool. Works only for Windows!";
	print "\n\nUsage: php installServerTasks.php [ --services ]";
	print "\n\n\t--services: Install tasks for apache/mysql as service";
	print "\n";
	die();
}

// main program
try {
	
	// check env
	if (!Tools::isWindows()) throw new Exception("This script does not run on Linux");
	exec("fsutil", $output, $ret); // fsutil is only accessible as administrator
    if  ($ret != 0) throw new Exception("This script needs admin priviledges!");
    
	installServerTasks();
	echo "\n\nInstallation of tasks successful. Check in 'Planned tasks' tool of Windows.";
	
	if (!extension_loaded("curl")) {
		throw new Exception("Cannot update start scripts because PHP-curl module is missing!");
	}
	if (isSMWPlus($assumedSMWPlusDir)) {
	   downloadMissingScripts($assumedSMWPlusDir);
	   echo "\n\nInstallation of scripts successful.";
	}
} catch(Exception $e) {
	print "\n".$e->getMessage()."\n";
}

/**
 * Checks via heuristic if SMWPlus is installed.
 * 
 * @param & $assumedSMWPlusDir
 * 
 * @return boolean
 */
function isSMWPlus(& $assumedSMWPlusDir) {
	global $mwrootDir;
    $assumedSMWPlusDir = realpath($mwrootDir."/../..");
	return file_exists($assumedSMWPlusDir."\README-SMWPLUS.txt");
}

/**
 * Installs scheduled tasks on Windows
 *  
 */
function installServerTasks() {
	global $mwrootDir;
	
	$sched_tasks_dir = $mwrootDir."/deployment/tools/internal/scheduled_tasks";
	$handle = @opendir($sched_tasks_dir);
	if (!$handle) {
		throw new Exception("Could not find templates for scheduled tasks");
	}


	if (isSMWPlus($assumedSMWPlusDir)) {
		// SMW+
		$runCommandTemplate = '"'.$assumedSMWPlusDir.'\$1"';
	} else {
		// probably no SMW+ so create tasks and ass hint for user
		$runCommandTemplate = '"add here your start script"';
	}

	while ($entry = readdir($handle) ){
		if ($entry[0] == '.'){
			continue;
		}

			
		if (!is_file($sched_tasks_dir."/".$entry)) continue;
		$content = file_get_contents($sched_tasks_dir."/".$entry);
		$parts = explode("_",$entry);
		if (strpos($entry, "runas_") !== 0) continue;
		$op = $parts[2];
		$type = $parts[3];
		$id = $op.'_'.$type;
		list($id, $ext) = explode(".", $id);
		switch($id) {
			case 'start_apache':

				$runCommand = str_replace('$1', 'apache_start.bat', $runCommandTemplate);
				$activecontent = str_replace("{{command}}", $runCommand, $content);
				break;
			case 'stop_apache':

				$runCommand = str_replace('$1', 'apache_stop.bat', $runCommandTemplate);
				$activecontent = str_replace("{{command}}", $runCommand, $content);
				break;
			case 'start_mysql':
				$runCommand = str_replace('$1', 'mysql_start.bat', $runCommandTemplate);
				$activecontent = str_replace("{{command}}", $runCommand, $content);
				break;
			case 'stop_mysql':
				$runCommand = str_replace('$1', 'mysql_stop.bat', $runCommandTemplate);
				$activecontent = str_replace("{{command}}", $runCommand, $content);
				break;
			case 'start_solr':
				$runCommand = str_replace('$1', 'solr\wiki\StartSolr.bat', $runCommandTemplate);
				$activecontent = str_replace("{{command}}", $runCommand, $content);
				break;
			case 'stop_solr':
				$runCommand = str_replace('$1', 'solr\wiki\StopSolr.bat', $runCommandTemplate);
				$activecontent = str_replace("{{command}}", $runCommand, $content);
				break;
			case 'start_memcachedservice':
				$id = "start_memcached";
				$runCommand = str_replace('$1', 'memcached\memcached -d start', $runCommandTemplate);
				$activecontent = str_replace("{{command}}", $runCommand, $content);
				break;
			case 'stop_memcachedservice':
				$id = "stop_memcached";
				$runCommand = str_replace('$1', 'memcached\memcached -d stop', $runCommandTemplate);
				$activecontent = str_replace("{{command}}", $runCommand, $content);
				break;
			case 'start_apacheservice':
				if (!$installServices) {
					$activecontent = NULL;
					break;
				}
				$id="start_apache";
				$runCommand = str_replace('$1', 'restartapacheservice.bat', $runCommandTemplate);
				$activecontent = str_replace("{{command}}", $runCommand, $content);
				break;
			case 'start_mysqlservice':
				if (!$installServices) {
					$activecontent = NULL;
					break;
				}
				$id="start_mysql";
				$activecontent = str_replace("{{command}}", '"net start mysql"', $content);
				break;
			case 'stop_mysqlservice':
				if (!$installServices) {
					$activecontent = NULL;
					break;
				}
				$id="stop_mysql";
				$activecontent = str_replace("{{command}}", '"net stop mysql"', $content);
				break;
			default:
				$runCommand = NULL;
				$activecontent = NULL;
		}

		if (!is_null($activecontent)) {
		 // save task description as temporary file:
		 $tempfile = Tools::getTempDir()."/temp_".uniqid().".txt";
		 $tempFileHandle = fopen($tempfile, "w");
		 fwrite($tempFileHandle, $activecontent);
		 fclose($tempFileHandle);

		 // create new task (and delete old if necessary)
		 print "\nDelete task: $id";
		 exec("schtasks /delete /f /tn $id", $out, $ret);
		 print "\nCreate task: $id";
		 exec("schtasks /create /tn $id /XML \"$tempfile\"", $out, $ret);
		 if ($ret != 0) {
		 	print "\nError: ".implode("\n", $out);
		 }

		 // delete temp file
		 unlink($tempfile);

		}
	}
	@closedir($handle);
	return $activecontent;
}

/**
 * Downloads updated version of start/stop scripts
 * 
 * @param $smwplusDir
 */
function downloadMissingScripts($smwplusDir) {
	downloadFile("startSolr.bat", $smwplusDir."/solr/wiki/startSolr.bat");
	downloadFile("stopSolr.bat", $smwplusDir."/solr/wiki/stopSolr.bat");
	downloadFile("restartapacheservice.bat", $smwplusDir."/restartapacheservice.bat");
	downloadFile("apache_start.bat", $smwplusDir."/apache_start.bat");
	downloadFile("apache_stop.bat", $smwplusDir."/apache_stop.bat");
	downloadFile("mysql_start.bat", $smwplusDir."/mysql_start.bat");
	downloadFile("mysql_stop.bat", $smwplusDir."/mysql_stop.bat");
}

/**
 * Downloads a file from 
 * http://dailywikibuilds.ontoprise.com/repository156/misc
 * 
 * @param string $file File name
 * @param string $dest Absolute file path 
 * @throws Exception
 */
function downloadFile($file, $dest) {
	    $ch = curl_init("http://dailywikibuilds.ontoprise.com/repository/misc/batchscripts/$file");
        $fp = fopen($dest, "w");

        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_NOPROGRESS, false);
        
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);
}
