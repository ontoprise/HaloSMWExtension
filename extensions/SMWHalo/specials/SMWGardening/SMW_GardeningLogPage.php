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
	private $gardeningIssues;
	
	function __construct() {
		global $wgRequest, $registeredBots;
		$bot_id = $wgRequest->getVal("bot");
		if ($bot_id == NULL) {
			$this->filter = new ConsistencyBotFilter();
		} else {
			$className = get_class($registeredBots[$bot_id]).'Filter';
			$this->filter = new $className();
		}
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
		if ($result instanceof GardeningIssue) {
			$text = $result->getRepresentation($skin);
			if ($text != NULL) {
				return $text;
			} else {
				return 'unknown issue of type: '.$result->getType();	
			} 
		} else if ($result instanceof Title) {
			$escapedDBkey = preg_replace("/'/", "&quot;", htmlspecialchars($result->getDBkey()));
			$text = $skin->makeLinkObj($result).': <img src="'.$wgServer.$wgScriptPath.'/extensions/SMWHalo/skins/info.gif" onclick="gardeningLogPage.toggle(\''.$escapedDBkey.'\')"/>' .
					'<div class="gardeningLogPageBox" id="'.$escapedDBkey.'" style="display:none;">';
			$gis = $this->gardeningIssues->getGardeningIssues(); 
			foreach($gis[$result->getDBkey()] as $gi) {
				$text .= $gi->getRepresentation($skin).'<br>';
			}
			return $text.'</div>';
		} else if (is_array($result) && count($result) == 2 && 
				$result[0] instanceof Title && $result[1] instanceof Title){
			// $result is tuple of titles ($t1, $t2)
			
			$escapedDBkey = preg_replace("/'/", "&quot;", htmlspecialchars($result[0]->getDBkey())).preg_replace("/'/", "&quot;", htmlspecialchars($result[1]->getDBkey()));
			
			$text = $skin->makeLinkObj($result[0]).' <-> '.$skin->makeLinkObj($result[1]).': <img src="'.$wgServer.$wgScriptPath.'/extensions/SMWHalo/skins/info.gif" onclick="gardeningLogPage.toggle(\''.$escapedDBkey.'\')"/>' .
					'<div class="gardeningLogPageBox" id="'.$escapedDBkey.'" style="display:none;">';
			$gis = $this->gardeningIssues->getGardeningIssues(); 
			foreach($gis[$result[0]->getDBkey().$result[1]->getDBkey()] as $gi) {
				$text .= $gi->getRepresentation($skin).'<br>';
			}
			return $text.'</div>';
			
		} else {
			return 'unknown data of type: '.get_class($result);
		}
	}

	function getResults($options) {
		global $wgRequest;
		$this->gardeningIssues = $this->filter != NULL ? $this->filter->getData($options, $wgRequest) : NULL;
		if ($this->gardeningIssues instanceof GardeningIssueContainer) {
			return $this->gardeningIssues->getTitles();
		} else if (is_array($this->gardeningIssues)) {
			return $this->gardeningIssues;
		}
		return array();
	}
}
?>
