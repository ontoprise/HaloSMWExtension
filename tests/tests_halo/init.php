<?php
/**
 * Initializes a empty MW database with the dumpfile(s) given.
 *
 *
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
	echo "\nTestdir missing. Use -t to set dump file.\n";
	die();
}

if (!isset($xamppDir)) {
	echo "\nNo XAMPP dir specified. Use -m to set MySQL dir.\n";
	die();
}

echo "\nInsertings LocalSettings.php ...";
tstInsertLocalSettings($testDir);
echo "done!\n";

echo "\nInitializing database for use with MW 1.13 ...";
tstInitializeDatabase();
echo "\ndone!\n";

echo "\nImporting wiki pages ...";
tstImportWikiPages();
echo "\ndone!\n";


function tstInitializeDatabase() {
	global $mw_dir, $xamppDir, $testDir, $wgDBuser, $wgDBpassword;

	// Import empty
	echo "\nImporting database...";
	echo "$xamppDir/mysql/bin/mysql.exe -u $wgDBuser --password=$wgDBpassword < $mw_dir"."tests/tests_halo/mw13_db.sql";
	exec("$xamppDir/mysql/bin/mysql.exe -u $wgDBuser --password=$wgDBpassword < $mw_dir"."tests/tests_halo/mw13_db.sql");
    echo "done.\n";
    
	// run setups
	echo "\nRun setups...\n";
	$handle = fopen($testDir."/runSetup.cfg", "r");
	while(!feof($handle)) {
		$line = fgets($handle);
		echo "$line";
		exec("$xamppDir/php/php.exe ".$mw_dir."extensions/".$line);
	}
	fclose($handle);
}

function tstImportWikiPages() {
	global $mw_dir, $xamppDir, $testDir;

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

			if (strpos($botDir.$entry, ".xml") !== false) {
				echo "\nAdding: ".$entry;
				exec("$xamppDir/php/php.exe ".$mw_dir."maintenance/importDump.php < ".$pagesDir."/".$entry);
					
			}
		}
	}
	closedir($handle);
}

function tstInsertLocalSettings($testDir) {
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
?>