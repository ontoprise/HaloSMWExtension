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
		$this->tsNamespace = TSNamespaces::getInstance();
	}



	///// Reading methods /////
	// delegate to default implementation

	function getSemanticData(SMWDIWikiPage $subject, $filter = false) {
		return $this->smwstore->getSemanticData($subject, $filter);
	}


	function getPropertyValues($subject, SMWDIProperty $property, $requestoptions = NULL) {
		return $this->smwstore->getPropertyValues($subject, $property, $requestoptions);
	}

	function getPropertySubjects(SMWDIProperty $property, $value, $requestoptions = NULL) {
		return $this->smwstore->getPropertySubjects($property, $value, $requestoptions);
	}

	function getAllPropertySubjects(SMWDIProperty $property, $requestoptions = NULL) {
		return $this->smwstore->getAllPropertySubjects($property, $requestoptions);
	}

	function getProperties(SMWDIWikiPage $subject, $requestoptions = NULL) {
		return $this->smwstore->getProperties($subject, $requestoptions);
	}

	function getInProperties(SMWDataItem $object, $requestoptions = NULL) {
		return $this->smwstore->getInProperties($object, $requestoptions);
	}

	function getSMWPropertyID(SMWDIProperty $property) {
		return $this->smwstore->getSMWPropertyID($property);
	}

	///// Writing methods /////

	function deleteSubject(Title $subject) {
		$this->smwstore->deleteSubject($subject);

		// make sure that TS is not update in maintenace mode
		if ( defined( 'DO_MAINTENANCE' ) && !defined('SMWH_FORCE_TS_UPDATE') ) {
			return;
		}

		$subject_iri = $this->tsNamespace->getFullIRI($subject);

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
			$sparulCommands[] = "DELETE MAPPING $subject_iri";

			$prop_ns = $this->tsNamespace->getNSPrefix(SMW_NS_PROPERTY);
			$naryPropFrag = "<$smwgTripleStoreGraph/$prop_ns";
			$sparulCommands[] = "DELETE FROM <$smwgTripleStoreGraph> { $subject_iri ?p ?b. ?b ?sub_prop ?v. } WHERE { $subject_iri ?p ?b. ?b ?sub_prop ?v. FILTER (isBlank(?b)) }";
			$sparulCommands[] = "DELETE FROM <$smwgTripleStoreGraph> { $subject_iri ?p ?o. }";
			if ($subject->getNamespace() == SMW_NS_PROPERTY) {
				$sparulCommands[] = TSNamespaces::getW3CPrefixes()."DELETE FROM <$smwgTripleStoreGraph> { ?s owl:onProperty $subject_iri. }";
			}
			if (isset($smwgEnableObjectLogicRules)) {
				// delete old rules...
				foreach($old_rules as $ruleID) {
					$sparulCommands[] = "DELETE MAPPING <$ruleID>";
					$sparulCommands[] = "DELETE RULE <$ruleID> FROM <$smwgTripleStoreGraph>";
				}
			}
			$con->connect();
			$con->update("/topic/WIKI.TS.UPDATE", $sparulCommands);
			$con->disconnect();
		} catch(Exception $e) {

		}
	}

	function doDataUpdate(SMWSemanticData $data) {
		wfProfileIn( "SMWTripleStore::doDataUpdate (SMWHalo)" );
		$this->smwstore->updateData($data);

		// update rules in internal store
		$subject = $data->getSubject()->getTitle();

		global $smwgEnableObjectLogicRules;
		if (isset($smwgEnableObjectLogicRules)) {
			$new_rules = self::$fullSemanticData->getRules();
			$old_rules = SMWRuleStore::getInstance()->getRules($subject->getArticleId());
			SMWRuleStore::getInstance()->clearRules($subject->getArticleId());
			SMWRuleStore::getInstance()->addRules($subject->getArticleId(), $new_rules);
		}
		// make sure that TS is not update in maintenace mode
		if ( defined( 'DO_MAINTENANCE' ) && !defined('SMWH_FORCE_TS_UPDATE') ) {
			wfProfileOut( "SMWTripleStore::doDataUpdate (SMWHalo)" );
			return;
		}
		$triples = array();

		$subject = $data->getSubject();
		$subject_iri = $this->tsNamespace->getFullIRI($subject->getTitle());
		// check for selective updates, ie. update only certain namespaces
		global $smwgUpdateTSOnNamespaces;

		if (isset($smwgUpdateTSOnNamespaces) && is_array($smwgUpdateTSOnNamespaces)) {
			if (!in_array($subject->getNamespace(), $smwgUpdateTSOnNamespaces)) {
				wfProfileOut( "SMWTripleStore::doDataUpdate (SMWHalo)" );
				return;
			}
		}

		// create triples from SemanticData object
		$this->handlePropertyAnnotations($data, $triples);
		$this->handleCategoryAnnotations($data, $triples);
		$this->handleRedirects($data, $triples);





		// connect to MessageBroker and send commands
		global $smwgMessageBroker, $smwgTripleStoreGraph;
		try {
			$con = TSConnection::getConnector();
			$sparulCommands = array();
			$sparulCommands[] = "DELETE MAPPING $subject_iri";
			if (!is_null($this->smwstore->getMapping())) {
				list($wikiURI, $tscURI) = $this->smwstore->getMapping();
				if (!is_null($tscURI) && !empty($tscURI)) {
					$sparulCommands[] = "INSERT MAPPING <".$wikiURI."> : <".$tscURI.">";
				}
			}
			$prefixes = TSNamespaces::$W3C_PREFIXES.TSNamespaces::$TSC_PREFIXES;
			$prop_ns = $this->tsNamespace->getNSPrefix(SMW_NS_PROPERTY);
			$naryPropFrag = "<$smwgTripleStoreGraph/$prop_ns";
			$sparulCommands[] = "DELETE FROM <$smwgTripleStoreGraph> { $subject_iri ?p ?b. ?b ?sub_prop ?v. } WHERE { $subject_iri ?p ?b. ?b ?sub_prop ?v. FILTER (isBlank(?b)) }";
			$sparulCommands[] = "DELETE FROM <$smwgTripleStoreGraph> { $subject_iri ?p ?o. }";

			$tripleSerialization = "";
			foreach($triples as $t) {
				$tripleSerialization .= implode(" ", $t);
				$tripleSerialization .= ". ";
			}
			$sparulCommands[] =  $prefixes."INSERT INTO <$smwgTripleStoreGraph> { ".$tripleSerialization." }";

			if (isset($smwgEnableObjectLogicRules)) {
				// delete old rules...
				foreach($old_rules as $ruleID) {
					$sparulCommands[] = "DELETE MAPPING <$ruleID>";
					$sparulCommands[] = "DELETE RULE <$ruleID> FROM <$smwgTripleStoreGraph>";
				}
				// ...and add new
				foreach($new_rules as $rule) {
					// The F-Logic parser does not accept linebreaks
					// => remove them
					list($ruleID, $ruleText, $native, $active, $type, $last_changed, $tsc_uri) = $rule;
					$ruleText = preg_replace("/[\n\r]/", " ", $ruleText);
					$nativeText = $native ? "NATIVE" : "";
					$activeText = !$active ? "INACTIVE" : "";
					if (!is_null($tsc_uri) && !empty($tsc_uri)) {
						$sparulCommands[] = "INSERT MAPPING <$ruleID> : <$tsc_uri>";
					}
					$sparulCommands[] = "INSERT $nativeText $activeText RULE <$ruleID> INTO <$smwgTripleStoreGraph> : \"".TSHelper::escapeForStringLiteral($ruleText)."\" TYPE \"$type\"";
				}
			}
			$con->connect();
			$con->update("/topic/WIKI.TS.UPDATE", $sparulCommands);
			$con->disconnect();
		} catch(Exception $e) {
			// print something??
		}
		wfProfileIn( "SMWTripleStore::doDataUpdate (SMWHalo)" );
	}

	/**
	 * Uses the semantic annotations in SMWSemanticData to create triples to insert into the TSC .
	 *
	 * @param SMWSemanticData $data
	 * @param array & $triples (subject, predicate, object) IRI or prefix form.
	 */
	private function handlePropertyAnnotations(SMWSemanticData $data, array & $triples) {
		//properties
		$bNodeCounter = 1;
		$subject = $data->getSubject();
		$subject_iri = $this->tsNamespace->getFullIRI($subject->getTitle());
		global $smwgTripleStoreGraph;
		foreach($data->getProperties() as $property) {
			$property_iri = $this->tsNamespace->getFullIRIFromDIProperty($property);
			$propertyValueArray = $data->getPropertyValues($property);
			$triplesFromHook = array();
			wfRunHooks('TripleStorePropertyUpdate', array(& $data, & $property, & $propertyValueArray, & $triplesFromHook));
			if ($triplesFromHook === false || count($triplesFromHook) > 0) {
				$triples = is_array($triplesFromHook) ? array_merge($triples, $triplesFromHook) : $triples;

				continue; // do not process normal triple generation, if hook provides triples.
			}

			// handle properties with special semantics
			if ($property->getKey() == "_TYPE") {
				// ingore. handeled by SMW_TS_SchemaContributor or SMW_TS_SimpleContributor
				continue;
			} elseif ($property->getKey() == "_CONV") {

				global $smwgContLang;
				$specialProperties = $smwgContLang->getPropertyLabels();
				$conversionPropertyLabel = str_replace(" ","_",$specialProperties['_CONV']);
				$property_iri = $this->tsNamespace->getFullIRIByName(SMW_NS_PROPERTY, $conversionPropertyLabel);
				if ( $subject->getNamespace() == SMW_NS_PROPERTY ) {
					foreach($propertyValueArray as $di) {
						if ( ( $di->getDIType() !== SMWDataItem::TYPE_STRING )) {
							continue; // ignore corrupted data and bogus inputs
						}
						$triples[] = array($subject_iri, $property_iri, "\"$factor ".trim($di->getString())."\"");

					}
				}
				continue;
			}

			elseif ($property->getKey() == "_INST") {
				// ingore. handeled by category section below
				continue;
			} elseif ($property->getKey() == "_SUBC") {
				// ingore. handeled by category section below
				continue;
			} elseif ($property->getKey() == "_REDI") {
				// ingore. handeled by redirect section below
				continue;
			} elseif ($property->getKey() == "_SUBP") {
				if ( $subject->getNamespace() == SMW_NS_PROPERTY ) {
					foreach($propertyValueArray as $value) {
						$superproperty_iri = $this->tsNamespace->getFullIRI($value->getTitle());
						$triples[] = array($subject_iri, "rdfs:subPropertyOf", $superproperty_iri);
					}

				}
				continue;
			} elseif ($property->getKey() == "_UNIT") {
				foreach($propertyValueArray as $value) {
					$string = $value->getString();
					$triples[] = array($subject_iri, $property_iri, "\"".TSHelper::escapeForStringLiteral($string)."\"^^xsd:string");
				}
				continue;
			} elseif ($property->getKey() == "_IMPO") {
				foreach($propertyValueArray as $value) {
					$string = $value->getString();
					$triples[] = array($subject_iri, $property_iri, "\"".TSHelper::escapeForStringLiteral($string)."\"^^xsd:string");
				}
				continue;
			} elseif ($property->getKey() == "_URI") {
				foreach($propertyValueArray as $value) {
					$uri = $value->getURI();
					$triples[] = array($subject_iri, $property_iri, "\"".TSHelper::escapeForStringLiteral($uri)."\"^^xsd:anyURI");
				}
				continue;
			} elseif ($property->getKey() == "_SERV") {
				foreach($propertyValueArray as $value) {
					$string = $value->getString();
					$triples[] = array($subject_iri, $property_iri, "\"".TSHelper::escapeForStringLiteral($string)."\"^^xsd:string");
				}
				continue;
			} elseif ($property->getKey() == "_PVAL") {
				foreach($propertyValueArray as $value) {
					$string = $value->getString();
					$triples[] = array($subject_iri, $property_iri, "\"".TSHelper::escapeForStringLiteral($string)."\"^^xsd:string");
				}
				continue;
			} elseif ($property->getKey() == "_ERRP") {
//				foreach($propertyValueArray as $value) {
//					$title = $value->getTitle();
//					$triples[] = array($subject_iri, $property_iri, "\"".TSHelper::escapeForStringLiteral($string)."\"^^xsd:string");
//				}
//				continue;
			} elseif ($property->getKey() == "_CONC") {
				foreach($propertyValueArray as $value) {
					$string = $value->getString();
					$triples[] = array($subject_iri, "tsctype:concept", "\"".TSHelper::escapeForStringLiteral($string)."\"^^xsd:string");
				}
				continue;
			}


			// there are other special properties which need not to be handled special
			// so they can be handled by the default machanism:

			foreach($propertyValueArray as $value) {


				if ($value->getDIType() == SMWDataItem::TYPE_BLOB) {
					$string = $value->getString();

					$triples[] = array($subject_iri, $property_iri, "\"".TSHelper::escapeForStringLiteral($string)."\"^^xsd:string");

				} elseif ($value->getDIType() == SMWDataItem::TYPE_WIKIPAGE) {
					$object_iri = $this->tsNamespace->getFullIRI($value->getTitle());
					$triples[] = array($subject_iri, $property_iri, $object_iri);

				} elseif ($value->getDIType() == SMWDataItem::TYPE_CONTAINER) {

					$sdata = $value->getSemanticData(); // SMWSemanticData object

					$properties  = $sdata->getProperties();

					foreach($properties as $p) {
						$values = $sdata->getPropertyValues($p);
						foreach($values as $v) {
								
							$xsdType = WikiTypeToXSD::getXSDTypeFromTypeID($v->getDIType());
							if ($v->getDIType() == SMWDataItem::TYPE_WIKIPAGE) {
								$object = $this->tsNamespace->getFullIRI(Title::newFromDBkey($v->getTitle()));
							} if ($v->getDIType() == SMWDataItem::TYPE_PROPERTY) {
								$object = $this->tsNamespace->getFullIRI(Title::newFromDBkey($v->getDiWikiPage()->getTitle()));
							}else {
								$string = TSHelper::serializeDataItem($v);
								$object = "\"".TSHelper::escapeForStringLiteral($string)."\"^^$xsdType";
							}
							$triples[] = array("_:".$bNodeCounter, $this->tsNamespace->getFullIRI($p->getDiWikiPage()->getTitle()), $object);

						}
					}

					$triples[] = array($subject_iri, $property_iri, "_:".$bNodeCounter);
					$bNodeCounter++;

				} else {
					// primitive value (including measures)
					if ($value->getDIType() == SMWDataItem::TYPE_NUMBER) {
						// check if it is a measure
						$factors = smwfGetStore()->getPropertyValues( $property->getDiWikipage(), new SMWDIProperty( '_CONV' ) );

						if (count($factors) === 0) {
							// number
							$string = TSHelper::serializeDataItem($value);
							$triples[] = array($subject_iri, $property_iri, "\"".TSHelper::escapeForStringLiteral($string)."\"^^xsd:double");
						} else {
							// measure
							foreach($factors as $di) {
								if ( ( $di->getDIType() !== SMWDataItem::TYPE_STRING )) {
									continue; // ignore corrupted data and bogus inputs
								}
								$string = explode(" ",trim($di->getString()));
								$number = reset($string);
								if ($number == 1) {
									$numericValue = $value->getNumber();
									$baseunit = next($string); //FIXME: make more robust
									$triples[] = array($subject_iri, $property_iri, "\"".TSHelper::escapeForStringLiteral($numericValue." ".$baseunit)."\"^^tsctype:unit");
								}
							}
						}
					} else {
						$xsdType = WikiTypeToXSD::getXSDTypeFromTypeID($value->getDIType());
						$string = TSHelper::serializeDataItem($value);
						$triples[] = array($subject_iri, $property_iri, "\"".TSHelper::escapeForStringLiteral($string)."\"^^$xsdType");
					}


				}
			}
				



		}
	}

	/**
	 * Uses the category annotations to create triples to insert into the TSC .
	 *
	 * @param SMWSemanticData $data
	 * @param array & $triples (subject, predicate, object) IRI or prefix form.
	 */
	private function handleCategoryAnnotations(SMWSemanticData $data, array & $triples) {
		// categories
		$subject = $data->getSubject();
		$subject_iri = $this->tsNamespace->getFullIRI($subject->getTitle());
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
				$supercategory_iri = $this->tsNamespace->getFullIRI($c);
				$triples[] = array($subject_iri, "rdfs:subClassOf", $supercategory_iri);
			}

			if (count($categories) == 0) {
				// if there are no supercategories create a statement that
				// indicates that this is a class
				$triples[] = array($subject_iri, "rdf:type", "owl:Class");
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
				$membercategory_iri = $this->tsNamespace->getFullIRI($c);
				$triples[] = array($subject_iri, "rdf:type", $membercategory_iri);
			}
		}
	}

	/**
	 * Uses the redirects to create triples to insert into the TSC .
	 *
	 * @param SMWSemanticData $data
	 * @param array & $triples (subject, predicate, object) IRI or prefix form.
	 */
	private function handleRedirects(SMWSemanticData $data, array & $triples) {
		// redirects
		$subject = $data->getSubject();
		$subject_iri = $this->tsNamespace->getFullIRI($subject->getTitle());
		$redirects = self::$fullSemanticData->getRedirects();

		foreach($redirects as $r) {
			switch($subject->getNamespace()) {
				case SMW_NS_PROPERTY: $prop = "owl:equivalentProperty";
				case NS_CATEGORY: $prop = "owl:equivalentClass";
				case NS_MAIN: $prop = "owl:sameAs";
				default: continue;
			}
			$redirect_iri = $this->tsNamespace->getFullIRI($r);

			$triples[] = array($subject_iri, $prop, $redirect_iri);
		}
	}


	function changeTitle(Title $oldtitle, Title $newtitle, $pageid, $redirid=0) {
		$this->smwstore->changeTitle($oldtitle, $newtitle, $pageid, $redirid);

		// make sure that TS is not update in maintenace mode
		if ( defined( 'DO_MAINTENANCE' ) && !defined('SMWH_FORCE_TS_UPDATE') ) {
			return;
		}
		$old_iri = $this->tsNamespace->getFullIRI($oldtitle);
		$new_iri = $this->tsNamespace->getFullIRI($newtitle);

		$sparulCommands = array();

		// update local rule store
		global $smwgEnableObjectLogicRules;
		if (isset($smwgEnableObjectLogicRules)) {
			$modifiedRules = SMWRuleStore::getInstance()->updateRules($redirid, $pageid, $newtitle);
			foreach($modifiedRules as $r) {
				list($old_rule_uri, $new_rule_uri) = $r;
				$sparulCommands[] = "MODIFY MAPPING $old_rule_uri : $new_rule_uri";
			}
		}

		// update triple store
		global $smwgMessageBroker, $smwgTripleStoreGraph;
		try {
			$con = TSConnection::getConnector();

			$sparulCommands[] = "MODIFY MAPPING $old_iri : $new_iri";

			$prop_ns = $this->tsNamespace->getNSPrefix(SMW_NS_PROPERTY);
			$naryPropFrag = "<$smwgTripleStoreGraph/$prop_ns";

			$sparulCommands[] = TSNamespaces::getW3CPrefixes()."MODIFY <$smwgTripleStoreGraph> DELETE  { $old_iri ?p ?o. } INSERT { $new_iri ?p ?o. }";
			$sparulCommands[] = TSNamespaces::getW3CPrefixes()."MODIFY <$smwgTripleStoreGraph> DELETE  { ?s $old_iri ?o. } INSERT { ?s $new_iri ?o. }";
			$sparulCommands[] = TSNamespaces::getW3CPrefixes()."MODIFY <$smwgTripleStoreGraph> DELETE  { ?s ?p $old_iri. } INSERT { ?s ?p $new_iri. }";
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
			SMWQMQueryManagementHandler::getInstance()->storeQueryMetadata($query);
			$qrc = new SMWQRCQueryResultsCache();
			return $qrc->getQueryResult($query);
		} else {
			return $this->doGetQueryResult($query);
		}
	}

	function doGetQueryResult(SMWQuery $query) {
		global $wgServer, $wgScript, $smwgWebserviceUser, $smwgWebservicePassword, $smwgDeployVersion;

		wfProfileIn( "SMWTripleStore::doGetQueryResult (SMWHalo)" );
		// make sure that TS is not queried in maintenace mode
		if ( defined( 'DO_MAINTENANCE' )  && !defined('SMWH_FORCE_TS_UPDATE') ) {
			return $this->smwstore->getQueryResult($query);
		}

		$toTSC = false; // redirects a normal ASK query to the TSC
		if (!($query instanceof SMWSPARQLQuery)) {
			// normal query from #ask
			// check source parameter and redirect to TSC's impl. if necessary.
			if (isset($query->params) && isset($query->params['source'])) {
				$query->fromASK = true;
				$query->mainLabelMissing = isset($query->params['mainlabel']) && $query->params['mainlabel']== '-';
				$toTSC = $query->params['source'] == 'tsc';
			}
		}

		// check resultintegration status and add metadata constraint for source if is not explicitly defined.
		// without metadata there is no resultintegration because the results can not be assigned to a particular datasource
		if (isset($query->params)
		&& is_array($query->params)
		&& array_key_exists('resultintegration', $query->params)
		&& !array_key_exists('metadata', $query->params)) {
			$query->params['metadata'] = "SWP2_AUTHORITY_ID";
		}


		if ($query instanceof SMWSPARQLQuery || $toTSC) {
			// handle only SPARQL queries and delegate all others
			//          wfRunHooks('RewriteSparqlQuery', array(&$query) );

			if ($query->getQueryString() == "") {
				$sqr = new SMWHaloQueryResult(array(), $query, array(), $this, false);
				$sqr->addErrors(array(wfMsg('smw_tsc_query_not_allowed')));
				return $sqr;
			}
			try {
				global $smwgTripleStoreGraph;
				$con = TSConnection::getConnector();
				$con->connect();

				// if graph parameter is set but empty or set and null, no wikigraph is given
				$wikigraph = array_key_exists('graph', $query->params) && ($query->params['graph'] == 'null' || empty($query->params['graph'])) ? '' : $smwgTripleStoreGraph;
				$response = $con->query($query->getQueryString(), $this->serializeParams($query), $wikigraph);

				global $smwgSPARQLResultEncoding;
				// PHP strings are always interpreted in ISO-8859-1 but may be actually encoded in
				// another charset.
				if (isset($smwgSPARQLResultEncoding) && $smwgSPARQLResultEncoding == 'UTF-8') {
					$response = utf8_decode($response);
				}

				// Allow extensions to transform the query result before it is
				// parsed.
				wfProfileIn( "SMWTripleStore::doGetQueryResult (SMWHalo): Hook 'ProcessSPARQLXMLResults'" );
				wfRunHooks('ProcessSPARQLXMLResults', array(&$query, &$response) );
				wfProfileOut( "SMWTripleStore::doGetQueryResult (SMWHalo): Hook 'ProcessSPARQLXMLResults'" );

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
							header("Cache-Control: no-cache");
							header('Pragma: no-cache');
							$sqr->addErrors(array(wfMsg('smw_ts_notconnected', $smwgWebserviceEndpoint)));

						} else {
							header("Cache-Control: no-cache");
							header('Pragma: no-cache');
							$sqr->addErrors(array($e->getMessage()));
						}
						// in case of an error
						// redirect query to the default SMW implementation
						// currently deactivated
						//return $this->smwstore->getQueryResult($query);

				}
				return $sqr;
			}


			switch ($query->querymode) {

				case SMWQuery::MODE_COUNT:
					$queryResult = $queryResult->getCount();
					break;
				default:
					if (is_array($queryResult)) {
						foreach ($queryResult as $key => $qr) {
							wfProfileIn( "SMWTripleStore::doGetQueryResult (SMWHalo): Hook 'ProcessQueryResults'" );
							wfRunHooks('ProcessQueryResults', array(&$query, &$queryResult[$key]));
							wfProfileOut( "SMWTripleStore::doGetQueryResult (SMWHalo): Hook 'ProcessQueryResults'" );
						}
					} else {
						wfProfileIn( "SMWTripleStore::doGetQueryResult (SMWHalo): Hook 'ProcessQueryResults'" );
						wfRunHooks('ProcessQueryResults', array(&$query, &$queryResult) );
						wfProfileOut( "SMWTripleStore::doGetQueryResult (SMWHalo): Hook 'ProcessQueryResults'" );
					}
					break;
			}
			wfProfileOut( "SMWTripleStore::doGetQueryResult (SMWHalo)" );
			return $queryResult;

		} else {
			// redirect query to the default SMW implementation
			$qresult = $this->smwstore->getQueryResult($query);
			wfProfileOut( "SMWTripleStore::doGetQueryResult (SMWHalo)" );
			return $qresult;
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

		try {
			$con = TSConnection::getConnector();
			$commandText = smwf_ts_getSyncCommands();
			$con->connect();
			$con->update("/topic/WIKI.TS.UPDATE", explode("\n", $commandText));
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


	public function getSMWPageIDandSort( $title, $namespace, $iw, &$sort, $canonical ) {
		return $this->smwstore->getSMWPageIDandSort($title, $namespace, $iw, $sort, $canonical);
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
		$dom->registerXPathNamespace("sparqlxml", "http://www.w3.org/2005/sparql-results#");
		if($dom === FALSE) return new SMWHaloQueryResult(array(), $query, array(), $this);

		$qResultSet = array();
		$sources = $dom->xpath('//sparqlxml:source');
		$sourcesSet = array();
		if (!is_null($sources) && $sources != '') {
			foreach($sources as $s) {
				$sourcesSet[] = (string) $s;
			}
		}

		// result integration parameter
		if (array_key_exists('resultintegration', $query->params) && $query->params['resultintegration'] == 'integrated') {
			$sourcesSet = array(); // show as if it was one source
		} else if (array_key_exists('resultintegration', $query->params) && $query->params['resultintegration'] == 'preferred') {
			// use first source which appears in result
			$dataspaces = explode(",",$query->params['dataspace']);
			foreach($dataspaces as $ds) {
				if (in_array($ds, $sourcesSet)) {
					$sourcesSet=array($ds);
					break;
				}
			}
		} // in any other case display the source separately

		if (count($sourcesSet) === 0) $sourcesSet[]='tsc'; // add at least 1 source

		foreach($sourcesSet as $s) {

			$resultFilter = $s == 'tsc' ? '' : '[@source="'.$s.'"]';
			$variables = $dom->xpath('//sparqlxml:variable');
			$results = $dom->xpath('//sparqlxml:result'.$resultFilter);


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
					if ($data == NULL) {

						// special case: Category printout
						global $wgLang;
						if ($pr->getLabel() == 'Category' || $pr->getLabel() == $wgLang->getNsText(NS_CATEGORY)) {
							if (array_key_exists('Category', $mapPRTOColumns)) {
								$mapPRTOColumns['Category'][] = $index;
							} else {
								$mapPRTOColumns['Category'] = array($index);
							}

							$prs[] = $pr;
							$index++;
							continue;
						}

						// special case: main column printout
						$hasMainColumn = true;
						if (in_array('_X_', $variableSet)) { // x is missing for INSTANCE queries
							$mapPRTOColumns['_X_'] = array($index);
							$prs[] = $pr;
							$index++;
						}

					} else  {

						// all other printouts
						if ( $data instanceof Title) {
							$label = $data->getDBkey();
						} else {
							// SMW_DV_Property
							$label = $data->getDataItem()->getKey();
						}
						if (array_key_exists($label, $mapPRTOColumns)) {
							$mapPRTOColumns[$label][] = $index;
						} else {
							$mapPRTOColumns[$label] = array($index);
						}

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
						if ($data instanceof Title) {
							$label =  $data->getDBkey();
						} else {
							//SMWDIProperty
							$label = $data->getKey();
						}
						if (array_key_exists($label, $mapPRTOColumns)) {
							$mapPRTOColumns[$label][] = $index;
						} else {
							$mapPRTOColumns[$label] = array($index);
						}
						$rewritten_pr = $this->rewritePrintrequest($pr);
						$prs[] = $rewritten_pr;
						$index++;
					}

				}
			}



			// generate PrintRequests for all bindings (if they do not exist already)
 
			foreach ($variables as $var) {
				
				$var_name = ucfirst((string) $var->attributes()->name);

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
				
				$prs[] = new SMWPrintRequest(SMWPrintRequest::PRINT_PROP, str_replace("_"," ",$sel_var), $data);

				if (array_key_exists($var_name, $mapPRTOColumns)) {
					$mapPRTOColumns[$var_name][] = $index;
				} else {
					$mapPRTOColumns[$var_name] = array($index);
				}

				$index++;
			}


			// create and add result rows
			// iterate over SPARQL-XML result nodes and add an SMWResultArray object for each result
			$qresults = array();
			$rowIndex = 0;
			foreach ($results as $r) {
				
				$row = array();
				$bindingNodeIndex = 0; // column = n-th XML binding node

				// reset column arrays
				foreach($mapPRTOColumns as $pr => $column) reset($mapPRTOColumns[$pr]);

				$bindingSet = $r->children(); // $bindingSet->binding denote all binding nodes

				// find result column and store result page in $resultInstance variable
				$resultInstance = NULL;
				foreach ($bindingSet->binding as $b) {
					$var_name = ucfirst((string) $bindingSet[$bindingNodeIndex]->attributes()->name);
					if (!$query->mainLabelMissing && $var_name == '_X_') {
						$resultColumn = current($mapPRTOColumns[$var_name]);
						next($mapPRTOColumns[$var_name]);

						$allValues = array();
						$this->parseBindungs($b, $var_name, $prs[$resultColumn], $allValues);
						// what happens if first column is merged??
						$firstValue = count($allValues) > 0 ? reset($allValues) : NULL;
						if (!($firstValue instanceof SMWWikiPageValue)) {
							// if resultInstance is not a wiki page title, create a dummy.
							$firstValue = new SMWDIWikiPage("dummy", NS_MAIN, "");
						}
						$resultInstance = $firstValue;
						break;
					}
				}

				if (is_null($resultInstance)) {
					$resultInstance = new SMWDIWikiPage("dummy", NS_MAIN, "");
				}

				// reset column arrays
				foreach($mapPRTOColumns as $pr => $column) reset($mapPRTOColumns[$pr]);

				// create result row. iterate over variable set and convert binding nodes to SMWDataValue objects
				$maxResultsInColumn = 0;
				$totalResults = 0;
				foreach ($variableSet as $var) {
					$var = ucfirst($var);
					if ($bindingNodeIndex < count($bindingSet)) {
						$b = $bindingSet[$bindingNodeIndex];
						$varOfBinding = ucfirst((string) $bindingSet[$bindingNodeIndex]->attributes()->name);
					} else {
						$varOfBinding = NULL;
					}

					if (is_null($varOfBinding) || $varOfBinding !== $var) {
						// missing binding (due to OPTIONAL)
						// add null value
						$varOfBinding = $var;
						$resultColumn = current($mapPRTOColumns[$varOfBinding]);
						next($mapPRTOColumns[$varOfBinding]);

						$allValues = array(); // a NULL value

						$row[$resultColumn] = new SMWHaloResultArray($resultInstance, $prs[$resultColumn], $this, $allValues);

						continue;
					}

					// ignore main variable if not displayed
					if (!$hasMainColumn && $varOfBinding == '_X_') {
						$bindingNodeIndex++;
						continue;
					}


					// get current result column of the variable
					$resultColumn = current($mapPRTOColumns[$varOfBinding]);
					next($mapPRTOColumns[$varOfBinding]);

					$allValues = array();
					// note: ignore bnodes
					$this->parseBindungs($b, $varOfBinding, $prs[$resultColumn], $allValues);

					$bindingNodeIndex++;
					$row[$resultColumn] = new SMWHaloResultArray($resultInstance, $prs[$resultColumn], $this, $allValues);
					$maxResultsInColumn = max(array($maxResultsInColumn, count($allValues)));
				}
				$rowIndex++;
				ksort($row);
				$qresults[] = $row;
				$totalResults += $maxResultsInColumn;
			}

			// create query result object
			$queryResult = new SMWHaloQueryResult($prs, $query, $qresults, $this, ($totalResults == $query->getLimit()));
			$qResultSet[$s] = $queryResult;
		}
		// consider multiple results
		return count($qResultSet) == 1 ? reset($qResultSet) : $qResultSet;
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
		} else {
			$titleText = $data->getDBkey();
		}
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

		$plainFormat = trim($pr->getOutputFormat()) == '-';

		foreach($bindingsChildren->uri as $sv) {
			$uris[] = array((string) $sv, $sv->metadata);
		}
		if (!empty($uris)) {
			foreach($uris as $uri) {
				list($sv, $metadata) = $uri;

				if ($sv == TSNamespaces::$RDF_NS."type") {
					$allValues[] = SMWDIProperty::newFromUserLabel('_INST');
				} else {
					$title = TSHelper::getTitleFromURI($sv, false);

					if (is_null($title) || $title instanceof Title) {
						if ($plainFormat) {
							$allValues[] = $this->createSMWDataItem(NULL, $title->getPrefixedText(), TSNamespaces::$XSD_NS."string", $metadata);
						} else {
							$allValues[] = $this->createSMWPageDataItem($title, $metadata);
						}
					} else {
						// external URI
						if ($plainFormat) {
							$v = $this->createSMWDataItem(NULL, $sv, TSNamespaces::$XSD_NS."string", $metadata);
						} else {
							global $lodgNEPEnabled;
							if ($lodgNEPEnabled) {
								// in case the NEP feature is active, create integration links.
								// guess local name
								$localname = TSHelper::convertURIToLocalName($sv);
								$v = $this->createIntegrationLinkDataItem($localname, $localname, $sv, $metadata);
							} else {
								// normal URI ouput
								$v = $this->createSMWDataItem(NULL, $sv, TSNamespaces::$XSD_NS."anyURI", $metadata);
							}

						}
						$allValues[] = $v;

					}
				}
			}
		} else {
			$literals = array();
			foreach($bindingsChildren->literal as $sv) {
				$literals[] = array((string) $sv, (string) $sv->attributes()->datatype, $sv->metadata);
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
				foreach($literals as $literal) {

					list($literalValue, $literalType, $metadata) = $literal;
					$property = !is_null($pr) ? $pr->getData() : NULL;
					if ($plainFormat) {

						$value = $this->createSMWDataItem(NULL, $literalValue, TSNamespaces::$XSD_NS."string", $metadata);
					} else {
						$value = $this->createSMWDataItem($property, $literalValue, $literalType, $metadata);

					}
					$allValues[] = $value;
				}
			}


		}
	}






	/**
	 *
	 * Creates primitive SMWDataItem object (ie. no SMWDIWikiPage). 
	 *
	 * @param $property
	 * @param $literalValue
	 * @param $literalType
	 * @param $metadata
	 */
	protected function createSMWDataItem($property, $literalValue, $literalType, $metadata) {
		if (!empty($literalValue)) {

			// create SMWDataValue either by property or if that is not possible by the given XSD type
			if ($property instanceof SMWPropertyValue ) {
				$propertyTitle = $property->getWikiPageValue()->getTitle();
				if (!$propertyTitle->exists()) {
					// fallback if property does not exist, then use tyoe
					$value = SMWDataValueFactory::newTypeIDValue(WikiTypeToXSD::getWikiType($literalType));
				} else {
					$value = SMWDataValueFactory::newPropertyObjectValue($property, $literalValue);
				}
			} else {
					
				$value = SMWDataValueFactory::newTypeIDValue(WikiTypeToXSD::getWikiType($literalType));
			}

			// set actual value
			if ($value->getTypeID() == '_dat') {
					
				// normalize dateTime
				if ($literalValue != '') {

					// remove time zone (if existing)
					if (preg_match('/[+-]\d\d:\d\d/', $literalValue) > 0) {
						$literalValue = substr($literalValue, 0, strlen($literalValue)-6);
					}

					// remove miliseconds (if existing)
					if (substr($literalValue, -4) == '.000') {
						$literalValue = substr($literalValue, 0, strlen($literalValue)-4);
					}

					// remove time (if it is 00:00:00, in this case only the date is usually significant)
					if (substr($literalValue, -9) == 'T00:00:00') {
						$literalValue = substr($literalValue, 0, strpos($literalValue, "T"));
					}

					// hack: can not use setUserValue for SMW_DV_Time for some reason.
					if ($property instanceof SMWPropertyValue ) {
						$propertyTitle = $property->getWikiPageValue()->getTitle();
							
						if (!$propertyTitle->exists()) {
							$valueTemp = SMWDataValueFactory::newTypeIDValue('_dat', str_replace("-","/",$literalValue));
						} else {
							$valueTemp = SMWDataValueFactory::newPropertyObjectValue($property, str_replace("-","/",$literalValue));
						}
					} else {
						$valueTemp = SMWDataValueFactory::newTypeIDValue('_dat', str_replace("-","/",$literalValue));
					}
					$value->setDataItem($valueTemp->getDataItem());
				}
			} else if ($value->getTypeID() == '_ema'
			|| $value->getTypeID() == '_tel'
			|| $value->getTypeID() == '_num'
			|| $value->getTypeID() == '_uri' ) {
				// set some types as DBkeys for normalization
				$value->setUserValue($literalValue);
			} else {
				// all others, set as user type
				$value->setUserValue($literalValue);
			}
		} else {

			// literal value is empty
			if ($property instanceof SMWPropertyValue ) {
				$value = SMWDataValueFactory::newPropertyObjectValue($property);
			} else {
				$value = SMWDataValueFactory::newTypeIDValue(is_null($property) ? '_str' :  '_wpg');

			}

		}
		// set metadata
		$di = $value->getDataItem();
		TSHelper::setMetadata($di, $metadata);
		return $di;
	}

	/**
	 * Creates SMWDIWikiPage object.
	 *
	 * @param string $uri Full URI
	 * @param hash array $metadata (propertyName=>value)
	 * @param string $nsFragment NS-prefix
	 * @param int $ns Namespace index
	 * @return SMWWikiPageValue
	 */
	protected function createSMWPageDataItem($title, $metadata) {

		$v = SMWDataValueFactory::newTypeIDValue('_wpg');
		if (is_null($title)) {
			$title = Title::newFromText(wfMsg('smw_ob_invalidtitle'), $ns);
		}
		$v->setValues($title->getDBkey(), $title->getNamespace(), $title->getArticleID(), false, '', $title->getFragment());
		$di = $v->getDataItem();
		TSHelper::setMetadata($di, $metadata);
		return $di;

	}

	/**
	 * Creates an integration link URI DataValue object, ie. a redlink which has an URI parameter.
	 * It can be used by the NEP mechanism.
	 *
	 * @param $dbkey
	 * @param $caption
	 * @param $uri
	 */
	protected function createIntegrationLinkDataItem($dbkey, $caption, $uri, $metadata) {
		global $wgServer, $wgArticlePath;
		$value = $wgServer.$wgArticlePath;
		$dbkey = urldecode($dbkey);

		$value = str_replace('$1', ucfirst($dbkey), $value);
		$value .= '?action=edit&uri='.urlencode($uri).'&redlink=1';
		$value = SMWDataValueFactory::newTypeIDValue('_ili', $value, str_replace("_", " ",$dbkey));
		$di = $value->getDataItem();
		TSHelper::setMetadata($di, $metadata);
		return $di;
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

		// serializes printouts
		foreach ($query->getExtraPrintouts() as $printout) {
			if (!$first) $result .= "|";
			if ($printout->getData() == NULL) {
				$label = $printout->getLabel();
				global $wgContLang;
				if ($label == $wgContLang->getNsText(NS_CATEGORY)) {
					$result .= "?$label"; // category printout
				} else {
					$result .= "?=$label";
				}
			} else if ($printout->getData() instanceof Title) {
				$outputFormat = $printout->getOutputFormat() !== NULL ? "#".$printout->getOutputFormat() : "";
				$result .= "?".$printout->getData()->getDBkey().$outputFormat."=".$printout->getLabel();
			} else if ($printout->getData() instanceof SMWPropertyValue ) {

				$outputFormat = $printout->getOutputFormat() !== NULL ? "#".$printout->getOutputFormat() : "";
				$dbkey = $printout->getData()->getDataItem()->getKey();
				$result .= "?".$dbkey.$outputFormat."=".$printout->getLabel();
			}
			$first = false;
		}

		// limit
		if ($query->getLimit() != NULL) {
			if (!$first) $result .= "|";
			$result .= "limit=".$query->getLimit();
			$first = false;
		}

		// offset
		if ($query->getOffset() != NULL) {
			if (!$first) $result .= "|";
			$result .= "offset=".$query->getOffset();
			$first = false;
		}

		// sort
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

		// merge: Note that default value is "true" for ASK but "false" for SPARQL queries
		if (isset($query->fromASK) && $query->fromASK == true) {
			if (!$first) $result .= "|";
			if (!array_key_exists('merge', $query->params)) {
				$value = "true";
			} else {
				$value = ($query->params['merge'] == "true") ? "true" : "false";
			}
			$result .= 'merge='.$value;
			$first = false;
		} else {
			if (!$first) $result .= "|";
			if (!array_key_exists('merge', $query->params)) {
				$value = "false";
			} else {
				$value = ($query->params['merge'] == "true") ? "true" : "false";
			}
			$result .= 'merge='.$value;
			$first = false;
		}

		if (isset($query->params)) {
			// Serialize all other additional parameters
			foreach ($query->params as $param => $value) {
				if ($param == 'sort' || $param == 'order' || $param == 'limit' || $param == 'offset' || $param == 'merge') continue;
				if (!$first) $result .= "|";
				$result .= "$param=".trim($value);
				$first = false;
			}
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
				if ($po->getData() instanceof Title) {
					$label = $po->getData()->getDBkey() ;
				} else {
					$label = $po->getData()->getDataItem()->getKey();
					
				}
				$contains |= strtolower($label) == strtolower($var_name);
			} else {
				$label = $po->getLabel();
				$contains |= strtolower($label) == strtolower($var_name);
			}

		}
		return $contains;
	}

}





