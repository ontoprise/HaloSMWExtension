<?php
global $smwgHaloIP;
require_once( "$smwgHaloIP/includes/storage/SMW_TripleStore.php" );


/**
 * @file
 * @ingroup SMWHaloTriplestore
 *
 * Triple store connector class for QuadDriver.
 *
 * Specials:
 *      Using section parameter of provenance information to create link if available
 *      Implementing some of the get.. methods
 *
 * @author: Kai
 */

class SMWTripleStoreQuad extends SMWTripleStore {


	/**
	 * Creates and initializes Triple store connector.
	 *
	 * @param SMWStore $smwstore All calls are delegated to this implementation.
	 */
	function __construct() {
		parent::__construct();
	}

	function getSemanticData( $subject, $filter = false ) {
		if ( defined( 'DO_MAINTENANCE' )  && !defined('SMWH_FORCE_TS_UPDATE') ) {
			$this->smwstore->getSemanticData($subject, $filter);
		}

		$naryPropertiesPresent = false;
		$client = TSConnection::getConnector();
		$client->connect();

		$values = array();

		$limit =  isset($requestoptions->limit) ? " LIMIT ".$requestoptions->limit : "";
		$offset =  isset($requestoptions->offset) ? " OFFSET ".$requestoptions->offset : "";

		// query
		if ($subject instanceof Title) {
			$v = SMWDataValueFactory::newTypeIDValue('_wpg');
			$v->setValues($subject->getDBkey(), $subject->getNamespace(), $subject->getArticleID(), false, '', $subject->getFragment());
			$semanticData = new SMWSemanticData($v);
			$subj_iri =  $this->tsNamespace->getFullIRI($subject);
		} else {
			$semanticData = new SMWSemanticData($subject);
			$subj_iri =  $this->tsNamespace->getFullIRI($subject->getTitle());
		}

		try {
			$response = $client->query("SELECT DISTINCT ?p ?o WHERE {  $subj_iri ?p ?o.  } ORDER BY ASC(?p) $limit $offset",  "merge=false");
		} catch(Exception $e) {
			wfDebug("Triplestore does probably not run.\n");
			$response = TSNamespaces::$EMPTY_SPARQL_XML;
		}

		global $smwgSPARQLResultEncoding;
		// PHP strings are always interpreted in ISO-8859-1 but may be actually encoded in
		// another charset.
		if (isset($smwgSPARQLResultEncoding) && $smwgSPARQLResultEncoding == 'UTF-8') {
			$response = utf8_decode($response);
		}

		$dom = simplexml_load_string($response);
		$dom->registerXPathNamespace("sparqlxml", "http://www.w3.org/2005/sparql-results#");
		$properties = array();

		$results = $dom->xpath('//sparqlxml:result');
		foreach ($results as $r) {
			$values = array();
			$children = $r->children(); // binding nodes
			$b = $children->binding[0];
			$sv = $b->children()->uri[0];
			if ($sv == TSNamespaces::$RDF_NS."type") continue;

			$title = TSHelper::getTitleFromURI((string) $sv);
			$property = SMWPropertyValue::makeUserProperty($title->getText());

			$b = $children->binding[1];
			if (isset($b->children()->bnode)) {
				$naryPropertiesPresent = true;
				continue;
			}
			foreach($b->children()->uri as $sv) {

				$title = TSHelper::getTitleFromURI($sv, false);
				if (is_null($title) || $title instanceof Title) {
					$value = $this->createSMWPageValue($title, $sv->metadata);
				} else {
					// external URI
					$value = SMWDataValueFactory::newTypeIDValue('_uri');
					$value->setDBkeys(array($sv));
					TSHelper::setMetadata($value, $sv->metadata);
				}
				$semanticData->addPropertyObjectValue($property, $value);

			}
			foreach($b->children()->literal as $sv) {
				$literalValue = (string) $sv;
				$literalType = (string) $sv->attributes()->datatype;

				$value = $this->createSMWDataValue($property, $literalValue, $literalType, $sv->metadata);

				$semanticData->addPropertyObjectValue($property, $value);
			}


		}

		if ($naryPropertiesPresent) {
			list($property, $value) = $this->readRecordPropertyValues($subject);
			$semanticData->addPropertyObjectValue($property, $value);
		}

		return $semanticData;
	}

