<?php
global $smwgIP, $smwgHaloIP;
require_once( "$smwgIP/includes/storage/SMW_Store.php" );
require_once( "$smwgHaloIP/includes/storage/stompclient/Stomp.php" );


/**
 * Triple store connector class.
 *
 * This class is a wrapper around the default SMWStore class. It delegates all
 * read operations to the default implementation. Write operation, namely:
 *
 *  1. updateData
 *  2. deleteSubject
 *  3. changeTitle
 *  4. setup
 *  5. drop
 *
 * are delegated too, but also sent to a MessageBroker supporting the Stomp protocol.
 * All commands are written in the SPARUL(1) syntax.
 *
 * SPARQL queries are sent to the triple store via webservice (SPARQL endpoint). ASK
 * queries are delgated to default SMWStore.
 *
 * (1) refer to http://jena.hpl.hp.com/~afs/SPARQL-Update.html
 *
 * Configuration in LocalSettings.php:
 *
 *  $smwgMessageBroker: The name or IP of the message broker
 *  $smwgWebserviceEndpoint: The name or IP of the SPARQL endpoint (with port if not 80)
 *
 * @author: Kai
 */

class SMWTripleStore extends SMWStore {

	// W3C namespaces
	protected static $RDF_NS = "http://www.w3.org/1999/02/22-rdf-syntax-ns#";
	protected static $OWL_NS = "http://www.w3.org/2002/07/owl#";
	protected static $RDFS_NS = "http://www.w3.org/2000/01/rdf-schema#";
	protected static $XSD_NS = "http://www.w3.org/2001/XMLSchema#";

	// (pre-defined) namespaces for different page types
	protected static $CAT_NS;
	protected static $PROP_NS;
	protected static $INST_NS;
	protected static $TYPE_NS;
	protected static $IMAGE_NS;
	protected static $HELP_NS;
	protected static $UNKNOWN_NS;

	// general namespace suffixes for different namespaces
	public static $CAT_NS_SUFFIX = "/category#";
	public static $PROP_NS_SUFFIX = "/property#";
	public static $INST_NS_SUFFIX = "/a#";
	public static $TYPE_NS_SUFFIX = "/type#";
	public static $IMAGE_NS_SUFFIX = "/image#";
	public static $HELP_NS_SUFFIX = "/help#";
	public static $UNKNOWN_NS_SUFFIX = "/ns_"; // only fragment. # is missing!

	// SPARQL-PREFIX statement with all pre-defined namespaces
	protected static $ALL_PREFIXES;

	public static $fullSemanticData;
	/**
	 * Creates and initializes Triple store connector.
	 *
	 * @param SMWStore $smwstore All calls are delegated to this implementation.
	 */
	function __construct() {
		global $smwgDefaultStore, $smwgBaseStore;
		$this->smwstore = new $smwgBaseStore;
		global $smwgTripleStoreGraph;

		// create default namespace prefixes
		self::$CAT_NS = $smwgTripleStoreGraph.self::$CAT_NS_SUFFIX;
		self::$PROP_NS = $smwgTripleStoreGraph.self::$PROP_NS_SUFFIX;
		self::$INST_NS = $smwgTripleStoreGraph.self::$INST_NS_SUFFIX;
		self::$TYPE_NS = $smwgTripleStoreGraph.self::$TYPE_NS_SUFFIX;
		self::$IMAGE_NS = $smwgTripleStoreGraph.self::$IMAGE_NS_SUFFIX;
		self::$HELP_NS = $smwgTripleStoreGraph.self::$HELP_NS_SUFFIX;
		self::$UNKNOWN_NS = $smwgTripleStoreGraph.self::$UNKNOWN_NS_SUFFIX;

		self::$ALL_PREFIXES = 'PREFIX xsd:<'.self::$XSD_NS.'> PREFIX owl:<'.self::$OWL_NS.'> PREFIX rdfs:<'.
		self::$RDFS_NS.'> PREFIX rdf:<'.self::$RDF_NS.'> PREFIX cat:<'.self::$CAT_NS.'> PREFIX prop:<'.
		self::$PROP_NS.'> PREFIX a:<'.self::$INST_NS.'> PREFIX type:<'.self::$TYPE_NS.'> PREFIX image:<'.self::$IMAGE_NS.'> PREFIX help:<'.self::$HELP_NS.'> ';
	}



	///// Reading methods /////
	// delegate to default implementation

	function getSemanticData($subject, $filter = false) {
		return $this->smwstore->getSemanticData($subject, $filter);
	}


	function getPropertyValues($subject, SMWPropertyValue $property, $requestoptions = NULL, $outputformat = '') {
		return $this->smwstore->getPropertyValues($subject, $property, $requestoptions, $outputformat);
	}

	function getPropertySubjects(SMWPropertyValue $property, $value, $requestoptions = NULL) {
		return $this->smwstore->getPropertySubjects($property, $value, $requestoptions);
	}

	function getAllPropertySubjects(SMWPropertyValue $property, $requestoptions = NULL) {
		return $this->smwstore->getAllPropertySubjects($property, $requestoptions);
	}

	function getProperties($subject, $requestoptions = NULL) {
		return $this->smwstore->getProperties($subject, $requestoptions);
	}

	function getInProperties(SMWDataValue $object, $requestoptions = NULL) {
		return $this->smwstore->getInProperties($object, $requestoptions);
	}

	function getSMWPropertyID(SMWPropertyValue $property) {
		return $this->smwstore->getSMWPropertyID($property);
	}

