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

/**
 * @file
 * @ingroup DITermImport
 * 
 * @author Thomas Schweitzer, Ingo Steinbauer
 */

if ( !defined( 'MEDIAWIKI' ) ) die;

//todo: change SGA so that this is not necessary anymore 
global $sgagIP;
require_once("$sgagIP/includes/SGA_GardeningBot.php");
require_once("$sgagIP/includes/SGA_GardeningIssues.php");
require_once("$sgagIP/includes/SGA_ParameterObjects.php");

/**
 * This bot imports terms of an external vocabulary.
 */
class TermImportBot extends GardeningBot {

	private $dateString = null;
	private $importErrors = array();
	
	function __construct() {
		parent::GardeningBot("smw_termimportbot");
	}

	public function getHelpText() {
		return wfMsg('smw_gard_termimportbothelp');
	}

	public function getLabel() {
		return wfMsg($this->id);
	}

	/**
	 * Returns an array of parameter objects
	 */
	public function createParameters() {
		$params = array();
		return $params;
	}

	public function isVisible() {
		return false;
	}

	/**
	 * This method is called by the bot framework. $paramArray contains the
	 * name of the Wiki article that contains the term import definition
	 */
	public function run($paramArray, $isAsync, $delay) {
		echo "\r\nBot executed!\n";
		
		$result = "";
		
		$termImportName = $paramArray["termImportName"];
		
		$timeInTitle = $this->getDateString();

		$this->createTermImportResultContentPreview($termImportName);
		
		$result = $this->importTerms($termImportName);
		
		if($result != wfMsg('smw_ti_import_successful')){
			$this->importErrors[] = $result;
		}
		
		$this->createTermImportResultContent($termImportName);
		
		//bot is executed in maintenaince mode in which no semantic data is stored to tsc
		//therefore refresh tsc after bot is done
		global $smwgDefaultStore;
		if($smwgDefaultStore == 'SMWTripleStore' || $smwgDefaultStore == 'SMWTripleStoreQuad'){
			define('SMWH_FORCE_TS_UPDATE', 'TRUE');
			smwfGetStore()->initialize(true);
		}
		
		return array($result, "TermImport:".$termImportName."/".$timeInTitle);
	}

	/**
	 * This function sets up the modules of the import framework according to the
	 * settings, reads the terms and creates articles for them.
	 */
	public function importTerms($termImportName) {
		echo "\r\nStarting to import terms for $termImportName...\n";

		//get settings from wiki article
		$settings = smwf_om_GetWikiText('TermImport:'.$termImportName);
		$start = strpos($settings, "<ImportSettings>");
		$end = strpos($settings, "</ImportSettings>") + 17 - $start;
		$settings = substr($settings, $start, $end);
	
		$parser = new DIXMLParser($settings);
		$result = $parser->parse();
		if ($result !== TRUE) {
			return $result;
		}

		$dalModule = $parser->getValuesOfElement(array('DALModule', 'ID'));
		if (count($dalModule) == 0) {
			$dalModule = $parser->getValuesOfElement(array('DALModules', 'Module', 'id'));
		}
		if (count($dalModule) == 0) {
			//todo: language
			return "Error: Data access layer module was not defined."; 
		}

		$dam = DIDAMRegistry::getDAM($dalModule[0]);
		if(!$dam){
			//todo: language
			return "Connecting the data access layer module $dalModule[0] failed."; 
		}
		$damId = $dalModule[0];

		$settingsXML = new SimpleXMLElement($settings);
		$source = $settingsXML->xpath('//DataSource');
		$source = $source[0]->asXML();
		
		$importSets = $parser->getValuesOfElement(array('ImportSets', 'ImportSet', 'Name'));
		if(count($importSets) > 0){
			$importSet = trim(''.$importSets[0]);
		} else {
			$importSet = '';
		}
		
		$inputPolicy = $parser->serializeElement(array('InputPolicy'));
		$conflictPolicy = $parser->serializeElement(array('ConflictPolicy'));
		$creationPattern = $parser->serializeElement(array('CreationPattern'));
		
		echo("\r\nGet Terms");
		$terms = $dam->getTerms($source, $importSet, $inputPolicy, $conflictPolicy);
		echo("\r\nTerms in place");
		
		try {
			$result = $this->createArticles($terms, $creationPattern, $conflictPolicy, $dam,$termImportName, $damId);
			
			echo "\r\nBot finished!\n";
			if ($result === true) {
				$result = wfMsg('smw_ti_import_successful');
			}
		} catch (Exception $e){
			$result = "Something bad happened during the Term Import: ".$e;
		}
		return $result;
	}

