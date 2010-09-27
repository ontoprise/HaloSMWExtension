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
  * @ingroup DIWSMaterialization
  * This file is responsible for detecting and processing
 * the usage of the materialization parser function.
 *
 * @author Ingo Steinbauer
 *
 */

/**
 * This group contains all parts of the Data Import extension that deal with materializing web service results.
 * @defgroup DIWSMaterialization
 * @ingroup DIWebServices
 */

global $wgExtensionFunctions, $wgHooks;
$wgExtensionFunctions[] = 'materializePF_Setup';
$wgHooks['LanguageGetMagic'][] = 'materializePF_Magic';

$wgHooks['ArticleSaveComplete'][] = 'materializePF_saveHook';
$wgHooks['ArticleDelete'][] = 'materializePF_deleteHook';

global $smwgDIIP;
require_once("$smwgDIIP/specials/Materialization/SMW_HashProcessor.php");
require_once("$smwgDIIP/specials/Materialization/SMW_MaterializationStorageAccess.php");

function materializePF_Setup() {
	global $wgParser;
	$wgParser->setFunctionHook( 'materializePF', 'materializePF_render' );
}


function materializePF_Magic( &$magicWords, $langCode ) {
	$magicWords['materializePF'] = array( 0, 'materialize' );
	return true;
}


function materializePF_render(&$parser) {
	$parameters = materializePF_getParameters(func_get_args());
	
	if($parser->OutputType() == 2){
		if(!(($parameters["update"] === "false" && $parameters["materialized"] != null) 
				|| $parameters["update"] === "both")){
			$parameters["materialized"] = $parser->replaceVariables("{{subst:".substr($parameters["call"], 2));
		}
		
		if($parameters["update"] != "final"){
			$parameters["call"] = str_replace("{","##mcoll##", $parameters["call"]);
			$parameters["call"] = str_replace("}","##mcolr##", $parameters["call"]);
			$parameters["call"] = str_replace("|","##pipe##", $parameters["call"]);
			$output = "{{#materialize:".$parameters["call"]."\n";
			$output .= "| update = ".$parameters["update"]."\n";
			$output .= "| materialized = \n".$parameters["materialized"]; 
			$output .= "\n}}";
		} else {
			$output = $parameters["materialized"];
		}
	} else {
		global $wgsmwRememberedMaterializations;
		
		$dbAccess = SMWMaterializationStorageAccess::getInstance();
		$db = $dbAccess->getDatabase();
		$pageId = $parser->getTitle()->getArticleID();
		$callHash = SMWHashProcessor::generateHashValue($parameters["call"]);
		$materialized = null;
		$sourceHash = $db->getMaterializationHash($pageId, $callHash); 
		if($sourceHash == null){
			$materialized = trim($parser->replaceVariables($parameters["call"])); 
			$materializationHash = SMWHashProcessor::generateHashValue(
				$materialized);
			$db->addMaterializationHash($pageId, $callHash, $materializationHash);	
		} else if ($parameters["update"] == "true"){
			$materialized = trim($parser->replaceVariables($parameters["call"]));
			$materializationHash = SMWHashProcessor::generateHashValue(
				$materialized);
			$db->deleteMaterializationHash($pageId, $callHash);	
			$db->addMaterializationHash($pageId, $callHash, $materializationHash);
		}
		
		$wgsmwRememberedMaterializations[$callHash] = null;
		
		$output = $parameters["materialized"];
		if($parameters["update"] == "both"){
			if($sourceHash){
				$materialized = trim($parser->replaceVariables($parameters["call"]));
				if(!SMWHashProcessor::isHashValueEqual(
						SMWHashProcessor::generateHashValue($materialized), 
						$sourceHash)){
					$output .= "<br/>".$materialized;
				}
			}
		} else if($parameters["update"] == "false"){
			$output = $parameters["materialized"];
			if($sourceHash){
				$materialized = trim($parser->replaceVariables($parameters["call"]));
				if(!SMWHashProcessor::isHashValueEqual(
						SMWHashProcessor::generateHashValue($materialized), 
						$sourceHash)){
					$output .= smwfEncodeMessages(array(wfMsg('smw_wwsm_update_msg')));
				}
			}	
		}
	}
	return $output;
}

function materializePF_saveHook(&$article, &$user, &$text){
	error();
	$articleId  = $article->getID();
	if($articleId != null){
		materializePF_updateDB($articleId);
	}
	return true;
}

function materializePF_deleteHook(&$article, &$user, $reason){
	$articleId  = $article->getID();
	materializePF_updateDB($articleId);
	return true;	
}

/**
 * method is called in the article save complete and
 * the delete hook. it checks for saved materializations
 * which can be removed from the database
 * 
 * @param $articleId
 */
function materializePF_updateDB($articleId){
	global $wgsmwRememberedMaterializations;
	
	$dbAccess = SMWMaterializationStorageAccess::getInstance();
	$db = $dbAccess->getDatabase();
	
	$savedMaterializations = $db->getCallHashes($articleId);
	
	foreach($savedMaterializations as $sourceHash => $dontCare){
		if(!array_key_exists($sourceHash, $wgsmwRememberedMaterializations)){
			$db->deleteMaterializationHash($articleId, $sourceHash);
		}
	}
	$wgsmwRememberedMaterializations =array();
	
}

/**
 * Utility method for the parser function which produces an
 * associative array from the parameters of the parser function
 * 
 * @param array<string> $parameters
 * @return array<string> 
 */
function materializePF_getParameters($parameters){
	$materialized = "";
	for($i=2; $i < sizeof($parameters); $i++){
		$parameter = trim($parameters[$i]);
		if (substr($parameter,0, 6) == "update"){
			$update = trim(substr($parameter, strpos($parameter, "=")+1, strlen($parameter)));
		} else if (substr($parameter,0, 12) == "materialized"){
			$materialized = trim(substr($parameter, strpos($parameter, "=")+1, strlen($parameter)));
		}
	}
	$response = array();
	$response["call"] = $parameters[1];
	$response["call"] = str_replace("##mcoll##", "{", $response["call"]);
	$response["call"] = str_replace("##mcolr##","}", $response["call"]);
	$response["call"] = str_replace("##pipe##","|", $response["call"]);
			
	if(!isset($update)){
		$response["update"] = "false";
	} else if(!($update === "false" || $update === "true" || 
				$update === "both" || $update === "final")){
		$response["update"] = "false";
	} else {
		$response["update"] = $update;
	}
	$response["materialized"] = $materialized;
	
	return $response;
}