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
global $smwgDIIP;
require_once("$smwgDIIP/specials/WebServices/SMW_IWebServiceClient.php");


/**
 * @file
 * @ingroup DIWebServices
 *
 * @author Ingo Steinbauer
 */

/**
 * Class for the access of RESTful web services. It implements the interface
 * <IWebServiceClient>.
 *
 * @author Ingo Steinbauer
 *
 */
class SMWRestClient implements IWebServiceClient {

	private $mURI;		  // string: the URI of the web service

	private $mAuthenticationType;
	private $mAuthenticationLogin;
	private $mAuthenticationPassword;
	private $contentType = false;
	private $responseHeader = false; 

	/**
	 * Constructor
	 * Creates an instance of an SMWRestClient with the given URI.
	 *
	 * @param string $uri
	 * 		URI of the web service
	 * @param string $authenticationType
	 * @param string $authenticationPassword
	 * @param string $authenticationlogin
	 * @return SMWRestClient
	 */
	public function __construct($uri, $authenticationType = "",
	$authenticationLogin = "", $authenticationPassword = "") {
		if($authenticationType == "http"){
			$protocol = substr($uri, 0, strpos($uri, "://") +3 );
			$host = substr($uri, strpos($uri, "://") +3 );
			$uri = $protocol.urlencode($authenticationLogin).":".urlencode($authenticationPassword)."@".$host;
		}

		$this->mURI = $uri;


	}

	/**
	 * Calls the web service
	 *
	 * @param string $operationName : post or get
	 * @param string [] $parameters : parameters for the web service call
	 */
	public function call($operationName, $parameters) {
		$this->mURI;

		if(array_key_exists("__rest__uri", $parameters)){
			$this->mURI .= trim(strip_tags($parameters["__rest__uri"][0]));
			unset($parameters["__rest__uri"]);
		}
		
		if(array_key_exists(DI_URL_SUFFIX, $parameters)){
			$this->mURI .= trim(strip_tags($parameters[DI_URL_SUFFIX][0]));
			unset($parameters[DI_URL_SUFFIX]);
		}
		
		$uri = $this->mURI;
		
				//todo define constants
		//create http request header if appropriaste parameters exist
		$header = "";
		if(array_key_exists("__rest__user_agent", $parameters)){
			$header .= "user_agent: ".$parameters["__rest__user_agent"][0]."\r\n";
			unset($parameters["__rest__user_agent"]);
		} else {
			$header .= "user_agent: smw data import extension\r\n";
		}

		if(array_key_exists(DI_ACCEPT, $parameters)){
			$header .= " accept: ".$parameters[DI_ACCEPT][0]."\r\n";
			unset($parameters[DI_ACCEPT]);
		}
		
		//the subject parameter is used for RDF extraction, it must not
		//be passed to the web service
		if(array_key_exists(DI_SUBJECT, $parameters)){
			unset($parameters[DI_SUBJECT]);
		}
		
		//the propertys parameter is used for RDF extraction, it must not
		//be passed to the web service
		if(array_key_exists(DI_PROPERTIES, $parameters)){
			unset($parameters[DI_PROPERTIES]);
		}
		
		//the language parameter is used for RDF extraction, it must not
		//be passed to the web service
		if(array_key_exists(DI_LANGUAGE, $parameters)){
			unset($parameters[DI_LANGUAGE]);
		}
		
		if(strtolower($operationName) == "get"){
			$params = array('http' => array('method' => 'GET', 'header' => $header, 'max_redirects' => 10,));
			
			$first = true;
			foreach($parameters as $key => $values){
				foreach($values as $value){
					if($first){
						$uri .= '?';
						$first=false;
					} else {
						$uri .= '&';
					}
					$uri .= http_build_query(array($key => $value));
				}
			}
		} else if (strtolower($operationName) == "post"){
			if(array_key_exists(DI_POST_SEPARATOR, $parameters)){
				$separator = $parameters[DI_POST_SEPARATOR][0];
				unset($parameters[DI_POST_SEPARATOR]);
			} else {
				$separator = "&";
			}

			$data = "";
			$first = true;
			foreach ($parameters as $key => $values){
				foreach($values as $value){
					if(!$first){
						$data .= $separator;
					}
					$data .= urlencode($key)."=".urlencode($value);
					$first = false;
				}
			}

			$params = array('http' => array(
    			'method' => 'POST',
    			'header' => $header,
				'content-length' =>strlen($data),
				'content' => $data
			));
		} else {
			return "unknown method name";
		}
		
		$uri = trim($uri);
		
		$ctx = stream_context_create($params);

		$fp = fopen($uri, 'rb', true, $ctx);
		
		if (!$fp) {
			return wfMsg('smw_wws_client_connect_failure').$uri;
		}

		$response = stream_get_contents($fp);
		
		if ($response === false) {
			return wfMsg('smw_wws_client_connect_failure').$uri;
		}
		
		$this->responseHeader = $http_response_header;
		
		return array($response);
	}
	
	public function getURI(){
		return $this->mURI;
	}
	
	public function getContentType(){
		foreach($this->responseHeader as $field){
			if(strpos(strtolower($field), "content-type") !== false){
				$this->contentType = (!$this->contentType && preg_match('/\/atom\+xml/', $field)) ? 'Atom' : $this->contentType;
  				$this->contentType = (!$this->contentType && preg_match('/\/rdf\+xml/', $field)) ? 'RDFXML' : $this->contentType;
  				$this->contentType = (!$this->contentType && preg_match('/\/(x\-)?turtle/', $field)) ? 'Turtle' : $this->contentType;
  				$this->contentType = (!$this->contentType && preg_match('/\/rdf\+n3/', $field)) ? 'Turtle' : $this->contentType;
  				$this->contentType = (!$this->contentType && preg_match('/text\/html/', $field)) ? 'SemHTML' : $this->contentType;
  				
				if($this->contentType) break;
  			}
		}
		
		return $this->contentType;		
	}
}
