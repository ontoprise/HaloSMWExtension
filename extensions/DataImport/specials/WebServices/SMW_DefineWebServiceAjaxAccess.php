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


global $smwgDIIP, $smwgHaloIP;
require_once($smwgDIIP.'/specials/WebServices/SMW_WebService.php');
require_once($smwgDIIP.'/specials/WebServices/SMW_WSDLArrayDetector.php');
require_once($smwgHaloIP.'/includes/SMW_OntologyManipulator.php');

/**
 * this method is called after step 1 (specifying uri)
 *
 * @param string $uri uri of a wsdl
 * @return string with "-,"-separated list of method-names provided by the wwsd
 */
global $wsClient;

function smwf_ws_processStep1($uri, $authenticationType, $user, $pw){
	global $wsClient;
	
	$wsClient = DefineWebServiceSpecialAjaxAccess::createWSClient($uri, $authenticationType, $user, $pw);
	if(is_array($wsClient)){
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
function smwf_ws_processStep2($uri, $authenticationType, $user, $pw, $methodName){
	global $wsClient;
	$wsClient = DefineWebServiceSpecialAjaxAccess::createWSClient($uri, $authenticationType, $user, $pw);
	
	$rawParameters = $wsClient->getOperation($methodName);
	
	$parameters = array();
	
	$numParam = count($rawParameters);
	if($numParam == 1){
		if($rawParameters[0][0] == 0){
			return "##no params required##";
		}
	}
	$typePath = null;
	for ($i = 1; $i < $numParam; ++$i) {
		$pName = $rawParameters[$i][0];
		$pType = $rawParameters[$i][1];
		$tempFlat = WebService::flattenParam($pName, $pType, $wsClient, $typePath);
		
		//this is necessary because the gui expects
		//that parameter paths start with a single "/"
		for($k=0; $k < count($tempFlat); $k++){
			$tempFlat[$k] = substr($tempFlat[$k], 1);
		}
		$parameters = array_merge($parameters , $tempFlat);
	}
	
	return "##handle exceptions##;".implode(";", $parameters);
}

/**
 * this method is called after step 3 (specify parameters)
 *
 * @param unknown_string $uri uri of the wsdl
 * @param unknown_string $methodName the method which was chosen by the user
 * @return string ";"-separated list of return types that have to be specified
 * 			for this method
 */
function smwf_ws_processStep3($uri, $authenticationType, $user, $pw, $methodName){
	
	$wsClient = DefineWebServiceSpecialAjaxAccess::createWSClient($uri, $authenticationType, $user, $pw);
	
	$rawResult = $wsClient->getOperation($methodName);
	
	$flatResult = WebService::flattenParam("", $rawResult[0], $wsClient, $typePath);
	
	return "todo:handle exceptions;".implode(";", $flatResult);
}

/**
 * this method is called after step 3 (specify ws-name)
 *
 * @param string $name name of the webservice
 * @param string $wwsd the wwsd which was created
 * @return string error/ok signals if the wwsd could be validated
 */
function smwf_ws_processStep6($name, $wwsd, $user, $wsSyntax){
	global $wgHooks;
	$wgHooks['ArticleSaveComplete'][] = 'WebServiceManager::articleSavedHook';
	
	//$editResult = explode(",", smwf_om_EditArticle("webservice:".$name, $user, $wwsd.$wsSyntax, ""));
	$editResult = explode(",", smwf_om_EditArticle("webservice:".$name, $user, $wwsd, ""));
	
	if($editResult[0]){
		$ws = WebService::newFromWWSD($name, $wwsd);
		if(is_array($ws)){
			return "isa ".implode(";", $ws);
		} else {
			//$res = $ws->validateWWSD();
			// $res = $ws->store();
			// if(!$res){
			//	return "error";
			//}
			return smwf_om_TouchArticle("webservice:".$name);
		}
	} else return "false done";
}

class DefineWebServiceSpecialAjaxAccess{
	/**
	 * creates a webservice-client for the given uri
	 *
	 * @param string $uri uri of the wsdl
	 * @return ws-client
	 */
	
	public static function createWSClient($uri, $authenticationType, $user, $pw) {
		global $smwgDIIP;

		try {
			$mProtocol = "SOAP";
			include_once($smwgDIIP . "/specials/WebServices/SMW_".
			$mProtocol."Client.php");
			$classname = "SMW".ucfirst(strtolower($mProtocol))."Client";
			if (!class_exists($classname)) {
				return array(wfMsg("smw_wws_invalid_protocol"));
			}
			$wsClient = new $classname($uri, $authenticationType, $user, $pw);
		} catch (Exception $e) {
			return array(wfMsg("smw_wws_invalid_wwsd"));
		}
		return $wsClient;
	}

	/**
	 *
	 *
	 * @param string $uri
	 * @param SoapClient $wsClient
	 * @param string $name
	 * @param string $type
	 * @param boolean $result
	 * @param  $typePath
	 * @return unknown
	 */

	/**
	 *
	 * @param string $uri
	 * 		the uri of the wsdl
	 * @param SMWSoapClient $wsClient
	 * 		a properly initialized soapclient for accessing the wsdl
	 * @param string $name
	 * 		The fields of the type are added to this name, separated by a dot.
	 * @param string $type
	 * 		The name of an XSD base type or a type defined in the WSDL.
	 * @param array<string> $typePath
	 * 		This array contains all types that were encountered in the recursion.
	 * 		To avoid an inifinite loop, the recursion stops if $type is already
	 * 		in the $typePath. This parameter is omitted in the top level call.
	 * @return array<string>
	 * 		All resulting paths. If a path causes an endless recursion, the
	 * 		keyword ##overflow## is appended to the path.
	 */
	//public static function getFlatParameters($uri, $wsClient, $name, $type, &$typePath=null){
	//	$flatParams = WebService::flattenParam($name, $type, $wsClient, $typePath);

		//$arrayDetector = new WSDLArrayDetector($uri);

		//$adParameters = $arrayDetector->getArrayPaths($type, $name);

		//if($result){
		//	$adParameters = $arrayDetector->cleanResultParts($adParameters);
		//}

	//	return $flatParams;
		//return $arrayDetector->mergePaths($flatParams, $adParameters);
	//}
}