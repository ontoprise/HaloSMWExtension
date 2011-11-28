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
 * @ingroup SemanticRules
 *
 * Provides access to TSC rule endpoint
 *
 * @author: Kai Kuehn
 *
 */

class SRRuleEndpoint {
	static private $_client;

	static private $instance = NULL;

	// implicitly set localhost if no messagebroker was defined.
	static public function getInstance() {
		global $wgServer, $wgScript, $smwgHaloWebserviceEndpoint, $smwgHaloWebserviceUser, $smwgHaloWebservicePassword, $smwgDeployVersion, $smwgWebserviceProtocol;

		if (self::$instance === NULL) {
			self::$instance = new self;
			if (isset($smwgWebserviceProtocol) && strtolower($smwgWebserviceProtocol) === 'rest') {
				list($host, $port) = explode(":", $smwgHaloWebserviceEndpoint);
				$credentials = isset($smwgHaloWebserviceUser) ? $smwgHaloWebserviceUser.":".$smwgHaloWebservicePassword : "";
				global $tscgIP;
				require_once( "$tscgIP/includes/triplestore_client/TSC_RESTWebserviceConnector.php" );
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
	 * @param string urifragment Fragment which must appear in the rule URI
	 * 
	 * @return string XML
	 */
	public function getRootRules($urifragment) {
		global $smwgWebserviceProtocol, $smwgHaloTripleStoreGraph;
        $urifragment = urlencode($urifragment);
        
		$payload = "graph=".urlencode($smwgHaloTripleStoreGraph);
		$payload .= "&urifragment=".urlencode($urifragment);
		list($header, $status, $res) = self::$_client->send($payload, "/getRootRules");

		$response = new AjaxResponse($this->encapsulateTreeElementAsXML($res));
		$response->setContentType( "application/xml" );
		$response->setResponseCode($status);

		return $response;
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
	public function getDependantRules($ruleID) {
		$ruleID = urlencode($ruleID);
		global $smwgWebserviceProtocol, $smwgHaloTripleStoreGraph;

		$payload = "graph=".urlencode($smwgHaloTripleStoreGraph)."&ruleID=$ruleID";
		list($header, $status, $res) = self::$_client->send($payload, "/getDependantRules");

		$response = new AjaxResponse($this->encapsulateTreeElementAsXML($res));
		$response->setContentType( "application/xml" );
		$response->setResponseCode($status);

		return $response;
	}

	/**
	 * Returns rules which define the given entities.
	 *
	 * @param string [] resources 
	 *
	 * @note: not intended to be called via ajax. No serialization yet available.
	 * 
	 * @return XML
	 */
	public function getDefiningRules($resourceURIs) {
		$resources = "";
		foreach($resourceURIs as $r) $resources .= "&resource=".urlencode($r);
		global $smwgWebserviceProtocol, $smwgHaloTripleStoreGraph;

		$payload = "graph=".urlencode($smwgHaloTripleStoreGraph).$resources;
		list($header, $status, $res) = self::$_client->send($payload, "/getDefiningRules");

		$attachMap = array();
		$dom = simplexml_load_string($res);
		if($dom === FALSE) return $attachMap;
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
	 * @param string $ruleID
	 * 
	 * @return XML
	 */
	public function getRule($ruleID) {
		$ruleID = urlencode($ruleID);
		global $smwgWebserviceProtocol, $smwgHaloTripleStoreGraph;

		$payload = "graph=".urlencode($smwgHaloTripleStoreGraph)."&ruleID=$ruleID";
		list($header, $status, $res) = self::$_client->send($payload, "/getRule");

		$response = new AjaxResponse($this->encapsulateMetadataAsXML($res));
		$response->setContentType( "application/xml" );
		$response->setResponseCode($status);

		return $response;

	}

	/**
	 * Returns rule containing the string given as filter in a resource or literal.
	 *
	 * @param string $filter 
	 * @param boolean asTree
	 * @param $resultformat
	 * @param $ajaxCall (if false, do not return AjaxResponse object but simple text)
	 * 
	 * @return XML
	 */
	public function searchForRulesByFragment($filter, $asTree, $resultformat = "xml", $ajaxCall = true) {
		$filter = urlencode($filter);
		$asTree = urlencode($asTree);
		global $smwgWebserviceProtocol, $smwgHaloTripleStoreGraph;

		$payload = "graph=".urlencode($smwgHaloTripleStoreGraph)."&fragment=$filter&asTree=$asTree";
		list($header, $status, $res) = self::$_client->send($payload, "/searchForRulesByFragment");

		if (!$ajaxCall) {
			return $resultformat == 'xml' ? $this->encapsulateTreeElementAsXML($res, true) : $this->encapsulateRuleWidget($res);
		}

		$response = new AjaxResponse($resultformat == 'xml' ? $this->encapsulateTreeElementAsXML($res, true) : $this->encapsulateRuleWidget($res));
		$response->setContentType( "application/xml" );
		$response->setResponseCode($status);

		return $response;

	}

	/**
	 * Serialize the given rules in several flavors: Easyreadible and stylized english.
	 *
	 * @param JSON $ruleTuples
	 *              
	 *             [ { ruletext : "...", ruleID: "...", native : true/false }, ... ]
	 * 
	 * @return XML serialized rules
	 */
	public function serializeRules($ruleTuples) {
		$encodedRuleTuples = array();
		$ruleTuplesObjects = json_decode($ruleTuples);
		foreach($ruleTuplesObjects as $rt) $encodedRuleTuples[] = urlencode(json_encode($rt));
		$ruleIDs = implode("&ruletuple=",$encodedRuleTuples);
		global $smwgWebserviceProtocol, $smwgHaloTripleStoreGraph;

		$payload = "graph=".urlencode($smwgHaloTripleStoreGraph)."&ruletuple=".$ruleIDs;
		list($header, $status, $res) = self::$_client->send($payload, "/serializeRules");

		$response = new AjaxResponse($res);
		$response->setContentType( "application/text" );
		$response->setResponseCode($status);

		return $response;

	}

	/**
	 * Translates the URI mappings in a rule.
	 *
	 * @param string $ruletext
	 * @param string ontology URI
	 */
	public function translateRuleURIs($ruletext, $uri, $ajaxCall = true) {
		global $smwgHaloTripleStoreGraph;
		$payload = "ruletext=".urlencode($ruletext)."&graph=".urlencode($uri);
		list($header, $status, $res) = self::$_client->send($payload, "/translateRuleURIs");

		if ($ajaxCall) {
			$response = new AjaxResponse($res);
			$response->setContentType( "application/text" );
			$response->setResponseCode($status);
		} else {
			return $res;
		}

		return $response;

	}

	/**
	 * Parses an ObjectLogic rule and returns the corresponding RuleObject.
	 *
	 * @param string $ruleid
	 *      The id of the rule. If it is <null>, $flogicrule must contain the
	 *      id in for of: RULE #id:flogic text
	 * @param string $oblrule
	 *      The text of the rule.
	 * @param string ajax call or directly called
	 * @return SMWRuleObject
	 *      The rule object contains the parsed literals of the rule.
	 */
	public function parseOblRule($ruleid, $oblrule, $ajaxCall = true) {

		$payload = "ruleText=".urlencode($oblrule);
		list($header, $status, $res) = self::$_client->send($payload, "/parserule");

		if ($status != 200) {
			throw new Exception("Can not parse rule: ".$res, $status);
		}
		$_parsedstring = $res;

		$_ruleObject = new SMWRuleObject();
		$_ruleObject->setAxiomId($ruleid);

		$parsedRuleXML = $_ruleObject->parseRuleObject(simplexml_load_string($_parsedstring));

		if ($ajaxCall) {
			$response = new AjaxResponse($parsedRuleXML);
			$response->setContentType( "application/xml" );
			$response->setResponseCode($status);
			return $response;

		} else {
			return $parsedRuleXML;
		}

	}

	private function encapsulateRuleWidget($resultXML) {

		$html = '';
		$dom = simplexml_load_string($resultXML);
		if($dom === FALSE) return "error:XML-parsing wrong";

		foreach($dom->children() as $rule) {
			if ($rule->getName() != 'rule') continue;
			$ruleURI = (string) $rule->attributes()->id;
			$active = (string) $rule->attributes()->active;
			$native =  (string) $rule->attributes()->native;
			$ruleText = (string) $rule[0];
			$easyreadible = isset($rule->children()->easyreadible) ? (string) $rule->children()->easyreadible[0] : "";
			$stylizedEnglish = isset($rule->children()->easyreadible) ? (string) $rule->children()->stylizedenglish[0] : "";

			list($containingPageURI, $rulename) = explode("$$", $ruleURI);
			$containingPageTitle = $this->getWikiTitleFromURI($containingPageURI);

			$rw = new SRRuleWidget($ruleURI, $ruleText, $active == "true", $native == "true");
			$html .= $rw->asHTML();

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
		TSNamespaces::getInstance(); // assure namespaces are initialized
		$allNamespaces = TSNamespaces::getAllNamespaces();

		foreach ($allNamespaces as $nsIndsex => $ns) {
			if (stripos($uri, $ns) === 0) {
				$lastIndexOfSlash = strrpos($uri, "/");
				$local = substr($uri, $lastIndexOfSlash+1);
				$title = Title::newFromText($local, $nsIndsex);
				return $title;
			}
		}

		$startNS = strlen(TSNamespaces::$UNKNOWN_NS);
		$length = strrpos($uri, "/") - $startNS;
		$ns = intval(substr($uri, $startNS, $length));

		$local = substr($uri, strrpos($uri, "/")+1);

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

}

