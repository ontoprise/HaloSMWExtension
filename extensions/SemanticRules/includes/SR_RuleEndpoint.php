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

	/**
	 * Return root rules by accessing the TSC rule endpoint. Encapsulates the results
	 * in a XML structure which is transformable by ruleTree.xslt.
	 *
	 * @return string XML
	 */
	public function getRootRules() {
		global $smwgWebserviceProtocol, $smwgTripleStoreGraph;

		$payload = "graph=$smwgTripleStoreGraph";
		list($header, $status, $res) = self::$_client->send($payload, "/getRootRules");
		if ($status != 200) {
			return "error:$status";
		}

		return $this->encapsulateTreeElementAsXML($res);
	}

	/**
	 * Return depdendant rules by accessing the TSC rule endpoint. That means rules which uses
	 * a property in the head which the given rule uses in the body.
	 *
	 * Encapsulates the results in a XML structure which is transformable by ruleTree.xslt.
	 *
	 * @param ruleID (as array)
	 * @return string XML
	 */
	public function getDependantRules($params) {
		$ruleID = $params[0];
		global $smwgWebserviceProtocol, $smwgTripleStoreGraph;

		$payload = "graph=$smwgTripleStoreGraph&ruleID=$ruleID";
		list($header, $status, $res) = self::$_client->send($payload, "/getDependantRules");
		if ($status != 200) {
			return "error:$status";
		}


		return $this->encapsulateTreeElementAsXML($res);
	}

	public function getRule($params) {
		$ruleID = $params[0];
		global $smwgWebserviceProtocol, $smwgTripleStoreGraph;

		$payload = "graph=$smwgTripleStoreGraph&ruleID=$ruleID";
		list($header, $status, $res) = self::$_client->send($payload, "/getRule");
		if ($status != 200) {
			return "error:$status";
		}
		 
		return $this->encapsulateMetadataAsXML($res);
	}

	/**
	 * Transforms XML format coming from TSC rule endpoint to transformable XML format
	 * used by OntologyBrowser.
	 *
	 * @return string XML
	 */
	private function encapsulateTreeElementAsXML($resultXML) {
		$id = uniqid (rand());
		$counter=0;
		$xml = '<result>';
		$dom = simplexml_load_string($resultXML);
		if($dom === FALSE) return "error:XML-parsing wrong";

		foreach($dom->children() as $rule) {
			$ruleURL = (string) $rule->attributes()->id;
			$ruleName = substr($ruleURL, strpos($ruleURL, "#")+1);
			$title_url = htmlspecialchars($ruleURL);
			$is_leaf = ((string) $rule->attributes()->leaf == 'true') ? 'isLeaf="true"':'';
			$uid = $id.($counter++);
			$xml .= '<ruleTreeElement '.$is_leaf.' title="'.$ruleName.'" title_url="'.$title_url.'" id="'.$uid.'"></ruleTreeElement>';

		}
		$xml .= '</result>';
		return $xml;
	}

	private function encapsulateMetadataAsXML($resultXML) {
		$id = uniqid (rand());
		$counter=0;
		$xml = '';
		$dom = simplexml_load_string($resultXML);
		if($dom === FALSE) return "error:XML-parsing wrong";

		foreach($dom->children() as $rule) {
			$ruleURL = (string) $rule->attributes()->id;
			$ruleName = substr($ruleURL, strpos($ruleURL, "#")+1);
			$title_url = htmlspecialchars($ruleURL);
			$is_leaf = ((string) $rule->attributes()->leaf == 'true') ? 'isLeaf="true"':'';
			$uid = $id.($counter++);
			$xml .= '<ruleMetadata title="'.$ruleName.'" title_url="'.$title_url.'"></ruleMetadata>';

		}
		$xml .= '';
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


		$payload = "ruleText=".urlencode($parseflogicinput);
		list($header, $status, $res) = self::$_client->send($payload, "/parserule");
		$_parsedstring = $res;


		$_ruleObject = new SMWRuleObject();
		$_ruleObject->setAxiomId($ruleid);
		return $_ruleObject->parseRuleObject(simplexml_load_string($_parsedstring));
	}


}

