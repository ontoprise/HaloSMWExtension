<?php

/*  Copyright 2011, ontoprise GmbH
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
 * @author Kai Kühn / ontoprise / 2011
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
	 * @param object $callback method askForOntologyPrefix(& $answer)
	 * @param boolean $noBundlePage Should a bundle page be created or not.
	 * @param boolean $force Ignore conflicts or not.
	 *
	 * @return string Prefixed used to make ontology pages unique (can be null)
	 *
	 */
	public function installOntology($bundleID, $inputfile, $callback, $noBundlePage = false, $force = false) {

		$outputfile = $inputfile.".xml";
		try {
			$ret = $this->convertOntology($inputfile, $outputfile, $bundleID, $noBundlePage);

			if ($ret != 0) {
				print "\nCould not convert ontology.";
				die(1);
			}
		} catch(Exception $e) {
			// onto2mwxml might not be installed
			print "\nCould not convert ontology. Reason: ";
			print "\n\n".$e->getMessage()."\n";
			die(1);
		}

		// read possible existing prefix if this is an update
		$prefix = DFBundleTools::getOntologyPrefix($bundleID);

		$outputfile_rel = $inputfile.".xml";
		// verifies the ontologies
		print "\n[Verifying ontology $inputfile...";
		do {
			$verificationLog = $this->verifyOntology($outputfile_rel, $bundleID, $prefix);

			//var_dump($verificationLog);
			$conflict = $this->checkForConflict($verificationLog, $callback);
			if ($conflict !== false) $prefix = $conflict;

		} while ($conflict !== false);
		print "done.";

		if ($prefix != '') {
			// write prefix file
			print "\n[Conflict detected. Using prefix '$prefix']";

		} else {
			print "\n[No Conflict detected]";
		}

		// do actual ontology install/update
		print "\n[Installing/updating ontology $inputfile...";
		$this->installOrUpdateOntology($outputfile_rel, $verificationLog, $bundleID, $prefix);

		// import external artifacts (e.g. mapping metadata/rules)
		$externalArtifactFile = $inputfile.".external";
		if (file_exists($externalArtifactFile)) {
			print "\n\t[Importing external artifacts from $externalArtifactFile...";
			$this->uploadExternalArtifacts($externalArtifactFile, $bundleID);
			print "done.]";
		}

		return $prefix;
	}

	/**
	 * Installs or updates ontologies specified in the deploy descriptor
	 *
	 * @param DeployDescriptor $dd
	 * @param object $callback method askForOntologyPrefix(& $answer)
	 */
	public function installOntologies($dd, $callback, $force = false) {
		$ontologies = $dd->getOntologies();
		$noBundlePage = false;
		foreach($ontologies as $loc) {
			$prefix = $this->installOntology($dd->getID(), $this->rootDir.$dd->getInstallationDirectory()."/".$loc, $callback, $noBundlePage, $force);
			$noBundlePage = true; // make sure that only the first ontology creates a bundle page

			// store prefix
			if ($prefix != '') {
				$basename = basename($this->rootDir.$dd->getInstallationDirectory()."/".$loc);
				$handle = fopen("$mwrootDir/extensions/$bundleID/".$basename.".prefix", "w");
				fwrite($handle, $prefix);
				fclose($handle);
			}
		}
	}






	/**
	 * De-installs ontologies contained in a bundle.
	 *
	 * @param DeployDescriptor $dd
	 */
	public function deinstallOntology($dd) {
		if (count($dd->getOntologies()) == 0) return;
		if (!defined('SMW_VERSION')) throw new InstallationError(DEPLOY_FRAMEWORK_NOT_INSTALLED, "SMW is not installed. Can not delete ontology.");

		foreach($dd->getOntologies() as $loc) {
			$bundleID = $dd->getID();
			Tools::deletePagesOfBundle($bundleID, $this->logger);
		}
	}

	/**
	 * Creates an deploy descriptor for an ontology bundle.
	 *
	 * @param string $ontologyID
	 * @param string $inputfile
	 */
	public function createDeployDescriptor($ontologyID, $inputfile, $prefix) {
		global $dfgLang;

		$ontologyBundlePage = Title::newFromText($ontologyID);
		$ontologyVersion = smwfGetStore()->getPropertyValues($ontologyBundlePage, SMWPropertyValue::makeUserProperty($dfgLang->getLanguageString('df_ontologyversion')));
		$installationDir = smwfGetStore()->getPropertyValues($ontologyBundlePage, SMWPropertyValue::makeUserProperty($dfgLang->getLanguageString('df_instdir')));
		$ontologyVersion = reset($ontologyVersion);
		$installationDir = reset($installationDir);
		$versionDBkeys = $ontologyVersion->getDBkeys();
		$version = reset($versionDBkeys);
		$installDirDBkeys = $installationDir->getDBkeys();
		$installDir = reset($installDirDBkeys);
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
        <id>$ontologyID</id>
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
        <file loc="$filename" ontologyid="$ontologyID"/>
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
	 * @param string $prefix Prefix used to distinguish 2 ontologies
	 *
	 * @return array of (Title t, string status)
	 *     status can be 'merge', 'conflict' or 'notexist'
	 */
	private function verifyOntology($inputfile, $bundleID, $prefix = '') {

		if( preg_match( '/\.gz$/', $inputfile ) ) {
			$filename = 'compress.zlib://' . $inputfile;
		}
		$fileHandle = fopen( $inputfile, 'rt' );
		return $this->verifyFromHandle( $fileHandle, $bundleID , $prefix);
	}

	/**
	 * Install/update ontology
	 *
	 * @param $file filepath relative to extension
	 * @param array $verificationLog
	 * @param string bundleID
	 * @param string $prefix
	 */
	private function installOrUpdateOntology($inputfile, $verificationLog, $bundleID, $prefix = '') {

		// remove ontology elements which do not exist anymore.
		global $dfgLang;
		$pagesToImport = array();

		foreach($verificationLog as $log) {
			list($title, $command)=$log;
			$pagesToImport[] = $title->getPrefixedText();
		}
		$bundleIDValue = SMWDataValueFactory::newTypeIDValue('_wpg', $bundleID);
		$pageValuesOfOntology = smwfGetStore()->getPropertySubjects(SMWPropertyValue::makeUserProperty($dfgLang->getLanguageString('df_partofbundle')), $bundleIDValue);
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
					print "\n\t[Removing page]: ".$title->getPrefixedText()."...";

					wfRunHooks('ArticleDeleteComplete', array(&$a, &$wgUser, "ontology removed: ".$bundleID, $id));
					print "done.]";
				}
			}


		}


		// install new or updated ontology
		if( preg_match( '/\.gz$/', $inputfile ) ) {
			$filename = 'compress.zlib://' . $inputfile;
		}
		$fileHandle = fopen( $inputfile, 'rt' );
		return $this->importFromHandle( $fileHandle, $bundleID , $prefix);
	}

	/**
	 * Verifies ontology file and checks if there are conflicts
	 *
	 * @param int $handle
	 * @param string $bundleID
	 * @param string $prefix
	 *
	 * @return array of (Title t, string status)
	 *     status can be 'merge', 'conflict' or 'notexist'
	 */
	private function verifyFromHandle( $handle, $bundleID , $prefix) {
		$source = new ImportStreamSource( $handle );
		$importer = new DeployWikiImporterDetector( $source, $bundleID, $prefix, 1, $this );

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
	 * @param string $prefix
	 *
	 */
	private function importFromHandle( $handle, $bundleID , $prefix) {


		$source = new ImportStreamSource( $handle );
		$importer = new DeployWikiOntologyImporter( $source, $bundleID, $prefix, 1, $this );

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
	 * @param string $bundleID
	 * @param boolean $noBundlePage
	 */
	private function convertOntology($inputfile, $outputfile, $bundleID, $noBundlePage = false) {
		// convert ontology

		$cwd = getcwd();
		$onto2mwxml_dir = $this->rootDir."/deployment/tools/onto2mwxml";
		print "\n[Convert ontology $inputfile...";

		chdir($onto2mwxml_dir);
		$ret = 0;
		if (Tools::isWindows()) {
			if (!file_exists("$onto2mwxml_dir/tsc")) {
				throw new Exception("Onto2MWXML tool is not correctly installed. Please take a look in deployment/tools/onto2mwxml/README.TXT.");
			} else {
				if ($noBundlePage) $noBundlePageParam = "--nobundlepage"; else $noBundlePageParam = "";
				exec("\"$onto2mwxml_dir/onto2mwxml.bat\" \"$inputfile\" -o \"$outputfile\" --bundleid \"$bundleID\" $noBundlePageParam", $output, $ret);
				if ($ret != 0) {
					foreach($output as $l) print "\n$l";
					throw new Exception("Onto2MWXML exited abnormally.");
				}
			}
		} else {
			if (!file_exists("$onto2mwxml_dir/tsc")) {
				throw new Exception("Onto2MWXML tool is not correctly installed. Please take a look in deployment/tools/onto2mwxml/README.TXT.");
			} else {
				if ($noBundlePage) $noBundlePageParam = "--nobundlepage"; else $noBundlePageParam = "";
				exec("\"$onto2mwxml_dir/onto2mwxml.sh\" \"$inputfile\" -o \"$outputfile\" --bundleid \"$bundleID\" $noBundlePageParam", $output, $ret);
				if ($ret != 0) {
					foreach($output as $l) print "\n$l";
					throw new Exception("Onto2MWXML exited abnormally.");
				}
			}
		}
		chdir($cwd);
		print "done.]";
		return $ret;
	}



	/**
	 * Checks for a conflict.
	 *
	 * @param array ($title, $msg) $verificationLog
	 * @param object $callback method askForOntologyPrefix(& $answer)
	 *
	 * @return mixed false if no conflict otherwise prefix to make solve conflict.
	 */
	private function checkForConflict($verificationLog, $callback) {
		$conflict = false;
		foreach($verificationLog as $l) {
			list($title, $msg) = $l;
			if ($msg == 'conflict') {
				print "\nConflict with: '$title'";
				$conflict = true;
				break;
			}
		}
		$answer=false;
		if ($conflict) {
			$callback->askForOntologyPrefix($answer);
		}

		return $answer;
	}


}