	function getProperties( $subject, $requestoptions = null ) {
		if ( defined( 'DO_MAINTENANCE' )  && !defined('SMWH_FORCE_TS_UPDATE') ) {
			$this->smwstore->getProperties($subject, $requestoptions);
		}


		$client = TSConnection::getConnector();
		$client->connect();

		$values = array();



		$limit =  isset($requestoptions->limit) ? " LIMIT ".$requestoptions->limit : "";
		$offset =  isset($requestoptions->offset) ? " OFFSET ".$requestoptions->offset : "";

		// query
		if ($subject instanceof Title) {
			$subj_iri =  $this->tsNamespace->getFullIRI($subject);
		} else {
			$subj_iri =  $this->tsNamespace->getFullIRI($subject->getTitle());
		}

		try {
			$response = $client->query("SELECT DISTINCT ?p WHERE { $subj_iri ?p ?o.  } ORDER BY ASC(?p) $limit $offset",  "merge=false");
		} catch(Exception $e) {
			wfDebug("Triplestore does probably not run.\n");
			$response = TSNamespaces::$EMPTY_SPARQL_XML;
		}

		global $smwgSPARQLResultEncoding;
		// PHP strings are always interpreted in ISO-8859-1 but may be actually encoded in
		// another charset.
		if (isset($smwgSPARQLResultEncoding) && $smwgSPARQLResultEncoding == 'UTF-8') {
			$response = utf8_decode($response);
		}

		$dom = simplexml_load_string($response);
		$dom->registerXPathNamespace("sparqlxml", "http://www.w3.org/2005/sparql-results#");
		$properties = array();

		$results = $dom->xpath('//sparqlxml:result');
		foreach ($results as $r) {
			$values = array();
			$children = $r->children(); // binding nodes
			$b = $children->binding[0];
			$sv = $b->children()->uri[0];

			if ($sv == TSNamespaces::$RDF_NS."type") continue;

			$title = TSHelper::getTitleFromURI((string) $sv);
			$properties[] = SMWPropertyValue::makeUserProperty($title->getText());

		}

		return $properties;
	}

	function getInProperties( SMWDataValue $object, $requestoptions = null ) {
		if ( defined( 'DO_MAINTENANCE' )  && !defined('SMWH_FORCE_TS_UPDATE') ) {
			$this->smwstore->getInProperties($object, $requestoptions);
		}


		$client = TSConnection::getConnector();
		$client->connect();

		$values = array();

		$limit =  isset($requestoptions->limit) ? " LIMIT ".$requestoptions->limit : "";
		$offset =  isset($requestoptions->offset) ? " OFFSET ".$requestoptions->offset : "";

		// query
		if ($object instanceof SMWWikiPageValue) {
			$objectNode =  $this->tsNamespace->getFullIRI($object->getTitle());
		} else {
			$typeID = $value->getTypeID();
			$xsdType = WikiTypeToXSD::getXSDType($typeID);
			$dbkey = $value->getDBkeys();
			$objectNode = '"'.TSHelper::escapeForStringLiteral($dbkey[0]).'"^^'.$xsdType;
		}

		try {
			$response = $client->query("PREFIX xsd:<".TSNamespaces::$XSD_NS."> SELECT DISTINCT ?p WHERE {  ?s ?p $objectNode. }  ORDER BY ASC(?p) $limit $offset",  "merge=false");
		} catch(Exception $e) {
			wfDebug("Triplestore does probably not run.\n");
			$response = TSNamespaces::$EMPTY_SPARQL_XML;
		}

		global $smwgSPARQLResultEncoding;
		// PHP strings are always interpreted in ISO-8859-1 but may be actually encoded in
		// another charset.
		if (isset($smwgSPARQLResultEncoding) && $smwgSPARQLResultEncoding == 'UTF-8') {
			$response = utf8_decode($response);
		}

		$dom = simplexml_load_string($response);
		$dom->registerXPathNamespace("sparqlxml", "http://www.w3.org/2005/sparql-results#");
		$properties = array();

		$results = $dom->xpath('//sparqlxml:result');
		foreach ($results as $r) {
			$values = array();
			$children = $r->children(); // binding nodes
			$b = $children->binding[0];
			$sv = $b->children()->uri[0];
			if ($sv == TSNamespaces::$RDF_NS."type") continue;

			$title = TSHelper::getTitleFromURI((string) $sv);
			$properties[] = SMWPropertyValue::makeUserProperty($title->getText());

		}

		return $properties;
	}

