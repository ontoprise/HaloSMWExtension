<?php
/*
 * Created on 04.02.2008
 *
 * Author: kai
 */
 
 global $wgAjaxExportList;

 
 // register ajax calls

 $wgAjaxExportList[] = 'smwfLaunchGardeningBot';
 $wgAjaxExportList[] = 'smwfCancelGardeningBot';
 $wgAjaxExportList[] = 'smwfGetGardeningLog';
 $wgAjaxExportList[] = 'smwfGetBotParameters';
 $wgAjaxExportList[] = 'smwfGetRegisteredBots';
 $wgAjaxExportList[] = 'smwfGetGardeningIssueClasses';
 
 // Gardening ajax calls

global $smwgHaloIP;
require_once( $smwgHaloIP . "/specials/SMWGardening/SMW_GardeningBot.php");
require_once( $smwgHaloIP . "/specials/SMWGardening/SMW_GardeningLog.php");

/**
 * Runs a gardening bot.
 *
 * @param $botID ID of bot
 * @param $params parameters as comma separated string
 *
 * @return $taskid ID of task.
 */
function smwfLaunchGardeningBot($botID, $params) {

	$taskid = GardeningBot::runBot($botID, $params);
	if (gettype($taskid) == 'integer') { // task id, no error code

		if ($taskid >= 0) {
			return SMWGardening::getGardeningLogTable();
		}

	} else {
		return $taskid;
	}
}

/**
 * Cancels a running bot.
 *
 * @param $taskid ID of task.
 */
function smwfCancelGardeningBot($taskid) {
	if (!GardeningBot::isUserAllowed(array(SMW_GARD_SYSOPS, SMW_GARD_GARDENERS))) {
	 	return; // only sysops and gardeners may cancel a bot.
	}
	// send term signal to bot
	if (GardeningBot::abortBot($taskid) !== true) {
		// if bot does not react: kill process
		$processID = GardeningBot::getProcessID($taskid);
		if ($processID != NULL) {
			GardeningBot::killProcess($processID);
		}
	}
	SMWGardening::getGardeningLogAccess()->removeGardeningTask($taskid);
	return SMWGardening::getGardeningLogTable();
}

/**
 * Returns gardening log as HTML
 */
function smwfGetGardeningLog() {
	return SMWGardening::getGardeningLogTable();
}

/**
 * Returns parameter form for given bot as HTML
 *
 * @param $botID
 */
function smwfGetBotParameters($botID) {
	return SMWGardening::getParameterFormularForBot($botID);
}


/**
 * Returns list of registered bots as HTML
 */
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



function smwfGetGardeningIssueClasses($bot_id) {
	global $registeredBots;
		
		if ($bot_id == NULL) {
			return "<span id=\"issueClasses\">unknown bot</span>";
		} else {
			$className = get_class($registeredBots[$bot_id]).'Filter';
			$filter = new $className();
			
	 		$html = "<span id=\"issueClasses\"><select name=\"class\">";
			$i = 0;
			foreach($filter->getIssueClasses() as $class) {
				$html .= "<option value=\"$i\">$class</option>";
				$i++;		
			}
	 		$html .= 	"</select>";
	 		
			$html .= $filter->getUserFilterControls(NULL, NULL);
			$html .= "</span>";
			return $html;
		}
}



 
?>
