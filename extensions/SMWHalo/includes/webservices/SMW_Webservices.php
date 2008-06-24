<?php
// import WS functions
require_once 'SMW_EQI.php';

// disable wsdl cache
ini_set("soap.wsdl_cache_enabled", "0");
// use eqi.wsdl in root directory 
$server = new SoapServer("extensions/SMWHalo/includes/webservices/eqi.wsdl"); 

// add additional functions if neccessary
$server->addFunction("query"); 

// execute
$server->handle(); 
?>