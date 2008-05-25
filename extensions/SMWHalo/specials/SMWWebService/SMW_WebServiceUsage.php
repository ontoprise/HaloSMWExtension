<?php
/*  Copyright 2008, ontoprise GmbH
 *  This file is part of the halo-Extension.
 *
 *   The halo-Extension is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The halo-Extension is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * This file provides a parser for the {{ws: }} syntax used by the
 * web service extension
 *
 * @author Ingo Steinbauer
 *
 */

// Define a setup function for the {{ ws:}} Syntax Parser
$wgExtensionFunctions[] ='webServiceUsage_Setup';
// Add a hook to initialise the magic word for the {{ ws:}} Syntax Parser
$wgHooks['LanguageGetMagic'][] = 'webServiceUsage_Magic';

// used to delete unused parameter sets that are no longer referred
// and web services that are no longer used on the edited article
$wgHooks['ParserAfterTidy'][] = 'detectRemovedWebServiceUsages';

// needed for formatting the ws-usage result
global $smwgHaloIP;
$wgAutoloadClasses['WebServiceListResultPrinter'] = $smwgHaloIP.'/specials/SMWWebService/SMW_WebServiceRPList.php';
$wgAutoloadClasses['WebServiceUlResultPrinter'] = $smwgHaloIP . '/specials/SMWWebService/SMW_WebServiceRPUl.php';
$wgAutoloadClasses['WebServiceOlResultPrinter'] = $smwgHaloIP . '/specials/SMWWebService/SMW_WebServiceRPOl.php';
$wgAutoloadClasses['WebServiceTableResultPrinter'] = $smwgHaloIP . '/specials/SMWWebService/SMW_WebServiceRPTable.php';

// needed for db access
require_once("SMW_WSStorage.php");
/**
 * Set a function hook associating the "webServiceUsage" magic word with our function
 */
function webServiceUsage_Setup() {
	global $wgParser;
	$wgParser->setFunctionHook( 'webServiceUsage', 'webServiceUsage_Render' );
}

/**
 * maps the magic word "webServiceUsage"to occurences of "ws:" in the wiki text
 */
function webServiceUsage_Magic( &$magicWords, $langCode ) {
	$magicWords['webServiceUsage'] = array( 0, 'ws' );
	return true;
}

/**
 * Parses the {{ ws: }} syntax and returns the resulting wikitext
 *
 * @param $parser
 * @return string
 * 		the rendered wikitext
 */
function webServiceUsage_Render( &$parser) {
	$parameters = func_get_args();

	// the name of the ws must be the first parameter of the parser function
	$wsName = trim($parameters[1]);


	$wsParameters = array();
	$wsReturnValues = array();
	$wsFormat = "";
	$wsFormattedResult = "syntax error";

	// determine the kind of the remaining parameters and get
	// their default value if one is specified
	//todo: handle defaults
	for($i=2; $i < sizeof($parameters); $i++){
		$parameter = trim($parameters[$i]);
		if($parameter{0} == "?"){
			$wsReturnValues[getSpecifiedParameterName(substr($parameters[$i], 1, strlen($parameters[$i])))] = getSpecifiedParameterValue($parameter);
		} else if (substr($parameter,0, 7) == "_format"){
			$wsFormat = getSpecifiedParameterValue($parameter);
		} else {
			$wsParameters[getSpecifiedParameterName($parameter)] = getSpecifiedParameterValue($parameter);
		}
	}

	if(validateWSUsage($wsName, $wsReturnValues, $wsParameters)){

		$parameterSetId = WSStorage::getDatabase()->storeParameterset($wsParameters);
		$wsResults = getWSResultsFromCache($wsName, $wsReturnValues, $wsParameters);
		$wsFormattedResult = formatWSResult($wsFormat, $wsResults)." parametersetid: ".$parameterSetId;
		rememberWSUsage($wsName, $parameterSetId);
		WSStorage::getDatabase()->addWSArticle($wsName, $parameterSetId, $parser->getTitle()->getArticleID())." zo ";
	}

	return $wsFormattedResult;
}

/**
 * determines if a value is specified by the parameter
 * by an equality sign
 *
 * @param string $parameter
 * @return string
 * 		the specified parameter or Null if none was specified
 *
 * todo: return null
 */
