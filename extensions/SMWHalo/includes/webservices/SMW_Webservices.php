<?php
/**
 * @file
 * @ingroup SMWHaloWebservices
 * 
 */
// import WS functions
require_once 'SMW_EQI.php';
global $smwgDeployVersion;
// disable wsdl cache
if (!isset($smwgDeployVersion) || !$smwgDeployVersion) ini_set("soap.wsdl_cache_enabled", "0");
// use eqi.wsdl in root directory 
$server = new SoapServer("extensions/SMWHalo/includes/webservices/eqi.wsdl"); 

// add additional functions if neccessary
$server->addFunction("query"); 

// execute
$server->handle(); 