	///// Writing methods /////

	function deleteSubject(Title $subject) {
		$this->smwstore->deleteSubject($subject);
		$subj_ns = $this->getNSPrefix($subject->getNamespace());

		$unknownNSPrefixes = $this->getUnknownNamespacePrefixes($subj_ns);


		// clear rules
		global $smwgEnableFlogicRules;
		if (isset($smwgEnableFlogicRules)) {
			$old_rules = SMWRuleStore::getInstance()->getRules($subject->getArticleId());
			SMWRuleStore::getInstance()->clearRules($subject->getArticleId());
		}
		global $smwgMessageBroker, $smwgTripleStoreGraph;
		try {
			$con = new TSConnection();
			$sparulCommands = array();
			$sparulCommands[] = self::$ALL_PREFIXES.$unknownNSPrefixes."DELETE FROM <$smwgTripleStoreGraph> { $subj_ns:".$subject->getDBkey()." ?p ?o. }";
			if ($subject->getNamespace() == SMW_NS_PROPERTY) {
			 $sparulCommands[] = self::$ALL_PREFIXES.$unknownNSPrefixes."DELETE FROM <$smwgTripleStoreGraph> { ?s owl:onProperty ".$subj_ns.":".$subject->getDBkey().". }";
			}
			if (isset($smwgEnableFlogicRules)) {
				// delete old rules...
				foreach($old_rules as $ruleID) {
					$sparulCommands[] = "DELETE RULE $ruleID FROM <$smwgTripleStoreGraph>";
				}
			}
			$con->connect();
			$con->send("/topic/WIKI.TS.UPDATE", $sparulCommands);
			$con->disconnect();
		} catch(Exception $e) {

		}
	}

