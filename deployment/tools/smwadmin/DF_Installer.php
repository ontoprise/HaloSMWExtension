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

// Error constants
define('DEPLOY_FRAMEWORK_INSTALL_LOWER_VERSION', 1);
define('DEPLOY_FRAMEWORK_NO_TMP_DIR', 2);
define('DEPLOY_FRAMEWORK_COULD_NOT_FIND_UPDATE', 3);
define('DEPLOY_FRAMEWORK_PACKAGE_NOT_EXISTS', 4);
define('DEPLOY_FRAMEWORK_DEPENDENCY_EXIST',5);
define('DEPLOY_FRAMEWORK_CODE_CHANGED',6);
define('DEPLOY_FRAMEWORK_MISSING_FILE', 7);
define('DEPLOY_FRAMEWORK_ALREADY_INSTALLED', 8);
define('DEPLOY_FRAMEWORK_NOT_INSTALLED', 9);
define('DEPLOY_FRAMEWORK_PRECEDING_CYCLE', 10);
define('DEPLOY_FRAMEWORK_WRONG_MW_VERSION', 11);
define('DEPLOY_FRAMEWORK_CREATING_RESTOREPOINT_FAILED', 12);
define('DEPLOY_FRAMEWORK_ONTOLOGYCONVERSION_FAILED', 13);
define('DEPLOY_FRAMEWORK_WRONG_VERSION', 14);
define('DEPLOY_FRAMEWORK_UNCOMPRESS_ERROR', 15);
define('DEPLOY_FRAMEWORK_ONTOLOGYCONFLICT_ERROR', 16);

require_once 'DF_PackageRepository.php';
require_once 'DF_Tools.php';
require_once 'DF_Rollback.php';


/**
 * @file
 * @ingroup DFInstaller
 *
 * @defgroup DFInstaller Installer
 * @ingroup DeployFramework
 *
 * Provides the basic installation routines for the smwadmin tool.
 *
 * @author: Kai K�hn / ontoprise / 2009
 *
 */

class Installer {

	static $instance = NULL; // singleton

	public static function getInstance($rootDir = NULL, $force = false, $noAsk = false, $noRollback = false) {
		if (!is_null(self::$instance)) return self::$instance;
		self::$instance = new Installer($rootDir, $force, $noAsk, $noRollback);
		return self::$instance;
	}
	/*
	 * Temporary folder for storing downloaded files
	 */
	var $tmpFolder;

	/*
	 * Mediawiki installation directory
	 */
	var $rootDir;

	// force installation even on warnings
	private $force;

	// no questions (for testing)
	private $noAsk;

	// Helper obejcts
	private $rollback;

	// global logger
	private $logger;

	private $errors;

	/**
	 * Creates new Installer.
	 *
	 * @param string $rootDir Explicit root dir. Only necessary for testing
	 */
	private function __construct($rootDir = NULL, $force = false, $noAsk = false, $noRollback = false) {
		// create temp folder
		$this->errors = array();
		$this->tmpFolder = Tools::isWindows() ? 'c:\temp\mw_deploy_tool' : '/tmp/mw_deploy_tool';
		if (!file_exists($this->tmpFolder)) Tools::mkpath($this->tmpFolder);
		if (!file_exists($this->tmpFolder)) {
			throw new InstallationError(DEPLOY_FRAMEWORK_NO_TMP_DIR, "Could not create temporary directory. Not Logged in as root?");
		}

		// get root dir
		$this->rootDir = $rootDir === NULL ? realpath(dirname(__FILE__)."/../../../") : $rootDir;

		$this->rollback = Rollback::getInstance($this->rootDir);

		$this->force = $force;
		$this->noAsk = $noAsk;
		$this->noRollback = $noRollback;
		$this->logger = Logger::getInstance();
	}

	/**
	 * Installs or updates a package.
	 *
	 * @param string $packageID
	 * @param int $version If omitted (or NULL), the latest version is installed.
	 */
	public function installOrUpdate($packageID, $version = NULL) {

		list($new_package, $old_package, $extensions_to_update, $contradictions) = $this->collectPackagesToInstall($packageID, $version);

		if (count($extensions_to_update) == 0)
		throw new InstallationError(DEPLOY_FRAMEWORK_COULD_NOT_FIND_UPDATE, "Set of packages to install is empty.", $packageID);
		// Install/update all dependant and super extensions
		print "\nThe following packages need to be installed";
		foreach($extensions_to_update as $etu) {
			list($dd, $min, $max) = $etu;
			print "\n- ".$dd->getID()."-".$dd->getVersion();
		}

		if (count($contradictions) > 0) {
			print "\nThe following extension can not be installed/updated due to conflicts:";
			foreach($contradictions as $etu) {
				list($dd, $min, $max) = $etu;
				print "\n- ".$dd->getID();
			}
		}
		$this->installOrUpdatePackages($extensions_to_update);


			
	}

