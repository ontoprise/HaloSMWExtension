<?php
/*  Copyright 2009, ontoprise GmbH
 *  This file is part of the Data Import-Extension.
 *
 *   The Data Import-Extension is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The Data Import-Extension is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  Author: Ingo Steinbauer
 */
if ( !defined( 'MEDIAWIKI' ) ) die;
global $sgagIP;
require_once("$sgagIP/includes/SGA_GardeningBot.php");
require_once("$sgagIP/includes/SGA_GardeningIssues.php");
require_once("$sgagIP/includes/SGA_ParameterObjects.php");

/**
 * This bot checks if Term Imports need updates and triggers them.
 *
 */


class TermImportUpdateBot extends GardeningBot {


	function __construct() {
		//todo:register termimportupdatebot
		parent::GardeningBot("smw_termimportupdatebot");
	}

	public function getHelpText() {
		//todo: provide help message
		return wfMsg('smw_gard_termimportupdatebothelp');
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
	
	/**
	 * This method is called by the bot framework. 
	 */
	public function run($paramArray, $isAsync, $delay) {
		echo "...started!\n";
		$result = "";
		$result = $this->updateTermImports();
		
		$date = getdate();
		$timeInTitle = $date["year"]."_".$date["mon"]."_".$date["mday"]."_".$date["hours"]."_".$date["minutes"]."_".$date["seconds"];
		return array($result, "TermImport:TermImport updates/".$timeInTitle);
	}
	
	/*
	 * Returns an array of strings that contains the term import
	 * definitions of all Term Imports that need to be updated
	 */
	private function getNecessaryTermImports(){
		SMWQueryProcessor::processFunctionParams(array("[[TermImport:+]] [[Category:TermImport]]")
			,$querystring,$params,$printouts);
		$queryResult = explode("|", 
			SMWQueryProcessor::getResultFromQueryString($querystring,$params,
			$printouts, SMW_OUTPUT_WIKI));
		
		unset($queryResult[0]);

		$necessaryTermImports = array();
		foreach($queryResult as $tiArticleName){
			$tiArticleName = substr($tiArticleName, 0, strpos($tiArticleName, "]]"));
			$xmlString = smwf_om_GetWikiText('TermImport:'.$tiArticleName);
			$start = strpos($xmlString, "<ImportSettings>");
			$end = strpos($xmlString, "</ImportSettings>") + 17 - $start;
			$xmlString = substr($xmlString, $start, $end);
			
			SMWQueryProcessor::processFunctionParams(array("[[belongsToTermImport::TermImport:".$tiArticleName."]]"
				,"?hasImportDate", "limit=1", "sort=hasImportDate", "order=descending", 
				"format=list", "mainlabel=-") 
				,$querystring,$params,$printouts);
		$queryResult =   
			SMWQueryProcessor::getResultFromQueryString($querystring,$params,
			$printouts, SMW_OUTPUT_WIKI);
		
			// timestamp creation depends on property type (page or date)
			$queryResult = trim(substr($queryResult, strpos($queryResult, "]]")+2));
			if(strpos($queryResult, "[[:") === 0){ //type page
				$queryResult = trim(substr($queryResult, strpos($queryResult, "|")+1));
				$queryResult = trim(substr($queryResult, 0, strpos($queryResult, "]")));
			} else { //type date
				$queryResult = trim(substr($queryResult, 0, strpos($queryResult, "[")));
			}
			$timestamp = strtotime($queryResult);

			$simpleXMLElement = new SimpleXMLElement($xmlString);
			$maxAge = $simpleXMLElement->xpath("//UpdatePolicy/maxAge/@value");
		
			if($maxAge != ""){
				if($timestamp == 0 || (wfTime() - $timestamp - $maxAge[0]->value*60) > 0){
					echo("\nRun this term import: ".$tiArticleName);
					$necessaryTermImports[$tiArticleName] = $xmlString;
				}	
			}
		}
		return $necessaryTermImports;
	}
	
	/**
	*	Checks if Term Imports need to be updated and triggers them 
	 */
	private function updateTermImports(){
		global $smwgDIIP;
		require_once($smwgDIIP."/specials/TermImport/SMW_WIL.php");
		
		global $wgUser;
		//todo
		$wgUser = User::newFromName('WikiSysop');
		
		$necessaryTermImports = $this->getNecessaryTermImports();	
		foreach($necessaryTermImports as $termImportName => $xmlString){
			echo("\nProcessing term import: " .$termImportName. "\n");
			
			$simpleXMLElement = new SimpleXMLElement($xmlString);

			$moduleConfig = $simpleXMLElement->xpath("//ModuleConfiguration");
			$moduleConfig = trim($moduleConfig[0]->asXML());

			$dataSource = $simpleXMLElement->xpath("//DataSource");
			$dataSource = trim($dataSource[0]->asXML());

			$mappingPolicy = $simpleXMLElement->xpath("//MappingPolicy");
			$mappingPolicy = trim($mappingPolicy[0]->asXML());

			$conflictPolicy = $simpleXMLElement->xpath("//ConflictPolicy");
			$conflictPolicy = trim($conflictPolicy[0]->asXML());

			$inputPolicy = $simpleXMLElement->xpath("//InputPolicy");
			$inputPolicy = trim($inputPolicy[0]->asXML());

			$importSets = $simpleXMLElement->xpath("//ImportSets");
			$importSets = trim($importSets[0]->asXML());

			$wil = new WIL();
			$terms = $wil->importTerms($moduleConfig, $dataSource, $importSets, $inputPolicy,
				$mappingPolicy, $conflictPolicy, $termImportName, false);
			
		}
	}
	
	private function startBot(){
		$bot = $registeredBots[$botID];
		$taskid = SGAGardeningLog::getGardeningLogAccess()->addGardeningTask($botID);
		$log = $bot->run($paramArray, $runAsync, 0);
 		$log .= "\n[[category:GardeningLog]]";
 		SGAGardeningLog::getGardeningLogAccess()->markGardeningTaskAsFinished($taskid, $log);
	}
}

// Create one instance to register the bot.
new TermImportUpdateBot();

define('SMW_TERMIMPORTUPDATE_BOT_BASE', 22000);
//define('SMW_GARDISSUE_ADDED_ARTICLE', SMW_TERMIMPORT_BOT_BASE * 100 + 1);
//define('SMW_GARDISSUE_UPDATED_ARTICLE', (SMW_TERMIMPORTUPDATE_BOT_BASE+1) * 100 + 1);
//define('SMW_GARDISSUE_MISSING_ARTICLE_NAME', (SMW_TERMIMPORTUPDATE_BOT_BASE+2) * 100 + 1);
//define('SMW_GARDISSUE_CREATION_FAILED', (SMW_TERMIMPORTUPDATE_BOT_BASE+2) * 100 + 2);
//define('SMW_GARDISSUE_UPDATE_SKIPPED', (SMW_TERMIMPORTUPDATE_BOT_BASE+3) * 100 + 1);

class TermImportUpdateBotIssue extends GardeningIssue {
	
	public function __construct($bot_id, $gi_type, $t1_ns, $t1, $t2_ns, $t2, $value, $isModified) {
		parent::__construct($bot_id, $gi_type, $t1_ns, $t1, $t2_ns, $t2, $value, $isModified);
	}

	protected function getTextualRepresenation(& $skin, $text1, $text2, $local = false) {
		//todo provide messages
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

class TermImportUpdateBotFilter extends GardeningIssueFilter {

	public function __construct() {
		parent::__construct(SMW_TERMIMPORTUPDATE_BOT_BASE);
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
		$gis = $gi_store->getGardeningIssues('smw_termimportupdatebot', NULL, $gi_class, $title, SMW_GARDENINGLOG_SORTFORTITLE, NULL);
		$gic[] = new GardeningIssueContainer($title, $gis);


		return $gic;
	}
}

?>