	function updateData(SMWSemanticData $data) {
		$this->smwstore->updateData($data);
			
		$triples = array();

		$subject = $data->getSubject();
		$subj_ns = $this->getNSPrefix($subject->getNamespace());
		$unknownNSPrefixes = "";
		$unknownNSPrefixes .= $this->getUnknownNamespacePrefixes($subj_ns);
			

		//properties
		foreach($data->getProperties() as $key => $property) {
			$propertyValueArray = $data->getPropertyValues($property);
			$triplesFromHook = array();
			wfRunHooks('TripleStorePropertyUpdate', array(& $data, & $property, & $propertyValueArray, & $triplesFromHook));
			if ($triplesFromHook === false || count($triplesFromHook) > 0) {
				$triples = is_array($triplesFromHook) ? array_merge($triples, $triplesFromHook) : $triples;
				continue; // do not process normal triple generation, if hook provides triples.
			}

			// handle properties with special semantics
			if ($property->getPropertyID() == "_TYPE") {
				// ingore. handeled by SMW_TS_SchemaContributor or SMW_TS_SimpleContributor
				continue;
			} elseif ($property->getPropertyID() == "_CONV") {
				// ingore. handeled by category section below
				global $smwgContLang;
				$specialProperties = $smwgContLang->getPropertyLabels();
				$conversionPropertyLabel = str_replace(" ","_",$specialProperties['_CONV']);
				if ( $subject->getNamespace() == SMW_NS_TYPE ) {
					foreach($propertyValueArray as $value) {
						// parse conversion annotation format
						$measures = explode(",", $value->getXSDValue());
							
						// parse linear factor followed by (first) unit
						$firstMeasure = reset($measures);
						$indexOfWhitespace = strpos($firstMeasure, " ");
						if ($indexOfWhitespace === false) continue; // not a valid measure, ignore
						$factor = trim(substr($firstMeasure, 0, $indexOfWhitespace));
						$unit = trim(substr($firstMeasure, $indexOfWhitespace));
						$triples[] = array("type:".$subject->getDBkey(), "prop:".$conversionPropertyLabel, "\"$factor $unit\"");
							
						// add all aliases for this conversion factor using the same factor
						$nextMeasure = next($measures);
						while($nextMeasure !== false) {
							$triples[] = array("type:".$subject->getDBkey(), "prop:".$conversionPropertyLabel, "\"$factor ".trim($nextMeasure)."\"");
							$nextMeasure = next($measures);
						}
							
					}
				}
				continue;
			}

			elseif ($property->getPropertyID() == "_INST") {
				// ingore. handeled by category section below
				continue;
			} elseif ($property->getPropertyID() == "_SUBC") {
				// ingore. handeled by category section below
				continue;
			} elseif ($property->getPropertyID() == "_REDI") {
				// ingore. handeled by redirect section below
				continue;
			} elseif ($property->getPropertyID() == "_SUBP") {
				if ( $subject->getNamespace() == SMW_NS_PROPERTY ) {
					foreach($propertyValueArray as $value) {
						$triples[] = array("prop:".$subject->getDBkey(), "rdfs:subPropertyOf", "prop:".$value->getDBkey());
					}

				}
				continue;
			}

			// there are other special properties which need not to be handled special
			// so they can be handled by the default machanism:
			foreach($propertyValueArray as $value) {
				if ($value->isValid()) {
					if ($value->getTypeID() == '_txt') {
						$triples[] = array($subj_ns.":".$subject->getDBkey(), "prop:".$property->getWikiPageValue()->getDBkey(), "\"".$this->escapeQuotes($value->getXSDValue())."\"^^xsd:string");

					} elseif ($value->getTypeID() == '_wpg') {
						$obj_ns = $this->getNSPrefix($value->getNamespace());
						$unknownNSPrefixes .= $this->getUnknownNamespacePrefixes($obj_ns);
						$triples[] = array($subj_ns.":".$subject->getDBkey(), "prop:".$property->getWikiPageValue()->getDBkey(), $obj_ns.":".$value->getDBkey());

					} elseif ($value->getTypeID() == '__nry') {
						continue; // do not add nary properties
					} else {
							
						if ($value->getUnit() != '') {
							$triples[] = array($subj_ns.":".$subject->getDBkey(), "prop:".$property->getWikiPageValue()->getDBkey(), "\"".$value->getXSDValue()." ".$value->getUnit()."\"^^xsd:unit");
						} else {
							if ($value->getXSDValue() != NULL) {
								$xsdType = WikiTypeToXSD::getXSDType($property->getPropertyTypeID());
								$triples[] = array($subj_ns.":".$subject->getDBkey(), "prop:".$property->getWikiPageValue()->getDBkey(), "\"".$this->escapeQuotes($value->getXSDValue())."\"^^$xsdType");
							} else if ($value->getNumericValue() != NULL) {
								$triples[] = array($subj_ns.":".$subject->getDBkey(), "prop:".$property->getWikiPageValue()->getDBkey(), "\"".$value->getNumericValue()."\"^^xsd:double");
							}
						}
							
					}
				}
			}



		}

		// categories
		$categories = self::$fullSemanticData->getCategories();
		if ($subject->getNamespace() == NS_CATEGORY) {
			foreach($categories as $c) {
				if ($c == NULL) continue;
				$triplesFromHook = array();
				wfRunHooks('TripleStoreCategoryUpdate', array(& $subject, & $c, & $triplesFromHook));
				if ($triplesFromHook === false || count($triplesFromHook) > 0) {
					$triples = is_array($triplesFromHook) ? array_merge($triples, $triplesFromHook) : $triples;
					continue;
				}
				$triples[] = array("cat:".$subject->getDBkey(), "rdfs:subClassOf", "cat:".$c->getDBkey());
			}
		} else {

			foreach($categories as $c) {
				if ($c == NULL) continue;
				$triplesFromHook = array();
				wfRunHooks('TripleStoreCategoryUpdate', array(& $subject, & $c, & $triplesFromHook));
				if ($triplesFromHook === false || count($triplesFromHook) > 0) {
					$triples = is_array($triplesFromHook) ? array_merge($triples, $triplesFromHook) : $triples;
					continue;
				}
				$triples[] = array($subj_ns.":".$subject->getDBkey(), "rdf:type", "cat:".$c->getDBkey());
			}
		}

		// rules
		global $smwgEnableFlogicRules;
		if (isset($smwgEnableFlogicRules)) {
			$new_rules = self::$fullSemanticData->getRules();
			$old_rules = SMWRuleStore::getInstance()->getRules($subject->getArticleId());
			SMWRuleStore::getInstance()->clearRules($subject->getArticleId());
			SMWRuleStore::getInstance()->addRules($subject->getArticleId(), $new_rules);
		}

		// redirects
		$redirects = self::$fullSemanticData->getRedirects();
			
		foreach($redirects as $r) {
			switch($subj_ns) {
				case SMW_NS_PROPERTY: $prop = "owl:equivalentProperty";
				case NS_CATEGORY: $prop = "owl:equivalentClass";
				case NS_MAIN: $prop = "owl:sameAs";
				default: continue;
			}
			$r_ns = $this->getNSPrefix($r->getNamespace());
			$unknownNSPrefixes .= $this->getUnknownNamespacePrefixes($r_ns);
			$triples[] = array($subj_ns.":".$subject->getDBkey(), $prop, $r_ns.":".$r->getDBkey());
		}
			
		// connect to MessageBroker and send commands
		global $smwgMessageBroker, $smwgTripleStoreGraph;
		try {
			$con = new TSConnection();
			$sparulCommands = array();
			$sparulCommands[] = self::$ALL_PREFIXES.$unknownNSPrefixes."DELETE FROM <$smwgTripleStoreGraph> { $subj_ns:".$subject->getDBkey()." ?p ?o. }";
			if ($subject->getNamespace() == SMW_NS_PROPERTY) {
				// delete all property constraints too
				$sparulCommands[] = self::$ALL_PREFIXES.$unknownNSPrefixes."DELETE FROM <$smwgTripleStoreGraph> { ?s owl:onProperty ".$subj_ns.":".$subject->getDBkey().". }";
			}
			$sparulCommands[] =  self::$ALL_PREFIXES.$unknownNSPrefixes."INSERT INTO <$smwgTripleStoreGraph> { ".$this->implodeTriples($triples)." }";

			if (isset($smwgEnableFlogicRules)) {
				// delete old rules...
				foreach($old_rules as $ruleID) {
					$sparulCommands[] = "DELETE RULE $ruleID FROM <$smwgTripleStoreGraph>";
				}
				// ...and add new
				foreach($new_rules as $ruleID => $ruleText) {
					$sparulCommands[] = "INSERT RULE $ruleID INTO <$smwgTripleStoreGraph> : \"".$this->escapeQuotes($ruleText)."\"";
				}
			}
			$con->connect();
			$con->send("/topic/WIKI.TS.UPDATE", $sparulCommands);
			$con->disconnect();
		} catch(Exception $e) {
			// print something??
		}
	}


