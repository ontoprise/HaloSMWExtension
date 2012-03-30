<?php
/*
 * Copyright (C) Vulcan Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program.If not, see <http://www.gnu.org/licenses/>.
 *
 */

global $rootDir;
require_once($rootDir.'/../maintenance/commandLine.inc' );
require_once($rootDir.'/io/import/DF_DeployWikiBundleImporter.php');
require_once($rootDir.'/io/import/DF_BackupReader.php');
require_once($rootDir.'/io/DF_BundleTools.php');

/**
 * @file
 * @ingroup DFInstaller
 *
 * Resource installer takes care about wikidump/resource (de-)installation.
 *
 * @author Kai K�hn
 *
 */
class ResourceInstaller {

	static $instance; // singleton

	var $rootDir; // MW installation dir
	var $logger;

	public static function getInstance($rootDir) {
		if (is_null(self::$instance)) {
			self::$instance = new ResourceInstaller($rootDir);
		}
		return self::$instance;
	}

	public function __construct($rootDir) {
		$this->rootDir = $rootDir;
		$this->logger = Logger::getInstance();
	}

	/**
	 * Installs wiki dumps and other resources.
	 *
	 * @param DeployDescriptor $dd
	 * @param DFVersion $fromVersion
	 * @param int $mode
	 */
	public function installOrUpdateWikidumps($dd, $fromVersion, $mode) {
		global $wgUser, $dfgOut;
		if (count($dd->getWikidumps()) ==  0) return;


		if (!defined('SMW_VERSION')) throw new InstallationError(DEPLOY_FRAMEWORK_NOT_INSTALLED, "SMW is not installed or at least is not active. The ontology could not be properly installed. Please restart smwadmin using -f (force) to install it.");

		// import new wiki pages
		$reader = new BackupReader($mode, $dd->getID());
		$wikidumps = $dd->getWikidumps();

		foreach($wikidumps as $file) {
			$dumpPath = $this->rootDir."/". $dd->getInstallationDirectory()."/".$file;
				
			if (!file_exists($dumpPath)) {
				$this->logger->warn("dump file '".$dumpPath."' does not exist.");
				$dfgOut->outputln("\tdump file '".$dumpPath."' does not exist. Ignore it.", DF_PRINTSTREAM_TYPE_WARN);
				continue;
			}
			// remove old pages
			if (!is_null($fromVersion) && $fromVersion != '') {
				// remove old pages
				$this->logger->info("\n[Removing unused pages from ".$dd->getID());
				$dfgOut->outputln("[Removing unused pages from ".$dd->getID());

				$verificationLog = $this->getPagesFromImport($dumpPath, $dd->getID());
				$this->removeOldPages($dd->getID(), $verificationLog);
				$dfgOut->outputln( "done.]");
			}

			$this->logger->info("Importing dump: $file");
			$dfgOut->outputln("[Importing dump: $file");
				
			$result = $reader->importFromFile($dumpPath );
			$dfgOut->outputln("done.]");

			// refresh imported pages (started as a separate process)
			global $rootDir;
			$this->logger->info("Refreshing dump: $file");
			$id = $dd->getID();
			$phpExe = 'php';
			if (array_key_exists('df_php_executable', DF_Config::$settings)  && !empty(DF_Config::$settings['df_php_executable'])) {
				$phpExe = DF_Config::$settings['df_php_executable'];
			}
			Tools::outStream("\"$phpExe\" \"$rootDir/tools/maintenance/refreshPages.php\" -d \"$dumpPath\" -b $id", $dfgOut);
		}

	}

	/**
	 * Inserts namespaces of DeployDescriptor.
	 *
	 * @param DeployDescriptor $dd
	 */
	public function installNamespaces($dd) {
		global $dfgOut;
		if (count($dd->getNamespaces()) == 0) return;

		$newPrefixes = $dd->getNamespaces();
		$registeredPrefixes = DFBundleTools::getRegisteredPrefixes();
		$registeredPrefixes = array_merge($registeredPrefixes, $newPrefixes);
		$this->logger->info("Insert new namespaces: ".print_r($newPrefixes, true));
		$dfgOut->outputln("[Insert new namespaces: ".implode(",", array_keys($newPrefixes))."...");
		DFBundleTools::storeRegisteredPrefixes($registeredPrefixes, $dd->getID());
		$dfgOut->outputln("done.]");
	}

