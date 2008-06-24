<?php

global $IP;
require_once( $IP . "/includes/SpecialPage.php" );

//todo: describe
class SMWWebServiceRepositorySpecial extends SpecialPage {

	//todo: make only accessible for admins
	//todo: describe
	public function __construct() {
		parent::__construct('WebServicerepository');
	}
	
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
			$oldestUpdate = wfTimestamp(TS_DB, $cacheResults[0]["lastUpdate"]);			
			$latestUpdate = wfTimestamp(TS_DB, $cacheResults[(sizeof($cacheResults)-1)]["lastUpdate"]);
			$html .= "<td>".$oldestUpdate." - ".$latestUpdate."</td>";
			
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