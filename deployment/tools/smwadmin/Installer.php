<?php

define('DEPLOY_FRAMEWORK_DOWNGRADE_NEEDED', 0);
define('DEPLOY_FRAMEWORK_INSTALL_LOWER_VERSION', 1);
define('DEPLOY_FRAMEWORK_NO_TMP_DIR', 2);

require_once 'PackageRepository.php';
require_once 'Tools.php';

/**
 * Provides the basic installation routines for the smwadmin tool.
 *
 * @author: Kai Kühn / ontoprise / 2009
 *
 */



class Installer {

	static $tmpFolder;
	static $rootDir;
    
	private $instDir;
	  /**
	   * Creates new Installer.
	   * 
	   * @param string $rootDir Explicit root dir. Only necessary for testing
	   */
	public function __construct($rootDir = NULL) {
		self::$tmpFolder = Tools::isWindows() ? 'c:\temp\mw_deploy_tool' : '/tmp/mw_deploy_tool';
		if (!file_exists(self::$tmpFolder)) Tools::mkpath(self::$tmpFolder);
		if (!file_exists(self::$tmpFolder)) {
			throw new InstallationError(DEPLOY_FRAMEWORK_NO_TMP_DIR, "Could not create temporary directory. Not Logged in as root?");
		}
		self::$rootDir = $rootDir === NULL ? realpath(dirname(__FILE__)."/../../../") : $rootDir;
	}
    
	public function setInstDir($instdir) {
		$this->instDir = $instDir;
	}

	public function installPackage($packageID, $version = NULL) {

		// 1. Connect to package repository.
		//PackageRepository::getPackageRepository();

		// 2. Check if package is installed
		$localPackages = PackageRepository::getLocalPackages(self::$rootDir.'/extensions');
		$ext = NULL;
		foreach($localPackages as $p) {
			if ($p->getID() == $packageID) {
				$ext = $p;
				break;
			}
		}



		// 3. Check dependencies for install/update
		// get package to install
		if ($version == NULL) {
			$dd = PackageRepository::getLatestDeployDescriptor($packageID);
		} else {
			$dd = PackageRepository::getDeployDescriptor($packageID, $version);
		}


			$updatesNeeded = array();
		if (is_null($ext)) {
			// new installation
			$this->checkForDependingExtensions($dd, $updatesNeeded, $localPackages);
			$this->checkForSuperExtensions($dd, $updatesNeeded, $localPackages);

		} else {

			// check version if lower
			if (is_numeric($version) && $ext->getVersion() > $version) {
				throw new InstallationError(DEPLOY_FRAMEWORK_INSTALL_LOWER_VERSION, "Really install lower version?", $ext);
			}
			// update
			$this->checkForDependingExtensions($dd, $updatesNeeded, $localPackages);
			$this->checkForSuperExtensions($dd, $updatesNeeded, $localPackages);
		}

		// calculate version which matches all depdencies of an extension.
		$extensions_to_update = array();
		foreach($updatesNeeded as $un) {
			list($un, $from, $to) = $un;
			if (!array_key_exists($un->getID(), $extensions_to_update)) {
				
				$extensions_to_update[$un->getID()] = array($from, $to);
			} else {
				list($min, $max) = $extensions_to_update[$un->getID()];
				if ($from > $min) $min = $from;
				if ($to < $max) $max = $to;
				$extensions_to_update[$un->getID()] = array($min, $max);
			}
		}

		// 4. Install/update all dependant extensions
		$d = new HttpDownload();
		foreach($extensions_to_update as $id=>$arr) {
			$url = PackageRepository::getVersion($id, $arr[0]); // 0: min, 1: max
			$d->downloadAsFileByURL($url, self::$tmpFolder."/$id-$arr[0].zip");
			 
			// unzip (requires 7-zip installed)
			exec('7z x -o'.$this->instDir." ".self::$tmpFolder."/$id-$arr[0].zip");
			 
			// apply deploy descriptor
		}
		
        /*
		// 5. Install update this extension
		$url = $this->getVersion($dd->getID());
		$d->downloadAsFileByURL($url, self::$tmpFolder."/".$dd->getID()."zip");

		// unzip (requires 7-zip installed)
		exec('7z x -o'.self::$rootDir." ".self::$tmpFolder."/".$dd->getID()."zip");
*/
		// apply deploy descriptor
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
		foreach($dependencies as $dep) {
			list($id, $from, $to) = $dep;
			foreach($localPackages as $p) {
				if ($id === $p->getID()) {
					if ($p->getVersion() < $from) {
						$updatesNeeded[] = array($p, $from, $to);
					}
					if ($p->getVersion() > $to) {
						throw new InstallationError(DEPLOY_FRAMEWORK_DOWNGRADE_NEEDED, "Downgrade needed", $p, $to);
					}
				}
			}
		}

		// get minimally required versions of packages to upgrade
		// and check if other extensions depending on them
		// need to get updated too.
		foreach($updatesNeeded as $up) {
			list($p, $minVersion, $maxVersion) = $up;
			$dd = PackageRepository::getDeployDescriptor($p->getID(), $minVersion);
			$packagesToUpdate = array_merge($packagesToUpdate, $updatesNeeded);
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

			if ($dd->getVersion() > $to) {
				$versions = PackageRepository::getAllVersions($p->getID());
				foreach($version as $v) {
					$ptoUpdate = PackageRepository::getDeployDescriptor($p->getID(), $v);
					list($id_ptu, $from_ptu, $to_ptu) = $ptoUpdate->getDependency($p->getID());
					if ($from_ptu > $from && $to_ptu < $to) {
						$updatesNeeded[] = array($p, $from_ptu, $to_ptu);
						break;
					}
				}
				$packagesToUpdate = array_merge($packagesToUpdate, $updatesNeeded);
				$this->checkForSuperExtensions($p, $packagesToUpdate, $localPackages);
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
}
?>