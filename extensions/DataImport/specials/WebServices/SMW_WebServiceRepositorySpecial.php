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
		parent::__construct('WebServiceRepository');
	}

	/**
	 * this methods constructs the special page webservice repository
	 *
	 */
	public function execute() {
		global $wgRequest, $wgOut;

		$wgOut->setPageTitle("Web Service Repository");

		global $wgCookiePrefix;
			
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


		global $smwgDIIP;
		require_once($smwgDIIP . '/specials/WebServices/SMW_WSStorage.php');

		$webServices = WSStorage::getDatabase()->getWebServices();
		ksort($webServices);

		$html .= "<h2><span class=\"mw-headline\">".wfMsg('smw_wwsr_intro')."</span></h2>";

		$html .= "<p>".wfMsg('smw_wwsr_rep_intro')."</p>";
		if($allowed){
			$html .= "<table width=\"100%\" class=\"smwtable\"><tr><th>".wfMsg('smw_wwsr_name')."</th><th>".wfMsg('smw_wwsr_lastupdate')."</th><th style=\"text-align: center\">".wfMsg('smw_wwsr_update_manual')."</th><th style=\"text-align: center\">".wfMsg('smw_wwsr_rep_edit')."</th><th style=\"text-align: center\">".wfMsg('smw_wwsr_confirm')."</th></tr>";
		} else {
			$html .= "<table width=\"100%\" class=\"smwtable\"><tr><th>".wfMsg('smw_wwsr_name')."</th><th>".wfMsg('smw_wwsr_lastupdate')."</th><th style=\"text-align: center\">".wfMsg('smw_wwsr_rep_edit')."</th></tr>";
		}
		foreach($webServices as $ws){
			$wsUrl = Title::newFromID($ws->getArticleID())->getFullURL();
			$wsName = substr($ws->getName(), 11, strlen($ws->getName()));
			$html .= "<tr><td><a href=\"".$wsUrl."\">".$wsName."</a></td>";

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
				$html .= "<td style=\"text-align: center\"><button id=\"update".$ws->getArticleID()."\" type=\"button\" name=\"update\" onclick=\"webServiceRepSpecial.updateCache('".$wsUpdateBot->getBotID()."', 'WS_WSID=".$ws->getArticleID()."')\">".wfMsg('smw_wwsr_update')."</button>";
				$html .= "<div id=\"updating".$ws->getArticleID()."\" style=\"display: none; text-align: center\">".wfMsg('smw_wwsr_updating')."</div></td>";
			}
			global $wgArticlePath;
			if(strpos($wgArticlePath, "?") > 0){
				$url = Title::makeTitleSafe(NS_SPECIAL, "DefineWebService")->getFullURL()."&wwsdId=".$ws->getArticleID();
			} else {
				$url = Title::makeTitleSafe(NS_SPECIAL, "DefineWebService")->getFullURL()."?wwsdId=".$ws->getArticleID();
			}
			$html .= "<td style=\"text-align: center\"><button id=\"edit".$ws->getArticleID()."\" type=\"button\" name=\"edit\" onclick=\"window.location.href = '".$url."';\">".wfMsg('smw_wwsr_rep_edit')."</button>";

			if($allowed){
				if($ws->getConfirmationStatus() != "true"){
					$html .= "<td style=\"text-align: center\" id=\"confirmText".$ws->getArticleID()."\">  <button type=\"button\" id=\"confirmButton".$ws->getArticleID()."\" onclick=\"webServiceRepSpecial.confirmWWSD(".$ws->getArticleID().")\">".wfMsg('smw_wwsr_confirm')."</button></td></tr>";
				} else {
					$html .= "<td style=\"text-align: center\">".wfMsg('smw_wwsr_confirmed')."</td></tr>";
				}
			} else {
				$html .= "</tr>";
			}
		}

		$html .= "</table>";

		$wgOut->addHTML($html);
	}
}

?>