	function changeTitle(Title $oldtitle, Title $newtitle, $pageid, $redirid=0) {
		$this->smwstore->changeTitle($oldtitle, $newtitle, $pageid, $redirid);
		$unknownNSPrefixes = "";
		$old_ns = $this->getNSPrefix($oldtitle->getNamespace());
		$unknownNSPrefixes .= $this->getUnknownNamespacePrefixes($old_ns);

		$new_ns = $this->getNSPrefix($newtitle->getNamespace());
		$unknownNSPrefixes .= $this->getUnknownNamespacePrefixes($new_ns);

		// update local rule store
		global $smwgEnableFlogicRules;
		if (isset($smwgEnableFlogicRules)) {
			SMWRuleStore::getInstance()->updateRules($redirid, $pageid);
		}

		// update triple store
		global $smwgMessageBroker, $smwgTripleStoreGraph;
		try {
			$con = new TSConnection();

			$sparulCommands = array();
			$sparulCommands[] = self::$ALL_PREFIXES.$unknownNSPrefixes."MODIFY <$smwgTripleStoreGraph> DELETE { $old_ns:".$oldtitle->getDBkey()." ?p ?o. } INSERT { $new_ns:".$newtitle->getDBkey()." ?p ?o. }";
			$sparulCommands[] = self::$ALL_PREFIXES.$unknownNSPrefixes."MODIFY <$smwgTripleStoreGraph> DELETE { ?s $old_ns:".$oldtitle->getDBkey()." ?o. } INSERT { ?s $new_ns:".$newtitle->getDBkey()." ?o. }";
			$sparulCommands[] = self::$ALL_PREFIXES.$unknownNSPrefixes."MODIFY <$smwgTripleStoreGraph> DELETE { ?s ?p $old_ns:".$oldtitle->getDBkey().". } INSERT { ?s ?p $new_ns:".$newtitle->getDBkey().". }";
			$con->connect();
			$con->send("/topic/WIKI.TS.UPDATE", $sparulCommands);
			$con->disconnect();
		} catch(Exception $e) {

		}
	}

	///// Query answering /////

	function getQueryResult(SMWQuery $query) {
		global $wgServer, $wgScript, $smwgWebserviceUser, $smwgWebservicePassword, $smwgDeployVersion;

		// handle only SPARQL queries and delegate all others
		if ($query instanceof SMWSPARQLQuery) {

			if (!isset($smwgDeployVersion) || !$smwgDeployVersion) ini_set("soap.wsdl_cache_enabled", "0");  //set for debugging
			$client = new SoapClient("$wgServer$wgScript?action=ajax&rs=smwf_ws_getWSDL&rsargs[]=get_sparql", array('login'=>$smwgWebserviceUser, 'password'=>$smwgWebservicePassword));

			try {
				global $smwgTripleStoreGraph;
				if (stripos(trim($query->getQueryString()), 'SELECT') === 0 || stripos(trim($query->getQueryString()), 'PREFIX') === 0) {
					// SPARQL, attach common prefixes
					$response = $client->query(self::$ALL_PREFIXES.$query->getQueryString(), $smwgTripleStoreGraph, $this->serializeParams($query));
				} else {

					// do not attach anything
					$response = $client->query($query->getQueryString(), $smwgTripleStoreGraph, $this->serializeParams($query));

				}

				$queryResult = $this->parseSPARQLXMLResult($query, $response);


			} catch(Exception $e) {
				//				var_dump($e);
				$sqr = new SMWQueryResult(array(), $query, false);
				$sqr->addErrors(array($e->getMessage()));
				return $sqr;
			}
			return $queryResult;

		} else {
			return $this->smwstore->getQueryResult($query);
		}
	}

	///// Special page functions /////
	// delegate to default implementation
	function getPropertiesSpecial($requestoptions = NULL) {
		return $this->smwstore->getPropertiesSpecial($requestoptions);
	}

	function getUnusedPropertiesSpecial($requestoptions = NULL) {
		return $this->smwstore->getUnusedPropertiesSpecial($requestoptions);
	}

	function getWantedPropertiesSpecial($requestoptions = NULL) {
		return $this->smwstore->getWantedPropertiesSpecial($requestoptions);
	}

	function getStatistics() {
		return $this->smwstore->getStatistics();
	}

	///// Setup store /////

	function setup($verbose = true) {
		$this->smwstore->setup($verbose);

		$this->createTables($verbose);

		global $smwgMessageBroker, $smwgTripleStoreGraph, $wgDBtype, $wgDBport, $wgDBserver, $wgDBname, $wgDBuser, $wgDBpassword, $wgDBprefix, $wgLanguageCode, $smwgBaseStore, $smwgIgnoreSchema, $smwgNamespaceIndex;
		$ignoreSchema = isset($smwgIgnoreSchema) && $smwgIgnoreSchema === true ? "true" : "false";
		try {
			$con = new TSConnection();
			$sparulCommands = array();
			$sparulCommands[] = "DROP <$smwgTripleStoreGraph>"; // drop may fail. don't worry
			$sparulCommands[] = "CREATE <$smwgTripleStoreGraph>";
			$sparulCommands[] = "LOAD smw://".urlencode($wgDBuser).":".urlencode($wgDBpassword)."@$wgDBserver:$wgDBport/$wgDBname?lang=$wgLanguageCode&smwstore=$smwgBaseStore&ignoreSchema=$ignoreSchema&smwnsindex=$smwgNamespaceIndex#".urlencode($wgDBprefix)." INTO <$smwgTripleStoreGraph>";
			$con->connect();
			$con->send("/topic/WIKI.TS.UPDATE", $sparulCommands);
			$con->disconnect();
		} catch(Exception $e) {

		}
	}

