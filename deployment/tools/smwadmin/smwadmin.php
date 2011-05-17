<?php

/*  Copyright 2009, ontoprise GmbH
 *
 *   The deployment tool is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The deployment tool is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @file
 * @ingroup DeployFramework
 *
 * @defgroup DeployFramework Deploy Framework
 * @ingroup DeployFramework
 *
 * Installation tool.
 *
 * @author: Kai K�hn / ontoprise / 2009
 *
 *
 */
define('DEPLOY_FRAMEWORK_VERSION', '{{$VERSION}} [B{{$BUILD_NUMBER}}]');

// termination constants
define('DF_TERMINATION_WITH_FINALIZE', 0);
define('DF_TERMINATION_ERROR', 1);
define('DF_TERMINATION_WITHOUT_FINALIZE', 2);

// 3 modes for handling ontology import conflicts.
define('DF_ONTOLOGYIMPORT_ASKINTERACTIVELY', 0);
define('DF_ONTOLOGYIMPORT_STOPONCONFLICT', 1);
define('DF_ONTOLOGYIMPORT_FORCEOVERWRITE', 2);

global $rootDir;
$rootDir = dirname(__FILE__);
$rootDir = str_replace("\\", "/", $rootDir);
$rootDir = realpath($rootDir."/../../");

$mwrootDir = dirname(__FILE__);
$mwrootDir = str_replace("\\", "/", $mwrootDir);
$mwrootDir = realpath($mwrootDir."/../../../");

require_once('DF_Tools.php');
require_once('DF_Installer.php');
require_once($mwrootDir.'/deployment/io/DF_Log.php');
require_once($mwrootDir.'/deployment/io/DF_PrintoutStream.php');

// output format of smwadmin as a console app is text
initializeLanguage();
$dfgPrintStream = DFPrintoutStream::getInstance(DF_OUTPUT_FORMAT_TEXT);

//Load Settings
if(file_exists($rootDir.'/settings.php'))
{
	require_once($rootDir.'/settings.php');
}

// check PHP version
$phpver = str_replace(".","",phpversion());
if ($phpver < 520) {
	$dfgPrintStream->output("\nPHP version must be >= 5.2\n", DF_PRINTSTREAM_TYPE_ERROR) ;
	die(DF_TERMINATION_ERROR);
}

if (array_key_exists('SERVER_NAME', $_SERVER) && $_SERVER['SERVER_NAME'] != NULL) {
	$dfgPrintStream->output("Invalid access! A maintenance script MUST NOT be accessed from remote.", DF_PRINTSTREAM_TYPE_ERROR);
	die(DF_TERMINATION_ERROR);
}

// check if the user is allowed to create files, directory.
if (!in_array("--nocheck", $_SERVER['argv'])) {
	$check = Tools::checkPriviledges();
	if ($check !== true) {
		fatalError($check);
	}


	// check required tools
	$check = Tools::checkEnvironment();
	if ($check !== true) {
		fatalError($check);
	}


	// check if LocalSettings.php is writeable

	@$success = touch("$rootDir/../LocalSettings.php");
	if ($success === false) {
		fatalError("LocalSettings.php is not accessible. Missing rights or file locked?");
	}
}

$packageToInstall = array();
$packageToDeinstall = array();
$packageToUpdate = array();

// ontologies as local files (full or relative path)
$ontologiesToInstall = array();

// local bundles to install
$localBundlesToInstall = array();

// defaults:
$dfgForce = false;
$dfgGlobalUpdate= false;
$dfgShowDescription=false;
$dfgListPackages=false;
$dfgCheckDep=false;
$dfgRestore=false;
$dfgCheckInst=false;
$dfgInstallPackages=false;
$dfgRestoreList=false;
$dfgCreateRestorePoint=false;
$dfgNoConflict=false;

$args = $_SERVER['argv'];
array_shift($args); // remove script name
if (count($args) === 0) {
	showHelp();
	die(DF_TERMINATION_WITHOUT_FINALIZE);
}

