<?php

/**
 * @file
  * @ingroup DAPCPTest
  * 
  * @author Dian
 */

require_once('../PCP.php');

$editTest = new PCP_Server();
$uc = new PCP_UserCredentials("tester1", "!>ontoprise?");
//if ($editTest->login($uc)){
	#var_dump($editTest->getCookies());
	#$editTest->getEditToken("Main Page");
	#$editTest->getEditToken("Main Page");
	#global $wgUser;
	
	#var_dump($wgUser);
	#
	#$uc = new PCP_UserCredentials("WikiSysop", "!>ontoprise?");
	#$editTest->login($uc);
	#$__cookies = $editTest->getCookies();
	#print ("\nCookies set to...\n");
	#var_dump($__cookies);
	#$__uc = new PCP_UserCredentials($__cookies['UserName'],NULL, $__cookies['UserID']);
	$__uc = new PCP_UserCredentials("Tester1",NULL, 2);
	#print ("\nSending data...\n");
	#var_dump($__uc);
	#$editTest->setCookies($__cookies['Token'], $__uc);
	$editTest->setCookies("288f64ff3537d7d659766f3e45dcae68", $__uc);
	#var_dump($__cookies);
	#print ("Logged in as WikiSysop");
	#var_dump($wgUser);
	$editTest->getEditToken("Main Page");
	#	
	#$editTest->logout();
//}else{
//	print ("ERROR: Testing failed!".__FILE__);
//}

