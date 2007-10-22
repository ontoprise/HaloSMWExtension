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
	$rep = new SMWLogPage();
	$result = $rep->doQuery( $offset, $limit );
	wfProfileOut('smwfDoSpecialLogPage (SMW Halo)');
	return $result;
}

class SMWLogPage extends SMWQueryPage {

	function getName() {
		return "SMWLogPage";
	}

	function isExpensive() {
		return false;
	}

	function isSyndicated() { return false; }

	function getPageHeader() {
		$html = '<p>' . wfMsg('smw_gardeninglogs_docu') . "</p><br />\n";
		$specialAttPage = Title::newFromText("GardeningLogs", NS_SPECIAL);
		global $wgRequest;
		$bot_id = $wgRequest->getVal("bot");
	
		
 		return $html;
	}
	
	function linkParameters() {
		global $wgRequest;
		$bot_id = $wgRequest->getVal("bot") == NULL ? '' : $wgRequest->getVal("bot");
		return array('bot' => $bot_id);
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
		global $wgRequest;
		$bot_id = $wgRequest->getVal("bot");
		$g_issues = SMWGardening::getGardeningIssuesAccess()->getGardeningIssues($bot_id);
		return $g_issues;
	}
}
?>