	/**
	 * Creates articles for the terms according to the creation pattern and conflict policy.
	 */
	private function createArticles($termsCollection, $creationPattern, $conflictPolicy, $dam, $termImportName, $damId) {
		
		echo("\r\nStart to create articles");

		$log = SGAGardeningIssuesAccess::getGardeningIssuesAccess();
		
		$parser = new DIXMLParser($creationPattern);
		$templateName = '';
		$extraCategories = '';
		$delimiter = '';
		if ($parser->parse() === TRUE) {
			$templateName = $parser->getValuesOfElement(array('CreationPattern','TemplateName'));
			if (is_array($templateName) && count($templateName) > 0){
				$templateName = trim(strip_tags($templateName[0]));
			} else {
				$templateName = '';
			}	 
		
			$extraCategories = $parser->getValuesOfElement(array('CreationPattern','ExtraCategories'));
			if (is_array($extraCategories) && count($extraCategories) > 0){
				$extraCategories = trim(strip_tags($extraCategories[0]));
			} else {
				$extraCategories= '';
			}
		
			$delimiter = $parser->getValuesOfElement(array('CreationPattern','Delimiter'));
			if (is_array($delimiter) && count($delimiter) > 0){
				$delimiter = trim(strip_tags($delimiter[0]));
			} else {
				$delimiter= '';
			}
		}
		
		$parser = new DIXMLParser($conflictPolicy);
		$cp = 'overwrite';
		if ($parser->parse() === TRUE) {
			$cp = $parser->getValuesOfElement(array('ConflictPolicy','Name'));
			$cp = $cp[0];
		}
		
		global $ditigConflictPolicies;
		$cp = $ditigConflictPolicies[$cp];
		$cp = new $cp();
		
		$overwriteExistingArticles = false;
		if($cp == 'ignore'){
			$overwriteExistingArticles = false;
		}
		
		$terms = $termsCollection->getTerms();
		$numTerms = count($terms);
		echo("\r\nNumber of terms: ".$numTerms."\n");
		$this->setNumberOfTasks(1);
		$this->addSubTask($numTerms);

		$timeInTitle = $this->getDateString();
		$termImportName = "TermImport:".$termImportName."/".$timeInTitle;
		$noErrors = true;
		foreach($terms as $term){
			
			//deal with callbacks
			foreach($term->getCallbacks() as $callback){
				list($callBackSucces, $logMsgs) = $dam->executeCallback(
					$callback, $templateName, $extraCategories, $delimiter, $overwriteExistingArticles, $termImportName);
				
				foreach($logMsgs as $logMsg){
					$log->addGardeningIssueAboutArticle(
						$this->id, $logMsg['id'], 
						Title::newFromText($logMsg['title']));
				}
				
				if(!$callBackSucces){
					$noErrors = false;
				}	
			} 
			
			//import new term if this is not an anonymous callback term
			if(!$term->isAnnonymousCallbackTerm()){
				
				$caResult = $this->createArticle($term, $templateName, $extraCategories, $delimiter, $cp, $termImportName, $damId);
				$this->worked(1);
	
				if ($caResult !== true) {
					$noErrors = false;
					$this->importErrors[] = $caResult;
				}
			} else {
				$this->worked(1);
			}
		}
		
		foreach($termsCollection->getErrorMsgs() as $msg){
			$this->importErrors[] = $msg;
		}
		
		if($noErrors){
			return wfMsg('smw_ti_import_successful');
		} else {
			return wfMsg('smw_ti_import_errors');
		} 		
	}

