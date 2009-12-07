<?php
# Provides WSDLs for external interfaces

$wgAjaxExportList[] = 'srf_ws_getWSDL';

#
# Returns WSDL file for wiki webservices
#
function srf_ws_getWSDL($wsdlID) {
    if ($wsdlID == 'get_flogic') {
        $wsdl = "extensions/SemanticRules/includes/webservices/flogic.wsdl";
        $handle = fopen($wsdl, "rb");
        $contents = fread ($handle, filesize ($wsdl));
        fclose($handle);
        global $smwgWebserviceEndpoint;
        if (isset($smwgWebserviceEndpoint)) return str_replace("{{webservice-endpoint}}", smwfgetWebserviceEndpoint($smwgWebserviceEndpoint), $contents);
        else echo "No webservice endpoint defined! Set \$smwgWebserviceEndpoint in your LocalSettings.php. E.g.: \$smwgWebserviceEndpoint = \"localhost:8080\"";
        exit;
    } else if ($wsdlID == 'get_explanation') {
        $wsdl = "extensions/SemanticRules/includes/webservices/explanation.wsdl";
        $handle = fopen($wsdl, "rb");
        $contents = fread ($handle, filesize ($wsdl));
        fclose($handle);
        global $smwgWebserviceEndpoint;
        if (isset($smwgWebserviceEndpoint)) return str_replace("{{webservice-endpoint}}", smwfgetWebserviceEndpoint($smwgWebserviceEndpoint), $contents);
        else echo "No webservice endpoint defined! Set \$smwgWebserviceEndpoint in your LocalSettings.php. E.g.: \$smwgWebserviceEndpoint = \"localhost:8080\"";
        exit;
    }
}   