	/**
	 * Installs a package from a file.
	 *
	 * Dependencies are not resolved. The user just gets a warning about that.
	 *
	 * @param $filePath bundle (zip file)
	 */
	public function installOrUpdateFromFile($filePath) {
		$dd = Tools::unzipDeployDescriptor($filePath, $this->tmpFolder);
		if (is_null($dd)) {
			throw new InstallationError(DEPLOY_FRAMEWORK_UNCOMPRESS_ERROR, "Uncompressing $filePath failed.");
		}
		$localPackages = PackageRepository::getLocalPackages($this->rootDir);

		// check dependencies
		$updatesNeeded=array();
		print "\n[Check for necessary updates...";
		$this->collectDependingExtensions($dd, $updatesNeeded, $localPackages);
		$this->collectSuperExtensions($dd, $updatesNeeded, $localPackages);
		print "done.]";

		//  calculate version which matches all depdencies of an extension.
		print "\n[Filter incompatible packages...";
		$this->filterIncompatiblePackages($updatesNeeded, $extensions_to_update, $contradictions);
		print "done.]";

		// Install/update all dependant and super extensions
		print "\nThe following packages need to be installed";
		foreach($extensions_to_update as $etu) {
			list($deployd, $min, $max) = $etu;
			print "\n- ".$deployd->getID()."-".$deployd->getVersion();
		}

		if (count($contradictions) > 0) {
			print "\nThe following extension can not be installed/updated due to conflicts:";
			foreach($contradictions as $etu) {
				list($deployd, $min, $max) = $etu;
				print "\n- ".$deployd->getID();
			}
		}
		$this->installOrUpdatePackages($extensions_to_update);

		$fromVersion = NULL;
		if (array_key_exists($dd->getID(), $localPackages)) {
			$fromVersion = $localPackages[$dd->getID()]->getVersion();
		}

		// dependencies are fine
		$id = $dd->getID();
		$version = $dd->getVersion();
		$this->logger->info("Unzip $filePath");
		$this->unzipFromFile($filePath);

		$this->logger->info("Apply configs for $filePath");
		$dd->applyConfigurations($this->rootDir, false, $fromVersion, $this);
		$this->errors = array_merge($this->errors, $dd->getLastErrors());

		// write finalize hint
		$handle = fopen($this->rootDir."/".$dd->getInstallationDirectory()."/init$.ext", "w");
		fwrite($handle, "1,".$fromVersion);
		fclose($handle);

		print "\n-------\n";
	}

	/**
	 * De-Installs extension. Checks if there are extensions which require the extension
	 * which is about to be deleted.
	 *
	 *  It de-initializes the extension by unapplying setup scripts.
	 *  It removes the configuration code from LocalSettings.php
	 *  It removes the extension code
	 *
	 * @param string $packageID
	 *
	 * @return DeployDescriptor of extension which is deleted
	 */
	public function deInstall($packageID) {

		print "\n[Checking for package $packageID...";
		$localPackages = PackageRepository::getLocalPackages($this->rootDir);
		$ext = NULL;
		foreach($localPackages as $p) {
			if ($p->getID() == $packageID) {
				$ext = $p;
				break;
			}
		}
		if (is_null($ext)) {
			$this->logger->error("Package does not exist $packageID");
			throw new InstallationError(DEPLOY_FRAMEWORK_PACKAGE_NOT_EXISTS, "Package does not exist", $packageID);
		}
		print "done.]";

		// check if there are depending extensions
		print "\n[Checking for dependent packages of $packageID...";
		$existDependency = false;
		$dependantPackages = array();
		foreach($localPackages as $p) {
			$dependencies = $p->getDependencies();

			foreach($dependencies as $dep) {
				list($id, $from, $to, $optional, $message) = $dep;
				if ($optional) continue;
				if ($id == $packageID) {
					$dependantPackages[] = $p->getID();
					$existDependency = true;
				}
			}
		}
		print "done.]";
		if ($existDependency) {
			$this->logger->error("Can not remove package. Dependency from the following packages exists: ".implode(",", $dependantPackages));
			throw new InstallationError(DEPLOY_FRAMEWORK_DEPENDENCY_EXIST, "Can not remove package. Dependency from the following packages exists:", $dependantPackages);
		}


		// unapply setups
		$this->logger->info("Unapply setups for ".$ext->getID());
		print "\n[Removing setup for ".$ext->getID()."...";
		$ext->unapplySetups($this->rootDir, false);
		print "done.]";

		// undo all config changes
		// - from LocalSettings.php
		// - from database (setup scripts)
		// - patches
		$this->logger->info("Unapply configs for ".$ext->getID());
		print "\n[Removing configurations for ".$ext->getID()."...";
		$ext->unapplyConfigurations($this->rootDir, false);
		$this->errors = array_merge($this->errors, $ext->getLastErrors());
		print "done.]";

		// remove extension code
		$this->logger->info("Remove code of ".$ext->getID());
		print "\n[Removing code for ".$ext->getID()."...";
		Tools::remove_dir($this->rootDir."/".$ext->getInstallationDirectory());
		print "done.]";

		// may contain files which are not located in the installation directory
		$this->logger->info("Delete external codefiles of ".$ext->getID());
		$this->deleteExternalCodefiles($ext);

		return $ext;
	}