// get command line parameters
for( $arg = reset( $args ); $arg !== false; $arg = next( $args ) ) {
	if ($arg == '--help') {
		showHelp();
		die(DF_TERMINATION_WITHOUT_FINALIZE);
	} else
	//-i => Install
	if ($arg == '-i') {
		$package = next($args);
		if ($package === false) fatalError("No package found");

		if (file_exists($package)) {
			$file_ext = reset(array_reverse(explode(".", $package)));
			if ($file_ext == 'owl' || $file_ext == 'rdf' || $file_ext == 'obl') {
				// import ontology
				$ontologiesToInstall[] = $package;
			} else if ($file_ext == 'zip') {
				// import bundle
				$localBundlesToInstall[] = $package;
			}

		} else {
			// assume it is a package
			$packageToInstall[] = $package;
		}
		continue;
	} else if ($arg == '-d') { // -d => De-install
		$package = next($args);
		if ($package === false) fatalError("No package found");
		$packageToDeinstall[] = $package;
			
		continue;
	} else if ($arg == '-u') { // u => update
		$package = next($args);
		if ($package === false || $package == '--dep') {
			if ($package == '--dep') {
				$dfgCheckDep = true;
			}
			$dfgGlobalUpdate = true;
			continue;
		}
		$packageToUpdate[] = $package;
		continue;
	} else if ($arg == '-l') { // => list packages
		$dfgListPackages = true;
		$pattern = next($args);
		continue;
	} else if ($arg == '--desc') { // => show description for each package
		$dfgShowDescription = true;
		continue;
	} else if ($arg == '--dep') { // => show dependencies
		$dfgCheckDep = true;
		continue;
	} else if ($arg == '--checkdump') { // => analyze installed dump
		$checkDump = true;
		$package = next($args);
		if ($package === false) fatalError("No package found");
		$packageToInstall[] = $package;
		continue;
	} else if ($arg == '--finalize') { // => finalize installation, ie. run scripts, import pages

		$dfgInstallPackages = true;
		continue;
	} else if ($arg == '-f') { // => force
		$dfgForce = true;
		continue;
	} else if ($arg == '-r') { // => rollback last installation
		$dfgRestorePoint = next($args);
		$dfgRestore = true;
		continue;
	} else if ($arg == '--rlist') {
		$dfgRestoreList = true;
		continue;
	} else if ($arg == '--rcreate') {
		$dfgCreateRestorePoint = true;
		$dfgRestorePoint = next($args);
		continue;
	} else if ($arg == '--noconflict') {
		$dfgNoConflict = true;
		continue;
	} else if ($arg == '--nocheck') {
		// ignore
		continue;
	} else {
		$dfgPrintStream->outputln("\nUnknown command: $arg. Try --help\n\n", DF_PRINTSTREAM_TYPE_ERROR);
		die(DF_TERMINATION_ERROR);
	}
	$params[] = $arg;
}

if ($dfgForce && $dfgNoConflict) {
	$dfgPrintStream->outputln("\nWARNING: -f and --noconflict are incompatible options. --noconflict is IGNORED.", DF_PRINTSTREAM_TYPE_WARN);
}

$logger = Logger::getInstance();
$installer = Installer::getInstance($mwrootDir, $dfgForce);
$rollback = Rollback::getInstance($mwrootDir);

if ($dfgInstallPackages) {
	// include commandLine.inc to be in maintenance mode
	$mediaWikiLocation = dirname(__FILE__) . '/../../..';
	require_once "$mediaWikiLocation/maintenance/commandLine.inc";

	initializeLanguage();
	// include the resource installer
	require_once('DF_ResourceInstaller.php');

	// finalize mode requires a wiki environment, so check and include a few things more
	checkWikiContext();

	$logger->info("Start initializing packages");
	$installer->initializePackages();
	$logger->info("End initializing packages");
	die(DF_TERMINATION_WITHOUT_FINALIZE);  // 2 is normal termination but no further action
} else {
	// check for non-initialized extensions
	$localPackages = PackageRepository::getLocalPackagesToInitialize($mwrootDir);
	if (count($localPackages) > 0) {
		$dfgPrintStream->outputln("\nThere are non-initialized extensions. Run: smwadmin --finalize\n");
		die(DF_TERMINATION_ERROR);
	}
}

