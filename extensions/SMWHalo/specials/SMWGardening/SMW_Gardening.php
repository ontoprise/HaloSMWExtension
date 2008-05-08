<?php
/*
 * Created on 12.03.2007
 *
 * Author: kai
 */
 if (!defined('MEDIAWIKI')) die();



global $IP;
require_once( "$IP/includes/SpecialPage.php" );
require_once("SMW_GardeningBot.php");
require_once("ConsistencyBot/SMW_ConsistencyBot.php");
require_once("Bots/SMW_SimilarityBot.php");
require_once("Bots/SMW_TemplateMaterializerBot.php");
require_once("Bots/SMW_UndefinedEntitiesBot.php");
require_once("Bots/SMW_MissingAnnotationsBot.php");
require_once("Bots/SMW_AnomaliesBot.php");
require_once("Bots/SMW_ImportOntologyBot.php");
require_once("Bots/SMW_ExportOntologyBot.php");

global $smwgHaloIP;
require_once("$smwgHaloIP/specials/SMWTermImport/SMW_TermImportBot.php");



/*
 * Called when gardening request in sent in wiki
 */

class SMWGardening extends SpecialPage {
	
	
	public function __construct() {
		parent::__construct('Gardening');
	}
	
	public function execute() {
		global $wgRequest, $wgOut;
		$wgOut->setPageTitle(wfMsg('gardening'));
		$html = "<div style=\"margin-bottom:10px;\">".wfMsg('smw_gard_welcome')."</div>";
		$html .= "<div id=\"gardening-container\">" .
					"<div id=\"gardening-tools\">" . SMWGardening::getRegisteredBots() .
					"</div>" .
					"<div id=\"gardening-tooldetails\"><div id=\"gardening-tooldetails-content\">".wfMsg('smw_gard_choose_bot')."</div></div>
		
					<div id=\"gardening-runningbots-head\">&nbsp;Current / Recent bot activities:</div>
					<div id=\"gardening-runningbots\">".SMWGardening::getGardeningLogTable()."</div>
				 </div>";
		$wgOut->addHTML($html);
	}
	
	static function getGardeningLogTable() {
		$html = "<table width=\"100%\" class=\"smwtable\"><tr><th>User</th><th>Action</th><th>Start-Time</th><th>End-Time</th><th>Log</th><th>Progress</th><th>State</th></tr>";
		$gardeningLog = SMWGardeningLog::getGardeningLogAccess()->getGardeningLogAsTable();
		if ($gardeningLog == null || !is_array($gardeningLog)) {
			return $gardeningLog;
		}
		foreach ($gardeningLog as $row) {
			$html .= "<tr>";
			for ($i=0; $i < count($row)-1;$i++) {
				
					
				if ($i == 4 && $row[3] != null) {
					$html .= "<td><a href=\"".$row[$i]."\">Log</a></td>";
				} else if ($i == 1) {
					$html .= "<td>".wfMsg($row[$i])."</td>";
				 } else if ($i == 5) {
				 	$html .= "<td>".(number_format(($row[$i]+0)*100))."%</td>";
				 }else { 
					$html .= "<td>".$row[$i]."</td>";
				}
			}
			$runningBot = $row[3] == null;
			$html .= ($runningBot ? "<td class=\"runningBots\">running</td>" : "<td class=\"finishedBots\">finished</td>");
			$html .= "<td><button type=\"button\" name=\"abort\" ".($runningBot ? "" : "disabled")." onclick=\"gardeningPage.cancel(event, ".$row[6].")\">".wfMsg('smw_gard_abortbot')."</button></td>";
			$html .= "</tr>";
		}
		$html .= "</table>";
		return $html;
	}
	
	static function getRegisteredBots() {
		global $registeredBots;
		 $htmlResult = "";
		 $first = true;
		 foreach($registeredBots as $botID => $bot) {
		 	if (!GardeningBot::isUserAllowed($bot->allowedForUserGroups())) {
		 		continue; // do not add this bot, because the user must not access it.
		 	}
		 	$htmlResult .= "<div class=\"entry\" onMouseOver=\"this.className='entry-over';\"" .
		 				   " onMouseOut=\"gardeningPage.showRightClass(event, this, '$botID')\" onClick=\"gardeningPage.showParams(event, this, '$botID')\" id=\"$botID\">" .
		 				   "<a>" .$bot->getLabel()."</a>" .
		 				   "</div>";
	
		 }
		 if ($htmlResult == '') {
		 	$htmlResult .= wfMsg('smw_gard_notools');
		 }
		 return $htmlResult;
	}
	
	static function getParameterFormularForBot($botID) {
		global $registeredBots;
 		$bot = $registeredBots[$botID];
 		if ($bot == null) {
 			return "unknown bot"; //TODO: externalize by wfMsg(...)
 		}
 		$htmlResult = "<div>".$bot->getHelpText()."</div>";
 		$htmlResult .= "<form id=\"gardeningParamForm\"";
 		$parameters = $bot->getParameters();
 		foreach($parameters as $param) {
 			$htmlResult .= $param->serializeAsHTML()."<br>";
 		}
 		$htmlResult .= "</form><br>";
 		$htmlResult .= "<button id=\"runBotButton\" type=\"button\" name=\"run\" onclick=\"gardeningPage.run(event)\">Run Bot</button>";
 		return $htmlResult;
	}
	
	
	
	
}
?>