	/**
	 * Updates all packages if possible
	 *
	 * @param boolean $onlyDependencyCheck
	 * @return true, if anything was updated.
	 */
	public function updateAll($onlyDependencyCheck = false) {

		$localPackages = PackageRepository::getLocalPackages($this->rootDir);

		// iterate through all installed packages, check if new or patched versions
		// are available and collect all depending extension to be updated.
		$updatesNeeded = array();
		foreach($localPackages as $tl_ext) {

			$dd = PackageRepository::getLatestDeployDescriptor($tl_ext->getID());

			if ($dd->getVersion() > $localPackages[$dd->getID()]->getVersion()
			|| ($dd->getVersion() == $localPackages[$dd->getID()]->getVersion() && $dd->getPatchlevel() > $localPackages[$dd->getID()]->getPatchlevel())) {
				$this->collectDependingExtensions($dd, $updatesNeeded, $localPackages, true);
				$updatesNeeded[] = array($dd, $dd->getVersion(), $dd->getVersion());
			}

		}

		// remove all packages which can not be updated due to conflicts
		$this->filterIncompatiblePackages($updatesNeeded, $extensions_to_update, $contradictions);

		if ($onlyDependencyCheck) {
			return array($extensions_to_update, $contradictions, false);
		} else {
			$this->installOrUpdatePackages($extensions_to_update);
			return array($extensions_to_update, $contradictions, true);

		}
	}


	/**
	 * List all available packages and show if it is installed and in wich version.
	 *
	 *
	 */
	public function listAvailablePackages($showDescription, $pattern = NULL) {

		$allPackages = PackageRepository::getAllPackages();
		$localPackages = PackageRepository::getLocalPackages($this->rootDir);
		if (count($allPackages) == 0) {
			print "\n\nNo packages available!\n";
			return;
		}
		print "\n Installed           | Bundle               | Av. versions  | Repository";
		print "\n-------------------------------------------------------------------------\n";

		foreach($allPackages as $p_id => $versions) {



			if (!is_null($pattern) && !empty($pattern)) { // filter packages
				if (substr(trim($pattern),0,1) == '*') {
					$cleanPattern = str_replace("*", "", $pattern);
					if (strpos($p_id, strtolower($cleanPattern)) === false) continue;
				} else {
					$cleanPattern = str_replace("*", "", $pattern);
					if (strpos($p_id, strtolower($cleanPattern)) !== 0) continue;
				}
			}
			if (array_key_exists($p_id, $localPackages)) {
				$patchlevel = $localPackages[$p_id]->getPatchlevel();
				$instTag = "[installed ".Tools::addVersionSeparators(array($localPackages[$p_id]->getVersion(), $patchlevel))."]";

			} else {
				$instTag = str_repeat(" ", 16);
			}

			$id_shown = $p_id;
			$id_shown .= str_repeat(" ", 22-strlen($p_id) >= 0 ? 22-strlen($p_id) : 0);
			$instTag .= str_repeat(" ", 20-strlen($instTag) >= 0 ? 20-strlen($instTag) : 0);
			$sep_v = array();
			foreach($versions as $v) $sep_v[] = Tools::addVersionSeparators($v);
			$versionsShown = "(".implode(", ", $sep_v).")";
			$versionsShown .= str_repeat(" ", 12-strlen($versionsShown) >= 0 ? 12-strlen($versionsShown) : 0);
			print "\n $instTag $id_shown  $versionsShown ".Tools::shortenURL($v[2], 70);

			if ($showDescription && array_key_exists($p_id, $localPackages)) print "\n ".$localPackages[$p_id]->getDescription()."\n\n";
		}

		// show local bundles
		$onlyLocalPackages = array_diff(array_keys($localPackages), array_keys($allPackages));
		if (count($onlyLocalPackages) > 0) {
			print "\n\nThe following bundles exist only locally:\n";
			foreach($onlyLocalPackages as $id) {
				$lp = $localPackages[$id];
				$display = "[installed ".Tools::addVersionSeparators(array($lp->getVersion(), $lp->getPatchlevel()))."]";
				$display .= str_repeat(" ", 20-strlen($display) >= 0 ? 20-strlen($display) : 0);
				$display .= $lp->getID();
				print "\n ".$display;
			}
		}
		print "\n\n";
	}

