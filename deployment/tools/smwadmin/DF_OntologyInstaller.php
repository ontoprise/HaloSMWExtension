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

	public function installOntologies($desc) {
		$ontologies = $desc->getOntologies();
		foreach($ontologies as $tuple) {
			list($loc, $ontologyID) = $tuple;
			
			// verifies the ontologies
			print "\n[Verifying ontology $loc...";
			$result = $this->verifyOntology($desc->getInstallationDirectory()."/".$loc, $ontologyID);
			var_dump($result);
			print "done.";
		}
	}

	/**
	 * Checks what would happen if the given ontology would be imported.
	 *
	 * @param $file filepath relative to extension
	 * @param string ontologyID
	 */
	public function verifyOntology($filepath, $ontologyID) {

		if( preg_match( '/\.gz$/', $filepath ) ) {
			$filename = 'compress.zlib://' . $filepath;
		}
		$fileHandle = fopen( $this->rootDir."/".$filepath, 'rt' );
		return $this->importFromHandle( $fileHandle, $ontologyID );
	}

	private function importFromHandle( $handle, $ontologyID ) {
			

		$source = new ImportStreamSource( $handle );
		$importer = new DeployWikiImporterDetector( $source, $ontologyID, 1, $this );

		$importer->setDebug( false );

		$importer->doImport();
			
		$result = $importer->getResult();
		return $result;


	}
}