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
 * Initializes the database for a HALO test scenario.
 *
 * @author: Kai K�hn
 */

$mw_dir = dirname(__FILE__) . '/../../';
$logDir = 'logs'; // log dir inside test directory which is given with -t

require_once('testFunctionsCmd.php');

# Process command line arguments
# Parse arguments

echo "Parse arguments...";
list($testDir, $xamppDir) = parseCmdParams('-t', '-x');

if ($testDir == null) {
	echo "\nTestdir missing. Use -t to set the extension's test directory.\n";
	die();
}

if (isWindows()) {
	if ($xamppDir == null) {
		echo "\nNo XAMPP dir specified. Use -x to set XAMPP dir.\n";
		die();
	}
	$phpExe = "\"$xamppDir/php/php.exe\"";
	$mysqlExe = "\"$xamppDir/mysql/bin/mysql.exe\"";
} else {
	$phpExe = exec('which php');
	$mysqlExe = exec('which mysql');
	if (strlen($phpExe) == 0 || strlen($mysqlExe) == 0) {
		echo "\nNo PHP or Mysql found at your system. Please install this software.\n";
		die();
	}
}

echo "\nInsertings LocalSettings.php ...";
copyLocalSettingsTest();
echo "\ndone!\n";

/* NOTE: These credentials MUST be set according to LocalSettingsForTest.php ! */
$wgDBuser="root";
$wgDBpassword="T9saG9MtwejYySj6";

echo "\nInitializing database for use with MW ...";
tstInitializeDatabase();
echo "\ndone!\n";
require_once( $mw_dir.'maintenance/commandLine.inc' );

echo "\nSetup required extensions ...\n";
checkSetupSteps();
echo "\ndone!\n";

echo "\nImporting wiki pages ...";
tstImportWikiPages();
echo "\ndone!\n";

echo "\nSetup log directory ...";
if (!is_dir($testDir.'/'.$logDir)) {
    echo runProcess('mkdir "'.$testDir.'/'.$logDir.'"');
    echo " created";
}
else echo " exists";
echo "\ndone!\n";

/**
 * Initializes the database (testdb) with an empty MW database
 * and runs the specified setup scripts.
 *
 */
function tstInitializeDatabase() {
	global $mw_dir, $testDir, $wgDBuser, $wgDBpassword, $wgDBname, $phpExe, $mysqlExe;

	// Import empty
	echo "\nImporting database...";
    echo "$mysqlExe -u $wgDBuser --password=$wgDBpassword --execute=\"DROP DATABASE IF EXISTS testdb; CREATE DATABASE testdb;\"";
    echo runProcess("$mysqlExe -u $wgDBuser --password=$wgDBpassword --execute=\"DROP DATABASE IF EXISTS testdb; CREATE DATABASE testdb;\"");
	echo "$mysqlExe -u $wgDBuser --password=$wgDBpassword testdb < \"$mw_dir"."tests/tests_halo/mw17_db.sql\"";
	echo runProcess("$mysqlExe -u $wgDBuser --password=$wgDBpassword testdb < \"$mw_dir"."tests/tests_halo/mw17_db.sql\"");
	echo "\ndone.\n";

   	echo "\nRun mediawiki update...";
	echo $phpExe.' "'.$mw_dir.'maintenance/update.php" --quick ';
	echo runProcess($phpExe.' "'.$mw_dir.'maintenance/update.php" --quick ');
	echo "\ndone.\n";

}

/**
 * Modify LocalSettings and run setup scripts to initialize the extension(s)
 */
function checkSetupSteps() {
    global $testDir;

    $localSettings = array();
    $runCfg= array();
    $steps= array();
    // check if there are several LocalSettings.php and runSetup.cfg
    if ($handle = opendir($testDir)) {
        while (false !== ($file = readdir($handle))) {
            if (strpos($file, "LocalSettings") === 0) {
                if ($file == "LocalSettings.php") {
                    $localSettings[0]= $file;
                    $steps[]= 0;
                }
                else if (preg_match('/^LocalSettings[_-]?(\d+)\.php$/', $file, $matches)) {
                    $localSettings[$matches[1]]= $file;
                    $steps[]= $matches[1];
                }
            }
            else if (strpos($file, "runSetup") === 0) {
                if ($file == "runSetup.cfg") {
                    $runCfg[0]= $file;
                    $steps[]= 0;
                }
                else if (preg_match('/^runSetup[_-]?(\d+)\.cfg$/', $file, $matches)) {
                    $runCfg[$matches[1]]= $file;
                    $steps[]= $matches[1];
                }
            }
        }
        closedir($handle);
    }

    // sort the steps if several LocalSettings and or runSetup.cfg are found
    $steps = array_unique($steps);
    sort($steps, SORT_NUMERIC);
    
    // for each possible step, check if there is a LocalSettings.php and or
    // a runSetup.cfg and then do the appropriate tasks.
    foreach ($steps as $step) {
        if (isset($localSettings[$step]))
            tstInsertLocalSettings($localSettings[$step]);
        else
            echo "No LocalSettings found for step $step\nSkip it.\n";
        if (isset($runCfg[$step]))
            tstRunSetupCfg($runCfg[$step]);
        else
            echo "No configuration file runSetup.cfg found for step $step in: $testDir\nSkip it.\n";
    }
}
/**
 * Imports wiki pages from the $testDir/pages directory. (wiki xml dumps)
 *
 */
