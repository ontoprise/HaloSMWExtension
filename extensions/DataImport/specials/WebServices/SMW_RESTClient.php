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
		
		$uri = $this->mURI;

		if(array_key_exists("__rest__user_agent", $parameters)){
			$userAgent = $parameters["__rest__user_agent"][0];
			unset($parameters["__rest__user_agent"]);
		} else {
			$userAgent = "smw data import extension";
		}

		if(array_key_exists("__rest__accept", $parameters)){
			$accept = $parameters["__rest__user_agent"][0];
			unset($parameters["__rest__accept"]);
		} else {
			$accept = "";
		}
		
		if(array_key_exists("_subject", $parameters)){
			unset($parameters["_subject"]);
		}
			
		if(strtolower($operationName) == "get"){
			$params = array('http' => array('method' => 'GET', 'user_agent' => $userAgent,));
			if(strlen($accept) > 0){
				$params["accept"] = $accept;
			}
			$first = true;

			foreach($parameters as $key => $values){
				foreach($values as $value){
					if($first){
						$uri .= "?".$key."=".urlencode($value);
						$first=false;
					} else {
						$uri .= "&".$key."=".urlencode($value);
					}
				}
			}
		} else if (strtolower($operationName) == "post"){
			if(array_key_exists("__post__separator", $parameters)){
				$separator = $parameters["__post__separator"][0];
				unset($parameters["__post__separator"]);
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
    			'content-length' =>strlen($data),
				'user_agent' => $userAgent,
				'content' => $data
			));
			if(strlen($accept) > 0){
				$params["accept"] = $accept;
			}
		} else {
			return "unknown method name";
		}

		$ctx = stream_context_create($params);

		$fp = @ fopen($uri, 'rb', true, $ctx);

		if (!$fp) {
			return wfMsg('smw_wws_client_connect_failure').$uri;
		}

		$response = stream_get_contents($fp);
		if ($response === false) {
			return wfMsg('smw_wws_client_connect_failure').$uri;
		}

		return array($response);
	}
	
	public function getURI(){
		return $this->mURI;
	}
}