	function getAllPropertyAnnotations(SMWPropertyValue $property, $requestoptions = NULL) {
		if ( defined( 'DO_MAINTENANCE' )  && !defined('SMWH_FORCE_TS_UPDATE') ) {
			$this->smwstore->getAllPropertyAnnotations($property, $requestoptions);
		}

		$client = TSConnection::getConnector();
		$client->connect();

		$values = array();

		$propertyName = $property->getWikiPageValue()->getTitle()->getDBkey();

		$limit =  isset($requestoptions->limit) ? " LIMIT ".$requestoptions->limit : "";
		$offset =  isset($requestoptions->offset) ? " OFFSET ".$requestoptions->offset : "";

		// query
		$property_iri =  $this->tsNamespace->getFullIRI($property->getWikiPageValue()->getTitle());


		try {
			$response = $client->query("SELECT ?s ?o WHERE {  ?s $property_iri ?o.  } ORDER BY ASC(?s) $limit $offset",  "merge=false");
		} catch(Exception $e) {
			wfDebug("Triplestore does probably not run.\n");
			$response = TSNamespaces::$EMPTY_SPARQL_XML;
		}

		global $smwgSPARQLResultEncoding;
		// PHP strings are always interpreted in ISO-8859-1 but may be actually encoded in
		// another charset.
		if (isset($smwgSPARQLResultEncoding) && $smwgSPARQLResultEncoding == 'UTF-8') {
			$response = utf8_decode($response);
		}

		$dom = simplexml_load_string($response);
		$dom->registerXPathNamespace("sparqlxml", "http://www.w3.org/2005/sparql-results#");
		$annotations = array();

		$results = $dom->xpath('//sparqlxml:result');
		foreach ($results as $r) {
			$values = array();
			$children = $r->children(); // binding nodes
			$b = $children->binding[0];
			$sv = $b->children()->uri[0];

			$title = TSHelper::getTitleFromURI((string) $sv);
			$title_dv = SMWDataValueFactory::newTypeIDValue('_wpg');
			$title_dv->setValues($title->getDBkey(), $title->getNamespace(), $title->getArticleID());


			$b = $children->binding[1];
			foreach($b->children()->uri as $sv) {

				$title = TSHelper::getTitleFromURI($sv, false);
				if (is_null($title) || $title instanceof Title) {
					$value = $this->createSMWPageValue($title, $sv->metadata);
				} else {
					// external URI
					$value = SMWDataValueFactory::newTypeIDValue('_uri');
					$value->setDBkeys(array($sv));
					TSHelper::setMetadata($value, $sv->metadata);
				}
				$values[] = $value;

			}
			foreach($b->children()->literal as $sv) {
				$literalValue = (string) $sv;
				$literalType = (string) $sv->attributes()->datatype;
				$metadata = $sv->metadata;
				$value = $this->createSMWDataValue($property, $literalValue, $literalType, $metadata);

				$values[] = $value;
			}
			$annotations[] = array($title_dv, $values);

		}

		return $annotations;
	}


	function getPropertyValues($subject, SMWPropertyValue $property, $requestoptions = NULL, $outputformat = '') {
		if ( defined( 'DO_MAINTENANCE' )  && !defined('SMWH_FORCE_TS_UPDATE') ) {
			$this->smwstore->getPropertyValues($subject, $property, $requestoptions, $outputformat);
		}
		if (!$property->isUserDefined()) {
			return parent::getPropertyValues($subject,$property,$requestoptions,$outputformat);
		}
		if (smwfCheckIfPredefinedSMWHaloProperty($property)) {
			return parent::getPropertyValues($subject,$property,$requestoptions,$outputformat);
		}


		$client = TSConnection::getConnector();
		$client->connect();

		$values = array();

		$subjctName = $subject->getDBkey();
		$propertyName = $property->getWikiPageValue()->getTitle()->getDBkey();

		$limit =  isset($requestoptions->limit) ? " LIMIT ".$requestoptions->limit : "";
		$offset =  isset($requestoptions->offset) ? " OFFSET ".$requestoptions->offset : "";

		// query
		if ($subject instanceof Title) {
			$subject_iri =  $this->tsNamespace->getFullIRI($subject);
		} else {
			$subject_iri =  $this->tsNamespace->getFullIRI($subject->getTitle());
		}

		$property_iri =  $this->tsNamespace->getFullIRI($property->getWikiPageValue()->getTitle());


		try {
			$response = $client->query("SELECT ?o WHERE { $subject_iri $property_iri ?o. } $limit $offset",  "merge=false");
		} catch(Exception $e) {
			wfDebug("Triplestore does probably not run.\n");
			$response = TSNamespaces::$EMPTY_SPARQL_XML;
		}

		global $smwgSPARQLResultEncoding;
		// PHP strings are always interpreted in ISO-8859-1 but may be actually encoded in
		// another charset.
		if (isset($smwgSPARQLResultEncoding) && $smwgSPARQLResultEncoding == 'UTF-8') {
			$response = utf8_decode($response);
		}

		$dom = simplexml_load_string($response);
		$dom->registerXPathNamespace("sparqlxml", "http://www.w3.org/2005/sparql-results#");
		$annotations = array();
		$results = $dom->xpath('//sparqlxml:result');
		foreach ($results as $r) {

			$children = $r->children(); // binding nodes
			$b = $children->binding[0]; // predicate


			foreach($b->children()->uri as $sv) {

				$title = TSHelper::getTitleFromURI($sv, false);
				if (is_null($title) || $title instanceof Title) {
					$value = $this->createSMWPageValue($title, $sv->metadata);
				} else {
					// external URI
					$value = SMWDataValueFactory::newTypeIDValue('_uri');
					$value->setDBkeys(array($sv));
					TSHelper::setMetadata($value, $sv->metadata);
				}
				$values[] = $value;

			}
			foreach($b->children()->literal as $sv) {
				$literalValue = (string) $sv;
				$literalType = (string) $sv->attributes()->datatype;
				$metadata = $sv->metadata;
				$value = $this->createSMWDataValue($property, $literalValue, $literalType, $sv->metadata);

				$values[] = $value;
			}



		}

		return $values;
	}


