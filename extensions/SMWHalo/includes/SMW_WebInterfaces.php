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
        global $wgServer, $wgScript;
		return str_replace("{{wiki-path}}", $wgServer.$wgScript, $contents);
		
	} else if ($wsdlID == 'get_sparql') {
		$wsdl = "extensions/SMWHalo/includes/webservices/sparql.wsdl";
		$handle = fopen($wsdl, "rb");
		$contents = fread ($handle, filesize ($wsdl));
		fclose($handle);
		global $smwgWebserviceEndpoint;
		if (isset($smwgWebserviceEndpoint)) return str_replace("{{webservice-endpoint}}", $smwgWebserviceEndpoint, $contents);
		else echo "No webservice endpoint defined! Set \$smwgWebserviceEndpoint in your LocalSettings.php. E.g.: \$smwgWebserviceEndpoint = \"localhost:8080\"";
		exit;
	}  else if ($wsdlID == 'get_manage') {
        $wsdl = "extensions/SMWHalo/includes/webservices/manage.wsdl";
        $handle = fopen($wsdl, "rb");
        $contents = fread ($handle, filesize ($wsdl));
        fclose($handle);
        global $smwgWebserviceEndpoint;
        if (isset($smwgWebserviceEndpoint)) return str_replace("{{webservice-endpoint}}", $smwgWebserviceEndpoint, $contents);
        else echo "No webservice endpoint defined! Set \$smwgWebserviceEndpoint in your LocalSettings.php. E.g.: \$smwgWebserviceEndpoint = \"localhost:8080\"";
        exit;
    } else if ($wsdlID == 'get_flogic') {
		$wsdl = "extensions/SMWHalo/includes/webservices/flogic.wsdl";
		$handle = fopen($wsdl, "rb");
		$contents = fread ($handle, filesize ($wsdl));
		fclose($handle);
		global $smwgWebserviceEndpoint;
		if (isset($smwgWebserviceEndpoint)) return str_replace("{{webservice-endpoint}}", $smwgWebserviceEndpoint, $contents);
		else echo "No webservice endpoint defined! Set \$smwgWebserviceEndpoint in your LocalSettings.php. E.g.: \$smwgWebserviceEndpoint = \"localhost:8080\"";
		exit;
	} else if ($wsdlID == 'get_explanation') {
		$wsdl = "extensions/SMWHalo/includes/webservices/explanation.wsdl";
		$handle = fopen($wsdl, "rb");
		$contents = fread ($handle, filesize ($wsdl));
		fclose($handle);
		global $smwgWebserviceEndpoint;
		if (isset($smwgWebserviceEndpoint)) return str_replace("{{webservice-endpoint}}", $smwgWebserviceEndpoint, $contents);
		else echo "No webservice endpoint defined! Set \$smwgWebserviceEndpoint in your LocalSettings.php. E.g.: \$smwgWebserviceEndpoint = \"localhost:8080\"";
		exit;
	}
}
?>