if ($dfgRestore) {
	$dfgPrintStream->outputln("\nThis operation will restore the wiki from the last restore point.");
	$dfgPrintStream->outputln("\nThat means the Mediawiki installation is overwritten as well as the");
	$dfgPrintStream->outputln("\ndatabase content!\n\n Do you really want to continue (y/n)? ");
	$line = trim(fgets(STDIN));
	$result = strtolower($line);
	if ($result === 'y') {
		$restorePoints = $rollback->getAllRestorePoints();
		if (count($restorePoints) === 0) {
			$dfgPrintStream->outputln("Nothing to restore.");
			$dfgPrintStream->outputln();
			die(DF_TERMINATION_WITHOUT_FINALIZE);
		}
		if ($dfgRestorePoint === false) {
			$dfgPrintStream->outputln("The following restore points are available:");
			$dfgPrintStream->outputln();
			do {
				$i = 1;
				foreach($restorePoints as $rp) {
					$timestamp = filemtime($rp);
					$dfgPrintStream->outputln("($i) ".basename($rp)." [".date(DATE_RSS, $timestamp)."]");
					$i++;
				}
				$dfgPrintStream->outputln("\n\nChoose one: ");
				$num = intval(trim(fgets(STDIN)));
			} while(!(is_int($num) && $num < $i && $num > 0));
			$dfgRestorePoint = basename($restorePoints[$num-1]);
		}
		$logger->info("Start restore operation on '$dfgRestorePoint'");
		$success = $rollback->restore($dfgRestorePoint);
		if (!$success) {
			$logger->error("Could not restore '$dfgRestorePoint'");
			$dfgPrintStream->outputln("\nCould not restore '$dfgRestorePoint'. Does it exist?", DF_PRINTSTREAM_TYPE_ERROR);
		}
		$logger->info("End restore operation");
		die(DF_TERMINATION_WITH_FINALIZE);
	} else {
		die(DF_TERMINATION_WITHOUT_FINALIZE);
	}
}

if ($dfgCreateRestorePoint) {
	if (empty($dfgRestorePoint)) {
		$dfgRestorePoint = "autogen_".uniqid();
	}
	$logger->info("Create restore point: $dfgRestorePoint");
	$rollback->saveInstallation($dfgRestorePoint);
	$rollback->saveDatabase($dfgRestorePoint);
	$logger->info("Restore point created");
	die(DF_TERMINATION_WITHOUT_FINALIZE);
}

if ($dfgRestoreList) {
	$restorePoints = $rollback->getAllRestorePoints();
	if (count($restorePoints) === 0) {
		$dfgPrintStream->outputln("No restore point exist.\n");
		die(DF_TERMINATION_WITHOUT_FINALIZE);
	}
	$i = 1;
	foreach($restorePoints as $rp) {
		$timestamp = filemtime($rp);
		$dfgPrintStream->outputln("($i) ".basename($rp)." [".date(DATE_RSS, $timestamp)."]");
		$i++;
	}
	$dfgPrintStream->outputln();
	die(DF_TERMINATION_WITHOUT_FINALIZE);
}

// Global update (ie. updates all packages to the latest possible version)
if ($dfgGlobalUpdate) {
	$logger->info("Start global update");
	handleGlobalUpdate($dfgCheckDep);
	$logger->info("End global update");
	die($dfgCheckDep === true  ? DF_TERMINATION_WITHOUT_FINALIZE : DF_TERMINATION_WITH_FINALIZE);
}

// List all available packages and show which are installed.
if ($dfgListPackages) {
	$installer->listAvailablePackages($dfgShowDescription, $pattern);
	die(DF_TERMINATION_WITHOUT_FINALIZE);  // 2 is normal termination but no further action
}



// install
foreach($packageToInstall as $toInstall) {
	$toInstall = str_replace(".", "", $toInstall);
	$parts = explode("-", $toInstall);
	$packageID = $parts[0];
	$version = count($parts) > 1 ? $parts[1] : NULL;
	try {
		$logger->info("Start install package '$packageID'".(is_null($version) ? "" : "-$version"));
		handleInstallOrUpdate($packageID, $version);
		$logger->info("End install package '$packageID'".(is_null($version) ? "" : "-$version"));
	} catch(InstallationError $e) {
		$logger->fatal($e);
		fatalError($e);
	} catch(HttpError $e) {
		$logger->fatal($e);
		fatalError($e);
	} catch(RepositoryError $e) {
		$logger->fatal($e);
		fatalError($e);
	} catch(RollbackInstallation $e) {
		$logger->fatal($e);
		fatalError("Installation failed! You can try to rollback: smwadmin -r");
	}
}

