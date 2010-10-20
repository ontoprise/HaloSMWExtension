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
 */


/**
 * @file
 * @ingroup DIWebServices
 * 
 * @author Ingo Steinbauer
 */

if ( !defined( 'MEDIAWIKI' ) ) die;
define('SMW_WS_SYSOP' , 'sysop');

global $IP;
require_once( $IP . "/includes/SpecialPage.php" );

global $smwgDIIP;
require_once("$smwgDIIP/specials/WebServices/SMW_WSUpdateBot.php");

/**
 * This class represents the special page webservice repository
 *
 * @author Ingo Steinbauer
 *
 */
class SMWWebServiceRepositorySpecial extends SpecialPage {

	public function __construct() {
		parent::__construct('DataImportRepository');
	}

	/**
	 * this methods constructs the special page webservice repository
	 *
	 */
	public function execute($par) {
		global $wgRequest, $wgOut;

		$wgOut->setPageTitle("Data Import Repository");
		
		$webTestDebug = $wgRequest->getVal( 'webTestDebug' );
		if ( !is_null( $webTestDebug ) ) {
			$webTestDebug = true;
		}

		global $wgCookiePrefix;
		
		global $wgArticlePath;
		$gardeningURL = Title::makeTitleSafe(NS_SPECIAL, "Gardening")->getFullURL();
			
		$allowed = false;
		global $wgUser;
		$user = $wgUser;
		if($user != null){
			$groupsOfUser = $user->getGroups();
			foreach($groupsOfUser as $key => $group) {
				if($group == SMW_WS_SYSOP){
					$allowed = true;
				}
			}
		}
			
		$html = "";

		$html .= "<table id=\"menue\" class=\"TabContainer\"><tr>";
		$html .= "<td id=\"web-service-tab\" class=\"ActiveTab\" onclick=\"webServiceRepSpecial.displayWebServiceTab()\">Web Service definitions</td>";
		$html .= "<td></td>";
		$html .= "<td id=\"term-import-tab\" class=\"InactiveTab\" onclick=\"webServiceRepSpecial.displayTermImportTab()\" onmouseover=\"webServiceRepSpecial.highlightTab(event)\">Term Import definitions</td>";
		$html .= "<td></td></tr></table>";

		global $smwgDIIP;
		
		// handle web service repository
		require_once($smwgDIIP . '/specials/WebServices/SMW_WSStorage.php');
		
		$webServices = WSStorage::getDatabase()->getWebServices();
		ksort($webServices);

		$html .= "<span id=\"web-service-tab-content\">";
		$html .= "<h2><span class=\"mw-headline\">".wfMsg('smw_wwsr_intro')."</span></h2>";

		$html .= "<p>".wfMsg('smw_wwsr_rep_intro')."</p>";
		
		$html .= '<p><a href="'.Title::makeTitleSafe(NS_SPECIAL, "DefineWebService")->getFullURL().'">'.wfMsg('smw_wwsr_rep_create_link').'</a></p>';
		
		if($allowed){
			$html .= "<table id=\"webservicetable\" class=\"smwtable\"><tr><th>".wfMsg('smw_wwsr_name')."</th><th>".wfMsg('smw_wwsr_lastupdate')."</th><th style=\"text-align: center\">".wfMsg('smw_wwsr_update_manual')."</th><th style=\"text-align: center\">".wfMsg('smw_wwsr_rep_edit')."</th><th style=\"text-align: center\">".wfMsg('smw_wwsr_delete')."</th><th style=\"text-align: center\">".wfMsg('smw_wwsr_confirm')."</th></tr>";
		} else {
			$html .= "<table id=\"webservicetable\" class=\"smwtable\"><tr><th>".wfMsg('smw_wwsr_name')."</th><th>".wfMsg('smw_wwsr_lastupdate')."</th><th style=\"text-align: center\">".wfMsg('smw_wwsr_rep_edit')."</th></tr>";
		}
		foreach($webServices as $ws){
			$title = Title::newFromID($ws->getArticleID());
			if(!is_null($title)){
				$wsUrl = $title->getFullURL();
				$wsName = substr($ws->getName(), 11, strlen($ws->getName()));
				$html .= "<tr id=\"ws-row-".$ws->getArticleID()."\"><td><a href=\"".$wsUrl."\">".$wsName."</a></td>";

				$cacheResults = WSStorage::getDatabase()->getResultsFromCache($ws->getArticleID());
				$oldestUpdate = "";
				if(count($cacheResults) >0){
					$oldestUpdate = $cacheResults[0]["lastUpdate"];
					if(strlen($oldestUpdate) > 0){
						$oldestUpdate = wfTimestamp(TS_DB, $oldestUpdate);
					}
				}
			
				$latestUpdate = "";
				if(sizeof($cacheResults) > 1){
					$latestUpdate = $cacheResults[(sizeof($cacheResults)-1)]["lastUpdate"];
					if(strlen($latestUpdate) > 0){
						$latestUpdate = wfTimestamp(TS_DB, $cacheResults[(sizeof($cacheResults)-1)]["lastUpdate"]);
						if(strlen($oldestUpdate) > 0){
							$latestUpdate = " - ".$latestUpdate;
						}
					}
				}
			

				$html .= "<td>".$oldestUpdate.$latestUpdate."</td>";
	
				if($allowed){
					$wsUpdateBot = new WSUpdateBot();
					$html .= "<td style=\"text-align: center\"><button id=\"update".$ws->getArticleID()."\" type=\"button\" name=\"update\" onclick=\"webServiceRepSpecial.updateCache('".$wsUpdateBot->getBotID()."', 'WS_WSID=".$ws->getArticleID()."')\" alt=\"".wfMsg('smw_wwsr_update')."\" title=\"".wfMsg('smw_wwsr_update_tooltip')."\">".wfMsg('smw_wwsr_update')."</button>";
					$html .= "<div id=\"updating".$ws->getArticleID()."\" style=\"display: none; text-align: center\"><a href=\"".$gardeningURL."\">".wfMsg('smw_wwsr_updating')."</a></div></td>";
				}
				global $wgArticlePath;
				if(strpos($wgArticlePath, "?") > 0){
					$url = Title::makeTitleSafe(NS_SPECIAL, "DefineWebService")->getFullURL()."&wwsdId=".$ws->getArticleID();
				} else {
					$url = Title::makeTitleSafe(NS_SPECIAL, "DefineWebService")->getFullURL()."?wwsdId=".$ws->getArticleID();
				}
				$html .= "<td style=\"text-align: center\"><button id=\"edit".$ws->getArticleID()."\" type=\"button\" name=\"edit\" onclick=\"window.location.href = '".$url."';\" alt=\"".wfMsg('smw_wwsr_rep_edit')."\" title=\"".wfMsg('smw_wwsr_rep_edit_tooltip')."\">".wfMsg('smw_wwsr_rep_edit')."</button>";
				
				if($allowed){
					$html .= "<td style=\"text-align: center\">  <button type=\"button\"  onclick=\"webServiceRepSpecial.deleteWWSD(".$ws->getArticleID().")\" alt=\"".wfMsg('smw_wwsr_delete')."\" title=\"".wfMsg('smw_wwsr_delete_tooltip')."\">".wfMsg('smw_wwsr_delete')."</button></td>";
				}
				
				if($allowed){
					if($ws->getConfirmationStatus() != "true"){
						$html .= "<td style=\"text-align: center\" id=\"confirmText".$ws->getArticleID()."\">  <button type=\"button\" id=\"confirmButton".$ws->getArticleID()."\" onclick=\"webServiceRepSpecial.confirmWWSD(".$ws->getArticleID().")\" alt=\"".wfMsg('smw_wwsr_confirm')."\" title=\"".wfMsg('smw_wwsr_confirm_tooltip')."\">".wfMsg('smw_wwsr_confirm')."</button></td></tr>";
					} else {
						$html .= "<td style=\"text-align: center\" alt=\"".wfMsg('smw_wwsr_confirm')."\" title=\"".wfMsg('smw_wwsr_confirm_tooltip')."\">".wfMsg('smw_wwsr_confirmed')."</td></tr>";
					}
				} else {
					$html .= "</tr>";
				}
			}
		}

		$html .= "</table>";
		$html .= "</span>";
		
		
		
		//Term Import definition tab
		$html .= "<span id=\"term-import-tab-content\" style=\"display: none\">";
		
		$html .= "<h2><span class=\"mw-headline\">".wfMsg('smw_tir_intro')."</span></h2>";
		$html .= "<p>".wfMsg('smw_tir_rep_intro')."</p>";
		
		$html .= '<p><a href="'.Title::makeTitleSafe(NS_SPECIAL, "TermImport")->getFullURL().'">'.wfMsg('smw_tir_rep_create_link').'</a></p>';
		
		if($allowed){
			$html .= "<table id=\"termimporttable\" width=\"100%\" class=\"smwtable\"><tr><th>".wfMsg('smw_wwsr_name')."</th><th>".wfMsg('smw_wwsr_lastupdate')."</th><th style=\"text-align: center\">".wfMsg('smw_wwsr_update_manual')."</th><th style=\"text-align: center\">".wfMsg('smw_wwsr_rep_edit')."</th><th style=\"text-align: center\">".wfMsg('smw_wwsr_delete')."</th></tr>";
		} else {
			$html .= "<table id=\"termimporttable\" width=\"100%\" class=\"smwtable\"><tr><th>".wfMsg('smw_wwsr_name')."</th><th>".wfMsg('smw_wwsr_lastupdate')."</th></tr>";
		}
		
		$log = SGAGardeningIssuesAccess::getGardeningIssuesAccess();
		SMWQueryProcessor::processFunctionParams(array("[[Category:TermImport]]")
			,$querystring,$params,$printouts);
		$queryResult = explode("|",
		SMWQueryProcessor::getResultFromQueryString($querystring,$params,
			$printouts, SMW_OUTPUT_WIKI));

		unset($queryResult[0]);
		
		foreach($queryResult as $tiArticleName){
			$tiArticleName = substr($tiArticleName, 0, strpos($tiArticleName, "]]"));
			
			
			$html .= "<tr id=\"ti-row-".$tiArticleName."\">";
			$tiUrl = Title::newFromText("TermImport:".$tiArticleName)->getFullURL();
			$html .= "<td><a href=\"".$tiUrl."\">".$tiArticleName."</a></td>";
			
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
			$html .= "<td>".$queryResult."</td>";
			
			if($allowed){
				$tiUpdateBot = new TermImportUpdateBot();
				$html .= "<td style=\"text-align: center\"><button id=\"update-ti-".$tiArticleName."\" 
					type=\"button\" name=\"update-ti\" 
					onclick=\"webServiceRepSpecial.updateTermImport('".$tiArticleName."')\" alt=\"".wfMsg('smw_wwsr_update')."\" title=\"".wfMsg('smw_wwsr_update_tooltip_ti')."\">".wfMsg('smw_wwsr_update')."</button>";
				$html .= "<div id=\"updating-ti-".$tiArticleName."\" style=\"display: none; text-align: center\"><a href=\"".$gardeningURL."\">".wfMsg('smw_wwsr_updating')."</a></div></td>";
			
				global $wgArticlePath;
				if(strpos($wgArticlePath, "?") > 0){
					$url = Title::makeTitleSafe(NS_SPECIAL, "TermImport")->getFullURL()."&tiname=".$tiArticleName;
				} else {
					$url = Title::makeTitleSafe(NS_SPECIAL, "TermImport")->getFullURL()."?tiname=".$tiArticleName;
				}
				$html .= "<td style=\"text-align: center\"><button id=\"edit".$tiArticleName."\" type=\"button\" name=\"edit\" onclick=\"window.location.href = '".$url."';\" alt=\"".wfMsg('smw_wwsr_rep_edit')."\" title=\"".wfMsg('smw_wwsr_rep_edit_tooltip_ti')."\">".wfMsg('smw_wwsr_rep_edit')."</button></td>";
				
				$html .= "<td style=\"text-align: center\"><button type=\"button\" name=\"delete\" onclick=\"webServiceRepSpecial.deleteTermImport('".$tiArticleName."')\" alt=\"".wfMsg('smw_wwsr_delete')."\" title=\"".wfMsg('smw_wwsr_rep_delete_tooltip_ti')."\">".wfMsg('smw_wwsr_delete')."</button></td>";
			}
			
			$html .= "</tr>";
		}
		
		$html .= "</table>";
		$html .= "</span>";
		
		$wgOut->addHTML($html);
	}
}
