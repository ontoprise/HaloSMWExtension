<?php
/**
 * Initializes the database for a HALO test scenario.
 *
 * @author: Kai K�hn
 */

$mw_dir = dirname(__FILE__) . '/../../';

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
tstInsertLocalSettings($testDir);
echo "\ndone!\n";

require_once( $mw_dir.'maintenance/commandLine.inc' );
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
	global $mw_dir, $testDir, $wgDBuser, $wgDBpassword, $wgDBname, $phpExe, $mysqlExe;

	// Import empty
	echo "\nImporting database...";
	echo "$mysqlExe -u $wgDBuser --password=$wgDBpassword < \"$mw_dir"."tests/tests_halo/mw13_db.sql\"";
	//exec("\"$xamppDir/mysql/bin/mysql.exe\" -u $wgDBuser --password=$wgDBpassword < \"$mw_dir"."tests/tests_halo/mw13_db.sql\"");
	runProcess("$mysqlExe -u $wgDBuser --password=$wgDBpassword < \"$mw_dir"."tests/tests_halo/mw13_db.sql\"");
	echo "\ndone.\n";

	// run setups
	echo "\nRun setups...\n";
	if (file_exists($testDir."/runSetup.cfg") && $handle = fopen($testDir."/runSetup.cfg", "r")) {
		while(!feof($handle)) {
			$line = fgets($handle);
			$prgArg = explode("|", $line);
			$prg = $prgArg[0];
			$arg = count($prgArg) > 1 ? $prgArg[1] : "";
			$cmd = $phpExe." \"".$mw_dir."extensions/".$prg."\" $arg";
			$cmd = str_replace("\n", "", $cmd);
			$cmd = str_replace("\r", "", $cmd);
			echo "$cmd";
			runProcess($cmd);

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
	$lstest = trim(file_get_contents("LocalSettingsForTest.php"));
	$ls = trim(file_get_contents($testDir."/LocalSettings.php"));
	if (! preg_match('/^<\?/', $ls)) $ls = "<?\n".$ls;
	if (! preg_match('/\?>$/', $ls)) $ls .= "\n?>";

	// write new LocalSettings.php
	$handle = fopen($mw_dir."LocalSettings.php","wb");
	echo "\nWrite in output file: ".$mw_dir."LocalSettings.php"."\n";
	fwrite($handle, $lstest.$ls);
	fclose($handle);

}

?>