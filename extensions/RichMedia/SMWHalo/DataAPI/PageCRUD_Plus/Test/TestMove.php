<?php

/**
 * @file
  * @ingroup DAPCPTest
  * 
  * @author Dian
 */

require_once('../PCP.php');

$editTest = new PCP_Server();
$uc = new PCP_UserCredentials("WikiSysop", "!>ontoprise?");
if ($editTest->login($uc)){
	#var_dump($editTest->getCookies());
	print("\nMoving ...");
	$editTest->movePage("Anotehr test has moved again", "Anotehr test moves on", true, false);
	$editTest->logout();
}else{
	print ("ERROR: Testing failed!".__FILE__);
}

