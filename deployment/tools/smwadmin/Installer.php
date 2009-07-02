<?php

// Error constants
define('DEPLOY_FRAMEWORK_INSTALL_LOWER_VERSION', 1);
define('DEPLOY_FRAMEWORK_NO_TMP_DIR', 2);
define('DEPLOY_FRAMEWORK_COULD_NOT_FIND_UPDATE', 3);
define('DEPLOY_FRAMEWORK_PACKAGE_NOT_EXISTS', 4);
define('DEPLOY_FRAMEWORK_DEPENDENCY_EXIST',5);
define('DEPLOY_FRAMEWORK_CODE_CHANGED',6);
define('DEPLOY_FRAMEWORK_MISSING_FILE', 7);
define('DEPLOY_FRAMEWORK_ALREADY_INSTALLED', 8);


require_once 'PackageRepository.php';
require_once 'Tools.php';

/**
 * Provides the basic installation routines for the smwadmin tool.
 *
 * @author: Kai Kühn / ontoprise / 2009
 *
 */



class Installer {

	/*
	 * Temporary folder for storing downloaded files
	 */
	static $tmpFolder;

	/*
	 * Mediawiki installation directory
	 */
	static $rootDir;

	/*
	 * Mediawiki versiohttp://www.heise.de/newsticker/foren/S-Re-Pendlerfamilien-werden-nicht-danken/forum-161573/msg-16980630/read/n
	 */
	static $mw_version;

	/*
	 * Installation directory
	 * Normally identical with $rootDir except for testing or dry runs.
	 */
	private $instDir;
	
	// dry run, ie. nothing is actually changed 
	private $dryRun;
	
	// force installation even on warnings
	private $force;
	
	// no questions (for testing)
	private $noAsk;


	/**
	 * Creates new Installer.
	 *
	 * @param string $rootDir Explicit root dir. Only necessary for testing
	 */
	public function __construct($rootDir = NULL, $dryRun = false, $force = false, $noAsk = false) {
		self::$tmpFolder = Tools::isWindows() ? 'c:\temp\mw_deploy_tool' : '/tmp/mw_deploy_tool';
		if (!file_exists(self::$tmpFolder)) Tools::mkpath(self::$tmpFolder);
		if (!file_exists(self::$tmpFolder)) {
			throw new InstallationError(DEPLOY_FRAMEWORK_NO_TMP_DIR, "Could not create temporary directory. Not Logged in as root?");
		}
		self::$rootDir = $rootDir === NULL ? realpath(dirname(__FILE__)."/../../../") : $rootDir;
		$this->instDir = $rootDir; // normally rootDir == instDir
		self::$mw_version = Tools::getMediawikiVersion(self::$rootDir);
		$this->dryRun = $dryRun;
		$this->force = $force;
		$this->noAsk = $noAsk;
	}

	public function setInstDir($instDir) {
		$this->instDir = $instDir;
	}

	/**
	 * Installs or updates a package.
	 *
	 * @param string $packageID
	 * @param int $version If omitted (or NULL), the latest version is installed.
	 */
	public function installOrUpdate($packageID, $version = NULL) {

		// 1. Check if package is installed
		print "\nCheck if package installed...";
		$localPackages = PackageRepository::getLocalPackages(self::$rootDir.'/extensions');
		$ext = NULL;
		foreach($localPackages as $p) {
			if ($p->getID() == $packageID) {
				$ext = $p;
				print "found!";
				break;
			}
		}
		
		if (is_null($ext)) {
			print "not found.";
		}

		if (!is_null($ext) && is_numeric($version) && $ext->getVersion() > $version) {
			throw new InstallationError(DEPLOY_FRAMEWORK_INSTALL_LOWER_VERSION, "Really install lower version? Use -f (force)", $ext);
		}
		
	    if (!is_null($ext) && is_numeric($version) && $ext->getVersion() == $version) {
            throw new InstallationError(DEPLOY_FRAMEWORK_ALREADY_INSTALLED, "Already installed. Nothing to do.", $ext);
        }

		// 2. Check code integrity of existing package
		if (!is_null($ext)) {
			print "\nCheck code integrity...";
			$status = $ext->validatecode(self::$rootDir);
			if ($status !== true) {
				if (!$this->force) {
					throw new InstallationError(DEPLOY_FRAMEWORK_CODE_CHANGED, "Code files were modified. Use -f (force)", $status);
				} else {
					print "\nWarning: Code files contain differences. Patches may get lost.";
				}
			}

	        print "done!";
		}
        
		// 3. Check dependencies for install/update
		// get package to install
		if ($version == NULL) {
			print "\nRead latest deploy descriptor of $packageID...";
			$dd = PackageRepository::getLatestDeployDescriptor($packageID);
		} else {
			print "\nRead deploy descriptor of $packageID-$version...";
			$dd = PackageRepository::getDeployDescriptor($packageID, $version);
		}
		$updatesNeeded = array();
		print "\nCheck for updates...";
		$this->checkForDependingExtensions($dd, $updatesNeeded, $localPackages);
		$this->checkForSuperExtensions($dd, $updatesNeeded, $localPackages);
        
		// 4. calculate version which matches all depdencies of an extension.
		print "\nCalculate matching versions";
		$this->calculateVersionRange($updatesNeeded, $extensions_to_update);

		// 5. Install/update all dependant and super extensions
        print "\nInstall dependant extensions or super extensions";		
		$this->installOrUpdatePackages($extensions_to_update, $localPackages);


		// 6. Install update this extension
		print "\nInstall ".$dd->getID()." or super extensions";       
		$this->installOrUpdatePackage($dd, $version, !is_null($ext) ? $ext->getVersion : NULL);


			
	}

