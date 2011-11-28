<?php
/*
 * Copyright (C) Vulcan Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program.If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 * @file
 * @ingroup SemanticGardening
 *
 * Created on 04.02.2008
 *
 * @author Kai K�hn
 *
 */

if ( !defined( 'MEDIAWIKI' ) ) die;

global $wgAjaxExportList;


// register ajax calls

$wgAjaxExportList[] = 'smwf_ga_LaunchGardeningBot';
$wgAjaxExportList[] = 'smwf_ga_CancelGardeningBot';
$wgAjaxExportList[] = 'smwf_ga_GetGardeningLog';
$wgAjaxExportList[] = 'smwf_ga_GetBotParameters';
$wgAjaxExportList[] = 'smwf_ga_GetRegisteredBots';
$wgAjaxExportList[] = 'smwf_ga_GetGardeningIssueClasses';
$wgAjaxExportList[] = 'smwf_ga_GetGardeningIssues';
$wgAjaxExportList[] = 'smwf_ga_LaunchDedicatedGardeningBot';
$wgAjaxExportList[] = 'smwf_ga_readBotLog';

// Gardening ajax calls


function smwf_ga_readBotLog($taskid) {
	
    $botDir = GardeningBot::getWriteableDir();
    $botLogFile =  "$botDir"."log_$taskid";
	$botlog = file_get_contents($botLogFile);
	$botlog = preg_replace("/\x08/","\n",$botlog); // replace backspace by linefeed.
	$response = new AjaxResponse($botlog);
	$response->setContentType( "application/html" );
	$response->setResponseCode(200);
	return $response;
}
/**
 * Runs a gardening bot.
 *
 * @param $botID ID of bot
 * @param $params parameters as comma separated string
 * @param $user_id ID of user (may be NULL, in this case the user logged in is used)
 * @param $user_pass password of user (may be NULL, in this case the user logged in is used)
 *
 * @return $taskid ID of task.
 */
function smwf_ga_LaunchGardeningBot($botID, $params, $user_id, $user_pass) {
	global $sgagDedicatedGardeningMachine, $sgagIP;
	// import bots
	sgagImportBots("$sgagIP/includes/bots");
	if (!isset($sgagDedicatedGardeningMachine) || $sgagDedicatedGardeningMachine == 'localhost' || $sgagDedicatedGardeningMachine == '127.0.0.1') {
		global $wgUser;
		$user = NULL;
		if ($wgUser !== NULL) {
			$user = $wgUser;
		} else {
			if ($user_id != NULL) {
				$passwordBlob = smwfGetPasswordBlob($user_id);
				if ($passwordBlob === $user_pass) {
					$user = User::newFromId($user_id);
				}

			}
		}
		$taskid = GardeningBot::runBot($botID, $params, $user);
		if (gettype($taskid) == 'integer') { // task id, no error code

			if ($taskid >= 0) {
				return SGAGardening::getGardeningLogTable();
			}

		} else {
			return $taskid;
		}
	} else {
		// redirect call to dedicated gardening server
		global $wgScript, $wgCookiePrefix;
		$userID = $_COOKIE[$wgCookiePrefix."UserID"];
			
		$passwordBlob = smwfGetPasswordBlob($userID);
		if($passwordBlob != NULL) {
			$matches = array();
			$result = http_get("http://$sgagDedicatedGardeningMachine$wgScript?action=ajax&rs=smwf_ga_LaunchGardeningBot&rsargs[]=$botID&rsargs[]=".urlencode($params)."&rsargs[]=".$userID."&rsargs[]=".urlencode($passwordBlob), array('timeout' => 5));

			preg_match('/Content-Length:\s*(\d+)/', $result, $matches);
			if (isset($matches[1])) {
				$contentLength = $matches[1];
				return substr($result, strlen($result) - $contentLength);
			} else if (stripos($result, "<table") !== false) {
				// heuristic if length is missing in HTTP answer (why can this happen?)
				return substr($result, stripos($result, "<table"));
			}
		}
		return "ERROR:gardening-tooldetails:".wfMsg('smw_gard_no_permission');
	}
}

/**
 * Cancels a running bot.
 *
 * @param $taskid ID of task.
 */