	/**
	 * Checks dependencies when installing the given package.
	 *
	 * @param string $packageID
	 * @param int $version
	 * @return array($new_package, $old_package, $extensions_to_update)
	 */
	public function collectPackagesToInstall($packageID, $version = NULL) {
		// 1. Check if package is installed
		print "\n[Check if package installed...";
		$localPackages = PackageRepository::getLocalPackages($this->rootDir);
		$old_package = NULL;
		foreach($localPackages as $p) {
			if ($p->getID() == $packageID) {
				$old_package = $p;
				print "found!]";
				break;
			}
		}

		if (is_null($old_package)) {
			print "not found.]";
		}


		// 2. Check code integrity of existing package
		if (!is_null($old_package)) {
			print "\n[Check code integrity...";
			$status = $old_package->validatecode($this->rootDir);
			if ($status !== true) {
				if (!$this->force) {
					throw new InstallationError(DEPLOY_FRAMEWORK_CODE_CHANGED, "Code files were modified. Use -f (force)", $status);
				} else {
					print "\nWarning: Code files contain differences. Patches may get lost.";
				}
			}

			print "done.]";
		}

		// 3. Get required package descriptor
		if ($version == NULL) {
			print "\n[Read latest deploy descriptor of $packageID...";
			$new_package = PackageRepository::getLatestDeployDescriptor($packageID);

		} else {
			print "\n[Read deploy descriptor of $packageID-$version...";
			$new_package = PackageRepository::getDeployDescriptor($packageID, $version);

		}

		// 5. check if update is neccessary
		if (!is_null($old_package) && $old_package->getVersion() > $new_package->getVersion()) {
			throw new InstallationError(DEPLOY_FRAMEWORK_INSTALL_LOWER_VERSION, "Really install lower version? Use -f (force)", $old_package);
		}

		if (!is_null($old_package) && ($old_package->getVersion() == $new_package->getVersion() && $old_package->getPatchlevel() == $new_package->getPatchlevel()) && !$this->force) {
			throw new InstallationError(DEPLOY_FRAMEWORK_ALREADY_INSTALLED, "Already installed. Nothing to do.", $old_package);
		}

		// 6. Check dependencies for install/update
		// get package to install
		$updatesNeeded = array(array($new_package, $new_package->getVersion(), $new_package->getVersion()));
		print "\n[Check for necessary updates...";
		$this->collectDependingExtensions($new_package, $updatesNeeded, $localPackages);
		$this->collectSuperExtensions($new_package, $updatesNeeded, $localPackages);
		print "done.]";

		// 7. calculate version which matches all depdencies of an extension.
		print "\n[Filter incompatible packages...";
		$this->filterIncompatiblePackages($updatesNeeded, $extensions_to_update, $contradictions);
		print "done.]";

		return array($new_package, $old_package, $extensions_to_update, $contradictions);
	}