	function drop($verbose = true) {
		$this->smwstore->drop($verbose);

		global $smwgMessageBroker, $smwgTripleStoreGraph;
		try {
			$con = new TSConnection();

			$con->connect();
			$con->send("/topic/WIKI.TS.UPDATE", "DROP <$smwgTripleStoreGraph>");
			$con->disconnect();
		} catch(Exception $e) {

		}
	}

	function refreshData(&$index, $count, $namespaces = false, $usejobs = true) {
		$this->smwstore->refreshData($index, $count, $namespaces, $usejobs);
	}


	// Helper methods

	/**
	 * Creates tables in Wiki SQL Database
	 *
	 * @param boolean $verbose
	 */
	private function createTables($verbose) {
		global $smwgHaloIP;
		require_once( $smwgHaloIP . "/includes/SMW_DBHelper.php");
		$db =& wfGetDB( DB_MASTER );

		$ruleTableName = $db->tableName('smw_rules');
		// create rule table
		DBHelper::setupTable($ruleTableName,
		array('subject_id'    => 'INT(8) UNSIGNED NOT NULL',
                            'rule_id'       => 'VARCHAR(255) binary NOT NULL',
                            'rule_text'      => 'TEXT NOT NULL'), $db, $verbose);
	}

	/**
	 * Drops SQL tables.
	 *
	 * @param boolean $verbose
	 */
	private function dropTables($verbose) {
		$db =& wfGetDB( DB_MASTER );

		$ruleTableName = $db->tableName('smw_rules');

		$db->query("DROP TABLE $ruleTableName", 'SMWTripleStore::dropTables');
		DBHelper::reportProgress(" ... dropped table $ruleTableName.\n", $verbose);

	}



	/**
	 * Implodes triples separated by a dot for SPARUL commands.
	 *
	 * @param array of $triples
	 * @return string
	 */
	private function implodeTriples($triples) {
		$result = "";
		foreach($triples as $t) {
			$result .= implode(" ", $t);
			$result .= ". ";
		}
		return $result;
	}

	/**
	 * Returns namespace prefix for SPARUL commands.
	 *
	 * @param int $namespace
	 * @return string
	 */
	private function getNSPrefix($namespace) {
		if ($namespace == SMW_NS_PROPERTY) return "prop";
		elseif ($namespace == NS_CATEGORY) return "cat";
		elseif ($namespace == NS_MAIN) return "a";
		elseif ($namespace == SMW_NS_TYPE) return "type";
		elseif ($namespace == NS_IMAGE) return "image";
		elseif ($namespace == NS_HELP) return "help";
		else return "ns_$namespace";
	}

	/**
	 * Create a SPARQL PREFIX statement for unknown namespaces.
	 *
	 * @param string $suffix which serves also as prefix.
	 * @return string
	 */
	private function getUnknownNamespacePrefixes($suffix) {
		if (substr($suffix, 0, 3) == "ns_") {
			global $smwgTripleStoreGraph;
			return " PREFIX $suffix:<$smwgTripleStoreGraph/$suffix#> ";
		}
		return "";
	}

	/**
	 * Escapes double quotes
	 *
	 * @param string $literal
	 * @return string
	 */
	private function escapeQuotes($literal) {
		return str_replace("\"", "\\\"", $literal);
	}

	/**
	 * Unquotes a string
	 *
	 * @param String $literal
	 * @return String
	 */
	private function unquote($literal) {
		$trimed_lit = trim($literal);
		if (stripos($trimed_lit, "\"") === 0 && strrpos($trimed_lit, "\"") === strlen($trimed_lit)-1) {
			$substr = substr($trimed_lit, 1, strlen($trimed_lit)-2);
			return str_replace("\\\"", "\"", $substr);
		}
		return $trimed_lit;
	}



	/**
	 * Removes type hint, e.g. "....."^^xsd:type gets to "....."
	 *
	 * @param string $literal
	 * @return string
	 */
	private function removeXSDType($literal) {
		$pos = strpos($literal, "^^");
		return $pos !== false ? substr($literal, 0, $pos) : $literal;
	}

