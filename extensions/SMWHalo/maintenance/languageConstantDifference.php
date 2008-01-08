<?php
/*
 * Created on 20.12.2007
 *
 * Author: kai
 * 
 * Example of usage: 
 * 
 * 	>php languageConstantDifference En De
 * 
 * 	Returns all language constants which appear in 
 * 	SMW_HaloLanguageEn but not in SMW_HaloLanguageDe 
 */
 
if ($_SERVER['SERVER_NAME'] != NULL) {
	echo "Invalid access! A maintenance script MUST NOT accessed from remote.";
	return;
}

$smwgHaloIP = dirname(__FILE__)."/..";

require_once($smwgHaloIP.'/languages/SMW_HaloLanguage'.$argv[1].".php");
require_once($smwgHaloIP.'/languages/SMW_HaloLanguage'.$argv[2].".php");

$lang1 = "SMW_HaloLanguage".$argv[1];
$lang2 = "SMW_HaloLanguage".$argv[2];

$lang1Obj = new $lang1();
$lang2Obj = new $lang2();

print "SMW_HaloLanguage".$argv[1]." contains the following constants " .
		"which "."SMW_HaloLanguage".$argv[2]." does not contain:\n";

print("Difference in content messages:\n\n");
foreach($lang1Obj->getContentMsgArray() as $key => $value) {
	if (!array_key_exists($key, $lang2Obj->getContentMsgArray())) {
		print("\t'".$key."' => '".$value."',\n\n");
	}
}

print("Difference in user messages:\n\n");
foreach($lang1Obj->getUserMsgArray() as $key => $value) {
	if (!array_key_exists($key, $lang2Obj->getUserMsgArray())) {
		print("\t'".$key."' => '".$value."',\n\n");
	}
}
 
 
?>