	/**
	 * De-Installs extension
	 *
	 * @param string $packageID
	 */
	public function deInstall($packageID) {
		
		print "\nChecking for package $packageID...";
		$localPackages = PackageRepository::getLocalPackages(self::$rootDir.'/extensions');
		$ext = NULL;
		foreach($localPackages as $p) {
			if ($p->getID() == $packageID) {
				$ext = $p;
				break;
			}
		}
		if (is_null($ext)) {
			throw new InstallationError(DEPLOY_FRAMEWORK_PACKAGE_NOT_EXISTS, "Package does not exist", $packageID);
		}

		// check if there are depending extensions
		print "\nChecking for dependant packages of $packageID...";
		$existDependency = false;
		foreach($localPackages as $p) {
			$dependencies = $p->getDependencies();

			foreach($dependencies as $dep) {
				list($id, $from, $to) = $dep;
				if ($id == $packageID) {
					$existDependency = true;
				}
			}
		}

		if ($existDependency) {
			throw new InstallationError(DEPLOY_FRAMEWORK_DEPENDENCY_EXIST, "Can not remove package. Dependency to the following packages exists:", $packageID);
		}

		// undo all config changes
		// - from LocalSettings.php
		// - from database (setup scripts)
		// - patches
		print "\nUnapply configurations of $packageID...";
		$ext->unapplyConfigurations($this->instDir, false);
		
		// remove extension code
		print "\nRemove code of $packageID...";
		Tools::remove_dir($this->instDir."/".$ext->getInstallationDirectory());

	}

	public function updateAll() {

		$localPackages = PackageRepository::getLocalPackages(self::$rootDir.'/extensions');

		// get top level extensions, ie. those which have no super extensions.
		$topLevelExtensions = array();
		foreach($localPackages as $l1) {
			$hasDependency = false;
			foreach($localPackages as $l2) {
				$hasDependency |= ($l2->getDependency($l1->getID()));
				if ($hasDependency) break;
			}
			if (!$hasDependency) {
				$topLevelExtensions[] = $l1;
			}
		}

		$updatesNeeded = array();
		foreach($topLevelExtensions as $tl_ext) {
			$dd = PackageRepository::getLatestDeployDescriptor($tl_ext->getID());

			$this->checkForDependingExtensions($dd, $updatesNeeded, $localPackages);
		}

		$this->calculateVersionRange($updatesNeeded, $extensions_to_update);

		$this->installOrUpdatePackages($extensions_to_update, $localPackages);
	}
	/**
	 * Install extensions.
	 *
	 * @param array(descriptor, minVersion, maxVersion) $extensions_to_update
	 * @param int fromVersion Update from this version
	 */
	private function installOrUpdatePackages($extensions_to_update, $localPackages) {
		$d = new HttpDownload();
		foreach($extensions_to_update as $id=>$arr) {
			list($desc, $min, $max) = $arr;
			$url = PackageRepository::getVersion($id, $min);
			$d->downloadAsFileByURL($url, self::$tmpFolder."/$id-$min.zip");

			// unzip
			$this->unzip($id, $min);

			// apply deploy descriptor and save local settings
			$fromVersion = array_key_exists($desc->getID(), $localPackages) ? $localPackages[$desc->getID()]->getVersion() : NULL;
			$desc->applyConfigurations($this->instDir, $this->dryRun, $fromVersion, $this);
				
				
			$this->installResources($desc);
			print "\n-------\n";
		}
	}

