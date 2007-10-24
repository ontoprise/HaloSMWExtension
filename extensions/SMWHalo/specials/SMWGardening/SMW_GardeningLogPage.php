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
			$className = get_class($registeredBots[$bot_id]).'Filter';
			$filter = new $className();
			return $html.$filter->getFilterControls($specialAttPage, $wgRequest);
		}
		
 		
	}
	
	function linkParameters() {
		global $wgRequest;
		$bot_id = $wgRequest->getVal("bot") == NULL ? '' : $wgRequest->getVal("bot");
		$type = $wgRequest->getVal("type") == NULL ? '' : $wgRequest->getVal("type");
		return array('bot' => $bot_id, 'type' => $type);
	}
	
	function sortDescending() {
		return false;
	}

	function formatResult( $skin, $result ) {
		if ($result instanceof GardeningIssue) {
			$text = $result->getTextualRepresenation($skin);
			if ($text != NULL) {
				return $text;
			} else {
				return "unknown issue!";	
			} 
		}
	}

	function getResults($options) {
		global $wgRequest, $registeredBots;
		$bot_id = $wgRequest->getVal("bot");
		if ($bot_id == NULL) {
			return array();
		} else {
			$className = get_class($registeredBots[$bot_id]).'Filter';
			$filter = new $className();
			return $filter->getData($options, $wgRequest);
		}
	}
}
?>
