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
 * This file is responsible for detecting and processing
 * the usage of web services in an article and in semantic properties.
 *
 * @author Ingo Steinbauer
 *
 */

// necessary for querying used properties
global $smwgIP;
require_once($smwgIP. "/includes/SMW_Factbox.php");

global $smwgHaloIP;

// needed for db access
require_once("$smwgHaloIP/specials/SMWWebService/SMW_WSStorage.php");
require_once("$smwgHaloIP/specials/SMWWebService/SMW_WebServiceCache.php");
require_once("$smwgHaloIP/specials/SMWWebService/SMW_WebService.php");

// Define a setup function for the {{ ws:}} Syntax Parser
$wgExtensionFunctions[] ='webServiceUsage_Setup';
// Add a hook to initialise the magic word for the {{ ws:}} Syntax Parser
$wgHooks['LanguageGetMagic'][] = 'webServiceUsage_Magic';

// used to delete unused parameter sets that are no longer referred
// and web services that are no longer used in this article.
$wgHooks['ArticleSaveComplete'][] = 'detectEditedWSUsages';
$wgHooks['ArticleDelete'][] = 'detectDeletedWSUsages';

// necessary for finding possible ws-property-pairs before they are processed
// by the responsible parsers
$wgHooks['ParserAfterStrip'][] = 'findWSPropertyPairs';



// needed for formatting the ws-usage result
global $smwgHaloIP;
$wgAutoloadClasses['WebServiceListResultPrinter'] = $smwgHaloIP.'/specials/SMWWebService/resultprinters/SMW_WebServiceRPList.php';
$wgAutoloadClasses['WebServiceUlResultPrinter'] = $smwgHaloIP . '/specials/SMWWebService/resultprinters/SMW_WebServiceRPUl.php';
$wgAutoloadClasses['WebServiceOlResultPrinter'] = $smwgHaloIP . '/specials/SMWWebService/resultprinters/SMW_WebServiceRPOl.php';
$wgAutoloadClasses['WebServiceTableResultPrinter'] = $smwgHaloIP . '/specials/SMWWebService/resultprinters/SMW_WebServiceRPTable.php';


/*
 * find ws that are used in properties
 */
function findWSPropertyPairs(&$parser, &$text){
	$pattern = "/\[\[		# beginning of semantic attribute
				[^]]*		# no closing squared bracket but everything else
				::			# identifies a semantic property
				[^]]*		# no closing squared bracket but everything else
				\{\{
				[^\]]*		# no closing squared bracket but everything else
				\#ws:		# beginning of webservice usage declaration
				[^]]*		# n closing squared bracket but everything else
				[^[]*
				\|	
				/xu";

	$text = preg_replace_callback($pattern, "extractWSPropertyPairNames", &$text);
	return true;
}

/*
 * extract names from possible ws-property-pairs and
 * remember them for later validation
 */
