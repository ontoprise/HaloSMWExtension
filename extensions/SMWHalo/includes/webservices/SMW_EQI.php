<?php
/**
 * @file
 * @ingroup SMWHaloWebservices
 * @author: Kai
 */

/**
 * Returns query results in the SPARQL XML format.
 *  
 * Serves as entry point for the wiki SOAP server as well as for answering 
 * queries via ajax interface.
 * 
 * @param string $queryString in ASK or SPARQL syntax
 * @return XML string
 */
function smwhExternalQuery($rawQuery, $format = "xml") {
	$mediaWikiLocation = dirname(__FILE__) . '/../../..';

	global $smwgHaloIP;
    
	require_once "$smwgHaloIP/includes/queryprinters/SMW_QP_XML.php";

	global $smwgHaloWebserviceEndpoint;
	$eqi = new ExternalQueryInterface();

	// source == null means default (SMW reasoner)
	$params = $eqi->parseParameters($rawQuery);
	$source = array_key_exists("source", $params) ? $params['source'] : NULL;
	$query = $params['query'];

	// check if source other than default or smw
	if (!is_null($source) && $source == 'tsc') {
		// TSC
		// if webservice endpoint is set, sent to TSC
		if (smwfIsTripleStoreConfigured()) {
			global $tscgIP;
			require_once $tscgIP.'/includes/triplestore_client/SMW_TSConnection.php';
            require_once "$tscgIP/includes/query_processor/SMW_QueryProcessor.php";
			return $eqi->answerSPARQL($query, $eqi->serializeParams($params));
		} else {
			// fallback, redirect to SMW
			return $eqi->answerASK($rawQuery, $format);
		}
			
	} else {
		// SMW

		// truncate any parameters or printouts, before parsing
		$paramPos = strpos($rawQuery, "|");
		if ($paramPos === false) {
			$queryString = $rawQuery;
		} else {
			$queryString = substr($rawQuery, 0, $paramPos);
		}

		// answer query
		$query = SMWQueryProcessor::createQuery($queryString, array(), false);
		if (count($query->getErrors()) > 0) {
			throw new Exception(implode("",$query->getErrors()));
		} else {
			return $eqi->answerASK($rawQuery, $format);
		}
	}
}

/**
 * Handles RDF requests to the triplestore.  
 * 
 * @param string $subject prefixed title 
 * @return RDF/XML all triples about the subject
 */
function smwhRDFRequest($subject) {
	global $wgLanguageCode;
	smwfHaloInitContentLanguage($wgLanguageCode);
	if (!smwfIsTripleStoreConfigured()) throw Exception("TS not configured");
	global $smwgHaloTripleStoreGraph;
	
	// get wiki URI from prefixed title
	$title = Title::newFromText($subject);
	$ts = TSNamespaces::getInstance();
	$iri = TSHelper::getUriFromTitle($title);
	$iri = $ts->getFullIRI($title);
	
	// request RDF/XML via CONSTRUCT query
	$con = TSConnection::getConnector();
	$con->connect();
	$rdf = $con->queryRDF("CONSTRUCT { $iri ?p ?o. $iri owl:sameAs ?source. } WHERE { GRAPH ?g { $iri ?p ?o. } OPTIONAL { GRAPH ?g2 { $iri prop:Imported_from ?source. } } }");
	return $rdf;
}

/**
 * External query interface which handles the requests
 * 
 * @author kuehn
 *
 */
class ExternalQueryInterface {

	/**
	 * Extracts query a parameters. Can handle SPARQL queries
	 *
	 * @param $rawQuery
	 * @return map of parameters with 'query' as special key for the query.
	 */
	function parseParameters($rawQuery) {
		$fragments = explode("|", $rawQuery);
		$params = array();
		$j=0;

		// make sure that || is not split (it may be the logical OR in SPARQL)
		for($i=0; $i < count($fragments);$i++) {
			if ($fragments[$i] == '') {
				$params[$j-1] .= "||".$fragments[$i+1];
				$i++;
			} else {
				$j++;
				$params[$j-1] = $fragments[$i];

			}
		}
		$map = array();
		// always assume that query is the first parameter
		$map['query'] = array_shift($params);

		// put all other parameters in a map
		foreach($params as $p) {
			$fragments = explode("|", $p); // again to split double ||
			foreach($fragments as $f) {
				if (trim($f) == '') continue;
				$keyValue = explode("=", $f);
				if (count($keyValue) == 1) $map[$keyValue[0]] = true;
				if (count($keyValue) == 2) $map[$keyValue[0]] = $keyValue[1];
			}
		}
		return $map;
	}
    
	/**
	 * Serializes parameters. Ignores query.
	 * 
	 * @param $paramMap
	 * return string. 
	 */
	function serializeParams($paramMap) {
		$result = "";
		foreach($paramMap as $key => $value) {
			if ($key == 'query') continue;
			if ($value === true) {
				$result .= "|$key";
			} else {
				$result .= "|$key=$value";
			}
		}
		return strlen($result) > 0 ? substr($result, 1) : "";
	}
	/**
	 * Answers a ASK query.
	 *
	 * @param string $rawQuery
	 * @return SPARQL XML string
	 */
	function answerASK($rawquery, $format = "xml") {

		// add desired query printer (SPARQL-XML)
		if (property_exists('SMWQueryProcessor','formats')) { // registration up to SMW 1.2.*
			SMWQueryProcessor::$formats['xml'] = 'SMWXMLResultPrinter'; // overwrite SMW printer

		} else { // registration since SMW 1.3.*
			global $smwgResultFormats;
			$smwgResultFormats['xml'] = 'SMWXMLResultPrinter';
		}

		// add query as first rawparam
		$paramPos = strpos($rawquery, "|");
		$rawparams[] = $paramPos === false ? $rawquery : substr($rawquery, 0, $paramPos);
		if ($paramPos !== false) {
			// add other params
			$ps = explode("|", substr($rawquery, $paramPos + 1));
			foreach ($ps as $param) {
				$param = trim($param);
				$rawparams[] = $param;
			}
		}

		// parse params and answer query
		SMWQueryProcessor::processFunctionParams($rawparams,$querystring,$params,$printouts);
		$params['format'] = $format;
		return SMWQueryProcessor::getResultFromQueryString($querystring,$params,$printouts, SMW_OUTPUT_FILE);

			
	}

	/**
	 * Answers a SPARQL query.
	 *
	 * @param string $rawQuery
	 * @return SPARQL XML string
	 * @throws Exception, SOAPExeption
	 */
	function answerSPARQL($query, $params) {
		global $smwgHaloTripleStoreGraph, $smwgWebserviceProtocol;

		if (isset($smwgWebserviceProtocol) && strtolower($smwgWebserviceProtocol) === 'rest') {

			$con = TSConnection::getConnector();
			$con->connect();
			return $con->query($query, $params, $smwgHaloTripleStoreGraph);
			
		} else {
			trigger_error("SOAP requests to TSC are not supported anymore.");
		}
	}


}

