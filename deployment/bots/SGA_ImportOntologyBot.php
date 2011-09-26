<?php
/**
 * @file
 * @ingroup ImportOntologyBot
 * 
 * @defgroup ImportOntologyBot
 * @ingroup SemanticGardeningBots
 * 
 * @author Kai K�hn
 * Created on 03.07.2007
 *
 * Author: kai
 */
if ( !defined( 'MEDIAWIKI' ) ) die;

global $sgagIP;
require_once("$sgagIP/includes/SGA_GardeningBot.php");
require_once("$sgagIP/includes/SGA_ParameterObjects.php");
require_once("$sgagIP/includes/SGA_GardeningIssues.php");

define('XML_SCHEMA_NS', 'http://www.w3.org/2001/XMLSchema#');

class ImportOntologyBot extends GardeningBot {

	private static $OWL_VALUES_FROM;

	// global log which contains wiki-markup
	private $globalLog;

	// use labels or localnames
	private $useLabels = true;

	// use ontology ID as a marker for the imported ontology
	private $ontologyID;

	function ImportOntologyBot() {
		parent::GardeningBot("smw_importontologybot");
		$this->globalLog = "== The following wiki pages were created during import: ==\n\n";
			
			
	}

	public function getHelpText() {
		return wfMsg('smw_gard_import_docu');
	}

	public function getLabel() {
		return wfMsg($this->id);
	}



	/**
	 * Returns an array mapping parameter IDs to parameter objects
	 */
	public function createParameters() {

		$param1 = new GardeningParamFileList('GARD_IO_FILENAME', "", SMW_GARD_PARAM_REQUIRED, 'owl');
		$param2= new GardeningParamFileList('GARD_IO_FILENAME', "", SMW_GARD_PARAM_REQUIRED, 'obl');
		return array($param1,$param2);
	
	}

	/**
	 * Import ontology
	 * Do not use echo when it is not running asynchronously.
	 */
	public function run($paramArray, $isAsync, $delay) {
                $this->globalLog = "";
		// do not allow to start synchronously.
		
		$fileName = urldecode($paramArray['GARD_IO_FILENAME']);
		$this->useLabels = false; //array_key_exists('GARD_IO_USE_LABELS', $paramArray);
		$this->ontologyID = urldecode($paramArray['GARD_IO_ONTOLOGY_ID']);

		$fileTitle = Title::newFromText($fileName);
		$fileLocation = wfFindFile($fileTitle)->getPath();

		global $IP;
	       chdir($IP.'/deployment/tools');
		   
		   print "\nImport file: $fileLocation";
	       exec($IP.'/deployment/tools/smwadmin -i "'.$fileLocation.'"', $out, $ret);
		   if ($ret != 0) {
				$errorText = implode("Error !\n", $out); 
				return $errorText;
		   }
	       return "done";
	}
	
	public function canBeRun() {
	    $req = true;
	    $filename = '././././deployment';
		$filename1 = '././././deployment/tools/onto2mwxml/tsc';
		if (!file_exists($filename)) {
            $req = false;
    		}
		if(!file_exists($filename1)){
			$req = false;
			}	    	
		return $req;
	}
	
	public function importOntology_TSC() {
	    $tsc = true;
		$filename1 = '././././deployment/tools/onto2mwxml/tsc';
		if (!file_exists($filename1)) {
			$tsc = $tsc;
    		}
		return $tsc;
	}

    public function importOntology_df() {
	    $df = true;
	    $filename = '././././deployment';
		if(!file_exists($filename)){
			$df = false;
			}	
		return $df;
	}
}
/*
 * Note: This bot filter has no real functionality. It is just a dummy to
 * prevent error messages in the GardeningLog. There are no gardening issues
 * about importing. Instead there's a textual log.
 *
 */
define('SMW_IMPORTONTOLOGY_BOT_BASE', 700);

class ImportOntologyBotFilter extends GardeningIssueFilter {



	public function __construct() {
		parent::__construct(SMW_IMPORTONTOLOGY_BOT_BASE);
		$this->gi_issue_classes = array(wfMsg('smw_gardissue_class_all'));

	}

	public function getUserFilterControls($specialAttPage, $request) {
		return '';
	}

	public function linkUserParameters(& $wgRequest) {

	}

	public function getData($options, $request) {
		parent::getData($options, $request, 0);
	}
}



// For importing an ontology please do not use the ImportBot any longer.
// Instead use the deployment framework: smwadmin -i <ontology-file>
// This will read the ontology and create appropriate wiki pages.  
