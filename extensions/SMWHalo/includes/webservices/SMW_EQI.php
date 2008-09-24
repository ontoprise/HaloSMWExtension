<?php

/**
 * Returns query results in the SPARQL XML format.
 *
 * @param string $queryString in ASK or SPARQL syntax
 * @return XML string
 */
function query($rawQuery, $format = "xml") {
	$mediaWikiLocation = dirname(__FILE__) . '/../../..';

	//TODO: import a triple store config file
	require_once "$mediaWikiLocation/SemanticMediaWiki/includes/SMW_QueryProcessor.php";
	require_once "$mediaWikiLocation/SMWHalo/includes/SMW_QP_XML.php";

	global $smwgSPARQLEndpoint;
	$eqi = new ExternalQueryInterface();
	// use heuristic to optimize parsing order
	if (stripos($rawQuery, "SELECT") !== false) {
		// parse possible SPARQL query text before ASK
		$smwgRAPPath = $mediaWikiLocation . "/SemanticMediaWiki/libs/rdfapi-php";
		$Rdfapi_includes= $smwgRAPPath . '/api/';
		define("RDFAPI_INCLUDE_DIR", $Rdfapi_includes); // not sure if the constant is needed within RAP
		include(RDFAPI_INCLUDE_DIR . "RdfAPI.php");
		include( RDFAPI_INCLUDE_DIR . 'vocabulary/RDF_C.php');
		include( RDFAPI_INCLUDE_DIR . 'vocabulary/OWL_C.php');
		include( RDFAPI_INCLUDE_DIR . 'vocabulary/RDFS_C.php');
		include( RDFAPI_INCLUDE_DIR . 'sparql/SparqlParser.php');

		$parser = new SparqlParser();
		try {
			$query = $parser->parse($rawQuery);
			if (isset($smwgSPARQLEndpoint)) {
				return $eqi->answerSPARQL($rawQuery);
			} else {
				// try to convert to ASK
				try {

					$ask = $eqi->transformSPARQLToASK($query);
					return $ask;
					//return $eqi->answerASK($ask);
				} catch(Exception $e) {
					return new SoapFault("error_mf_query","Malformed Query","SMWPlus",$e->getMessage());
				}
			}
		} catch(SparqlParserException $e) {
			$query = SMWQueryProcessor::createQuery($rawQuery, false, "xml");
			if (count($query->getErrors()) > 0) {
				// probably SPARQL query, so return SPARQL parser error
				return new SoapFault("error_mf_query","Malformed Query","SMWPlus",$e->getMessage());
			} else {
				return $eqi->answerASK($rawQuery, $format);
			}
		}
			
	} else {
		// the other way round

		// truncate any parameters or printouts, before parsing
		$paramPos = strpos($rawQuery, "|");
		if ($paramPos === false) {
			$queryString = $rawQuery;
		} else {
			$queryString = substr($rawQuery, 0, $paramPos);
		}
			
		$query = SMWQueryProcessor::createQuery($queryString, array(), false);
		if (count($query->getErrors()) > 0) {
			$smwgRAPPath = $mediaWikiLocation . "/SemanticMediaWiki/libs/rdfapi-php";
			$Rdfapi_includes= $smwgRAPPath . '/api/';
			define("RDFAPI_INCLUDE_DIR", $Rdfapi_includes); // not sure if the constant is needed within RAP
			include(RDFAPI_INCLUDE_DIR . "RdfAPI.php");
			include( RDFAPI_INCLUDE_DIR . 'vocabulary/RDF_C.php');
			include( RDFAPI_INCLUDE_DIR . 'vocabulary/OWL_C.php');
			include( RDFAPI_INCLUDE_DIR . 'vocabulary/RDFS_C.php');
			include( RDFAPI_INCLUDE_DIR . 'sparql/SparqlParser.php');

			$parser = new SparqlParser();
			try {
				$query = $parser->parse($rawQuery);
				return $eqi->answerSPARQL($rawQuery);
			} catch(SparqlParserException $e) {
				$errors = implode(",",$query->getErrors());
				// probably ASK query, so return SMWProcessor error
				return new SoapFault("error_mf_query","Malformed Query","SMWPlus",$errors);
			}

		} else {
			return $eqi->answerASK($rawQuery, $format);
		}
	}
}

class ExternalQueryInterface {

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
        return SMWQueryProcessor::getResultFromQueryString($querystring,$params,$printouts);

			
	}

	/**
	 * Answers a SPARQL query.
	 *
	 * @param string $rawQuery
	 * @return SPARQL XML string
	 */
	function answerSPARQL($rawQuery) {
		global $wgServer, $wgScript;
		$client = new SoapClient("$wgServer$wgScript?action=get_sparql");

		try {
			global $smwgNamespace;
			$response = $client->query($rawQuery, $smwgNamespace);
			return $response;

		} catch(Exception $e) {
			return ""; // What to return here?
		}
	}

	/**
	 * Converts a SPARQL query to ASK syntax
	 *
	 * @param Query $query (object from SPARQLParser)
	 */
	function transformSPARQLToASK($query) {
		require_once 'SMW_SPARQLTransformer.php';
		$st = new SMWSPARQLTransformer($query);
		return $st->transform();

	}
}

class MalformedQueryException extends Exception {

}

?>