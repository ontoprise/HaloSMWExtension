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
 * Created on 12.03.2007
 *
 * @file
 * @ingroup SMWHaloSpecials
 * @ingroup SMWHaloTriplestore
 *
 * @author Kai K�hn
 */
if (!defined('MEDIAWIKI')) die();

global $IP;
require_once( "$IP/includes/SpecialPage.php" );

/*
 * Called when gardening request in sent in wiki
 */

class TSCTripleStoreAdmin extends SpecialPage {


	public function __construct() {
		parent::__construct('TSA', 'delete');
	}

	public function execute($par) {
		global $wgRequest, $wgOut, $smwgMessageBroker, $smwgHaloWebserviceEndpoint, $wgUser, $smwgHaloEnableObjectLogicRules;

		if ( !$this->userCanExecute( $wgUser ) ) {
			// If the user is not authorized, show an error.
			$this->displayRestrictionError();
			return;
		}

		$wgOut->setPageTitle(wfMsg('tsa'));
		$html = "";
		if (!smwfIsTripleStoreConfigured()) {
			$wgOut->addWikiText(wfMsg('tsc_advertisment'));
			return;
		}
		if ($wgRequest->getVal('init') != NULL) {
			// after init
			smwfGetStore()->initialize(false);
			$html .= $wgUser->getSkin()->makeKnownLinkObj(Title::newFromText("TSA", NS_SPECIAL), wfMsg('smw_tsa_waitsoemtime'));
			$wgOut->addHTML($html);
			return;
		}
		$html .= "<div style=\"margin-bottom:10px;\">".wfMsg('smw_tsa_welcome')."</div>";
		global $smwgHaloTripleStoreGraph;
		TSConnection::getConnector()->connect();
		try {
			$status = TSConnection::getConnector()->getStatus($smwgHaloTripleStoreGraph);
		} catch(Exception $e) {

			// if no connection could be created
			$html .= "<div style=\"color:red;font-weight:bold;\">".wfMsg('smw_tsa_couldnotconnect')."</div>".wfMsg('smw_tsa_addtoconfig').
        	"<pre>".
        	"\$smwgHaloWebserviceEndpoint = &lt;IP and port of TSC webservice endpoint&gt;\n\nExample:\n\n".
            "\$smwgHaloWebserviceEndpoint = \"localhost:8080\";</pre>".
			wfMsg('smw_tsa_addtoconfig2')." <pre>enableSMWHalo();</pre>".
			wfMsg('smw_tsa_addtoconfig3');
			$smwForumLink = '<a href="http://smwforum.ontoprise.com/smwforum/index.php/Help:TripleStore_Basic">SMW-Forum</a>';
			$html .= "<br><b>".wfMsg('smw_tsa_addtoconfig4', $smwForumLink)."</b>";
			$wgOut->addHTML($html);
			return;

		}
			
		// normal
		$html .= "<h2>".wfMsg('smw_tsa_driverinfo')."</h2>".$status['driverInfo']."";
		$html .= "<h2>".wfMsg('smw_tsa_tscinfo')."</h2>";
		$html .= wfMsg('smw_tsa_tscversion').": ".$status['tscversion'];

		$html .= "<h2>".wfMsg('smw_tsa_licenseinfo')."</h2>";
			$html .= wfMsg('smw_tsa_license').": ";
		if ($status['licenseState'] != 'VALID' && $status['licenseState'] != 'NOT_AVAILABLE') {
			$html .= "<span style=\"color:red;font-weight:bold;\">";
		} else {
			$html .= "<span style=\"color:green;font-weight:bold;\">";
		}
		$html .= $status['licenseState'];
		$html .= "</span>";

		if (!in_array('RULES', $status['features']) && $smwgHaloEnableObjectLogicRules === true) $html .= "<div style=\"color:red;font-weight:bold;\">".
		wfMsg('smw_tsa_norulesupport')."</div>";

		$html .= "<h2>".wfMsg('smw_tsa_loadgraphs')."</h2>";
		$html .= "<table>";
		foreach($status['loadedGraphs'] as $g) {
			$html .= '<tr><td>'.$g.'</td></tr>';
		}
		$html .= "</table>";

		$html .= "<h2>".wfMsg('smw_tsa_autoloadfolder')."</h2>";
		$html .= $status['autoloadFolder'];

		$html .= "<h2>".wfMsg('smw_tsa_tscparameters')."</h2>";
		$html .= "<table>";
		foreach($status['startParameters'] as $p) {
			list($name, $value) = $p;
			$html .= '<tr><td>'.$name.'</td><td>'.$value.'</td></tr>';
		}
		$html .= "</table>";

		$html .= "<h2>".wfMsg('smw_tsa_synccommands')."</h2>";
		$rewrittenCommands = array();
		foreach($status['syncCommands'] as $c) {
			$rewrittenCommands[] = $this->hideCredentials($c);
		}
		$html .= '<pre>'.htmlspecialchars(implode("\n",$rewrittenCommands)).'</pre>';



		$html .= "<h2>".wfMsg('smw_tsa_status')."</h2>";
		if ($status['isInitialized'] == true) {
			$html .= "<div style=\"color:green;font-weight:bold;\">".wfMsg('smw_tsa_wikiconfigured', (is_array($smwgHaloWebserviceEndpoint) ? implode(", ", $smwgHaloWebserviceEndpoint) : $smwgHaloWebserviceEndpoint))."</div>";
			$tsaPage = Title::newFromText("TSA", NS_SPECIAL);
			$html .= "<br><form><input name=\"init\" type=\"submit\" value=\"".wfMsg('smw_tsa_reinitialize')."\"/><input name=\"title\" type=\"hidden\" value=\"".$tsaPage->getPrefixedDBkey()."\"/></form>";
		} else {
			$html .= "<div style=\"color:red;font-weight:bold;\">".wfMsg('smw_tsa_notinitalized')."</div>".wfMsg('smw_tsa_pressthebutton');
			$tsaPage = Title::newFromText("TSA", NS_SPECIAL);
			$html .= "<br><form><input name=\"init\" type=\"submit\" value=\"".wfMsg('smw_tsa_initialize')."\"/><input name=\"title\" type=\"hidden\" value=\"".$tsaPage->getPrefixedDBkey()."\"/></form>";
		}

		$wgOut->addHTML($html);
	}

	/**
	 * Eliminates the credentials from a smw://... link
	 *
	 * @param string $sparul
	 * @return string
	 */
	private function hideCredentials($sparul) {
		if (strpos($sparul, "smw://") !== false) {
			$start = strpos($sparul, "smw://") + strlen("smw://");
			$end = strpos($sparul, "@");

			for($i = $start; $i < $end; $i++) {
				$sparul[$i] = "X";
			}
		}
		return $sparul;
	}
}

