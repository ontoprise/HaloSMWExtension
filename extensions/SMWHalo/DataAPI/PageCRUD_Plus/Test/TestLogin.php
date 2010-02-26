<?php

/**
 * @file
  * @ingroup DAPCPTest
  * 
  * @author Dian
 */

chdir('F:\xampp\htdocs\mw');
require_once('F:/xampp/htdocs/mw/includes/Webstart.php');


$uc = new PCPUserCredentials("WikiSysop", "!>ontoprise?");
$ws = new PCPWikiSystem("http://localhost/mw");
$loginTest = new PCPClient($uc,$ws);
$loginTest->cookieFile = getcwd()."/cookiejar.txt";
if ($loginTest->login($uc)){
	var_dump($loginTest->readPage(NULL, "Main Page"));
}


