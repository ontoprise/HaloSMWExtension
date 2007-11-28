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
 $wgHooks['BeforePageDisplay'][]='smwRSAddHTMLHeader';
 $wgHooks['BeforePageDisplay'][]='smwFWAddHTMLHeader';
 $wgHooks['ParserBeforeStrip'][] = 'smwRegisterQueryResultEditor'; // register the <ask> parser hook
 // register ajax calls

 $wgAjaxExportList[] = 'smwfLaunchGardeningBot';
 $wgAjaxExportList[] = 'smwfCancelGardeningBot';
 $wgAjaxExportList[] = 'smwfGetGardeningLog';
 $wgAjaxExportList[] = 'smwfGetBotParameters';
 $wgAjaxExportList[] = 'smwfGetRegisteredBots';
 $wgAjaxExportList[] = 'smwfGetGardeningIssueClasses';
 $wgAjaxExportList[] = 'smwfSendAnnotationRatings';
 
 // global functions

 // OntologyBrowser scripts callback
 function smwOBAddHTMLHeader(&$out) {
	global $smwgHaloScriptPath, $smwgDeployVersion, $smwgHaloIP, $wgLanguageCode, $smwgScriptPath;

	$jsm = SMWResourceManager::SINGLETON();

	if (!isset($smwgDeployVersion) || $smwgDeployVersion === false) {
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/prototype.js', "all", -1, NS_SPECIAL.":OntologyBrowser");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/scriptaculous/effects.js', "all", -1, NS_SPECIAL.":OntologyBrowser");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/OntologyBrowser/generalTools.js', "all", -1, NS_SPECIAL.":OntologyBrowser");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/Language/SMW_Language.js', "all", -1, NS_SPECIAL.":OntologyBrowser");

		smwfHaloAddJSLanguageScripts($jsm, "all", -1, NS_SPECIAL.":OntologyBrowser");
		
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/OntologyBrowser/ontologytools.js', "all", -1, NS_SPECIAL.":OntologyBrowser");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/OntologyBrowser/treeview.js', "all", -1, NS_SPECIAL.":OntologyBrowser");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/OntologyBrowser/treeviewActions.js', "all", -1, NS_SPECIAL.":OntologyBrowser");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/OntologyBrowser/treeviewData.js', "all", -1, NS_SPECIAL.":OntologyBrowser");
		$jsm->addScriptIf($smwgScriptPath .  '/skins/SMW_tooltip.js', "all", -1, NS_SPECIAL.":OntologyBrowser");
	} else {
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/prototype.js', "all", -1, NS_SPECIAL.":OntologyBrowser");
		smwfHaloAddJSLanguageScripts($jsm, "all", -1, NS_SPECIAL.":OntologyBrowser");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/deployGeneralTools.js', "all", -1, NS_SPECIAL.":OntologyBrowser");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/OntologyBrowser/deployOntologyBrowser.js', "all", -1, NS_SPECIAL.":OntologyBrowser");
	}

	$jsm->addCSSIf($smwgHaloScriptPath . '/skins/OntologyBrowser/treeview.css', "all", -1, NS_SPECIAL.":OntologyBrowser");
	$jsm->addCSSIf($smwgHaloScriptPath . '/skins/OntologyBrowser/SMW_custom.css', "all", -1, NS_SPECIAL.":OntologyBrowser");

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
	$processID = GardeningBot::getProcessID($taskid);
	if ($processID != NULL) {
		GardeningBot::killProcess($processID);
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
	 /*	if (!GardeningBot::isUserAllowed($bot->allowedForUserGroups())) {
	 		continue; // do not add this bot, because the user must not access it.
	 	}*/
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
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/prototype.js', "all", -1, array(NS_SPECIAL.":Gardening", NS_SPECIAL.":GardeningLog"));
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/scriptaculous/scriptaculous.js', "all", -1, array(NS_SPECIAL.":Gardening", NS_SPECIAL.":GardeningLog"));
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/OntologyBrowser/generalTools.js', "all", -1, array(NS_SPECIAL.":Gardening", NS_SPECIAL.":GardeningLog"));
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/Language/SMW_Language.js',  "all", -1, array(NS_SPECIAL.":Gardening", NS_SPECIAL.":GardeningLog"));
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/Gardening/gardening.js', "all", -1, array(NS_SPECIAL.":Gardening", NS_SPECIAL.":GardeningLog"));
		
	} else {
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/prototype.js', "all", -1, array(NS_SPECIAL.":Gardening", NS_SPECIAL.":GardeningLog"));
		smwfHaloAddJSLanguageScripts($jsm, "all", -1, array(NS_SPECIAL.":Gardening", NS_SPECIAL.":GardeningLog"));
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/deployGeneralTools.js', "all", -1, array(NS_SPECIAL.":Gardening", NS_SPECIAL.":GardeningLog"));

		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/Gardening/deployGardening.js', "all", -1, array(NS_SPECIAL.":Gardening", NS_SPECIAL.":GardeningLog"));
		
	}

	$jsm->addCSSIf($smwgHaloScriptPath . '/skins/Gardening/gardening.css', "all", -1, NS_SPECIAL.":Gardening");
	$jsm->addCSSIf($smwgHaloScriptPath . '/skins/Gardening/gardeningLog.css', "all", -1, NS_SPECIAL.":GardeningLog");

	return true;
}

