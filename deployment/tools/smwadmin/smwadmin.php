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
define('DEPLOY_FRAMEWORK_VERSION', '{{$VERSION}} [B${env.BUILD_NUMBER}]');

// termination constants
define('DF_TERMINATION_WITH_FINALIZE', 0);
define('DF_TERMINATION_ERROR', 1);
define('DF_TERMINATION_WITHOUT_FINALIZE', 2);

// Setting constants (FIXME: must be merged from settings.php)
define('DF_HOME_DIR_NOT_WRITEABLE', 3);

global $rootDir;
$rootDir = dirname(__FILE__);
$rootDir = str_replace("\\", "/", $rootDir);
$rootDir = realpath($rootDir."/../../");

$mwrootDir = dirname(__FILE__);
$mwrootDir = str_replace("\\", "/", $mwrootDir);
$mwrootDir = realpath($mwrootDir."/../../../");

//Load Settings
if(file_exists($rootDir.'/settings.php'))
{
	require_once($rootDir.'/settings.php');
}
require_once('DF_Tools.php');
require_once('DF_UserInput.php');
require_once('DF_Installer.php');
require_once($mwrootDir.'/deployment/io/DF_Log.php');
require_once($mwrootDir.'/deployment/io/DF_PrintoutStream.php');
require_once($mwrootDir.'/deployment/languages/DF_Language.php');
// output format of smwadmin as a console app is text

$dfgLogToFile=false;
$dfgOutputFormat="text";
$args = $_SERVER['argv'];
for( $arg = reset( $args ); $arg !== false; $arg = next( $args ) ) {
	if ($arg == '--logtofile') {
		$dfgLogToFile = next($args);
		continue;
	} else if ($arg == '--outputformat') {
		$dfgOutputFormat = next($args);
		continue;
	}
}

dffInitLanguage();
$dfgOut = DFPrintoutStream::getInstance(DF_OUTPUT_FORMAT_TEXT);
$dfgOut->start(DF_OUTPUT_TARGET_STDOUT);

if ($dfgLogToFile !== false) {
	$dfgOut->start(DF_OUTPUT_TARGET_FILE, $dfgLogToFile);
}

if ($dfgOutputFormat != "text") {
	$dfgOut->setMode($dfgOutputFormat);
}

// check PHP version
$phpver = str_replace(".","",phpversion());
if ($phpver < 520) {
	dffExitOnFatalError("PHP version must be >= 5.2\n");
}

if (array_key_exists('SERVER_NAME', $_SERVER) && $_SERVER['SERVER_NAME'] != NULL) {
	dffExitOnFatalError("Invalid access! A maintenance script MUST NOT be accessed from remote.");
}

// check if the user is allowed to create files, directory.
if (!in_array("--nocheck", $_SERVER['argv'])) {
	$check = Tools::checkPriviledges($mwrootDir);
	if ($check !== true) {
		dffExitOnFatalError($check);
	}

	// check if LocalSettings.php is writeable

	@$success = touch("$rootDir/../LocalSettings.php");
	if ($success === false) {
		dffExitOnFatalError("LocalSettings.php is not accessible. Missing rights or file locked?");
	}
}

