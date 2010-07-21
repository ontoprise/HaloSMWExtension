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

	/**
	 * @see superclass
	 */
	function getAllPropertyAnnotations(SMWPropertyValue $property, $requestoptions = NULL) {

		global $smwgTripleStoreGraph;

		$client = TSConnection::getConnector();
		$client->connect();

		$values = array();

		$propertyName = $property->getWikiPageValue()->getTitle()->getDBkey();

		$limit =  isset($requestoptions->limit) ? " LIMIT ".$requestoptions->limit : "";
		$offset =  isset($requestoptions->offset) ? " OFFSET ".$requestoptions->offset : "";

		// query

		$nsPrefixProp = $this->tsNamespace->getNSPrefix($property->getWikiPageValue()->getTitle()->getNamespace());

		try {
			$response = $client->query("SELECT ?s ?o WHERE { GRAPH ?g { ?s <$smwgTripleStoreGraph/$nsPrefixProp#$propertyName> ?o. } } ORDER BY ASC(?s) $limit $offset",  "merge=false");
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

		$annotations = array();
		$results = $dom->xpath('//result');
		foreach ($results as $r) {
			$values = array();
			$children = $r->children(); // binding nodes
			$b = $children->binding[0];
			$sv = $b->children()->uri[0];

			$title = $this->getTitleFromURI((string) $sv);
			$title_dv = SMWDataValueFactory::newTypeIDValue('_wpg');
			$title_dv->setValues($title->getDBkey(), $title->getNamespace(), $title->getArticleID());


			$b = $children->binding[1];
			foreach($b->children()->uri as $sv) {

				$object = $this->getTitleFromURI((string) $sv);
				$value = SMWDataValueFactory::newPropertyObjectValue($property, $object);
				$metadata = $sv->attributes();
				foreach($metadata as $mdProperty => $mdValue) {
					if (strpos($mdProperty, "_meta_") === 0) {
						$value->setMetadata(substr($mdProperty,6), explode("|||",$mdValue));
					}
				}
				$values[] = $value;

			}
			foreach($b->children()->literal as $sv) {
				$literal = array((string) $sv, $sv->attributes()->datatype);
				$value = $this->getLiteral($literal, $property);
				$metadata = $sv->attributes();
				foreach($metadata as $mdProperty => $mdValue) {
					if (strpos($mdProperty, "_meta_") === 0) {
						$value->setMetadata(substr($mdProperty,6), explode("|||",$mdValue));
					}
				}
				$values[] = $value;
			}
			$annotations[] = array($title_dv, $values);


		}

		return $annotations;
	}

	/**
	 * @see superclass
	 */
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
		$nsPrefix = $this->tsNamespace->getNSPrefix($subject->getNamespace());
		$nsPrefixProp = $this->tsNamespace->getNSPrefix($property->getWikiPageValue()->getTitle()->getNamespace());

		try {
			$response = $client->query("SELECT ?o WHERE { GRAPH ?g { <$smwgTripleStoreGraph/$nsPrefix#$subjctName> <$smwgTripleStoreGraph/$nsPrefixProp#$propertyName> ?o. } } $limit $offset",  "merge=false");
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

		$annotations = array();
		$results = $dom->xpath('//result');
		foreach ($results as $r) {

			$children = $r->children(); // binding nodes
			$b = $children->binding[0]; // predicate


			foreach($b->children()->uri as $sv) {

				$object = $this->getTitleFromURI((string) $sv);
				$value = SMWDataValueFactory::newPropertyObjectValue($property, $object);
				$metadata = $sv->attributes();
				foreach($metadata as $mdProperty => $mdValue) {
					if (strpos($mdProperty, "_meta_") === 0) {
						$value->setMetadata(substr($mdProperty,6), explode("|||",$mdValue));
					}
				}
				$values[] = $value;

			}
			foreach($b->children()->literal as $sv) {
				$literal = array((string) $sv, $sv->attributes()->datatype);
				$value = $this->getLiteral($literal, $property);
				$metadata = $sv->attributes();
				foreach($metadata as $mdProperty => $mdValue) {
					if (strpos($mdProperty, "_meta_") === 0) {
						$value->setMetadata(substr($mdProperty,6), explode("|||",$mdValue));
					}
				}
				$values[] = $value;
			}



		}

		return $values;
	}

	/**
	 * @see superclass
	 */
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

		$nsPrefixProp = $this->tsNamespace->getNSPrefix($property->getWikiPageValue()->getTitle()->getNamespace());

		try {
			if (is_null($value)) {

				$response = $client->query("SELECT ?s WHERE { GRAPH ?g { ?s <$smwgTripleStoreGraph/$nsPrefixProp#$propertyName> ?o. } } $limit $offset",  "merge=false|graph=$smwgTripleStoreGraph");

			} else if ($value instanceof SMWWikiPageValue) {
				$objectName = $value->getTitle()->getDBkey();
				$nsPrefixObj = $this->tsNamespace->getNSPrefix($value->getTitle()->getNamespace());

				$response = $client->query("SELECT ?s WHERE { GRAPH ?g { ?s <$smwgTripleStoreGraph/$nsPrefixProp#$propertyName> <$smwgTripleStoreGraph/$nsPrefixObj#$objectName>. } } $limit $offset",  "merge=false");

			} else {
				$objectvalue = str_replace('"', '\"', array_shift($value->getDBkeys()));
				$objecttype = WikiTypeToXSD::getXSDType($value->getTypeID());

				$response = $client->query("SELECT ?s WHERE { GRAPH ?g { ?s <$smwgTripleStoreGraph/$nsPrefixProp#$propertyName> \"$objectvalue\"^^$objecttype. } } $limit $offset",  "merge=false");

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

		$annotations = array();
		$results = $dom->xpath('//result');
		foreach ($results as $r) {

			$children = $r->children(); // binding nodes
			$b = $children->binding[0]; // predicate
			$sv = $b->children()->uri[0];

			$title = $this->getTitleFromURI((string) $sv);
			$value = SMWWikiPageValue::makePage($title->getDBkey(), $title->getNamespace());

			$metadata = $sv->attributes();
			foreach($metadata as $mdProperty => $mdValue) {
				if (strpos($mdProperty, "_meta_") === 0) {
					$value->setMetadata(substr($mdProperty,6), explode("|||",$mdValue));
				}
			}

			$values[] = $value;




		}

		return $values;
	}

	function getAllPropertySubjects(SMWPropertyValue $property, $requestoptions = NULL) {
		return $this->getPropertySubjects($property,NULL,$requestoptions);
	}

	/**
	 * @see superclass
	 */
	protected function addURIToResult($uris, & $allValues) {

		foreach($uris as $uri) {
			list($sv, $metadata) = $uri;

			$nsFound = false;
			foreach (TSNamespaces::getAllNamespaces() as $nsIndsex => $ns) {
				if (stripos($sv, $ns) === 0) {
					$allValues[] = $this->createSMWDataValue($sv, $metadata, $ns, $nsIndsex);
					$nsFound = true;
				}
			}

			if ($nsFound) continue;

			// result with unknown namespace
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


					if (strpos($sv, "#") !== false) {
						$local = substr($sv, strpos($sv, "#")+1);
					} else if (strrpos($sv, "/") !== false) {
						$local = substr($sv, strrpos($sv, "/")+1);
					}
					$title = Title::newFromText($local, $ns);

					if (is_null($title)) {
						$title = Title::newFromText(wfMsg('smw_ob_invalidtitle'), $ns);
					}
					$v = SMWDataValueFactory::newTypeIDValue('_wpg');
					$v->setValues($title->getDBkey(), $ns, $title->getArticleID(), false, '', $title->getFragment());

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
	 * @see superclass
	 */
	protected function createSMWDataValue($sv, $metadata, $nsFragment, $ns) {

		$local = substr($sv, strlen($nsFragment));
		$title = Title::newFromText($local, $ns);

		if (is_null($title)) {
			$title = Title::newFromText(wfMsg('smw_ob_invalidtitle'), $ns);
		}
		$v = SMWDataValueFactory::newTypeIDValue('_wpg');
		$v->setValues($title->getDBkey(), $ns, $title->getArticleID(), false, '', $title->getFragment());
		foreach($metadata as $mdProperty => $mdValue) {
			if (strpos($mdProperty, "_meta_") === 0) {
				$v->setMetadata(substr($mdProperty,6), explode("|||",$mdValue));
			}
		}
		return $v;

	}

	private function getTitleFromURI($sv) {

		foreach (TSNamespaces::$ALL_NAMESPACES as $nsIndsex => $ns) {
			if (stripos($sv, $ns) === 0) {

				$local = substr($sv, strlen($ns));
				return Title::newFromText($local, $nsIndsex);


			}
		}



		// result with unknown namespace
		if (stripos($sv, TSNamespaces::$UNKNOWN_NS) === 0) {


			$startNS = strlen(TSNamespaces::$UNKNOWN_NS);
			$length = strpos($sv, "#") - $startNS;
			$ns = intval(substr($sv, $startNS, $length));

			$local = substr($sv, strpos($sv, "#")+1);

			return Title::newFromText($local, $ns);



		}

		return NULL;
	}


	private function getLiteral($literal, $predicate) {
		list($literalValue, $literalType) = $literal;
		if (!empty($literalValue)) {

			// create SMWDataValue either by property or if that is not possible by the given XSD type
			if ($predicate instanceof SMWPropertyValue ) {
				$value = SMWDataValueFactory::newPropertyObjectValue($predicate, $literalValue);
			} else {
				$value = SMWDataValueFactory::newTypeIDValue(WikiTypeToXSD::getWikiType($literalType));
			}
			if ($value->getTypeID() == '_dat') { // exception for dateTime
				if ($literalValue != '') $value->setDBkeys(array(str_replace("-","/", $literalValue)));
			} else if ($value->getTypeID() == '_ema') { // exception for email
				$value->setDBkeys(array($literalValue));
			} else {
				$value->setUserValue($literalValue);
			}
		} else {

			if ($predicate instanceof SMWPropertyValue ) {
				$value = SMWDataValueFactory::newPropertyObjectValue($predicate);
			} else {
				$value = SMWDataValueFactory::newTypeIDValue('_wpg');

			}

		}
		return $value;
	}

}





