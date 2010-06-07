<?php
/**
 * @file
 * @ingroup SemanticRules
 *
 * Provides access to TSC rule endpoint
 *
 * @author: Kai Kuehn / ontoprise / 2010
 *
 */

class SRRuleEndpoint {
    static private $_client;

    static private $instance = NULL;

    // implicitly set localhost if no messagebroker was defined.
    static public function getInstance() {
        global $wgServer, $wgScript, $smwgWebserviceEndpoint, $smwgWebserviceUser, $smwgWebservicePassword, $smwgDeployVersion, $smwgWebserviceProtocol;

        if (self::$instance === NULL) {
            self::$instance = new self;
            if (isset($smwgWebserviceProtocol) && strtolower($smwgWebserviceProtocol) === 'rest') {
                list($host, $port) = explode(":", $smwgWebserviceEndpoint);
                $credentials = isset($smwgWebserviceUser) ? $smwgWebserviceUser.":".$smwgWebservicePassword : "";
                self::$_client = new RESTWebserviceConnector($host, $port, "ruleparsing", $credentials);
            } else {
                trigger_error("SOAP endpoints are no more supported.");
            }
        }
        return self::$instance;
    }

    public function getRootRules() {
        global $smwgWebserviceProtocol;
        if (isset($smwgWebserviceProtocol) && strtolower($smwgWebserviceProtocol) === 'rest') {
            $payload = "";
            list($header, $status, $res) = self::$_client->send($payload, "/getRootRules");
            if ($status != 200) {
            	return "error:$status";
            }
        } else {
            trigger_error("SOAP endpoints are no more supported.");
        }
        
        $xml = '<result>';
        $dom = simplexml_load_string($res);
        if($dom === FALSE) return "error:XML-parsing wrong";
       
       
        foreach($dom->children() as $rule) {
        	$title_url = htmlspecialchars((string) $rule->attributes()->id);
        	$xml .= '<ruleTreeElement title_url="'.$title_url.'" id="ID_171494c0d0f3f87d2f1"></ruleTreeElement>';
        
        }
        $xml .= '</result>';
        return $xml;
    }

    /**
     * Parses an ObjectLogic rule and returns the corresponding RuleObject.
     *
     * @param string $ruleid
     *      The id of the rule. If it is <null>, $flogicrule must contain the
     *      id in for of: RULE #id:flogic text
     * @param string $flogicrule
     *      The text of the rule.
     * @return SMWRuleObject
     *      The rule object contains the parsed literals of the rule.
     */
    public function parseOblRule($ruleid, $flogicrule) {
        $_flogicstring = $flogicrule;

        // initFlogic returns the sessionId
        $parseflogicinput=array();
        //      $parseflogicinput['flogicString'] =
        $parseflogicinput = $_flogicstring;

        global $smwgWebserviceProtocol;
        if (isset($smwgWebserviceProtocol) && strtolower($smwgWebserviceProtocol) === 'rest') {
            $payload = "ruleText=".urlencode($parseflogicinput);
            list($header, $status, $res) = self::$_client->send($payload, "/parserule");
            $_parsedstring = $res;
        } else {
            trigger_error("SOAP endpoints are no more supported.");
        }

        $_ruleObject = new SMWRuleObject();
        $_ruleObject->setAxiomId($ruleid);
        return $_ruleObject->parseRuleObject(simplexml_load_string($_parsedstring));
    }
}

