<?php
chdir('C:\xampp\htdocs\mw');
require_once('C:/xampp/htdocs/mw/includes/Webstart.php');

$editTest = new PCPServer();
$uc = new PCPUserCredentials("WikiSysop", "!>ontoprise?");
//if ($editTest->login($uc)){
	#var_dump($editTest->getCookies());
	#$editTest->getEditToken("Main Page");
	$editTest->updatePage($uc, "Testpage", "Now it should work.", "Page updated.");
	$editTest->logout();
//}else{
//	print ("ERROR: Testing failed!".__FILE__);
//}

?>