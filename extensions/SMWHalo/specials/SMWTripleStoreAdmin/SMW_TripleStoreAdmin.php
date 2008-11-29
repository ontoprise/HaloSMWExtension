<?php
/*
 * Created on 12.03.2007
 *
 * Author: kai
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
    
    public function execute() {
        global $wgRequest, $wgOut, $smwgMessageBroker, $wgUser, $smwgEnableFlogicRules;
        $wgOut->setPageTitle(wfMsg('tsa'));
        $html = "";
        if ($wgRequest->getVal('init') != NULL) {
        	// after init
        	smwfGetStore()->setup(false);
        	$html .= $wgUser->getSkin()->makeKnownLinkObj(Title::newFromText("TSA", NS_SPECIAL), wfMsg('smw_tsa_waitsoemtime'));
            $wgOut->addHTML($html);
            return;
        }
        $html .= "<div style=\"margin-bottom:10px;\">".wfMsg('smw_tsa_welcome')."</div>";
        $status = $this->getStatus();
        if ($status === false) {
        	// if no connection could be created
        	$html .= "<div style=\"color:red;font-weight:bold;\">".wfMsg('smw_tsa_couldnotconnect')."</div>".wfMsg('smw_tsa_addtoconfig').
        	"<pre>\$smwgMessageBroker  = &lt;IP of messagebroker&gt;\n".
        	"\$smwgWebserviceEndpoint = &lt;IP and port of SPARQL endpoint&gt;\n\nExample:\n\n\$smwgMessageBroker  = \"localhost\";\n".
            "\$smwgWebserviceEndpoint = \"localhost:8080\";</pre>".
            wfMsg('smw_tsa_addtoconfig2')." <pre>enableSMWHalo('SMWHaloStore2', 'SMWTripleStore');</pre>".
        	wfMsg('smw_tsa_addtoconfig3')." <pre>enabaleSemantics('http://mywiki', true);</pre>";
        	$wgOut->addHTML($html);
        	return;
        }
       
        // normal 
        $html .= "<h2>".wfMsg('smw_tsa_driverinfo')."</h2>".$status->driverInfo."";
        
        // show warning when rule support is missing or defined although it is not available.
        if ($status->ruleSupport && (!isset($smwgEnableFlogicRules) || $smwgEnableFlogicRules === false)) $html .= "<div style=\"color:red;font-weight:bold;\">".
        wfMsg('smw_tsa_rulesupport')."</div>";
        if (!$status->ruleSupport && $smwgEnableFlogicRules === true) $html .= "<div style=\"color:red;font-weight:bold;\">".
        wfMsg('smw_tsa_norulesupport')."</div>";
        
        $html .= "<h2>".wfMsg('smw_tsa_status')."</h2>";
        if ($status->isInitialized == true) {
        	 $html .= "<div style=\"color:green;font-weight:bold;\">".wfMsg('smw_tsa_wikiconfigured', $smwgMessageBroker)."</div>";
        } else {
        	 $html .= "<div style=\"color:red;font-weight:bold;\">".wfMsg('smw_tsa_notinitalized')."</div>".wfMsg('smw_tsa_pressthebutton');
        	 $tsaPage = Title::newFromText("TSA", NS_SPECIAL);
        	 $html .= "<br><form><input name=\"init\" type=\"submit\" value=\"".wfMsg('smw_tsa_initialize')."\"/><input name=\"title\" type=\"hidden\" value=\"".$tsaPage->getPrefixedDBkey()."\"/></form>";
        }
        $wgOut->addHTML($html);
    }
    
    /**
     * Calls the webservice which gives status information about the triple store connector.
     *
     * @return String (HTML)
     */
    private function getStatus() {
    	global $wgServer,$wgScript,$smwgNamespace, $smwgWebserviceUser, $smwgWebServicePassword;
        ini_set("soap.wsdl_cache_enabled", "0");  //set for debugging
        $client = new SoapClient("$wgServer$wgScript?action=ajax&rs=smwf_ws_getWSDL&rsargs[]=get_manage", array('connection_timeout' => 4, 'login'=>$smwgWebserviceUser, 'password'=>$smwgWebServicePassword));
          try {
                global $smwgNamespace;
                $statusJSON = $client->getTripleStoreStatus($smwgNamespace);
                $json = new Services_JSON();
                $status = $json->decode($statusJSON);

            } catch(Exception $e) {
                return false;
            }
        return $status;
    }
}
?>
