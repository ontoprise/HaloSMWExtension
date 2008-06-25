<?php
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
 */


global $IP;
require_once( $IP . "/includes/SpecialPage.php" );

/**
 * This class represents the special page webservice repository
 *
 * @author Ingo Steinbauer
 *
 */
class SMWWebServiceRepositorySpecial extends SpecialPage {

	//todo: make only accessible for admins
	public function __construct() {
		parent::__construct('WebServicerepository');
	}
	
	/**
	 * this methods constructs the special page webservice repository
	 *
	 */
	public function execute() {
		global $wgRequest, $wgOut;

		$wgOut->setPageTitle("Web Service Repository");

		$html = "";

		global $smwgHaloIP;
		require_once($smwgHaloIP . '/specials/SMWWebService/SMW_WSStorage.php');

		$webServices = WSStorage::getDatabase()->getWebServices();

		$html .= "<h2><span class=\"mw-headline\">Available Wiki Web Service Definitions</span></h2>";

		$html .= "<table width=\"100%\" class=\"smwtable\"><tr><th>Name</th><th>Last Updates</th><th>Update</th><th>Confirm</th></tr>";
		foreach($webServices as $ws){
			$wsUrl = Title::newFromID($ws->getArticleID())->getInternalURL();
			$wsName = substr($ws->getName(), 11, strlen($ws->getName()));
			$html .= "<tr><td><a href=\"".$wsUrl."\">".$wsName."</a></td>";

			$cacheResults = WSStorage::getDatabase()->getResultsFromCache($ws->getArticleID());
			$oldestUpdate = $cacheResults[0]["lastUpdate"];
			if(strlen($oldestUpdate) > 0){
				$oldestUpdate = wfTimestamp(TS_DB, $oldestUpdate);
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

			$html .= "<td><button type=\"button\" name=\"update\" onclick=\"webServiceSpecial.updateCache(".$ws->getArticleID().")\">Update</button></td>";

			if($ws->getConfirmationStatus() != "true"){
				$html .= "<td id=\"confirmText\">  <button type=\"button\" id=\"confirmButton\" onclick=\"webServiceSpecial.confirmWWSD(".$ws->getArticleID().")\">Confirm</button></td></tr>";
			} else {
				$html .= "<td>confirmed</td></tr>";
			}
		}

		$html .= "</table>";

		$wgOut->addHTML($html);
	}
}

?>