// check required tools
$check = Tools::checkEnvironment();
if ($check !== true) {
	dffExitOnFatalError($check);
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
$dfgRemoveRestorePoint=false;
$dfgListpages="no";
$dfgRemoveReferenced=false;
$dfgRemoveStillUsed=false;
$dfgIncludeImages=false;
$dfgIncludeImages=false;
$dfgIncludeInstances=false;
$dfgBundleID = "";
$dfgNoAsk=false;

$args = $_SERVER['argv'];
array_shift($args); // remove script name
if (count($args) === 0) {
	dffShowHelp();
	die(DF_TERMINATION_WITHOUT_FINALIZE);
}

// get command line parameters
for( $arg = reset( $args ); $arg !== false; $arg = next( $args ) ) {
	if ($arg == '--help') {
		dffShowHelp();
		die(DF_TERMINATION_WITHOUT_FINALIZE);
	} else
	//-i => Install
	if ($arg == '-i') {
		$package = next($args);
		if ($package === false) dffExitOnFatalError("No package found");

		if (file_exists($package)) {
			$help1 = explode(".", $package);
			$help2 = array_reverse($help1);
			$file_ext = reset($help2);
			if (Tools::checkIfOntologyFile($package)) {
				// import ontology
				$ontologiesToInstall[] = $package;
			} else if ($file_ext == 'zip') {
				// import bundle
				$localBundlesToInstall[] = $package;
			}

		} else {
			// assume it is a package but print a warning if the name has a
			// ontology extension.
			if (Tools::checkIfOntologyFile($package)) {
				$dfgOut->outputln("Are you sure '$package' is intended to be a package? It does not exist as a file.\n", DF_PRINTSTREAM_TYPE_WARN);
			}
			$packageToInstall[] = $package;
		}
		continue;
	} else if ($arg == '-d') { // -d => De-install
		$package = next($args);
		if ($package === false) dffExitOnFatalError("No package found");

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
			// assume it is a package but print a warning if the name has a
			// ontology extension.
			$file_ext = reset(array_reverse(explode(".", $package)));
			if ($file_ext == 'owl' || $file_ext == 'rdf' || $file_ext == 'obl') {
				$dfgOut->outputln("Are you sure '$package' is intended to be a package? It does not exist as a file.\n", DF_PRINTSTREAM_TYPE_WARN);
			}
			$packageToUpdate[] = $package;
		}

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
		if ($package === false) dffExitOnFatalError("No package found");
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
	} else if ($arg == '--rremove') {
		$dfgRemoveRestorePoint = true;
		$dfgRestorePoint = next($args);
		continue;
	} else if ($arg == '--noask') {
		$dfgNoAsk = true;
		continue;
	} else if ($arg == '--logtofile') {
		$dfgLogToFile = next($args);
		continue;
	} else if ($arg == '--outputformat') {
		$dfgOutputFormat = next($args);
		continue;
	} else if ($arg == '--listpages') {
		$dfgListpages = next($args);
		continue;
	} else if ($arg == '--removereferenced') {
		$dfgRemoveReferenced = true;
		continue;
	} else if ($arg == '--includeImages') {
		$dfgIncludeImages = true;
		continue;
	} else if ($arg == '--includeTemplates') {
		$dfgIncludeTemplates = true;
		continue;
	} else if ($arg == '--includeInstances') {
		$dfgIncludeInstances = true;
		continue;
	} else if ($arg == '--removestillused') {
		$dfgRemoveStillUsed = true;
		continue;
	} else if ($arg == '--bundleid') {
		$dfgBundleID = next($args);
		continue;
	} else if ($arg == '--nocheck') {
		// ignore
		continue;
	} else {
		dffExitOnFatalError("\nUnknown command: $arg. Try --help\n\n");
	}
	$params[] = $arg;
}




try {
	$logger = Logger::getInstance();
	$installer = Installer::getInstance($mwrootDir, $dfgForce);
	$rollback = Rollback::getInstance($mwrootDir);
} catch(DF_SettingError $e) {
	dffExitOnFatalError($e);
}

if ($dfgInstallPackages) {
	// include commandLine.inc to be in maintenance mode
	$mediaWikiLocation = dirname(__FILE__) . '/../../..';
	require_once "$mediaWikiLocation/maintenance/commandLine.inc";
	$dfgOut->setMode($dfgOutputFormat);

	dffInitLanguage();
	// include the resource installer
	require_once('DF_ResourceInstaller.php');

	// finalize mode requires a wiki environment, so check and include a few things more
	dffCheckWikiContext();

	$logger->info("Start initializing packages");
	$installer->initializePackages();
	$logger->info("End initializing packages");
	$dfgOut->outputln('__OK__');
	die(DF_TERMINATION_WITHOUT_FINALIZE);  // 2 is normal termination but no further action
} else {
	// check for non-initialized extensions
	$localPackages = PackageRepository::getLocalPackagesToInitialize($mwrootDir);
	if (count($localPackages) > 0) {
		dffExitOnFatalError("\nThere are non-initialized extensions. Run: smwadmin --finalize\n");
	}
}

