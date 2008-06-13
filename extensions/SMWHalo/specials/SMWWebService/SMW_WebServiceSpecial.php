<?php

global $IP;
require_once( $IP . "/includes/SpecialPage.php" );

class SMWWebServiceSpecial extends SpecialPage {

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
		
		$html .= "<table width=\"100%\" class=\"smwtable\"><tr><th>Name</th><th>Last Update</th><th>Update</th><th>Confirm</th></tr>";
		foreach($webServices as $ws){
			$wsUrl = Title::newFromID($ws->getArticleID())->getInternalURL();
			$wsName = substr($ws->getName(), 11, strlen($ws->getName())); 
			$html .= "<tr><td><a href=\"".$wsUrl."\">".$wsName."</a></td>";
			$cacheResult = WSStorage::getDatabase()->getResultFromCache($this->mArticleID, $parameterSetId);
			$lastUpdate = wfTimestamp(TS_MW, $cacheResult["lastUpdate"]);
			
			$html .= "<td>".$lastUpdate."</td>";
			
			$html .= "<td><button type=\"button\" name=\"update\" onclick=\"webServiceSpecial.updateCache(".$ws->getArticleID().")\">Update</button></td>";
			$html .= "<td><button type=\"button\" id=\"confirm\" onclick=\"webServiceSpecial.confirmWWSD(".$ws->getArticleID().")\">Confirm</button></td></tr>";
		}
		$html .= "</table>";
		
		$wgOut->addHTML($html);
	}
}

?>