function getSpecifiedParameterValue($parameter){
	$pos = strpos($parameter, "=");

	if($pos > 0){
		return trim(substr($parameter, $pos+1));
	} else {
		return "default";
	}
}

/**
 * retrieve the name of a paramter
 *
 * @param unknown_string $parameter
 * @return string
 * 		the parameter name
 */
function getSpecifiedParameterName($parameter){
	$pos = strpos($parameter, "=");

	if($pos > 0){
		return trim(substr($parameter, 0, $pos));
	} else {
		return $parameterName;
	}
}

/**
 * format the ws result in the given result format
 *
 * @param string $wsFormat
 * @param string_type $wsResults
 * @return string
 * 		the formatted result
 */
function formatWSResult($wsFormat, $wsResults){
	if($wsFormat == null){
		$printer = WebServiceListResultPrinter::getInstance();
		return $printer->getWikiText($wsResults);
	} else if($wsFormat == "list"){
		$printer = WebServiceListResultPrinter::getInstance();
		return $printer->getWikiText($wsResults);
	} else if($wsFormat == "ol"){
		$printer = WebServiceOlResultPrinter::getInstance();
		return $printer->getWikiText($wsResults);
	} else if($wsFormat == "ul"){
		$printer = WebServiceUlResultPrinter::getInstance();
		return $printer->getWikiText($wsResults);
	} else if($wsFormat == "table"){
		$printer = WebServiceTableResultPrinter::getInstance();
		return $printer->getWikiText($wsResults);
	}
}

//todo
function validateWSUsage($wsName, $wsReturnValues, $wsParameters){
	//todo
	return true;
}

/**
 * this function detects parameter sets that are no longer referred and
 * web services that are no longer used in this article
 *
 * @param Parser $parser
 * @param string $text
 * @return boolean true
 */
function detectRemovedWebServiceUsages(&$parser, &$text){
	$oldWSUsages = WSStorage::getDatabase()->getWSsUsedInArticle($parser->getTitle()->getArticleID());
	$rememberedWSUsages = getRememberedWSUsages();

	foreach($oldWSUsages as $oldWSUsage){
		$remove = true;
		foreach($rememberedWSUsages as $rememberedWSUsage){
			if(($rememberedWSUsage[0] == $oldWSUsage[0])
			&& ($rememberedWSUsage[1] == $oldWSUsage[1])){
				$remove = false;
			}
		}

		if($remove){
			WSStorage::getDatabase()->removeWSArticle($oldWSUsage[0], $oldWSUsage[1], $parser->getTitle()->getArticleId());
			$parameterSetIds = WSStorage::getDatabase()->getUsedParameterSetIds($oldWSUsage[1]);
			if(sizeof($parameterSetIds) == 0){
				WSStorage::getDatabase()->removeParameterSet($oldWSUsage[1]);
			}
		}
	}

	initWSUsageMemory();
	return true;
}


//todo
function getWSResultsFromCache($wsName, $wsReturnValues, $wsParameters){
	$testArray = array();
	for($k=0; $k < 7; $k++){
		$testArraySub = array();

		for($i=0; $i < 6; $i++){
			array_push(&$testArraySub, $i);
		}
		array_push(&$testArray, $testArraySub);
	}
	return $testArray;
}

/**
 * remember ws-usage
 *
 * @param string $wsName
 */
function rememberWSUsage($wsName, $parameterSetId){
	WSUsageMemory::rememberWSUsage($wsName, $parameterSetId);
}

/*
 * get remembered ws-usages
 */
function getRememberedWSUsages(){
	return WSUsageMemory::getWSUsages();
}
/*
 * initialize the ws-usage memory
 */
function initWSUsageMemory(){
	WSUsageMemory::refresh();
}

/*
 * helper class for remembering ws-usage
 * todo: replace this with a better solution
 */
class WSUsageMemory{

	static private $wsUsages = array();

	static public function rememberWSUsage($wsName, $parameterSetId){
		array_push(WSUsageMemory::$wsUsages, array($wsName, $parameterSetId));
	}

	static public function getWSUsages(){
		return WSUsageMemory::$wsUsages;
	}

	static public function refresh(){
		WSUsageMemory::$wsUsages = array();
	}

}
?>