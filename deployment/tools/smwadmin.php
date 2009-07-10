<?php
/**
 * Installation tool.
 *
 * @author: Kai Kühn / ontoprise / 2009
 *
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
		if ($package === false || $package == '--dep') {
			if ($package == '--dep') {
				$checkDep = true;
			}
			$globalUpdate = true;
			continue;
		}
		$packageToUpdate[] = $package;
		continue;
	}
	if ($arg == '-l') { // => list packages
		$listPackages = true;
		continue;
	}

	if ($arg == '-desc') { // => show description for each package
		$showDescription = true;
		continue;
	}

	if ($arg == '--dep') { // => show dependencies
		$checkDep = true;
		continue;
	}

	if ($arg == '--dump') { // => analyze installed dump
		$checkDump = true;
		continue;
	}

	if ($arg == '-f') { // => force
		$force = true;
		continue;
	}
	$params[] = $arg;
}


$mediaWikiLocation = dirname(__FILE__) . '/../..';
require_once "$mediaWikiLocation/maintenance/commandLine.inc";

$help = array_key_exists("help", $options);

if ($help || count($argv) == 0) {
	echo "\nsmwhalo admin utility v0.1, Ontoprise 2009";
	echo "\n\nUsage: smwadmin [ -i | -d ] <package>[-<version>]";
	echo "\n       smwadmin -u [ <package>[-<version>] ]";
	echo "\n";
	echo "\n\t-i : Install";
	echo "\n\t-d : De-Install";
	echo "\n\t-u : Update";
	echo "\n\t-l : List installed packages.";
	echo "\n\t--dep : Check only dependencies but do not install.";
	echo "\n\t--dump : Check only dumps for changes but do not install.";
	echo "\n";
	echo "\nExamples:\n\n\tsmwadmin -i smwhalo-1.4.4 -u smw-1.4.2: Installs the given packages";
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

// Global update (ie. updates all packages to the latest possible version)
if ($globalUpdate) {
	list($extensions_to_update, $updated) = $installer->updateAll($checkDep);
	if ($checkDep) {
		if (count($extensions_to_update) > 0) {

			print "\n\nThe following extensions would get updated:\n";
			foreach($extensions_to_update as $id => $etu) {
				list($desc, $min, $max) = $etu;
				print "\n\t*$id-".Tools::addVersionSeparators($min);
			}
		}
		print "\n\n";

	}

	if ($updated && count($extensions_to_update) > 0) {
		echo "\n\nYour installation is now up-to-date!\n";
	} else if (count($extensions_to_update) == 0) {
		echo "\n\nYour installation is already up-to-date!\n";
	}
	die();
}

// List all available packages and show which are installed.
if ($listPackages) {
	$installer->listAvailablePackages($showDescription);
	die();
}

// install
foreach($packageToInstall as $toInstall) {
	$toInstall = str_replace(".", "", $toInstall);
	$parts = explode("-", $toInstall);
	$packageID = $parts[0];
	$version = count($parts) > 1 ? $parts[1] : NULL;
	try {
		handleInstallOrUpdate($packageID, $version);
	} catch(InstallationError $e) {
		fatalError($e);
	} catch(HttpError $e) {
		fatalError($e);
	} catch(RepositoryError $e) {
		fatalError($e);
	} catch(RollbackInstallation $e) {
		$rollback->rollback();
	}
}

//de-install
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
	}catch(RepositoryError $e) {
		fatalError($e);
	}
}

// update
foreach($packageToUpdate as $toUpdate) {
	$toUpdate = str_replace(".", "", $toUpdate);
	$parts = explode("-", $toInstall);
	$packageID = $parts[0];
	$version = count($parts) > 1 ? $parts[1] : NULL;
	try {
		handleInstallOrUpdate($packageID, $version);
	} catch(InstallationError $e) {
		fatalError($e);
	} catch(HttpError $e) {
		fatalError($e);
	} catch(RollbackInstallation $e) {
		$rollback->rollback();
	} catch(RepositoryError $e) {
	 	fatalError($e);
	}
}

print "\n\nOK.\n";


function handleInstallOrUpdate($packageID, $version) {
	global $checkDump, $checkDep, $installer, $res_installer;
	if (isset($checkDump) && $checkDump == true) {
		// check status of a currently installed wikidump
		$res_installer->checkWikidump($packageID, $version);
		print "\n\n";

	} else if (isset($checkDep) && $checkDep == true) {

		// check dependencies of a package to install or update
		list($new_package, $old_package, $extensions_to_update) = $installer->checkDependencies($packageID, $version);
		print "\n\nThe following extensions would be installed:\n";
		foreach($extensions_to_update as $etu) {
			list($desc, $min, $max) = $etu;
			$id = $desc->getID();
			print "\n\t*$id-".Tools::addVersionSeparators($min);
		}
		print "\n\t*".$new_package->getID()."-".Tools::addVersionSeparators($new_package->getVersion());

		print "\n\n";
	} else {

		// install or update
		$installer->installOrUpdate($packageID, $version);
	}
}
/**
 * Shows a fatal error which aborts installation.
 *
 * @param Exception $e (InstallationError, HttpError, RollbackInstallation)
 */
function fatalError($e) {
	print "\n\n";

	if ($e instanceof InstallationError) {
		switch($e->getErrorCode()) {
			case DEPLOY_FRAMEWORK_DEPENDENCY_EXIST: {
				$packages = $e->getArg1();
				print $e->getMsg()."\n";
				foreach($packages as $p) {
					print "\n\t*$p";
				}
				break;
			}
			case DEPLOY_FRAMEWORK_ALREADY_INSTALLED:
				$package = $e->getArg1();
				print $e->getMsg()."\n";
				print "\n\t*".$package->getID()."-".$package->getVersion();
				break;
			case DEPLOY_FRAMEWORK_INSTALL_LOWER_VERSION:
			case DEPLOY_FRAMEWORK_NO_TMP_DIR:
			case DEPLOY_FRAMEWORK_COULD_NOT_FIND_UPDATE:
			case DEPLOY_FRAMEWORK_PACKAGE_NOT_EXISTS:
			case DEPLOY_FRAMEWORK_CODE_CHANGED:
			case DEPLOY_FRAMEWORK_MISSING_FILE:
			default: echo "Error: ".$e->getMsg(); break;
		}
	} else if ($e instanceof HttpError) {
		print $e->getMsg();
		//print $e->getHeader(); // for debugging
	} else if ($e instanceof RepositoryError) {
        print "\n".$e->getMsg();
    } else if (is_string($e)) {
		print "\n".$e;
	}
	print "\n\n";
	// stop installation
	die();
}


?>