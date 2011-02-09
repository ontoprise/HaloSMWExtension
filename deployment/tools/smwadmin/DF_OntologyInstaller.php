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

	public function installOntologies($desc, $callback) {
		$ontologies = $desc->getOntologies();
		foreach($ontologies as $tuple) {
			list($loc, $ontologyID) = $tuple;
			
			// convert ontology 
			
			
			// read possible existing prefix if this is an update
			
			
			// verifies the ontologies
			print "\n[Verifying ontology $loc...";
			$prefix='';
			do {
				$verificationLog = $this->verifyOntology($desc->getInstallationDirectory()."/".$loc, $ontologyID, $prefix);
				$conflict = $this->checkForConflict($verificationLog, $callback);
				$prefix = $conflict;
			} while ($conflict !== false);
				
			print "done.";
			
			// do actual install/update
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
		$conflict = true;
		foreach($verificationLog as $l) {
			list($title, $msg) = $l;
			if ($msg == 'conflict') {
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
		$fileHandle = fopen( $this->rootDir."/".$filepath, 'rt' );
		return $this->importFromHandle( $fileHandle, $ontologyID , $prefix);
	}

	private function importFromHandle( $handle, $ontologyID , $prefix) {
			

		$source = new ImportStreamSource( $handle );
		$importer = new DeployWikiImporterDetector( $source, $ontologyID, $prefix, 1, $this );

		$importer->setDebug( false );

		$importer->doImport();
			
		$result = $importer->getResult();
		return $result;


	}
}