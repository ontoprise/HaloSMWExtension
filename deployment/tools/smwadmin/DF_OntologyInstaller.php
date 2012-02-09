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
require_once($rootDir.'/io/import/DF_DeployWikiOntologyImporter.php');
require_once($rootDir.'/io/import/DF_OntologyDetector.php');
require_once($rootDir.'/io/import/DF_OntologyMerger.php');
require_once($rootDir.'/io//DF_BundleTools.php');

/**
 * @file
 * @ingroup DFInstaller
 *
 * Ontology installer takes care about ontology (de-)installation.
 *
 * @author Kai Kühn
 *
 */
class OntologyInstaller {

	static $instance; // singleton

	var $rootDir; // MW installation dir
	var $logger;

	public static function getInstance($rootDir) {
		if (is_null(self::$instance)) {
			self::$instance = new OntologyInstaller($rootDir);
		}
		return self::$instance;
	}

	public function __construct($rootDir) {
		$this->rootDir = $rootDir;
		$this->logger = Logger::getInstance();
	}

	/**
	 * Installs an ontology from a file.
	 *
	 * @param string $bundleID ID of stub bundle which will be created for the ontology.
	 * @param string $inputfile Full path of input file
	 * @param boolean $noBundlePage Should a bundle page be created or not.
	 * @param int $mode How to deal with conflicts  (see DF_ONOLOGYIMPORT_.. constants)
	 *
	 * @return string Prefixed used to make ontology pages unique (can be null)
	 *
	 */
	public function installOrUpdateOntology($inputfile, $noBundlePage = false, $bundleID = '') {
		global $dfgOut;
		$outputfile = $inputfile.".xml";
		try {
			// create input file with additional settings
			global $smwgHaloTripleStoreGraph;
			$dfgOut->outputln("[Get used prefixes...");
			$prefixNamespaceMappings = DFBundleTools::getRegisteredPrefixes();
			$settingsFile = $inputfile.".settings";
			$settings = new stdClass();
			$settings->ns_mappings = $prefixNamespaceMappings;
			$settings->base_uri = $smwgHaloTripleStoreGraph;
			if (!empty($bundleID)) {
				$settings->bundle_id = $bundleID;
			}

			$handle = fopen($settingsFile, "w");
			fwrite($handle, json_encode($settings));
			fclose($handle);
			$dfgOut->output("done.]");

			// convert ontology file
			$ret = $this->convertOntology($inputfile, $outputfile, $noBundlePage);

			if ($ret != 0) {
				$dfgOut->outputln("Could not convert ontology.");
				unlink($settingsFile);
				die(1);
			}

			$outputFromOnto2mwxmlText = file_get_contents($settingsFile);
			$outputFromOnto2mwxml = json_decode($outputFromOnto2mwxmlText);

		} catch(Exception $e) {
			// onto2mwxml might not be installed
			dffExitOnFatalError("Could not convert ontology. Reason: ".$e->getMessage());
		}


		$outputfile_rel = $inputfile.".xml";

		// verifies the ontologies
		// has only informative character. The only conflicts appearing
		// now occur if two ontologies use the same entity.
		$dfgOut->outputln("[Verifying ontology $inputfile...");
		$bundleID = $outputFromOnto2mwxml->ontology_uri;
		$verificationLog = $this->verifyOntology($outputfile_rel, $bundleID);
		$conflict = $this->checkForConflict($verificationLog);

		$dfgOut->output("done.");

		if ($conflict) {
			// write prefix file
			$dfgOut->outputln("[Conflicts detected. At least two bundles share one or more pages.]");

		} else {
			$dfgOut->outputln("[No conflicts detected]");
		}

		// check if ontology is already installed

		$ontologyURI = DFBundleTools::getOntologyURI($bundleID);

		if (!is_null($ontologyURI)) {
			if ($outputFromOnto2mwxml->ontology_uri  == $ontologyURI) {
				// it is an update,so remove old version first
				$dfgOut->outputln("[Delete old ontology $ontologyURI...");
				$this->deinstallAllOntologies($bundleID);
				$dfgOut->output("done.]");
			}
		}

		// update prefixes (comes from onto2mwxml)
		$dfgOut->outputln("[Update prefixes...");

		DFBundleTools::storeRegisteredPrefixes($outputFromOnto2mwxml->ns_mappings);
		unlink($settingsFile);
		$dfgOut->output("done.]");

		// do actual ontology install/update
		$dfgOut->outputln("[Installing/updating ontology $inputfile...");
		$this->installOrUpdateOntologyXML($outputfile_rel, $verificationLog, $bundleID);

		// import external artifacts (e.g. mapping metadata/rules)
		$externalArtifactFile = $inputfile.".external";
		if (file_exists($externalArtifactFile)) {
			$dfgOut->outputln("\t[Importing external artifacts from $externalArtifactFile...");
			$this->uploadExternalArtifacts($externalArtifactFile, $bundleID);
			$dfgOut->output("done.]");
		}


		return $bundleID;
	}

