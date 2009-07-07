<?php
/**
 * Central configuration tool.
 *
 * @author: Kai K�hn
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
		if ($package === false || $package == '-c') {
			if ($package == '-c') {
				$checkDep = true;
			}
			$globalUpdate = true;
			continue;
		}
		$packageToUpdate[] = $package;
		continue;
	}
	if ($arg == '-l') {
		$listPackages = true;
		continue;
	}

	if ($arg == '-lall') {
		$listAvailablePackages = true;
		continue;
	}

	if ($arg == '-desc') {
		$showDescription = true;
		continue;
	}

	if ($arg == '-c') {
		$checkDep = true;
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
	echo "\nsmwhalo admin utility, Ontoprise 2009";
	echo "\n\nUsage: smwadmin [ -i | -d ] <package>[-<version>]";
	echo "\n       smwadmin -u [ <package>[-<version>] ]";
	echo "\n";
	echo "\n\t-i : Install";
	echo "\n\t-d : De-Install";
	echo "\n\t-u : Update";
	echo "\n\t-c : Check only dependencies but do not install.";
	echo "\n\t-l : List installed packages.";
	echo "\n\t-lall : List all available packages.";
	echo "\n";
	echo "\nExamples:\n\tsmwadmin -i smwhalo-1.4.4 -u smw-1.4.2: Installs the given packages";
	echo "\n\tsmwadmin -i smwhalo: Installs latest version of smwhalo";
	echo "\n\tsmwadmin -u: Updates complete installation";
	echo "\n\tsmwadmin -u -c: Shows what would be updated.";
	echo "\n\tsmwadmin -d smw: Removes the package smw.";
	echo "\n\n";
	die();
}

$rootDir = realpath(dirname(__FILE__)."/../..");
$installer = Installer::getInstance($rootDir, $force);
$rollback = Rollback::getInstance($rootDir);
$res_installer = ResourceInstaller::getInstance($rootDir);

if ($globalUpdate) {
	$updated = $installer->updateAll($checkDep);
	if ($checkDep) die();
	if ($updated) {
		echo "\n\nYour installation is now up-to-date!\n";
	} else {
		echo "\n\nYour installation is already up-to-date!\n";
	}
	die();
}

if ($listPackages) {
	$installer->listPackages();
	die();
}

if ($listAvailablePackages) {
	$installer->listAvailablePackages($showDescription);
	die();
}


foreach($packageToInstall as $toInstall) {
	$toInstall = str_replace(".", "", $toInstall);
	$parts = explode("-", $toInstall);
	$packageID = $parts[0];
	$version = count($parts) > 1 ? $parts[1] : NULL;
	try {
		if ($checkDep) {
			list($new_package, $old_package, $extensions_to_update) = $installer->checkDependencies($packageID, $version);
				
			print "\n\nThe following extensions would be installed:\n";
			foreach($extensions_to_update as $id => $etu) {
				list($desc, $min, $max) = $etu;
				print "\n\t*$id-".Tools::addVersionSeparators($min);
			}
			print "\n\t*".$new_package->getID()."-".Tools::addVersionSeparators($new_package->getVersion());

			$res_installer->checkWikidump($packageID, $version);

			print "\n\n";
		} else {
			$installer->installOrUpdate($parts[0], count($parts) > 1 ? $parts[1] : NULL);
		}
	} catch(InstallationError $e) {
		fatalError($e);
	} catch(HttpError $e) {
		fatalError($e);
	} catch(RollbackInstallation $e) {
		$rollback->rollback();
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
	} catch(RollbackInstallation $e) {
		// currently not supported
	}
}

foreach($packageToUpdate as $toUpdate) {
	$toUpdate = str_replace(".", "", $toUpdate);
	$parts = explode("-", $toUpdate);
	try {
		if ($checkDep) {
			$installer->checkDependencies($parts[0], count($parts) > 1 ? $parts[1] : NULL);
		} else {
			$installer->installOrUpdate($parts[0], count($parts) > 1 ? $parts[1] : NULL);
		}
	} catch(InstallationError $e) {
		fatalError($e);
	} catch(HttpError $e) {
		fatalError($e);
	} catch(RollbackInstallation $e) {
		$rollback->rollback();
	}
}

print "\n\nOK.\n";

/**
 * Shows a fatal error which aborts installation.
 *
 * @param Exception $e (InstallationError, HttpError, RollbackInstallation)
 */
function fatalError($e) {
	switch($e->getErrorCode()) {
		case DEPLOY_FRAMEWORK_DEPENDENCY_EXIST: {
			$packages = $e->getArg1();
			print "\n".$e->getMsg()."\n";
			foreach($packages as $p) {
				print "\n\t*$p";
			}
			break;
		}
		case DEPLOY_FRAMEWORK_ALREADY_INSTALLED:
			$package = $e->getArg1();
			print "\n".$e->getMsg()."\n";
			print "\n\t*".$package->getID()."-".$package->getVersion();
			break;
		case DEPLOY_FRAMEWORK_INSTALL_LOWER_VERSION:
		case DEPLOY_FRAMEWORK_NO_TMP_DIR:
		case DEPLOY_FRAMEWORK_COULD_NOT_FIND_UPDATE:
		case DEPLOY_FRAMEWORK_PACKAGE_NOT_EXISTS:
		case DEPLOY_FRAMEWORK_CODE_CHANGED:
		case DEPLOY_FRAMEWORK_MISSING_FILE:
		default: echo "\nError: ".$e->getMsg(); break;
	}
	print "\n\n";
	// stop installation
	die();
}


?>