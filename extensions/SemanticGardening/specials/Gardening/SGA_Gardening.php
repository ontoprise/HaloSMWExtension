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
 * @ingroup SemanticGardeningSpecials
 *
 * Created on 12.03.2007
 *
 * @author Kai K�hn
 */
if (!defined('MEDIAWIKI')) die();

if (function_exists("sgafGardeningInitMessages"))
sgafGardeningInitMessages();




global $sgagIP;
require_once($sgagIP. "/includes/SGA_GardeningBot.php");
require_once( $sgagIP . '/includes/SGA_GardeningBot.php');
require_once( $sgagIP . '/includes/SGA_GardeningIssues.php');
require_once("$sgagIP/includes/SGA_ParameterObjects.php");

// import bots
sgagImportBots("$sgagIP/includes/bots");




/*
 * Called when gardening request in sent in wiki
 */

class SGAGardening extends SpecialPage {


	public function __construct() {
		parent::__construct('Gardening');
	}

	public function execute($par) {
		global $wgRequest, $wgOut, $wgUser;
		$wgOut->setPageTitle(wfMsg('gardening'));
		$gardeningLogPage = Title::newFromText(wfMsg('gardeninglog'), NS_SPECIAL);
		$gardeningLogLink = $wgUser->getSkin()->makeKnownLinkObj($gardeningLogPage);
		$html = "<div>".wfMsg('smw_gard_welcome', $gardeningLogLink)."</div>";
		$html .= "<div id=\"gardening-container\">" .
					"<div id=\"gardening-tools\">" . SGAGardening::getRegisteredBots() .
					"</div>" .
					"<div id=\"gardening-tooldetails\"><div id=\"gardening-tooldetails-content\">".wfMsg('smw_gard_choose_bot')."</div></div>
		
					<div id=\"gardening-runningbots-head\">&nbsp;Current / Recent bot activities:
					<a id=\"gardening-togglebotlistbutton\" onclick=\"gardeningPage.toggleBotList(event)\">Toggle bot list / periodic bots</a></div>
					<div id=\"gardening-runningbots\">".SGAGardening::getGardeningLogTable()."</div>
					<div style=\"display:none\" id=\"gardening-periodicbots\">".SGAGardening::getPeriodicBotTable()."</div>
				 </div>";
		$wgOut->addHTML($html);
	}

	static function getPeriodicBotTable() {
		$pe = SGAPeriodicExecutors::getPeriodicExecutors();
		$periodicBots = $pe->getAllRegisteredBots();
		$html = "<table class=\"smwtable\" style=\"width: 100%\">";
		$html .= "<th>Bot</th>";
		$html .= "<th>Last run</th>";
		$html .= "<th>Interval</th>";
		foreach ($periodicBots as $row) {
			list($id,$botid,$params,$lastrun,$duration, $runonce) = $row;
			$html .= "<tr id=\"periodic-bot-entry-$id\">";

			$html .= "<td>".wfMsg($botid)."</td>";

			if ($runonce == 'y') {
				$html .= "<td>".$lastrun."</td>";
			} else {
				$html .= "<td>n/a</td>";
			}

			if ($duration == 3600 * 24) {
				$duration = wfMsg('smw_gard_daily');
			} else  if ($duration == 3600 * 24 * 7) {
				$duration = wfMsg('smw_gard_weekly');
			} else  if ($duration == 3600) {
				$duration = wfMsg('smw_gard_hourly');
			} else {
				$duration = $duration." seconds";
			}
			$html .= "<td>".$duration."</td>";
			$html .= "<td><input type=\"button\" onclick=\"gardeningPage.removePeriodicBot(event,$id)\" value=\"".wfMsg('smw_gard_removebot')."\"></input></td>";
			$html .= "</tr>";

		}
		$html .= "</table>";
		return $html;
	}

	static function getGardeningLogTable() {
		global $wgServer,$wgScript, $wgArticlePath;
		$html = "<table width=\"100%\" class=\"smwtable\"><tr><th>User</th><th>Action</th><th>Start-Time</th><th>End-Time</th><th>Log</th><th>Progress</th><th>State</th></tr>";
		$gardeningLog = SGAGardeningLog::getGardeningLogAccess()->getGardeningLogAsTable();
		if ($gardeningLog == null || !is_array($gardeningLog)) {
			return $gardeningLog;
		}
		$glp = Title::newFromText(wfMsg('gardeninglog'), NS_SPECIAL);
		foreach ($gardeningLog as $row) {
			$html .= "<tr>";
			list($user,$gardeningbot,$starttime,$endtime,$log, $progress, $id, $comment) = $row;

			$html .= "<td>".$user."</td>";
			$html .= "<td>".wfMsg($gardeningbot)."</td>";
			$html .= "<td>".$starttime."</td>";
			$html .= "<td>".$endtime."</td>";

			if ($endtime != null) {
				// check if it points to log page or GardeningLog
				// FIXME: clean up: GardeningLog links should be simply empty
				$parts = explode("?bot=", $log);
				if (count($parts) == 2) { // GardeningLog
					$botID = $parts[1];
					$html .= "<td><a href=\"".$glp->getFullURL("bot=$botID")."\">Log</a></td>";
				} else { // log page
					$logPage = Title::newFromText($parts[0]);
					$html .= "<td><a href=\"".$logPage->getFullURL()."\">Log</a></td>";
				}

			}
				
			$html .= "<td>".(number_format(($progress+0)*100))."%</td>";

			$runningBot = $endtime == null;
			$html .= ($runningBot ? "<td class=\"runningBots\">running</td>" : "<td class=\"finishedBots\">finished</td>");
			$html .= "<td><button type=\"button\" name=\"abort\" ".($runningBot ? "" : "disabled")." onclick=\"gardeningPage.cancel(event, ".$id.")\">".wfMsg('smw_gard_abortbot')."</button></td>";
			global $sgaTempDir;
			$html .= "<td><a href=\"$wgServer$wgScript?action=ajax&rs=smwf_ga_readBotLog&rsargs[]=".$id."\">".wfMsg('smw_gard_consolelog')."</a></td>";
			$html .= "</tr>";
		}
		$html .= "</table>";
		return $html;
	}