	/**
	 * Installs or updates ontologies specified in the deploy descriptor
	 *
	 * @param DeployDescriptor $dd
	 *
	 */
	public function installOntologies($dd) {
		$ontologies = $dd->getOntologies();
		$noBundlePage = false;
		foreach($ontologies as $loc) {
			$prefix = $this->installOrUpdateOntology($this->rootDir.$dd->getInstallationDirectory()."/".$loc, $noBundlePage, $dd->getID());
			$noBundlePage = true; // make sure that only the first ontology creates a bundle page
		}
	}


	/**
	 * De-installs all ontologies contained in a bundle.
	 *
	 * @param string $bundleID
	 */
	public function deinstallAllOntologies($bundleID) {

		if (!defined('SMW_VERSION')) throw new InstallationError(DEPLOY_FRAMEWORK_NOT_INSTALLED, "SMW is not installed. Can not delete ontology.");
		global $dfgOut;

		// removes used prefixes;
		$prefixesToRemove = DFBundleTools::getPrefixesUsedBy($bundleID);
		// remove those ONLY used for $bundleID
		$nsMappings = DFBundleTools::getRegisteredPrefixes();
		foreach($prefixesToRemove as $prefix) {
			unset($nsMappings[$prefix]);
			$dfgOut->outputln("\t\t[Removed prefix: ".$prefix."]");
		}
		DFBundleTools::storeRegisteredPrefixes($nsMappings);

		// process the pages which are part of the bundle
		// remove the part of the bundle which should be deleted.
		// if the page is completely empty afterwards, remove it.
		$bundlePages = DFBundleTools::getBundlePages($bundleID);
		$om = new OntologyMerger();
		$db =& wfGetDB( DB_MASTER );
		global $wgUser;
		foreach($bundlePages as $title) {
			$rev = Revision::loadFromTitle( $db, $title);

			if ($om->containsBundle($bundleID, $rev->getRawText())) {
				$newText = $om->removeBundle($bundleID, $rev->getRawText());
				if (trim($newText) == '') {
					$a = new Article($title);

					if( wfRunHooks('ArticleDelete', array(&$a, &$wgUser, &$reason, &$error)) ) {
						if( $a->doDeleteArticle( "article is empty" ) ) {
							//if (!is_null($logger)) $logger->info("Removing page: ".$title->getPrefixedText());
							$dfgOut->outputln("\t\t[Removing page]: ".$title->getPrefixedText()."...");
							wfRunHooks('ArticleDeleteComplete', array(&$a, &$wgUser, "article is empty", $bundleID));
							$dfgOut->output("done.]");
						}
					}
				} else {
						
					$dfgOut->outputln("\t\t[Modifying page]: ".$title->getPrefixedText()."...");   
                    $a = new Article($title);
                    $a->doEdit($newText, "auto-generated by smwadmin");
                    $dfgOut->output("done.]");
					//$parseOutput = $wgParser->parse($rev->getText(), $title, $wgParser->mOptions);
					//SMWParseData::storeData($parseOutput, $title);
				}
			}
		}
		
	}

