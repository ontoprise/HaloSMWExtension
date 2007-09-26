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



$wgHooks['BeforePageDisplay'][]='smwGAAddHTMLHeader';

global $wgAjaxExportList;
$wgAjaxExportList[] = 'smwfLaunchGardeningBot';
$wgAjaxExportList[] = 'smwfCancelGardeningBot';
$wgAjaxExportList[] = 'smwfGetGardeningLog';
$wgAjaxExportList[] = 'smwfGetBotParameters';
$wgAjaxExportList[] = 'smwfGetRegisteredBots';

// standard functions for creating a new special
function doSMW_Gardening() {
	SMW_Gardening::execute();
}
	
SpecialPage::addPage( new SpecialPage('Gardening','',true,'doSMW_Gardening',false) );


/*
 * Called when gardening request in sent in wiki
 */
function smwfLaunchGardeningBot($botID, $params) {
	
	$taskid = GardeningBot::runBot($botID, $params);
	if (gettype($taskid) == 'integer') { // task id, no error code
		
		if ($taskid >= 0) {
			return SMW_Gardening::getGardeningLogTable();
		} 
	
	} else {
		return $taskid;
	}
}

function smwfCancelGardeningBot($taskid) {
	if (!GardeningBot::isUserAllowed(array(SMW_GARD_SYSOPS, SMW_GARD_GARDENERS))) {
	 	return; // only sysops and gardeners may cancel a bot.
	}
	$processID = GardeningBot::getProcessID($taskid);
	if ($processID != NULL) {
		GardeningBot::killProcess($processID);
	} 
	GardeningLog::removeGardeningTask($taskid);
	return SMW_Gardening::getGardeningLogTable();
}

function smwfGetGardeningLog() {
	return SMW_Gardening::getGardeningLogTable();
}

function smwfGetBotParameters($botID) {
	return SMW_Gardening::getParameterFormularForBot($botID);
}

function smwfGetRegisteredBots() {
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

function smwGAAddHTMLHeader(&$out) {
	global $smwgHaloScriptPath, $smwgDeployVersion;
	
	$jsm = SMWResourceManager::SINGLETON();
	
	if (!isset($smwgDeployVersion) || $smwgDeployVersion === false) { 
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/prototype.js', "all", -1, NS_SPECIAL.":Gardening");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/scriptaculous/scriptaculous.js', "all", -1, NS_SPECIAL.":Gardening");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/OntologyBrowser/generalTools.js', "all", -1, NS_SPECIAL.":Gardening");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/Language/SMW_Language.js',  "all", -1, NS_SPECIAL.":Gardening");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/Gardening/gardening.js', "all", -1, NS_SPECIAL.":Gardening");
	} else {
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/prototype.js', "all", -1, NS_SPECIAL.":Gardening");
		smwfHaloAddJSLanguageScripts($jsm, "all", -1, NS_SPECIAL.":Gardening");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/deployGeneralTools.js', "all", -1, NS_SPECIAL.":Gardening");
		
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/Gardening/deployGardening.js', "all", -1, NS_SPECIAL.":Gardening");
	}
	
	$jsm->addCSSIf($smwgHaloScriptPath . '/scripts/Gardening/gardening.css', "all", -1, NS_SPECIAL.":Gardening");

	return true;
}

class SMW_Gardening {

	static function execute() {
		global $wgRequest, $wgOut, $smwgIQEnabled, $smwgIQMaxLimit, $wgUser, $smwgIQSortingEnabled;
		$skin = $wgUser->getSkin();
		$html = "<div style=\"margin-bottom:10px;\">".wfMsg('smw_gard_welcome')."</div>";
		$html .= "<div id=\"gardening-container\">" .
					"<div id=\"gardening-tools\">" . smwfGetRegisteredBots() .
					"</div>" .
					"<div id=\"gardening-tooldetails\"><div id=\"gardening-tooldetails-content\">".wfMsg('smw_gard_choose_bot')."</div></div>
		
					<div id=\"gardening-runningbots-head\">&nbsp;Current / Recent bot activities:</div>
					<div id=\"gardening-runningbots\">".SMW_Gardening::getGardeningLogTable()."</div>
				 </div>";
		$wgOut->addHTML($html);
	}
	
	static function getGardeningLogTable() {
		$html = "<table width=\"100%\" class=\"smwtable\"><tr><th>User</th><th>Action</th><th>Start-Time</th><th>End-Time</th><th>Log</th><th>Progress</th><th>State</th></tr>";
		$gardeningLog = GardeningLog::getGardeningLog();
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
 		$htmlResult .= "<button type=\"button\" name=\"run\" onclick=\"gardeningPage.run(event)\">Run Bot</button>";
 		return $htmlResult;
	}
	
}
?>
