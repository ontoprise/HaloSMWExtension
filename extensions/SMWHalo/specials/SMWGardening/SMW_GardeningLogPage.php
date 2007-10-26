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
	
	function __construct() {
		global $wgRequest, $registeredBots;
		$bot_id = $wgRequest->getVal("bot");
		if ($bot_id == NULL) {
			$this->filter = NULL;
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
		$html = '<p>' . wfMsg('smw_gardeninglogs_docu') . "</p><br />\n";
		$specialAttPage = Title::newFromText(wfMsg('gardeninglog'), NS_SPECIAL);
		global $wgRequest, $registeredBots;
		$bot_id = $wgRequest->getVal("bot");
		if ($bot_id == NULL) {
			$html .= "<form action=\"".$specialAttPage->getFullURL()."\">";
			$html .= "<select name=\"bot\">";
					
			foreach($registeredBots as $bot_id => $bot) {
				$html .= "<option value=\"".$bot->getBotID()."\" onclick=\"gardeningLogPage.selectBot('".$bot->getBotID()."')\">".$bot->getLabel()."</option>";
			}
	 		$html .= "</select>";
	 		$html .= "<span id=\"issueClasses\"></span>";
	 		$html .= "<input type=\"submit\" value=\" Go \">";
	 		$html .= "</form>";
	 		return $html;
		} else {
			// filter must be != NULL, because $bot_id != NULL
			return $html.$this->filter->getFilterControls($specialAttPage, $wgRequest);
		}
		
 		
	}
	
	function linkParameters() {
		global $wgRequest;
		$bot_id = $wgRequest->getVal("bot") == NULL ? '' : $wgRequest->getVal("bot");
		$type = $wgRequest->getVal("class") == NULL ? '' : $wgRequest->getVal("class");
		$params = array('bot' => $bot_id, 'class' => $type);
		return array_merge($params, $this->filter->linkUserParameters($wgRequest));
	}
	
	function sortDescending() {
		return false;
	}

	function formatResult( $skin, $result ) {
		if ($result instanceof GardeningIssue) {
			$text = $result->getRepresentation($skin);
			if ($text != NULL) {
				return $text;
			} else {
				return "unknown issue!";	
			} 
		}
	}

	function getResults($options) {
		global $wgRequest;
		return $this->filter != NULL ? $this->filter->getData($options, $wgRequest) : array();
	}
}
?>