// install ontologies
if (count($ontologiesToInstall) > 0) {

	$mediaWikiLocation = dirname(__FILE__) . '/../../..';
	require_once "$mediaWikiLocation/maintenance/commandLine.inc";

	initializeLanguage();

	// requires a wiki environment, so check and include a few things more
	checkWikiContext();
	require_once($rootDir.'/tools/smwadmin/DF_OntologyInstaller.php');

	global $rootDir, $dfgPrintStream;
	foreach($ontologiesToInstall as $filePath) {

		$oInstaller = OntologyInstaller::getInstance(realpath($rootDir."/../"));

		$confirm = new DFOntologyConflictConfirm();

		if (!isset($ontologyID)) {
			$fileName = basename($filePath);
			$bundleID = reset(explode(".", $fileName));
		}

		// make path absolute is given as relative
		if (file_exists($rootDir."/tools/".$filePath)) {
			$filePath = $rootDir."/tools/".$filePath;
		}

		$bundleID = strtolower($bundleID);

		global $dfgForce, $dfgNoConflict;
		if ($dfgForce) {
			$mode = DF_ONTOLOGYIMPORT_FORCEOVERWRITE;
		} else if ($dfgNoConflict) {
			$mode = DF_ONTOLOGYIMPORT_STOPONCONFLICT;
		} else {
			$mode = DF_ONTOLOGYIMPORT_ASKINTERACTIVELY;
		}

		try {
			$prefix = $oInstaller->installOntology($bundleID, $filePath, $confirm, false, $mode);

			// copy ontology and create ontology bundle
			$dfgPrintStream->outputln( "[Creating deploy descriptor...");
			$xml = $oInstaller->createDeployDescriptor($bundleID, $filePath, $prefix);
			Tools::mkpath($mwrootDir."/extensions/$bundleID");
			$handle = fopen($mwrootDir."/extensions/$bundleID/deploy.xml", "w");
			fwrite($handle, $xml);
			fclose($handle);
			$dfgPrintStream->output("done.]");
			$dfgPrintStream->outputln("[Copying ontology file...");
			copy($filePath, $mwrootDir."/extensions/$bundleID/".basename($filePath));

			// store prefix
			if ($prefix != '') {
				$handle = fopen("$mwrootDir/extensions/$bundleID/".basename($filePath).".prefix", "w");
				fwrite($handle, $prefix);
				fclose($handle);
			}

			// register in Localsettings.php
			$ls = file_get_contents("$mwrootDir/LocalSettings.php");
			if (strpos($ls, "/*start-$bundleID*/" ) === false) {
				$handle = fopen("$mwrootDir/LocalSettings.php", "a");
				fwrite($handle, "/*start-$bundleID*/\n/*end-$bundleID*/");
				fclose($handle);
			}

			$dfgPrintStream->output( "done.]");
		} catch(InstallationError $e) {

			switch($e->getErrorCode()) {
				case DEPLOY_FRAMEWORK_ONTOLOGYCONFLICT_ERROR: 
					$dfgPrintStream->outputln("ontology conflict\n", DF_PRINTSTREAM_TYPE_ERROR);
			}

			die(DF_TERMINATION_WITHOUT_FINALIZE);
		}
	}
}

// install local bundles
if (count($localBundlesToInstall) > 0) {

	foreach($localBundlesToInstall as $filePath) {
		try {
			$installer->installOrUpdateFromFile($filePath);
		} catch(InstallationError $e) {
			$logger->fatal($e);
			fatalError($e);
		} catch(HttpError $e) {
			$logger->fatal($e);
			fatalError($e);
		} catch(RollbackInstallation $e) {
			$logger->fatal($e);
			fatalError("Installation failed! You can try to rollback: smwadmin -r");
		}catch(RepositoryError $e) {
			$logger->fatal($e);
			fatalError($e);
		}
	}
}

