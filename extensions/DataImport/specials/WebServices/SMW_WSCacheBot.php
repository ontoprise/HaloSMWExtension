<?php

/*  Copyright 2008, ontoprise GmbH
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
*/

/**
 * @file
 * @ingroup DIWebServices
 * 
  *  @author: Ingo Steinbauer
 */
if ( !defined( 'MEDIAWIKI' ) ) die;
global $sgagIP;
require_once("$sgagIP/includes/SGA_GardeningBot.php");


/**
 * This bot searches for outdated ws-cache-entries and removes
 * them from the database
 *
 */
class WSCacheBot extends GardeningBot {

	/**
	 * Constructor
	 *
	 */
	function __construct() {
		parent::GardeningBot("smw_wscachebot");
	}


	public function getHelpText() {
		return wfMsg('smw_ws_cachebothelp');
	}

	public function getLabel() {
		return wfMsg($this->id);
	}

	//	public function allowedForUserGroups() {
	//		return array(SMW_GARD_GARDENERS, SMW_GARD_SYSOPS, SMW_GARD_ALL_USERS);
	//	}
	//
	public function createParameters() {

		$params = array();
		return $params;
	}

	public function run($paramArray, $isAsync, $delay) {
		$this->cleanCompleteCache();
		
		global $smwgDefaultStore;
		if($smwgDefaultStore == 'SMWTripleStore' || $smwgDefaultStore == 'SMWTripleStoreQuad'){
			define('SMWH_FORCE_TS_UPDATE', 'TRUE');
			smwfGetStore()->initialize(true);
		}
		
		return '';
	}

	public function getImageDirectory() {
		return 'extensions/DataImport/skins/webservices';
	}
	/**
	 * this method calls cleanWSCacheEntries for
	 * each existing webservice
	 *
	 */
	private function cleanCompleteCache(){
		$webServices = WSStorage::getDatabase()->getWebServices();
		$this->setNumberOfTasks(sizeof($webServices));
		foreach($webServices as $ws){
			$this->cleanWSCacheEntries($ws);
		}
	}

	/**
	 * this method checks for each cache entry of
	 * a webservice if it has to be deleted
	 *
	 * @param webservice $ws
	 */
	private function cleanWSCacheEntries($ws){
		$log = SGAGardeningIssuesAccess::getGardeningIssuesAccess();

		if($ws->getSpanOfLife() != "0"){
			$cacheResults = WSStorage::getDatabase()->getResultsFromCache($ws->getArticleID());
			$this->addSubTask(sizeof($cacheResults)+1);
			//echo($ws->getArticleID());
			$deletedCacheEntries = 0;
			foreach($cacheResults as $cacheResult){
				if($ws->doesExpireAfterUpdate() == "false"){
					if($cacheResult["lastAccess"]){
						if(wfTimestamp(TS_UNIX, $cacheResult["lastAccess"])
						< wfTimestamp(TS_UNIX, $cacheResult["lastUpdate"])){
							$compareTS = $cacheResult["lastUpdate"];
						} else {
							$compareTS = $cacheResult["lastAccess"];
						}
					} else {
						$compareTS = $cacheResult["lastUpdate"];
					}
				} else {
					$compareTS = $cacheResult["lastUpdate"];
				}

				//todo: change to days again
				if(wfTime() - wfTimestamp(TS_UNIX, $compareTS)
				> $ws->getSpanOfLife() *24*60*60){
					WSStorage::getDatabase()->removeWSEntryFromCache(
					$ws->getArticleID(), $cacheResult["paramSetId"]);
					$deletedCacheEntries += 1;
				}
				$this->worked(1);
			}
			// echo($ws->getName()."-".$deletedCacheEntries);
			if($deletedCacheEntries > 0){
				$log->addGardeningIssueAboutValue(
				$this->id, SMW_GARDISSUE__REMOVED_WSCACHE_ENTRIES,
				Title::newFromText($ws->getName()), $deletedCacheEntries);
			}
		} else {
			$this->addSubTask(1);
			$this->worked(1);
		}
	}
}

new WSCacheBot();

define('SMW_WSCACHE_BOT_BASE', 2500);
define('SMW_GARDISSUE__REMOVED_WSCACHE_ENTRIES', SMW_WSCACHE_BOT_BASE * 100 + 1);


class WSCacheBotIssue extends GardeningIssue {

	public function __construct($bot_id, $gi_type, $t1_ns, $t1, $t2_ns, $t2, $value, $isModified) {
		parent::__construct($bot_id, $gi_type, $t1_ns, $t1, $t2_ns, $t2, $value, $isModified);
	}

	protected function getTextualRepresenation(&$skin, $text1, $text2, $local = false) {
		switch($this->gi_type) {
			case SMW_GARDISSUE__REMOVED_WSCACHE_ENTRIES:
				return wfMsg('smw_ws_cachbot_log');
					
			default: return NULL;

		}
	}
}

class WSCacheBotFilter extends GardeningIssueFilter {

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
