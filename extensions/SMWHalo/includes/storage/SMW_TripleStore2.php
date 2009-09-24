<?php
global $smwgIP, $smwgHaloIP;
require_once( "$smwgIP/includes/storage/SMW_Store.php" );
require_once( "$smwgHaloIP/includes/storage/SMW_RuleStore.php" );
require_once( "$smwgHaloIP/includes/storage/stompclient/Stomp.php" );


/**
 * Triple store connector class.
 *
 * This class is able to process the SPARQL-XML compliant output which
 * can be activated in TSC by using the option 'sparql-xml-compliant' at
 * startup.
 *
 * @author: Kai
 */

class SMWTripleStore2 extends SMWTripleStore {


	/**
	 * Creates and initializes Triple store connector.
	 *
	 * @param SMWStore $smwstore All calls are delegated to this implementation.
	 */
	function __construct() {
		parent::__construct();

	}


	/**
	 * Parses a SPARQL XML-Result and returns an SMWQueryResult.
	 *
	 * @param SMWQuery $query
	 * @param xml string $sparqlXMLResult
	 * @return SMWQueryResult
	 */
	protected function parseSPARQLXMLResult(& $query, & $sparqlXMLResult) {

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
			$data = SMWPropertyValue::makeUserProperty($var_name);
			$prs[] = new SMWPrintRequest(SMWPrintRequest::PRINT_THIS, str_replace("_"," ",$var_name), $data);


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

				$allValues = array();

				$bindingsChildren = $b->children();
				$uris = array();
					
				foreach($bindingsChildren->uri as $sv) {
					$uris[] = (string) $sv;
				}
				if (!empty($uris)) {
					$this->addURIToResult($uris, $prs[$resultColumn], $allValues);
				} else {
					$literals = array();
					foreach($bindingsChildren->literal as $sv) {
						$literals[] = array((string) $sv, $sv->attributes()->datatype);
					}
					if (!empty($literals)) $this->addLiteralToResult($literals, $prs[$resultColumn], $allValues);
				}
				// note: ignore bnodes

				$columnIndex++;
				$row[$resultColumn] = new SMWResultArray($allValues, $prs[$resultColumn]);
			}

			ksort($row);
			$queryResult->addRow($row);
		}

		return $queryResult;
	}

	/**
	 * Add an URI to an array of results
	 *
	 * @param string $sv A single value
	 * @param PrintRequest prs
	 * @param array & $allValues
	 */
	protected function addURIToResult($uris, $prs, & $allValues) {
			
		foreach($uris as $sv) {
			$nsFound = false;
			foreach (parent::$ALL_NAMESPACES as $nsIndsex => $ns) {
				if (stripos($sv, $ns) === 0) {
					$allValues[] = $this->createSMWDataValue($sv, $ns, $nsIndsex);
					$nsFound = true;
				}
			}

			if ($nsFound) continue;

			// result with unknown namespace
			if (stripos($sv, parent::$UNKNOWN_NS) === 0) {

				if (empty($sv)) {
					$v = SMWDataValueFactory::newTypeIDValue('_wpg');
					$allValues[] = $v;
				} else {
					$startNS = strlen(parent::$UNKNOWN_NS);
					$length = strpos($sv, "#") - $startNS;
					$ns = intval(substr($sv, $startNS, $length));

					$local = substr($sv, strpos($sv, "#")+1);

					$title = Title::newFromText($local, $ns);
					$v = SMWDataValueFactory::newTypeIDValue('_wpg');
					$v->setValues($title->getDBkey(), $ns, $title->getArticleID());
					$allValues[] = $v;
				}
			} else {
				// external URI
				$v = SMWDataValueFactory::newTypeIDValue('_uri');
				$v->setXSDValue($sv);
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
	protected function addLiteralToResult($literals, $prs, & $allValues) {
		foreach($literals as $literal) {

			list($literalValue, $literalType) = $literal;
			if (!empty($literalValue)) {

				// create SMWDataValue either by property or if that is not possible by the given XSD type
				if ($property instanceof SMWPropertyValue ) {
					$value = SMWDataValueFactory::newPropertyObjectValue($prs->getData(), $literalValue);
				} else {
					$value = SMWDataValueFactory::newTypeIDValue(self::getWikiType($literalType));
				}
				if ($value->getTypeID() == '_dat') { // exception for dateTime
					if ($literalValue != '') $value->setXSDValue($$literalValue);
				} if ($value->getTypeID() == '_ema') { // exception for email
					$value->setXSDValue($literalValue);
				} else {
					$value->setUserValue($literalValue);
				}
			} else {
				$property = $prs->getData();
				if ($property instanceof SMWPropertyValue ) {
					$value = SMWDataValueFactory::newPropertyObjectValue($property);
				} else {
					$value = SMWDataValueFactory::newTypeIDValue('_wpg');

				}

			}
			$allValues[] = $value;
		}
	}

	/**
	 * Translates XSD-URIs to wiki datatype IDs.
	 * @param $xsdURI
	 * @return unknown_type
	 */
	private static function getWikiType($xsdURI) {
		$hashIndex = strpos($xsdURI, '#');
		if ($hashIndex !== false) {
			$xsdURI = substr($xsdURI, $hashIndex+1);
		}
		switch($xsdURI) {
			case 'string': return "_str";
			case 'float': return "_num";
			case 'double': return "_num";
			case 'boolean': return "_boo";
			case 'dateTime': return "_dat";
			default: return "_str";

		}
	}

}






