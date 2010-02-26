<?php

/**
 * @file
  * @ingroup DAPCPTest
  * 
  * @author Dian
 */

chdir('F:\xampp\htdocs\mw');
require_once('F:/xampp/htdocs/mw/includes/Webstart.php');


$uc = new PCPUserCredentials("WikiSysop", "!>?");
$ws = new PCPWikiSystem("http://localhost/mw");
$loginTest = new PCPServer($uc,$ws);
var_dump($loginTest->login($uc));
	
//$loginTest->logout($uc);


