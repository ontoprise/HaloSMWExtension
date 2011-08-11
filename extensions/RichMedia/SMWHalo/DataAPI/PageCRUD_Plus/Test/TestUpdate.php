<?php

/**
 * @file
  * @ingroup DAPCPTest
  * 
  * @author Dian
 */

chdir('C:\xampp\htdocs\mw');
require_once('C:/xampp/htdocs/mw/includes/Webstart.php');

$editTest = new PCPServer();
$uc=$editTest->login(new PCPUserCredentials("WikiSysop", "!>ontoprise?"));
	#var_dump($editTest->getCookies());
	#$editTest->getEditToken("Main Page");
	$uc->id = NULL;
	$uc->un = NULL;			
	$editTest->updatePage($uc, "Testpage", "Works again.", "Page updated.");
	$editTest->logout();
//}else{
//	print ("ERROR: Testing failed!".__FILE__);
//}