	/**
	 * Creates an deploy descriptor for an ontology bundle.
	 *
	 * @param string $bundleID
	 * @param string $inputfile
	 */
	public function createDeployDescriptor($bundleID, $inputfile) {
		global $dfgLang;

		$ontologyBundleTitle = Title::newFromText($bundleID);
		$ontologyBundleDi = SMWDIWikiPage::newFromTitle($ontologyBundleTitle);
		$ontologyVersion = smwfGetStore()->getPropertyValues($ontologyBundleDi, SMWDIProperty::newFromUserLabel($dfgLang->getLanguageString('df_ontologyversion')));
		$installationDir = smwfGetStore()->getPropertyValues($ontologyBundleDi, SMWDIProperty::newFromUserLabel($dfgLang->getLanguageString('df_instdir')));
		$ontologyVersion = reset($ontologyVersion);
		$installationDir = reset($installationDir);
		
		$version = $ontologyVersion->getString();
		$installDir = $installationDir->getString();
		$installDir = strtolower($installDir);
		$filename = basename($inputfile);

		// set others to defaults
		$vendor = '';
		$maintainer= '';
		$description = '';

		$xml =  <<<ENDS
<?xml version="1.0" encoding="UTF-8"?>
<deploydescriptor>
    <global>
        <version>$version</version>
        <patchlevel>0</patchlevel>
        <id>$bundleID</id>
        <vendor>$vendor</vendor>
        <maintainer>$maintainer</maintainer>
        <instdir>$installDir</instdir>
        <description>
        $description
        </description>
        <helpurl></helpurl>
        <dependencies>
        </dependencies>
    </global>
    <codefiles>
        <!-- empty -->
    </codefiles>
    <wikidumps>
        <!-- empty -->
    </wikidumps>
    <resources>
        <!-- empty -->
    </resources>
    <ontologies>
        <file loc="$filename"/>
    </ontologies>
    <configs>

    </configs>
</deploydescriptor>
ENDS
        ;
        return $xml;
	}

	/**
	 * Uploads an external entity containing arbitrary artifacts. Marks this upload
	 * as part of the bundle.
	 *
	 * @param string $filepath Absolute path
	 * @param string $bundleID
	 */
	private function uploadExternalArtifacts($filepath, $bundleID) {
		global $dfgLang;
		$im_file = wfLocalFile(Title::newFromText(basename($filepath), NS_FILE));
		$text = "[[".$dfgLang->getLanguageString('df_partofbundle')."::".ucfirst($bundleID)."]]";
		$im_file->upload($filepath, "auto-inserted external artifacts from $bundleID", $text);
	}

	/**
	 * Checks what would happen if the given ontology would be imported.
	 *
	 * @param $inputfile filepath relative to extension
	 * @param string $bundleID
	 *
	 * @return array of (Title t, string status)
	 *     status can be 'merge', 'conflict' or 'notexist'
	 */
	private function verifyOntology($inputfile, $bundleID) {

		if( preg_match( '/\.gz$/', $inputfile ) ) {
			$filename = 'compress.zlib://' . $inputfile;
		}
		$fileHandle = fopen( $inputfile, 'rt' );
		return $this->verifyFromHandle( $fileHandle, $bundleID);
	}

	/**
	 * Install/update ontology
	 *
	 * @param $file filepath relative to extension
	 * @param array $verificationLog
	 * @param string bundleID
	 */
	private function installOrUpdateOntologyXML($inputfile, $verificationLog, $bundleID) {

		// remove ontology elements which do not exist anymore.
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


		// install new or updated ontology
		if( preg_match( '/\.gz$/', $inputfile ) ) {
			$filename = 'compress.zlib://' . $inputfile;
		}
		$fileHandle = fopen( $inputfile, 'rt' );
		return $this->importFromHandle( $fileHandle, $bundleID );
	}