if ($dfgRestore) {
	$dfgOut->outputln("\nThis operation will restore the wiki from the last restore point.");
	$dfgOut->outputln("\nThat means the Mediawiki installation is overwritten as well as the");
	$dfgOut->outputln("\ndatabase content!\n\n ");
	$result = DFUserInput::consoleConfirm("Do you really want to continue (y/n)?");

	if ($result) {
		$restorePoints = $rollback->getAllRestorePoints();
		if (count($restorePoints) === 0) {
			$dfgOut->outputln("Nothing to restore.");
			$dfgOut->outputln();
			die(DF_TERMINATION_WITHOUT_FINALIZE);
		}
		if ($dfgRestorePoint === false) {
			$dfgOut->outputln("The following restore points are available:");
			$dfgOut->outputln();
			do {
				$i = 1;
				foreach($restorePoints as $rp) {
					$timestamp = filemtime($rp);
					$dfgOut->outputln("($i) ".basename($rp)." [".date(DATE_RSS, $timestamp)."]");
					$i++;
				}
				$dfgOut->outputln("\n\nChoose one: ");
				$num = intval(trim(fgets(STDIN)));
			} while(!(is_int($num) && $num < $i && $num > 0));
			$dfgRestorePoint = basename($restorePoints[$num-1]);
		}
		$logger->info("Start restore operation on '$dfgRestorePoint'");
		$success = $rollback->restore($dfgRestorePoint);
		if (!$success) {
			$logger->error("Could not restore '$dfgRestorePoint'");
			$dfgOut->outputln("\nCould not restore '$dfgRestorePoint'. Does it exist?", DF_PRINTSTREAM_TYPE_ERROR);
		}
		$logger->info("End restore operation");
		$dfgOut->outputln('__OK__');
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
	$dfgOut->outputln('__OK__');
	die(DF_TERMINATION_WITHOUT_FINALIZE);
}

if($dfgRemoveRestorePoint) {
	if (empty($dfgRestorePoint)) {
		dffExitOnFatalError("Name of restore point missing.");
	}
	try {
		$success = $rollback->removeRestorePoint($dfgRestorePoint);
		if ($success) {
			$dfgOut->outputln('__OK__');
			die(DF_TERMINATION_WITHOUT_FINALIZE);
		} else {
			dffExitOnFatalError("Removing of restore point '$dfgRestorePoint' failed.");
		}
	} catch(InstallationError $e) {
		dffExitOnFatalError($e->getMsg());
	}
}

if ($dfgRestoreList) {
	$restorePoints = $rollback->getAllRestorePoints();
	if (count($restorePoints) === 0) {
		$dfgOut->outputln("No restore point exist.\n");
		die(DF_TERMINATION_WITHOUT_FINALIZE);
	}
	$i = 1;
	foreach($restorePoints as $rp) {
		$timestamp = filemtime($rp);
		$dfgOut->outputln("($i) ".basename($rp)." [".date(DATE_RSS, $timestamp)."]");
		$i++;
	}
	$dfgOut->outputln();
	die(DF_TERMINATION_WITHOUT_FINALIZE);
}

// Global update (ie. updates all packages to the latest possible version)
if ($dfgGlobalUpdate) {
	$logger->info("Start global update");
	dffHandleGlobalUpdate($dfgCheckDep);
	$logger->info("End global update");
	$dfgOut->outputln('__OK__');
	die($dfgCheckDep === true  ? DF_TERMINATION_WITHOUT_FINALIZE : DF_TERMINATION_WITH_FINALIZE);
}

// List all available packages and show which are installed.
if ($dfgListPackages) {
	$installer->listAvailablePackages($dfgShowDescription, $pattern);
	die(DF_TERMINATION_WITHOUT_FINALIZE);  // 2 is normal termination but no further action
}



// install

// if more than one bundle is installed at once,
// consolidate the bundle list first, ie. remove all redundant bundles.
if (count($packageToInstall) > 1) {
	$packageToInstall = PackageRepository::getTopMostExtensions($packageToInstall);
}

foreach($packageToInstall as $toInstall) {
	$toInstall = str_replace(".", "", $toInstall);
	$parts = explode("-", $toInstall);
	$packageID = $parts[0];
	$version = count($parts) > 1 ? new DFVersion($parts[1]) : NULL;
	try {
		$logger->info("Start install package '$packageID'".(is_null($version) ? "" : "-".$version->toVersionString()));
		dffHandleInstallOrUpdate($packageID, $version);
		$logger->info("End install package '$packageID'".(is_null($version) ? "" : "-".$version->toVersionString()));
	} catch(InstallationError $e) {
		$logger->fatal($e);
		dffExitOnFatalError($e);
	} catch(HttpError $e) {
		$logger->fatal($e);
		dffExitOnFatalError($e);
	} catch(RepositoryError $e) {
		$logger->fatal($e);
		dffExitOnFatalError($e);
	} catch(RollbackInstallation $e) {
		$logger->fatal($e);
		dffExitOnFatalError("Installation failed! You can try to rollback: smwadmin -r");
	}
}

// install/updates ontologies
if (count($ontologiesToInstall) > 0) {

	$mediaWikiLocation = dirname(__FILE__) . '/../../..';
	require_once "$mediaWikiLocation/maintenance/commandLine.inc";

	dffInitLanguage();

	// requires a wiki environment, so check and include a few things more
	dffCheckWikiContext();
	require_once($rootDir.'/tools/smwadmin/DF_OntologyInstaller.php');
	$dfgOut->setMode($dfgOutputFormat);

	$localpackages = PackageRepository::getLocalPackages($mwrootDir);
	if (!array_key_exists('smw', $localpackages)) {
		dffExitOnFatalError("Ontology import needs at least SMW installed.");
	}
	
	global $rootDir, $dfgOut;
	$requiredPropertiesExist = DFBundleTools::checkBundleProperties($dfgOut);
	if (!$requiredPropertiesExist) {
		dffExitOnFatalError("Some properties do not exist or have wrong types.");
	}
	
	foreach($ontologiesToInstall as $filePath) {

		$oInstaller = OntologyInstaller::getInstance(realpath($rootDir."/../"));

		// make path absolute is given as relative
		if (file_exists($rootDir."/tools/".$filePath)) {
			$filePath = $rootDir."/tools/".$filePath;
		}

		global $dfgForce;
		
		try {

			$bundleID = $oInstaller->installOrUpdateOntology($filePath, false, $dfgBundleID);

			// copy ontology and create ontology bundle
			$dfgOut->outputln( "[Creating deploy descriptor...");
			$xml = $oInstaller->createDeployDescriptor($bundleID, $filePath);
			Tools::mkpath($mwrootDir."/extensions/$bundleID");
			$handle = fopen($mwrootDir."/extensions/$bundleID/deploy.xml", "w");
			fwrite($handle, $xml);
			fclose($handle);
			$dfgOut->output("done.]");
			$dfgOut->outputln("[Copying ontology file...");
			copy($filePath, $mwrootDir."/extensions/$bundleID/".basename($filePath));
			copy($filePath.".xml", $mwrootDir."/extensions/$bundleID/".basename($filePath).".xml");

			// register in Localsettings.php
			$ls = file_get_contents("$mwrootDir/LocalSettings.php");
			if (strpos($ls, "/*start-$bundleID*/" ) === false) {
				$handle = fopen("$mwrootDir/LocalSettings.php", "a");
				fwrite($handle, "/*start-$bundleID*/\n/*end-$bundleID*/");
				fclose($handle);
			}

			$dfgOut->output( "done.]");
		} catch(InstallationError $e) {

			switch($e->getErrorCode()) {
				case DEPLOY_FRAMEWORK_ONTOLOGYCONFLICT_ERROR:
					$dfgOut->outputln("ontology conflict\n", DF_PRINTSTREAM_TYPE_ERROR);
			}
			global $dfgLogToFile;
			if ($dfgLogToFile != false) $dfgOut->outputln('$$ERROR$$');
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
			dffExitOnFatalError($e);
		} catch(HttpError $e) {
			$logger->fatal($e);
			dffExitOnFatalError($e);
		} catch(RollbackInstallation $e) {
			$logger->fatal($e);
			dffExitOnFatalError("Installation failed! You can try to rollback: smwadmin -r");
		}catch(RepositoryError $e) {
			$logger->fatal($e);
			dffExitOnFatalError($e);
		}
	}
}

//de-install
// if more than one bundle is de-installed at once,
// consolidate the bundle list first, ie. correct the order and add additional extensions if necessary.
if (count($packageToDeinstall) > 1) {
	$packageToDeinstall = PackageRepository::getDeletionOrder($packageToDeinstall, $mwrootDir);

	//TODO: ask for confirmation?
}

foreach($packageToDeinstall as $toDeInstall) {

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

			dffInitLanguage();
			// include the resource installer
			require_once('DF_ResourceInstaller.php');
			require_once('DF_OntologyInstaller.php');
			$dfgOut->setMode($dfgOutputFormat);
			// include commandLine.inc to be in maintenance mode
			dffCheckWikiContext();

			$packageID = $dd->getID();
			$version = $dd->getVersion()->toVersionString();
			$logger->info("Start un-install package '$packageID'".(is_null($version) ? "" : "-$version"));
			$installer->deinitializePackages($dd);
			$logger->info("End un-install package '$packageID'".(is_null($version) ? "" : "-$version"));
		}
	} catch(InstallationError $e) {
		$logger->fatal($e);
		dffExitOnFatalError($e);
	} catch(HttpError $e) {
		$logger->fatal($e);
		dffExitOnFatalError($e);
	} catch(RollbackInstallation $e) {
		$logger->fatal($e);
		dffExitOnFatalError("Installation failed! You can try to rollback: smwadmin -r");
	}catch(RepositoryError $e) {
		$logger->fatal($e);
		dffExitOnFatalError($e);
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
		dffHandleInstallOrUpdate($packageID, $version);
		$logger->info("End update package '$packageID'".(is_null($version) ? "" : "-$version"));
	} catch(InstallationError $e) {
		$logger->fatal($e);
		dffExitOnFatalError($e);
	} catch(HttpError $e) {
		$logger->fatal($e);
		dffExitOnFatalError($e);
	} catch(RollbackInstallation $e) {
		$logger->fatal($e);
		dffExitOnFatalError("Installation failed! You can try to rollback: smwadmin -r");
	} catch(RepositoryError $e) {
		$logger->fatal($e);
		dffExitOnFatalError($e);
	}

}

