<?php

/**
 * @file
  * @ingroup DAPCPExample
  *
  * @author Dian
 */

require_once('.../extensions/DataAPI/PageCRUD_Plus/PCP.php');

// create the target system object
$smwSystem = new PCPWikiSystem("http://localhost/smw");

// create the user credentials object  
$uc = new PCPUserCredentials(
		"user", # the username
		"pass" # the password
		);

// create the PCP client object 
$updateTest = new PCPClient($uc, $smwSystem);

if ($updateTest->login()){	
	echo $updateTest->updatePage($uc, "Testpage", "Updating some content");
	$updateTest->logout();
}else{
	print ("ERROR: Testing failed!");
}