//de-install
foreach($packageToDeinstall as $toDeInstall) {
	$toDeInstall = str_replace(".", "", $toDeInstall);
	try {
		$dd = $installer->deinstall($toDeInstall);
		if (count($dd->getWikidumps()) > 0
		|| count($dd->getResources()) >  0
		|| count($dd->getUninstallScripts()) > 0
		|| count($dd->getCodefiles()) > 0
		|| count($dd->getOntologies()) > 0) {
			// include commandLine.inc to be in maintenance mode
			$mediaWikiLocation = dirname(__FILE__) . '/../../..';
			require_once "$mediaWikiLocation/maintenance/commandLine.inc";

			initializeLanguage();
			// include the resource installer
			require_once('DF_ResourceInstaller.php');
			require_once('DF_OntologyInstaller.php');

			// include commandLine.inc to be in maintenance mode
			checkWikiContext();

			$packageID = $dd->getID();
			$version = $dd->getVersion();
			$logger->info("Start un-install package '$packageID'".(is_null($version) ? "" : "-$version"));
			$installer->deinitializePackages($dd);
			$logger->info("End un-install package '$packageID'".(is_null($version) ? "" : "-$version"));
		}
	} catch(InstallationError $e) {
		$logger->fatal($e);
		fatalError($e);
	} catch(HttpError $e) {
		$logger->fatal($e);
		fatalError($e);
	} catch(RollbackInstallation $e) {
		$logger->fatal($e);
		fatalError("Installation failed! You can try to rollback: smwadmin -r");
	}catch(RepositoryError $e) {
		$logger->fatal($e);
		fatalError($e);
	}

}

// update
foreach($packageToUpdate as $toUpdate) {
	$toUpdate = str_replace(".", "", $toUpdate);
	$parts = explode("-", $toUpdate);
	$packageID = $parts[0];
	$version = count($parts) > 1 ? $parts[1] : NULL;
	try {
		$logger->info("Start update package '$packageID'".(is_null($version) ? "" : "-$version"));
		handleInstallOrUpdate($packageID, $version);
		$logger->info("End update package '$packageID'".(is_null($version) ? "" : "-$version"));
	} catch(InstallationError $e) {
		$logger->fatal($e);
		fatalError($e);
	} catch(HttpError $e) {
		$logger->fatal($e);
		fatalError($e);
	} catch(RollbackInstallation $e) {
		$logger->fatal($e);
		fatalError("Installation failed! You can try to rollback: smwadmin -r");
	} catch(RepositoryError $e) {
		$logger->fatal($e);
		fatalError($e);
	}

}

if (count($installer->getErrors()) === 0) {
	$dfgPrintStream->outputln( "\nOK.\n");
	die($dfgCheckDep === true ? DF_TERMINATION_WITHOUT_FINALIZE : DF_TERMINATION_WITH_FINALIZE);
} else {
	$dfgPrintStream->outputln("\nErrors occured:\n");
	foreach($installer->getErrors() as $e) {
		$dfgPrintStream->outputln( $e, DF_PRINTSTREAM_TYPE_ERROR);
	}
	$dfgPrintStream->outputln();
	die(DF_TERMINATION_ERROR);
}