if ($dfgListpages != "no") {
	// read pages

	require_once( $rootDir.'/../maintenance/commandLine.inc' );
	require_once( $rootDir.'/../maintenance/backup.inc' );
	require_once($rootDir."/descriptor/DF_DeployDescriptor.php");
	require_once($rootDir."/tools/smwadmin/DF_PackageRepository.php");
	require_once($rootDir."/tools/smwadmin/DF_Tools.php");
	require_once($rootDir."/tools/smwadmin/DF_UserInput.php");
	require_once($rootDir.'/io/import/DF_DeployWikiBundleImporter.php');
	require_once($rootDir.'/io/import/DF_OntologyDetector.php');
	$localPackages = PackageRepository::getLocalPackages($mwrootDir);
	if (!array_key_exists($dfgListpages, $localPackages)) {
		return NULL;
	}
	$dd = $localPackages[$dfgListpages];

	$result=array();
	$result['wikidumps'] = array();
	foreach($dd->getWikidumps() as $loc) {
		$handle = fopen( $mwrootDir."/".$dd->getInstallationDirectory()."/$loc", 'rt' );
		$source = new ImportStreamSource( $handle );
		$importer = new DeployWikiImporterDetector( $source, $dd->getID() );

		$importer->setDebug( false );

		$importer->doImport();

		$pageTitles = $importer->getResult();

		foreach($pageTitles as $tuple) {
			list($title, $command) = $tuple;
			$result['wikidumps'][$loc][] = $title->getPrefixedText();

		}
	}
	$result['ontologies'] = array();
	foreach($dd->getOntologies() as $loc) {
		$handle = fopen( $mwrootDir."/".$dd->getInstallationDirectory()."/$loc.xml", 'rt' );
		$source = new ImportStreamSource( $handle );
		$importer = new DeployWikiImporterDetector( $source, $dd->getID());

		$importer->setDebug( false );

		$importer->doImport();

		$pageTitles = $importer->getResult();

		foreach($pageTitles as $tuple) {
			list($title, $command) = $tuple;
			$result['ontologies'][$loc][] = $title->getPrefixedText();

		}
	}


	if ($dfgOutputFormat != 'json') {
		dffExitOnFatalError("Only json format supported: --outputformat json");
	}

	$dfgOut->output(json_encode($result));
	$dfgOut->setVerbose(false);
}