function tstImportWikiPages() {
	global $mw_dir, $testDir, $phpExe;

	$pagesDir = $testDir."/pages";
	if(is_dir($pagesDir)) {
		$handle = @opendir($pagesDir);
		if (!$handle) {
			trigger_error("\nDirectory '$pagesDir' could not be opened.\n");
		}

		while ( ($entry = readdir($handle)) !== false ){
			if ($entry[0] == '.'){
				continue;
			}

			if (is_dir($pagesDir."/".$entry)) {
				// Unterverzeichnis
				sgagImportBots($pagesDir."/".$entry);

			} else{

				if (strpos($entry, ".xml") !== false) {
					echo "\nAdding: ".$entry;
					echo "\n".$phpExe." \"".$mw_dir."maintenance/importDump.php\" < \"".$pagesDir."/".$entry."\"";
					echo runProcess($phpExe." \"".$mw_dir."maintenance/importDump.php\" < \"".$pagesDir."/".$entry."\"");
				}
			}
		}
		closedir($handle);
	} else {
		echo "\nNo Directory '$pagesDir' existent.\nSkip import.\n";
	}

	echo "\nRun scripts after import...\n";
	if (file_exists($testDir."/runScriptAfterImport.cfg") && $handle = fopen($testDir."/runScriptAfterImport.cfg", "r")) {
		while(!feof($handle)) {
			$line = fgets($handle);
			$prgArg = explode("|", $line);
			$prg = $prgArg[0];
			$arg = count($prgArg) > 1 ? $prgArg[1] : "";
			$cmd = $phpExe." \"".$mw_dir."extensions/".$prg."\" $arg";
			$cmd = str_replace("\n", "", $cmd);
			$cmd = str_replace("\r", "", $cmd);
			echo "$cmd\n";
			echo runProcess($cmd);

		}
		fclose($handle);
	} else {
		echo "No configuration file runScriptAfterImport.cfg found in: $testDir\nSkip it.";
	}
}

/**
 * Inserts the LocalSettings.php from the $testdDir
 *
 * @param string $name of LocalSettings chunk that is added
 *               to global LocalSettings.php
 */
function tstInsertLocalSettings($name) {
	global $mw_dir, $testDir;

	// read old LocalSettings.php
	$lstest = trim(file_get_contents($mw_dir."LocalSettings.php"));
	$ls = trim(file_get_contents($testDir."/".$name));
	if (! preg_match('/^<\?/', $ls)) $ls = "<?\n".$ls;
	if (! preg_match('/\?>$/', $ls)) $ls .= "\n?>";

	// write new LocalSettings.php
	$handle = fopen($mw_dir."LocalSettings.php","wb");
	echo "\nAttach $name to global config at: ".$mw_dir."LocalSettings.php"."\n";
	fwrite($handle, $lstest.$ls);
	fclose($handle);

}

/**
 * Run the commands of a runSetup.cfg file from the test directory.
 *
 * @param string $name of the runSetup.cfg (without path)
 */
function tstRunSetupCfg($name) {
    global $testDir, $mw_dir, $phpExe;
    // run setups
	if (file_exists($testDir."/".$name) && $handle = fopen($testDir."/".$name, "r")) {
        echo "\nRun setup... $name\n";
		while(!feof($handle)) {
			$line = fgets($handle);
			$prgArg = explode("|", $line);
			$prg = array_shift($prgArg);
			$arg = count($prgArg) > 0 ? implode(' ', $prgArg) : "";
			$cmd = $phpExe." \"".$mw_dir."extensions/".$prg."\" $arg";
			$cmd = str_replace("\n", "", $cmd);
			$cmd = str_replace("\r", "", $cmd);
			echo "$cmd\n";
			echo runProcess($cmd);

		}
		fclose($handle);
	} else {
        echo "Error: specified file $testDir/$name not found\n";
    }
}

/**
 * Copy LocalSettingsForTest.php to the main directory of the
 * wiki installation.
 */
function copyLocalSettingsTest() {
	global $mw_dir, $testDir;

    echo "\nWrite in output file: ".$mw_dir."LocalSettings.php"."\n";
    $ok= copy("LocalSettingsForTest.php", $mw_dir."LocalSettings.php");
    if (! $ok)
        echo "Error copying LocalSettings.php\n";
}

?>