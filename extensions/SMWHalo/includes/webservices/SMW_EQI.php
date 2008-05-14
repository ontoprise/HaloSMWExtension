<?php

/**
 * Returns query results in the SPARQL XML format.
 *
 * @param string $queryString
 * @return XML string
 */
function query($queryString) { 
  $mediaWikiLocation = dirname(__FILE__) . '/../../..';
  require_once "$mediaWikiLocation/SemanticMediaWiki/includes/SMW_QueryProcessor.php";
  require_once "$mediaWikiLocation/SMWHalo/includes/SMW_QP_XML.php";
  
  SMWQueryProcessor::$formats['xml'] = 'SMWXMLResultPrinter';
  $result =  SMWQueryProcessor::getResultFromHookParams($queryString,array('format' => 'xml'),SMW_OUTPUT_HTML);
  return $result;
} 


?>