if (count($installer->getErrors()) === 0) {
	$dfgOut->outputln( "\n__OK__\n");
	die($dfgCheckDep === true ? DF_TERMINATION_WITHOUT_FINALIZE : DF_TERMINATION_WITH_FINALIZE);
} else {
	$dfgOut->outputln("\nErrors occured:\n");
	foreach($installer->getErrors() as $e) {
		$dfgOut->outputln( $e, DF_PRINTSTREAM_TYPE_ERROR);
	}
	dffExitOnFatalError();

}

function dffShowHelp() {
	global $dfgOut;
	$dfgOut->outputln( "Wiki administration tool utility v".DEPLOY_FRAMEWORK_VERSION.", Ontoprise 2009-2011");
	$dfgOut->outputln();
	$dfgOut->outputln( "Usage: smwadmin [ -i | -d ] <bundle>[-<version>] [ additional-options ]");
	$dfgOut->outputln( "       smwadmin -u [ <bundle>[-<version>] ] [ additional-options ]");
	$dfgOut->outputln( "       smwadmin -r [ <restore point name> ]");
	$dfgOut->outputln( "       smwadmin -l [--desc ]");
	$dfgOut->outputln( "       smwadmin --rcreate [ <restore point name> ]");
	$dfgOut->outputln( "       smwadmin --rlist");
	$dfgOut->outputln( "       smwadmin --finalize");
	$dfgOut->outputln();
	$dfgOut->outputln( "\t-i <bundle>: Install");
	$dfgOut->outputln( "\t-d <bundle> ]: De-Install");
	$dfgOut->outputln( "\t-u <bundle>: Update");
	$dfgOut->outputln( "\t-l [ pattern ] : List installed bundles.");
	$dfgOut->outputln( "\t-l --desc: Shows additional description about the bundles.");
	$dfgOut->outputln( "\t-r [ name ]: Restore from a wiki-restore-point.");
	$dfgOut->outputln( "\t--rcreate [ name ]: Explicitly creates a wiki-restore-point.");
	$dfgOut->outputln( "\t--rlist : Shows all existing wiki-restore-points");
	$dfgOut->outputln( "\t--finalize: Finalizes installation");
	$dfgOut->outputln( "\tAdditional options (can only be used with the optional above): ");
	$dfgOut->outputln( "\t--dep : Check only dependencies but do not install.");
	$dfgOut->outputln( "\t-f: Force operation (ignore any problems if possible)");
	//$dfgOut->outputln( "\t--checkdump <bundle>: Check only dumps for changes but do not install.");
	$dfgOut->outputln( "\t--nocheck: Skips checks for appropriate rights.");
	$dfgOut->outputln( "\t--noask: Skips all questions (assuming mostly 'yes' except for optional bundles");
	$dfgOut->outputln( "\t--removereferenced: Removes all templates, images and instances referenced used by a bundle. Used with -d");
	$dfgOut->outputln( "\t\t--removeTemplates: Removes all templates referenced used by a bundle. Used with -d");
	$dfgOut->outputln( "\t\t--removeInstances: Removes all instances referenced used by a bundle. Used with -d");
	$dfgOut->outputln( "\t\t--removeImages: Removes all images referenced used by a bundle. Used with -d");
	$dfgOut->outputln( "\t--removestillused: Removes also pages which are used by other bundles. Used with -d --removereferenced");

	$dfgOut->outputln();
	$dfgOut->outputln('Examples:');
	$dfgOut->outputln( "\tsmwadmin -i smwhalo Installs the given bundles");
	$dfgOut->outputln( "\tsmwadmin -u: Updates complete installation");
	$dfgOut->outputln( "\tsmwadmin -u --dep: Shows what would be updated.");
	$dfgOut->outputln( "\tsmwadmin -d smw: Removes the bundle smw.");
	$dfgOut->outputln( "\tsmwadmin -r [name] : Restores old installation from a restore point. User is prompted for which.");
	$dfgOut->outputln( "\tsmwadmin -i smwhalo -f: Installs smwhalo ignoring any problems");
	$dfgOut->outputln( "\tsmwadmin -u --noask: Updates the complete installation with no check for environment.");
	$dfgOut->outputln( "\n");

	$logger = Logger::getInstance();
	$dfgOut->outputln( "The DF's log files are stored in: ".$logger->getLogDir());
	$dfgOut->outputln( "\n");
}





