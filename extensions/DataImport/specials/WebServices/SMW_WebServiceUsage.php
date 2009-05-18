<?php
/*  Copyright 2008, ontoprise GmbH
 *  This file is part of the Data Import-Extension.
 *
 *   The Data Import-Extension is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The Data Import-Extension is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * This file is responsible for detecting and processing
 * the usage of web services in an article and in semantic properties.
 *
 * @author Ingo Steinbauer
 *
 */
if ( !defined( 'MEDIAWIKI' ) ) die;

// necessary for querying used properties
global $smwgIP;
require_once($smwgIP. "/includes/SMW_Factbox.php");

global $smwgDIIP;
// needed for db access
require_once("$smwgDIIP/specials/WebServices/SMW_WSStorage.php");
require_once("$smwgDIIP/specials/WebServices/SMW_WebServiceCache.php");
require_once("$smwgDIIP/specials/WebServices/SMW_WebService.php");


global $wgExtensionFunctions, $wgHooks;
// Define a setup function for the {{ ws:}} Syntax Parser
$wgExtensionFunctions[] ='webServiceUsage_Setup';

//Add a hook to initialise the magic word for the {{ ws:}} Syntax Parser
$wgHooks['LanguageGetMagic'][] = 'webServiceUsage_Magic';

// used to delete unused parameter sets that are no longer referred
// and web services that are no longer used in this article.
$wgHooks['ArticleSaveComplete'][] = 'detectEditedWSUsages';
$wgHooks['ArticleDelete'][] = 'detectDeletedWSUsages';

// to handle action=purge
$wgHooks['OutputPageBeforeHTML'][] = 'handlePurge';


global $wgAutoloadClasses;
// needed for formatting the ws-usage result
$wgAutoloadClasses['WebServiceListResultPrinter'] = $smwgDIIP.'/specials/WebServices/resultprinters/SMW_WebServiceRPList.php';
$wgAutoloadClasses['WebServiceUlResultPrinter'] = $smwgDIIP . '/specials/WebServices/resultprinters/SMW_WebServiceRPUl.php';
$wgAutoloadClasses['WebServiceOlResultPrinter'] = $smwgDIIP . '/specials/WebServices/resultprinters/SMW_WebServiceRPOl.php';
$wgAutoloadClasses['WebServiceTableResultPrinter'] = $smwgDIIP . '/specials/WebServices/resultprinters/SMW_WebServiceRPTable.php';
$wgAutoloadClasses['WebServiceTemplateResultPrinter'] = $smwgDIIP . '/specials/WebServices/resultprinters/SMW_WebServiceRPTemplate.php';
$wgAutoloadClasses['WebServiceTransposedResultPrinter'] = $smwgDIIP . '/specials/WebServices/resultprinters/SMW_WebServiceRPTransposed.php';
$wgAutoloadClasses['WebServiceTIXMLResultPrinter'] = $smwgDIIP . '/specials/WebServices/resultprinters/SMW_WebServiceRPTIXML.php';


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
 * Simply calls webServiceUsage_processCall
 *
 * @param $parser
 * @return string
 * 		the rendered wikitext
 */
function webServiceUsage_Render( &$parser) {
	$parameters = func_get_args();

	return webServiceUsage_processCall($parser, $parameters);
}

/**
 * Simply calls webServiceUsage_processCall
 *
 * @param $parameters :
 *
 * @return string
 * 		the rendered wikitext
 */
function webservice_getPreview($articleName, $parameters){
	return webServiceUsage_processCall($articleName, $parameters, true);
}

/**
 * Parses the {{ ws: }} syntax and returns the resulting wikitext
 *
 * @param $parser : a parser object if called by mediawiki or
 * 					a string if called by the preview function
 * @param $preview : boolean
 * @return string
 * 		the rendered wikitext
 */