	/**
	 * Parses a SPARQL XML-Result and returns an SMWQueryResult.
	 *
	 * @param SMWQuery $query
	 * @param xml string $sparqlXMLResult
	 * @return SMWQueryResult
	 */
	private function parseSPARQLXMLResult(& $query, & $sparqlXMLResult) {

		// parse xml results
		$dom = simplexml_load_string($sparqlXMLResult);

		$variables = $dom->xpath('//variable');
		$results = $dom->xpath('//result');

		// if no results return empty result object
		if (count($results) == 0) return new SMWQueryResult(array(), $query);

		$variableSet = array();
		foreach($variables as $var) {
			$variableSet[] = (string) $var->attributes()->name;
		}

		// PrinterRequests to use
		$prs = array();

		// Use PrintRequests to determine which variable denotes what type of entity. If no PrintRequest is given use first result row
		// (which exist!) to determine which variable denotes what type of entity.


		// maps print requests (variable name) to result columns ( var_name => index )
		$mapPRTOColumns = array();

		// use user-given PrintRequests if possible
		$print_requests = $query->getDescription()->getPrintRequests();
		$hasMainColumn = false;
		$index = 0;
		if ($query->fromASK) {

			// SPARQL query which was transformed from ASK
			// x variable is handeled specially as main variable
			foreach($print_requests as $pr) {

				$data = $pr->getData();
				if ($data == NULL) { // main column
					$hasMainColumn = true;
					if (in_array('_X_', $variableSet)) { // x is missing for INSTANCE queries
						$mapPRTOColumns['_X_'] = $index;
						$prs[] = $pr;
						$index++;
					}

				} else  {
					// make sure that variables get truncated for SPARQL compatibility when used with ASK.
					$label = $data instanceof Title ? $data->getDBkey() : $data->getXSDValue();
					preg_match("/[A-Z][\\w_]*/", $label, $matches);
					$mapPRTOColumns[$matches[0]] = $index;
					$prs[] = $pr;
					$index++;
				}

			}
		} else {

			// native SPARQL query
			foreach($print_requests as $pr) {

				$data = $pr->getData();
				if ($data != NULL) {
					$label = $data instanceof Title ? $data->getDBkey() : $data->getXSDValue();
					$mapPRTOColumns[$label] = $index;
					$prs[] = $pr;
					$index++;
				}

			}
		}


		// generate PrintRequests for all bindings (if they do not exist already)
		$var_index = 0;
		$bindings = $results[0]->children()->binding;
		foreach ($bindings as $b) {
			$var_name = ucfirst((string) $variables[$var_index]->attributes()->name);
			$var_index++;

			// if no mainlabel, do not create a printrequest for _X_ (instance variable for ASK-converted queries)
			if ($query->mainLabelMissing && $var_name == "_X_") {
				continue;
			}
			// do not generate new PrintRequest if already given
			if ($this->containsPrintRequest($var_name, $print_requests, $query)) continue;

			// otherwise create one
			if (stripos($b, self::$CAT_NS) === 0) {
				$prs[] = new SMWPrintRequest(SMWPrintRequest::PRINT_THIS, str_replace("_"," ",$var_name), Title::newFromText($var_name, NS_CATEGORY));
			} else if (stripos($b, self::$PROP_NS) === 0) {
				$prs[] = new SMWPrintRequest(SMWPrintRequest::PRINT_THIS, str_replace("_"," ",$var_name), SMWPropertyValue::makeUserProperty($var_name, SMW_NS_PROPERTY));
			} else if (stripos($b, self::$INST_NS) === 0) {
				$prs[] = new SMWPrintRequest(SMWPrintRequest::PRINT_THIS, str_replace("_"," ",$var_name), Title::newFromText($var_name, NS_MAIN));
			} else if (stripos($b, self::$HELP_NS) === 0) {
				$prs[] = new SMWPrintRequest(SMWPrintRequest::PRINT_THIS, str_replace("_"," ",$var_name), Title::newFromText($var_name, NS_HELP));
			} else if (stripos($b, self::$IMAGE_NS) === 0) {
				$prs[] = new SMWPrintRequest(SMWPrintRequest::PRINT_THIS, str_replace("_"," ",$var_name), Title::newFromText($var_name, NS_IMAGE));
			} else if (stripos($b, self::$UNKNOWN_NS) === 0) {
				$startNS = strlen(self::$UNKNOWN_NS);
				$length = strpos($b, "#") - $startNS;
				$ns = intval(substr($b, $startNS, $length));
				$prs[] = new SMWPrintRequest(SMWPrintRequest::PRINT_THIS, str_replace("_"," ",$var_name), Title::newFromText($var_name, $ns));
			} else {
				$prs[] = new SMWPrintRequest(SMWPrintRequest::PRINT_THIS, str_replace("_"," ",$var_name), SMWPropertyValue::makeUserProperty($var_name, SMW_NS_PROPERTY));
			}
			$mapPRTOColumns[$var_name] = $index;
			$index++;
		}

		// Query result object
		$queryResult = new SMWQueryResult($prs, $query, (count($results) > $query->getLimit()));
			
		// create and add result rows
		// iterate result rows and add an SMWResultArray object for each field
		foreach ($results as $r) {
			$row = array();
			$columnIndex = 0; // column = n-th XML binding node

			$children = $r->children(); // $chilren->binding denote all binding nodes
			foreach ($children->binding as $b) {
					
				$var_name = ucfirst((string) $children[$columnIndex]->attributes()->name);
				if (!$hasMainColumn && $var_name == '_X_') {

					$columnIndex++;
					continue;
				}
				$resultColumn = $mapPRTOColumns[$var_name];
				$multiValue = explode("|", $b);
				$allValues = array();

				foreach($multiValue as $sv) {
					$this->addValueToResult($sv, $prs[$resultColumn], $allValues);
				}
				$columnIndex++;
				$row[$resultColumn] = new SMWResultArray($allValues, $prs[$resultColumn]);

			}

			ksort($row);
			$queryResult->addRow($row);
		}
		return $queryResult;
	}
    
