<?php
require_once 'SMW_AddIn2.php';

// this webservice can be called in other programs via the following code
// global $wgServer, $wgScript, $smwgHaloWebserviceUser, $smwgHaloWebservicePassword;
// $client = new SoapClient("$wgServer$wgScript?action=ajax&rs=smwf_wt_getWSDL&rsargs[]=get_addin", 
// 		array('login'=>$smwgHaloWebserviceUser, 'password'=>$smwgHaloWebservicePassword));

// disable wsdl cache
ini_set("soap.wsdl_cache_enabled", "0");

$servidorSoap = new SoapServer("extensions/SemanticWikiTag/includes/webservices/addin2.wsdl");

$servidorSoap->setClass("AddIn2");
$servidorSoap->handle();

?>