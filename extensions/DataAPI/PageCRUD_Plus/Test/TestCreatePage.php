<?php
chdir('C:\xampp\htdocs\mw');
require_once('C:/xampp/htdocs/mw/includes/Webstart.php');

$editTest = new PCPServer();
$uc = new PCPUserCredentials("WikiSysop", "!>ontoprise?");
if ($editTest->login($uc)){
	#var_dump($editTest->getCookies());
	#$editTest->getEditToken("Main Page");
	echo $editTest->createPage($uc, "Another test", "Adding some content");
	$editTest->logout();
}else{
	print ("ERROR: Testing failed!".__FILE__);
}

?>