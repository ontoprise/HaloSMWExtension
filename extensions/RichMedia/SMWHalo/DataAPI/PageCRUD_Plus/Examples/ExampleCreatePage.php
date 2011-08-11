<?php

/**
 * @file
  * @ingroup DAPCPExample
  * 
  * @author Dian
 */

/**
 * This group contains all parts of the DataAPI that provide examples for the PCP component
 * @defgroup DAPCPExample
 * @ingroup DAPCP
 */

// the path to your wiki installation home
chdir('/xampp/htdocs/wiki');
require_once('/xampp/htdocs/wiki/includes/Webstart.php');

$createTest = new PCPServer();
$uc = new PCPUserCredentials("TestUser", "TestPassword");
if ($createTest->login($uc)){	
	echo $createTest->createPage($uc, "A test page", "Adding some content");
	$createTest->logout();
}else{
	print ("ERROR: Testing failed!");
}