	/**
	 * Install extensions.
	 *
	 * @param array(descriptor, minVersion, maxVersion) $extensions_to_update
	 * @param int fromVersion Update from this version
	 */
	private function installOrUpdatePackages($extensions_to_update) {
		$d = new HttpDownload();
		$localPackages = PackageRepository::getLocalPackages($this->rootDir);
		$num = 0;
		foreach($extensions_to_update as $arr) {
			list($desc, $min, $max) = $arr;
			$id = $desc->getID();
			// log extension for possible rollback

			// apply deploy descriptor and save local settings
			$fromVersion = array_key_exists($desc->getID(), $localPackages) ? $localPackages[$desc->getID()]->getVersion() : NULL;
			$fromPatchlevel = array_key_exists($desc->getID(), $localPackages) ? $localPackages[$desc->getID()]->getPatchlevel() : NULL;
			if (!is_null($fromVersion)) {
				$desc->createConfigElements($fromVersion, $fromPatchlevel);
			}
			if (!$this->noRollback) {
				$success = $this->rollback->saveInstallation();
				if (!$success) {
					throw new InstallationError(DEPLOY_FRAMEWORK_CREATING_RESTOREPOINT_FAILED, "Could not copy the installation");
				}
				if (count($desc->getInstallScripts()) > 0) {
					$success = $this->rollback->saveDatabase();
					if (!$success) {
						throw new InstallationError(DEPLOY_FRAMEWORK_CREATING_RESTOREPOINT_FAILED, "Could not save the database.");
					}
				}
			}


			list($url,$repo_url) = PackageRepository::getVersion($id, $desc->getVersion());
			$credentials = PackageRepository::getCredentials($repo_url);

			$this->logger->info("Download $id-".$desc->getVersion().".zip");
			$d->downloadAsFileByURL($url, $this->tmpFolder."/$id-".$desc->getVersion().".zip", $credentials);

			// unzip
			$this->logger->info("Unzip $id-".$desc->getVersion().".zip");
			$this->unzip($desc);

			$this->logger->info("Apply configs for $id-".$desc->getVersion().".zip");
			$desc->applyConfigurations($this->rootDir, false, $fromVersion, $this);
			$this->errors = array_merge($this->errors, $desc->getLastErrors());

			$installDirectory = $this->rootDir."/".$desc->getInstallationDirectory();
			if ($desc->isNonPublic()) {
				$installDirectory = Tools::getProgramDir()."/Ontoprise/".$desc->getInstallationDirectory();
				Tools::mkpath($installDirectory);
			}

			$handle = fopen($installDirectory."/init$.ext", "w");
			fwrite($handle, $num.",".$fromVersion);
			fclose($handle);
			$num++;

			print "\n-------\n";
		}


	}

	/**
	 * Runs the setups scripts of the extensions and installs all resource files and wikidumps.
	 *
	 * Note: requires wiki context when called.
	 *
	 */
	public function initializePackages() {
		require_once 'DF_ResourceInstaller.php';
		require_once 'DF_OntologyInstaller.php';
		$res_installer = ResourceInstaller::getInstance($this->rootDir);
		$ont_installer = OntologyInstaller::getInstance($this->rootDir);
		$localPackages = PackageRepository::getLocalPackagesToInitialize($this->rootDir);
		ksort($localPackages, SORT_NUMERIC);

		if (count($localPackages) === 0) {
			print "\nNo finalization required.\n";
			return;
		}
		// apply the setup operations which must not happen
		// before all extensions are updated
		foreach($localPackages as $tupl) {
			list($desc, $fromVersion) = $tupl;
			try {
				$this->logger->info("Apply setups for: ".$desc->getID());
				$desc->applySetups($this->rootDir, false);
			} catch(RollbackInstallation $e) {
				// ignore here
			}
		}

		// do the actual work
		global $dfgForce, $dfgNoConflict;
		foreach($localPackages as $tupl) {
			list($desc, $fromVersion) = $tupl;
			if ($dfgForce) {
				$mode = DF_ONTOLOGYIMPORT_FORCEOVERWRITE;
			} else if ($dfgNoConflict) {
				$mode = DF_ONTOLOGYIMPORT_STOPONCONFLICT;
			} else {
				$mode = DF_ONTOLOGYIMPORT_ASKINTERACTIVELY;
			}
			$ont_installer->installOntologies($desc, $this, $mode);
			$res_installer->installOrUpdateResources($desc);
			$res_installer->installOrUpdateWikidumps($desc, $fromVersion, $this->force ? DEPLOYWIKIREVISION_FORCE : DEPLOYWIKIREVISION_WARN);
			$res_installer->installOrUpdateMappings($desc);
		}

		// print (optional) notices
		$shownNotices=array();
		foreach($localPackages as $tupl) {
			list($desc, $fromVersion) = $tupl;
			$notice = $desc->getNotice();
			if ($notice !== '') {
				$notice = trim($notice);
				if (!in_array($notice, $shownNotices)) {
					print "\n\n=========================================================";
					print "\nNOTICE (".$desc->getID()."): $notice";
					print "\n\n=========================================================";
					$shownNotices[] = $notice;
				}
			}
		}

		// remove installation hint files
		print "\n[Clean up...";
		foreach($localPackages as $tupl) {
			list($desc, $fromVersion) = $tupl;
			$installDirectory = $this->rootDir."/".$desc->getInstallationDirectory();
			if ($desc->isNonPublic()) {
				$installDirectory = Tools::getProgramDir()."/Ontoprise/".$desc->getInstallationDirectory();
				Tools::mkpath($installDirectory);
			}
			$this->logger->info("Mark extension as initialized: ".$desc->getID());
			unlink($installDirectory."/init$.ext");
		}
		print "done.]\n\n";

	}