	/**
	 * Install extension
	 *
	 * @param descriptor $dd
	 * @param int $version
	 */
	private function installOrUpdatePackage($dd, $version, $fromVersion) {
		$d = new HttpDownload();
		if (is_null($version)) {
			list($url,$version) = PackageRepository::getLatestVersion($dd->getID());

		} else {
			$url = PackageRepository::getVersion($dd->getID(), $version);
		}
		$d->downloadAsFileByURL($url, self::$tmpFolder."/".$dd->getID()."-$version.zip");

		// unzip
		$this->unzip($dd->getID(), $version);

		// apply deploy descriptor
		$dd->applyConfigurations($this->instDir, $this->dryRun, $fromVersion, $this);

		// install wiki pages
		$this->installResources($dd);

	}
    
	/**
	 * Installs wiki dumps and other resources.
	 *
	 * @param DeployDescriptorParser $dd
	 */
	private function installResources($dd) {
		
		// wiki dumps
		require_once( '../../maintenance/commandLine.inc' );
		require_once('../io/import/DeployWikiImporter.php');
		require_once('../io/import/BackupReader.php');
		$reader = new BackupReader($this->force ? DEPLOYWIKIREVISION_FORCE : DEPLOYWIKIREVISION_WARN);
		$wikidumps = $dd->getWikidumps();
		foreach($wikidumps as $file) {
			if (!$this->dryRun) $result = $reader->importFromFile( self::$rootDir."/".$file );
		}
		
		// resources files
		$resources = $dd->getResources();
	       foreach($resources as $file) {
            if (!$this->dryRun) {
            	if (is_dir(self::$rootDir."/".$file)) {
            		Tools::mkpath(dirname(self::$rootDir."/images/".$file));
            		Tools::copy_dir(self::$rootDir."/".$file, self::$rootDir."/images/".$file);
            	} else {
            		Tools::mkpath(dirname(self::$rootDir."/images/".$file));
            		copy(self::$rootDir."/".$file, self::$rootDir."/images/".$file);
            	}
            }
        }
	}

	/**
	 * Unzips the package denoted by $id and $version
	 *
	 *  (requires 7-zip installed on Windows, unzip on Linux)
	 *
	 * @param string $id
	 * @param int $version
	 */
	private function unzip($id, $version) {
		if ($this->dryRun) return;
		if (Tools::isWindows()) {
			print "\n\nUncompressing:\n7z x -y -o".$this->instDir." ".self::$tmpFolder."\\".$id."-$version.zip";
			exec('7z x -y -o'.$this->instDir." ".self::$tmpFolder."\\".$id."-$version.zip");
		} else {
			print "\n\nUncompressing:\nunzip ".self::$tmpFolder."/".$id."-$version.zip -d ".$this->instDir;
			exec('unzip '.self::$tmpFolder."/".$id."-$version.zip -d ".$this->instDir);
		}
	}

	/**
	 * Calculates for any extension individually the interval of min/max version, so that it is a subset of all.
	 *
	 * @param array($dd, $min, $max) $updatesNeeded
	 * @param array(id=>array($dd, $min, $max)) $extensions_to_update
	 */
	private function calculateVersionRange($updatesNeeded, & $extensions_to_update) {
		$extensions_to_update = array();
		foreach($updatesNeeded as $un) {
			list($un, $from, $to) = $un;
			if (!array_key_exists($un->getID(), $extensions_to_update)) {

				$extensions_to_update[$un->getID()] = array($un, $from, $to);
			} else {
				list($min, $max) = $extensions_to_update[$un->getID()];
				if ($from > $min) $min = $from;
				if ($to < $max) $max = $to;
				$extensions_to_update[$un->getID()] = array($un, $min, $max);
			}
		}
	}



	public function downloadProgres($percentage) {
		// do nothing
	}
	public function downloadFinished($filename) {
		// do nothing
	}