	/**
	 * Add a resource or property value to an array of results
	 *
	 * @param string $sv A single value (literal or URI)
	 * @param PrintRequest prs
	 * @param array & $allValues
	 */
	private function addValueToResult($sv, $prs, & $allValues) {
		// category result
		if (stripos($sv, self::$CAT_NS) === 0) {
			$local = substr($sv, strlen(self::$CAT_NS));
			$title = Title::newFromText(utf8_decode($local), NS_CATEGORY);
			$v = SMWDataValueFactory::newTypeIDValue('_wpg');
			$v->setValues($title->getDBkey(), NS_CATEGORY, $title->getArticleID());
			$allValues[] = $v;

			// property result
		} else if (stripos($sv, self::$PROP_NS) === 0) {

			$local = substr($sv, strlen(self::$PROP_NS));
			$title = Title::newFromText(utf8_decode($local), SMW_NS_PROPERTY);
			$v = SMWDataValueFactory::newTypeIDValue('_wpg');
			$v->setValues($title->getDBkey(), SMW_NS_PROPERTY, $title->getArticleID());
			$allValues[] = $v;

			// instance result
		} else if (stripos($sv, self::$INST_NS) === 0) {

			$local = substr($sv, strlen(self::$INST_NS));
			$title = Title::newFromText(utf8_decode($local), NS_MAIN);
			$v = SMWDataValueFactory::newTypeIDValue('_wpg');
			$v->setValues($title->getDBkey(), NS_MAIN, $title->getArticleID());
			$allValues[] = $v;

			// help result
		} else if (stripos($sv, self::$HELP_NS) === 0) {

			$local = substr($sv, strlen(self::$HELP_NS));
			$title = Title::newFromText(utf8_decode($local), NS_HELP);
			$v = SMWDataValueFactory::newTypeIDValue('_wpg');
			$v->setValues($title->getDBkey(), NS_HELP, $title->getArticleID());
			$allValues[] = $v;

			// image result
		} else if (stripos($sv, self::$IMAGE_NS) === 0) {

			$local = substr($sv, strlen(self::$IMAGE_NS));
			$title = Title::newFromText(utf8_decode($local), NS_IMAGE);
			$v = SMWDataValueFactory::newTypeIDValue('_wpg');
			$v->setValues($title->getDBkey(), NS_IMAGE, $title->getArticleID());
			$allValues[] = $v;

			// result with unknown namespace
		} else if (stripos($sv, self::$UNKNOWN_NS) === 0) {
			$startNS = strlen(self::$UNKNOWN_NS);
			$length = strpos($sv, "#") - $startNS;
			$ns = intval(substr($sv, $startNS, $length));


			$local = substr($sv, strpos($sv, "#")+1);

			$title = Title::newFromText(utf8_decode($local), $ns);
			$v = SMWDataValueFactory::newTypeIDValue('_wpg');
			$v->setValues($title->getDBkey(), $ns, $title->getArticleID());
			$allValues[] = $v;

			// property value result
		} else {
            
			$literal = $this->unquote($this->removeXSDType($sv));
			$value = SMWDataValueFactory::newPropertyObjectValue($prs->getData(), $literal);
			if ($value->getTypeID() == '_dat') { // exception for dateTime
				if ($literal != '') $value->setXSDValue(utf8_decode($literal));
			} else {
				$value->setUserValue(utf8_decode($literal));
			}
			$allValues[] = $value;


		}
	}

	/**
	 * Serializes parameters and extraprintouts of SMWQuery.
	 * These informations are needed to generate a correct SPARQL query.
	 *
	 * @param SMWQuery $query
	 * @return String
	 */
	private function serializeParams($query) {
		$result = "";
		$first = true;

		foreach ($query->getExtraPrintouts() as $printout) {
			if (!$first) $result .= "|";
			if ($printout->getData() == NULL) {
				$result .= "?=".$printout->getLabel();
			} else if ($printout->getData() instanceof Title) {
				$result .= "?".$printout->getData()->getDBkey()."=".$printout->getLabel();
			} else if ($printout->getData() instanceof SMWPropertyValue ) {
				$outputFormat = $printout->getOutputFormat() !== NULL ? "#".$printout->getOutputFormat() : "";
				$result .= "?".$printout->getData()->getXSDValue().$outputFormat."=".$printout->getLabel();
			}
			$first = false;
		}
		if ($query->getLimit() != NULL) {
			if (!$first) $result .= "|";
			$result .= "limit=".$query->getLimit();
			$first = false;
		}
		if ($query->getOffset() != NULL) {
			if (!$first) $result .= "|";
			$result .= "offset=".$query->getOffset();
			$first = false;
		}
		if ($query->sort) {
			if (!$first) $result .= "|";
			$first = false;
			$sort = "sort=";
			$order = "order=";
			$firstsort = true;
			foreach($query->sortkeys as $sortkey => $orderkey) {
				if (!$firstsort) { $sort .= ","; $order .= ",";  }
				$sort .= $sortkey;
				$order .= $orderkey;
				$firstsort = false;
			}
			$result .= $sort."|".$order;
		}
		return $result;
	}

	/**
	 * Returns true, if the given variable $var_name is represented by a PrintRequest in $prqs
	 *
	 * @param String $var_name
	 * @param array $prqs
	 * @return boolean
	 */
	private function containsPrintRequest($var_name, array & $prqs, & $query) {
		$contains = false;
		foreach($prqs as $po) {
			if ($query->fromASK && $po->getData() == NULL && $var_name == '_X_') {
				return true;
			}
			if ($po->getData() != NULL) {
				$label = $po->getData() instanceof Title ? $po->getData()->getDBkey() : $po->getData()->getXSDValue();
				$contains |= strtolower($label) == strtolower($var_name);
			}

		}
		return $contains;
	}


}

/**
 * Provides access to local rule store.
 *
 */
class SMWRuleStore {
	private static $INSTANCE = NULL;

	public static function getInstance() {
		if (self::$INSTANCE == NULL) {
			self::$INSTANCE = new SMWRuleStore();
		}
		return self::$INSTANCE;
	}