function webServiceUsage_processCall(&$parser, $parameters, $preview=false) {
	global $wgsmwRememberedWSUsages, $purgePage;
	$purgePage = true;

	// the name of the ws must be the first parameter of the parser function
	$wsName = trim($parameters[1]);

	$ws = WebService::newFromName($wsName);
	if(!$ws){
		return smwfEncodeMessages(array(wfMsg('smw_wsuse_wwsd_not_existing', $wsName)));
	}
	$wsId = $ws->getArticleID();

	$wsParameters = array();
	$wsReturnValues = array();
	$wsFormat = "";
	$wsTemplate = "";
	$wsStripTags = "false";

	// determine the kind of the remaining parameters and get
	// their default value if one is specified
	for($i=2; $i < sizeof($parameters); $i++){
		$parameter = trim($parameters[$i]);
		if($parameter{0} == "?"){
			$wsReturnValues[getSpecifiedParameterName(substr($parameter, 1, strlen($parameter)))] = getSpecifiedParameterValue($parameter);
		} else if (substr($parameter,0, 7) == "_format"){
			$wsFormat = getSpecifiedParameterValue($parameter);
		} else if (substr($parameter,0, 9) == "_template"){
			$wsTemplate = getSpecifiedParameterValue($parameter);
		} else if (substr($parameter,0, 10) == "_striptags"){
			$wsStripTags = str_replace(",", "", getSpecifiedParameterValue($parameter));
		} else {
			$specParam = getSpecifiedParameterValue($parameter);
			if($specParam){
				$wsParameters[getSpecifiedParameterName($parameter)] = $specParam;
			}
		}
	}
	
	$response = validateWSUsage($wsId, $wsReturnValues, $wsParameters);
	$messages = $response[0];
	$wsParameters = $response[1];
	
	if(sizeof($messages) == 0){
		$parameterSetId = WSStorage::getDatabase()->storeParameterset($wsParameters);

		$removeParameterSetForPreview = true;
		if(strpos($parameterSetId, "#") === 0){
			$parameterSetId = substr($parameterSetId, 1);
			$removeParameterSetForPreview = false;
		}

		$wsResults = getWSResultsFromCache($ws, $wsReturnValues, $parameterSetId);

		$subst = false;
		if(!$preview){
			if($parser->OutputType() == 2){
				$subst = true;
			} else {
				$subst = false;
			}
		}

		$errorMessages = $ws->getErrorMessages();
		if(count($errorMessages) > 0){
			//todo:provide a better implementation
			if(strpos($errorMessages[0],
			substr(wfMsg('smw_wws_client_connect_failure'),0,10)) === 0){
				if(!is_array($wsResults)){
					$wsFormattedResult = $wsResults." ".smwfEncodeMessages($errorMessages);
				} else {
					$wsFormattedResult = formatWSResult($wsFormat, $wsTemplate, $wsStripTags, $wsResults, $subst);
					$wsFormattedResult .= " ".smwfEncodeMessages($errorMessages);
				}
			} else {
				$wsFormattedResult = smwfEncodeMessages($errorMessages);
			}
		} else {
			$wsFormattedResult = formatWSResult($wsFormat, $wsTemplate, $wsStripTags, $wsResults, $subst);
		}

		if(!$preview){
			$articleId = $parser->getTitle()->getArticleID();
		} else {
			$articleId = 0;
			if(strlen($parser) > 0){
				$t = Title::makeTitleSafe(0, $parser);
				$articleId = $t->getArticleID();
			}
		}

		//handle cache issues for previews
		if(!$preview){
			$wgsmwRememberedWSUsages[] = array($wsId, $parameterSetId, "", array_pop(array_keys($wsReturnValues)));
		} else {
			WebServiceCache::removeWSParameterPair($wsId, $parameterSetId);
			if($removeParameterSetForPreview){
				WSStorage::getDatabase()->removeParameterSet($parameterSetId);
			}
		}

		if($preview){
			global $wgParser;
			$t = Title::makeTitleSafe(0, $parser);
			$parser = $wgParser;
			$popts = new ParserOptions();
			$parser->startExternalParse($t, $popts, Parser::OT_HTML);

			$wsFormattedResult = $parser->internalParse($wsFormattedResult);
			$wsFormattedResult = $parser->doBlockLevels($wsFormattedResult, true);
			return $wsFormattedResult;
		}
		$wsFormattedResult = $parser->replaceVariables($wsFormattedResult);
		$wsFormattedResult = $parser->doBlockLevels($wsFormattedResult, true);
		//remove <p>-tag around ws-result
		if(substr($wsFormattedResult, 0, 3) == "<p>"){
			$wsFormattedResult = trim(substr($wsFormattedResult, 3, strlen($wsFormattedResult) - 7));
			
		}
		return $wsFormattedResult;
	} else {
		return smwfEncodeMessages($messages);
	}
}

