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
 * This file provides methods that are accessed by ajax-calls from
 * the special page for defining a wwsd.
 *
 * @author Ingo Steinbauer
 *
 */
if ( !defined( 'MEDIAWIKI' ) ) die;
global $wgAjaxExportList;

$wgAjaxExportList[] = 'smwf_ws_processStep1';
$wgAjaxExportList[] = 'smwf_ws_processStep2';
$wgAjaxExportList[] = 'smwf_ws_processStep3';
$wgAjaxExportList[] = 'smwf_ws_processStep6';


global $smwgHaloIP;
require_once($smwgHaloIP.'/specials/SMWWebService/SMW_WebService.php');
require_once($smwgHaloIP.'/specials/SMWWebService/SMW_WSDLArrayDetector.php');

/**
 * this method is called after step 1 (specifying uri)
 *
 * @param string $uri uri of a wsdl
 * @return string with "-,"-separated list of method-names provided by the wwsd
 */

function smwf_ws_processStep1($uri){
	$wsClient = createWSClient($uri);	if(is_array($wsClient)){
		return "false";
	} else {
		$operations = $wsClient->getOperations();
		return "todo:handle exceptions;".implode(";", $operations);
	}
}

/**
 * this method is called after step 2 (choose method)
 *
 * @param unknown_string $uri uri of the wsdl
 * @param unknown_string $methodName the method which was chosen by the user
 * @return string ";"-separated list of parameters that have to be specified
 * 			for this method
 */
function smwf_ws_processStep2($uri, $methodName){
	$wsClient = createWSClient($uri);
	$rawParameters = $wsClient->getOperation($methodName);
	$parameters = array();
	$numParam = count($rawParameters);
	if($numParam == 1){
		if($rawParameters[0][0] == 0){
			return "todo:handle noparams;";
		}
	}
	for ($i = 1; $i < $numParam; ++$i) {
		$pName = $rawParameters[$i][0];
		$pType = $rawParameters[$i][1];
		$tempFlat = getFlatParameters($uri, $wsClient, $pName, $pType, false);
		$parameters = array_merge($parameters , $tempFlat);
	}
	return "todo:handle exceptions;".implode(";", $parameters);
}

/**
 * this method is called after step 3 (specify parameters)
 *
 * @param unknown_string $uri uri of the wsdl
 * @param unknown_string $methodName the method which was chosen by the user
 * @return string ";"-separated list of return types that have to be specified
 * 			for this method
 */
function smwf_ws_processStep3($uri, $methodName){
	$wsClient = createWSClient($uri);
	$rawResult = $wsClient->getOperation($methodName);
	$flatResult = getFlatParameters($uri, $wsClient ,"", $rawResult[0], true);
	return "todo:handle exceptions;".implode(";", $flatResult);
}

/**
 * this method is called after step 3 (specify ws-name)
 *
 * @param string $name name of the webservice
 * @param string $wwsd the wwsd which was created
 * @return string error/ok signals if the wwsd could be validated
 */
function smwf_ws_processStep6($name, $wwsd){
	$ws = WebService::newFromWWSD($name, $wwsd);
	if(is_array($ws)){
		return implode(";", $ws);
	} else {
		$res = $ws->validateWithWSDL();
		//TS		if(is_array($res)){
		//			return implode(";", $res);
		//		}
		$res = $ws->store();
		if(!$res){
			return "error";
		}
		return "ok";
	}
}


/**
 * creates a webservice-client for the given uri
 *
 * @param string $uri uri of the wsdl
 * @return ws-client
 */
function createWSClient($uri) {
	// include the correct client
	global $smwgHaloIP;

	$wsClient;

	try {
		//todo: also allow other protocols
		$mProtocol = "SOAP";
		include_once($smwgHaloIP . "/specials/SMWWebService/SMW_".
		$mProtocol."Client.php");
		$classname = "SMW".ucfirst(strtolower($mProtocol))."Client";
		if (!class_exists($classname)) {
			return array(wfMsg("smw_wws_invalid_protocol"));
		}
		$wsClient = new $classname($uri);
	} catch (Exception $e) {
		return array(wfMsg("smw_wws_invalid_wwsd"));
	}
	return $wsClient;
}



/**
 * creates flat "."-separated paths
 *
 * @param unknown_type $wsClient
 * @param unknown_type $name
 * @param unknown_type $type
 * @param unknown_type $typePath
 * @return unknown
 */
function flattenParam($wsClient, $name, $type, &$typePath=null) {
	//todo: this method was copied from WebService -> refactor
	$flatParams = array();

	if (!$wsClient->isCustomType($type) && substr($type,0, 7) != "ArrayOf") {
		// $type is a simple type
		$flatParams[] = $name;
		return $flatParams;
	}

	if (substr($type,0, 7) == "ArrayOf") {
		if (!$wsClient->isCustomType(substr($type,0, 7))) {
			$flatParams[] = $name."[]";
			return $flatParams;
		}
	}

	$tp = $wsClient->getTypeDefinition($type);
	foreach ($tp as $var => $type) {
		if(substr($type,0, 7) == "ArrayOf"){
			$type = substr($type, 7);
			$fname = empty($name) ? $var."[]" : $name.'.'.$var."[]";
		} else {
			$fname = empty($name) ? $var : $name.'.'.$var;
		}
		if ($wsClient->isCustomType($type)) {
			if (!$typePath) {
				$typePath = array();
			}
			if (in_array($type, $typePath)) {
				// stop recursion
				$flatParams[] = $fname."##overflow##";
				break;
			}
			$typePath[] = $type;
			$names = flattenParam($wsClient, $fname, $type, $typePath);
			$flatParams = array_merge($flatParams,$names);
			array_pop($typePath);
		} else {
			$flatParams[] = $fname.=" (".$type.")";
		}
	}
	return $flatParams;
}

function getFlatParameters($uri, $wsClient, $name, $type, $result=false, &$typePath=null){
	$flatParams = flattenParam($wsClient, $name, $type, $typePath);

	//todo: what if soap but no wsdl available
	
	//todo require_once
	$arrayDetector = new WSDLArrayDetector($uri);
			
	//todo: is type correct here?
	$adParameters = $arrayDetector->getArrayPaths($type, $name);

	if($result){
		$adParameters = $arrayDetector->cleanResultParts($adParameters);
	}
	
	return $arrayDetector->mergePaths($flatParams, $adParameters);
}


?>