<?php
/*  Copyright 2008, ontoprise GmbH
 *  This file is part of the Data Import-Extension.
 *
 *   The Data Import-Extension is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 * @file
 * @ingroup DIWebServices
 * 
 * @author Ingo Steinbauer
 */

if ( !defined( 'MEDIAWIKI' ) ) die;
global $sgagIP;
require_once("$sgagIP/includes/SGA_GardeningBot.php");
require_once("$sgagIP/includes/SGA_GardeningIssues.php");
require_once("$sgagIP/includes/SGA_ParameterObjects.php");



// needed for db access
global $smwgDIIP;
require_once("$smwgDIIP/specials/WebServices/SMW_WSStorage.php");
require_once("$smwgDIIP/specials/WebServices/SMW_WebServiceUsage.php");

/**
 * This bot updates outdated ws-cache-entries that are used in semantic
 * properties
 *
 */
class WSUpdateBot extends GardeningBot {

	/**
	 * Constructor
	 *
	 */
	function __construct() {
		parent::GardeningBot("smw_wsupdatebot");
	}


	public function getHelpText() {
		return wfMsg('smw_ws_updatebothelp');
	}

	public function getLabel() {
		return wfMsg($this->id);
	}

	//	public function allowedForUserGroups() {
	//		return array(SMW_GARD_GARDENERS, SMW_GARD_SYSOPS, SMW_GARD_ALL_USERS);
	//	}

	public function createParameters() {
		return array();
	}

	public function getImageDirectory() {
		return 'extensions/DataImport/skins/webservices';
	}

	public function run($paramArray, $isAsync, $delay) {
		echo("bot started\n");
		if(array_key_exists("WS_WSID", $paramArray) && $paramArray["WS_WSID"] != null){
			$log = SGAGardeningIssuesAccess::getGardeningIssuesAccess();
			$ws = WebService::newFromID($paramArray["WS_WSID"]);
			$affectedArticles = $this->updateWSResults($ws);
			$this->setNumberOfTasks(2);
		} else {
			$affectedArticles = $this->updateAllWSResults();
		}

		ksort($affectedArticles);

		$affectedArticles = array_flip($affectedArticles);

		echo("\nRefreshing articles: \n");

		foreach($affectedArticles as $articleId => $dontCare){
			echo("\t refreshing articleId: " . $articleId . "\n");
			$title = Title::newFromID($articleId);
			$updatejob = new SMWUpdateJob($title);
			$updatejob->run();
		}

		echo("\nbot finished");
		
		global $smwgDefaultStore;
		if($smwgDefaultStore == 'SMWTripleStore' || $smwgDefaultStore == 'SMWTripleStoreQuad'){
			define('SMWH_FORCE_TS_UPDATE', 'TRUE');
			smwfGetStore()->initialize(true);
		}
		
		return '';
	}

	/**
	 * This method updates cache entries that are used in properties
	 * that are outdated for all webservices
	 *
	 */
	private function updateAllWSResults(){
		$log = SGAGardeningIssuesAccess::getGardeningIssuesAccess();
		$affectedArticles = array();
		$webServices = WSStorage::getDatabase()->getWebservices();
		$this->setNumberOfTasks(count($webServices) + 1);
		foreach($webServices as $ws){
			echo("\n\n".$ws->getName()."\n");
			if($ws->getConfirmationStatus() == "once"
			|| $ws->getConfirmationStatus() == "false"){
				$log->addGardeningIssueAboutValue(
				$this->id, SMW_GARDISSUE_MISSCONFIRM_WSCACHE_ENTRIES,
				Title::newFromText($ws->getName()), 0);
			} else {
				$affectedArticles = array_merge($affectedArticles, $this->updateWSResults($ws));
			}
			$this->worked(1);
		}
		return $affectedArticles;
	}

