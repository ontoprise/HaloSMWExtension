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

global $rootDir;
require_once($rootDir.'/../maintenance/commandLine.inc' );
require_once($rootDir.'/io/import/DF_DeployWikiBundleImporter.php');
require_once($rootDir.'/io/import/DF_BackupReader.php');

/**
 * @file
 * @ingroup DFInstaller
 *
 * Resource installer takes care about wikidump/resource (de-)installation.
 *
 * @author Kai K�hn / ontoprise / 2009
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
	 */
	public function installOrUpdateWikidumps($dd, $fromVersion, $mode) {
		global $wgUser;
		if (count($dd->getWikidumps()) ==  0) return;


		if (!defined('SMW_VERSION')) throw new InstallationError(DEPLOY_FRAMEWORK_NOT_INSTALLED, "SMW is not installed or at least is not active. The ontology could not be properly installed. Please restart smwadmin using -f (force) to install it.");

		// remove old pages
		if (!is_null($fromVersion) && $fromVersion != '') {
			// remove old pages
			$this->logger->info("Remove unused pages from ".$dd->getID());
			print "\n[Remove unused pages...";
			$query = SMWQueryProcessor::createQuery("[[Ontology version::$fromVersion]][[Part of bundle::".$dd->getID()."]]", array());
			$res = smwfGetStore()->getQueryResult($query);
			$next = $res->getNext();
			while($next !== false) {

				$title = $next[0]->getNextObject()->getTitle();
				if (!is_null($title)) {
					$a = new Article($title);
					$reason = "ontology removed: ".$dd->getID();
					$id = $title->getArticleID( GAID_FOR_UPDATE );
					if( wfRunHooks('ArticleDelete', array(&$a, &$wgUser, &$reason, &$error)) ) {
						if( $a->doDeleteArticle( $reason ) ) {
							$this->logger->info("Remove old page from $fromVersion: ".$title->getPrefixedText());
							print "\n\t[Remove old page from $fromVersion: ".$title->getPrefixedText();
							wfRunHooks('ArticleDeleteComplete', array(&$a, &$wgUser, $reason, $id));
							print "done.]";
						}
					}

				}
				$next = $res->getNext();
			}
		}
		print "\ndone.]";

		// import new wiki pages
		$reader = new BackupReader($mode);
		$wikidumps = $dd->getWikidumps();

		foreach($wikidumps as $file) {
			$this->logger->info("Import ontology: $file");
			print "\n[Import ontology: $file";
			$dumpPath = $this->rootDir."/". $dd->getInstallationDirectory()."/".$file;
			if (!file_exists($dumpPath)) {
				$this->logger->warn("dump file '".$dumpPath."' does not exist.");
				print "\n\t[WARNING]: dump file '".$dumpPath."' does not exist.";
				continue;
			}
			$result = $reader->importFromFile($dumpPath );
			print "\ndone.]";
		}

		// refresh imported pages
		/*$pageTitles = $reader->getImportedPages();
		global $wgParser;
		$wgParser->mOptions = new ParserOptions();
		$this->logger->info("Refreshing ontology: $file");
		print "\n[Refreshing ontology: $file";
		
		foreach($pageTitles as $pageName) {
			$t = Title::newFromText($pageName);
			if ($t->getNamespace() == NS_FILE) continue;
			$rev = Revision::newFromTitle($t);
			$parseOutput = $wgParser->parse($rev->getText(), $t, $wgParser->mOptions);
			SMWParseData::storeData($parseOutput, $t);
			$this->logger->info($t->getText()." refreshed.");
			print "\n\t[".$t->getText()." refreshed]";
		}*/

	}

	/**
	 * Deinstalls wikidumps contained in the given descriptor.
	 *
	 * @param DeployDescriptor $dd

	 */
	public function deinstallWikidump($dd) {

		if (count($dd->getWikidumps()) == 0) return;
		if (!defined('SMW_VERSION')) throw new InstallationError(DEPLOY_FRAMEWORK_NOT_INSTALLED, "SMW is not installed. Can not delete ontology.");

		Tools::deletePagesOfBundle($dd->getID(), $this->logger);
	}


	/**
	 * Deletes resources contained in the given descriptor.
	 *
	 * @param DeployDescriptor $dd

	 */
	public function deleteResources($dd) {
		global $wgUser;
		if (count($dd->getResources()) ==  0) return;


		$resources = $dd->getResources();
		foreach($resources as $file) {
			$title = Title::newFromText(basename($file), NS_IMAGE);
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
					print "\n\t[Remove old page: ".$title->getPrefixedText();
					wfRunHooks('ArticleDeleteComplete', array(&$a, &$wgUser, $reason, $id));
					print "done.]";
				}
			}

		}

		if (count($dd->getOnlyCopyResources()) ==  0) return;
		$resources = $dd->getOnlyCopyResources();
		foreach($resources as $src => $dest) {
			$this->logger->info("Remove resource: ".$dest);
			print "\n\t[Remove resource: ".$dest;
			if (is_dir($this->rootDir."/".$dest)) {
				Tools::remove_dir($this->rootDir."/".$dest);
			} else {
				unlink($this->rootDir."/".$dest);
			}
			print "done.]";
		}
	}



	/**
	 * Checks if the page contained in the given package are modified and displays those.
	 *
	 * @param $packageID
	 * @param $version

	 */
	public function checkWikidump($packageID, $version) {
		if (!defined('SMW_VERSION')) throw new InstallationError(DEPLOY_FRAMEWORK_NOT_INSTALLED, "SMW is not installed although it is needed to check ontology status.");

		$localPackages = PackageRepository::getLocalPackages($this->rootDir.'/extensions');
		$package = array_key_exists($packageID, $localPackages) ? $localPackages[$packageID] : NULL;
		$packageFound = !is_null($package) && ($package->getVersion() == $version || $version == NULL);
		if (!$packageFound) {
			throw new InstallationError(DEPLOY_FRAMEWORK_NOT_INSTALLED, "The specified package is not installed. Nothing to check.");
		}

		print "\n\n[Checking ontology...";
		$reader = new BackupReader(DEPLOYWIKIREVISION_INFO);
		$wikidumps = $package->getWikidumps();
		foreach($wikidumps as $file) {
			if (!file_exists($this->rootDir."/".$file)) {
				print "\n\t[WARNING]: dump file '".$this->rootDir."/".$file."' does not exist.";
				continue;
			}
			$result = $reader->importFromFile( $this->rootDir."/".$file );
		}
		print "done.]";
	}

	/**
	 * Installs the resources as uploaded files.
	 *
	 * @param DeployDescriptor $dd
	 * @param int $fromVersion

	 */
	public function installOrUpdateResources($dd) {

		if (count($dd->getResources()) ==  0) return;

		// resources files
		$this->logger->info("Uploading resources for ".$dd->getID());
		print "\n[Uploading resources...";
		$resources = $dd->getResources();
		foreach($resources as $file) {
			$resourcePath = $this->rootDir."/".$dd->getInstallationDirectory()."/".$file;
			if (!file_exists($resourcePath)) {
				$this->logger->warn("'$resourcePath' does not exist.");
				print "\n\t[WARNING]: '$resourcePath' does not exist.";
				continue;
			}
			$this->logger->info("Import ".$resourcePath);
			print "\n\t[Import ".Tools::shortenPath($resourcePath)."...";
			if (is_dir($resourcePath)) {
				$this->importResources($resourcePath);
			} else {
				$im_file = wfLocalFile(Title::newFromText(basename($resourcePath), NS_IMAGE));
				$im_file->upload($resourcePath, "auto-inserted image", "noText");
			}
			print "done.]";

		}
		print "\ndone.]";

		if (count($dd->getOnlyCopyResources()) ==  0) return;

		$this->logger->info("Copying resources for ".$dd->getID());
		print "\n[Copying resources...";
		$resources = $dd->getOnlyCopyResources();
		foreach($resources as $file => $dest) {
			$resourcePathSrc = $this->rootDir."/".$file;
			if (!file_exists($resourcePathSrc)) {
				$this->logger->warn("'$resourcePathSrc' does not exist.");
				print "\n\t[WARNING]: '$resourcePathSrc' does not exist.";
				continue;
			}
			$this->logger->info("Copy '".$resourcePathSrc."' to '".$this->rootDir."/".$dest);
			print "\n\t[Copy '".Tools::shortenPath($resourcePathSrc)."' to '".Tools::shortenPath($this->rootDir."/".$dest)."'...";
			if (is_dir($resourcePathSrc)) {
				Tools::copy_dir($resourcePathSrc, $this->rootDir."/".$dest);
			} else {
				Tools::mkpath(dirname($this->rootDir."/".$dest));
				copy($resourcePathSrc, $this->rootDir."/".$dest);

			}
			print "done.]";

		}
		print "\ndone.]";
	}

	/**
	 * Import mapping pages.
	 *
	 * @param $dd
	 */
	public function installOrUpdateMappings($dd, $dryRun = false) {
		if (count($dd->getMappings()) ==  0) return;

		if (!defined('LOD_NS_MAPPING')) {
			$this->logger->warn("LinkedData extension is not installed. Can not install mappings.");
			print "\n\t[WARNING]: LinkedData extension is not installed. Can not install mappings.";
			return;
		}

		$importedMappings = array();
		// import mappings
		$this->logger->info("Import mappings for ".$dd->getID());
		print "\n[Importing mappings...";
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
		print "\ndone.]";

		// import new
		foreach($resources as $source => $list) {
			foreach($list as $tuple) {
				$content = "";
				list($file, $target) = $tuple;
				$resourcePath = $this->rootDir."/".$dd->getInstallationDirectory()."/".$file;
				if (!file_exists($resourcePath)) {
					$this->logger->warn("'$resourcePath' does not exist.");
					print "\n\t[WARNING]: '$resourcePath' does not exist.";
					continue;
				}
				print "\n\t[Import '$resourcePath'";
				if (is_dir($resourcePath)) {
					print "\n\tMapping location '$resourcePath' must be a file not a directory.";
				} else {

					$mappingContent = file_get_contents($resourcePath);
					$content .= "<mapping target=\"$target\">\n".$mappingContent."\n</mapping>";

				}
				print "done.]";
			}
			$mappingTitle = Title::newFromText($source, LOD_NS_MAPPING);
			$a = new Article($mappingTitle);
			$this->logger->info("Insert mapping: $source");
			print "\n[Insert mapping [$source]...";
			if (!$a->exists()) {
				if (!$dryRun) $a->insertNewArticle($content, "auto-generated mapping page", false, false);
			} else {
				// should not happen because of delete above. anyway
				if (!$dryRun) $a->doEdit($content, "auto-generated mapping page");
			}
			$importedMappings[] = array($source, $target, $content);
			print "done.]";
		}
		return $importedMappings;
	}

	/**
	 * Import resources from the given directory
	 * Currently only images resources.
	 *
	 * @param $SourceDirectory

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