	function getPropertySubjects(SMWPropertyValue $property, $value, $requestoptions = NULL) {
		if ( defined( 'DO_MAINTENANCE' )  && !defined('SMWH_FORCE_TS_UPDATE') ) {
			$this->smwstore->getPropertySubjects($property, $value, $requestoptions);
		}
		if (!$property->isUserDefined()) {
			return parent::getPropertySubjects($property, $value, $requestoptions);
		}
		if (smwfCheckIfPredefinedSMWHaloProperty($property)) {
			return parent::getPropertyValues($subject,$property,$requestoptions,$outputformat);
		}

		$client = TSConnection::getConnector();
		$client->connect();

		$values = array();


		$propertyName = $property->getWikiPageValue()->getTitle()->getDBkey();
		$limit =  isset($requestoptions->limit) ? " LIMIT ".$requestoptions->limit : "";
		$offset =  isset($requestoptions->offset) ? " OFFSET ".$requestoptions->offset : "";


		$propertyIRI = $this->tsNamespace->getFullIRI($property->getWikiPageValue()->getTitle());

		try {
			if (is_null($value)) {
				$response = $client->query("SELECT ?s WHERE {  ?s $propertyIRI ?o. } ORDER BY ASC(?s) $limit $offset",  "merge=false");

			} else if ($value instanceof SMWWikiPageValue) {

				$objectIRI = $this->tsNamespace->getFullIRI($value->getTitle());
				$response = $client->query("SELECT ?s WHERE {  ?s $propertyIRI $objectIRI.  } ORDER BY ASC(?s) $limit $offset",  "merge=false");

			} else {
				$typeID = $value->getTypeID();
				$xsdType = WikiTypeToXSD::getXSDType($typeID);
				$dbkey = $value->getDBkeys();
				$objectValue = '"'.TSHelper::escapeForStringLiteral($dbkey[0]).'"^^'.$xsdType;

				$response = $client->query("SELECT ?s WHERE {  ?s $propertyIRI $objectValue.  } ORDER BY ASC(?s) $limit $offset",  "merge=false");

			}
		} catch(Exception $e) {
			wfDebug("Triplestore does probably not run.\n");
			$response = TSNamespaces::$EMPTY_SPARQL_XML;
		}
		// query


		global $smwgSPARQLResultEncoding;
		// PHP strings are always interpreted in ISO-8859-1 but may be actually encoded in
		// another charset.
		if (isset($smwgSPARQLResultEncoding) && $smwgSPARQLResultEncoding == 'UTF-8') {
			$response = utf8_decode($response);
		}

		$dom = simplexml_load_string($response);
		$dom->registerXPathNamespace("sparqlxml", "http://www.w3.org/2005/sparql-results#");
		$annotations = array();

		$results = $dom->xpath('//sparqlxml:result');
		foreach ($results as $r) {

			$children = $r->children(); // binding nodes
			$b = $children->binding[0]; // predicate
			$sv = $b->children()->uri[0];

			$title = TSHelper::getTitleFromURI($sv, false);
			if (is_null($title) || $title instanceof Title) {
				$value = $this->createSMWPageValue($title, $sv->metadata);
			} else {
				// external URI
				$value = SMWDataValueFactory::newTypeIDValue('_uri');
				$value->setDBkeys(array($sv));
				TSHelper::setMetadata($value, $sv->metadata);
			}

			$values[] = $value;




		}

		return $values;
	}