	/**
	 * Checks for updates on depending extensions if the package described by $dd would be installed.
	 * Goes recursively down the dependency tree.
	 *
	 * @param DeployDescriptorParser $dd
	 * @param array $packagesToUpdate
	 * @param array of DeployDescriptorParser $localPackages
	 */
	private function checkForDependingExtensions($dd, & $packagesToUpdate, $localPackages) {
		$dependencies = $dd->getDependencies();
		$updatesNeeded = array();
        
		// find packages which need to get updated
		// or installed.
		foreach($dependencies as $dep) {
			list($id, $from, $to) = $dep;
		    $packageFound = false;
			foreach($localPackages as $p) {
				if ($id === $p->getID()) {
					$packageFound = true;
					if ($p->getVersion() < $from) {
						print "\nNeed update: ".$p->getID()." ($from-$to)";
						$updatesNeeded[] = array($p->getID(), $from, $to);
					}
					if ($p->getVersion() > $to) {
						print "\nNeed downgrade: ".$p->getID()." ($from-$to)";
						$updatesNeeded[] = array($p->getID(), $from, $to);
					}
				}
			}
			if (!$packageFound) {
				// package was not installed at all.
				print "\nNeed installation: ".$id." ($from-$to)";
				$updatesNeeded[] = array($id, $from, $to);
			}
		}

		// get minimally required versions of packages to upgrade or install
		// and check if other extensions depending on them
		// need to get updated too.
		foreach($updatesNeeded as $up) {
			list($id, $minVersion, $maxVersion) = $up;
			$dd = PackageRepository::getDeployDescriptor($id, $minVersion);

			$packagesToUpdate[] = array($dd, $minVersion, $maxVersion);
			$this->checkForDependingExtensions($dd, $packagesToUpdate, $localPackages);
		}
	   
	}

	/**
	 * Checks for updates on super extensions if the package described by $dd would be installed.
	 * Goes recursively up the dependency tree.
	 *
	 * @param DeployDescriptorParser $dd
	 * @param array $packagesToUpdate
	 * @param array of DeployDescriptorParser $localPackages
	 */
	private function checkForSuperExtensions($dd, & $packagesToUpdate, $localPackages) {
		$updatesNeeded = array();
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
				foreach($version as $v) {
					$ptoUpdate = PackageRepository::getDeployDescriptor($p->getID(), $v);
					list($id_ptu, $from_ptu, $to_ptu) = $ptoUpdate->getDependency($p->getID());
					if ($from_ptu <= $dd->getVersion() && $to_ptu >= $dd->getVersion()) {
						print "\nNeed update ".$p->getID()." ($from_ptu-$to_ptu)";
						$updatesNeeded[] = array($p, $from_ptu, $to_ptu);
						$updateFound = true;
						break;
					}
				}
				if (!$updateFound) throw new InstallationError(DEPLOY_FRAMEWORK_COULD_NOT_FIND_UPDATE, "Could not find update for: ".$p->getID());
				$packagesToUpdate = array_merge($packagesToUpdate, $updatesNeeded);
				$this->checkForSuperExtensions($p, $packagesToUpdate, $localPackages);
			}
		}

	}

	/**
	 * Callback method. Reads user for required parameters.
	 *
	 * @param array($name=>(array($type, $description)) $userParams
	 * @param array($name=>$value) $mapping
	 *
	 */
	public function getUserReqParams($userParams, & $mapping) {
		if ($this->noAsk || count($userParams) == 0) return;
		print "\n\nRequired parameters:";
		foreach($userParams as $name => $up) {
			list($type, $desc) = $up;
			print "\n$desc\n";
			print "$name ($type): ";
			$line = trim(fgets(STDIN));
			$mapping[$name] = $line;
		}

	}

	/**
	 * Checks the integrity of the package.
	 *
	 * @param DeployDescriptorParser $ext
	 */
	private function checkIntegrity($ext) {

		$dumps = $ext->getWikidumps();
		foreach($dumps as $loc) {
			if (!is_dir(self::$rootDir."/".$loc) && !file_exists(self::$rootDir."/".$loc)) {
				throw new InstallationError(DEPLOY_FRAMEWORK_MISSING_FILE, "Missing file or directory.", self::$rootDir."/".$loc);
			}
		}
		$resources = $ext->getResources();
		foreach($dumps as $loc) {
			if (!is_dir(self::$rootDir."/".$loc) && !file_exists(self::$rootDir."/".$loc)) {
				throw new InstallationError(DEPLOY_FRAMEWORK_MISSING_FILE, "Missing file or directory.", self::$rootDir."/".$loc);
			}
		}
	}
}

class InstallationError extends Exception {

	var $msg;
	var $arg1;
	var $arg2;

	public function __construct($errCode, $msg = '', $arg1 = NULL, $arg2 = NULL) {
		$this->errCode = $errCode;
		$this->msg = $msg;
		$this->arg1;
		$this->arg2;
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
?>