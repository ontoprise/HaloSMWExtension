<?php
/*
 * Usage: create wsdl xml file
 * author Yongjun
 */
require_once 'SMW_AddIn.php';

// disable wsdl cache
ini_set("soap.wsdl_cache_enabled", "0");
global $preIP;
// use eqi.wsdl in root directory 
$servidorSoap = new SoapServer("extensions/SemanticWikiTag/includes/webservices/addin.wsdl");

$servidorSoap->setClass("AddIn");
$servidorSoap->handle();

?>