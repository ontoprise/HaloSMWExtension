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
		global $smwgTripleStoreGraph;

		$semanticData = new SMWSemanticData($subject);

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

		return $semanticData;
	}

	function getProperties( $subject, $requestoptions = null ) {
		global $smwgTripleStoreGraph;

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
		global $smwgTripleStoreGraph;

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

		global $smwgTripleStoreGraph;

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

		if (!$property->isUserDefined()) {
			return parent::getPropertyValues($subject,$property,$requestoptions,$outputformat);
		}
		if (smwfCheckIfPredefinedSMWHaloProperty($property)) {
			return parent::getPropertyValues($subject,$property,$requestoptions,$outputformat);
		}

		global $smwgTripleStoreGraph;
		$client = TSConnection::getConnector();
		$client->connect();

		$values = array();

		$subjctName = $subject->getDBkey();
		$propertyName = $property->getWikiPageValue()->getTitle()->getDBkey();

		$limit =  isset($requestoptions->limit) ? " LIMIT ".$requestoptions->limit : "";
		$offset =  isset($requestoptions->offset) ? " OFFSET ".$requestoptions->offset : "";

		// query
		 $subject_iri =  $this->tsNamespace->getFullIRI($subject);
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
		if (!$property->isUserDefined()) {
			return parent::getPropertySubjects($property, $value, $requestoptions);
		}
		if (smwfCheckIfPredefinedSMWHaloProperty($property)) {
			return parent::getPropertyValues($subject,$property,$requestoptions,$outputformat);
		}
		global $smwgTripleStoreGraph;
		$client = TSConnection::getConnector();
		$client->connect();

		$values = array();


		$propertyName = $property->getWikiPageValue()->getTitle()->getDBkey();
		$limit =  isset($requestoptions->limit) ? " LIMIT ".$requestoptions->limit : "";
		$offset =  isset($requestoptions->offset) ? " OFFSET ".$requestoptions->offset : "";

		
		$propertyIRI = $this->tsNamespace->getFullIRI($property->getWikiPageValue()->getTitle());

		try {
			if (is_null($value)) {
				$response = $client->query("SELECT ?s WHERE {  ?s $propertyIRI ?o. } ORDER BY ASC(?s) $limit $offset",  "merge=false|graph=$smwgTripleStoreGraph");

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






}





