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
require_once('smwadmin/Installer.php');

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
		$packageToInstall[] = $package;
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
	if ($arg == '--dryRun') {
		$dryRun = true;
		continue;
	}
    if ($arg == '-f') {
        $force = true;
        continue;
    }
	$params[] = $arg;
}


$mediaWikiLocation = dirname(__FILE__) . '/../..';
require_once "$mediaWikiLocation/maintenance/commandLine.inc";

$help = array_key_exists("help", $options);

if ($help) {
	echo "\nSMWHalo Admin utility, Ontoprise 2009";
	echo "\n\nUsage: smwadmin [ -i | -d ] <package>[-<version>]";
	echo "\n       smwadmin -u [ <package>[-<version>] ]";
	echo "\n";
	echo "\n\t-i : Install";
	echo "\n\t-d : De-Install";
	echo "\n\t-u : Update";
	echo "\n";
	echo "\nExample: smwadmin -i smwhalo-1.4.4 -u smw-1.4.2";
	echo "\n\tsmwadmin -u";
	die();
}

$installer = new Installer(realpath(dirname(__FILE__)."/../.."), $dryRun, $force);

if ($globalUpdate) {
	$installer->updateAll();
	echo "\n\nYour installation is now up-to-date!";
	die();
}


foreach($packageToInstall as $toInstall) {
	$toInstall = str_replace(".", "", $toInstall);
	$parts = explode("-", $toInstall);
	try {
		$installer->installOrUpdate($parts[0], count($parts) > 1 ? $parts[1] : NULL);
	} catch(InstallationError $e) {
		fatalError($e);
	} catch(HttpError $e) {
        fatalError($e);
    }
}

foreach($packageToDeinstall as $toDeInstall) {
	$toDeInstall = str_replace(".", "", $toDeInstall);
	try {
		$installer->deinstall($toDeInstall);
	} catch(InstallationError $e) {
		fatalError($e);
	} catch(HttpError $e) {
        fatalError($e);
    }
}

foreach($packageToUpdate as $toUpdate) {
	$toInstall = str_replace(".", "", $toInstall);
	try {
		$installer->installOrUpdate($toInstall);
	} catch(InstallationError $e) {
		fatalError($e);
	}
}

print "\n\Operation successful.\n";

function fatalError($e) {
	switch($e->getErrorCode()) {
		case DEPLOY_FRAMEWORK_DEPENDENCY_EXIST: {
			$packages = $e->getArg1();
			print "\n".$e->getMsg();
			foreach($packages as $p) {
				print "\n\t$p";
			}
		  break;
		}
		default: echo "\nError: ".$e->getMsg(); break;
	}
	// stop installation
	die();
}


?>