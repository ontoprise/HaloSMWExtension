<?php

global $smwgDIIP;	
$smwgDIIP = "D:/devel/workspace-wtp/HaloSMWExtensionSVN/extensions/DataImport";

require_once("SMW_RESTClient.php");

$client = new SMWRestClient("http://api.opencalais.com/enlighten/rest", "","","");

$result = $client->call("post", array("licenseID" => "y3x8744tt2ev6rybpt8xpxnx", "content" => "This is another test.", "__post__separator" => "#########"));

print_r("result:\n".$result);

?>