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
 * @file
 * @ingroup DIWebServices
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
require_once("$smwgDIIP/specials/WebServices/SMW_WSTriplifier.php");
require_once("$smwgDIIP/specials/WebServices/smwstoragelayer/SMW_WSQueryResult.php");


global $wgHooks;
/// used to delete unused parameter sets that are no longer referred
// and web services that are no longer used in this article.
$wgHooks['ArticleSaveComplete'][] = 'wsuf_detectEditedWSUsages';
$wgHooks['ArticleDelete'][] = 'wsuf_detectDeletedWSUsages';

// to handle action=purge
$wgHooks['OutputPageBeforeHTML'][] = 'wsuf_handlePurge';

/**
 * Simply calls webServiceUsage_processCall
 *
 * @param $parser
 * @return string
 * 		the rendered wikitext
 */
function wsuf_Render( &$parser) {
	$parameters = func_get_args();

	return SMWWebServiceUsage::processCall($parser, $parameters);
}

/*
 * calls detectRemovedWebServiceUsages
 */
function wsuf_detectDeletedWSUsages(&$article, &$user, $reason){
	$articleId  = $article->getID();
	SMWWebServiceUsage::detectRemovedWebServiceUsages($articleId);
	return true;
}
/*
 * calls detectRemovedWebServiceUsages()
 *
 */
function wsuf_detectEditedWSUsages(&$article, &$user, $text){
	$articleId  = $article->getID();
	if($articleId != null){
		SMWWebServiceUsage::detectRemovedWebServiceUsages($articleId);
	}
	return true;
}

/*
 * save properties in the factbox if action=purge
 */
function wsuf_handlePurge(&$out, &$text){
	global $purgePage;

	$purgePage = false;
	return true;
}


class SMWWebServiceUsage {
	
