<?php
global $smwgIP, $smwgHaloIP;
require_once( "$smwgIP/includes/storage/SMW_Store.php" );
require_once( "$smwgHaloIP/includes/storage/SMW_RuleStore.php" );
require_once( "$smwgHaloIP/includes/storage/SMW_RESTWebserviceConnector.php" );
require_once( "$smwgHaloIP/includes/storage/SMW_HaloQueryResult.php" );
require_once( "$smwgHaloIP/includes/storage/SMW_TS_Helper.php" );

/**
 * @file
 * @ingroup SMWHaloTriplestore
 *
 * @defgroup SMWHaloTriplestore SMWHalo Triplestore
 * @ingroup SMWHalo
 *
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



	public static $fullSemanticData;
	protected $tsNamespace;


	/**
	 * Creates and initializes Triple store connector.
	 *
	 * @param SMWStore $smwstore All calls are delegated to this implementation.
	 */
	function __construct() {
		global $smwgBaseStore;
		$this->smwstore = new $smwgBaseStore;
		$this->tsNamespace = new TSNamespaces();
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
		$subj_ns = $this->tsNamespace->getNSPrefix($subject->getNamespace());



		// clear rules
		global $smwgEnableObjectLogicRules;
		if (isset($smwgEnableObjectLogicRules)) {
			$old_rules = SMWRuleStore::getInstance()->getRules($subject->getArticleId());
			SMWRuleStore::getInstance()->clearRules($subject->getArticleId());
		}
		global $smwgMessageBroker, $smwgTripleStoreGraph;
		try {
			$con = TSConnection::getConnector();
			$sparulCommands = array();
			$sparulCommands[] = TSNamespaces::getW3CPrefixes()."DELETE FROM <$smwgTripleStoreGraph> { <$smwgTripleStoreGraph/$subj_ns#".$subject->getDBkey()."> ?p ?o. }";
			if ($subject->getNamespace() == SMW_NS_PROPERTY) {
				$sparulCommands[] = TSNamespaces::getW3CPrefixes()."DELETE FROM <$smwgTripleStoreGraph> { ?s owl:onProperty <$smwgTripleStoreGraph/$subj_ns#".$subject->getDBkey().">. }";
			}
			if (isset($smwgEnableObjectLogicRules)) {
				// delete old rules...
				foreach($old_rules as $ruleID) {
					$sparulCommands[] = "DELETE RULE $ruleID FROM <$smwgTripleStoreGraph>";
				}
			}
			$con->connect();
			$con->update("/topic/WIKI.TS.UPDATE", $sparulCommands);
			$con->disconnect();
		} catch(Exception $e) {

		}
	}

	function updateData(SMWSemanticData $data) {
		$this->smwstore->updateData($data);

		$triples = array();

		$subject = $data->getSubject();

		// check for selective updates, ie. update only certain namespaces
		global $smwgUpdateTSOnNamespaces;

		if (isset($smwgUpdateTSOnNamespaces) && is_array($smwgUpdateTSOnNamespaces)) {
			if (!in_array($subject->getNamespace(), $smwgUpdateTSOnNamespaces)) {
				return;
			}
		}

		$subj_ns = $this->tsNamespace->getNSPrefix($subject->getNamespace());



		//properties
		global $smwgTripleStoreGraph;
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
						$measures = explode(",", array_shift($value->getDBkeys()));

						// parse linear factor followed by (first) unit
						$firstMeasure = reset($measures);
						$indexOfWhitespace = strpos($firstMeasure, " ");
						if ($indexOfWhitespace === false) continue; // not a valid measure, ignore
						$factor = trim(substr($firstMeasure, 0, $indexOfWhitespace));
						$unit = trim(substr($firstMeasure, $indexOfWhitespace));
						$triples[] = array("<$smwgTripleStoreGraph/type#".$subject->getDBkey().">", "<$smwgTripleStoreGraph/property#".$conversionPropertyLabel.">", "\"$factor $unit\"");

						// add all aliases for this conversion factor using the same factor
						$nextMeasure = next($measures);
						while($nextMeasure !== false) {
							$nextMeasure = str_replace('"', '\"', $nextMeasure);
							$triples[] = array("<$smwgTripleStoreGraph/type#".$subject->getDBkey().">", "<$smwgTripleStoreGraph/property#".$conversionPropertyLabel.">", "\"$factor ".trim($nextMeasure)."\"");
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
						$triples[] = array("<$smwgTripleStoreGraph/property#".$subject->getDBkey().">", "rdfs:subPropertyOf", "<$smwgTripleStoreGraph/property#".$value->getDBkey().">");
					}

				}
				continue;
			}

			// there are other special properties which need not to be handled special
			// so they can be handled by the default machanism:
			foreach($propertyValueArray as $value) {
				if ($value->isValid()) {
					if ($value->getTypeID() == '_txt') {
						$triples[] = array("<$smwgTripleStoreGraph/$subj_ns#".$subject->getDBkey().">", "<$smwgTripleStoreGraph/property#".$property->getWikiPageValue()->getDBkey().">", "\"".$this->escapeForStringLiteral(array_shift($value->getDBkeys()))."\"^^xsd:string");

					} elseif ($value->getTypeID() == '_wpg' || $value->getTypeID() == '_wpp' || $value->getTypeID() == '_wpc' || $value->getTypeID() == '_wpf') {
						$obj_ns = $this->tsNamespace->getNSPrefix($value->getNamespace());

						$triples[] = array("<$smwgTripleStoreGraph/$subj_ns#".$subject->getDBkey().">", "<$smwgTripleStoreGraph/property#".$property->getWikiPageValue()->getDBkey().">", "<$smwgTripleStoreGraph/$obj_ns#".$value->getDBkey().">");

					} elseif ($value->getTypeID() == '_rec') {
						continue; // do not add records (aka nary properties)
					} else {

						if ($value->getUnit() != '') {
							// attribute with unit value
							$triples[] = array("<$smwgTripleStoreGraph/$subj_ns#".$subject->getDBkey().">", "<$smwgTripleStoreGraph/property#".$property->getWikiPageValue()->getDBkey().">", "\"".array_shift($value->getDBkeys())." ".$value->getUnit()."\"^^xsd:unit");
						} else {
							if (!is_null($property->getWikiPageValue())) {
								if (array_shift($value->getDBkeys()) != NULL) {
									// attribute with textual value
									$xsdType = WikiTypeToXSD::getXSDType($property->getPropertyTypeID());
									$triples[] = array("<$smwgTripleStoreGraph/$subj_ns#".$subject->getDBkey().">", "<$smwgTripleStoreGraph/property#".$property->getWikiPageValue()->getDBkey().">", "\"".$this->escapeForStringLiteral(array_shift($value->getDBkeys()))."\"^^$xsdType");
								} else if ($value->getNumericValue() != NULL) {
									// attribute with numeric value
									$triples[] = array("<$smwgTripleStoreGraph/$subj_ns#".$subject->getDBkey().">", "<$smwgTripleStoreGraph/property#".$property->getWikiPageValue()->getDBkey().">", "\"".$value->getNumericValue()."\"^^xsd:double");
								}
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
				$triples[] = array("<$smwgTripleStoreGraph/category#".$subject->getDBkey().">", "rdfs:subClassOf", "<$smwgTripleStoreGraph/category#".$c->getDBkey().">");
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
				$triples[] = array("<$smwgTripleStoreGraph/$subj_ns#".$subject->getDBkey().">", "rdf:type", "<$smwgTripleStoreGraph/category#".$c->getDBkey().">");
			}
		}

		// rules
		global $smwgEnableObjectLogicRules;
		if (isset($smwgEnableObjectLogicRules)) {
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
			$r_ns = $this->tsNamespace->getNSPrefix($r->getNamespace());

			$triples[] = array("<$smwgTripleStoreGraph/$subj_ns#".$subject->getDBkey().">", $prop, "<$smwgTripleStoreGraph/$r_ns#".$r->getDBkey().">");
		}

		// connect to MessageBroker and send commands
		global $smwgMessageBroker, $smwgTripleStoreGraph;
		try {
			$con = TSConnection::getConnector();
			$sparulCommands = array();
			$sparulCommands[] = TSNamespaces::getW3CPrefixes()."DELETE FROM <$smwgTripleStoreGraph> { <$smwgTripleStoreGraph/$subj_ns#".$subject->getDBkey()."> ?p ?o. }";
			
			$sparulCommands[] =  TSNamespaces::getW3CPrefixes()."INSERT INTO <$smwgTripleStoreGraph> { ".$this->implodeTriples($triples)." }";

			if (isset($smwgEnableObjectLogicRules)) {
				// delete old rules...
				foreach($old_rules as $ruleID) {
					$sparulCommands[] = "DELETE RULE $ruleID FROM <$smwgTripleStoreGraph>";
				}
				// ...and add new
				foreach($new_rules as $rule) {
					// The F-Logic parser does not accept linebreaks
					// => remove them
					list($ruleID, $ruleText, $native, $active, $type) = $rule;
					$ruleText = preg_replace("/[\n\r]/", " ", $ruleText);
					$nativeText = $native ? "NATIVE" : "";
					$activeText = !$active ? "INACTIVE" : "";
					$sparulCommands[] = "INSERT $nativeText $activeText RULE $ruleID INTO <$smwgTripleStoreGraph> : \"".$this->escapeForStringLiteral($ruleText)."\" TYPE \"$type\"";
				}
			}
			$con->connect();
			$con->update("/topic/WIKI.TS.UPDATE", $sparulCommands);
			$con->disconnect();
		} catch(Exception $e) {
			// print something??
		}
	}


	function changeTitle(Title $oldtitle, Title $newtitle, $pageid, $redirid=0) {
		$this->smwstore->changeTitle($oldtitle, $newtitle, $pageid, $redirid);

		$old_ns = $this->tsNamespace->getNSPrefix($oldtitle->getNamespace());


		$new_ns = $this->tsNamespace->getNSPrefix($newtitle->getNamespace());


		// update local rule store
		global $smwgEnableObjectLogicRules;
		if (isset($smwgEnableObjectLogicRules)) {
			SMWRuleStore::getInstance()->updateRules($redirid, $pageid);
		}

		// update triple store
		global $smwgMessageBroker, $smwgTripleStoreGraph;
		try {
			$con = TSConnection::getConnector();

			$sparulCommands = array();
			$sparulCommands[] = TSNamespaces::getW3CPrefixes()."MODIFY <$smwgTripleStoreGraph> DELETE  { <$smwgTripleStoreGraph/$old_ns#".$oldtitle->getDBkey()."> ?p ?o. } INSERT { <$smwgTripleStoreGraph/$new_ns#".$newtitle->getDBkey()."> ?p ?o. }";
			$sparulCommands[] = TSNamespaces::getW3CPrefixes()."MODIFY <$smwgTripleStoreGraph> DELETE  { ?s <$smwgTripleStoreGraph/$old_ns#".$oldtitle->getDBkey()."> ?o. } INSERT { ?s <$smwgTripleStoreGraph/$new_ns#".$newtitle->getDBkey()."> ?o. }";
			$sparulCommands[] = TSNamespaces::getW3CPrefixes()."MODIFY <$smwgTripleStoreGraph> DELETE  { ?s ?p <$smwgTripleStoreGraph/$old_ns#".$oldtitle->getDBkey().">. } INSERT { ?s ?p <$smwgTripleStoreGraph/$new_ns#".$newtitle->getDBkey().">. }";
			$con->connect();
			$con->update("/topic/WIKI.TS.UPDATE", $sparulCommands);
			$con->disconnect();
		} catch(Exception $e) {

		}
	}

	///// Query answering /////

	public function getQueryResult(SMWQuery $query){
		global $smwgQRCEnabled;
		if($smwgQRCEnabled){
			$qrc = new SMWQRCQueryResultsCache();
			return $qrc->getQueryResult($query);
		} else {
			return $this->doGetQueryResult($query);
		}
	}

	function doGetQueryResult(SMWQuery $query) {
		global $wgServer, $wgScript, $smwgWebserviceUser, $smwgWebservicePassword, $smwgDeployVersion;

		// handle only SPARQL queries and delegate all others
		if ($query instanceof SMWSPARQLQuery) {
//			wfRunHooks('RewriteSparqlQuery', array(&$query) );

			if ($query->getQueryString() == "") {
				$sqr = new SMWHaloQueryResult(array(), $query, array(), $this, false);
				$sqr->addErrors(array(wfMsgForContent('hacl_sp_empty_query')));
				return $sqr;
			}
			try {
				$con = TSConnection::getConnector();
				$con->connect();
				$response = $con->query($query->getQueryString(), $this->serializeParams($query));
					
				global $smwgSPARQLResultEncoding;
				// PHP strings are always interpreted in ISO-8859-1 but may be actually encoded in
				// another charset.
				if (isset($smwgSPARQLResultEncoding) && $smwgSPARQLResultEncoding == 'UTF-8') {
					$response = utf8_decode($response);
				}

				$queryResult = $this->parseSPARQLXMLResult($query, $response);


			} catch(Exception $e) {

				switch ($query->querymode) {

					case SMWQuery::MODE_COUNT:
						$sqr = $e->getMessage();
						break;
					default:
						$sqr = new SMWHaloQueryResult(array(), $query, array(), $this);
						if ($e->getCode() == 0) {
							// happens most likely when TSC is not running
							global $smwgWebserviceEndpoint;
						    $sqr->addErrors(array(wfMsg('smw_ts_notconnected', $smwgWebserviceEndpoint)));
						} else {
							$sqr->addErrors(array($e->getMessage()));
						}

						break;
				}
				return $sqr;
			}

//			wfRunHooks('FilterQueryResults', array(&$queryResult) );

			switch ($query->querymode) {

				case SMWQuery::MODE_COUNT:
					$queryResult = $queryResult->getCount();
					break;
				default:

					break;
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

	}

	function initialize($verbose = true) {
		global $smwgMessageBroker, $smwgTripleStoreGraph, $wgDBtype, $wgDBport, $wgDBserver, $wgDBname, $wgDBuser, $wgDBpassword, $wgDBprefix, $wgLanguageCode, $smwgBaseStore, $smwgIgnoreSchema, $smwgNamespaceIndex;
		$ignoreSchema = isset($smwgIgnoreSchema) && $smwgIgnoreSchema === true ? "true" : "false";
		try {
			$con = TSConnection::getConnector();
			$sparulCommands = array();
			$sparulCommands[] = "DROP SILENT GRAPH <$smwgTripleStoreGraph>"; // drop may fail. don't worry
			$sparulCommands[] = "CREATE SILENT GRAPH <$smwgTripleStoreGraph>";
			$sparulCommands[] = "LOAD smw://".urlencode($wgDBuser).":".urlencode($wgDBpassword)."@$wgDBserver:$wgDBport/$wgDBname?lang=$wgLanguageCode&smwstore=$smwgBaseStore&ignoreSchema=$ignoreSchema&smwnsindex=$smwgNamespaceIndex#".urlencode($wgDBprefix)." INTO <$smwgTripleStoreGraph>";
			$con->connect();
			$con->update("/topic/WIKI.TS.UPDATE", $sparulCommands);
			$con->disconnect();
		} catch(Exception $e) {

		}
	}


	function drop($verbose = true) {
		$this->smwstore->drop($verbose);
	}

	function refreshData(&$index, $count, $namespaces = false, $usejobs = true) {
		$this->smwstore->refreshData($index, $count, $namespaces, $usejobs);
	}

	public function getSMWPageID($title, $namespace, $iw, $canonical=true) {
		return $this->smwstore->getSMWPageID($title, $namespace, $iw, $canonical);
	}

	public function cacheSMWPageID($id, $title, $namespace, $iw) {
		return $this->smwstore->cacheSMWPageID($id, $title, $namespace, $iw);
	}

	// Helper methods





	/**
	 * Implodes triples separated by a dot for SPARUL commands.
	 *
	 * @param array of $triples
	 * @return string
	 */
	protected function implodeTriples($triples) {
		$result = "";
		foreach($triples as $t) {
			$result .= implode(" ", $t);
			$result .= ". ";
		}
		return $result;
	}





	/**
	 * Escapes double quotes, backslash and line feeds for a SPARUL string literal.
	 *
	 * @param string $literal
	 * @return string
	 */
	protected function escapeForStringLiteral($literal) {
		return str_replace(array("\\", "\"", "\n", "\r"), array("\\\\", "\\\"", "\\n" ,"\\r"), $literal);
	}

	/**
	 * Unquotes a string
	 *
	 * @param String $literal
	 * @return String
	 */
	protected function unquote($literal) {
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
	protected function removeXSDType($literal) {
		$pos = strpos($literal, "^^");
		return $pos !== false ? substr($literal, 0, $pos) : $literal;
	}

	/**
	 * Parses a SPARQL XML-Result and returns an SMWHaloQueryResult.
	 *
	 * @param SMWQuery $query
	 * @param xml string $sparqlXMLResult
	 * @return SMWHaloQueryResult
	 */
	protected function parseSPARQLXMLResult(& $query, & $sparqlXMLResult) {

		// parse xml results

		$dom = simplexml_load_string($sparqlXMLResult);
		if($dom === FALSE) return new SMWHaloQueryResult(array(), $query, array(), $this);
		$variables = $dom->xpath('//variable');
		$results = $dom->xpath('//result');
		// if no results return empty result object
		if (count($results) == 0) return new SMWHaloQueryResult(array(), $query, array(), $this);

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

		// _X_ is used for the main variable.
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

					$label = $data instanceof Title ? $data->getDBkey() : array_shift($data->getDBkeys());
					$mapPRTOColumns[$label] = $index;
					$rewritten_pr = $this->rewritePrintrequest($pr);
					$prs[] = $rewritten_pr;
					$index++;
				}

			}
		} else {

			// native SPARQL query, no main variable
			foreach($print_requests as $pr) {

				$data = $pr->getData();
				if ($data != NULL) {
					$label = $data instanceof Title ? $data->getDBkey() : array_shift($data->getDBkeys());
					$mapPRTOColumns[$label] = $index;
					$rewritten_pr = $this->rewritePrintrequest($pr);
					$prs[] = $rewritten_pr;
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
			// do not generate new printRequest if already given
			if ($this->containsPrintRequest($var_name, $print_requests, $query)) continue;

			// otherwise create one
			$var_path = explode(".", $var_name);
			$sel_var = ucfirst($var_path[count($var_path)-1]);
			$data = SMWPropertyValue::makeUserProperty($sel_var);
			$prs[] = new SMWPrintRequest(SMWPrintRequest::PRINT_THIS, str_replace("_"," ",$sel_var), $data);



			$mapPRTOColumns[$var_name] = $index;
			$index++;
		}


		// get resultpage, ie. the pages which "define" a result row.
		if ($query->fromASK) {
			// ASK queries usually always have a result column,
			// except if a single instance is requested

			// result column is available if first variable is "_X_"
			$var_name = ucfirst((string) $variables[0]->attributes()->name);

			// not available, so set dummys
			foreach ($results as $r) {
				// SPARQL queries do not, so just set a dummy
				$resultPages[] = SMWDataValueFactory::newTypeIDValue('_wpg');
			}

		} else {
			foreach ($results as $r) {
				// SPARQL queries do not, so just set a dummy
				$resultPages[] = SMWDataValueFactory::newTypeIDValue('_wpg');
			}
		}

		// create and add result rows
		// iterate result rows and add an SMWResultArray object for each field
		$qresults = array();
		$rowIndex = 0;
		foreach ($results as $r) {
			$row = array();
			$columnIndex = 0; // column = n-th XML binding node

			$children = $r->children(); // $chilren->binding denote all binding nodes
			foreach ($children->binding as $b) {

				$var_name = ucfirst((string) $children[$columnIndex]->attributes()->name);

				// ignore main variable if not displayed
				if (!$hasMainColumn && $var_name == '_X_') {
					$columnIndex++;
					continue;
				}
				$resultColumn = $mapPRTOColumns[$var_name];

				$allValues = array();
				$this->parseBindungs($b, $var_name, $prs[$resultColumn], $allValues);

				// note: ignore bnodes

				$columnIndex++;
				$row[$resultColumn] = new SMWHaloResultArray($resultPages[$rowIndex], $prs[$resultColumn], $this, $allValues);
			}
			$rowIndex++;
			ksort($row);
			$qresults[] = $row;

		}
		// Query result object
		$queryResult = new SMWHaloQueryResult($prs, $query, $qresults, $this, (count($results) > $query->getLimit()));

		return $queryResult;
	}

	/**
	 * Rewrite printrequests in the way that subselection are cut down to normal property selections
	 * in order to display them properly.
	 *
	 * @param SMWPrintRequest $pr
	 * @return SMWPrintRequest
	 */
	private function rewritePrintrequest($pr) {
	 $data = $pr->getData();
	 $rewritten_prs = $pr;
	 if ($data instanceof Title) { // property chain appear as Title
	 	$titleText = $data->getText();
	 	$chain = explode(".",$titleText);

	 	if (count($chain) > 1) {
	 		$newtitle = Title::newFromText($chain[count($chain)-1], SMW_NS_PROPERTY);
	 		if ($newtitle->exists()) {
	 			$newlabel = $pr->getLabel() != $titleText ? $pr->getLabel() : $newtitle->getText();
	 			$newData = SMWPropertyValue::makeUserProperty($newtitle->getText());
	 		} else {
	 			$newlabel = $pr->getLabel() != $titleText ? $pr->getLabel() : $newtitle->getText();
	 			$newData = $newtitle;
	 		}

	 		$rewritten_prs = new SMWPrintRequest($newtitle->exists() ? SMWPrintRequest::PRINT_PROP : SMWPrintRequest::PRINT_THIS, $newlabel, $newData, $pr->getOutputFormat());
	 		$rewritten_prs->getHash();

	 	}
	 }
	 return $rewritten_prs;
	}



	/**
	 * Parse bindungs from the SPARQL-XML binding node $b.
	 * Creates SMWWikiPageValue objects from <uri> SPARQL-XML nodes.
	 * Creates SMWDataValue objects from a <literal> SPARQL-XML nodes.
	 *
	 * @param $b Binding node
	 * @param $var_name Binding variable
	 * @param $pr QueryPrinter contains property and thus denotes type (optional)
	 * @param array (out) $allValues SMWDataResults
	 */
	protected function parseBindungs($b, $var_name, $pr, & $allValues) {
		$bindingsChildren = $b->children();
		$uris = array();

		foreach($bindingsChildren->uri as $sv) {
			$uris[] = array((string) $sv, $sv->attributes());
		}
		if (!empty($uris)) {
			$this->addURIToResult($uris, $allValues);
		} else {
			$literals = array();
			foreach($bindingsChildren->literal as $sv) {
				$literals[] = array((string) $sv, (string) $sv->attributes()->datatype, $sv->attributes());
			}

			if (!empty($literals)) {
				if ($var_name == '_X_') {
					// force adding as URI even if it is a literal
					foreach($literals as $l) {
						list($literalValue, $literalType, $metadata) = $l;
						$title = Title::newFromText($literalValue, NS_MAIN);
						$v = SMWDataValueFactory::newTypeIDValue('_wpg');
						$v->setValues($title->getDBkey(), $title->getNamespace(), $title->getArticleID());
					}
				} else
				$this->addLiteralToResult($literals, $pr, $allValues);
			}


		}
	}

	/**
	 * Gets an array of tuples (URI, metadata-uri) and creates SMWWikiPageValue objects.
	 *
	 * @param array of tuples (uri, hash array metadata) $uris
	 * @param array SMWDataValue (out) & $allValues
	 */
	protected function addURIToResult($uris, & $allValues) {

		foreach($uris as $uri) {
			list($sv, $metadata) = $uri;

			// check if common namespace from MW or SMW
			// and create SMWDataValue accoringly
			$nsFound = false;
			foreach (TSNamespaces::getAllNamespaces() as $nsIndsex => $ns) {
				if (stripos($sv, $ns) === 0) {
					$allValues[] = $this->createSMWDataValue($sv, $metadata, $ns, $nsIndsex);
					$nsFound = true;
				}
			}

			if ($nsFound) continue;

			// result with unknown namespace
			// unknown means the namespace has a suffix: /ns_<index>#
			// where <index> is the namespace index.
			if (stripos($sv, TSNamespaces::$UNKNOWN_NS) === 0) {

				if (empty($sv)) {
					$v = SMWDataValueFactory::newTypeIDValue('_wpg');
					foreach($metadata as $mdProperty => $mdValue) {
						if (strpos($mdProperty, "_meta_") === 0) {
							$v->setMetadata(substr($mdProperty,6), explode("|||",$mdValue));
						}
					}

					$allValues[] = $v;
				} else {
					$startNS = strlen(TSNamespaces::$UNKNOWN_NS);
					$length = strpos($sv, "#") - $startNS;
					$ns = intval(substr($sv, $startNS, $length));

					$local = substr($sv, strpos($sv, "#")+1);

					$title = Title::newFromText($local, $ns);
					if (is_null($title)) {
						$title = Title::newFromText(wfMsg('smw_ob_invalidtitle'), $ns);
					}
					$v = SMWDataValueFactory::newTypeIDValue('_wpg');
					$v->setValues($title->getDBkey(), $ns, $title->getArticleID());
					foreach($metadata as $mdProperty => $mdValue) {
						if (strpos($mdProperty, "_meta_") === 0) {
							$v->setMetadata(substr($mdProperty,6), explode("|||",$mdValue));
						}
					}
					$allValues[] = $v;
				}
			} else {
				// external URI

				$v = SMWDataValueFactory::newTypeIDValue('_uri');
				$v->setDBkeys(array($sv));
				foreach($metadata as $mdProperty => $mdValue) {
					if (strpos($mdProperty, "_meta_") === 0) {
						$v->setMetadata(substr($mdProperty,6), explode("|||",$mdValue));
					}
				}
				$allValues[] = $v;

			}
		}

	}

	/**
	 * Gets an array of literal tuples (value, type, metadata-uri) and creates according
	 * SMWDataValue objects.
	 *
	 * @param array Tuple (string value, string xsd-type, hash array metadata) $literals
	 * @param PrintRequest $pr QueryPrinter contains property and thus denotes type (optional)
	 * @param array SMWDataValue (out) & $allValues
	 */
	protected function addLiteralToResult($literals, $pr, & $allValues) {
		foreach($literals as $literal) {

			list($literalValue, $literalType, $metadata) = $literal;
			$property = !is_null($pr) ? $pr->getData() : NULL;
			if (!empty($literalValue)) {

				// create SMWDataValue either by property or if that is not possible by the given XSD type
				
				if ($property instanceof SMWPropertyValue ) {
					$propertyTitle = Title::newFromText($pr->getData()->getText(), SMW_NS_PROPERTY);
					if (!$propertyTitle->exists()) {
						// fallback if property does not exist, then use tyoe
						$value = SMWDataValueFactory::newTypeIDValue(WikiTypeToXSD::getWikiType($literalType));
					} else {
					    $value = SMWDataValueFactory::newPropertyObjectValue($pr->getData(), $literalValue);
					}
				} else {
					$value = SMWDataValueFactory::newTypeIDValue(WikiTypeToXSD::getWikiType($literalType));
				}
				if ($value->getTypeID() == '_dat') { // exception for dateTime
					if ($literalValue != '') {
						// do not display time if it is 00:00:00
						if (substr($literalValue, -9) == 'T00:00:00') {
							$literalValue = substr($literalValue, 0, strpos($literalValue, "T"));
						}
						$value->setDBkeys(array(str_replace("-","/",$literalValue)));
					}
				} else if ($value->getTypeID() == '_ema' || $value->getTypeID() == '_tel') { // exception for email
					$value->setDBkeys(array($literalValue));
				} else {
					$value->setUserValue($literalValue);
				}
			} else {

				if ($property instanceof SMWPropertyValue ) {
					$value = SMWDataValueFactory::newPropertyObjectValue($property);
				} else {
					$value = SMWDataValueFactory::newTypeIDValue('_wpg');

				}

			}
			foreach($metadata as $mdProperty => $mdValue) {
				if (strpos($mdProperty, "_meta_") === 0) {
					$value->setMetadata(substr($mdProperty,6), explode("|||",$mdValue));
				}
			}
			$allValues[] = $value;
		}
	}

	/**
	 * Creates SMWWikiPageValue object from a (possibly) merged result.
	 *
	 * @param string $uri Full URI
	 * @param hash array $metadata (propertyName=>value)
	 * @param string $nsFragment NS-prefix
	 * @param int $ns Namespace index
	 * @return SMWWikiPageValue
	 */
	protected function createSMWDataValue($uri, $metadata, $nsFragment, $ns) {

		$local = substr($uri, strlen($nsFragment));
		$title = Title::newFromText($local, $ns);
		if (is_null($title)) {
			$title = Title::newFromText(wfMsg('smw_ob_invalidtitle'), $ns);
		}
		$v = SMWDataValueFactory::newTypeIDValue('_wpg');
		$v->setValues($title->getDBkey(), $ns, $title->getArticleID());
		foreach($metadata as $mdProperty => $mdValue) {
			if (strpos($mdProperty, "_meta_") === 0) {
				$v->setMetadata(substr($mdProperty,6), explode("|||",$mdValue));
			}
		}
		return $v;

	}

	/**
	 * Serializes parameters and extraprintouts of SMWQuery.
	 * These informations are needed to generate a correct SPARQL query.
	 *
	 * @param SMWQuery $query
	 * @return string
	 */
	protected function serializeParams($query) {
		$result = "";
		$first = true;

		foreach ($query->getExtraPrintouts() as $printout) {
			if (!$first) $result .= "|";
			if ($printout->getData() == NULL) {
				$result .= "?=".$printout->getLabel();
			} else if ($printout->getData() instanceof Title) {
				$outputFormat = $printout->getOutputFormat() !== NULL ? "#".$printout->getOutputFormat() : "";
				$result .= "?".$printout->getData()->getDBkey().$outputFormat."=".$printout->getLabel();
			} else if ($printout->getData() instanceof SMWPropertyValue ) {
				$outputFormat = $printout->getOutputFormat() !== NULL ? "#".$printout->getOutputFormat() : "";
				$result .= "?".array_shift($printout->getData()->getDBkeys()).$outputFormat."=".$printout->getLabel();
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

		if ($query->mergeResults === false) {
			if (!$first) $result .= "|";
			$result .= 'merge=false';
			$first = false;
		}


		if (isset($query->params) && isset($query->params['dataspace'])) {
			if (!$first) $result .= "|";
			$result .= 'dataspace='.trim($query->params['dataspace']);
			$first = false;
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
	protected function containsPrintRequest($var_name, array & $prqs, & $query) {
		$contains = false;
		foreach($prqs as $po) {
			if ($query->fromASK && $po->getData() == NULL && $var_name == '_X_') {
				return true;
			}
			if ($po->getData() != NULL) {
				$label = $po->getData() instanceof Title ? $po->getData()->getDBkey() : array_shift($po->getData()->getDBkeys());
				$contains |= strtolower($label) == strtolower($var_name);
			}

		}
		return $contains;
	}

	
}