	/**
	 * Deinstalls wikidumps contained in the given descriptor.
	 *
	 * @param DeployDescriptor $dd

	 */
	public function deinstallWikidump($dd) {

		if (count($dd->getWikidumps()) == 0) return;
		if (!defined('SMW_VERSION')) throw new InstallationError(DEPLOY_FRAMEWORK_NOT_INSTALLED, "SMW is not installed. Can not delete ontology.");
		global $dfgRemoveReferenced, $dfgIncludeTemplates, $dfgIncludeInstances, $dfgRemoveStillUsed;
		DFBundleTools::deletePagesOfBundle($dd->getID(), $this->logger, $dfgRemoveReferenced || $dfgIncludeTemplates, $dfgRemoveReferenced || $dfgIncludeInstances, !$dfgRemoveStillUsed);
	}


	/**
	 * Deletes resources contained in the given descriptor.
	 *
	 * @param DeployDescriptor $dd

	 */
	public function deleteResources($dd) {
		global $wgUser, $dfgOut;
		if (count($dd->getResources()) ==  0) return;

		global $dfgRemoveReferenced, $dfgIncludeImages,  $dfgRemoveStillUsed;
		if ($dfgRemoveReferenced || $dfgIncludeImages) {
			DFBundleTools::deleteReferencedImagesOfBundle($dd->getID(), $this->logger, !$dfgRemoveStillUsed);
		}

		$resources = $dd->getResources();
		foreach($resources as $file) {
			$title = Title::newFromText(basename($file), NS_IMAGE);
			if (!$title->exists()) continue;
			$im_file = wfLocalFile($title);

			// delete thumbs for this image too
			$thumbs = $im_file->getThumbnails();
			foreach($thumbs as $thumb) {
				unlink($im_file->getThumbPath($thumb));
			}
			$im_file->delete("remove resource");
			$a = new Article($title);

			$reason = "remove resource";
			$id = $title->getArticleID( GAID_FOR_UPDATE );
			if( wfRunHooks('ArticleDelete', array(&$a, &$wgUser, &$reason, &$error)) ) {
				if( $a->doDeleteArticle( $reason ) ) {
					$this->logger->info("Remove old page: ".$title->getPrefixedText());
					$dfgOut->outputln("\t[Remove old page: ".$title->getPrefixedText());
					wfRunHooks('ArticleDeleteComplete', array(&$a, &$wgUser, $reason, $id));
					$dfgOut->output("done.]");
				}
			}

		}

		if (count($dd->getOnlyCopyResources()) ==  0) return;
		$resources = $dd->getOnlyCopyResources();
		foreach($resources as $src => $dest) {
			$this->logger->info("Remove resource: ".$dest);
			$dfgOut->outputln("\t[Remove resource: ".$dest);
			if (is_dir($this->rootDir."/".$dest)) {
				Tools::remove_dir($this->rootDir."/".$dest);
			} else {
				unlink($this->rootDir."/".$dest);
			}
			$dfgOut->output("done.]");
		}
	}



	/**
	 * Checks if the page contained in the given package are modified and displays those.
	 *
	 * @param $packageID
	 * @param $version

	 */
	public function checkWikidump($packageID, $version) {
		global $dfgOut;
		if (!defined('SMW_VERSION')) throw new InstallationError(DEPLOY_FRAMEWORK_NOT_INSTALLED, "SMW is not installed although it is needed to check ontology status.");

		$localPackages = PackageRepository::getLocalPackages($this->rootDir);
		$package = array_key_exists($packageID, $localPackages) ? $localPackages[$packageID] : NULL;
		$packageFound = !is_null($package) && ($package->getVersion() == $version || $version == NULL);
		if (!$packageFound) {
			throw new InstallationError(DEPLOY_FRAMEWORK_NOT_INSTALLED, "The specified bundle is not installed. Nothing to check.");
		}

		$dfgOut->outputln("\n[Checking ontology...");
		$reader = new BackupReader(DEPLOYWIKIREVISION_INFO);
		$wikidumps = $package->getWikidumps();
		foreach($wikidumps as $file) {
			if (!file_exists($this->rootDir."/".$file)) {
				$dfgOut->outputln("\t[WARNING]: dump file '".$this->rootDir."/".$file."' does not exist.", DF_PRINTSTREAM_TYPE_WARN);
				continue;
			}
			$result = $reader->importFromFile( $this->rootDir."/".$file );
		}
		$dfgOut->output("done.]");
	}

