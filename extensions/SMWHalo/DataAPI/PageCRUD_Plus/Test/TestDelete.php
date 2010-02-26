<?php

/**
 * @file
  * @ingroup DAPCPTest
  * 
  * @author Dian
 */

require_once('../PCP.php');

$deleteTest = new PCP_Server();
$uc = new PCP_UserCredentials("WikiSysop", "!>ontoprise?");
if ($deleteTest->login($uc)){
	#var_dump($editTest->getCookies());
	$deleteTest->deletePage("Another test 2");
	$deleteTest->logout();
}else{
	print ("ERROR: Testing failed!".__FILE__);
}

