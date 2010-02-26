<?php

/**
 * @file
  * @ingroup DAPCPExample
  *
  * @author Dian
 */

// the path to your wiki installation home
chdir('/xampp/htdocs/wiki');
require_once('/xampp/htdocs/wiki/includes/Webstart.php');

$readTest = new PCPServer();
$uc = new PCPUserCredentials("TestUser", "TestPassword");
if ($readTest->login($uc)){	
	echo $readTest->readPage($uc,"Main Page");
	$readTest->logout();
}else{
	print ("ERROR: Testing failed!");
}