	/**
	 * Creates an article for the given term according to the creation pattern and
	 * conflict policy
	 */
	private function createArticle($term, $templateName, $extraCategories, $delimiter, $conflictPolicy, $termImportName, $damId) {
		
		$log = SGAGardeningIssuesAccess::getGardeningIssuesAccess();

		$title = $term->getArticleName();
		if (!$title) {
			echo("\r\n".wfMsg('smw_ti_missing_articlename'));
			$log->addGardeningIssueAboutArticle(
				$this->id, SMW_GARDISSUE_MISSING_ARTICLE_NAME,
				Title::newFromText(wfMsg('smw_ti_import_error')));
			return wfMsg('smw_ti_missing_articlename');
		}
		
		if ($title == '') {
			echo("\r\n".wfMsg('smw_ti_invalid_articlename', $title));
			$log->addGardeningIssueAboutArticle(
				$this->id, SMW_GARDISSUE_MISSING_ARTICLE_NAME,
				Title::newFromText(wfMsg('smw_ti_import_error')));
			return wfMsg('smw_ti_invalid_articlename', $title);
		}

		$title = Title::newFromText($title);
		
		//check if this is a valid title
		if(true || is_null($title)){
			$title = $term->getSaferArticleName();
			$title = Title::newFromText($title);

			if (is_null($title)) {
				echo("\r\n".wfMsg('smw_ti_invalid_articlename', $term->getArticleName()));
				$log->addGardeningIssueAboutArticle(
					$this->id, SMW_GARDISSUE_MISSING_ARTICLE_NAME,
					Title::newFromText(wfMsg('smw_ti_import_error')));
				return wfMsg('smw_ti_invalid_articlename', $term->getSaferArticleName());
			}
		}
		
		$result = $conflictPolicy->createArticle(
			$term, $templateName, $extraCategories, $delimiter, $title, $termImportName, $log, $this->id, $damId);
		return $result;
	}

	private function createTermImportResultContent($termImportName){
		$result = "__NOTOC__\n";
		$result .= "==== Import summary ====";
		$result .= "\n\nTerm Import definition: [[belongsToTermImport::TermImport:".$termImportName."|"
			.$termImportName."]]"." [[BelongsToTermImportWithLabel::".$termImportName."| ]]";
		$result .= "\n\nImport date: [[hasImportDate::";
		$result .= $this->getDateString()."]]";
			
		
		if(count($this->importErrors) > 0){
			$result .= "\n\nResult: Some errors occured.[[wasImportedSuccessfully::false| ]] (Please see errors below.)";
		} else {
			$result .= "\n\nResult: Term import has been completed successfully.[[wasImportedSuccessfully::true| ]]";
		}
		$result .= "\n==== Added terms ====\n";
		$result .= "{{#ask: [[WasAddedDuringTermImport::TermImport:".$termImportName."/"
			.$this->getDateString()."]] | format=ul}}";
		
		$result .= "\n==== Updated terms ====\n";
		$result .= "{{#ask: [[WasUpdatedDuringTermImport::TermImport:".$termImportName."/"
			.$this->getDateString()."]]| format=ul}}";
		
		$result .= "\n==== Ignored terms ====\n";
		$result .= "{{#ask: [[IgnoredDuringTermImport::TermImport:".$termImportName."/"
			.$this->getDateString()."]]| format=ul}}";
		
		if(count($this->importErrors) > 0){
			$result .= "\n==== Import errors ====\n";
			foreach($this->importErrors as $error){
				$result .= "\n* ".$error;
			}
		}
		$result .= "\n[[Category:TermImportRun]]";

		$timeInTitle = $this->getDateString();
		smwf_om_EditArticle("TermImport:".$termImportName
			."/".$timeInTitle, 'TermImportBot', $result, '');
		smwf_om_TouchArticle("TermImport:".$termImportName);
	}
	
	private function createTermImportResultContentPreview($termImportName){
		$result = "__NOTOC__\n";
		$result .= "==== Import summary ====";
		$result .= "\n\nTerm Import definition: [[belongsToTermImport::TermImport:".$termImportName."|"
			.$termImportName."]]"." [[belongsToTermImportWithLabel::".$termImportName."| ]]";
		$result .= "\n\nImport date: [[hasImportDate::";
		$result .= $this->getDateString()."]]";
			
		$result .= "\n\nResult: Some errors occured.[[wasImportedSuccessfully::false| ]] (Check [[Special:Gardening]] if Term Import is finished.)";
		
		$result .= "\n==== Added terms ====\n";
		$result .= "{{#ask: [[WasAddedDuringTermImport::TermImport:".$termImportName."/"
			.$this->getDateString()."]]| format=ul}}";
		
		$result .= "\n==== Updated terms ====\n";
		$result .= "{{#ask: [[WasUpdatedDuringTermImport::TermImport:".$termImportName."/"
			.$this->getDateString()."]]| format=ul}}";
		
		$result .= "\n==== Ignored terms ====\n";
		$result .= "{{#ask: [[IgnoredDuringTermImport::TermImport:".$termImportName."/"
			.$this->getDateString()."]]| format=ul}}";
		
		$result .= "\n[[Category:TermImportRun]]";
		
		$timeInTitle = $this->getDateString();
		
		smwf_om_EditArticle("TermImport:".$termImportName
			."/".$timeInTitle, 'TermImportBot', $result, '');
		//smwf_om_TouchArticle("TermImport:".$termImportName."/".$timeInTitle);
		smwf_om_TouchArticle("TermImport:".$termImportName);
	}

