<?php

require_once( '../../maintenance/commandLine.inc' );
require_once('../io/import/DeployWikiImporter.php');
require_once('../io/import/BackupReader.php');

class ResourceInstaller {

	static $instance;

	var $rootDir;

	public static function getInstance($rootDir) {
		if (is_null(self::$instance)) {
			self::$instance = new ResourceInstaller($rootDir);
		}
		return self::$instance;
	}

	public function __construct($rootDir) {
		$this->rootDir = $rootDir;
	}

	/**
	 * Installs wiki dumps and other resources.
	 *
	 * @param DeployDescriptorParser $dd
	 */
	public function installOrUpdateWikidumps($dd, $fromVersion, $mode) {

		if (count($dd->getWikidumps()) ==  0) return;

		// check if smw is installed
		$localPackages = PackageRepository::getLocalPackages($this->rootDir.'/extensions', true);
		$smwInstalled = array_key_exists('smw', $localPackages);
		
		if ($smwInstalled && !defined('SMW_VERSION')) throw new InstallationError(DEPLOY_FRAMEWORK_NOT_INSTALLED, "SMW has been installed but it is not active. Please restart smwadmin to enable it.");

		// wiki dumps
		$reader = new BackupReader($mode);
		$wikidumps = $dd->getWikidumps();
		foreach($wikidumps as $file) {
			print "\nImport ontology: $file";
			$result = $reader->importFromFile( $this->rootDir."/".$file );
		}
		if (!is_null($fromVersion)) {
			// remove old pages
			print "\nRemove unused pages...";
			$res = smwfGetStore()->getQueryResult("[[Ontology version::$fromVersion]][[Part of bundle::".$dd->getID()."]]");
			$next = $res->getNext();
			while($next) {
				$title = Title::newFromText($next->getNextObject());
				if (!is_null($title)) {
					$a = new Article($title);
					print "\nRemove: ".$title->getPrefixedText();
					$a->doDeleteArticle("ontology update to ".$dd->getVersion());
				}
				$next = $res->getNext();
			}
		}

	}

	public function checkWikidump($packageID, $version) {
		$localPackages = PackageRepository::getLocalPackages($this->rootDir.'/extensions', true);
		$package = array_key_exists($packageID, $localPackages) ? $localPackages[$packageID] : NULL;
		$packageFound = !is_null($package) && ($package->getVersion() == $version || $version == NULL);
		if (!$packageFound) {
			throw new InstallationError(DEPLOY_FRAMEWORK_NOT_INSTALLED, "The specified package is not installed. Nothing to check.");
		}
        
		print "\n\nChecking ontology...";
		$reader = new BackupReader(DEPLOYWIKIREVISION_INFO);
		$wikidumps = $package->getWikidumps();
		foreach($wikidumps as $file) {
			$result = $reader->importFromFile( $this->rootDir."/".$file );
		}
	}

	public function installOrUpdateResources($dd, $fromVersion) {
		// resources files
		print "\nCopying resources...";
		$resources = $dd->getResources();
		foreach($resources as $file) {
			print "\nCopy $file...";
			if (is_dir($this->rootDir."/".$file)) {
				Tools::mkpath(dirname($this->rootDir."/images/".$file));
				Tools::copy_dir($this->rootDir."/".$file, $this->rootDir."/images/".$file);
			} else {
				Tools::mkpath(dirname($this->rootDir."/images/".$file));
				copy($this->rootDir."/".$file, $this->rootDir."/images/".$file);
			}
			print "done.";
		}
	}
}
?>