	/**
	 * Deinitializes the package, ie.
	 *
	 * 	Note: requires wiki context when called.
	 *
	 * 	(1) deinstall ontologies
	 *  (2) deinstall resources
	 *
	 * @param DeployDescriptor $dd
	 */
	public function deinitializePackages($dd) {

		$res_installer = ResourceInstaller::getInstance($this->rootDir);
		$ont_installer = OntologyInstaller::getInstance($this->rootDir);

		// remove wikidumps
		$this->logger->info("De-installing wikidumps: ".$dd->getID());
		print "\n[De-installing wikidumps...";
		$res_installer->deinstallWikidump($dd);
		print "\ndone.]";

		// remove ontologies
		$this->logger->info("De-installing ontologies: ".$dd->getID());
		print "\n[De-installing ontologies...";
		$ont_installer->deinstallOntology($dd);
		print "\ndone.]";

		// delete resources
		$this->logger->info("Delete resourcs: ".$dd->getID());
		print "\n[Deleting resources...";
		$res_installer->deleteResources($dd);
		print "\ndone.]";

	}

	/**
	 * Deletes codefiles which are *not* located in the installation directory.
	 *
	 * @param DeployDescriptor $dd
	 */
	public function deleteExternalCodefiles($dd) {

		if (count($dd->getCodefiles()) ==  0) return;
		$codefiles = $dd->getCodefiles();
		print "\n[Deleting external codefiles...";
		foreach($codefiles as $f) {
			if (strpos($f, $dd->getInstallationDirectory()) === 0) continue; // ignore these
			print "\n\t[Remove $f...";
			$path = $this->rootDir."/".$dd->getInstallationDirectory()."/".$f;
			if (is_dir($path)) {
				Tools::remove_dir($path);
			} else if (file_exists($path)) {
				unlink($path);
			}
			print "done.]";
		}
		print "\ndone.]";
	}

	/**
	 * Unzips the package denoted by $id and $version
	 *
	 *  (requires unzip installed on Windows, on Linux)
	 *
	 * @param string $id
	 * @param int $version
	 */
	private function unzip($dd) {
		$id = $dd->getID();
		$version =	$dd->getVersion();
		$excludedFiles = $dd->getExcludedFiles();
		$excludedFilesString = "";
		if (count($excludedFiles) > 0) {
			$excludedFilesString = "-x ".implode(" ", $excludedFiles); // FIXME: quote, could contain whitespaces
		}

		$unzipDirectory = $this->rootDir;
		if ($dd->isNonPublic()) {
			$unzipDirectory = Tools::getProgramDir()."/Ontoprise";
			Tools::mkpath($unzipDirectory);
		}

		print "\n[unzip ".$id."-$version.zip...";
		if (Tools::isWindows()) {
			exec('unzip -o "'.$this->tmpFolder."\\".$id."-$version.zip\" -d \"".$unzipDirectory.'" '.$excludedFilesString);
		} else {
			exec('unzip -o "'.$this->tmpFolder."/".$id."-$version.zip\" -d \"".$unzipDirectory.'" '.$excludedFilesString);
		}
		print "done.]";
	}

	/**
	 * Unzips the given bundle.
	 *
	 * @param $filePath of bundle (absolute or relative)
	 */
	private function unzipFromFile($filePath) {

		$unzipDirectory = $this->rootDir;
		if ($dd->isNonPublic()) {
			$unzipDirectory = Tools::getProgramDir()."/Ontoprise";
			Tools::mkpath($unzipDirectory);
		}

		print "\n[unzip ".$filePath."...";
		exec('unzip -o "'.$filePath.'" -d "'.$unzipDirectory.'"');
		print "done.]";

	}