function showHelp() {
	global $dfgPrintStream;
	$dfgPrintStream->outputln( "smwhalo admin utility v".DEPLOY_FRAMEWORK_VERSION.", Ontoprise 2009-2011");
	$dfgPrintStream->outputln( "Usage: smwadmin [ -i | -d ] <package>[-<version>]");
	$dfgPrintStream->outputln( "       smwadmin -u [ <package>[-<version>] ]");
	$dfgPrintStream->outputln( "       smwadmin -r");
	$dfgPrintStream->outputln( "       smwadmin -l");
	$dfgPrintStream->outputln();
	$dfgPrintStream->outputln( "\t-i <package>: Install");
	$dfgPrintStream->outputln( "\t-d <package> ]: De-Install");
	$dfgPrintStream->outputln( "\t-u <package>: Update");
	$dfgPrintStream->outputln( "\t-l [ pattern ] : List installed packages.");
	$dfgPrintStream->outputln( "\t-l --desc: Shows additional description about the packages.");
	$dfgPrintStream->outputln( "\t-r [ name ]: Restore from a wiki-restore-point.");
	$dfgPrintStream->outputln( "\t--rcreate [ name ]: Explicitly creates a wiki-restore-point.");
	$dfgPrintStream->outputln( "\t--rlist : Shows all existing wiki-restore-points");
	$dfgPrintStream->outputln( "\t--dep : Check only dependencies but do not install.");
	$dfgPrintStream->outputln( "\tAdvanced options: ");
	$dfgPrintStream->outputln( "\t--finalize: Finalizes installation");
	$dfgPrintStream->outputln( "\t-f: Force operation (ignore any problems if possible)");
	//$dfgPrintStream->outputln( "\t--checkdump <package>: Check only dumps for changes but do not install.");
	$dfgPrintStream->outputln( "\t--noconflict: Assures that there are no conflicts on ontology import. Will stop the process, if not.");
	$dfgPrintStream->outputln( "\t--nocheck: Skips the environment checks");
	$dfgPrintStream->outputln();
	$dfgPrintStream->outputln( "Examples:\tsmwadmin -i smwhalo Installs the given packages");
	$dfgPrintStream->outputln( "\tsmwadmin -u: Updates complete installation");
	$dfgPrintStream->outputln( "\tsmwadmin -u --dep: Shows what would be updated.");
	$dfgPrintStream->outputln( "\tsmwadmin -d smw: Removes the package smw.");
	$dfgPrintStream->outputln( "\tsmwadmin -r [name] : Restores old installation from a restore point. User is prompted for which.");
	$dfgPrintStream->outputln( "\n");

	$logDir = Tools::getHomeDir()."/df_log";
	$dfgPrintStream->outputln( "The DF's log files are stored in: $logDir");
	$dfgPrintStream->outputln( "\n");
}





