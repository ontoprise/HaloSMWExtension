<?php

require_once( '../../maintenance/commandLine.inc' );
require_once('../io/import/DeployWikiImporter.php');
require_once('../io/import/BackupReader.php');

/**
 * Resource installer takes care about wikidump/resource (de-)installation.
 * 
 * @author Kai Kühn / ontoprise / 2009
 *
 */
class ResourceInstaller {

	static $instance; // singleton

	var $rootDir; // MW installation dir

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
					$a->doDeleteArticle("ontology update to ".$dd->ID()."-".$dd->getVersion());
				}
				$next = $res->getNext();
			}
		}

	}
	
	/**
	 * Deinstalls wikidumps contained in the given descriptor.
	 * @param $dd
	 * @return unknown_type
	 */
	public function deinstallWikidump($dd) {
		print "\nRemove ontologies...";
		$res = smwfGetStore()->getQueryResult("[[Part of bundle::".$dd->getID()."]]");
		$next = $res->getNext();
		while($next) {
			$title = Title::newFromText($next->getNextObject());
			if (!is_null($title)) {
				$a = new Article($title);
				print "\nRemove: ".$title->getPrefixedText();
				$a->doDeleteArticle("ontology removed: ".$dd->ID());
			}
			$next = $res->getNext();
		}
	}
	

	/**
	 * Deletes resources contained in the given descriptor.
	 * 
	 * @param $dd
	 * @return unknown_type
	 */
	public function deleteResources($dd) {
		$resources = $dd->getResources();
		foreach($resources as $file) {
			$title = Title::newFromText(basename($file), NS_IMAGE);
			$im_file = wfLocalFile($title);
			$im_file->delete("remove resource");
			$a = new Article($titleObj);
			$a->doDelete("remove resource");
		}
	}
	
	/**
	 * Checks if the page contained in the given package are modified and displays those. 
	 * @param $packageID
	 * @param $version
	 * @return unknown_type
	 */
	public function checkWikidump($packageID, $version) {
		if (!defined('SMW_VERSION')) throw new InstallationError(DEPLOY_FRAMEWORK_NOT_INSTALLED, "SMW is not installed although it is needed to check ontology status.");
		
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

	/**
	 * Installs the resources as uploaded files.
	 *
	 * @param $dd
	 * @param $fromVersion
	 * @return unknown_type
	 */
	public function installOrUpdateResources($dd, $fromVersion) {
		// resources files
		print "\nCopying resources...";
		$resources = $dd->getResources();
		foreach($resources as $file) {
			print "\nCopy $file...";
			if (is_dir($this->rootDir."/".$file)) {
				
				$this->importResources($this->rootDir."/".$file);
			} else {
				
				$im_file = wfLocalFile(Title::newFromText(basename($this->rootDir."/".$file), NS_IMAGE));
				$im_file->upload($this->rootDir."/".$file, "auto-inserted image", "noText");
			}
			print "done.";
		}
	}

	/**
	 * Import resources from the given directory
	 * Currently only images resources.
	 *
	 * @param $SourceDirectory
	 * @return unknown_type
	 */
	private function importResources($SourceDirectory) {
			
		if (basename($SourceDirectory) == "CVS") { // ignore CVS dirs
			return;
		}
		if (basename($SourceDirectory) == ".svn") { // ignore .svn dirs
			return;
		}
		// add trailing slashes
		if (substr($SourceDirectory,-1)!='/'){
			$SourceDirectory .= '/';
		}
			
		$handle = @opendir($SourceDirectory);
		if (!$handle) {
			print ("\nDirectory '$SourceDirectory' could not be opened.\n");
		}
		while ( ($entry = readdir($handle)) !== false ){

			if ($entry[0] == '.'){
				continue;
			}


			if (is_dir($SourceDirectory.$entry)) {
				// Unterverzeichnis
				$success = $this->importResources($SourceDirectory.$entry);

			} else{

					
				// simulate an upload
				$im_file = wfLocalFile(Title::newFromText(basename($SourceDirectory.$entry), NS_IMAGE));
				$im_file->upload($SourceDirectory.$entry, "auto-inserted image", "noText");
					
			}

		}
	}
	


}
?>