/**
 * determines if a value is specified by the parameter
 * by an equality sign
 *
 * @param string $parameter
 * @return string
 * 		the specified parameter or Null if none was specified
 *
 */
function getSpecifiedParameterValue($parameter){
	$pos = strpos($parameter, "=");
	if($pos > 0){
		return trim(substr($parameter, $pos+1));
	} else {
		return null;
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
		return $parameter;
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
function formatWSResult($wsFormat, $wsTemplate, $wsStripTags, $wsResults = null, $subst = false){
	if(is_string($wsResults)){
		return smwfEncodeMessages(array($wsResults));
	}

	//handle erroneous wwsds
	foreach($wsResults as $key => $wsResult){
		if(is_string($wsResult)){
		} else if(is_array($wsResult)){
			foreach($wsResult as $subKey => $subWsResult){
				if(is_string($subWsResult) || is_numeric($subWsResult)){
				} else if($subWsResult != ""){
					$wsResults[$key][$subKey] = smwfEncodeMessages(array(wfMsg('smw_wsuse_type_mismatch'))).print_r($subWsResult, true).$subKey;
				}
			}
		} else {
			$wsResult[$key] = smwfEncodeMessages(array(wfMsg('smw_wsuse_type_mismatch'))).print_r($wsResult, true).$key;
		}
	}

	if($wsFormat == null){
		$printer = WebServiceListResultPrinter::getInstance();
		return $printer->getWikiText($wsTemplate, getReadyToPrintResult($wsResults, $wsStripTags), $subst);
	} else if($wsFormat == "list"){
		$printer = WebServiceListResultPrinter::getInstance();
		return $printer->getWikiText($wsTemplate, getReadyToPrintResult($wsResults, $wsStripTags), $subst);
	} else if($wsFormat == "ol"){
		$printer = WebServiceOlResultPrinter::getInstance();
		return $printer->getWikiText($wsTemplate, getReadyToPrintResult($wsResults, $wsStripTags), $subst);
	} else if($wsFormat == "ul"){
		$printer = WebServiceUlResultPrinter::getInstance();
		return $printer->getWikiText($wsTemplate, getReadyToPrintResult($wsResults, $wsStripTags), $subst);
	} else if($wsFormat == "table"){
		$printer = WebServiceTableResultPrinter::getInstance();
		return $printer->getWikiText($wsTemplate, getReadyToPrintResult($wsResults, $wsStripTags), $subst);
	} else if($wsFormat == "template"){
		$printer = WebServiceTemplateResultPrinter::getInstance();
		return $printer->getWikiText($wsTemplate, getReadyToPrintResult($wsResults, $wsStripTags), $subst);
	} else if($wsFormat == "transposed"){
		$printer = WebServiceTransposedResultPrinter::getInstance();
		return $printer->getWikiText($wsTemplate, getReadyToPrintResult($wsResults, $wsStripTags), $subst);
	} else if($wsFormat == "tixml"){
		$printer = WebServiceTIXMLResultPrinter::getInstance();
		return $printer->getWikiText($wsTemplate, getReadyToPrintResult($wsResults, $wsStripTags), $subst);
	}
}

/*
 * validates ws-usage
 */
function validateWSUsage($wsId, $wsReturnValues, $wsParameters){
	$ws = WebService::newFromId($wsId);

	//validate subparameters and construct appropriate parameters
	$subParameters = array();
	foreach($wsParameters as $name => $value){
		$name = explode(".", $name);
		if(count($name) > 1){
			unset($wsParameters[$name[0].".".$name[1]]);
			if(array_key_exists($name[0], $wsParameters)){
				unset($wsParameters[$name[0]]);
			}
			$subParameters[$name[0]][$name[1]] = $value;
		}
	}
	
	$result = $ws->validateSpecifiedSubParameters($subParameters);
	$mSP = $result[0];
	if(!is_null($result[1])){
		foreach($result[1] as $key => $value){
			if(strlen($value) > 0){
				$wsParameters[$key] = $value;
			}
		}
	}
	
	$temp = print_r($wsParameters, true);
	//$temp();

	if(count($mSP) == 0){
		$mSP = array();
	}

	$mP = $ws->validateSpecifiedParameters($wsParameters);
	$mR = $ws->validateSpecifiedResults($wsReturnValues);

	return array(array_merge($mSP, $mP, $mR), $wsParameters);
}

/*
 * calls detectRemovedWebServiceUsages
 *
 */
function detectDeletedWSUsages(&$article, &$user, $reason){
	$articleId  = $article->getID();
	detectRemovedWebServiceUsages($articleId);
	return true;
}
/*
 * calls detectRemovedWebServiceUsages()
 *
 */
function detectEditedWSUsages(&$article, &$user, &$text){
	$articleId  = $article->getID();
	if($articleId != null){
		detectRemovedWebServiceUsages($articleId);
	}
	return true;
}

/*
 * save properties in the factbox if action=purge
 */
function handlePurge(&$out, &$text){
	global $purgePage;

	$purgePage = false;
	return true;
}

/**
 * this function detects parameter sets that are no longer referred and
 * web services that are no longer used in this article
 *
 * @param string $articleId
 *
 * @return boolean true
 */
function detectRemovedWebServiceUsages($articleId){
	global $wgsmwRememberedWSUsages, $purgePage;
	$purgePage = false;
	$rememberedWSUsages = $wgsmwRememberedWSUsages;

	if($rememberedWSUsages != null){
		foreach($rememberedWSUsages as $rememberedWSUsage){
			WSStorage::getDatabase()->addWSArticle(
			$rememberedWSUsage[0], $rememberedWSUsage[1], $articleId);
		}
	}


	$oldWSUsages = WSStorage::getDatabase()->getWSsUsedInArticle($articleId);

	foreach($oldWSUsages as $oldWSUsage){
		$remove = true;
		if($rememberedWSUsages != null){
			foreach($rememberedWSUsages as $rememberedWSUsage){
				if(($rememberedWSUsage[0] == $oldWSUsage[0])
				&& ($rememberedWSUsage[1] == $oldWSUsage[1])){
					$remove = false;
				}
			}
		}
		if($remove){
			WSStorage::getDatabase()->removeWSArticle($oldWSUsage[0], $oldWSUsage[1], $articleId);
			WebServiceCache::removeWSParameterPair($oldWSUsage[0], $oldWSUsage[1]);
			$parameterSetIds = WSStorage::getDatabase()->getUsedParameterSetIds($oldWSUsage[1]);
			if(sizeof($parameterSetIds) == 0){
				WSStorage::getDatabase()->removeParameterSet($oldWSUsage[1]);
			}
		}
	}

	return true;
}


/**
 * get the result from the cache
 *
 * @param unknown_string $wsId
 * @param array $wsReturnValues the requested result parts and default values
 * @param array $parameterSetId the specified parameters
 * @return array
 */
function getWSResultsFromCache($ws, $wsReturnValues, $parameterSetId){
	$result = $ws->call($parameterSetId, $wsReturnValues);

	return $result;
}


/**
 * prepares the result for the result printers
 *
 * @param array $result
 * @return array
 */
function getReadyToPrintResult($result, $wsStripTags){
	$niceResult = array();
	$size = 0;
	foreach($result as $title => $values){
		if($size < sizeof($values)){
			$size = sizeof($values);
		}
	}

	foreach($result as $title => $values){
		while(sizeof($values) < $size){
			$values[] = "";
		}
	}

	for($i=0; $i<($size+1); $i++){
		$niceResult[$i] = array();
		foreach($result as $title => $values){
			if($i == 0){
				$niceResult[$i][] = $title;
			} else {
				$keys = array_keys($values);
				if($wsStripTags === "false"){
					$niceResult[$i][] = @ trim($values[$keys[$i-1]]);
				} else {
					$niceResult[$i][] = @ trim(strip_tags($values[$keys[$i-1]], $wsStripTags));
				}

			}
		}
	}
	return $niceResult;
}







?>