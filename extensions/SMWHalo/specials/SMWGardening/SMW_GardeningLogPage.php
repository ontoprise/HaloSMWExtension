<?php
/*
 * Created on 19.10.2007
 *
 * Author: kai
 */
 if (!defined('MEDIAWIKI')) die();

global $smwgIP;
include_once( "$smwgIP/specials/QueryPages/SMW_QueryPage.php" );

function smwfDoSpecialLogPage() {
	wfProfileIn('smwfDoSpecialLogPage (SMW Halo)');
	list( $limit, $offset ) = wfCheckLimits();
	$rep = new SMWGardeningLogPage();
	$result = $rep->doQuery( $offset, $limit );
	wfProfileOut('smwfDoSpecialLogPage (SMW Halo)');
	return $result;
}

class SMWGardeningLogPage extends SMWQueryPage {
	
	private $filter;
	private $showAll = false;
	
	function __construct() {
		global $wgRequest, $registeredBots;
		$bot_id = $wgRequest->getVal("bot");
		if ($bot_id == NULL) {
			$this->filter = new ConsistencyBotFilter();
		} else {
			$className = get_class($registeredBots[$bot_id]).'Filter';
			$this->filter = new $className();
		}
		$this->showAll = $wgRequest->getVal("pageTitle") != NULL;
	}
	function getName() {
		return "GardeningLog";
	}

	function isExpensive() {
		return false;
	}

	function isSyndicated() { return false; }

	function getPageHeader() {
		global $wgRequest;
		$html = '<p>' . wfMsg('smw_gardeninglogs_docu') . "</p><br />\n";
		$specialAttPage = Title::newFromText(wfMsg('gardeninglog'), NS_SPECIAL);
		return $html.$this->filter->getFilterControls($specialAttPage, $wgRequest);
	}
	
	function doQuery( $offset, $limit, $shownavigation=true ) {
		global $wgRequest, $wgOut;
		if ($wgRequest->getVal('limit') == NULL) $limit = 20;
		parent::doQuery($offset, $limit, $shownavigation);
		$wgOut->addHTML("<button type=\"button\" id=\"showall\" onclick=\"gardeningLogPage.toggleAll()\">Expand All</button>");
	}
	
	function linkParameters() {
		global $wgRequest;
		$bot_id = $wgRequest->getVal("bot") == NULL ? '' : $wgRequest->getVal("bot");
		$gi_class = $wgRequest->getVal("class") == NULL ? '' : $wgRequest->getVal("class");
		$params = array('bot' => $bot_id, 'class' => $gi_class);
		return array_merge($params, $this->filter->linkUserParameters($wgRequest));
	}
	
	function sortDescending() {
		return false;
	}

	function formatResult( $skin, $result ) {
		global $wgServer, $wgScriptPath;
	    if ($result instanceof GardeningIssueContainer){
			
			$bound = $result->getBound();
			$gis = $result->getGardeningIssues();
			if (is_array($bound)) {
				$escapedDBkey = preg_replace("/'/", "&quot;", htmlspecialchars($bound[0]->getPrefixedDBkey())).preg_replace("/'/", "&quot;", htmlspecialchars($bound[1]->getPrefixedDBkey()));
				$text = $skin->makeLinkObj($bound[0]).' <-> '.$skin->makeLinkObj($bound[1]).': <img class="clickable" src="'.$wgServer.$wgScriptPath.'/extensions/SMWHalo/skins/info.gif" onclick="gardeningLogPage.toggle(\''.$escapedDBkey.'\')"/>' .
					'<div class="gardeningLogPageBox" id="'.$escapedDBkey.'" style="display:'.($this->showAll ? "block" : "none").';">';
			} else {
				$escapedDBkey = preg_replace("/'/", "&quot;", htmlspecialchars($bound->getPrefixedDBkey()));
				$text = $skin->makeLinkObj($bound).': <img class="clickable" src="'.$wgServer.$wgScriptPath.'/extensions/SMWHalo/skins/info.gif" onclick="gardeningLogPage.toggle(\''.$escapedDBkey.'\')"/>' .
					'<div class="gardeningLogPageBox" id="'.$escapedDBkey.'" style="display:'.($this->showAll ? "block" : "none").';">';
			}
						
			foreach($gis as $gi) {
				$text .= $gi->getRepresentation($skin).'<br>';
			}
			return $text.'</div>';
			
		} else {
			return 'unknown data of type: '.get_class($result);
		}
	}

	function getResults($options) {
		global $wgRequest;
		return $this->filter->getData($options, $wgRequest);
	}
}
?>
