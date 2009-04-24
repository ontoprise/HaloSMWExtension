<?php
/**
 * Initializes the database for a HALO test scenario.
 *
 * @author: Kai K�hn
 */

$mw_dir = dirname(__FILE__) . '/../../';
require_once( $mw_dir.'maintenance/commandLine.inc' );

if ( isset( $_SERVER ) && array_key_exists( 'REQUEST_METHOD', $_SERVER ) ) {
	print "This script must be run from the command line\n";
	exit();
}


if( version_compare( PHP_VERSION, '5.0.0' ) < 0 ) {
	print "Sorry! This version of MediaWiki requires PHP 5; you are running " .
	PHP_VERSION . ".\n\n" .
        "If you are sure you already have PHP 5 installed, it may be " .
        "installed\n" .
        "in a different path from PHP 4. Check with your system administrator.\n";
	die( -1 );
}


# Process command line arguments
# Parse arguments

echo "Parse arguments...";

$params = array();
for( $arg = reset( $argv ); $arg !== false; $arg = next( $argv ) ) {
	//-b => BotID
	if ($arg == '-t') {
		$testDir = next($argv);
		continue;
	}
	if ($arg == '-x') {
		$xamppDir = next($argv);
		continue;
	}
	$params[] = $arg;
}

if (!isset($testDir)) {
	echo "\nTestdir missing. Use -t to set the extension's test directory.\n";
	die();
}

if (isWindows()) {
	if (!isset($xamppDir)) {
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
tstInsertLocalSettings($testDir);
echo "\ndone!\n";

echo "\nInitializing database for use with MW 1.13 ...";
tstInitializeDatabase();
echo "\ndone!\n";

echo "\nImporting wiki pages ...";
tstImportWikiPages();
echo "\ndone!\n";

/**
 * Initializes the database (testdb) with an empty MW database 
 * and runs the specified setup scripts.
 *
 */
function tstInitializeDatabase() {
	global $mw_dir, $testDir, $wgDBuser, $wgDBpassword, $phpExe, $mysqlExe;

	// Import empty
	echo "\nImporting database...";
	echo "$mysqlExe -u $wgDBuser --password=$wgDBpassword < \"$mw_dir"."tests/tests_halo/mw13_db.sql\"";
	//exec("\"$xamppDir/mysql/bin/mysql.exe\" -u $wgDBuser --password=$wgDBpassword < \"$mw_dir"."tests/tests_halo/mw13_db.sql\"");
	runProcess("$mysqlExe -u $wgDBuser --password=$wgDBpassword < \"$mw_dir"."tests/tests_halo/mw13_db.sql\"");
	echo "\ndone.\n";

	// run setups
	echo "\nRun setups...\n";
	$handle = fopen($testDir."/runSetup.cfg", "r");
	if ($handle) {
		while(!feof($handle)) {
			$line = fgets($handle);
			echo "$line";
			runProcess($phpExe." ".$mw_dir."extensions/".$line."\"");
		}
		fclose($handle);
	} else {
		echo "No configuration file runSetup.cfg found in: $testDir\nSkip it.";
	}
}

/**
 * Imports wiki pages from the $testDir/pages directory. (wiki xml dumps)
 *
 */
function tstImportWikiPages() {
	global $mw_dir, $testDir, $phpExe;

	$pagesDir = $testDir."/pages";
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
				runProcess($phpExe." \"".$mw_dir."maintenance/importDump.php\" < \"".$pagesDir."/".$entry."\"");
					
			}
		}
	}
	closedir($handle);
}

/**
 * Inserts the LocalSettings.php from the $testdDir
 *
 * @param string $testDir
 */
function tstInsertLocalSettings() {
	global $mw_dir, $testDir;

	// read old LocalSettings.php
	$handle_lstest = fopen("LocalSettingsForTest.php", "r");
	$handle_ls = fopen($testDir."/LocalSettings.php", "r");
	$contents_ls = fread ($handle_lstest, filesize ("LocalSettingsForTest.php"));
	$contents_lstest = fread ($handle_ls, filesize ($testDir."/LocalSettings.php"));


	$contents_ls = str_replace( "/*USERDEFINED*/", $contents_lstest, $contents_ls);
	fclose($handle_lstest);
	fclose($handle_ls);
	// write new LocalSettings.php
	$handle = fopen($mw_dir."LocalSettings.php","wb");
	echo "\nWrite in output file: ".$mw_dir."LocalSettings.php"."\n";
	fwrite($handle, $contents_ls);
	fclose($handle);

}

/**
 * Runs an external process synchronous. 
 *
 * @param string $runCommand
 */
function runProcess($runCommand) {
	if (isWindows()) {
		$keepConsoleAfterTermination = false;
		$runCommand = "\"$runCommand\"";
		$wshShell = new COM("WScript.Shell");
		$clOption = $keepConsoleAfterTermination ? "/K" : "/C";
		$runCommand = "cmd $clOption ".$runCommand;
		$oExec = $wshShell->Run($runCommand, 7, true);
	} else {
		exec("\"$runCommand\"");
	}
}

/**
 * Checks if the OS is Windows
 * returns true/false
 */
function isWindows() {
	static $thisBoxRunsWindows;
	
	if (! is_null($thisBoxRunsWindows)) return $thisBoxRunsWindows;
	
	ob_start();
	phpinfo();
	$info = ob_get_contents();
	ob_end_clean();
	//Get Systemstring
	preg_match('!\nSystem(.*?)\n!is',strip_tags($info),$ma);
	//Check if it consists 'windows' as string
	preg_match('/[Ww]indows/',$ma[1],$os);
	if($os[0]=='' && $os[0]==null ) {
		$thisBoxRunsWindows= false;
	} else {
		$thisBoxRunsWindows = true;
	}
	return $thisBoxRunsWindows;
}
?>