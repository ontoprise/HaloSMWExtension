<?php
/**
 * Central configuration tool.
 *
 * @author: Kai Kühn
 *
 * Usage:
 *
 *  smwadmin -i <package>
 *  smwadmin -d <package>
 *  smwadmin -u [ <package> ]
 *
 */

require_once('smwadmin/Tools.php');

// check tools and rights
$check = Tools::checkEnvironment();
if ($check !== true) {
	fatalError($check);
}
$check = Tools::checkPriviledges();
if ($check !== true) {
    fatalError($check);
}



if (array_key_exists('SERVER_NAME', $_SERVER) && $_SERVER['SERVER_NAME'] != NULL) {
	echo "Invalid access! A maintenance script MUST NOT accessed from remote.";
	return;
}

$packageToInstall = array();
$packageToDeinstall = array();
$packageToUpdate = array();

// get parameters
for( $arg = reset( $argv ); $arg !== false; $arg = next( $argv ) ) {

	//-i => Install
	if ($arg == '-i') {
		$package = next($argv);
		if ($package === false) fatalError("No package found");
		$packageToInstall = next($argv);
		continue;
	} // -d => De-install
	if ($arg == '-d') {
		$package = next($argv);
		if ($package === false) fatalError("No package found");
		$packageToDeinstall[] = $package;
			
		continue;
	}
	if ($arg == '-u') { // u => update
		$package = next($argv);
		if ($package === false) {
			$globalUpdate = true;
			continue;
		}
		$packageToUpdate[] = $package;
		continue;
	}
	$params[] = $arg;
}


$mediaWikiLocation = dirname(__FILE__) . '/../..';
require_once "$mediaWikiLocation/maintenance/commandLine.inc";

$help = array_key_exists("help", $options);

if ($help) {
	echo "\nSMWAdmin utility, Ontoprise 2009";
	echo "\n\nUsage: smwadmin [ -i | -d ] <package>";
	echo "\n       smwadmin -u [ <package> ]";
	echo "\n";
	echo "\n\t-i : Install";
	echo "\n\t-d : De-Install";
	echo "\n\t-u : Update";
	echo "\n";
	die();
}

if ($globalUpdate) {
	doGlobalUpdate();
	echo "\n\nYour installation is now up-to-date!";
	die();
}

foreach($packageToInstall as $toInstall) {
	installPackage($toInstall);
}

foreach($packageToDeinstall as $toDeInstall) {
	deinstallPackage($toDeInstall);
}

foreach($packageToUpdate as $toUpdate) {
	updatePackage($toUpdate);
}

function fatalError($msg) {
	echo "\nError: $msg";
	die();
}


?>