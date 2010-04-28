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
function query($rawQuery, $format = "xml") {
	$mediaWikiLocation = dirname(__FILE__) . '/../../..';

	global $smwgHaloIP;
    require_once $smwgHaloIP.'/includes/storage/SMW_RESTWebserviceConnector.php';
	require_once "$mediaWikiLocation/SemanticMediaWiki/includes/SMW_QueryProcessor.php";
	require_once "$mediaWikiLocation/SMWHalo/includes/queryprinters/SMW_QP_XML.php";

	global $smwgWebserviceEndpoint;
	$eqi = new ExternalQueryInterface();

	// source == null means default (SMW reasoner)
	$params = $eqi->parseParameters($rawQuery);
	$source = array_key_exists("source", $params) ? $params['source'] : NULL;
	$query = $params['query'];

	// check if source other than default or smw
	if (!is_null($source) && $source != 'smw') {
		// TSC
		// if webservice endpoint is set, sent to TSC
		if (isset($smwgWebserviceEndpoint)) {
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
			throw new Exception($query->getErrors());
		} else {
			return $eqi->answerASK($rawQuery, $format);
		}
	}
}

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
		global $wgServer, $wgScript, $smwgWebserviceProtocol, $smwgWebserviceUser, $smwgWebservicePassword, $smwgUseLocalhostForWSDL;

		if (isset($smwgWebserviceProtocol) && strtolower($smwgWebserviceProtocol) === 'rest') {

			global $smwgTripleStoreGraph;
			if (stripos(trim($query), 'SELECT') === 0 || stripos(trim($query), 'PREFIX') === 0) {
				// SPARQL, attach common prefixes
				$query = TSNamespaces::getAllPrefixes().$query;
			}
			$queryRequest = "<query>";
			$queryRequest .= "<text><![CDATA[$query]]></text>";
			$queryRequest .= "<params><![CDATA[$params]]></params>";
			$queryRequest .= "<graph><![CDATA[$smwgTripleStoreGraph]]></graph>";
			$queryRequest .= "</query>";

			global $smwgWebserviceUser, $smwgWebservicePassword, $smwgWebserviceEndpoint;
			list($host, $port) = explode(":", $smwgWebserviceEndpoint);
			$credentials = isset($smwgWebserviceUser) ? $smwgWebserviceUser.":".$smwgWebservicePassword : "";
			$queryClient = new RESTWebserviceConnector($host, $port, "sparql", $credentials);

			list($header, $status, $result) = $queryClient->send($queryRequest);
			if ($status != 200) {
				throw new Exception(strip_tags($result), $status);
			}
			return $result;
		} else {
			if (isset($smwgUseLocalhostForWSDL) && $smwgUseLocalhostForWSDL === true) $host = "http://localhost"; else $host = $wgServer;
			$client = new SoapClient("$host$wgScript?action=ajax&rs=smwf_ws_getWSDL&rsargs[]=get_sparql", array('login'=>$smwgWebserviceUser, 'password'=>$smwgWebservicePassword));


			global $smwgTripleStoreGraph;
			$response = $client->query($query, $smwgTripleStoreGraph, $params);
			return $response;
		}
	}


}

