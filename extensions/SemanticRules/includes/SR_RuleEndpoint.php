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
	 * @param ruleID
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

	/**
	 * Returns rules which define the given entities.
	 *
	 * @param resources (as array)
	 */
	public function getDefiningRules($params) {
		$resources = "";
		foreach($params as $r) $resources .= "&resource=".urlencode($r);
		global $smwgWebserviceProtocol, $smwgTripleStoreGraph;

		$payload = "graph=$smwgTripleStoreGraph$resources";
		list($header, $status, $res) = self::$_client->send($payload, "/getDefiningRules");
		if ($status != 200) {
			return "error:$status";
		}
		$attachMap = array();
		$dom = simplexml_load_string($res);
		if($dom === FALSE) return "error:XML-parsing wrong";
		foreach($dom->children() as $resource) {
			$resourceURI = (string) $resource->attributes()->id;
			$wikiName = $this->getWikiTitleFromURI($resourceURI)->getPrefixedDBkey();
			$rules = array();
			foreach($resource->children() as $rule) {
				$ruleURI = (string) $rule->attributes()->id;
				$rules[] = $ruleURI;
			}
			$attachMap[$wikiName] = $rules;
		}


		return $attachMap;
	}

	/**
	 * Returns rule metadata.
	 *
	 * @param $ruleID
	 */
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

	public function searchForRulesByFragment($params, $resultformat = "xml") {
		$filter = $params[0];
		global $smwgWebserviceProtocol, $smwgTripleStoreGraph;

		$payload = "graph=$smwgTripleStoreGraph&fragment=$filter";
		list($header, $status, $res) = self::$_client->send($payload, "/searchForRulesByFragment");
		if ($status != 200) {
			return "error:$status";
		}

		return $resultformat == 'xml' ? $this->encapsulateTreeElementAsXML($res, true) : $this->encapsulateRuleWidget($res);
	}

	public function serializeRules($params) {
		$ruleIDs = implode("&ruleID=",$params);
		global $smwgWebserviceProtocol, $smwgTripleStoreGraph;

		$payload = "graph=$smwgTripleStoreGraph&ruleID=$ruleIDs";
		list($header, $status, $res) = self::$_client->send($payload, "/serializeRules");
		if ($status != 200) {
			return "error:$status";
		}

		return $res;
	}

	private function encapsulateRuleWidget($resultXML) {
		$i = 0;
		$html = '';
		$dom = simplexml_load_string($resultXML);
		if($dom === FALSE) return "error:XML-parsing wrong";

		foreach($dom->children() as $rule) {
			if ($rule->getName() != 'rule') continue;
			$ruleURI = (string) $rule->attributes()->id;
			$ruleText = (string) $rule[0];
			$easyreadible = isset($rule->children()->easyreadible) ? (string) $rule->children()->easyreadible[0] : "";
            $stylizedEnglish = isset($rule->children()->easyreadible) ? (string) $rule->children()->stylizedenglish[0] : "";

            list($containingPageURI, $rulename) = explode("$$", $ruleURI);
            $containingPageTitle = $this->getWikiTitleFromURI($containingPageURI);
			$html .= '<div id="rule_content_'.$i.'" ruleID="'.htmlspecialchars($ruleURI).'" class="ruleWidget"><a style="margin-left: 5px;" href="'.htmlspecialchars($containingPageTitle->getFullURL()).'">'.htmlspecialchars($rulename).'</a> | '.wfMsg('sr_ruleselector').'<select style="margin-top: 5px;" name="rule_content_selector'.$i.'" onchange="sr_rulewidget.selectMode(event)"><option mode="easyreadible">'.wfMsg('sr_easyreadible').'</option><option mode="stylized">'.wfMsg('sr_stylizedenglish').'</option></select> '. // tab container
                         '<div id="rule_content_'.$i.'_easyreadible" class="ruleSerialization">'.htmlspecialchars($easyreadible).'</div>'. // tab 1
                         '<div id="rule_content_'.$i.'_stylized" class="ruleSerialization" style="display:none;">'.htmlspecialchars($stylizedEnglish).'</div>'.
                     '</div>'; // tab 2
			$i++;
		}

		$html .= '';
		return $html;
	}

	/**
	 * Transforms XML format coming from TSC rule endpoint to transformable XML format
	 * used by OntologyBrowser.
	 *
	 * @return string XML
	 */
	private function encapsulateTreeElementAsXML($resultXML, $expanded = false) {
		$id = uniqid (rand());
		$counter=0;
		$xml = '<result>';
		$dom = simplexml_load_string($resultXML);
		if($dom === FALSE) return "error:XML-parsing wrong";

		$this->_encapsulateTreeElementAsXML($dom, $id, $counter, $expanded, $xml);
		$xml .= '</result>';
		return $xml;
	}

	private function _encapsulateTreeElementAsXML($node, $id, $counter, $expanded, & $xml) {
		foreach($node->children() as $rule) {
			if ($rule->getName() != 'rule') continue;
			$ruleURI = (string) $rule->attributes()->id;
			$ruleText = (string) $rule[0];
			// $$ separates page URI containing the rule from rule name
			$help = explode("$$", $ruleURI);
			$pageURI = $help[0];
			$ruleName = $help[1];
			$containingPageAsWikiText = $this->getWikiTitleFromURI($pageURI)->getPrefixedDBkey();

			$active_att = ((string) $rule->attributes()->active == 'false') ? 'inactive="true"':'';
			$leaf_att = ((string) $rule->attributes()->leaf == 'true') ? 'isLeaf="true"':'';
			$dirty_att = ((string) $rule->attributes()->dirty == 'true') ? 'isDirty="true"':'';
			$expanded_att = $expanded ? 'expanded="true"' : "";
			$uid = $id.($counter++);
			$xml .= '<ruleTreeElement '.$leaf_att.' '.$expanded_att.' '.$active_att.' '.$dirty_att.' title="'.htmlspecialchars($ruleName). // displayed name
                     '" title_url="'.htmlspecialchars($ruleURI).                         // full URI of rule
                     '" containing_page="'.htmlspecialchars($containingPageAsWikiText).  // containing page
                     '" id="'.$uid.'"><![CDATA['.$ruleText.']]>';
			$this->_encapsulateTreeElementAsXML($rule, $id, $counter, $expanded, $xml);
			$xml .= '</ruleTreeElement>';
		}

	}

	private function getWikiTitleFromURI($uri) {
		new TSNamespaces(); // assure namespaces are initialized
		$allNamespaces = TSNamespaces::getAllNamespaces();

		foreach ($allNamespaces as $nsIndsex => $ns) {
			if (stripos($uri, $ns) === 0) {
				$help = explode('#', $uri);
				$local = $help[1];
				$title = Title::newFromText($local, $nsIndsex);
				return $title;
			}
		}

		$startNS = strlen(TSNamespaces::$UNKNOWN_NS);
		$length = strpos($uri, "#") - $startNS;
		$ns = intval(substr($uri, $startNS, $length));

		$local = substr($uri, strpos($sv, "#")+1);

		$title = Title::newFromText($local, $ns);
		return $title;
	}

	private function encapsulateMetadataAsXML($resultXML) {
		$id = uniqid (rand());
		$counter=0;
		$xml = '';
		$dom = simplexml_load_string($resultXML);
		if($dom === FALSE) return "error:XML-parsing wrong";

		foreach($dom->children() as $rule) {
			$ruleURI = (string) $rule->attributes()->id;
			$is_active = (string) $rule->attributes()->active;
			$is_native = (string) $rule->attributes()->native;
			$type = (string) $rule->attributes()->type;
			$ruleText = (string) $rule[0];
			// $$ separates page URI containing the rule from rule name
			$help = explode("$$", $ruleURI);
			$pageURI = $help[0];
			$ruleName = $help[1];
			$containingPageAsWikiText = $this->getWikiTitleFromURI($pageURI)->getPrefixedDBkey();

			$defines = "";
			foreach($rule->children()->defining as $defining) {
				$d = (string) $defining;
				$defines .= "<defining>". $this->getWikiTitleFromURI($d)->getPrefixedDBkey()."</defining>";
			}

			$uses = "";
			foreach($rule->children()->using as $using) {
				$u = (string) $using;
				$uses .= "<using>". $this->getWikiTitleFromURI($u)->getPrefixedDBkey()."</using>";
			}

			$easyreadible = isset($rule->children()->easyreadible) ? (string) $rule->children()->easyreadible[0] : NULL;
			$stylizedEnglish = isset($rule->children()->easyreadible) ? (string) $rule->children()->stylizedenglish[0] : NULL;

			$easyreadibleText = !is_null($easyreadible) ? '<easyreadible><![CDATA['.$easyreadible.']]></easyreadible>' : "";
			$stylizedEnglishText = !is_null($stylizedEnglish) ? '<stylizedenglish><![CDATA['.$stylizedEnglish.']]></stylizedenglish>' : "";

			$uid = $id.($counter++);
			$xml .= '<ruleMetadata title="'.htmlspecialchars($ruleName). // displayed name
                     '" title_url="'.htmlspecialchars($ruleURI).                         // full URI of rule
                     '" containing_page="'.htmlspecialchars($containingPageAsWikiText).  // containing page
                     '" type="'.$type.'" id="'.$uid.'" active="'.$is_active.'" native="'.$is_native.'">'.$defines.$uses.
                     '<ruletext><![CDATA['.$ruleText.']]></ruletext>'.
			$easyreadibleText.
			$stylizedEnglishText.
			         '</ruleMetadata>';

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

