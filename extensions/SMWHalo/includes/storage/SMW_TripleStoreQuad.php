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

	function getAllPropertyAnnotations(SMWPropertyValue $property, $requestoptions = NULL) {

		global $smwgTripleStoreGraph, $smwgTripleStoreQuadMode;

		$client = TSConnection::getConnector();
		$client->connect();

		$values = array();


		$propertyName = $property->getWikiPageValue()->getTitle()->getDBkey();

		$limit =  isset($requestoptions->limit) ? " LIMIT ".$requestoptions->limit : "";
		$offset =  isset($requestoptions->offset) ? " OFFSET ".$requestoptions->offset : "";

		 

		// query

		$nsPrefixProp = $this->tsNamespace->getNSPrefix($property->getWikiPageValue()->getTitle()->getNamespace());
		if (isset($smwgTripleStoreQuadMode) && $smwgTripleStoreQuadMode == true) {
			$response = $client->query("SELECT ?_prov_s ?s ?o WHERE { GRAPH ?_prov_s { ?s <$smwgTripleStoreGraph/$nsPrefixProp#$propertyName> ?o. } } ORDER BY ASC(?s) $limit $offset",  "merge=false");
		} else {
			$response = $client->query("SELECT ?_prov_s ?s ?o WHERE { ?_prov_s <$smwgTripleStoreGraph/$nsPrefixProp#$propertyName> ?o. } ORDER BY ASC(?s) $limit $offset ",  "merge=false");
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
			$provenance = $sv->attributes()->provenance;
			$title = $this->getTitleFromURI((string) $sv, $provenance);
			$title_dv = SMWDataValueFactory::newTypeIDValue('_wpg');
            $title_dv->setValues($title->getDBkey(), $title->getNamespace(), $title->getArticleID());
			

			$b = $children->binding[1];
			foreach($b->children()->uri as $sv) {
				$provenance = $sv->attributes()->provenance;
				$object = $this->getTitleFromURI((string) $sv, $provenance);
				$value = SMWDataValueFactory::newPropertyObjectValue($property, $object);
				$values[] = $value;

			}
			foreach($b->children()->literal as $sv) {
				$literal = array((string) $sv, $sv->attributes()->datatype);
				$value = $this->getLiteral($literal, $property);
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
		global $smwgTripleStoreGraph, $smwgTripleStoreQuadMode;
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
		if (isset($smwgTripleStoreQuadMode) && $smwgTripleStoreQuadMode == true) {
			$response = $client->query("SELECT ?o WHERE { GRAPH ?g { <$smwgTripleStoreGraph/$nsPrefix#$subjctName> <$smwgTripleStoreGraph/$nsPrefixProp#$propertyName> ?o. } } $limit $offset",  "");
		} else {
			$response = $client->query("SELECT ?o WHERE { <$smwgTripleStoreGraph/$nsPrefix#$subjctName> <$smwgTripleStoreGraph/$nsPrefixProp#$propertyName> ?o. } $limit $offset",  "");
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
				$provenance = $sv->attributes()->provenance;
				$object = $this->getTitleFromURI((string) $sv, $provenance);
				$value = SMWDataValueFactory::newPropertyObjectValue($predicate, $object);
				$values[] = $value;

			}
			foreach($b->children()->literal as $sv) {
				$literal = array((string) $sv, $sv->attributes()->datatype);
				$value = $this->getLiteral($literal, $predicate);
				$values[] = $value;
			}



		}

		return $values;
	}

	function getPropertySubjects(SMWPropertyValue $property, $value, $requestoptions = NULL) {
		if (!$property->isUserDefined()) {
			return parent::getPropertySubjects($property, $value, $requestoptions);
		}

		global $smwgTripleStoreGraph, $smwgTripleStoreQuadMode;
		$client = TSConnection::getConnector();
		$client->connect();

		$values = array();


		$propertyName = $property->getWikiPageValue()->getTitle()->getDBkey();
		$limit =  isset($requestoptions->limit) ? " LIMIT ".$requestoptions->limit : "";
		$offset =  isset($requestoptions->offset) ? " OFFSET ".$requestoptions->offset : "";

		$nsPrefixProp = $this->tsNamespace->getNSPrefix($property->getWikiPageValue()->getTitle()->getNamespace());
		if (is_null($value)) {
			if (isset($smwgTripleStoreQuadMode) && $smwgTripleStoreQuadMode == true) {
				$response = $client->query("SELECT ?s WHERE { GRAPH ?g { ?s <$smwgTripleStoreGraph/$nsPrefixProp#$propertyName> ?o. } } $limit $offset",  "");
			} else {
				$response = $client->query("SELECT ?s WHERE { ?s <$smwgTripleStoreGraph/$nsPrefixProp#$propertyName> ?o. } $limit $offset",  "");
			}
		} else if ($value instanceof SMWWikiPageValue) {
			$objectName = $value->getTitle()->getDBkey();
			$nsPrefixObj = $this->tsNamespace->getNSPrefix($value->getTitle()->getNamespace());
			if (isset($smwgTripleStoreQuadMode) && $smwgTripleStoreQuadMode == true) {
				$response = $client->query("SELECT ?s WHERE { GRAPH ?g { ?s <$smwgTripleStoreGraph/$nsPrefixProp#$propertyName> <$smwgTripleStoreGraph/$nsPrefixObj#$objectName>. } } $limit $offset",  "");
			} else {
				$response = $client->query("SELECT ?s WHERE { <$smwgTripleStoreGraph/$nsPrefix#$instanceName> <$smwgTripleStoreGraph/$nsPrefixProp#$propertyName> <$smwgTripleStoreGraph/$nsPrefixObj#$objectName>. } $limit $offset",  "");
			}
		} else {
			$objectvalue = str_replace('"', '\"', $value->getXSDValue());
			$objecttype = WikiTypeToXSD::getXSDType($value->getTypeID());
			if (isset($smwgTripleStoreQuadMode) && $smwgTripleStoreQuadMode == true) {
				$response = $client->query("SELECT ?s WHERE { GRAPH ?g { ?s <$smwgTripleStoreGraph/$nsPrefixProp#$propertyName> \"$objectvalue\"^^$objecttype. } } $limit $offset",  "");
			} else {
				$response = $client->query("SELECT ?s WHERE { <$smwgTripleStoreGraph/$nsPrefix#$instanceName> <$smwgTripleStoreGraph/$nsPrefixProp#$propertyName> \"$objectvalue\"^^$objecttype. } $limit $offset",  "");
			}
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
			$provenance = $sv->attributes()->provenance;
			$title = $this->getTitleFromURI((string) $sv, $provenance);
			$value = SMWDataValueFactory::newPropertyObjectValue($property, $title);

			$values[] = $value;




		}

		return $values;
	}

	function getAllPropertySubjects(SMWPropertyValue $property, $requestoptions = NULL) {
		return $this->getPropertySubjects($property,NULL,$requestoptions);
	}

	/**
	 * Add an URI to an array of results
	 *
	 * @param string $sv A single value
	 * @param PrintRequest prs
	 * @param array & $allValues
	 */
	protected function addURIToResult($uris, $prs, & $allValues, $outputformat) {
			
		foreach($uris as $uri) {
			list($sv, $provenance) = $uri;

			$nsFound = false;
			foreach (TSNamespaces::getAllNamespaces() as $nsIndsex => $ns) {
				if (stripos($sv, $ns) === 0) {
					$allValues[] = $this->createSMWDataValue($sv, $provenance, $ns, $nsIndsex, $outputformat);
					$nsFound = true;
				}
			}

			if ($nsFound) continue;

			// result with unknown namespace
			if (stripos($sv, TSNamespaces::$UNKNOWN_NS) === 0) {

				if (empty($sv)) {
					$v = SMWDataValueFactory::newTypeIDValue('_wpg');
					if (!is_null($provenance) && $provenance != '' ){
						$v->setProvenance($provenance);
					}
					$allValues[] = $v;
				} else {
					$startNS = strlen(TSNamespaces::$UNKNOWN_NS);
					$length = strpos($sv, "#") - $startNS;
					$ns = intval(substr($sv, $startNS, $length));

                    $nosection = strpos($outputformat,"nosection");
					if ($nosection === false && !is_null($provenance) && $provenance != '' && strpos($provenance, "section=") !== false) {
							
						// UP special behaviour: if provenance contains section, use it as fragment identifier
						$uri_parts = explode("#", $provenance);
						$local = substr($uri_parts[0], strrpos($uri_parts[0], "/")+1);

						$sectionIndex = strpos($uri_parts[1], "section=");
						$section = substr($uri_parts[1], $sectionIndex, strpos($uri_parts[1], "&", $sectionIndex));

						$sections_parts = explode("=", $section);
						$section_name = urldecode($sections_parts[1]);
						$section_name = preg_replace('/\{\{[^}]*\}\}/', '', $section_name);
						$section_name = str_replace("'","",$section_name);
						$title = Title::makeTitle(0, $local, $section_name);
					} else {
						$local = substr($sv, strpos($sv, "#")+1);
						$title = Title::newFromText($local, $ns);
					}
					if (is_null($title)) {
						$title = Title::newFromText(wfMsg('smw_ob_invalidtitle'), $ns);
					}
					$v = SMWDataValueFactory::newTypeIDValue('_wpg');
					$v->setValues($title->getDBkey(), $ns, $title->getArticleID(), false, '', $title->getFragment());
					if (!is_null($provenance) && $provenance != '' ) {
						$v->setProvenance($provenance);
					}
					$allValues[] = $v;
				}
			} else {
				// external URI

				$v = SMWDataValueFactory::newTypeIDValue('_uri');
				$v->setXSDValue($sv);
				if (!is_null($provenance) && $provenance != '') {
					$v->setProvenance($provenance);
				}
				$allValues[] = $v;

			}
		}

	}

	/**
	 * Add a literal to an array of results
	 *
	 * @param string $sv A single value
	 * @param PrintRequest prs
	 * @param array & $allValues
	 */


	/**
	 * Creates  SWMDataValue object from a (possibly) merged result.
	 *
	 * @param string $sv
	 * @param string $nsFragment
	 * @param int $ns
	 * @return SMWDataValue
	 */
	protected function createSMWDataValue($sv, $provenance, $nsFragment, $ns, $outputformat) {
        $nosection = strpos($outputformat,"nosection");
		if ($nosection === false && !is_null($provenance) && $provenance != '' && strpos($provenance, "section=") !== false) {
			// UP special behaviour: if provenance contains section, use it as fragment identifier
			$uri_parts = explode("#", $provenance);
			$local = substr($uri_parts[0], strrpos($uri_parts[0], "/")+1);

			$sectionIndex = strpos($uri_parts[1], "section=");
			$section = substr($uri_parts[1], $sectionIndex, strpos($uri_parts[1], "&", $sectionIndex));

			$sections_parts = explode("=", $section);
			$section_name = urldecode($sections_parts[1]);
			$section_name = preg_replace('/\{\{[^}]*\}\}/', '', $section_name);
			$section_name = str_replace("'","",$section_name);
			$title = Title::makeTitle(0, $local, $section_name);
		} else {
			$local = substr($sv, strlen($nsFragment));
			$title = Title::newFromText($local, $ns);
		}
		if (is_null($title)) {
			$title = Title::newFromText(wfMsg('smw_ob_invalidtitle'), $ns);
		}
		$v = SMWDataValueFactory::newTypeIDValue('_wpg');
		$v->setValues($title->getDBkey(), $ns, $title->getArticleID(), false, '', $title->getFragment());
		if (!is_null($provenance) && $provenance != '' ){
			$v->setProvenance($provenance);
		}
		return $v;

	}

	private function getTitleFromURI($sv, $provenance = "") {

		foreach (TSNamespaces::$ALL_NAMESPACES as $nsIndsex => $ns) {
			if (stripos($sv, $ns) === 0) {
				if (!is_null($provenance) && $provenance != '' && strpos($provenance, "section=") !== false) {
					// UP special behaviour: if provenance contains section, use it as fragment identifier
					$uri_parts = explode("#", $provenance);
					$local = substr($uri_parts[0], strrpos($uri_parts[0], "/")+1);

					$sectionIndex = strpos($uri_parts[1], "section=");
					$section = substr($uri_parts[1], $sectionIndex, strpos($uri_parts[1], "&", $sectionIndex));

					$sections_parts = explode("=", $section);
					$section_name = urldecode($sections_parts[1]);
					$section_name = preg_replace('/\{\{[^}]*\}\}/', '', $section_name);
					$section_name = str_replace("'","",$section_name);
					return Title::makeTitle($nsIndsex, $local, $section_name);
				} else {
					$local = substr($sv, strlen($ns));
					return Title::newFromText($local, $nsIndsex);
				}

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
				if ($literalValue != '') $value->setXSDValue(str_replace("-","/", $literalValue));
			} else if ($value->getTypeID() == '_ema') { // exception for email
				$value->setXSDValue($literalValue);
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





