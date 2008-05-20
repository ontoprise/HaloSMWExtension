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
$wgHooks['LanguageGetMagic'][]       = 'webServiceUsage_Magic';



global $smwgHaloIP;
$wgAutoloadClasses['WebServiceListResultPrinter'] = $smwgHaloIP.'/specials/SMWWebService/SMW_WebServiceRPList.php';
$wgAutoloadClasses['WebServiceUlResultPrinter'] = $smwgHaloIP . '/specials/SMWWebService/SMW_WebServiceRPUl.php';
$wgAutoloadClasses['WebServiceOlResultPrinter'] = $smwgHaloIP . '/specials/SMWWebService/SMW_WebServiceRPOl.php';
$wgAutoloadClasses['WebServiceTableResultPrinter'] = $smwgHaloIP . '/specials/SMWWebService/SMW_WebServiceRPTable.php';

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
	// unless we return true, other parser functions extensions won't get loaded.
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
	$wsName = $parameters[1]." ";

	// determine the kind of the remaining parameters and get
	// their default value if one is specified
	$wsParameters = array();
	$wsReturnValues = array();
	$wsFormat = "";

	for($i=2; $i < sizeof($parameters); $i++){
		$parameter = trim($parameters[$i]);

		if($parameter{0} == "?"){
			$wsReturnValues[$parameter] = getSpecifiedParameterValue($parameter);
		} else if (substr($parameter,0, 7) == "_format"){
			$wsFormat = getSpecifiedParameterValue($parameter);
		} else {
			$wsParameters[$parameter] = getSpecifiedParameterValue($parameter);
		} 
	}

	$wsResults = getWSResultsFromCache($wsName, $wsReturnValues, $wsParameters);

	$wsFormattedResult = formatWSResult($wsFormat, $wsResults);

	return $wsFormattedResult;
}

/**
 * determines if a value is specified by the parameter
 * by an equality sign
 *
 * @param string $parameter
 * @return string
 * 		the specified parameter or Null if none was specified
 */
function getSpecifiedParameterValue($parameter){
	$pos = strpos($parameter, "=");

	if($pos > 0){
		return trim(substr($parameter, $pos+1));
	} else {
		return "default";
	}
}

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

?>