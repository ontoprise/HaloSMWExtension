<?php
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

class SMWTripleStoreAdmin extends SpecialPage {


	public function __construct() {
		parent::__construct('TSA', 'delete');
	}

	public function execute($par) {
		global $wgRequest, $wgOut, $smwgMessageBroker, $smwgWebserviceEndpoint, $wgUser, $smwgEnableObjectLogicRules;
		$wgOut->setPageTitle(wfMsg('tsa'));
		$html = "";
		if ($wgRequest->getVal('init') != NULL) {
			// after init
			smwfGetStore()->initialize(false);
			$html .= $wgUser->getSkin()->makeKnownLinkObj(Title::newFromText("TSA", NS_SPECIAL), wfMsg('smw_tsa_waitsoemtime'));
			$wgOut->addHTML($html);
			return;
		}
		$html .= "<div style=\"margin-bottom:10px;\">".wfMsg('smw_tsa_welcome')."</div>";
		global $smwgTripleStoreGraph;
		TSConnection::getConnector()->connect();
		try {
			$status = TSConnection::getConnector()->getStatus($smwgTripleStoreGraph);
		} catch(Exception $e) {
				
			// if no connection could be created
			$html .= "<div style=\"color:red;font-weight:bold;\">".wfMsg('smw_tsa_couldnotconnect')."</div>".wfMsg('smw_tsa_addtoconfig').
        	"<pre>".
        	"\$smwgWebserviceEndpoint = &lt;IP and port of TSC webservice endpoint&gt;\n\nExample:\n\n".
            "\$smwgWebserviceEndpoint = \"localhost:8080\";</pre>".
			wfMsg('smw_tsa_addtoconfig2')." <pre>enableSMWHalo('SMWHaloStore2', 'SMWTripleStore', 'http://mywiki');</pre>".
			wfMsg('smw_tsa_addtoconfig3');
			$smwForumLink = '<a href="http://smwforum.ontoprise.com/smwforum/index.php/Help:Installing_the_Basic_Triplestore_manually">SMW-Forum</a>';
			$html .= "<br><b>".wfMsg('smw_tsa_addtoconfig4', $smwForumLink)."</b>";
			$wgOut->addHTML($html);
			return;
				
		}
			
		// normal
		$html .= "<h2>".wfMsg('smw_tsa_driverinfo')."</h2>".$status['driverInfo']."";
		$html .= "<h2>".wfMsg('smw_tsa_tscinfo')."</h2>";
		$html .= wfMsg('smw_tsa_tscversion').": ".$status['tscversion'];
		// show warning when rule support is missing or defined although it is not available.
		if (in_array('RULES', $status['features']) && (!isset($smwgEnableObjectLogicRules) || $smwgEnableObjectLogicRules === false)) $html .= "<div style=\"color:red;font-weight:bold;\">".
		wfMsg('smw_tsa_rulesupport')."</div>";
		if (!in_array('RULES', $status['features']) && $smwgEnableObjectLogicRules === true) $html .= "<div style=\"color:red;font-weight:bold;\">".
		wfMsg('smw_tsa_norulesupport')."</div>";

		$html .= "<h2>".wfMsg('smw_tsa_status')."</h2>";
		if ($status['isInitialized'] == true) {
			$html .= "<div style=\"color:green;font-weight:bold;\">".wfMsg('smw_tsa_wikiconfigured', (is_array($smwgWebserviceEndpoint) ? implode(", ", $smwgWebserviceEndpoint) : $smwgWebserviceEndpoint))."</div>";
			$tsaPage = Title::newFromText("TSA", NS_SPECIAL);
			$html .= "<br><form><input name=\"init\" type=\"submit\" value=\"".wfMsg('smw_tsa_reinitialize')."\"/><input name=\"title\" type=\"hidden\" value=\"".$tsaPage->getPrefixedDBkey()."\"/></form>";
		} else {
			$html .= "<div style=\"color:red;font-weight:bold;\">".wfMsg('smw_tsa_notinitalized')."</div>".wfMsg('smw_tsa_pressthebutton');
			$tsaPage = Title::newFromText("TSA", NS_SPECIAL);
			$html .= "<br><form><input name=\"init\" type=\"submit\" value=\"".wfMsg('smw_tsa_initialize')."\"/><input name=\"title\" type=\"hidden\" value=\"".$tsaPage->getPrefixedDBkey()."\"/></form>";
		}
		$wgOut->addHTML($html);
	}


}

