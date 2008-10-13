<?php
# Provides WSDLs for external interfaces

$wgAjaxExportList[] = 'smwf_ws_callEQI';
$wgAjaxExportList[] = 'smwf_ws_getWSDL';


#
# Handle 'quick query' call
# (answers URL-encoded ASK queries formatted as table in HTML)
#
function smwf_ws_callEQI($query) {
	global $IP;
	require_once( $IP . '/extensions/SMWHalo/includes/webservices/SMW_EQI.php' );
	return query($query, "exceltable");
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

		return str_replace("{{wiki-path}}", $_SERVER['SERVER_ADDR'].":".$_SERVER['SERVER_PORT'].$_SERVER['SCRIPT_NAME'], $contents);
		
	} else if ($wsdlID == 'get_sparql') {
		$wsdl = "extensions/SMWHalo/includes/webservices/sparql.wsdl";
		$handle = fopen($wsdl, "rb");
		$contents = fread ($handle, filesize ($wsdl));
		fclose($handle);
		global $smwgSPARQLEndpoint;
		if (isset($smwgSPARQLEndpoint)) echo str_replace("{{sparql-endpoint}}", $smwgSPARQLEndpoint, $contents);
		else echo "No SPARQL endpoint defined! Set \$smwgSPARQLEndpoint in your LocalSettings.php. E.g.: \$smwgSPARQLEndpoint = \"localhost:8080\"";
		exit;
	} else if ($wsdlID == 'get_flogic') {
		$wsdl = "extensions/SMWHalo/includes/webservices/flogic.wsdl";
		$handle = fopen($wsdl, "rb");
		$contents = fread ($handle, filesize ($wsdl));
		fclose($handle);
		global $smwgFlogicEndpoint;
		if (isset($smwgFlogicEndpoint)) echo str_replace("{{flogic-endpoint}}", $smwgFlogicEndpoint, $contents);
		else echo "No FLogic endpoint defined! Set \$smwgFlogicEndpoint in your LocalSettings.php. E.g.: \$smwgFlogicEndpoint = \"localhost:8080\"";
		exit;
	} else if ($wsdlID == 'get_explanation') {
		$wsdl = "extensions/SMWHalo/includes/webservices/explanation.wsdl";
		$handle = fopen($wsdl, "rb");
		$contents = fread ($handle, filesize ($wsdl));
		fclose($handle);
		global $smwgExplanationEndpoint;
		if (isset($smwgExplanationEndpoint)) echo str_replace("{{explanation-endpoint}}", $smwgExplanationEndpoint, $contents);
		else echo "No Explanation endpoint defined! Set \$smwgExplanationEndpoint in your LocalSettings.php. E.g.: \$smwgExplanationEndpoint = \"localhost:8080\"";
		exit;
	}
}
?>