	private function getDateString(){
		if($this->dateString == null){
			$date = getdate();
			$mon = $date["mon"]<10 ? "0".$date["mon"] : $date["mon"];
			$mday = $date["mday"]<10 ? "0".$date["mday"] : $date["mday"];
			$hours = $date["hours"]<10 ? "0".$date["hours"] : $date["hours"];
			$minutes = $date["minutes"]<10 ? "0".$date["minutes"] : $date["minutes"];
			$seconds = $date["seconds"]<10 ? "0".$date["seconds"] : $date["seconds"];

			$this->dateString = $date["year"]."/".$mon."/".$mday." "
			.$hours.":".$minutes.":".$seconds;
		}
		return $this->dateString;
	}
}

define('SMW_TERMIMPORT_BOT_BASE', 2200);
define('SMW_GARDISSUE_ADDED_ARTICLE', SMW_TERMIMPORT_BOT_BASE * 100 + 1);
define('SMW_GARDISSUE_UPDATED_ARTICLE', (SMW_TERMIMPORT_BOT_BASE+1) * 100 + 2);
define('SMW_GARDISSUE_MISSING_ARTICLE_NAME', (SMW_TERMIMPORT_BOT_BASE+2) * 100 + 3);
define('SMW_GARDISSUE_CREATION_FAILED', (SMW_TERMIMPORT_BOT_BASE+3) * 100 + 4);
define('SMW_GARDISSUE_UPDATE_SKIPPED', (SMW_TERMIMPORT_BOT_BASE+4) * 100 + 5);

class TermImportBotIssue extends GardeningIssue {

	public function __construct($bot_id, $gi_type, $t1_ns, $t1, $t2_ns, $t2, $value, $isModified) {
		parent::__construct($bot_id, $gi_type, $t1_ns, $t1, $t2_ns, $t2, $value, $isModified);
	}

	protected function getTextualRepresenation(& $skin, $text1, $text2, $local = false) {
		switch($this->gi_type) {
			case SMW_GARDISSUE_ADDED_ARTICLE:
				return wfMsg('smw_ti_added_article', $text1);
			case SMW_GARDISSUE_UPDATED_ARTICLE:
				return wfMsg('smw_ti_updated_article', $text1);
			case SMW_GARDISSUE_MISSING_ARTICLE_NAME:
				return wfMsg('smw_ti_missing_articlename');
			case SMW_GARDISSUE_CREATION_FAILED:
				return wfMsg('smw_ti_creation_failed', $text1);
			case SMW_GARDISSUE_UPDATE_SKIPPED:
				return wfMsg('smw_ti_articleNotUpdated', $text1);
			
			default: return NULL;

		}
	}
}

class TermImportBotFilter extends GardeningIssueFilter {

	public function __construct() {
		parent::__construct(SMW_TERMIMPORT_BOT_BASE);
		$this->gi_issue_classes = array(wfMsg('smw_gardissue_class_all'),
		wfMsg('smw_gardissue_ti_class_added_article'),
		wfMsg('smw_gardissue_ti_class_updated_article'),
		wfMsg('smw_gardissue_ti_class_system_error'),
		wfMsg('smw_gardissue_ti_class_update_skipped'));
	}

	public function getUserFilterControls($specialAttPage, $request) {
		return '';
	}

	public function linkUserParameters(& $wgRequest) {
		return array('pageTitle' => $wgRequest->getVal('pageTitle'));
	}

	public function getData($options, $request) {
		$pageTitle = $request->getVal('pageTitle');
		if ($pageTitle != NULL) {
			// show only issue of *ONE* title
			return $this->getGardeningIssueContainerForTitle($options, $request, Title::newFromText(urldecode($pageTitle)));
		} else return parent::getData($options, $request);
	}

	private function getGardeningIssueContainerForTitle($options, $request, $title) {
		$gi_class = $request->getVal('class') == 0 ? NULL : $request->getVal('class') + $this->base - 1;


		$gi_store = SGAGardeningIssuesAccess::getGardeningIssuesAccess();

		$gic = array();
		$gis = $gi_store->getGardeningIssues('smw_termimportbot', NULL, $gi_class, $title, SMW_GARDENINGLOG_SORTFORTITLE, NULL);
		$gic[] = new GardeningIssueContainer($title, $gis);

		return $gic;
	}
}