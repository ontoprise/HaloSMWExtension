<?php

/**
 * Returns query results in the SPARQL XML format.
 *
 * @param string $queryString in ASK or SPARQL syntax
 * @return XML string
 */
function query($queryString, $format = "xml") { 
  $mediaWikiLocation = dirname(__FILE__) . '/../../..';
 
  //TODO: import a triple store config file
  require_once "$mediaWikiLocation/SemanticMediaWiki/includes/SMW_QueryProcessor.php";
  require_once "$mediaWikiLocation/SMWHalo/includes/SMW_QP_XML.php";
  
  global $smwgUseTripleStore;
  $eqi = new ExternalQueryInterface();
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
        if (isset($smwgUseTripleStore)) {
            return $eqi->answerSPARQL($queryString);
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
        $query = SMWQueryProcessor::createQuery($queryString, false, "xml");
        if (count($query->getErrors()) > 0) {
            // probably SPARQL query, so return SPARQL parser error
           return new SoapFault("error_mf_query","Malformed Query","SMWPlus",$e->getMessage());
        } else {
            return $eqi->answerASK($queryString, $format);
        }
    }
       
  } else {
    // the other way round
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
           $query = $parser->parse($queryString);
           return $eqi->answerSPARQL($queryString);
        } catch(SparqlParserException $e) {
             $errors = implode(",",$query->getErrors());
             // probably ASK query, so return SMWProcessor error
             return new SoapFault("error_mf_query","Malformed Query","SMWPlus",$errors);
        }
      
    } else {
        return $eqi->answerASK($queryString, $format);
    }
  }
} 

class ExternalQueryInterface {
    
    /**
     * Answers a ASK query.
     *
     * @param string $queryString
     * @return SPARQL XML string
     */
    function answerASK($queryString, $format = "xml") {
      SMWQueryProcessor::$formats['xml'] = 'SMWXMLResultPrinter';
      $result =  SMWQueryProcessor::getResultFromHookParams($queryString,array('format' => $format),SMW_OUTPUT_HTML);
      return $result;
    }
    
    /**
     * Answers a SPARQL query.
     *
     * @param string $queryString
     * @return SPARQL XML string
     */
    function answerSPARQL($queryString) {
        //TODO: ask triple store
        return "Answer SPARQL";
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