	/**
	 * Installs the resources as uploaded files.
	 *
	 * @param DeployDescriptor $dd
	 * @param int $fromVersion

	 */
	public function installOrUpdateResources($dd) {
		global $dfgOut;
		if (count($dd->getResources()) ==  0) return;

		global $dfgLang;
		$partOfBundlePropertyName = $dfgLang->getLanguageString('df_partofbundle');
		// resources files
		$this->logger->info("Uploading resources for ".$dd->getID());
		$dfgOut->outputln("[Uploading resources...");
		$resources = $dd->getResources();
		foreach($resources as $file) {
			$resourcePath = $this->rootDir."/".$dd->getInstallationDirectory()."/".$file;
			if (!file_exists($resourcePath)) {
				$this->logger->warn("'$resourcePath' does not exist.");
				$dfgOut->outputln("\t'$resourcePath' does not exist.", DF_PRINTSTREAM_TYPE_WARN);
				continue;
			}
			$this->logger->info("Import ".$resourcePath);
			$dfgOut->outputln("\t[Import ".Tools::shortenPath($resourcePath)."...");
			if (is_dir($resourcePath)) {
				$this->importResources($resourcePath, $dd->getID());
			} else {
				$im_file = wfLocalFile(Title::newFromText(basename($resourcePath), NS_IMAGE));
				$im_file->upload($resourcePath, "auto-inserted image", "[[".$partOfBundlePropertyName."::".ucfirst($dd->getID())."]]");
			}
			$dfgOut->output("done.]");

		}
		$dfgOut->outputln("done.]");

		if (count($dd->getOnlyCopyResources()) ==  0) return;

		$this->logger->info("Copying resources for ".$dd->getID());
		$dfgOut->outputln("[Copying resources...");
		$resources = $dd->getOnlyCopyResources();
		foreach($resources as $file => $dest) {
			$resourcePathSrc = $this->rootDir."/".$file;
			if (!file_exists($resourcePathSrc)) {
				$this->logger->warn("'$resourcePathSrc' does not exist.");
				$dfgOut->outputln("\t'$resourcePathSrc' does not exist.", DF_PRINTSTREAM_TYPE_WARN);
				continue;
			}
			$this->logger->info("Copy '".$resourcePathSrc."' to '".$this->rootDir."/".$dest);
			$dfgOut->outputln("\t[Copy '".Tools::shortenPath($resourcePathSrc)."' to '".Tools::shortenPath($this->rootDir."/".$dest)."'...");
			if (is_dir($resourcePathSrc)) {
				Tools::copy_dir($resourcePathSrc, $this->rootDir."/".$dest);
			} else {
				Tools::mkpath(dirname($this->rootDir."/".$dest));
				copy($resourcePathSrc, $this->rootDir."/".$dest);

			}
			$dfgOut->output( "done.]");

		}
		$dfgOut->outputln("done.]");
	}

	/**
	 * Import mapping pages.
	 *
	 * @param $dd
	 */
	public function installOrUpdateMappings($dd, $dryRun = false) {
		global $dfgOut;
		if (count($dd->getMappings()) ==  0) return;

		if (!defined('LOD_NS_MAPPING')) {
			$this->logger->warn("LinkedData extension is not installed. Can not install mappings.");
			$dfgOut->outputln("\tLinkedData extension is not installed. Can not install mappings.", DF_PRINTSTREAM_TYPE_WARN);
			return;
		}

		$importedMappings = array();
		// import mappings
		$this->logger->info("Import mappings for ".$dd->getID());
		$dfgOut->outputln("[Importing mappings...");
		$resources = $dd->getMappings();

		// delete old mapping articles
		foreach($resources as $source => $list) {

			$mappingTitle = Title::newFromText($source, LOD_NS_MAPPING);
			$a = new Article($mappingTitle);

			// check if article for source exists and delete if so
			if ($a->exists()) {
				if (!$dryRun) $a->doDeleteArticle("update");
			}
		}
		$dfgOut->outputln("done.]");

		// import new
		foreach($resources as $source => $list) {
			foreach($list as $tuple) {
				$content = "";
				list($file, $target) = $tuple;
				$resourcePath = $this->rootDir."/".$dd->getInstallationDirectory()."/".$file;
				if (!file_exists($resourcePath)) {
					$this->logger->warn("'$resourcePath' does not exist.");
					$dfgOut->outputln("\t'$resourcePath' does not exist.", DF_PRINTSTREAM_TYPE_WARN);
					continue;
				}
				$dfgOut->outputln("\t[Import '$resourcePath'");
				if (is_dir($resourcePath)) {
					$dfgOut->outputln("\tMapping location '$resourcePath' must be a file not a directory.", DF_PRINTSTREAM_TYPE_WARN);
				} else {

					$mappingContent = file_get_contents($resourcePath);
					$content .= "<mapping target=\"$target\">\n".$mappingContent."\n</mapping>";

				}
				$dfgOut->output("done.]");
			}
			$mappingTitle = Title::newFromText($source, LOD_NS_MAPPING);
			$a = new Article($mappingTitle);
			$this->logger->info("Insert mapping: $source");
			$dfgOut->outputln("[Insert mapping [$source]...");
			if (!$a->exists()) {
				if (!$dryRun) $a->insertNewArticle($content, "auto-generated mapping page", false, false);
			} else {
				// should not happen because of delete above. anyway
				if (!$dryRun) $a->doEdit($content, "auto-generated mapping page");
			}
			$importedMappings[] = array($source, $target, $content);
			$dfgOut->output("done.]");
		}
		return $importedMappings;
	}

