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
 * the usage of the materialization parser function.
 *
 * @author Ingo Steinbauer
 *
 */

global $wgExtensionFunctions, $wgHooks;
$wgExtensionFunctions[] = 'materializePF_Setup';
$wgHooks['LanguageGetMagic'][] = 'materializePF_Magic';

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
			$output = "{{#materialize:".$parameters["call"]."\n";
			$output .= "| update = ".$parameters["update"]."\n";
			$output .= "| materialized = \n".$parameters["materialized"]; 
			$output .= "\n}}";
		} else {
			$output = $parameters["materialized"];
		}
	} else {
		if($parameters["update"] == "both"){
			$output = $parameters["materialized"];
			$materialized = $parser->replaceVariables($parameters["call"]);
			if($output != trim($materialized)){
				$output .= "<br/>".$materialized;
			}
		} else if($parameters["update"] == "false"){
			$output = $parameters["materialized"];
			$materialized = $parser->replaceVariables($parameters["call"]);
			if($output != trim($materialized)){
				$output .= smwfEncodeMessages(array(wfMsg('smw_wwsm_update_msg')));
			}	
		} else {
			$output = $parameters["materialized"];
		}
	}
	return $output;
}

/**
 * Utility method for the parser function which produces an
 * associative array from the parameters of the parser function
 * 
 * @param array<string> $parameters
 * @return array<string> 
 */
function materializePF_getParameters($parameters){
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
	if($update == null){
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

?>