	/**
	 * Calculates for any extension individually the possible interval of version,
	 * which can be installed. Removes all packages which can not be
	 * installed because there are contradicting requirements in terms of version numbers,
	 * ie. min version > max version.
	 *
	 * @param array($dd, $min, $max) $updatesNeeded
	 * @param array(id=>array($dd, $min, $max)) $extensions_to_update
	 */
	private function filterIncompatiblePackages($updatesNeeded, & $extensions_to_update, & $contradictions) {
		$extensions = array();

		$removed_extensions = array();
		$extensions_to_update = array();
		$contradictions = array();
		// calculate the intersection of min/max version number of each package
		foreach($updatesNeeded as $un) {
			list($dd, $from, $to) = $un;
			if (!array_key_exists($dd->getID(), $extensions)) {

				$extensions[$dd->getID()] = array($dd, $from, $to);
			} else {
				list($dd2, $min, $max) = $extensions[$dd->getID()];
				if ($from > $min) $min = $from;
				if ($to < $max) $max = $to;
				$extensions[$dd->getID()] = array($dd2, $min, $max);
			}
		}

		// remove all packages with min > max, ie. they can not be installed
		// because no suitable version exists.
		// remove also all super-packages of such.
		do {
			$lastSize = count($removed_extensions);
			foreach($extensions as $id => $e) {
				list($un, $min, $max) = $e;
				if ($min <= $max) {
					$deps = $un->getDependencies();
					foreach($deps as $dep) {
						list($depID, $from, $to, $optional, $message) = $dep;
						if ($optional) continue;
						if (array_key_exists($depID, $removed_extensions)) {
							$removed_extensions[$id] = $un;
						}

					}
				} else {
					$removed_extensions[$id] = $un;
					$contradictions[] = array($id, $min, $max);
				}
			}
		} while(count($removed_extensions) > $lastSize);

		foreach($removed_extensions as $id => $re) {
			unset($extensions[$id]);
		}

		// sort the packages list topologically according to their dependency graph.
		$precedenceOrder = $this->sortForDependencies($extensions);
		foreach($precedenceOrder as $po) {
			$extensions_to_update[$po] = $extensions[$po];
		}

	}

	/**
	 * Provides a topologic sorting based on the dependency graph.
	 *
	 * @param array(ID=>array($dd,$min,$max)) $extensions_to_update
	 * @return array(ID)
	 */
	private function sortForDependencies(& $extensions_to_update) {
		$sortedPackages = array();
		$vertexes = array_keys($extensions_to_update);
		$descriptors = array_values($extensions_to_update);
		while (!empty($vertexes)) {
			$cycle = true;
			foreach($vertexes as $v) {
				$hasPreceding = false;
				foreach($descriptors as $e) {
					list($dd, $from, $to) = $e;
					if (in_array($dd->getID(), $vertexes)) {
						if ($dd->hasDependency($v)) {
							$hasPreceding = true;
							break;
						}
					}
				}
				if (!$hasPreceding) {
					$cycle = false;
					break;
				}
			}
			if (!$hasPreceding) {
				// remove $v from $vertexes
				$vertexes = array_diff($vertexes, array($v));

			}
			$sortedPackages[] = $v;

			if ($cycle) throw new InstallationError(DEPLOY_FRAMEWORK_PRECEDING_CYCLE, "Cycle in the dependency graph.");
		}
		return array_reverse($sortedPackages);
	}





	/**
	 * Checks for updates on depending extensions if the package described by $dd would be installed.
	 * Goes recursively down the dependency tree.
	 *
	 * @param DeployDescriptor $dd
	 * @param array $packagesToUpdate
	 * @param array of DeployDescriptor $localPackages
	 * @param boolean $globalUpdate Global update or not (-u)
	 */
	private function collectDependingExtensions($dd, & $packagesToUpdate, $localPackages, $globalUpdate = false) {
		$dependencies = $dd->getDependencies();
		$updatesNeeded = array();

		// find packages which need to get updated
		// or installed.

		foreach($dependencies as $dep) {
			list($id, $from, $to, $optional, $message) = $dep;
			if ($optional && !$globalUpdate) {
				// ask for installation of optional packages
				// do not ask if it is a global update or if it already exists.
				if (array_key_exists($id, $localPackages)) {
					continue;
				}
				$this->getUserConfirmation("$message\nInstall optional extension '$id'? ", $result);
				if ($result != 'y') {
					continue;
				}
			}
			$packageFound = false;
			foreach($localPackages as $p) {
				if ($id === $p->getID()) {
					$packageFound = true;
					if ($p->getVersion() < $from) {

						$updatesNeeded[] = array($id, $from, $to);
					}
					if ($p->getVersion() > $to) {

						throw new InstallationError(DEPLOY_FRAMEWORK_INSTALL_LOWER_VERSION, "Requires '$id' to be installed at most in version ".Tools::addVersionSeparators(array($to,0)).". Downgrades are not supported.");
					}
				}
			}
			if (!$packageFound) {
				// package was not installed at all.

				$updatesNeeded[] = array($id, $from, $to);
			}
		}

		// get minimally required versions of packages to upgrade or install
		// and check if other extensions depending on them
		// need to get updated too.
		foreach($updatesNeeded as $up) {
			list($id, $minVersion, $maxVersion) = $up;

			$desc_min = PackageRepository::getDeployDescriptorFromRange($id, $minVersion, $maxVersion);

			$packagesToUpdate[] = array($desc_min, $minVersion, $maxVersion);
			$this->collectDependingExtensions($desc_min, $packagesToUpdate, $localPackages, $globalUpdate);
		}

	}