	/**
	 * Simply calls webServiceUsage_processCall
	 *
	 * @param $parameters :
	 *
	 * @return string
	 * 		the rendered wikitext
	 */
	public static function getPreview($articleName, $parameters){
		return self::processCall($articleName, $parameters, true);
	}
	
/*
 * This method processes a translated SMW query as a ws call
 * and returns the result encoded as a SMWWSQueryResult object.
 */
public static function processSMWQueryASWSCall($parameters){
	$parser = null;	
	return self::processCall($parser, $parameters, false, true);
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
	public static function processCall(&$parser, $parameters, $preview=false, $smwQueryMode=false, $rawResults = false) {
		global $wgsmwRememberedWSUsages, $purgePage, $wgsmwRememberedWSTriplifications;
		$purgePage = true;
		
		//parse web service call parameters
		list($wsParameters, $wsReturnValues, $configArgs) = 
			self::parseWSCallParameters($parameters);
		$configArgs['webservice'] = trim($parameters[1]);
		$wsTriplify = (array_key_exists('triplify', $configArgs)) ? true : false;
		$displayTripleSubjects = (array_key_exists('displaytriplesubjects', $configArgs)) 
			? $configArgs['displaytriplesubjects'] : false;
	
		// the name of the ws must be the first parameter of the parser function
		$wsName = trim($parameters[1]);
		$ws = WebService::newFromName($wsName);
		if(!$ws){
			$errorMSG = wfMsg('smw_wsuse_wwsd_not_existing', $wsName);
			return self::formatWSResult($errorMSG, $configArgs, $wsParameters, $wsReturnValues, $smwQueryMode);
		}
		$wsId = $ws->getArticleID();
	
		//get article id
		if(!$preview && !$smwQueryMode){
			$articleId = $parser->getTitle()->getArticleID();
		} else {
			$articleId = 0;
			if(strlen($parser) > 0){
				$t = Title::makeTitleSafe(0, $parser);
				$articleId = $t->getArticleID();
			}
		}
		
		$allAliases = WebService::newFromID($wsId)->getAllResultPartAliases();
		
		//process triplification instructions
		if($wsTriplify || $displayTripleSubjects){
			if ( !defined( 'LOD_LINKEDDATA_VERSION') && $wsTriplify){
				//ld extension not installed
				$errorMSG = wfMsg('smw_wsuse_missing_ld_extension');
				return self::formatWSResult($errorMSG, $configArgs, $wsParameters, $wsReturnValues, $smwQueryMode);
			}
			
			//get subject creation pattern from wwsd if necessary
			$triplificationSubject = $ws->getTriplificationSubject();
			if(strlen($triplificationSubject) == 0){
				//no subject creation pattern defind
				$errorMSG = wfMsg('smw_wsuse_missing_triplification_subject');
				return self::formatWSResult($errorMSG, $configArgs, $wsParameters, $wsReturnValues, $smwQueryMode);
			}
			
			//add triplification subject aliases to result parts if necessary and
			//remember those special result parts
			
			$subjectCreationPatternParts = array();
			foreach($allAliases as $alias => $dc){
				if(strpos(strtolower($triplificationSubject), "?".$alias."?") !== false){
					$alias = explode('.', $alias);
					if(!array_key_exists($alias[0].".".$alias[1], $wsReturnValues)
							&& !array_key_exists($alias[0], $wsReturnValues)){
						$wsReturnValues[$alias[0].".".$alias[1]] = "";
						$subjectCreationPatternParts[] = $alias[1];
					}		
				}
			}
		} 
		
		//validate ws call parameters
		list($messages, $wsParameters) = 
			self::validateWSUsage($wsId, $wsReturnValues, $wsParameters);
		
		if(sizeof($messages) > 0){
			$errorMSG = implode(' ', $messages);
			return self::formatWSResult($errorMSG, $configArgs, $wsParameters, $wsReturnValues, $smwQueryMode);
		}
		
		$parameterSetId = WSStorage::getDatabase()->storeParameterset($wsParameters);

		//check if parameter set id must be removed
		//afterwards if preview or smwquerymode
		$removeParameterSetForRemove = true;
		if(strpos($parameterSetId, "#") === 0){
			$parameterSetId = substr($parameterSetId, 1);
			$removeParameterSetForRemove = false;
		}

		$wsResults = self::getWSResultsFromCache($ws, $wsReturnValues, $parameterSetId);
		
		if(is_string($wsResults)){
			
			//todo: remove parameter sets and so on
			$wsResults = self::formatWSResult($wsResults, $configArgs, $wsParameters, $wsReturnValues, $smwQueryMode);
		
		} else {
			
			if($rawResults){
				return $wsResults;
			}
			
			//process triplification instructions
			if(($wsTriplify || $displayTripleSubjects) && !is_string($wsResults)){
				$wsResultsForTriplification = $wsResults;
				foreach($subjectCreationPatternParts as $p){
					if(array_key_exists(strtolower($p), $wsResults)){
						unset($wsResults[strtolower($p)]);
						unset($wsReturnValues['result.'.$p]);
					}
				}
				//only triplify if this is not for the preview
				if ((!$preview && !$smwQueryMode) || $displayTripleSubjects){
					if(!is_array($wgsmwRememberedWSTriplifications)){
						$wgsmwRememberedWSTriplifications = array();
					}
					$dropGraph = false;
					if(!array_key_exists($wsId, $wgsmwRememberedWSTriplifications)){
						$wgsmwRememberedWSTriplifications[$wsId] = null;
						$dropGraph = true;
					}
					
					$tmp = $wsResultsForTriplification;
					$wsResultsForTriplification = array();
					foreach($allAliases as $alias => $dontCare){
						$alias = substr($alias, strpos($alias, '.') + 1);
						if(array_key_exists(strtolower($alias), $tmp)){
							$results = $tmp[strtolower(strtolower($alias))];
							$wsResultsForTriplification[$alias] = $results;
						} 
					}
					
					$subjects[$displayTripleSubjects] = 
						WSTriplifier::getInstance()
							->triplify($wsResultsForTriplification, $triplificationSubject, $wsId, $wsTriplify && !$preview && !$smwQueryMode, $articleId, $dropGraph, $subjectCreationPatternParts, $parser);
				}
			}
			
			if($displayTripleSubjects){
				$wsResults = array_merge($subjects, $wsResults);
				$wsReturnValues = array_merge(array( 'result.'.$displayTripleSubjects => ''), $wsReturnValues);
			} 
			
			foreach($allAliases as $alias => $dontCare){
				if(array_key_exists(strtolower($alias), $wsReturnValues) && strlen($wsReturnValues[strtolower($alias)]) == 0){
					$wsReturnValues[strtolower($alias)] = substr($alias, strpos($alias, '.') + 1);
				}
			}
			
			$wsResults = self::formatWSResult($wsResults, $configArgs, $wsParameters, $wsReturnValues, $smwQueryMode);
		}
		
		//handle cache issues for previews
		if(!$preview && !$smwQueryMode){
			$tmp = array_keys($wsReturnValues);
			$wgsmwRememberedWSUsages[] = array($wsId, $parameterSetId, "", array_pop($tmp));
		} else {
			WebServiceCache::removeWSParameterPair($wsId, $parameterSetId);
			if($removeParameterSetForRemove){
				WSStorage::getDatabase()->removeParameterSet($parameterSetId);
			}
		}
		
		if($preview){
			global $wgParser;
			$t = Title::makeTitleSafe(0, $parser);
			$parser = $wgParser;
			$popts = new ParserOptions();
			$parser->startExternalParse($t, $popts, Parser::OT_HTML);

			$wsResults = $parser->internalParse($wsResults);
			$wsResults = $parser->doBlockLevels($wsResults, true);
			return $wsResults;
		}
		
		if(!$smwQueryMode){
			//todo: Is this still necessary?
			$wsResults = $parser->replaceVariables($wsResults);
		}
		
		return $wsResults;
	}
	
	private static function parseWSCallParameters($parameters){
		$wsParameters = array();
		$wsReturnValues = array();
		$configArgs = array();
	
		// determine the kind of the remaining parameters and get
		// their default value if one is specified
		for($i=2; $i < count($parameters); $i++){
			$parameter = trim($parameters[$i]);
			if($parameter{0} == "?"){
				$wsReturnValues[strtolower(self::getSpecifiedParameterName(substr($parameter, 1, strlen($parameter))))] 
					= self::getSpecifiedParameterValue($parameter);
			} else if (substr($parameter,0, 22) == "_displayTripleSubjects"){
				$displayTripleSubjects = explode("=", $parameter, 2);
				if(array_key_exists(1, $displayTripleSubjects) && strlen($displayTripleSubjects[1]) > 0){
					$configArgs['displaytriplesubjects'] = $displayTripleSubjects[1];
				} else {
					$configArgs['displaytriplesubjects'] = "Triple subjects";
				}
			} else if (strpos($parameter, '_') === 0){
				$parameter = explode('=', $parameter, 2);
				$label = substr($parameter[0], 1);
				$value = (count($parameter) > 1) ? $parameter[1] : ''; 
				$configArgs[$label] = $value;
			}else {
				$specParam = self::getSpecifiedParameterValue($parameter);
				if($specParam){
					$wsParameters[strtolower(self::getSpecifiedParameterName($parameter))] = $specParam;
				}
			}
		}
		
		return array($wsParameters, $wsReturnValues, $configArgs);  	  
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
	public static function getSpecifiedParameterValue($parameter){
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
	public static function getSpecifiedParameterName($parameter){
		$pos = strpos($parameter, "=");
	
		if($pos > 0){
			return trim(substr($parameter, 0, $pos));
		} else {
			return $parameter;
		}
	}
	
	/*
	 * Format error messages as results
	 */
	private static function formatErrorMsgAsResult (
			$wsResults, $configArgs, $wsParameters, $wsReturnValues, $smwQueryMode){
	
		$format = 'table';
		
		$query = 	SMWQueryProcessor::createQuery( 
			'[[dummy]]', array(), SMWQueryProcessor::INLINE_QUERY, $format, array());
		
		$queryResult = 
			new SMWWSQueryResult(array(), $query, array(), new SMWWSSMWStore(), false);
	
		$queryResult->addErrors(array($wsResults));
		
		if($smwQueryMode){
			return $queryResult;
		}
		
		$printer = SMWQueryProcessor::getResultPrinter( $format);
		$result = $printer->getResult( $queryResult, array(), SMW_OUTPUT_WIKI);
	
		if(array_key_exists('format', $configArgs) && $configArgs['format'] == 'xount'){
			$result = '';
		}
		
		return $result;
	}
	
	
	/*
	 * sort wsresult according to sort and order parameter
	 */
	private static function sortWSResult($wsResults, $configArgs){
		
		if(array_key_exists('sort', $configArgs) && $configArgs['sort'] && array_key_exists(ucfirst($configArgs['sort']), $wsResults)){
			$sortArray = array();
			foreach($wsResults[ucfirst($configArgs['sort'])] as $key => $value){
				$sortArray[$value][] = $key;
			}
			
			ksort($sortArray);
			
			if(array_key_exists('order', $configArgs) && strtolower($configArgs['order']) == 'desc'){
				$descSortArray = array();
				$sortKeys = array_keys($sortArray);
				for($i = count($sortArray)-1; $i >= 0; $i--){
					$descSortArray[$sortKeys[$i]] = $sortArray[$sortKeys[$i]];
				}
				$sortArray = $descSortArray;
			}
			
			foreach($wsResults as $columnName => $columnValues){
				$sortedColumnValues = array();
				foreach($sortArray as $key => $sortKeys){
					foreach($sortKeys as $sortKey){
						$sortedColumnValues[] = $columnValues[$sortKey]; 
					}
				}
				$wsResults[$columnName] = $sortedColumnValues;
			}
		}
		
		return $wsResults;
	} 
	
	/*
	 * Deal with limit and offset parameter
	 */
	private static function formatWithLimitAndOffset($wsResults, $configArgs){
	
		$furtherResults = false;
		
		if(array_key_exists('offset', $configArgs) && is_int($configArgs['offset'] +1)){
			$offset = $configArgs['offset'];
			foreach($wsResults as $key => $values){
				array_splice($values, 0, $offset);
				$wsResults[$key] = $values;
			}
		}	
		
		global $smwgQMaxLimit, $smwgQMaxInlineLimit;
		if(array_key_exists('limit', $configArgs) && is_int($configArgs['limit'] + 1) && $configArgs['limit'] > 0){
			$limit = $configArgs['limit'];
			$limit = min($smwgQMaxLimit, $limit);
		} else {
			$limit = $smwgQMaxInlineLimit;
		}
			
		$furtherResults = false;
		foreach($wsResults as $key => $values){
			$furtherResults = (count($values) > $limit || $furtherResults) ? true : false;
			array_splice($values, $limit);
			$wsResults[$key] = $values;
		}
		
		return array($wsResults, $furtherResults);
	} 
	
	/*
	 * format ws results with smw result printers
	 */
	private static function formatWSResultWithSMWQPs(
			$wsResults, $configArgs, $wsParameters, $wsReturnValues, $smwQueryMode){
				
		//do sorting
		$wsResults = self::sortWSResult($wsResults, $configArgs);
		
		//deal with limit and offset
		list($wsResults, $furtherResults) = self::formatWithLimitAndOffset($wsResults, $configArgs);
		
		$format = (array_key_exists('format', $configArgs)) ? $configArgs['format'] : '';
		
		//todo: create print requests array for constructor below
		$printRequests = array();
		$queryResults = array();
		$typeIds = array();
		
		//get Type ids
		$numTypeFormats = array('sum' => true, 'min' => true, 'max' => true, 'average' => true);
		foreach($wsResults as $columnLabel => $values){
			if(array_key_exists(strtolower($format), $numTypeFormats)){
				$typeIds[$columnLabel] = '_num';	
			} else {
				$typeIds[$columnLabel] = '_txt';
			}
		}
		
		//create print requests
		foreach($wsReturnValues as $id => $label){
			$id = ucfirst(substr($id, strpos($id, '.')+1));
			if(!$label) $label = $id;
			$printRequests[$id] = 
				new SMWPrintRequest(SMWPrintRequest::PRINT_THIS, $label, $id);
		}
		
		//transpose ws result
		foreach($wsResults as $columnLabel => $values){
			foreach($values as $key => $value){
				$queryResultColumnValues = array();
				
				$resultInstance = SMWDataValueFactory::newTypeIDValue('_wpg');
				$title = Title::newFromText(wfMsg('smw_ob_invalidtitle'), '');
				$resultInstance->setValues($title->getDBkey(), $title->getNamespace(), $title->getArticleID(), false, '', $title->getFragment());
				
				$dataValue = SMWDataValueFactory::newTypeIDValue($typeIds[$columnLabel]);
				$dataValue->setUserValue($value);
				$queryResultColumnValues[] = $dataValue;
				
				//this is necessary, because one can edit with the properties
				//parameter of the LDConnector additional columns
				if(!array_key_exists(ucfirst($columnLabel), $printRequests)){
					$printRequests[ucfirst($columnLabel)] = 
						new SMWPrintRequest(SMWPrintRequest::PRINT_THIS, $columnLabel, ucfirst($columnLabel));
				}
				
				$queryResultColumnValues = 
					new SMWWSResultArray($resultInstance, $printRequests[ucfirst($columnLabel)], $queryResultColumnValues);
				
				@ $queryResults[$key][$columnLabel] = $queryResultColumnValues; 	
			}
		}
		
		//translate ws call to SMW ask query
		$queryParams = array();
		foreach($wsParameters as $param => $value){
			$queryParams['_'.$param] = $value;
		}
		foreach($configArgs as $param => $value){
			$queryParams[$param] = $value;
		}
		$queryParams['source'] = 'webservice';
		$queryParams['webservice'] = $configArgs['webservice'];
		
		//create query object
		$query = 	SMWQueryProcessor::createQuery( 
			'[[dummy]]', 
			$queryParams, 
			SMWQueryProcessor::INLINE_QUERY, 
			$format, 
			$printRequests);
			
		$query->params = $queryParams;			
		
		//create query result object
		$queryResult = 
			new SMWWSQueryResult($printRequests, $query, $queryResults, new SMWWSSMWStore(), $furtherResults);
		
		//deal with count mode
		if($format == 'count'){
			return count($queryResults);
		}	

		//return the query result object if this is called by special:ask
		if($smwQueryMode){
			return $queryResult;
		}
		
		
		$printer = SMWQueryProcessor::getResultPrinter( $format, SMWQueryProcessor::INLINE_QUERY);
		$result = $printer->getResult( $queryResult, $configArgs, SMW_OUTPUT_WIKI);
		
		return $result;
	}
	
	/**
	 * format the ws result in the given result format
	 */
	public static function formatWSResult($wsResults, $configArgs, $wsParameters, $wsReturnValues, $smwQueryMode = false){
		
		//deal with error messages
		if(is_string($wsResults)){
			return self::formatErrorMsgAsResult($wsResults, $configArgs, 
				$wsParameters, $wsReturnValues, $smwQueryMode);
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
				$wsResults[$key] = smwfEncodeMessages(array(wfMsg('smw_wsuse_type_mismatch'))).print_r($wsResult, true).$key;
			}
		}
		
		$stripTags = (array_key_exists('striptags', $configArgs)) ? $configArgs['striptags'] : false;
		$wsResults = self::getReadyToPrintResult($wsResults, $stripTags);
		
		return self::formatWSResultWithSMWQPs($wsResults, $configArgs, $wsParameters, $wsReturnValues, $smwQueryMode);
	}
	
	/*
	 * validates ws-usage
	 */
	public static function validateWSUsage($wsId, $wsReturnValues, $wsParameters){
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
		
		if(count($mSP) == 0){
			$mSP = array();
		}
		
		$mP = $ws->validateSpecifiedParameters($wsParameters);
		$mR = $ws->validateSpecifiedResults($wsReturnValues);
	
		
		return array(array_merge($mSP, $mP, $mR), $wsParameters);
	}
	
	/**
	 * this function detects parameter sets that are no longer referred and
	 * web services that are no longer used in this article
	 *
	 * @param string $articleId
	 *
	 * @return boolean true
	 */
	public static function detectRemovedWebServiceUsages($articleId){
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
		$wgsmwRememberedWSUsages = array();
		
		//deal with triplifying
		global $wgsmwRememberedWSTriplifications;
		if(!is_array($wgsmwRememberedWSTriplifications)){
			$wgsmwRememberedWSTriplifications = array();
		}
		
		foreach($oldWSUsages as $oldWSUsage){
			if(!array_key_exists($oldWSUsage[0], $wgsmwRememberedWSTriplifications)){
				WSTriplifier::getInstance()->removeWSUsage($oldWSUsage[0], $articleId);
			}
		}
		
		$wgsmwRememberedWSTriplifications = array();
		
		//eof deal with triplification
		
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
	public static function getWSResultsFromCache($ws, $wsReturnValues, $parameterSetId){
		$result = $ws->call($parameterSetId, $wsReturnValues);
	
		return $result;
	}
	
	
	/**
	 * deal with striptags and fill short columns with dummies
	 */
	public static function getReadyToPrintResult($result, $stripTags){
		//compute longest column
		$size = 0;
		foreach($result as $title => $values){
			if($size < sizeof($values)){
				$size = sizeof($values);
			}
		}
		
		//deal with striptags parameter and fill columns
		foreach($result as $title => $values){
			foreach($values as $key => $value){
				if($stripTags === false){
					$result[$title][$key] = @ str_replace("|", "{{!}}",trim($result[$title][$key]));
				} else {
					$result[$title][$key] = @ str_replace("|", "{{!}}",trim(strip_tags($result[$title][$key], $stripTags)));
				}
			}
			
			while(sizeof($result[$title]) < $size){
				$result[$title][] = "";
			}
		}
	
		return $result;
	}
	
}







