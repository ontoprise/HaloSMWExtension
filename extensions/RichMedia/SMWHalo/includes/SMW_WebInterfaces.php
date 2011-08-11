<?php
/**
 * @file
 * @ingroup SMWHaloWebservices
 * 
 * @defgroup SMWHaloWebservices SMWHalo Webservices
 * @ingroup SMWHalo
 */
# Provides WSDLs for external interfaces

$wgAjaxExportList[] = 'smwf_ws_callEQI';
$wgAjaxExportList[] = 'smwf_ws_callEQIXML';
$wgAjaxExportList[] = 'smwf_ws_getWSDL';
$wgAjaxExportList[] = 'smwf_ws_RDFRequest';

#
# Handle 'quick query' call
# (answers URL-encoded ASK queries formatted as table in HTML)
#
function smwf_ws_callEQI($query) {
	global $IP;
	require_once( $IP . '/extensions/SMWHalo/includes/webservices/SMW_EQI.php' );
	return smwhExternalQuery($query, "exceltable");
}

# same as smwf_ws_callEQI except that XML is returned
function smwf_ws_callEQIXML($query) {
	
	global $IP;
	require_once( $IP . '/extensions/SMWHalo/includes/webservices/SMW_EQI.php' );
	$result= new AjaxResponse( smwhExternalQuery($query, "xml") );
	$result->setContentType( "application/sparql-xml" );
	return $result;
}

# RDF request. Requires triple store
function smwf_ws_RDFRequest($subject) {
    global $IP;
    require_once( $IP . '/extensions/SMWHalo/includes/webservices/SMW_EQI.php' );
    $result= new AjaxResponse( smwhRDFRequest($subject) );
    $result->setContentType( "application/rdf+xml" );
    return $result;
}



#
# Returns WSDL file for wiki webservices
#
function smwf_ws_getWSDL($wsdlID) {
	if ($wsdlID == 'get_eqi') {
		$wsdl = "extensions/SMWHalo/includes/webservices/eqi.wsdl";
		$handle = fopen($wsdl, "rb");
		$contents = fread ($handle, filesize ($wsdl));
		fclose($handle);
		global $wgServer, $wgScript;
		return str_replace("{{wiki-path}}", $wgServer.$wgScript, $contents);

	} 
}


