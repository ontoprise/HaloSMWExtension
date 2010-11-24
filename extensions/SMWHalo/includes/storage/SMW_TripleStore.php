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
			$prop_ns = $this->tsNamespace->getNSPrefix(SMW_NS_PROPERTY);
			$naryPropFrag = "<$smwgTripleStoreGraph/$prop_ns";
			$sparulCommands[] = "DELETE FROM <$smwgTripleStoreGraph> { $subject_iri ?p ?b. ?b $naryPropFrag#_1> ?v1. ?b $naryPropFrag#_2> ?v2. ?b $naryPropFrag#_3> ?v3. ?b $naryPropFrag#_4> ?v4. ?b $naryPropFrag#_5> ?v5.}";
			$sparulCommands[] = "DELETE FROM <$smwgTripleStoreGraph> { $subject_iri ?p ?o. }";
			if ($subject->getNamespace() == SMW_NS_PROPERTY) {
				$sparulCommands[] = TSNamespaces::getW3CPrefixes()."DELETE FROM <$smwgTripleStoreGraph> { ?s owl:onProperty $subject_iri. }";
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

		// make sure that TS is not update in maintenace mode
		if ( defined( 'DO_MAINTENANCE' ) && !defined('SMWH_FORCE_TS_UPDATE') ) {
			return;
		}
		$triples = array();

		$subject = $data->getSubject();
		$subject_iri = $this->tsNamespace->getFullIRI($subject->getTitle());
		// check for selective updates, ie. update only certain namespaces
		global $smwgUpdateTSOnNamespaces;

		if (isset($smwgUpdateTSOnNamespaces) && is_array($smwgUpdateTSOnNamespaces)) {
			if (!in_array($subject->getNamespace(), $smwgUpdateTSOnNamespaces)) {
				return;
			}
		}

		// create triples from SemanticData object
		$this->handlePropertyAnnotations($data, $triples);
		$this->handleCategoryAnnotations($data, $triples);
		$this->handleRedirects($data, $triples);

		// create rules
		$subject = $data->getSubject()->getTitle();

		global $smwgEnableObjectLogicRules;
		if (isset($smwgEnableObjectLogicRules)) {
			$new_rules = self::$fullSemanticData->getRules();
			$old_rules = SMWRuleStore::getInstance()->getRules($subject->getArticleId());
			SMWRuleStore::getInstance()->clearRules($subject->getArticleId());
			SMWRuleStore::getInstance()->addRules($subject->getArticleId(), $new_rules);
		}


		// connect to MessageBroker and send commands
		global $smwgMessageBroker, $smwgTripleStoreGraph;
		try {
			$con = TSConnection::getConnector();
			$sparulCommands = array();
			$prefixes = TSNamespaces::$W3C_PREFIXES.TSNamespaces::$TSC_PREFIXES;
			$prop_ns = $this->tsNamespace->getNSPrefix(SMW_NS_PROPERTY);
			$naryPropFrag = "<$smwgTripleStoreGraph/$prop_ns";
			$sparulCommands[] = "DELETE FROM <$smwgTripleStoreGraph> { $subject_iri ?p ?b. ?b $naryPropFrag#_1> ?v1. ?b $naryPropFrag#_2> ?v2. ?b $naryPropFrag#_3> ?v3. ?b $naryPropFrag#_4> ?v4. ?b $naryPropFrag#_5> ?v5.}";
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
					$sparulCommands[] = "DELETE RULE <$ruleID> FROM <$smwgTripleStoreGraph>";
				}
				// ...and add new
				foreach($new_rules as $rule) {
					// The F-Logic parser does not accept linebreaks
					// => remove them
					list($ruleID, $ruleText, $native, $active, $type) = $rule;
					$ruleText = preg_replace("/[\n\r]/", " ", $ruleText);
					$nativeText = $native ? "NATIVE" : "";
					$activeText = !$active ? "INACTIVE" : "";
					$sparulCommands[] = "INSERT $nativeText $activeText RULE <$ruleID> INTO <$smwgTripleStoreGraph> : \"".TSHelper::escapeForStringLiteral($ruleText)."\" TYPE \"$type\"";
				}
			}
			$con->connect();
			$con->update("/topic/WIKI.TS.UPDATE", $sparulCommands);
			$con->disconnect();
		} catch(Exception $e) {
			// print something??
		}
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
		foreach($data->getProperties() as $key => $property) {
			$property_iri = $this->tsNamespace->getFullIRIFromProperty($property);
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
				$prop_ns = $this->tsNamespace->getNSPrefix(SMW_NS_PROPERTY);
				$naryPropFrag = "<$smwgTripleStoreGraph/$prop_ns";
				global $smwgContLang;
				$specialProperties = $smwgContLang->getPropertyLabels();
				$conversionPropertyLabel = str_replace(" ","_",$specialProperties['_CONV']);
				if ( $subject->getNamespace() == SMW_NS_TYPE ) {
					foreach($propertyValueArray as $value) {
						// parse conversion annotation format
						$dbkeys = $value->getDBkeys();
						$measures = explode(",", array_shift($dbkeys));

						// parse linear factor followed by (first) unit
						$firstMeasure = reset($measures);
						$indexOfWhitespace = strpos($firstMeasure, " ");
						if ($indexOfWhitespace === false) continue; // not a valid measure, ignore
						$factor = trim(substr($firstMeasure, 0, $indexOfWhitespace));
						$unit = trim(substr($firstMeasure, $indexOfWhitespace));
						$triples[] = array($subject_iri, "$naryPropFrag#$conversionPropertyLabel>", "\"$factor $unit\"");

						// add all aliases for this conversion factor using the same factor
						$nextMeasure = next($measures);
						while($nextMeasure !== false) {
							$nextMeasure = str_replace('"', '\"', $nextMeasure);
							$triples[] = array($subject_iri, "$naryPropFrag#$conversionPropertyLabel>", "\"$factor ".trim($nextMeasure)."\"");
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
						$superproperty_iri = $this->tsNamespace->getFullIRI($value->getTitle());
						$triples[] = array($subject_iri, "rdfs:subPropertyOf", $superproperty_iri);
					}

				}
				continue;
			} elseif ($property->getPropertyID() == "_UNIT") {
				foreach($propertyValueArray as $value) {
					$dbkeys = $value->getDBkeys();
					$firstValue = array_shift($dbkeys);
					$triples[] = array($subject_iri, $property_iri, "\"".TSHelper::escapeForStringLiteral($firstValue)."\"^^xsd:string");
				}
				continue;
			} elseif ($property->getPropertyID() == "_IMPO") {
				foreach($propertyValueArray as $value) {
					$dbkeys = $value->getDBkeys();
					$firstValue = array_shift($dbkeys);
					$triples[] = array($subject_iri, $property_iri, "\"".TSHelper::escapeForStringLiteral($firstValue)."\"^^xsd:string");
				}
				continue;
			} elseif ($property->getPropertyID() == "_URI") {
				foreach($propertyValueArray as $value) {
					$dbkeys = $value->getDBkeys();
					$firstValue = array_shift($dbkeys);
					$triples[] = array($subject_iri, $property_iri, "\"".TSHelper::escapeForStringLiteral($firstValue)."\"^^xsd:anyURI");
				}
				continue;
			} elseif ($property->getPropertyID() == "_SERV") {
				foreach($propertyValueArray as $value) {
					$dbkeys = $value->getDBkeys();
					$firstValue = array_shift($dbkeys);
					$triples[] = array($subject_iri, $property_iri, "\"".TSHelper::escapeForStringLiteral($firstValue)."\"^^xsd:string");
				}
				continue;
			} elseif ($property->getPropertyID() == "_PVAL") {
				foreach($propertyValueArray as $value) {
					$dbkeys = $value->getDBkeys();
					$firstValue = array_shift($dbkeys);
					$triples[] = array($subject_iri, $property_iri, "\"".TSHelper::escapeForStringLiteral($firstValue)."\"^^xsd:string");
				}
				continue;
			} elseif ($property->getPropertyID() == "_ERRP") {
				foreach($propertyValueArray as $value) {
					$dbkeys = $value->getDBkeys();
					$firstValue = array_shift($dbkeys);
					$triples[] = array($subject_iri, $property_iri, "\"".TSHelper::escapeForStringLiteral($firstValue)."\"^^xsd:string");
				}
				continue;
			} elseif ($property->getPropertyID() == "_LIST") {
				foreach($propertyValueArray as $value) {
						
					$typeValues = $value->getTypeValues();
					$i=0;
					$triples[] = array($subject_iri, $property_iri, "_:".$bNodeCounter);
					foreach($typeValues as $tv) {
						$dbkeys = $tv->getDBkeys();
						$firstValue = array_shift($dbkeys);
						$triples[] = array("_:".$bNodeCounter, $this->tsNamespace->getFullIRIByName(SMW_NS_PROPERTY, "_$i"), WikiTypeToXSD::getXSDType($firstValue));
						$i++;
					}
					for($j = $i; $j < 5; $j++) {
						$triples[] = array("_:".$bNodeCounter, $this->tsNamespace->getFullIRIByName(SMW_NS_PROPERTY, "_$j"), "<".TSNamespaces::$DEFAULT_VALUE_URI.">");
					}
						
				}
				continue;
			}


			// there are other special properties which need not to be handled special
			// so they can be handled by the default machanism:

			foreach($propertyValueArray as $value) {
					
				if ($value->isValid()) {
					if ($value->getTypeID() == '_txt') {
						$dbkeys = $value->getDBkeys();

						$triples[] = array($subject_iri, $property_iri, "\"".TSHelper::escapeForStringLiteral(array_shift($dbkeys))."\"^^xsd:string");

					} elseif ($value->getTypeID() == '_wpg' || $value->getTypeID() == '_wpp' || $value->getTypeID() == '_wpc' || $value->getTypeID() == '_wpf') {
						$object_iri = $this->tsNamespace->getFullIRI($value->getTitle());
						$triples[] = array($subject_iri, $property_iri, $object_iri);

					} elseif ($value->getTypeID() == '_rec') {
						 
						$sdata = $value->getData(); // SMWSemanticData object
						$v1 = reset($sdata->getPropertyValues(SMWPropertyValue::makeProperty("_1")));
						$v2 =  reset($sdata->getPropertyValues(SMWPropertyValue::makeProperty("_2")));
						$v3 =  reset($sdata->getPropertyValues(SMWPropertyValue::makeProperty("_3")));
						$v4 =  reset($sdata->getPropertyValues(SMWPropertyValue::makeProperty("_4")));
						$v5 =  reset($sdata->getPropertyValues(SMWPropertyValue::makeProperty("_5")));


						$triples[] = array($subject_iri, $property_iri, "_:".$bNodeCounter);

						if ($v1 !== false) {
							$xsdType = WikiTypeToXSD::getXSDType($v1->getTypeID());
							$dbkeys = $v1->getDBkeys();
							$firstValue = array_shift($dbkeys);
							if (is_null($xsdType)) {
								$object = $this->tsNamespace->getFullIRI(Title::newFromDBkey($v1->getTitle()));
							} else {
								$object = "\"".TSHelper::escapeForStringLiteral($firstValue)."\"^^$xsdType";
							}
							$triples[] = array("_:".$bNodeCounter, $this->tsNamespace->getFullIRIByName(SMW_NS_PROPERTY, "_1"), $object);
						} else {
							$triples[] = array("_:".$bNodeCounter, $this->tsNamespace->getFullIRIByName(SMW_NS_PROPERTY, "_1"), "<".TSNamespaces::$DEFAULT_VALUE_URI.">");
						}

						if ($v2 !== false) {
							$xsdType = WikiTypeToXSD::getXSDType($v2->getTypeID());
							$dbkeys = $v2->getDBkeys();
							$firstValue = array_shift($dbkeys);
							if (is_null($xsdType)) {
								$object = $this->tsNamespace->getFullIRI(Title::newFromDBkey($v2->getTitle()));
							} else {
								$object = "\"".TSHelper::escapeForStringLiteral($firstValue)."\"^^$xsdType";
							}
							$triples[] = array("_:".$bNodeCounter, $this->tsNamespace->getFullIRIByName(SMW_NS_PROPERTY, "_2"), $object);
						} else {
							$triples[] = array("_:".$bNodeCounter, $this->tsNamespace->getFullIRIByName(SMW_NS_PROPERTY, "_2"), "<".TSNamespaces::$DEFAULT_VALUE_URI.">");
						}

						if ($v3 !== false) {
							$xsdType = WikiTypeToXSD::getXSDType($v3->getTypeID());
							$dbkeys = $v3->getDBkeys();
							$firstValue = array_shift($dbkeys);
							if (is_null($xsdType)) {
								$object = $this->tsNamespace->getFullIRI(Title::newFromDBkey($v3->getTitle()));
							} else {
								$object = "\"".TSHelper::escapeForStringLiteral($firstValue)."\"^^$xsdType";
							}
							$triples[] = array("_:".$bNodeCounter, $this->tsNamespace->getFullIRIByName(SMW_NS_PROPERTY, "_3"), $object);
						} else {
							$triples[] = array("_:".$bNodeCounter, $this->tsNamespace->getFullIRIByName(SMW_NS_PROPERTY, "_3"), "<".TSNamespaces::$DEFAULT_VALUE_URI.">");
						}

						if ($v4 !== false) {
							$xsdType = WikiTypeToXSD::getXSDType($v4->getTypeID());
							$dbkeys = $v4->getDBkeys();
							$firstValue = array_shift($dbkeys);
							if (is_null($xsdType)) {
								$object = $this->tsNamespace->getFullIRI(Title::newFromDBkey($v4->getTitle()));
							} else {
								$object = "\"".TSHelper::escapeForStringLiteral($firstValue)."\"^^$xsdType";
							}
							$triples[] = array("_:".$bNodeCounter, $this->tsNamespace->getFullIRIByName(SMW_NS_PROPERTY, "_4"), $object);
						} else {
							$triples[] = array("_:".$bNodeCounter, $this->tsNamespace->getFullIRIByName(SMW_NS_PROPERTY, "_4"), "<".TSNamespaces::$DEFAULT_VALUE_URI.">");
						}

						if ($v5 !== false) {
							$xsdType = WikiTypeToXSD::getXSDType($v5->getTypeID());
							$dbkeys = $v5->getDBkeys();
							$firstValue = array_shift($dbkeys);
							if (is_null($xsdType)) {
								$object = $this->tsNamespace->getFullIRI(Title::newFromDBkey($v5->getTitle()));
							} else {
								$object = "\"".TSHelper::escapeForStringLiteral($firstValue)."\"^^$xsdType";
							}
							$triples[] = array("_:".$bNodeCounter, $this->tsNamespace->getFullIRIByName(SMW_NS_PROPERTY, "_5"), $object);
						} else {
							$triples[] = array("_:".$bNodeCounter, $this->tsNamespace->getFullIRIByName(SMW_NS_PROPERTY, "_5"), "<".TSNamespaces::$DEFAULT_VALUE_URI.">");
						}
						$bNodeCounter++;

					} else {
						// primitive value (including measures)

						if ($value->getUnit() != '') {
							// attribute with unit value (measure)
							$dbkeys = $value->getDBkeys();
							$triples[] = array($subject_iri, $property_iri, "\"".array_shift($dbkeys)." ".$value->getUnit()."\"^^tsctype:unit");
						} else {
							// other value
							if (!is_null($property->getWikiPageValue())) {
								$dbkeys = $value->getDBkeys();
								$firstValue = array_shift($dbkeys);
								if (!is_null($firstValue)) {
									$xsdType = WikiTypeToXSD::getXSDType($value->getTypeID());
									// special treatment for geo coords
									if ($property->getPropertyTypeID() == '_geo') {
										$dbkeys = $value->getDBkeys();
										$triples[] = array($subject_iri, $property_iri, "\"".TSHelper::escapeForStringLiteral(implode(",", $dbkeys))."\"^^$xsdType");
									} else {
										// all other primitive types
										$triples[] = array($subject_iri, $property_iri, "\"".TSHelper::escapeForStringLiteral($firstValue)."\"^^$xsdType");
									}
								}
							}
						}

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
			$qrc = new SMWQRCQueryResultsCache();
			return $qrc->getQueryResult($query);
		} else {
			return $this->doGetQueryResult($query);
		}
	}

	function doGetQueryResult(SMWQuery $query) {
		global $wgServer, $wgScript, $smwgWebserviceUser, $smwgWebservicePassword, $smwgDeployVersion;


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

		if ($query instanceof SMWSPARQLQuery || $toTSC) {
			// handle only SPARQL queries and delegate all others
			//			wfRunHooks('RewriteSparqlQuery', array(&$query) );

			if ($query->getQueryString() == "") {
				$sqr = new SMWHaloQueryResult(array(), $query, array(), $this, false);
				$sqr->addErrors(array(wfMsg('smw_tsc_query_not_allowed')));
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

				// Allow extensions to transform the query result before it is
				// parsed.
				wfRunHooks('ProcessSPARQLXMLResults', array(&$query, &$response) );

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
							wfRunHooks('ProcessQueryResults', array(&$query, &$queryResult[$key]));
						}
					} else {
						wfRunHooks('ProcessQueryResults', array(&$query, &$queryResult) );
					}
					break;
			}
			return $queryResult;

		} else {
			// redirect query to the default SMW implementation
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
			$sparulCommands[] = "LOAD <smw://".urlencode($wgDBuser).":".urlencode($wgDBpassword)."@$wgDBserver:$wgDBport/$wgDBname?lang=$wgLanguageCode&smwstore=$smwgBaseStore&ignoreSchema=$ignoreSchema&smwnsindex=$smwgNamespaceIndex#".urlencode($wgDBprefix)."> INTO <$smwgTripleStoreGraph>";
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
					if ($data == NULL) { // main column
						$hasMainColumn = true;
						if (in_array('_X_', $variableSet)) { // x is missing for INSTANCE queries
							$mapPRTOColumns['_X_'] = array($index);
							$prs[] = $pr;
							$index++;
						}

					} else  {
						if ( $data instanceof Title) {
							$label = $data->getDBkey();
						} else {
							$dbkeys = $data->getDBkeys();
							$label =  array_shift($dbkeys);
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
							$dbkeys = $data->getDBkeys();
							$label = array_shift($dbkeys);

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


				if (array_key_exists($var_name, $mapPRTOColumns)) {
					$mapPRTOColumns[$var_name][] = $index;
				} else {
					$mapPRTOColumns[$var_name] = array($index);
				}

				$index++;
			}


			// create and add result rows
			// iterate result rows and add an SMWResultArray object for each field
			$qresults = array();
			$rowIndex = 0;
			foreach ($results as $r) {
				$row = array();
				$columnIndex = 0; // column = n-th XML binding node

				// reset column arrays
				foreach($mapPRTOColumns as $pr => $column) reset($mapPRTOColumns[$pr]);

				$children = $r->children(); // $chilren->binding denote all binding nodes

				// find result column and store result page in $resultInstance variable
				$resultInstance = NULL;
				foreach ($children->binding as $b) {
					$var_name = ucfirst((string) $children[$columnIndex]->attributes()->name);
					if ($var_name == '_X_') {
						$resultColumn = current($mapPRTOColumns[$var_name]);
						next($mapPRTOColumns[$var_name]);

						$allValues = array();
						$this->parseBindungs($b, $var_name, $prs[$resultColumn], $allValues);
						// what happens if first column is merged??
						$resultInstance = count($allValues) > 0 ? reset($allValues) : SMWDataValueFactory::newTypeIDValue('_wpg');
						break;
					}
				}

				if (is_null($resultInstance)) {
					$resultInstance = SMWDataValueFactory::newTypeIDValue('_wpg');
				}

				// reset column arrays
				foreach($mapPRTOColumns as $pr => $column) reset($mapPRTOColumns[$pr]);

				foreach ($children->binding as $b) {

					$var_name = ucfirst((string) $children[$columnIndex]->attributes()->name);

					// ignore main variable if not displayed
					if (!$hasMainColumn && $var_name == '_X_') {
						$columnIndex++;
						continue;
					}

					// get current result column of the variable
					$resultColumn = current($mapPRTOColumns[$var_name]);
					next($mapPRTOColumns[$var_name]);

					$allValues = array();
					$this->parseBindungs($b, $var_name, $prs[$resultColumn], $allValues);

					// note: ignore bnodes

					$columnIndex++;
					$row[$resultColumn] = new SMWHaloResultArray($resultInstance, $prs[$resultColumn], $this, $allValues);

				}
				$rowIndex++;
				ksort($row);
				$qresults[] = $row;

			}
			// Query result object
			$queryResult = new SMWHaloQueryResult($prs, $query, $qresults, $this, (count($results) == $query->getLimit()));
			$qResultSet[$s] = $queryResult;
		}

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
			$uris[] = array((string) $sv, $sv->metadata);
		}
		if (!empty($uris)) {
			foreach($uris as $uri) {
				list($sv, $metadata) = $uri;

				$title = TSHelper::getTitleFromURI($sv, false);
				if (is_null($title) || $title instanceof Title) {
					$allValues[] = $this->createSMWPageValue($title, $metadata);
				} else {
					// external URI

					$v = SMWDataValueFactory::newTypeIDValue('_uri');
					$v->setDBkeys(array($sv));
					TSHelper::setMetadata($v, $metadata);
					$allValues[] = $v;

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
					$value = $this->createSMWDataValue($property, $literalValue, $literalType, $metadata);
					$allValues[] = $value;
				}
			}


		}
	}






	/**
	 *
	 * Creates primitive SMWWikiDataValue object. (ie. no SMWWikiPageValue)
	 *
	 * @param $property
	 * @param $literalValue
	 * @param $literalType
	 * @param $metadata
	 */
	protected function createSMWDataValue($property, $literalValue, $literalType, $metadata) {
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

					// remove time if it is 00:00:00
					if (substr($literalValue, -9) == 'T00:00:00') {
						$literalValue = substr($literalValue, 0, strpos($literalValue, "T"));
					}
					// hack: can not use setUserValue for SMW_DV_Time for some reason.
					$valueTemp = SMWDataValueFactory::newPropertyObjectValue($property, str_replace("-","/",$literalValue));
					$value->setDBkeys($valueTemp->getDBkeys());
				}
			} else if ($value->getTypeID() == '_ema'
			|| $value->getTypeID() == '_tel'
			|| $value->getTypeID() == '_num') {
				// set some types as DBkeys for normalization
				$value->setDBkeys(array($literalValue));
			} else {
				// all others, set as user type
				$value->setUserValue($literalValue);
			}
		} else {

			// literal value is empty
			if ($property instanceof SMWPropertyValue ) {
				$value = SMWDataValueFactory::newPropertyObjectValue($property);
			} else {
				$value = SMWDataValueFactory::newTypeIDValue('_wpg');

			}

		}
		// set metadata
		TSHelper::setMetadata($value, $metadata);
		return $value;
	}

	/**
	 * Creates SMWWikiPageValue object.
	 *
	 * @param string $uri Full URI
	 * @param hash array $metadata (propertyName=>value)
	 * @param string $nsFragment NS-prefix
	 * @param int $ns Namespace index
	 * @return SMWWikiPageValue
	 */
	protected function createSMWPageValue($title, $metadata) {

		$v = SMWDataValueFactory::newTypeIDValue('_wpg');
		if (is_null($title)) {
			$title = Title::newFromText(wfMsg('smw_ob_invalidtitle'), $ns);
		}
		$v->setValues($title->getDBkey(), $title->getNamespace(), $title->getArticleID(), false, '', $title->getFragment());
		TSHelper::setMetadata($v, $metadata);
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
				$dbkeys = $printout->getData()->getDBkeys();
				$result .= "?".array_shift($dbkeys).$outputFormat."=".$printout->getLabel();
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

		if (!isset($query->mergeResults) || $query->mergeResults !== 0) {
			if (!$first) $result .= "|";
			$result .= 'merge='.(!isset($query->mergeResults) || $query->mergeResults ? "true" : "false");
			$first = false;
		}

		if (isset($query->params)) {
			// Serialize all additional parameters
			foreach ($query->params as $param => $value) {
				if (!$first) $result .= "|";
				$result .= "$param=".trim($value);
				$first = false;
			}
		}
/*
		if (isset($query->params) && isset($query->params['dataspace'])) {
			if (!$first) $result .= "|";
			$result .= 'dataspace='.trim($query->params['dataspace']);
			$first = false;
		}

		if (isset($query->params) && isset($query->params['metadata'])) {
			if (!$first) $result .= "|";
			$result .= 'metadata='.trim($query->params['metadata']);
			$first = false;
		}
*/

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
					$dbkeys = $po->getData()->getDBkeys();
					$label =  array_shift($dbkeys);
				}
				$contains |= strtolower($label) == strtolower($var_name);
			}

		}
		return $contains;
	}

}