function handleGlobalUpdate($dfgCheckDep) {
	global $installer, $dfgPrintStream;
	try {
		list($extensions_to_update, $contradictions, $updated) = $installer->updateAll($dfgCheckDep);
		if ($dfgCheckDep) {
			if (count($extensions_to_update) > 0) {

				$dfgPrintStream->outputln("\nThe following extensions would get updated:\n");
				foreach($extensions_to_update as $id => $etu) {
					list($desc, $min, $max) = $etu;
					$dfgPrintStream->outputln( "\t*$id-".Tools::addVersionSeparators(array($min, $desc->getPatchlevel())));
				}


			}
			if (count($contradictions) > 0) {
				$dfgPrintStream->outputln("\nThe following extensions can not be installed/updated due to conflicts:");
				foreach($contradictions as $etu) {
					list($dd, $min, $max) = $etu;
					$dfgPrintStream->outputln("* ".$dd->getID());
				}
			}
			$dfgPrintStream->outputln("\n");

		}

		if ($updated && count($extensions_to_update) > 0) {
			$dfgPrintStream->outputln("\nYour installation is now up-to-date!\n");
		} else if (count($extensions_to_update) == 0) {
			$dfgPrintStream->outputln("\nYour installation is already up-to-date!\n");
		}
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

function handleInstallOrUpdate($packageID, $version) {
	global $checkDump, $dfgCheckDep, $installer, $res_installer, $dfgPrintStream;
	if (isset($checkDump) && $checkDump == true) {
		// include commandLine.inc to be in maintenance mode
		$mediaWikiLocation = dirname(__FILE__) . '/../../..';
		require_once "$mediaWikiLocation/maintenance/commandLine.inc";

		initializeLanguage();

		// check status of a currently installed wikidump
		checkWikiContext();

		// include the resource installer
		require_once('DF_ResourceInstaller.php');

		$res_installer = ResourceInstaller::getInstance($mwrootDir);
		$res_installer->checkWikidump($packageID, $version);
		$dfgPrintStream->outputln("\n");

	} else if ($dfgCheckDep === true) {

		// check dependencies of a package to install or update
		list($new_package, $old_package, $extensions_to_update) = $installer->collectPackagesToInstall($packageID, $version);
		$dfgPrintStream->outputln("\nThe following extensions would be installed:\n");
		foreach($extensions_to_update as $etu) {
			list($desc, $min, $max) = $etu;
			$id = $desc->getID();
			$dfgPrintStream->outputln("\t*$id-".Tools::addVersionSeparators(array($min, $desc->getPatchlevel())));
		}


		$dfgPrintStream->outputln("\n");
	} else {

		// install or update
		$installer->installOrUpdate($packageID, $version);
	}
}

/**
 * Checks if the wiki context is valid.
 *
 */
function checkWikiContext() {
    
	global $wgDBadminuser,$wgDBadminpassword, $wgDBtype, $wgDBserver, 
	       $wgDBadminuser, $wgDBadminpassword, $wgDBname, $dfgPrintStream;
	# Attempt to connect to the database as a privileged user
	# This will vomit up an error if there are permissions problems
	$dbclass = 'Database' . ucfirst( $wgDBtype ) ;
	$wgDatabase = new $dbclass( $wgDBserver, $wgDBadminuser, $wgDBadminpassword, $wgDBname, 1 );

	if( !$wgDatabase->isOpen() ) {
		# Appears to have failed
		$dfgPrintStream->outputln( "A connection to the database could not be established. Check the\n" );
		$dfgPrintStream->outputln( "values of \$wgDBadminuser and \$wgDBadminpassword.\n" );
		die(DF_TERMINATION_ERROR);
	}


	// check if AdminSettings.php is available
	if (!isset($wgDBadminuser) && !isset($wgDBadminpassword)) {
		$dfgPrintStream->outputln("Please set create AdminSettings.php file (on MW < 1.16). ".
		          "On MW >= 1.16 set both variables in LocalSettings.php.", DF_PRINTSTREAM_TYPE_FATAL);
		die(DF_TERMINATION_ERROR);
	}

}


/**
 * Initializes the language object
 *
 * Note: Requires wiki context
 */
function initializeLanguage() {
	global $wgLanguageCode, $dfgLang, $mwrootDir;
	$langClass = "DF_Language_$wgLanguageCode";
	if (!file_exists($mwrootDir."/deployment/languages/$langClass.php")) {
		$langClass = "DF_Language_En";
	}
	require_once($mwrootDir."/deployment/languages/$langClass.php");
	$dfgLang = new $langClass();
}


/**
 * Shows a fatal error which aborts installation.
 *
 * @param Exception $e (InstallationError, HttpError, RollbackInstallation)
 */
function fatalError($e) {
	$dfgPrintStream->outputln();

	if ($e instanceof InstallationError) {
		switch($e->getErrorCode()) {
			case DEPLOY_FRAMEWORK_DEPENDENCY_EXIST: {
				$packages = $e->getArg1();
				$dfgPrintStream->outputln($e->getMsg());
				$dfgPrintStream->outputln();
				foreach($packages as $p) {
					$dfgPrintStream->outputln("\t*$p", DF_PRINTSTREAM_TYPE_FATAL);
				}
				break;
			}
			case DEPLOY_FRAMEWORK_ALREADY_INSTALLED:
				$package = $e->getArg1();
				$dfgPrintStream->outputln($e->getMsg(), DF_PRINTSTREAM_TYPE_FATAL);
				$dfgPrintStream->outputln("\t*".$package->getID()."-".$package->getVersion(), DF_PRINTSTREAM_TYPE_FATAL);
				break;
			
			default: $dfgPrintStream->outputln($e->getMsg(), DF_PRINTSTREAM_TYPE_FATAL); break;
		}
	} else if ($e instanceof HttpError) {
		$dfgPrintStream->outputln($e->getMsg(), DF_PRINTSTREAM_TYPE_FATAL);
	
	} else if ($e instanceof RepositoryError) {
		$dfgPrintStream->outputln($e->getMsg(), DF_PRINTSTREAM_TYPE_FATAL);
	} else if (is_string($e)) {
		$dfgPrintStream->outputln($e, DF_PRINTSTREAM_TYPE_FATAL);
	}
	$dfgPrintStream->outputln();
	$dfgPrintStream->outputln();
	
	// stop installation
	die(DF_TERMINATION_ERROR);
}

class DFOntologyConflictConfirm {
	function askForOntologyPrefix(& $result) {
		global $dfgPrintStream;
		$dfgPrintStream->outputln("\nOntology conflict. Please enter prefix: ");
		$line = trim(fgets(STDIN));
		$result = $line;
	}
}
