<?php

/**
 * @file
  * @ingroup DAPCPTest
  * 
  * @author Dian
 */

chdir('C:\xampp\htdocs\mw');
require_once('C:/xampp/htdocs/mw/includes/Webstart.php');
require_once('C:/xampp/htdocs/mw/extensions/PageCRUD_Plus/WS/Server.php');

//$editTest = new PCPServer();
$uc;// = new PCP_UserCredentials("WikiSysop", "!>ontoprise?");
#if ($editTest->login($uc)){
	#var_dump($editTest->getCookies());
	#$editTest->getEditToken("Main Page");
	#print($uc->toXML());
	
//	var_dump($editTest->readPage(NULL,"Main Page"));
//var_dump($editTest->readPage($uc,"Testeintrag"));
//	$editTest->logout();
#}else{
#	print ("ERROR: Testing failed!".__FILE__);
#}
var_dump(readPage(NULL, NULL,NULL, NULL, NULL, "Main Page",NULL));