function dffHandleGlobalUpdate($dfgCheckDep) {
	global $installer, $dfgOut;
	try {
		list($extensions_to_update, $contradictions, $updated) = $installer->updateAll($dfgCheckDep);
		if ($dfgCheckDep) {
			if (count($extensions_to_update) > 0) {

				$dfgOut->outputln("\nThe following extensions would get updated:\n");
				foreach($extensions_to_update as $id => $etu) {
					list($desc, $min, $max) = $etu;
					$dfgOut->outputln( "\t*$id-".$min->toVersionString()."_".$desc->getPatchlevel());
				}


			}
			if (count($contradictions) > 0) {
				$dfgOut->outputln("\nThe following extensions can not be installed/updated due to conflicts:");
				foreach($contradictions as $etu) {
					list($dd, $min, $max) = $etu;
					$dfgOut->outputln("* ".$dd->getID());
				}
			}
			$dfgOut->outputln("\n");

		}

		if ($updated && count($extensions_to_update) > 0) {
			$dfgOut->outputln("\nYour installation is now up-to-date!\n");
		} else if (count($extensions_to_update) == 0) {
			$dfgOut->outputln("\nYour installation is already up-to-date!\n");
		}
	} catch(InstallationError $e) {
		dffExitOnFatalError($e);
	} catch(HttpError $e) {
		dffExitOnFatalError($e);
	} catch(RollbackInstallation $e) {
		dffExitOnFatalError("Installation failed! You can try to rollback: smwadmin -r");
	} catch(RepositoryError $e) {
		dffExitOnFatalError($e);
	}

}

