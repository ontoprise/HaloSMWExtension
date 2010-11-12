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

$wgAjaxExportList[] = 'smwf_wsu_processStep1';
$wgAjaxExportList[] = 'smwf_wsu_processStep2';
$wgAjaxExportList[] = 'smwf_wsu_getPreview';
$wgAjaxExportList[] = 'smwf_uws_getPage';

$wgHooks['ajaxMIMEtype'][] = 'smwf_uws_getPageMimeType';

function smwf_uws_getPageMimeType($func, & $mimeType) {
    if ($func == 'smwf_uws_getPage') $mimeType = 'text/html; charset=utf-8';
   return true;
}

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
	
	$response .= $webService->getProtocol().";";
	
	foreach($parameters->children() as $param){
		if($param->attributes()->name != DI_PROPERTIES_ALIAS || $param->attributes()->path != DI_PROPERTIES){
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
			if(($part->attributes()->property != DI_ALL_SUBJECTS || $part->attributes()->name != DI_ALL_SUBJECTS_ALIAS)
					&& ($part->attributes()->property != DI_ALL_PROPERTIES || $part->attributes()->name != DI_ALL_PROPERTIES_ALIAS)
					&& ($part->attributes()->property != DI_ALL_OBJECTS || $part->attributes()->name != DI_ALL_OBJECTS_ALIAS)){
				if(strlen(''.$part->attributes()->name) > 0){ //this is for ignoring namespace definitions
					$response .= $result->attributes()->name;
					$response .= ".".$part->attributes()->name;
					$response .= ";";
				}
			}
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
	return SMWWebServiceUsage::getPreview($articleName, $parameters);
}

function smwf_uws_getPage($args= "") {
	global $wgServer, $wgScript, $wgLang;

        $uwsScript = $wgScript.'/'.$wgLang->getNsText(NS_SPECIAL).':UseWebService';
	$page = "";
	if (function_exists('curl_init')) {
		list($httpErr, $page) = uws_doHttpRequestWithCurl($wgServer, $uwsScript);
	}
	else {
		if (strtolower(substr($wgServer, 0, 5)) == "https"){
			return "Error: for HTTPS connections please activate the Curl module in your PHP configuration";
		}
		list ($httpErr, $page) =
			uws_doHttpRequest($wgServer, $_SERVER['SERVER_PORT'], $uwsScript);
	}
	// this happens at any error (also if the URL can be called but a 404 is returned)
	if ($page === false || $httpErr != 200){
		return "Error: The Data Import extension seems not to be installed. Please install that extension in order to be able to add Web Service calls.<br/>HTTP Error code ".$httpErr;
	}

	// create the new source code, by removing the wiki stuff,
	// keep the header (because of all css and javascripts) and the main content part only
	$newPage = "";
	uws_mvDataFromPage($page, $newPage, '<body');
	$newPage.= '<body style="background-image:none; background-color: #ffffff;"><div id="globalWrapper">';

	uws_mvDataFromPage($page, $newPage, "<!-- start content -->", false);
	Uws_mvDataFromPage($page, $newPage, "<!-- end content -->");
	$newPage.="</div></body></html>";

	// have a string where to store JS command for onload event
	$onloadArgs = '';

	// parse submited params
	$params = array();
	parse_str($args, $params);

	if (strlen($onloadArgs) > 0)
	$newPage = str_replace('<body',
    	'<body onload="'.$onloadArgs.'"',
		$newPage);


	return $newPage;

}

function uws_doHttpRequest($server, $port, $file) {
	if ($file{0} != "/") $file = "/".$file;
	$server = preg_replace('/^http(s)?:\/\//i', '', $server);
	$cont = "";
	$ip = gethostbyname($server);
	$fp = fsockopen($ip, $port);
	if (!$fp) return array(-1, false);
	$com = "GET $file HTTP/1.1\r\nAccept: */*\r\n".
           "User-Agent: ".$_SERVER['HTTP_USER_AGENT']."\r\n".
           "Host: $server:$port\r\n".
           "Connection: Keep-Alive\r\n";
	if (isset($_SERVER['AUTH_TYPE']) && isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']))
	$com .= "Authorization: Basic ".base64_encode($_SERVER['PHP_AUTH_USER'].":".$_SERVER['PHP_AUTH_PW'])."\r\n";
	$com .= "\r\n";
	fputs($fp, $com);
	while (!feof($fp))
	$cont .= fread($fp, 1024);
	fclose($fp);
	$httpHeaders= explode("\r\n", substr($cont, 0, strpos($cont, "\r\n\r\n")));
	list($protocol, $httpErr, $message) = explode(' ', $httpHeaders[0]);
	$offset = 8;
	$cont = substr($cont, strpos($cont, "\r\n\r\n") + $offset );
	return array($httpErr, $cont);
}

/**
 * retrieve a web page via curl
 *
 * @param string server i.e. http://www.domain.com (incl protocol prefix)
 * @param string file	i.e. /path/to/script.cgi or /some/file.html
 * @return array(int, string) with httpCode, page
 */
function uws_doHttpRequestWithCurl($server, $file) {
	if ($file{0} != "/") $file = "/".$file;
	$c = curl_init();
	curl_setopt($c, CURLOPT_URL, $server.$file);
	curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
	// needs authentication?
	if (isset($_SERVER['AUTH_TYPE']) && isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
		curl_setopt($c, CURLOPT_USERPWD, $_SERVER['PHP_AUTH_USER'].":".$_SERVER['PHP_AUTH_PW']);
	}
	// user agent (important i.e. for Popup in FCK Editor)
	if (isset($_SERVER['HTTP_USER_AGENT']))
	curl_setopt($c, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);

	$page = curl_exec($c);
	$httpErr = curl_getinfo($c, CURLINFO_HTTP_CODE);
	curl_close($c);
	return array($httpErr, $page);
}

/**
 * copy data from page to newPage by defing a pattern, up to where
 * the string is copied from the begining. If $copy is set to false
 * the data will be deleted from $page without copying it to $newPage
 */
function uws_mvDataFromPage(&$page, &$newPage, $pattern, $copy= true) {
	$pos = strpos($page, $pattern);
	if ($pos === false) return;
	if ($copy) {
		$newPage.= substr($page, 0, $pos);
	}
	$page = substr($page, $pos -1);
}