	static function getGardeningLogAsJSON() {
		global $wgServer,$wgScript, $wgArticlePath;

		$gLog = array();
		$gardeningLog = SGAGardeningLog::getGardeningLogAccess()->getGardeningLogAsTable();
		if ($gardeningLog == null || !is_array($gardeningLog)) {
			return json_encode($gLog);
		}

		foreach ($gardeningLog as $row) {

			$o = new stdClass();
			list($user,$gardeningbot,$starttime,$endtime,$log, $progress, $id, $comment) = $row;
			$o->user = $user;
			$o->gardeningbot = $gardeningbot;
			$o->starttime = $starttime;
			$o->endtime = $endtime;
			$o->log = $log;
			$o->progress = $progress;
			$o->id = $id;
			$o->comment = $comment;
			$gLog[] = $o;
		}

		return json_encode($gLog);
	}

	static function getRegisteredBots() {
		global $registeredBots, $wgUser, $wgServer, $wgScriptPath;
		$htmlResult = "";
		$first = true;
		foreach($registeredBots as $botID => $bot) {
			if (is_null($wgUser) || !$wgUser->isAllowed('gardening')) {
				continue; // do not add this bot, because the user must not access it.
			}

			if (!$bot->isVisible()) {
				continue;
			}

			$imageDirectory = $bot->getImageDirectory();

			// if $imageDirectory is NULL, try to find icons in the SemanticGardening skin folder
			$imageDirectory = $imageDirectory == NULL ? 'extensions/SemanticGardening/skins' : $imageDirectory;

			$htmlResult .= "<div class=\"entry\" onMouseOver=\"this.className='entry-over';\"" .
							" onMouseOut=\"gardeningPage.showRightClass(event, this, '$botID')\" onClick=\"gardeningPage.showParams(event, this, '$botID')\" id=\"$botID\">" .
							"<table width=\"100%\"><tr>" .
							"<td><img src=\"$wgServer$wgScriptPath/$imageDirectory/" . $botID . "_image.png\"/></td>" .
							"<td><a>" . $bot->getLabel() . "</a></td>" .
							"</tr></table>" .
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
			
		if ($bot->canBeRun()) {
			$htmlResult .= "<form id=\"gardeningParamForm\"";
			$parameters = $bot->getParameters();
			foreach($parameters as $param) {
				$htmlResult .= $param->serializeAsHTML()."<br>";
			}
			$htmlResult .= "</form><br>";
			$htmlResult .= "<button id=\"runBotButton\" type=\"button\" name=\"run\" onclick=\"gardeningPage.run(event)\">Run

Bot</button>";
			$htmlResult .= "<hr>".wfMsg('smw_gard_addperiodicbot_msg')."<div style=\"margin-top: 5px\">".wfMsg('smw_gard_interval')."<select style=\"margin-left: 5px\" id=\"periodic_intervals\">";
			$htmlResult .= "<option value=\"daily\" name=\"daily\">".wfMsg('smw_gard_daily')."</option>";
			$htmlResult .= "<option value=\"weekly\" name=\"weekly\">".wfMsg('smw_gard_weekly')."</option>";
			$htmlResult .= "<option value=\"hourly\" name=\"hourly\">".wfMsg('smw_gard_hourly')."</option>";
			$htmlResult .= "</select></div>";
			$htmlResult .= "<div style=\"margin-top: 5px\">".wfMsg('smw_gard_startatdate')." <input id=\"startatdate\" type=\"text\" /> ".wfMsg('smw_gard_startattime')." <input id=\"startattime\" type=\"text\" /></div>";
			$htmlResult .= "<div><button style=\"margin-top: 5px\" id=\"addPeriodic\" type=\"button\" name=\"addperiodic\" onclick=\"gardeningPage.addPeriodic(event)\">".wfMsg('smw_gard_addperiodicbot')."</button></div>";
		}

		return $htmlResult;

	}


}