	/**
	 * Import resources from the given directory
	 * Currently only images resources.
	 *
	 * @param $SourceDirectory

	 */
	private function importResources($SourceDirectory, $bundleID) {

		global $dfgLang;
		$partOfBundlePropertyName = $dfgLang->getLanguageString('df_partofbundle');

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
			$dfgOut->outputln("Directory '$SourceDirectory' could not be opened.\n", DF_PRINTSTREAM_TYPE_WARN);
		}
		while ( ($entry = readdir($handle)) !== false ){

			if ($entry[0] == '.'){
				continue;
			}


			if (is_dir($SourceDirectory.$entry)) {
				// Unterverzeichnis
				$success = $this->importResources($SourceDirectory.$entry, $bundleID);

			} else{


				// simulate an upload
				$im_file = wfLocalFile(Title::newFromText(basename($SourceDirectory.$entry), NS_IMAGE));
				$im_file->upload($SourceDirectory.$entry, "auto-inserted image", "[[".$partOfBundlePropertyName."::".ucfirst($bundleID)."]]");

			}

		}
	}


	/**
	 * Reads a dump file and returns the verification log (which itself contains
	 * a list of pages).
	 *
	 * @param string $dumpPath
	 * @param string $bundleID
	 *
	 * @return array of (Title t, string status)
	 *     status can be 'merge', 'conflict' or 'notexist'
	 */
	private function getPagesFromImport( $dumpPath, $bundleID ) {
		$handle = fopen( $dumpPath, 'rt' );
		$source = new ImportStreamSource( $handle );
		$importer = new DeployWikiImporterDetector( $source, $bundleID);

		$importer->setDebug( false );

		$importer->doImport();

		$result = $importer->getResult();
		return $result;
	}

	/**
	 * Removes pages which are no more contained in the bundle to be installed.
	 *
	 * @param string $bundleID
	 * @param array of (Title t, string status) $verificationLog
	 *
	 */
	private function removeOldPages($bundleID, $verificationLog) {
		global $dfgLang, $dfgOut;
		$pagesToImport = array();

		foreach($verificationLog as $log) {
			list($title, $command)=$log;
			$pagesToImport[] = $title->getPrefixedText();
		}
		$bundleIDValue = SMWDIWikiPage::newFromTitle(Title::newFromText($bundleID));
		$pageValuesOfOntology = smwfGetStore()->getPropertySubjects(SMWDIProperty::newFromUserLabel($dfgLang->getLanguageString('df_partofbundle')), $bundleIDValue);
		$existingPages = array();
		foreach($pageValuesOfOntology as $pv) {
			$existingPages[] = $pv->getTitle()->getPrefixedText();
		}


		$pagesToDelete = array_diff($existingPages, $pagesToImport);

		global $wgUser;
		foreach($pagesToDelete as $p) {
			$title = Title::newFromText($p);
			$a = new Article($title);

			$id = $title->getArticleID( GAID_FOR_UPDATE );
			if( wfRunHooks('ArticleDelete', array(&$a, &$wgUser, &$reason, &$error)) ) {
				if( $a->doDeleteArticle("ontology removed: ".$bundleID) ) {
					$this->logger->info("Removing page: ".$title->getPrefixedText());
					$dfgOut->outputln("\t[Removing page]: ".$title->getPrefixedText()."...");

					wfRunHooks('ArticleDeleteComplete', array(&$a, &$wgUser, "ontology removed: ".$bundleID, $id));
					$dfgOut->output("done.]");
				}
			}


		}

	}
}