function dffHandleInstallOrUpdate($packageID, $version) {
	global $checkDump, $dfgCheckDep, $installer, $res_installer, $dfgOut;
	if (isset($checkDump) && $checkDump == true) {
		// include commandLine.inc to be in maintenance mode
		$mediaWikiLocation = dirname(__FILE__) . '/../../..';
		require_once "$mediaWikiLocation/maintenance/commandLine.inc";

		dffInitLanguage();

		// check status of a currently installed wikidump
		dffCheckWikiContext();

		// include the resource installer
		require_once('DF_ResourceInstaller.php');
		$dfgOut->setMode($dfgOutputFormat);
		$res_installer = ResourceInstaller::getInstance($mwrootDir);
		$res_installer->checkWikidump($packageID, $version);
		$dfgOut->outputln("\n");

	} else if ($dfgCheckDep === true) {

		// check dependencies of a package to install or update
		list($new_package, $old_package, $extensions_to_update) = $installer->collectPackagesToInstall($packageID, $version);
		$dfgOut->outputln("\nThe following extensions would be installed:\n");
		foreach($extensions_to_update as $etu) {
			list($desc, $min, $max) = $etu;
			$id = $desc->getID();
			$dfgOut->outputln("\t*$id-".$min->toVersionString()."_".$desc->getPatchlevel());
		}


		$dfgOut->outputln("\n");
	} else {

		// install or update
		$installer->installOrUpdate($packageID, $version);
	}
}