	/**
	 * Verifies ontology file and checks if there are conflicts
	 *
	 * @param int $handle
	 * @param string $bundleID

	 *
	 * @return array of (Title t, string status)
	 *     status can be 'merge', 'conflict' or 'notexist'
	 */
	private function verifyFromHandle( $handle, $bundleID) {
		$source = new ImportStreamSource( $handle );
		$importer = new DeployWikiImporterDetector( $source, $bundleID);

		$importer->setDebug( false );

		$importer->doImport();
			
		$result = $importer->getResult();
		return $result;
	}

	/**
	 * Imports ontology.
	 *
	 * @param int $handle
	 * @param string $bundleID

	 *
	 */
	private function importFromHandle( $handle, $bundleID ) {


		$source = new ImportStreamSource( $handle );
		$importer = new DeployWikiOntologyImporter( $source, $bundleID, 1);

		$importer->setDebug( false );

		$importer->doImport();

		$result = $importer->getResult();
		return $result;


	}

	/**
	 * Converts an ontology via onto2mwxml.
	 *
	 * @param string $inputfile
	 * @param string $outputfile
	 * @param boolean $noBundlePage
	 * @param string $bundleID (optional)
	 */
	private function convertOntology($inputfile, $outputfile, $noBundlePage = false, $bundleID = '') {
		// convert ontology
		global $dfgOut, $dfgOutputFormat;
		$cwd = getcwd();
		$onto2mwxml_dir = $this->rootDir."/deployment/tools/onto2mwxml";
		$dfgOut->outputln("[Convert ontology $inputfile...");

		chdir($onto2mwxml_dir);
		$ret = 0;
		$langCode = dffGetLanguageCode();
		if (Tools::isWindows()) {
				
			if ($noBundlePage) $noBundlePageParam = "--nobundlepage"; else $noBundlePageParam = "";
			if (!empty($bundleID)) $bundleID ='--bundleid \"$bundleID\"';
			exec("\"$onto2mwxml_dir/onto2mwxml.bat\" -i \"$inputfile\" -o \"$outputfile\" $bundleID $noBundlePageParam --outputformat $dfgOutputFormat --lang $langCode", $output, $ret);
			if ($ret != 0) {
				foreach($output as $l) $dfgOut->outputln("$l");
				throw new Exception("Onto2MWXML exited abnormally.");
			}
				
		} else {
				
			if ($noBundlePage) $noBundlePageParam = "--nobundlepage"; else $noBundlePageParam = "";
			if (!empty($bundleID)) $bundleID ='--bundleid \"$bundleID\"';
			exec("\"$onto2mwxml_dir/onto2mwxml.sh\" -i \"$inputfile\" -o \"$outputfile\" $bundleID $noBundlePageParam --outputformat $dfgOutputFormat --lang $langCode", $output, $ret);
			if ($ret != 0) {
				foreach($output as $l) $dfgOut->outputln("$l");
				throw new Exception("Onto2MWXML exited abnormally.");
			}
				
		}
		chdir($cwd);
		$dfgOut->output("done.]");
		return $ret;
	}



	/**
	 * Checks for a conflict.
	 *
	 * @param array ($title, $msg) $verificationLog
	 *
	 * @return mixed false if no conflict otherwise prefix to make solve conflict.
	 */
	private function checkForConflict($verificationLog) {
		$conflict = false;
		global $dfgOut;
		foreach($verificationLog as $l) {
			list($title, $msg) = $l;
			if ($msg == 'conflict') {
				$dfgOut->outputln("Conflict! Title already exists: '$title'");
				$conflict = true;
			}
		}

		return $conflict;
	}


}
