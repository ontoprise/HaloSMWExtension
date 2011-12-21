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
		
					<div id=\"gardening-runningbots-head\">&nbsp;Current / Recent bot activities:</div>
					<div id=\"gardening-runningbots\">".SGAGardening::getGardeningLogTable()."</div>
				 </div>";
		$wgOut->addHTML($html);
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
			for ($i=0; $i < count($row)-1;$i++) {

					
				if ($i == 4 && $row[3] != null) {
					// check if it points to log page or GardeningLog
					// FIXME: clean up: GardeningLog links should be simply empty
					$parts = explode("?bot=", $row[$i]);
					if (count($parts) == 2) { // GardeningLog
						$botID = $parts[1];
						$html .= "<td><a href=\"".$glp->getFullURL("bot=$botID")."\">Log</a></td>";
					} else { // log page
						$logPage = Title::newFromText($parts[0]);
						$html .= "<td><a href=\"".$logPage->getFullURL()."\">Log</a></td>";
					}
						
				} else if ($i == 1) {
					$html .= "<td>".wfMsg($row[$i])."</td>";
				} else if ($i == 5) {
					$html .= "<td>".(number_format(($row[$i]+0)*100))."%</td>";
				} else {
					$html .= "<td>".$row[$i]."</td>";
				}
			}
			$runningBot = $row[3] == null;
			$html .= ($runningBot ? "<td class=\"runningBots\">running</td>" : "<td class=\"finishedBots\">finished</td>");
			$html .= "<td><button type=\"button\" name=\"abort\" ".($runningBot ? "" : "disabled")." onclick=\"gardeningPage.cancel(event, ".$row[6].")\">".wfMsg('smw_gard_abortbot')."</button></td>";
			global $sgaTempDir;
			$html .= "<td><a href=\"$wgServer$wgScript?action=ajax&rs=smwf_ga_readBotLog&rsargs[]=".$row[$i]."\">".wfMsg('smw_gard_consolelog')."</a></td>";
			$html .= "</tr>";
		}
		$html .= "</table>";
		return $html;
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
		} 
		
		return $htmlResult;

	}


}

