<?php

//global $smwgDIIP;

//require_once("$smwgDIIP/libs/arc/ARC2,php");

require_once("ARC2.php");

$uri = "http://dbpedia.org/resource/";
$item = "Toyota";

$parser = ARC2::getRDFParser();
$parser->parse($uri.$item);

$property = "http://dbpedia.org/property/keyPeople";

$result = "";
$index = $parser->getSimpleIndex(0);


$result = $index[$uri.$item][$property];

//foreach($index as $subject => $preds){
//	$result .= "\n".$subject;
//	foreach($preds as $pred => $objects){
//		$result .= "\n\t".$pred;
//		foreach($objects as $object){
//			$result .= "\n\t".$object;
//		}
//	}
//	break;
//}

foreach($result as $r){
	$add = true;
	if(array_key_exists("lang", $r)){
		if($r["lang"] != "en") {
			$add = false;
		}
	}
	if($add){
		$res[] = $r["value"];
	}
}

print_r($res);

