<?php
/**
 * Installation tool.
 *
 * @author: Kai K�hn / ontoprise / 2009
 *
 *
 */
define('DEPLOY_FRAMEWORK_VERSION', 0.1);

require_once('smwadmin/DF_Tools.php');
require_once('smwadmin/DF_Installer.php');

if (array_key_exists('SERVER_NAME', $_SERVER) && $_SERVER['SERVER_NAME'] != NULL) {
	echo "Invalid access! A maintenance script MUST NOT accessed from remote.";
	return;
}

// check tools
$check = Tools::checkEnvironment();
if ($check !== true) {
	fatalError($check);
}

// check if the user is allowed to create files, directory. 
$check = Tools::checkPriviledges();
if ($check !== true) {
	fatalError($check);
}

# Attempt to connect to the database as a privileged user
# This will vomit up an error if there are permissions problems
$dbclass = 'Database' . ucfirst( $wgDBtype ) ;
$wgDatabase = new $dbclass( $wgDBserver, $wgDBadminuser, $wgDBadminpassword, $wgDBname, 1 );

if( !$wgDatabase->isOpen() ) {
    # Appears to have failed
    echo( "A connection to the database could not be established. Check the\n" );
    echo( "values of \$wgDBadminuser and \$wgDBadminpassword.\n" );
    exit();
}

$packageToInstall = array();
$packageToDeinstall = array();
$packageToUpdate = array();

// get command line parameters
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
		$pattern = next($argv);
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

	if ($arg == '--checkdump') { // => analyze installed dump
		$checkDump = true;
		$package = next($argv);
		if ($package === false) fatalError("No package found");
		$packageToInstall[] = $package;
		continue;
	}

	if ($arg == '-f') { // => force
		$force = true;
		continue;
	}

	if ($arg == '-r') { // => rollback last installation
		$restore = true;
		continue;
	}
	$params[] = $arg;
}


$mediaWikiLocation = dirname(__FILE__) . '/../..';
require_once "$mediaWikiLocation/maintenance/commandLine.inc";

// check if AdminSettings.php is available
if (!isset($wgDBadminuser) && !isset($wgDBadminpassword)) {
	fatalError("Please set create AdminSettings.php file. Otherwise rollback mechanism will not work properly.");
}

// create language object
$langClass = "DF_Language_$wgLanguageCode";
if (!file_exists("../languages/$langClass.php")) {
	$langClass = "DF_Language_En";
}
require_once("../languages/$langClass.php");
$dfgLang = new $langClass();

$help = array_key_exists("help", $options);
if ($help || count($argv) == 0) {
	showHelp();
	die();
}

$rootDir = realpath(dirname(__FILE__)."/../..");
$installer = Installer::getInstance($rootDir, $force);
$rollback = Rollback::getInstance($rootDir);
$res_installer = ResourceInstaller::getInstance($rootDir);

if ($restore) {
	handleRollback();
	die();
}

// Global update (ie. updates all packages to the latest possible version)
if ($globalUpdate) {
	handleGlobalUpdate($checkDep);
	die();
}

// List all available packages and show which are installed.
if ($listPackages) {
	$installer->listAvailablePackages($showDescription, $pattern);
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
		fatalError("Installation failed! You can try to rollback: smwadmin -r");
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
		fatalError("Installation failed! You can try to rollback: smwadmin -r");
	} catch(RepositoryError $e) {
		fatalError($e);
	}
}

print "\n\nOK.\n";

function showHelp() {
	echo "\nsmwhalo admin utility v".DEPLOY_FRAMEWORK_VERSION.", Ontoprise 2009";
	echo "\n\nUsage: smwadmin [ -i | -d ] <package>[-<version>]";
	echo "\n       smwadmin -u [ <package>[-<version>] ]";
	echo "\n";
	echo "\n\t-i <package>: Install";
	echo "\n\t-d <package> ]: De-Install";
	echo "\n\t-u <package>: Update";
	echo "\n\t--checkdump <package>: Check only dumps for changes but do not install.";
	echo "\n\t-l [ pattern ] : List installed packages.";
	echo "\n\t-r : Rollback last installation.";
	echo "\n\t--dep : Check only dependencies but do not install.";
	echo "\n";
	echo "\nExamples:\n\n\tsmwadmin -i smwhalo-1.4.4 -u smw-142: Installs the given packages";
	echo "\n\tsmwadmin -i smwhalo: Installs latest version of smwhalo";
	echo "\n\tsmwadmin -u: Updates complete installation";
	echo "\n\tsmwadmin -u --dep: Shows what would be updated.";
	echo "\n\tsmwadmin -d smw: Removes the package smw.";
	echo "\n\n";
	 
}

function handleRollback() {
	global $rollback;
	print "Rollback...";
	$rollback->rollback();
	 
}


function handleGlobalUpdate($checkDep) {
	global $installer;
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
	 
}

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