	/**
	 * Returns rule from local rule store for a given page id.
	 *
	 * @param int $page_id
	 * @return array of rule_id
	 */
	public function getRules($page_id) {
		$db =& wfGetDB( DB_SLAVE );

		$ruleTableName = $db->tableName('smw_rules');
		$res = $db->select($ruleTableName, array('rule_id'), array('subject_id' => $page_id));
		$results = array();

		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$results[] = $row->rule_id;
			}
		}
		$db->freeResult($res);
		return $results;
	}

	/**
	 * Adds new rules to the local rule store.
	 *
	 * @param int $article_id
	 * @param array $new_rules (ruleID => ruleText)
	 */
	public function addRules($article_id, $new_rules) {

		$db =& wfGetDB( DB_MASTER );
		$smw_rules = $db->tableName('smw_rules');
		foreach($new_rules as $rule_id => $ruleText) {
			$db->insert($smw_rules, array('subject_id' => $article_id, 'rule_id' => $rule_id, 'rule_text' => $ruleText));
		}
	}

	/**
	 * Removes rule from given article
	 *
	 * @param int $article_id
	 */
	public function clearRules($article_id) {

		$db =& wfGetDB( DB_MASTER );
		$smw_rules = $db->tableName('smw_rules');
		$db->delete($smw_rules, array('subject_id' => $article_id));
	}

	/**
	 * Updates article IDs. In case of a renaming operation.
	 *
	 * @param int $old_article_id
	 * @param int $new_article_id
	 */
	public function updateRules($old_article_id, $new_article_id) {
		$db =& wfGetDB( DB_MASTER );
		$smw_rules = $db->tableName('smw_rules');
		$db->update($smw_rules, array('subject_id' => $new_article_id), array('subject_id' => $old_article_id));
	}
}

class WikiTypeToXSD {

	/**
	 * Map primitve types or units to XSD values
	 *
	 * @param unknown_type $wikiTypeID
	 * @return unknown
	 */
	public static function getXSDType($wikiTypeID) {
		switch($wikiTypeID) {

			// direct supported types
			case '_str' : return 'xsd:string';
			case '_txt' : return 'xsd:string';
			case '_num' : return 'xsd:double';
			case '_boo' : return 'xsd:boolean';
			case '_dat' : return 'xsd:dateTime';

			// not supported by TS. Take xsd:string
			case '_geo' :
			case '_cod' :
			case '_ema' :
			case '_uri' :
			case '_anu' : return 'xsd:string';

			// single unit type in SMW
			case '_tem' : return 'xsd:unit';

			//only relevant for schema import
			case '_wpc' :
			case '_wpf' :
			case '_wpp' :
			case '_wpg' : return 'cat:DefaultRootCategory';

			// unknown or composite type
			default:
				// if builtin (starts with _) then regard it as string
				if (substr($wikiTypeID, 0, 1) == '_') return "xsd:string";
				// if n-ary, regard it as string
				if (preg_match('/\w+(;\w+)+/', $wikiTypeID) !== false) return "xsd:string";
				// otherwise assume a unit
				return 'xsd:unit';
		}

	}
}

/**
 * Provides an abstraction for the connection to the triple store.
 * Currently, two connector types are supported:
 *
 *  1. MessageBroker
 *  2. Webservice
 *
 */
class TSConnection {

	private $con;

	/**
	 * Connects to the triplestore
	 *
	 */
	public function connect() {
		global $smwgMessageBroker, $smwgDeployVersion;

		if (isset($smwgMessageBroker)) {
			$this->con = new StompConnection("tcp://$smwgMessageBroker:61613");
			$this->con->connect();
		} else {
			global $smwgWebserviceUser, $smwgWebservicePassword, $wgServer, $wgScript;
			if (!isset($smwgDeployVersion) || !$smwgDeployVersion) ini_set("soap.wsdl_cache_enabled", "0");  //set for debugging
			$this->con = new SoapClient("$wgServer$wgScript?action=ajax&rs=smwf_ws_getWSDL&rsargs[]=get_sparul", array('login'=>$smwgWebserviceUser, 'password'=>$smwgWebservicePassword));

		}
	}

	/**
	 * Disconnects
	 *
	 */
	public function disconnect() {
		global $smwgMessageBroker;
		if (isset($smwgMessageBroker)) {
			$this->con->disconnect();
		} else {
			// do nothing
		}
	}

	/**
	 * Sends SPARUL commands
	 *
	 * @param string $topic (only) relevant for a messagebroker.
	 * @param string or array of strings $commands
	 */
	public function send($topic, $commands) {
		global $smwgMessageBroker, $smwgTSEncodeUTF8;
		if (isset($smwgMessageBroker)) {
			if (!is_array($commands)) {
				$enc_commands = isset($smwgTSEncodeUTF8) && $smwgTSEncodeUTF8 === true ? utf8_encode($commands) : $commands;
				$this->con->send($topic, $enc_commands);
				return;
			}
			$commandStr = implode("|||",$commands);
			$enc_commands = isset($smwgTSEncodeUTF8) && $smwgTSEncodeUTF8 === true ? utf8_encode($commandStr) : $commandStr;
			$this->con->send($topic, $enc_commands);

		} else {
			// ignore topic
			if (!is_array($commands)) {
				$enc_commands = isset($smwgTSEncodeUTF8) && $smwgTSEncodeUTF8 === true ? utf8_encode($commands) : $commands;
				$this->con->update($enc_commands);
				return;
			}
			$commandStr = implode("|||",$commands);
			$enc_commands = isset($smwgTSEncodeUTF8) && $smwgTSEncodeUTF8 === true ? utf8_encode($commandStr) : $commandStr;
			$this->con->update($enc_commands);

		}
	}


}



?>