/**
 * Checks if the wiki context is valid.
 *
 */
function dffCheckWikiContext() {

	global $wgDBadminuser,$wgDBadminpassword, $wgDBtype, $wgDBserver,
	$wgDBadminuser, $wgDBadminpassword, $wgDBname, $dfgOut;
	# Attempt to connect to the database as a privileged user
	# This will vomit up an error if there are permissions problems
	$dbclass = 'Database' . ucfirst( $wgDBtype ) ;
	$wgDatabase = new $dbclass( $wgDBserver, $wgDBadminuser, $wgDBadminpassword, $wgDBname, 1 );

	if( !$wgDatabase->isOpen() ) {
		# Appears to have failed
		dffExitOnFatalError( "A connection to the database could not be established. Check the\n".
					"values of \$wgDBadminuser and \$wgDBadminpassword.\n" );

	}


	// check if AdminSettings.php is available
	if (!isset($wgDBadminuser) && !isset($wgDBadminpassword)) {
		dffExitOnFatalError("Please set create AdminSettings.php file (on MW < 1.16). ".
		          "On MW >= 1.16 set both variables in LocalSettings.php.");
	}

}




/**
 * Shows a fatal error which aborts installation.
 *
 * @param Exception $e (InstallationError, HttpError, RollbackInstallation)
 */
function dffExitOnFatalError($e) {
	global $dfgOut;
	$dfgOut->outputln();

	if ($e instanceof InstallationError || $e instanceof DF_SettingError) {
		switch($e->getErrorCode()) {
			case DEPLOY_FRAMEWORK_DEPENDENCY_EXIST: {
				$packages = $e->getArg1();
				$dfgOut->outputln($e->getMsg());
				$dfgOut->outputln();
				foreach($packages as $p) {
					$dfgOut->outputln("\t*$p", DF_PRINTSTREAM_TYPE_FATAL);
				}
				break;
			}
			case DEPLOY_FRAMEWORK_ALREADY_INSTALLED:
				$package = $e->getArg1();
				$dfgOut->outputln($e->getMsg(), DF_PRINTSTREAM_TYPE_FATAL);
				$dfgOut->outputln("\t*".$package->getID()."-".$package->getVersion()->toVersionString(), DF_PRINTSTREAM_TYPE_FATAL);
				break;

			default: $dfgOut->outputln($e->getMsg(), DF_PRINTSTREAM_TYPE_FATAL); break;
		}
	} else if ($e instanceof HttpError) {
		$dfgOut->outputln($e->getMsg(), DF_PRINTSTREAM_TYPE_FATAL);

	} else if ($e instanceof RepositoryError) {
		$dfgOut->outputln($e->getMsg(), DF_PRINTSTREAM_TYPE_FATAL);
	} else if (is_string($e)) {
		if (!empty($e)) $dfgOut->outputln($e, DF_PRINTSTREAM_TYPE_FATAL);
	}
	$dfgOut->outputln();
	$dfgOut->outputln();

	global $dfgLogToFile;
	if ($dfgLogToFile != false) $dfgOut->outputln('$$ERROR$$');
	$dfgOut->outputln();
	// stop installation
	die(DF_TERMINATION_ERROR);
}





