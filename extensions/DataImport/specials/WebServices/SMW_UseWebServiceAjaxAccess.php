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
 * This file provides methods that are accessed by ajax-calls from
 * the special page for defining a wwsd.
 *
 * @author Ingo Steinbauer
 *
 */
if ( !defined( 'MEDIAWIKI' ) ) die;
global $wgAjaxExportList;

$wgAjaxExportList[] = 'smwf_wsu_processStep1';
$wgAjaxExportList[] = 'smwf_wsu_processStep2';
$wgAjaxExportList[] = 'smwf_wsu_getPreview';


/**
 * this method is called after step 1 (choose web service)
 *
 * @param string $uri uri of a wsdl
 * @return string with "-,"-separated list of method-names provided by the wwsd
 */
global $wsClient;

function smwf_wsu_processStep1($name){
	global $smwgDIIP;

	require_once($smwgDIIP.'/specials/WebServices/SMW_WebService.php');
	require_once($smwgDIIP . '/specials/WebServices/SMW_WSStorage.php');

	$webService = WebService::newFromName($name);
	$parameters = new SimpleXMLElement("<p>".$webService->getParameters()."</p>");

	$response = "";
	foreach($parameters->children() as $param){
		$response .= $param->attributes()->name;
		$response .= ";";
		$response .= $param->attributes()->optional;
		$response .= ";";
		$response .= $param->attributes()->defaultValue;
		$response .= ";";

		$subParameterProcessor = new SMWSubParameterProcessor(
		$param->asXML(), array());

		$nonOptionalSubParameters = $subParameterProcessor->getMissingSubParameters();
		foreach($nonOptionalSubParameters as $name => $value){
			$response .= $param->attributes()->name.".".$name.";";
			$response .= "false;";
		if($value == null){
				$response .= ";";
			} else {
				$response .= $value.";";
			}
		}

		$optionalSubParameters = $subParameterProcessor->getOptionalSubParameters();
		foreach($optionalSubParameters as $name => $value){
			$response .= $param->attributes()->name.".".$name.";";
			$response .= "true;";
			if($value == null){
				$response .= ";";
			} else {
				$response .= $value.";";
			}
		}

		$defaultSubParameters = $subParameterProcessor->getDefaultSubParameters();
		foreach($defaultSubParameters as $name => $value){
			$response .= $param->attributes()->name.".".$name.";";
			$response .= "false;";
			$response .= $value.";";
		}
	}
	return $response;
}

function smwf_wsu_processStep2($name){
	global $smwgDIIP;

	require_once($smwgDIIP.'/specials/WebServices/SMW_WebService.php');
	require_once($smwgDIIP . '/specials/WebServices/SMW_WSStorage.php');

	$webService = WebService::newFromName($name);
	$results = new SimpleXMLElement("<p>".$webService->getResult()."</p>");

	$response = "";
	foreach($results->children() as $result){
		foreach($result->children() as $part){
			$response .= $result->attributes()->name;
			$response .= ".".$part->attributes()->name;
			$response .= ";";
		}
	}
	return $response;

}

function smwf_wsu_getPreview($articleName, $wsSyn){
	global $smwgDIIP;
	require_once($smwgDIIP.'/specials/WebServices/SMW_WebServiceUsage.php');

	$wsSyn = str_replace("\n", "", $wsSyn);
	$wsSyn = substr($wsSyn, 0, strlen($wsSyn)-2);

	$wsSyn = explode("|", $wsSyn);

	$parameters = array();
	$parameters[] = "dummy";
	$parameters[] = substr($wsSyn[0], 6);
	for($i=1; $i < count($wsSyn); $i++){
		$parameters[] = $wsSyn[$i];
	}
	return webservice_getPreview($articleName, $parameters);
}

?>