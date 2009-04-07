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
 *  @author: Ingo Steinbauer
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

	public function allowedForUserGroups() {
		return array(SMW_GARD_GARDENERS, SMW_GARD_SYSOPS, SMW_GARD_ALL_USERS);
	}

	public function createParameters() {
		return array();
	}
	
    public function getImageDirectory() {
        return 'extensions/DataImport/skins/webservices';
    }
    
	public function run($paramArray, $isAsync, $delay) {
		//echo("bot started");
		if($paramArray["WS_WSID"] != null){
			//echo("bot started");
			$log = SGAGardeningIssuesAccess::getGardeningIssuesAccess();
			$this->setNumberOfTasks(1);
			$ws = WebService::newFromID($paramArray["WS_WSID"]);
			$this->updateWSProperty($ws, true);
		} else {
			$this->updateAllWSProperties();
		}
		return '';
	}

	/**
	 * This method updates cache entries that are used in properties
	 * that are outdated for all webservices
	 *
	 */
	private function updateAllWSProperties(){
		$log = SGAGardeningIssuesAccess::getGardeningIssuesAccess();
		$webServices = WSStorage::getDatabase()->getWebservices();
		$this->setNumberOfTasks(sizeof($webServices));
		foreach($webServices as $ws){
			if($ws->getConfirmationStatus() == "once"
			|| $ws->getConfirmationStatus() == "false"){
				$log->addGardeningIssueAboutValue(
				$this->id, SMW_GARDISSUE_MISSCONFIRM_WSCACHE_ENTRIES,
				Title::newFromText($ws->getName()), 0);
			} else {
				$this->updateWSProperty($ws, false);
			}
		}
	}

	/**
	 * This method updates cache entries that are used in properties
	 * that are outdated for a single webservice
	 *
	 */
	private function updateWSProperty($ws, $all){
		$log = SGAGardeningIssuesAccess::getGardeningIssuesAccess();

		$props = WSStorage::getDatabase()->getWSPropertyUsages($ws->getArticleID());

		if(sizeof($props) > 0){
			$this->addSubTask(sizeof($props)+1);
			$updatedEntries = 0;

			foreach ($props as $prop){
				//echo("\n0; ".$ws->getName());
				//echo(" 1; ".$prop["pageId"]);
				//echo(" 2: ".$prop["propertyName"]);

				$cacheResult = WSStorage::getDatabase()->getResultFromCache(
				$ws->getArticleID(), $prop["paramSetId"]);

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

				if($all){
					$refresh = true;
				}

				//updates are necessary
				if($refresh){
					if($updatedEntries > 0){
						sleep($ws->getUpdateDelay());
					}

					$parameters = WSStorage::getDatabase()->getParameters($prop["paramSetId"]);
					$parameters = $ws->initializeCallParameters($parameters);

					$response = $ws->getWSClient()->call($ws->getMethod(), $parameters);

					$goon = true;

					$errorMessages = $ws->getErrorMessages();
					if(is_string($response) || sizeof($errorMessages) > 0){
						$log->addGardeningIssueAboutValue(
						$this->id, SMW_GARDISSUE_ERROR_WSCACHE_ENTRIES,
						Title::newFromText($ws->getName()), 0);
						$goon = false;
					}

					if($goon) {
						WSStorage::getDatabase()->storeCacheEntry(
						$ws->getArticleID(),
						$prop["paramSetId"],
						serialize($response),
						wfTimeStamp(TS_MW, wfTime()),
						wfTimeStamp(TS_MW, wfTime()));

						//update the smw-storage
						$response = $ws->getCallResultParts($response, array($prop["resultSpec"]));
						$response = array_pop(array_pop($response));
						$cacheResult = $ws->getCallResultParts
						(unserialize($cacheResult["result"]), array($prop["resultSpec"]));
						$cacheResult = array_pop(array_pop($cacheResult));
						//echo("cacheresult:::: ".$cacheResult);


						$subject = Title::newFromID($prop["pageId"]);
						$smwData = smwfGetStore()->getSemanticData($subject);

						echo(" 4: ".$prop["propertyName"]);

						$smwProps = $smwData->getProperties();

						$tempPropertyValues = array();
						foreach($smwProps as $smwProp){
							$tempPropertyValues[$smwProp->getText()] =
							$smwData->getPropertyValues($smwProp);
						}

						$smwData->clear();

						$added = false;
						foreach($tempPropertyValues as $key => $values){
							if(count($cacheResult)>0){
								foreach($values as $value){
									$content = $value->getXSDValue();
									//echo(" a; ".$key);
									//echo(" b; ".$prop["propertyName"]);
									//echo(" c; ".$content);
									//echo(" d; ".$response."\n");
									if(strtolower($key) == strtolower($prop["propertyName"])
									&& strtolower($content) == strtolower($cacheResult)){
										$content = $response;
										$added = true;
									}
									$newValue = SMWDataValueFactory::newPropertyValue($key, $content);
									$smwData->addPropertyValue($key, $newValue);
								}
							}
						}
						if(!$added){
							//echo("\n added1: ".$prop["propertyName"]);
							$newValue = SMWDataValueFactory::newPropertyValue($prop["propertyName"], $response);
							//echo("\n added2: ".$newValue);
							$smwData->addPropertyValue($prop["propertyName"], $newValue);
						}

						smwfGetStore()->updateData($smwData, false);
						$updatedEntries += 1;
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

		// update cache results that are not used as a property
		// triggered when the user presses the update button
		// in the special page Web Service Repository
		if($all){
			$articles = WSStorage::getDatabase()->getWSUsages($ws->getArticleID());
			
			$updatedEntries = 0;
			foreach($articles as $article){
				$exists = false;
				foreach($props as $prop){
					if($props["paramSetId"] == $article["paramSetId"]){
						$exists = true;
						break;
					}
				}
				
				if($exists){
					continue;
				}
				
				if($updatedEntries > 0){
					sleep($ws->getUpdateDelay());
				}

				$parameters = WSStorage::getDatabase()->getParameters($article["paramSetId"]);
				$parameters = $ws->initializeCallParameters($parameters);

				$response = $ws->getWSClient()->call($ws->getMethod(), $parameters);

				$goon = true;
					
				$errorMessages = $ws->getErrorMessages();
				if(is_string($response) || sizeof($errorMessages) > 0){
					$log->addGardeningIssueAboutValue(
					$this->id, SMW_GARDISSUE_ERROR_WSCACHE_ENTRIES,
					Title::newFromText($ws->getName()), 0);
					$goon = false;
				}
					
				if($goon) {
					WSStorage::getDatabase()->storeCacheEntry(
					$ws->getArticleID(),
					$article["paramSetId"],
					serialize($response),
					wfTimeStamp(TS_MW, wfTime()),
					wfTimeStamp(TS_MW, wfTime()));
				}
				$updatedEntries += 1;
			}
		}
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

?>