<?php

/**
 * Returns query results in the SPARQL XML format.
 *
 * @param string $queryString in ASK or SPARQL syntax
 * @return XML string
 */
function query($queryString) { 
  
  $mediaWikiLocation = dirname(__FILE__) . '/../../..';
  require_once "$mediaWikiLocation/SemanticMediaWiki/includes/SMW_QueryProcessor.php";
  require_once "$mediaWikiLocation/SMWHalo/includes/SMW_QP_XML.php";
  
  // use heuristic to optimize parsing order
  if (stripos($queryString, "SELECT") !== false) {
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
        $query = $parser->parse($queryString);
    return answerSPARQL($queryString, $query);
    } catch(SparqlParserException $e) {
	    $query = SMWQueryProcessor::createQuery($queryString, false, "xml");
	    if (count($query->getErrors()) > 0) {
	    	// probably SPARQL query, so return SPARQL parser error
	       return new SoapFault("error_mf_query","Malformed Query","SMWPlus",$e->getMessage());
	    } else {
	    	return answerASK($queryString, $query);
	    }
    }
       
  } else {
  	// the other way round
	$query = SMWQueryProcessor::createQuery($queryString, false, "xml");
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
	       $query = $parser->parse($queryString);
	    return answerSPARQL($queryString, $query);
	    } catch(SparqlParserException $e) {
	         $errors = implode(",",$query->getErrors());
		     // probably ASK query, so return SMWProcessor error
		     return new SoapFault("error_mf_query","Malformed Query","SMWPlus",$errors);
	    }
	  
	} else {
		return answerASK($queryString, $query);
	}
  }
} 

function answerASK($queryString, $query) {
  SMWQueryProcessor::$formats['xml'] = 'SMWXMLResultPrinter';
  $result =  SMWQueryProcessor::getResultFromHookParams($queryString,array('format' => 'xml'),SMW_OUTPUT_HTML);
  return $result;
}

function answerSPARQL($queryString, $query) {
	//TODO: ask triple store
	return "Answer SPARQL";
}

?>