function smwf_ga_CancelGardeningBot($taskid, $user_id, $user_pass) {
	global $sgagDedicatedGardeningMachine;
	if (!isset($sgagDedicatedGardeningMachine) || $sgagDedicatedGardeningMachine == 'localhost' || $sgagDedicatedGardeningMachine == '127.0.0.1') {
		global $wgUser;
		$user = $wgUser;
		if ($user_id != NULL) {
			$passwordBlob = smwfGetPasswordBlob($user_id);
			if ($passwordBlob === $user_pass) {
				$user = User::newFromId($user_id);
			}

		}

		if (is_null($user) || !$user->isAllowed('gardening')) {
			return SGAGardening::getGardeningLogTable(); // only sysops and gardeners may cancel a bot.
		}
		// send term signal to bot
		if (GardeningBot::abortBot($taskid) !== true) {
			// if bot does not react: kill bot
			GardeningBot::killBot($taskid);

		}
		SGAGardeningLog::getGardeningLogAccess()->removeGardeningTask($taskid);
		return SGAGardening::getGardeningLogTable();
	} else {
		// redirect call to dedicated gardening server
		global $wgScript, $wgCookiePrefix;
		$userID = $_COOKIE[$wgCookiePrefix."UserID"];

		$passwordBlob = smwfGetPasswordBlob($userID);
		if($passwordBlob != NULL) {
			$matches = array();
			$result = http_get("http://$sgagDedicatedGardeningMachine$wgScript?action=ajax&rs=smwf_ga_CancelGardeningBot&rsargs[]=".$taskid."&rsargs[]=".$userID."&rsargs[]=".urlencode($passwordBlob));

			preg_match('/Content-Length:\s*(\d+)/', $result, $matches);
			if (isset($matches[1])) {
				$contentLength = $matches[1];
				return substr($result, strlen($result) - $contentLength);
			} else if (stripos($result, "<table") !== false) {
				// heuristic if length is missing in HTTP answer (why can this happen?)
				return substr($result, stripos($result, "<table"));
			}
		}
		return "ERROR:gardening-tooldetails:".wfMsg('smw_gard_no_permission');
	}
}

/**
 * Returns gardening log as HTML
 */
function smwf_ga_GetGardeningLog() {

	return SGAGardening::getGardeningLogTable();
}

/**
 * Returns parameter form for given bot as HTML
 *
 * @param $botID
 */
function smwf_ga_GetBotParameters($botID) {

	return SGAGardening::getParameterFormularForBot($botID);
}


/**
 * Returns list of registered bots as HTML
 */
function smwf_ga_GetRegisteredBots() {

	global $registeredBots, $wgUser;
	$htmlResult = "";
	$first = true;
	foreach($registeredBots as $botID => $bot) {
		if (is_null($wgUser) || !$wgUser->isAllowed('gardening')) {
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



function smwf_ga_GetGardeningIssueClasses($bot_id) {

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


/**
 * Get Gardening issues for a pair of titles. Every parameter (except $bot_id)
 * may be empty or NULL
 *
 * @param string $botIDs A comma-separated list of Bot-IDs
 * @param string $giType type of issue.
 * @param string $giClass Class of issue.
 * @param string $title The name of an article
 * @param string $sortfor column to sort for. Default by title.
 *              One of the constants: SMW_GARDENINGLOG_SORTFORTITLE, SMW_GARDENINGLOG_SORTFORVALUE
 *
 * @return string xml
 * <gardeningIssues title="title" >
 *   <bot name="botname" title="Name of bot for GUI">
 *     <issue>Description of issue.</issue>
 *     ...
 *   </bot>
 *   ...
 * </gardeningIssues>
 *
 */
function smwf_ga_GetGardeningIssues($botIDs, $giType, $giClass, $title, $sortfor) {

	global $wgTitle;
	$gardeningAccess = SGAGardeningIssuesAccess::getGardeningIssuesAccess();

	if (!$title) {
		return 'smwf_ga_GetGardeningIssues: not title specified.';
	}
	$t = Title::newFromText($title);
	$article = new Article($t);

	if (!$article->exists()) {
		return 'smwf_ga_GetGardeningIssues: invalid title specified.';
	}

	if (!$botIDs) {
		return 'smwf_ga_GetGardeningIssues: no bot specified.';
	}

	if (!$giType) {
		$giType = null;
	}
	if (!$giClass) {
		$giClass = null;
	}
	if (!$sortfor) {
		$sortfor = null;
	}

	$botIDs = explode(',', $botIDs);
	$issues = array();
	foreach($botIDs as $b) {
		$issues[$b] = $gardeningAccess->getGardeningIssues($b, $giType, $giClass, $t, $sortfor, NULL);
	}


	$result = '<gardeningIssues title="'.$title.'">';
	foreach ($issues as $bot => $issueArray) {
		$botTitle = wfMsg($bot);
		$result .= '<bot name="'.$bot.'" title="'.$botTitle.'">';
		$skinDummy = NULL;
		foreach ($issueArray as $is) {
			$result .= '<issue>'.$is->getRepresentation($skinDummy, true).'</issue>';
		}
		$result .= '</bot>';
	}
	$result .= "</gardeningIssues>";
	return $result;

}

/**
 * Returns (MD5-hashed) passwort.
 *
 * @param int $userID
 * @return password hash as string
 */
function smwfGetPasswordBlob($userID) {
	$db = wfGetDB(DB_SLAVE);
	$pass_blob = NULL;
	$res = $db->select($db->tableName('user'), array('user_password'), array('user_id'=>$userID));
	if($db->numRows( $res ) == 1) {
		$row = $db->fetchObject($res);
		$pass_blob = $row->user_password;
	}
	return $pass_blob;
}


