<?php
global $tscgIP;
require_once( "$tscgIP/includes/triplestore_client/TSC_TripleStore.php" );


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
	function __construct(SMWStore $basestore) {
		parent::__construct($basestore);
	}

	function getSemanticData(SMWDIWikiPage $subject, $filter = false) {

		if ( $this->localRequest || (defined( 'DO_MAINTENANCE' )  && !defined('SMWH_FORCE_TS_UPDATE')) ) {
			return $this->smwstore->getSemanticData($subject, $filter);
		}

		$naryPropertiesPresent = false;
		$client = TSConnection::getConnector();
		$client->connect();

		$values = array();

		// query
		$semanticData = new SMWSemanticData($subject);
		$subj_iri =  $this->tsNamespace->getFullIRI($subject->getTitle());


		try {
			$response = $client->query("SELECT DISTINCT ?p ?o WHERE {  GRAPH ?G { $subj_iri ?p ?o. } } ",  "merge=false");
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
			if ($sv == TSNamespaces::$RDF_NS."type") {
				$property = SMWDIProperty::newFromUserLabel('_INST');

			} else if ($sv == TSNamespaces::$RDFS_NS."subClassOf") {
				$property = SMWDIProperty::newFromUserLabel('_INST');
			} else if ($sv == TSNamespaces::$RDFS_NS."subPropertyOf") {
				$property = SMWDIProperty::newFromUserLabel('_SUBP');
			} else {

				$title = TSHelper::getTitleFromURI((string) $sv);
				$property = SMWDIProperty::newFromUserLabel($title->getText());
			}

			$b = $children->binding[1];
			if (isset($b->children()->bnode)) {
				$naryPropertiesPresent = true;
				continue;
			}
			foreach($b->children()->uri as $sv) {

				$title = TSHelper::getTitleFromURI($sv, false);
				if (is_null($title) || $title instanceof Title) {
					$value = $this->createSMWPageDataItem($title, $sv->metadata);
				} else {
					// external URI
					$value = SMWDIUri::doUnserialize((string) $sv);
					TSHelper::setMetadata($value, $sv->metadata);
				}
				$semanticData->addPropertyObjectValue($property, $value);

			}
			foreach($b->children()->literal as $sv) {
				$literalValue = (string) $sv;
				$literalType = (string) $sv->attributes()->datatype;

				$value = $this->createSMWDataItem($property, $literalValue, $literalType, $sv->metadata);

				$semanticData->addPropertyObjectValue($property, $value);
			}


		}

		if ($naryPropertiesPresent) {
			$this->readRecordPropertyValues($subject, $semanticData);

		}

		return $semanticData;
	}

	function getProperties(SMWDIWikiPage $subject, $requestoptions = null) {

		if (  $this->localRequest || (defined( 'DO_MAINTENANCE' )  && !defined('SMWH_FORCE_TS_UPDATE')) ) {
			$this->setLocalRequest(true);
			$result = $this->smwstore->getProperties($subject, $requestoptions);

			return $result;
		}



		$client = TSConnection::getConnector();
		$client->connect();

		$values = array();



		$limit =  (!is_null($requestoptions) && $requestoptions->limit > -1) ? " LIMIT ".$requestoptions->limit : "";
		$offset = (!is_null($requestoptions) && $requestoptions->offset > 0) ? " OFFSET ".$requestoptions->offset : "";

		// query
		$subj_iri =  $this->tsNamespace->getFullIRI($subject->getTitle());

		try {
			$response = $client->query("SELECT DISTINCT ?p WHERE { GRAPH ?G {  $subj_iri ?p ?o. } } $limit $offset",  "merge=false");
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

			if ($sv == TSNamespaces::$RDF_NS."type") {
				$properties[] = SMWDIProperty::newFromUserLabel('_INST');
			}  else if ($sv == TSNamespaces::$RDFS_NS."subClassOf") {
				$properties[] = SMWDIProperty::newFromUserLabel('_INST');
			} else if ($sv == TSNamespaces::$RDFS_NS."subPropertyOf") {
				$properties[] = SMWDIProperty::newFromUserLabel('_SUBP');
			} else {

				$title = TSHelper::getTitleFromURI((string) $sv);
				$properties[] = SMWDIProperty::newFromUserLabel($title->getText());
			}
		}

		return $properties;
	}

	function getInProperties( SMWDataItem $object, $requestoptions = null ) {

		if ( $this->localRequest || (defined( 'DO_MAINTENANCE' )  && !defined('SMWH_FORCE_TS_UPDATE')) ) {
			$this->setLocalRequest(true);
			$result =  $this->smwstore->getInProperties($object, $requestoptions);

			return $result;
		}


		$client = TSConnection::getConnector();
		$client->connect();

		$values = array();

		$limit =  (!is_null($requestoptions) && $requestoptions->limit > -1) ? " LIMIT ".$requestoptions->limit : "";
		$offset = (!is_null($requestoptions) && $requestoptions->offset > 0) ? " OFFSET ".$requestoptions->offset : "";

		// query


		if (is_null($object)) {
			$objectNode = "?o";
		} else if ($object instanceof SMWDIWikiPage) {
			$objectNode = $this->tsNamespace->getFullIRI($object->getTitle());
		} else {
			$serialization = TSHelper::serializeDataItem($object);
			$xsdType = WikiTypeToXSD::getXSDTypeFromTypeID($object->getDIType());
			$objectNode = '"'.TSHelper::escapeForStringLiteral($serialization).'"^^'.$xsdType;
		}


		try {
			$response = $client->query("PREFIX xsd:<".TSNamespaces::$XSD_NS."> SELECT DISTINCT ?p WHERE { GRAPH ?G {  ?s ?p $objectNode. } }  $limit $offset",  "merge=false");
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
			if ($sv == TSNamespaces::$RDF_NS."type") {
				$properties[] = SMWDIProperty::newFromUserLabel('_INST');
			} else if ($sv == TSNamespaces::$RDFS_NS."subClassOf") {
				$properties[] = SMWDIProperty::newFromUserLabel('_INST');
			} else if ($sv == TSNamespaces::$RDFS_NS."subPropertyOf") {
				$properties[] = SMWDIProperty::newFromUserLabel('_SUBP');
			} else {

				$title = TSHelper::getTitleFromURI((string) $sv);
				if (!is_null($title) && $title instanceof Title) $properties[] = SMWDIProperty::newFromUserLabel($title->getText());
			}
		}

		return $properties;
	}

	function getAllPropertyAnnotations(SMWDIProperty $property, $requestoptions = NULL) {

		if ( $this->localRequest || (defined( 'DO_MAINTENANCE' )  && !defined('SMWH_FORCE_TS_UPDATE')) ) {
			return array();
		}

		$client = TSConnection::getConnector();
		$client->connect();

		$values = array();

		$limit =  (!is_null($requestoptions) && $requestoptions->limit > -1) ? " LIMIT ".$requestoptions->limit : "";
		$offset = (!is_null($requestoptions) && $requestoptions->offset > 0) ? " OFFSET ".$requestoptions->offset : "";

		$propertyID = $property->getKey();
		if ($propertyID == '_INST') {
			$property_iri = "<".TSNamespaces::$RDF_NS."type>";
		} else if ($propertyID == '_INST') {
			$property_iri = "<".TSNamespaces::$RDFS_NS."subClassOf>";
		} else if ($propertyID == '_SUBP') {
			$property_iri = "<".TSNamespaces::$RDFS_NS."subPropertyOf>";
		} else {
			$propertyName = $property->getDiWikiPage()->getTitle()->getDBkey();

			$property_iri =  $this->tsNamespace->getFullIRI($property->getDiWikiPage()->getTitle());
		}
		// add boundary constraint here too?

		try {
			$response = $client->query("SELECT ?s ?o WHERE { GRAPH ?G {  ?s $property_iri ?o. } }  $limit $offset",  "merge=false");
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
			$title_di = $this->createSMWPageDataItem($title, null);


			$b = $children->binding[1];
			foreach($b->children()->uri as $sv) {

				$title = TSHelper::getTitleFromURI($sv, false);
				if (is_null($title) || $title instanceof Title) {
					$value = $this->createSMWPageDataItem($title, $sv->metadata);
				} else {
					// external URI
					$value = SMWDIUri::doUnserialize((string) $sv);
					TSHelper::setMetadata($value, $sv->metadata);
				}
				$values[] = $value;

			}
			foreach($b->children()->literal as $sv) {
				$literalValue = (string) $sv;
				$literalType = (string) $sv->attributes()->datatype;
				$metadata = $sv->metadata;
				$value = $this->createSMWDataItem($property, $literalValue, $literalType, $metadata);

				$values[] = $value;
			}
			$annotations[] = array($title_di, $values);

		}

		return $annotations;
	}


	function getPropertyValues($subject, SMWDIProperty $property, $requestoptions = NULL ) {
			
		if (is_null($subject)) {
			return $this->getAllPropertyAnnotations($property, $requestoptions);
		}

		if ( $this->localRequest || (defined( 'DO_MAINTENANCE' )  && !defined('SMWH_FORCE_TS_UPDATE')) ) {
			$this->setLocalRequest(true);
			$result = $this->smwstore->getPropertyValues($subject, $property, $requestoptions);

			return $result;
		}


		if (!$property->isUserDefined()) {

			$result = parent::getPropertyValues($subject,$property,$requestoptions);

			return $result;
		}
		if (smwfCheckIfPredefinedSMWHaloProperty($property)) {

			$result = parent::getPropertyValues($subject,$property,$requestoptions);

			return $result;
		}


		$client = TSConnection::getConnector();
		$client->connect();

		$values = array();

		$subjctName = $subject->getDBkey();
		$propertyName = $property->getDiWikiPage()->getTitle()->getDBkey();

		$limit =  (!is_null($requestoptions) && $requestoptions->limit > -1) ? " LIMIT ".$requestoptions->limit : "";
		$offset = (!is_null($requestoptions) && $requestoptions->offset > 0) ? " OFFSET ".$requestoptions->offset : "";

		// query
		if ($subject instanceof Title) {
			$subject_iri =  $this->tsNamespace->getFullIRI($subject);
		} else {
			$subject_iri =  $this->tsNamespace->getFullIRI($subject->getTitle());
		}

		if ($property->getKey() == '_INST') {
			$property_iri = "<".TSNamespaces::$RDF_NS."type>";
		} else if ($property->getKey() == '_INST') {
			$property_iri = "<".TSNamespaces::$RDFS_NS."subClassOf>";
		} else if ($property->getKey() == '_SUBP') {
			$property_iri = "<".TSNamespaces::$RDFS_NS."subPropertyOf>";
		} else {
			$property_iri =  $this->tsNamespace->getFullIRI($property->getDiWikiPage()->getTitle());
		}


		try {
			$response = $client->query("SELECT ?o WHERE { GRAPH ?G {  $subject_iri $property_iri ?o. } } $limit $offset",  "merge=false");
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
					$value = $this->createSMWPageDataItem($title, $sv->metadata);
				} else {
					// external URI
					$value = SMWDIUri::doUnserialize((string) $sv);
					TSHelper::setMetadata($value, $sv->metadata);
				}
				$values[] = $value;

			}
			foreach($b->children()->literal as $sv) {
				$literalValue = (string) $sv;
				$literalType = (string) $sv->attributes()->datatype;
				$metadata = $sv->metadata;
				$value = $this->createSMWDataItem($property, $literalValue, $literalType, $sv->metadata);

				$values[] = $value;
			}



		}

		return $values;
	}


	function getPropertySubjects(SMWDIProperty $property, $value, $requestoptions = NULL) {

		if ($this->localRequest || (defined( 'DO_MAINTENANCE' )  && !defined('SMWH_FORCE_TS_UPDATE')) ) {
			$this->setLocalRequest(true);
			$result = $this->smwstore->getPropertySubjects($property, $value, $requestoptions);

			return $result;
		}
		if (!$property->isUserDefined()) {

			$result = parent::getPropertySubjects($property, $value, $requestoptions);

			return $result;
		}
		if (smwfCheckIfPredefinedSMWHaloProperty($property)) {

			$result = parent::getPropertySubjects($property,$value,$requestoptions);

			return $result;
		}

		$client = TSConnection::getConnector();
		$client->connect();

		$values = array();


		$propertyName = $property->getKey();
		$limit =  (!is_null($requestoptions) && $requestoptions->limit > -1) ? " LIMIT ".$requestoptions->limit : "";
		$offset = (!is_null($requestoptions) && $requestoptions->offset > 0) ? " OFFSET ".$requestoptions->offset : "";

		if ( isset($requestoptions) ) {
			if ($requestoptions->ascending ) {
				$op = $requestoptions->include_boundary ? ' >= ' : ' > ';
			} else {
				$op = $requestoptions->include_boundary ? ' <= ' : ' < ';
			}
		}

		// FIXME: filter only for instances in the wiki main namespace.
		// SPARQL builtin required for selecting localname
		$nsMainPrefix = TSNamespaces::getInstance()->getNSURI(NS_MAIN);

		$boundaryFilter = isset($requestoptions) && !is_null($requestoptions->boundary) ? "FILTER (str(?s) $op \"".$nsMainPrefix.TSHelper::escapeForStringLiteral($requestoptions->boundary)."\")" : "";

		if ($property->getKey() == '_INST') {
			$propertyIRI = "<".TSNamespaces::$RDF_NS."type>";
		} else if ($property->getKey() == '_INST') {
			$propertyIRI = "<".TSNamespaces::$RDFS_NS."subClassOf>";
		} else if ($property->getKey() == '_SUBP') {
			$propertyIRI = "<".TSNamespaces::$RDFS_NS."subPropertyOf>";
		} else {
			$propertyIRI = $this->tsNamespace->getFullIRI($property->getDiWikiPage()->getTitle());
		}

		try {
			if (is_null($value)) {

				$response = $client->query("SELECT ?s WHERE { GRAPH ?G {  ?s $propertyIRI ?o. $boundaryFilter } } $limit $offset",  "merge=false");

			} else if ($value instanceof SMWDIWikiPage) {

				$objectIRI = $this->tsNamespace->getFullIRI($value->getTitle());
				$response = $client->query("SELECT ?s WHERE { GRAPH ?G {  ?s $propertyIRI $objectIRI. $boundaryFilter } } $limit $offset",  "merge=false");

			} else {

				$serialization = TSHelper::serializeDataItem($value);
				$xsdType = WikiTypeToXSD::getXSDTypeFromTypeID($value->getDIType());
				$objectValue = '"'.TSHelper::escapeForStringLiteral($serialization).'"^^'.$xsdType;

				$response = $client->query("SELECT ?s WHERE { GRAPH ?G {  ?s $propertyIRI $objectValue. $boundaryFilter } } $limit $offset",  "merge=false");

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
				$value = $this->createSMWPageDataItem($title, $sv->metadata);
			} else {
				// external URI
				$value = SMWDIUri::doUnserialize((string) $sv);
				TSHelper::setMetadata($value, $sv->metadata);
			}

			$values[] = $value;




		}

		return $values;
	}

	function getAllPropertySubjects(SMWDIProperty $property, $requestoptions = NULL) {
		return $this->getPropertySubjects($property,NULL,$requestoptions);
	}

	private function readRecordPropertyValues(SMWDIWikiPage $subject, $semanticData) {

		$client = TSConnection::getConnector();
		$client->connect();

		$values = array();

		// query
		$subj_iri =  $this->tsNamespace->getFullIRI($subject->getTitle());


		try {

			$response = $client->query("SELECT DISTINCT ?p ?b ?sp ?v WHERE { GRAPH ?G {  $subj_iri ?p ?b. ?b ?sp ?v . FILTER(isBlank(?b)) } } ORDER BY ASC(?p) ",  "merge=false");
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
		$naryProps = array();
		$bnodes2Values = array();

		foreach ($results as $r) {

			$children = $r->children(); // binding nodes

			$b = $children->binding[1];
			if (!isset($b->children()->bnode)) continue;
			$bnodeName = (string) $b->children()->bnode[0];

			// property of sub object
			$b = $children->binding[2];
			$sv = $b->children()->uri[0];
			$title = TSHelper::getTitleFromURI((string) $sv);
			$propertyDi = SMWDIProperty::newFromUserLabel($title->getText());

			// value
			$v = $this->getResultValue($children->binding[3]);

			if (!array_key_exists($bnodeName, $bnodes2Values) ) {
				$bnodes2Values[$bnodeName] = array();
				$bnodes2Values[$bnodeName][] = array($propertyDi, $v);
			} else {
				$bnodes2Values[$bnodeName][] = array($propertyDi, $v);
			}


		}
			

		$visitedBNodes = array();
		foreach ($results as $r) {
			$children = $r->children(); // binding nodes
			$b = $children->binding[0];
			$sv = $b->children()->uri[0];
			if ($sv == TSNamespaces::$RDF_NS."type") continue;

			$title = TSHelper::getTitleFromURI((string) $sv);
			$naryPropertyDi = SMWDIProperty::newFromUserLabel($title->getText());

			if (!is_null($naryPropertyDi)) {
				$bnodeName = (string) $children->binding[1]->children()->bnode;
				if (in_array($bnodeName, $visitedBNodes)) continue;
				$visitedBNodes[] = $bnodeName;
				foreach($bnodes2Values[$bnodeName] as $v) {
					list($propertyDi, $propertyValueDi) = $v;
					$semanticData->addPropertyObjectValue( $propertyDi, $propertyValueDi );
				}

			}
		}


	}

	private function getResultValue($b) {

		if (isset($b->children()->uri)) {
			$sv = reset($b->children()->uri);
			if ($sv == "http://__defaultvalue__/doesnotexist") return "";
			$title = TSHelper::getTitleFromURI($sv, false);
			if (is_null($title)) {
				return new SMWErrorValue(SMWDataItem::TYPE_ERROR);
			} else if ($title instanceof Title) {
				return SMWDIWikiPage::newFromTitle($title);
			}

		} else if (isset($b->children()->literal)) {
			$sv = reset($b->children()->literal);
			$literalValue = (string) $sv;
			return SMWCompatibilityHelpers::dataItemFromDBKeys(SMWDataItem::TYPE_STRING, array($literalValue));
		}
		return new SMWErrorValue(SMWDataItem::TYPE_ERROR);
	}

}





