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


	public function installOntology($ontologyID, $inputfile, $callback) {

		$outputfile = $inputfile.".xml";
		try {
			$ret = $this->convertOntology($inputfile, $outputfile);

			if ($ret != 0) {
				print "\nCould not convert ontology.";
				die(1);
			}
		} catch(Exception $e) {
			// onto2mwxml might not be installed
			print "\nCould not convert ontology. Reason: ";
			print $e->getMessage();
			die(1);
		}

		// read possible existing prefix if this is an update
		$prefixFile = $inputfile.".prefix";
		if (file_exists($prefixFile)) {
			$prefix = trim(file_get_contents($prefixFile));
		} else {
			$prefix='';
		}

		$outputfile_rel = $inputfile.".xml";
		// verifies the ontologies
		print "\n[Verifying ontology $inputfile...";
		do {
			$verificationLog = $this->verifyOntology($outputfile_rel, $ontologyID, $prefix);

			 //var_dump($verificationLog);
			$conflict = $this->checkForConflict($verificationLog, $callback);
			if ($conflict !== false) $prefix = $conflict;

		} while ($conflict !== false);
		print "done.";

		if ($prefix != '') {
			// write prefix file
			print "\n[Conflict detected. Using prefix '$prefix']";
			$handle = fopen($prefixFile, "w");
			fwrite($handle, $prefix);
			fclose($handle);
		} else {
			print "\n[No Conflict detected]";
		}

		// do actual install/update
		print "\n[Installing/updating ontology $inputfile...";
		$this->installOrUpdateOntology($outputfile_rel, $verificationLog, $ontologyID, $prefix);
	}

	/**
	 * Converts an ontology via onto2mwxml.
	 *
	 * @param $inputfile
	 * @param $outputfile
	 */
	private function convertOntology($inputfile, $outputfile) {
		// convert ontology
		print "\n[Convert ontology...";

		$cwd = getcwd();
		$onto2mwxml_dir = $this->rootDir."/deployment/tools/onto2mwxml";
		echo $onto2mwxml_dir;
		chdir($onto2mwxml_dir);
		$ret = 0;
		if (Tools::isWindows()) {
			if (!file_exists("$onto2mwxml_dir/onto2mwxml.exe")) {
				if (!file_exists($outputfile)) {
					throw new Exception("Onto2MWXML tool is not installed.");
				}
			} else {
				if (!file_exists($outputfile)) {
					exec("$onto2mwxml_dir/onto2mwxml.exe $inputfile -o $outputfile", $output, $ret);
				}
			}
		} else {
			if (!file_exists("$onto2mwxml_dir/onto2mwxml.sh")) {
				if (!file_exists($outputfile)) {
					throw new Exception("Onto2MWXML tool is not installed.");
				}
			} else {
				if (!file_exists($outputfile)) {
					exec("$onto2mwxml_dir/onto2mwxml.sh $inputfile -o $outputfile", $output, $ret);
				}

			}
		}
		chdir($cwd);
		print "done.]";
		return $ret;
	}

	/**
	 * Installs or updates ontologies specified in the deploy descriptor
	 *
	 * @param DeployDescriptor $desc
	 * @param object $callback method askForOntologyPrefix(& $answer)
	 */
	public function installOntologies($desc, $callback) {
		$ontologies = $desc->getOntologies();
		foreach($ontologies as $tuple) {
			list($loc, $ontologyID) = $tuple;
			$this->installOntology($ontologyID, $this->rootDir.$desc->getInstallationDirectory()."/".$loc, $callback);
		}
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
			$callback->askForOntologyPrefix(& $answer);
		}
		return $answer;
	}

	/**
	 * Checks what would happen if the given ontology would be imported.
	 *
	 * @param $file filepath relative to extension
	 * @param string ontologyID
	 */
	public function verifyOntology($filepath, $ontologyID, $prefix = '') {

		if( preg_match( '/\.gz$/', $filepath ) ) {
			$filename = 'compress.zlib://' . $filepath;
		}
		$fileHandle = fopen( $filepath, 'rt' );
		return $this->readFromHandle( $fileHandle, $ontologyID , $prefix);
	}

	/**
	 * Install/update ontology
	 *
	 * @param $file filepath relative to extension
	 * @param array $verificationLog
	 * @param string ontologyID
	 * @param string $prefix
	 */
	public function installOrUpdateOntology($filepath, $verificationLog, $ontologyID, $prefix = '') {

		// remove ontology elements which do not exist anymore.
		global $dfgLang;
		$pagesToImport = array();

		foreach($verificationLog as $log) {
			list($title, $command)=$log;
			$pagesToImport[] = $title->getPrefixedText();
		}
		$pageValuesOfOntology = smwfGetStore()->getAllPropertySubjects(SMWPropertyValue::makeUserProperty($dfgLang->getLanguageString('df_part_of_ontology')));
		$existingPages = array();
		foreach($pageValuesOfOntology as $pv) {
			$existingPages[] = $pv->getTitle()->getPrefixedText();
		}


		$pagesToDelete = array_diff($existingPages, $pagesToImport);


		foreach($pagesToDelete as $p) {
			$title = Title::newFromText($p);
			$a = new Article($title);
			print "\n\tRemove page: ".$title->getPrefixedText();
			$a->doDeleteArticle("ontology removed: ".$ontologyID);

		}

			
		// install new or updated ontology
		if( preg_match( '/\.gz$/', $filepath ) ) {
			$filename = 'compress.zlib://' . $filepath;
		}
		$fileHandle = fopen( $filepath, 'rt' );
		return $this->importFromHandle( $fileHandle, $ontologyID , $prefix);
	}

	private function readFromHandle( $handle, $ontologyID , $prefix) {
			

		$source = new ImportStreamSource( $handle );
		$importer = new DeployWikiImporterDetector( $source, $ontologyID, $prefix, 1, $this );

		$importer->setDebug( false );

		$importer->doImport();
			
		$result = $importer->getResult();
		return $result;


	}

	private function importFromHandle( $handle, $ontologyID , $prefix) {


		$source = new ImportStreamSource( $handle );
		$importer = new DeployWikiOntologyImporter( $source, $ontologyID, $prefix, 1, $this );

		$importer->setDebug( false );

		$importer->doImport();

		$result = $importer->getResult();
		return $result;


	}
}