function extractWSPropertyPairNames($hits){
	foreach ($hits as $hit){
		$hit = trim($hit);
		$sColPos = strPos($hit, "::");
		$propertyName = substr($hit, 2 ,$sColPos-2);
		$propertyName = trim($propertyName);

		$wsColPos = strPos($hit, "#ws:");
		$wsSPos = strPos($hit, "|", $wsColPos);
		$wsName = substr($hit, $wsColPos+4, $wsSPos-5-$wsColPos);
		$wsName = trim($wsName);

		$hit.="_property = ".$propertyName." | ";
		return $hit;
	}
}


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
	//todo; results muessen über result.part angesprochen werden
	global $wgsmwRememberedWSUsages;
	$parameters = func_get_args();

	// the name of the ws must be the first parameter of the parser function
	$wsName = trim($parameters[1]);

	$ws = WebService::newFromName($wsName);
	if(!$ws){
		return "Error: A WWSD for ".$wsName." does not exist";
	}
	$wsId = $ws->getArticleID();

	$wsParameters = array();
	$wsReturnValues = array();
	$wsFormat = "";

	// determine the kind of the remaining parameters and get
	// their default value if one is specified

	for($i=2; $i < sizeof($parameters); $i++){
		$parameter = trim($parameters[$i]);
		if($parameter{0} == "?"){
			$wsReturnValues[getSpecifiedParameterName(substr($parameters[$i], 1, strlen($parameters[$i])))] = getSpecifiedParameterValue($parameter);
		} else if (substr($parameter,0, 7) == "_format"){
			$wsFormat = getSpecifiedParameterValue($parameter);
		} else if (substr($parameter,0, 9) == "_property"){
			$propertyName = getSpecifiedParameterValue($parameter);
		} else {
			$specParam = getSpecifiedParameterValue($parameter);
			if($specParam){
				$wsParameters[getSpecifiedParameterName($parameter)] = $specParam;
			}
		}
	}

	//todo allow empty parameter sets:parametersetid=0;
	$messages = validateWSUsage($wsId, $wsReturnValues, $wsParameters);
	if(sizeof($messages) == 0){
		$parameterSetId = WSStorage::getDatabase()->storeParameterset($wsParameters);
		$wsResults = getWSResultsFromCache($wsId, $wsReturnValues, $parameterSetId);
		$wsFormattedResult = $wsResults;//formatWSResult($wsFormat, $wsResults);
		WSStorage::getDatabase()->addWSArticle($wsId, $parameterSetId, $parser->getTitle()->getArticleID());
		$wgsmwRememberedWSUsages[] = array($wsId, $parameterSetId, $propertyName);
		return $wsFormattedResult;
	} else {
		$return = "The usage of the #ws-syntax was errone: <ul>";
		foreach($messages as $mess){
			$return .= "<li>".$mess."</li>";
		}
		$return .="</ul>";
		return $return;
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
 * todo: return null
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
function formatWSResult($wsFormat, $wsResults){
	if($wsFormat == null){
		$printer = WebServiceListResultPrinter::getInstance();
		return $printer->getWikiText(getReadyToPrintResult($wsResults));
	} else if($wsFormat == "list"){
		$printer = WebServiceListResultPrinter::getInstance();
		return $printer->getWikiText(getReadyToPrintResult($wsResults));
	} else if($wsFormat == "ol"){
		$printer = WebServiceOlResultPrinter::getInstance();
		return $printer->getWikiText(getReadyToPrintResult($wsResults));
	} else if($wsFormat == "ul"){
		$printer = WebServiceUlResultPrinter::getInstance();
		return $printer->getWikiText(getReadyToPrintResult($wsResults));
	} else if($wsFormat == "table"){
		$printer = WebServiceTableResultPrinter::getInstance();
		return $printer->getWikiText(getReadyToPrintResult($wsResults));
	}
}

//todo
function validateWSUsage($wsId, $wsReturnValues, $wsParameters){
	$ws = WebService::newFromId($wsId);
	$mP = $ws->validateSpecifiedParameters($wsParameters);
	$mR = $ws->validateSpecifiedResults($wsReturnValues);
	return array_merge($mP, $mR);
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


/**
 * this function detects parameter sets that are no longer referred and
 * web services that are no longer used in this article or in a semantic property
 *
 * @param string $articleId
 *
 * @return boolean true
 */
function detectRemovedWebServiceUsages($articleId){
	$oldWSUsages = WSStorage::getDatabase()->getWSsUsedInArticle($articleId);
	global $wgsmwRememberedWSUsages;
	$rememberedWSUsages = $wgsmwRememberedWSUsages;

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

	if($rememberedWSUsages != null){
		$smwProperties = SMWFactbox::$semdata->getProperties();
		foreach($rememberedWSUsages as $rememberedWSUsage){
			if($smwProperties[$rememberedWSUsage[2]] != null){
				WSStorage::getDatabase()->addWSProperty($smwProperties[$rememberedWSUsage[2]]->getArticleId(), $rememberedWSUsage[0], $rememberedWSUsage[1], $articleId);
			}
		}
	}
	$wsProperties = WSStorage::getDatabase()->getWSPropertiesUsedInArticle($articleId);

	foreach($wsProperties as $wsProperty){
		$deleteProperty = true;
		if($rememberedWSUsages != null){
			foreach($rememberedWSUsages as $rememberedWSUsage){
				if($rememberedWSUsage[2] != null){
					if (($rememberedWSUsage[0] == $wsProperty[0])
					&& ($rememberedWSUsage[1] == $wsProperty[1])
					&& ($smwProperties[$rememberedWSUsage[2]]->getArticleId() == $wsProperty[2])){
						$deleteProperty = False;
					}}
			}
		}
		if($deleteProperty){
			WSStorage::getDatabase()->removeWSProperty($wsProperty[2], $wsProperty[0], $wsProperty[1], $articleId);
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
function getWSResultsFromCache($wsId, $wsReturnValues, $parameterSetId){
	//todo: handle default return values

	$returnValues = array();

	foreach($wsReturnValues as $key => $value){
		$returnValues[] = $key;
	}

	$ws = WebService::newFromID($wsId);
	$result = $ws->call($parameterSetId, $returnValues);

	return $result;
}


/**
 * prepares the result for the result printers
 *
 * @param array $result
 * @return array
 */
function getReadyToPrintResult($result){
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
				$niceResult[$i][] = $values[$i-1];
			}
		}
	}
	return $niceResult;
}





?>