	/**
	 * This method updates cache entries that are used in properties
	 * that are outdated for a single webservice
	 *
	 */
	private function updateWSResults($ws){
		$log = SGAGardeningIssuesAccess::getGardeningIssuesAccess();
		echo("updating " .$ws->getName() ."\n");
		$parameterSets = WSStorage::getDatabase()->getWSUsages($ws->getArticleID());

		$updatedEntries = 0;
		$affectedArticles = array();
		foreach($parameterSets as $parameterSet){
			echo("\t updating paramater set " .$parameterSet["paramSetId"] . "\n");

			$cacheResult = WSStorage::getDatabase()->getResultFromCache(
			$ws->getArticleID(), $parameterSet["paramSetId"]);

			$refresh = false;
			if(count($cacheResult) < 1){
				$refresh = true;
			}
			if(!$refresh){
				if($ws->getQueryPolicy() > 0){
					if(wfTime() - wfTimestamp(TS_UNIX, $cacheResult["lastUpdate"])
					> ($ws->getQueryPolicy()*60)){
						$refresh = true;
					}
				}
			}

			if($refresh){
				echo("\t\t update necessary\n");
				if($updatedEntries > 0){
					sleep($ws->getUpdateDelay());
					echo ("\t\t sleeping " .$ws->getUpdateDelay()."\n");
				}
				$parameters = WSStorage::getDatabase()->getParameters($parameterSet["paramSetId"]);
				$parameters = $ws->initializeCallParameters($parameters);

				$response = $ws->getWSClient()->call($ws->getMethod(), $parameters);

				$goon = true;
					
				if(is_string($response)){
					$log->addGardeningIssueAboutValue(
					$this->id, SMW_GARDISSUE_ERROR_WSCACHE_ENTRIES,
					Title::newFromText($ws->getName()), 0);
					$goon = false;
				}
					
				if($goon) {
					WSStorage::getDatabase()->storeCacheEntry(
					$ws->getArticleID(),
					$parameterSet["paramSetId"],
					serialize($response),
					wfTimeStamp(TS_MW, wfTime()),
					wfTimeStamp(TS_MW, wfTime()));
					echo("\t\t update was successfully\n");

					//get articles which have to be refreshed
				}
			}

			$tempAffectedArticles = WSStorage::getDatabase()
			->getUsedWSParameterSetPairs($ws->getArticleID(), $parameterSet["paramSetId"]);
				
			if($ws->getQueryPolicy() > 0){
				if($refresh || count($tempAffectedArticles) > 1){
					$affectedArticles = array_merge($affectedArticles, $tempAffectedArticles);
				}
				$updatedEntries += 1;
			}

		}

		return $affectedArticles;
	}
}

$ws = new WSUpdateBot();
define('SMW_WSUPDATE_BOT_BASE', 2600);
define('SMW_GARDISSUE_UPDATED_WSCACHE_ENTRIES', SMW_WSUPDATE_BOT_BASE * 100 + 1);
define('SMW_GARDISSUE_ERROR_WSCACHE_ENTRIES', SMW_WSUPDATE_BOT_BASE * 100 + 2);
define('SMW_GARDISSUE_MISSCONFIRM_WSCACHE_ENTRIES', SMW_WSUPDATE_BOT_BASE * 100 + 3);


class WSUpdateBotIssue extends GardeningIssue {

	public function __construct($bot_id, $gi_type, $t1_ns, $t1, $t2_ns, $t2, $value, $isModified) {
		parent::__construct($bot_id, $gi_type, $t1_ns, $t1, $t2_ns, $t2, $value, $isModified);
	}

	protected function getTextualRepresenation(& $skin, $text1, $text2, $local = false) {
		switch($this->gi_type) {
			case SMW_GARDISSUE_UPDATED_WSCACHE_ENTRIES:
				return wfMsg('smw_ws_updatebot_log');
			case SMW_GARDISSUE_MISSCONFIRM_WSCACHE_ENTRIES:
				return wfMsg('smw_ws_updatebot_confirmation');
			case SMW_GARDISSUE_ERROR_WSCACHE_ENTRIES:
				return wfMsg('smw_ws_updatebot_callerror');
			default: return NULL;
		}
	}
}

class WSUpdateBotFilter extends GardeningIssueFilter {

	public function __construct() {
		parent::__construct(SMW_WSCACHE_BOT_BASE);
		$this->gi_issue_classes = array(wfMsg('smw_gardissue_class_all'));
	}

	public function getUserFilterControls($specialAttPage, $request) {
		return '';
	}

	public function linkUserParameters(& $wgRequest) {
		return array();
	}

	public function getData($options, $request) {
		return parent::getData($options, $request);
	}

	private function getGardeningIssueContainerForTitle($options, $request, $title) {
		$gi_class = $request->getVal('class') == 0 ? NULL : $request->getVal('class') + $this->base - 1;

		$gi_store = SGAGardeningIssuesAccess::getGardeningIssuesAccess();

		$gic = array();
		$gis = $gi_store->getGardeningIssues('smw_wscachebot', NULL, $gi_class, $title, SMW_GARDENINGLOG_SORTFORTITLE, NULL);
		$gic[] = new GardeningIssueContainer($title, $gis);

		return $gic;
	}
}
