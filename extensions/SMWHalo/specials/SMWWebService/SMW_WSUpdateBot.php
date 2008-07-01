<?
/*  Copyright 2008, ontoprise GmbH
 *  This file is part of the halo-Extension.
 *
 *   The halo-Extension is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The halo-Extension is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  Author: Ingo Steinbauer
 */

global $smwgHaloIP;
require_once("$smwgHaloIP/specials/SMWGardening/SMW_GardeningBot.php");


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
		//todo: create message
		return wfMsg('smw_ws_updatebothelp');
	}

	public function getLabel() {
		return wfMsg($this->id);
	}

	public function allowedForUserGroups() {
		return array(SMW_GARD_GARDENERS, SMW_GARD_SYSOPS, SMW_GARD_ALL_USERS);
	}

	public function createParameters() {
		$params = array();
		return $params;
	}

	public function run($paramArray, $isAsync, $delay) {
		//echo("update started");
		$this->updateAllWSProperties();
		return '';
	}

	/**
	 * todo: write help message
	 *
	 *
	 */
	private function updateAllWSProperties(){
		$webServices = WSStorage::getDatabase()->getWebservices();
		$this->setNumberOfTasks(sizeof($webServices));
		// todo: confirmation status beruecksichtigen
		foreach($webServices as $ws){
			//echo($ws->getName(). " started\n");
			$this->updateWSProperty($ws);
		}
	}

	/**
	 * todo:write
	 *
	 */
	private function updateWSProperty($ws){
		$log = SMWGardeningIssuesAccess::getGardeningIssuesAccess();
		
		$props = WSStorage::getDatabase()->getWSPropertyUsages($ws->getArticleID());
		
		if(sizeof($props) > 0){
			echo($ws->getName()."numProps: ".sizeof($props)." \n");
			$this->addSubTask(sizeof($props)+1);
			$updatedEntries = 0;
			foreach ($props as $prop){
				echo($props["propertyId"]." -".$prop["paramSetId"]."\n");
				$cacheResult = WSStorage::getDatabase()->getResultFromCache(
					$ws->getArticleID(), $prop["paramSetId"]);

				if(!$cacheResult || !(($ws->getQueryPolicy() == 0) ||
						(wfTime() - wfTimestamp(TS_UNIX, $cacheResult["lastUpdate"])
					< ($ws->getQueryPolicy()*60)))){
					$parameters = WSStorage::getDatabase()->getParameters($prop["paramSetId"]);
					if($updatedEntries > 0){
						sleep($ws->getUpdateDelay());
					}
					echo("wake up");
					$response = $ws->getWSClient()->call($ws->getMethod(), parameters);
					if(is_string($response)){
						if(substr($response, 0, 11) == "_ws-error: "){
							//todo: error handling: log entry
						} else {
							//todo: nur speichern, wenn kein fehler
							WSStorage::getDatabase()->storeCacheEntry(
								$ws->getArticleID(),
								$prop["paramSetId"],
								serialize($response),
								wfTimeStamp(TS_MW, wfTime()),
								wfTimeStamp(TS_MW, wfTime()));

							$updatedEntries += 1;
						}
					}
				}
				$this->worked(1);
			}
			if($updatedEntries > 0){
				$log->addGardeningIssueAboutValue(
				$this->id, SMW_GARDISSUE_UPDATED_WSCACHE_ENTRIES,
				Title::newFromText($ws->getName()), $updatedEntries);
			}
		} else {
			$this->addSubTask(1);
			$this->worked(1);
		}
	}
}

new WSUpdateBot();
define('SMW_WSUPDATE_BOT_BASE', 2600);
define('SMW_GARDISSUE_UPDATED_WSCACHE_ENTRIES', SMW_WSCACHE_BOT_BASE * 100 + 1);


class WSUpdateBotIssue extends GardeningIssue {

	public function __construct($bot_id, $gi_type, $t1_ns, $t1, $t2_ns, $t2, $value, $isModified) {
		parent::__construct($bot_id, $gi_type, $t1_ns, $t1, $t2_ns, $t2, $value, $isModified);
	}

	protected function getTextualRepresenation(& $skin, $text1, $text2, $local = false) {
		switch($this->gi_type) {
			case SMW_GARDISSUE_UPDATED_WSCACHE_ENTRIES:
				return wfMsg('smw_ws_update_log');
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

		$gi_store = SMWGardeningIssuesAccess::getGardeningIssuesAccess();

		$gic = array();
		$gis = $gi_store->getGardeningIssues('smw_wscachebot', NULL, $gi_class, $title, SMW_GARDENINGLOG_SORTFORTITLE, NULL);
		$gic[] = new GardeningIssueContainer($title, $gis);

		return $gic;
	}
}

?>