	function getAllPropertySubjects(SMWPropertyValue $property, $requestoptions = NULL) {
		return $this->getPropertySubjects($property,NULL,$requestoptions);
	}

	private function readRecordPropertyValues($subject) {

		$client = TSConnection::getConnector();
		$client->connect();

		$values = array();

		$limit =  isset($requestoptions->limit) ? " LIMIT ".$requestoptions->limit : "";
		$offset =  isset($requestoptions->offset) ? " OFFSET ".$requestoptions->offset : "";

		// query
		if ($subject instanceof Title) {

			$subj_iri =  $this->tsNamespace->getFullIRI($subject);
		} else {

			$subj_iri =  $this->tsNamespace->getFullIRI($subject->getTitle());
		}

		try {
			$slot0 = $this->tsNamespace->getFullIRIByName(SMW_NS_PROPERTY, "_1");
			$slot1 = $this->tsNamespace->getFullIRIByName(SMW_NS_PROPERTY, "_2");
			$slot2 = $this->tsNamespace->getFullIRIByName(SMW_NS_PROPERTY, "_3");
			$slot3 = $this->tsNamespace->getFullIRIByName(SMW_NS_PROPERTY, "_4");
			$slot4 = $this->tsNamespace->getFullIRIByName(SMW_NS_PROPERTY, "_5");
			$response = $client->query("SELECT DISTINCT ?p ?b ?s0 ?s1 ?s2 ?s3 ?s4 WHERE {  $subj_iri ?p ?b. ?b $slot0 ?s0 . ?b $slot1 ?s1 . ?b $slot2 ?s2 . ?b $slot3 ?s3 . ?b $slot4 ?s4  } ORDER BY ASC(?p) $limit $offset",  "merge=false");
		} catch(Exception $e) {
			wfDebug("Triplestore does probably not run.\n");
			$response = TSNamespaces::$EMPTY_SPARQL_XML;
		}

		global $smwgSPARQLResultEncoding;
		// PHP strings are always interpreted in ISO-8859-1 but may be actually encoded in
		// another charset.
		if (isset($smwgSPARQLResultEncoding) && $smwgSPARQLResultEncoding == 'UTF-8') {
			$response = utf8_decode($response);
		}

		$dom = simplexml_load_string($response);
		$dom->registerXPathNamespace("sparqlxml", "http://www.w3.org/2005/sparql-results#");
		$properties = array();

		$results = $dom->xpath('//sparqlxml:result');
		foreach ($results as $r) {
			$values = array();
			$children = $r->children(); // binding nodes
			$b = $children->binding[0];
			$sv = $b->children()->uri[0];
			if ($sv == TSNamespaces::$RDF_NS."type") continue;

			$title = TSHelper::getTitleFromURI((string) $sv);
			$property = SMWPropertyValue::makeUserProperty($title->getText());

			$b = $children->binding[1];
			if (!isset($b->children()->bnode)) continue;

			$v0 = $this->getResultValue($children->binding[2]);
			$v1 = $this->getResultValue($children->binding[3]);
			$v2 = $this->getResultValue($children->binding[4]);
			$v3 = $this->getResultValue($children->binding[5]);
			$v4 = $this->getResultValue($children->binding[6]);
		}

		return array($property, SMWDataValueFactory::newPropertyObjectValue($property, implode(";",array($v0, $v1, $v2, $v3, $v4))));
	}

	private function getResultValue($b) {
	
		if (isset($b->children()->uri)) {
			$sv = reset($b->children()->uri);
			if ($sv == "http://__defaultvalue__/doesnotexist") return "";
			$title = TSHelper::getTitleFromURI($sv, false);
			if (is_null($title) || $title instanceof Title) {
				return $title->getPrefixedDBkey();
			} else {
				return (string) $sv;
			}

		} else if (isset($b->children()->literal)) {
			$sv = reset($b->children()->literal);
			$literalValue = (string) $sv;
			return $literalValue;
		}
		return "";
	}

}