	/**
	 * Checks for updates on super extensions if the package described by $dd would be installed.
	 * Goes recursively up the dependency tree.
	 *
	 * @param DeployDescriptor $dd
	 * @param array $packagesToUpdate
	 * @param array of DeployDescriptor $localPackages
	 */
	private function collectSuperExtensions($dd, & $packagesToUpdate, $localPackages) {

		foreach($localPackages as $p) {

			// check if a local extension has $dd as a dependency
			$dep = $p->getDependency($dd->getID());
			if ($dep == NULL) continue;
			list($id, $from, $to) = $dep;

			// if $dd's version exceeds the limit of the installed,
			// try to find an update
			if ($dd->getVersion() > $to) {
				$versions = PackageRepository::getAllVersions($p->getID());
				// iterate through the available versions
				$updateFound = false;
				foreach($versions as $v) {
					$ptoUpdate = PackageRepository::getDeployDescriptor($p->getID(), $v);
					list($id_ptu, $from_ptu, $to_ptu) = $ptoUpdate->getDependency($p->getID());
					if ($from_ptu <= $dd->getVersion() && $to_ptu >= $dd->getVersion()) {

						$packagesToUpdate[] = array($p, $from_ptu, $to_ptu);
						$updateFound = true;
						break;
					}
				}
				if (!$updateFound) throw new InstallationError(DEPLOY_FRAMEWORK_COULD_NOT_FIND_UPDATE, "Could not find update for: ".$p->getID());

				$this->collectSuperExtensions($p, $packagesToUpdate, $localPackages);
			}
		}

	}

	/**
	 * Callback method. Reads user for required parameters.
	 *
	 *
	 * @param array($name=>(array($type, $description)) $userParams
	 * @param out array($name=>$value) $mapping
	 *
	 */
	public function getUserReqParams($userParams, & $mapping) {
		if ($this->noAsk || count($userParams) == 0) return;
		print "\n\nRequired parameters:";
		foreach($userParams as $name => $up) {
			list($type, $desc, $proposal) = $up;
			if (!is_null($proposal) && $proposal != '') {
				$parts = explode(":", $proposal);
				if (count($parts) > 1) {
					switch($parts[0]) {
						case "search": {
							$proposal = Tools::whereis(trim($parts[1]));
							$proposal = trim($proposal);
							break;
						}
						default:
							$proposal = '';
							break;
					}
				}
			}

			// use proposal if given
			if (!is_null($proposal) && $proposal != '') {
				$mapping[$name] = $proposal;
			} else {
				print "\n$desc\n";
				print "$name ($type): ";
				$line = trim(fgets(STDIN));
				$line = str_replace("\\", "/", $line); // do not allow backslashes
				$mapping[$name] = $line;
			}
		}

	}

	/**
	 * Callback method. Requests a confirmation by the user.
	 *
	 *
	 * @param string $message
	 * @param out boolean $result
	 * @return unknown
	 */
	public function getUserConfirmation($message, & $result) {
		if ($this->noAsk) return 'y';
		print "\n\n$message [ (y)es/(n)o ]";
		$line = trim(fgets(STDIN));
		$result = strtolower($line);
	}

	public function askForOntologyPrefix(& $result) {
		print "\n\nOntology conflict. Please enter prefix: ";
		$line = trim(fgets(STDIN));
		$result = $line;
	}

	public function getErrors() {
		return $this->errors;
	}

	public function downloadProgres($percentage) {
		// do nothing
	}
	public function downloadFinished($filename) {
		// do nothing
	}

}

class InstallationError extends Exception {

	var $msg;
	var $arg1;
	var $arg2;

	public function __construct($errCode, $msg = '', $arg1 = NULL, $arg2 = NULL) {
		$this->errCode = $errCode;
		$this->msg = $msg;
		$this->arg1 = $arg1;
		$this->arg2 = $arg2;
	}

	public function getMsg() {
		return $this->msg;
	}

	public function getErrorCode() {
		return $this->errCode;
	}

	public function getArg1() {
		return $this->arg1;
	}

	public function getArg2() {
		return $this->arg2;
	}
}