// QueryInterface scripts callback
function smwfQIAddHTMLHeader(&$out){
	global $smwgHaloScriptPath, $smwgDeployVersion, $smwgScriptPath;


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
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/SemanticToolbar/SMW_Help.js', "all", -1, NS_SPECIAL.":QueryInterface");
		$jsm->addScriptIf($smwgScriptPath .  '/skins/SMW_tooltip.js', "all", -1, NS_SPECIAL.":QueryInterface");
	} else {

		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/prototype.js', "all", -1, NS_SPECIAL.":QueryInterface");
		smwfHaloAddJSLanguageScripts($jsm, "all", -1, NS_SPECIAL.":QueryInterface");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/deployGeneralTools.js', "all", -1, NS_SPECIAL.":QueryInterface");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/QueryInterface/deployQueryInterface.js', "all", -1, NS_SPECIAL.":QueryInterface");
	}
	$jsm->addCSSIf($smwgScriptPath .  '/skins/SMW_custom.css', "all", -1, NS_SPECIAL.":QueryInterface");	
	$jsm->addCSSIf($smwgHaloScriptPath . '/skins/QueryInterface/treeview.css', "all", -1, NS_SPECIAL.":QueryInterface");
	$jsm->addCSSIf($smwgHaloScriptPath . '/skins/QueryInterface/qi.css', "all", -1, NS_SPECIAL.":QueryInterface");

	return true; // do not load other scripts or CSS
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

function smwRegisterQueryResultEditor(&$parser, &$text, &$stripstate){
	$parser->setHook( 'ask', 'smwAddQueryResultEditor' );
	return true; // always return true, in order not to stop MW's hook processing!
}

global $smwgHaloIP;
require_once($smwgHaloIP . '/includes/SMW_QueryHighlighter.php');

/**
 * The <ask> parser hook processing part.
 */
function smwAddQueryResultEditor($text, $param, &$parser) {
	global $smwgQEnabled;
	
	if ($smwgQEnabled) {
		return applyQueryHighlighting($text, $param);
	} else {
		return smwfEncodeMessages(array(wfMsgForContent('smw_iq_disabled')));
	}
}

function smwRSAddHTMLHeader(& $out) {
	global $smwgHaloScriptPath, $smwgDeployVersion, $smwgHaloIP, $wgLanguageCode, $smwgScriptPath;

	$jsm = SMWResourceManager::SINGLETON();

	if (!isset($smwgDeployVersion) || $smwgDeployVersion === false) {
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/prototype.js', "all", -1, NS_SPECIAL.":RefactorStatistics");
		
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/Language/SMW_Language.js', "all", -1, NS_SPECIAL.":RefactorStatistics");

		smwfHaloAddJSLanguageScripts($jsm, "all", -1, NS_SPECIAL.":RefactorStatistics");
		
	} else {
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/prototype.js', "all", -1, NS_SPECIAL.":RefactorStatistics");
		smwfHaloAddJSLanguageScripts($jsm, "all", -1, NS_SPECIAL.":RefactorStatistics");
		
	}

	$jsm->addCSSIf($smwgHaloScriptPath . '/skins/RefactorPreview/refactorpreview.css', "all", -1, NS_SPECIAL.":RefactorStatistics");
	
	return true;
}

function smwFWAddHTMLHeader(& $out) {
	global $smwgHaloScriptPath, $smwgDeployVersion, $smwgHaloIP, $wgLanguageCode, $smwgScriptPath;

	$jsm = SMWResourceManager::SINGLETON();

	if (!isset($smwgDeployVersion) || $smwgDeployVersion === false) {
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/prototype.js', "all", -1, NS_SPECIAL.":FindWork");
		
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/Language/SMW_Language.js', "all", -1, NS_SPECIAL.":FindWork");

		smwfHaloAddJSLanguageScripts($jsm, "all", -1, NS_SPECIAL.":FindWork");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/FindWork/findwork.js', "all", -1, NS_SPECIAL.":FindWork");
		
	} else {
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/prototype.js', "all", -1, NS_SPECIAL.":FindWork");
		smwfHaloAddJSLanguageScripts($jsm, "all", -1, NS_SPECIAL.":FindWork");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/FindWork/findwork.js', "all", -1, NS_SPECIAL.":FindWork");
	}

	
	
	return true;
}

function smwfSendAnnotationRatings($json) {
	// TODO: has to be replaced by *real* JSON decoding. >= PHP 5.2.0
	$ratings = array();
	$listOFRatings = substr($json, 1, strlen($json) - 2);
	preg_match_all("/\[([^]]*)\]/", $listOFRatings, $ratings);
	foreach($ratings[1] as $r) {
		list($subject, $predicate, $object, $rating) = split(",", $r);
		smwfGetSemanticStore()->rateAnnotation(trim(str_replace("\"", "", $subject)),
											   trim(str_replace("\"", "", $predicate)), 
											   trim(str_replace("\"", "", $object)),
											   intval(str_replace("\"", "", $rating)));
	}
	return "ratings saved.";
}
?>
