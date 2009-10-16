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

require_once( '../../maintenance/commandLine.inc' );
require_once('../io/import/DF_DeployWikiImporter.php');
require_once('../io/import/DF_BackupReader.php');

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
	 * @param DeployDescriptor $dd
	 */
	public function installOrUpdateWikidumps($dd, $fromVersion, $mode) {
		global $wgUser;
		if (count($dd->getWikidumps()) ==  0) return;


		if (!defined('SMW_VERSION')) throw new InstallationError(DEPLOY_FRAMEWORK_NOT_INSTALLED, "SMW is not installed or at least is not active. The ontology could not be properly installed. Please restart smwadmin using -f (force) to install it.");

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
							print "\n\tRemove old page from $fromVersion: ".$title->getPrefixedText();
							wfRunHooks('ArticleDeleteComplete', array(&$a, &$wgUser, $reason, $id));
						}
					}
						
				}
				$next = $res->getNext();
			}
		}

	}

	/**
	 * Deinstalls wikidumps contained in the given descriptor.
	 *
	 * @param DeployDescriptor $dd

	 */
	public function deinstallWikidump($dd) {

		if (count($dd->getWikidumps()) == 0) return;
		if (!defined('SMW_VERSION')) throw new InstallationError(DEPLOY_FRAMEWORK_NOT_INSTALLED, "SMW is not installed. Can not delete ontology.");

		$this->deletePagesOfBundle($dd->getID());
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
			print "\n\tRemove resource: ".$title->getPrefixedText();
			$reason = "remove resource";
			$id = $title->getArticleID( GAID_FOR_UPDATE );
			if( wfRunHooks('ArticleDelete', array(&$a, &$wgUser, &$reason, &$error)) ) {
				if( $a->doDeleteArticle( $reason ) ) {
					print "\n\tRemove old page: ".$title->getPrefixedText();
					wfRunHooks('ArticleDeleteComplete', array(&$a, &$wgUser, $reason, $id));
				}
			}
			
		}

		if (count($dd->getOnlyCopyResources()) ==  0) return;
		$resources = $dd->getOnlyCopyResources();
		foreach($resources as $src => $dest) {
			print "\n\tRemove resource: ".$dest;
			if (is_dir($this->rootDir."/".$dest)) {
				Tools::remove_dir($this->rootDir."/".$dest);
			} else {
				unlink($this->rootDir."/".$dest);
			}
		}
	}

	/**
	 * Deletes codefiles which are *not* located in the installation directory.
	 *
	 * @param DeployDescriptor $dd
	 */
	public function deleteExternalCodefiles($dd) {

		if (count($dd->getCodefiles()) ==  0) return;
		$codefiles = $dd->getCodefiles();
		print "\nDeleting external codefiles...";
		foreach($codefiles as $f) {
			if (strpos($f, $dd->getInstallationDirectory()) === 0) continue; // ignore these
			print "\n\tRemove $f";
			if (is_dir($this->rootDir."/".$f)) {
				Tools::remove_dir($this->rootDir."/".$f);
			} else if (file_exists($this->rootDir."/".$f)) {
				unlink($this->rootDir."/".$f);
			}
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
	 * @param DeployDescriptor $dd
	 * @param int $fromVersion

	 */
	public function installOrUpdateResources($dd) {

		if (count($dd->getResources()) ==  0) return;

		// resources files
		print "\nUploading resources...";
		$resources = $dd->getResources();
		foreach($resources as $file) {
			$resourcePath = $this->rootDir."/".$dd->getInstallationDirectory()."/".$file;
			print "\n\tCopy $file...";
			if (is_dir($resourcePath)) {

				$this->importResources($resourcePath);
			} else {
				print $resourcePath."\n";
				$im_file = wfLocalFile(Title::newFromText(basename($resourcePath), NS_IMAGE));
				$im_file->upload($resourcePath, "auto-inserted image", "noText");
			}
			print "done.";
		}

		if (count($dd->getOnlyCopyResources()) ==  0) return;
		print "\nCopying resources...";
		$resources = $dd->getOnlyCopyResources();
		foreach($resources as $file => $dest) {
			$resourcePathSrc = $this->rootDir."/".$dd->getInstallationDirectory()."/".$file;
			print "\n\tCopy $file...";
			if (is_dir($resourcePathSrc)) {

				Tools::copy_dir($resourcePathSrc, $this->rootDir."/".$dest);
			} else {
				Tools::mkpath(dirname($this->rootDir."/".$dest));
				copy($resourcePathSrc, $this->rootDir."/".$dest);

			}
			print "done.";
		}
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

	/**
	 * Removes articles belonging to a bundle. It is assumed that everything other than instances of categories of a bundle
	 * and templates used by such is marked with the 'Part of bundle' annotation. Templates which are used by pages other than
	 * that are kept.
	 *
	 * @param string $ext_id
	 */
	private function deletePagesOfBundle($ext_id) {
		global $dfgLang;
		$db =& wfGetDB( DB_MASTER );
		$smw_ids = $db->tableName('smw_ids');
		$smw_rels2 = $db->tableName('smw_rels2');
		$page = $db->tableName('page');
		$categorylinks = $db->tableName('categorylinks');
		$templatelinks = $db->tableName('templatelinks');
		$db->query( 'CREATE TEMPORARY TABLE df_page_of_bundle (id INT(8) NOT NULL)
                    TYPE=MEMORY', 'SMW::createVirtualTableForPagesOfBundle' );

		$db->query( 'CREATE TEMPORARY TABLE df_page_of_templates_used (title  VARCHAR(255) NOT NULL)
                    TYPE=MEMORY', 'SMW::createVirtualTableForTemplatesUsed' );
		$db->query( 'CREATE TEMPORARY TABLE df_page_of_templates_must_persist (title  VARCHAR(255) NOT NULL)
                    TYPE=MEMORY', 'SMW::createVirtualTableForTemplatesUsed' );

		$partOfBundlePropertyID = smwfGetStore()->getSMWPropertyID(SMWPropertyValue::makeUserProperty($dfgLang->getLanguageString("df_partofbundle")));
		$ext_id = strtoupper(substr($ext_id, 0, 1)).substr($ext_id, 1);
		$partOfBundleID = smwfGetStore()->getSMWPageID($ext_id, NS_MAIN, "");

		// put all pages belonging to a bundle (all except templates, ie. categories, properties, instances of categories and all other pages denoted by
		// the 'part of bundle' annotation like Forms, Help pages, etc..) in df_page_of_bundle
		$db->query('INSERT INTO df_page_of_bundle (SELECT page_id FROM '.$page.' JOIN '.$smw_ids.' ON smw_namespace = page_namespace AND smw_title = page_title JOIN '.$smw_rels2.' ON smw_id = s_id WHERE p_id = '.$partOfBundlePropertyID.' AND o_id = '.$partOfBundleID.')');
		$db->query('INSERT INTO df_page_of_bundle (SELECT cl_from FROM '.$categorylinks.' JOIN '.$page.' ON cl_to = page_title AND page_namespace = '.NS_CATEGORY.' JOIN '.$smw_ids.' ON smw_namespace = page_namespace AND smw_title = page_title JOIN '.$smw_rels2.' ON smw_id = s_id WHERE p_id = '.$partOfBundlePropertyID.' AND o_id = '.$partOfBundleID.')');

		// get all templates used on these pages
		$db->query('INSERT INTO df_page_of_templates_used (SELECT tl_title FROM '.$templatelinks.' WHERE tl_from IN (SELECT * FROM df_page_of_bundle))');

		// get all templates which are also used on other pages and must therefore persist
		$db->query('INSERT INTO df_page_of_templates_must_persist (SELECT title FROM df_page_of_templates_used JOIN '.$templatelinks.' ON title = tl_title AND tl_from NOT IN (SELECT * FROM df_page_of_bundle))');

		// delete those from the table of used templates
		$db->query('DELETE FROM df_page_of_templates_used WHERE title IN (SELECT * FROM df_page_of_templates_must_persist)');

		// select all templates which can be deleted
		$res = $db->query('SELECT DISTINCT title FROM df_page_of_templates_used');

		// DELETE templates
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {

				$template = Title::newFromText($row->title, NS_TEMPLATE);

				$a = new Article($template);
				print "\n\tRemove page: ".$template->getPrefixedText();
				$a->doDeleteArticle("ontology removed: ".$ext_id);

			}
		}
		$db->freeResult($res);

		// DELETE pages of bundle
		$res = $db->query('SELECT DISTINCT id FROM df_page_of_bundle');

		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {

				$page = Title::newFromID($row->id);
				// DELETE
				$a = new Article($page);
				print "\n\tRemove page: ".$page->getPrefixedText();
				$a->doDeleteArticle("ontology removed: ".$ext_id);
			}
		}
		$db->freeResult($res);

		$db->query('DROP TEMPORARY TABLE df_page_of_bundle');
		$db->query('DROP TEMPORARY TABLE df_page_of_templates_used');
		$db->query('DROP TEMPORARY TABLE df_page_of_templates_must_persist');
	}

}
