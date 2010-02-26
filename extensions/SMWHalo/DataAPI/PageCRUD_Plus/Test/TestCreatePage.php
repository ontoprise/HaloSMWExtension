<?php

/**
 * @file
  * @ingroup DAPCPTest
  * 
  * @author Dian
 */

/**
 * This group contains all parts of the DataAPI that deal with tests for the PCP component
 * @defgroup DAPCPTest
 * @ingroup DAPCP
 */

chdir('F:\xampp\htdocs\mw');
require_once('F:/xampp/htdocs/mw/includes/Webstart.php');

$editTest = new PCPServer();
$uc = new PCPUserCredentials("WikiSysop", "", 1, "93f49f1cb77a00ae03b3eb90069d76dd");
//$editTest->login($uc);
//if ($editTest->login($uc)){
	#var_dump($editTest->getCookies());
	#$editTest->getEditToken("Main Page");
	var_dump($editTest->createPage($uc, "Another test", "Adding some content"));
	$editTest->logout();
//}else{
//	print ("ERROR: Testing failed!".__FILE__);
//}

