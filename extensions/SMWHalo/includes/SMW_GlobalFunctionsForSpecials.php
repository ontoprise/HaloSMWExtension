<?php
/*
 * Created on 26.09.2007
 *
 * Author: kai
 */

 global $wgHooks, $wgAjaxExportList;

 // register hooks
 $wgHooks['BeforePageDisplay'][]='smwOBAddHTMLHeader';
 $wgHooks['BeforePageDisplay'][]='smwGAAddHTMLHeader';
 $wgHooks['BeforePageDisplay'][]='smwfQIAddHTMLHeader';

 // register ajax calls

 $wgAjaxExportList[] = 'smwfLaunchGardeningBot';
 $wgAjaxExportList[] = 'smwfCancelGardeningBot';
 $wgAjaxExportList[] = 'smwfGetGardeningLog';
 $wgAjaxExportList[] = 'smwfGetBotParameters';
 $wgAjaxExportList[] = 'smwfGetRegisteredBots';

 // global functions

 // OntologyBrowser scripts callback
 function smwOBAddHTMLHeader(&$out) {
	global $smwgHaloScriptPath, $smwgDeployVersion, $smwgHaloIP, $wgLanguageCode;

	$jsm = SMWResourceManager::SINGLETON();

	if (!isset($smwgDeployVersion) || $smwgDeployVersion === false) {
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/prototype.js', "all", -1, NS_SPECIAL.":OntologyBrowser");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/scriptaculous/effects.js', "all", -1, NS_SPECIAL.":OntologyBrowser");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/OntologyBrowser/generalTools.js', "all", -1, NS_SPECIAL.":OntologyBrowser");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/Language/SMW_Language.js', "all", -1, NS_SPECIAL.":OntologyBrowser");

		smwfHaloAddJSLanguageScripts($jsm, "all", -1, NS_SPECIAL.":OntologyBrowser");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/OntologyBrowser/treeview.js', "all", -1, NS_SPECIAL.":OntologyBrowser");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/OntologyBrowser/treeviewActions.js', "all", -1, NS_SPECIAL.":OntologyBrowser");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/OntologyBrowser/treeviewData.js', "all", -1, NS_SPECIAL.":OntologyBrowser");
	} else {
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/prototype.js', "all", -1, NS_SPECIAL.":OntologyBrowser");
		smwfHaloAddJSLanguageScripts($jsm, "all", -1, NS_SPECIAL.":OntologyBrowser");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/deployGeneralTools.js', "all", -1, NS_SPECIAL.":OntologyBrowser");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/OntologyBrowser/deployOntologyBrowser.js', "all", -1, NS_SPECIAL.":OntologyBrowser");
	}

	$jsm->addCSSIf($smwgHaloScriptPath . '/skins/OntologyBrowser/treeview.css', "all", -1, NS_SPECIAL.":OntologyBrowser");

	return true;
}

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
			return SMW_Gardening::getGardeningLogTable();
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
	$processID = GardeningBot::getProcessID($taskid);
	if ($processID != NULL) {
		GardeningBot::killProcess($processID);
	}
	GardeningLog::removeGardeningTask($taskid);
	return SMW_Gardening::getGardeningLogTable();
}

/**
 * Returns gardening log as HTML
 */
function smwfGetGardeningLog() {
	return SMW_Gardening::getGardeningLogTable();
}

/**
 * Returns parameter form for given bot as HTML
 *
 * @param $botID
 */
function smwfGetBotParameters($botID) {
	return SMW_Gardening::getParameterFormularForBot($botID);
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

// Gardening scripts callback
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

	$jsm->addCSSIf($smwgHaloScriptPath . '/skins/Gardening/gardening.css', "all", -1, NS_SPECIAL.":Gardening");

	return true;
}

// QueryInterface scripts callback
function smwfQIAddHTMLHeader(&$out){
	global $smwgHaloScriptPath, $smwgDeployVersion;


	$jsm = SMWResourceManager::SINGLETON();

	if (!isset($smwgDeployVersion) || $smwgDeployVersion === false) {
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/prototype.js', "all", -1, NS_SPECIAL.":QueryInterface");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/Language/SMW_Language.js', "all", -1, NS_SPECIAL.":QueryInterface");
		smwfHaloAddJSLanguageScripts($jsm, "all", -1, NS_SPECIAL.":QueryInterface");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/Logger/smw_logger.js', "all", -1, NS_SPECIAL.":QueryInterface");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/OntologyBrowser/generalTools.js', "all", -1, NS_SPECIAL.":QueryInterface");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/QueryInterface/treeviewQI.js', "all", -1, NS_SPECIAL.":QueryInterface");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/QueryInterface/queryTree.js', "all", -1, NS_SPECIAL.":QueryInterface");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/QueryInterface/Query.js', "all", -1, NS_SPECIAL.":QueryInterface");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/QueryInterface/QIHelper.js', "all", -1, NS_SPECIAL.":QueryInterface");
	} else {

		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/prototype.js', "all", -1, NS_SPECIAL.":QueryInterface");
		smwfHaloAddJSLanguageScripts($jsm, "all", -1, NS_SPECIAL.":QueryInterface");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/deployGeneralTools.js', "all", -1, NS_SPECIAL.":QueryInterface");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/QueryInterface/deployQueryInterface.js', "all", -1, NS_SPECIAL.":QueryInterface");

	}

	$jsm->addCSSIf($smwgHaloScriptPath . '/skins/QueryInterface/treeview.css', "all", -1, NS_SPECIAL.":QueryInterface");
	$jsm->addCSSIf($smwgHaloScriptPath . '/skins/QueryInterface/qi.css', "all", -1, NS_SPECIAL.":QueryInterface");

	return true; // do not load other scripts or CSS
}
?>
