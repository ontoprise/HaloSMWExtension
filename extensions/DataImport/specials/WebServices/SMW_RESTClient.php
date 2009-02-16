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
 * Class for the access of RESTful web services. It implements the interface
 * <IWebServiceClient>.
 *
 * @author Ingo Steinbauer
 *
 */
class SMWRestClient implements IWebServiceClient {

	private $mURI;		  // string: the URI of the web service

	private $mAuthenticationType; //todo:describe
	private $mAuthenticationLogin; //todo:describe
	private $mAuthenticationPassword; //todo:describe

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
	 * @param string [] $parameters : todo: add documentation
	 */
	public function call($operationName, $parameters) {
		$uri = $this->mURI;

		if(strtolower($operationName) == "get"){
			$params = array('http' => array('method' => 'GET'));
			$first = true;
			foreach($parameters as $key => $value){
				if($first){
					$uri .= "?".$key."=".urlencode($value);
					$first=false;
				} else {
					$uri .= "&".$key."=".urlencode($value);
				}
			}
		} else if (strtolower($operationName) == "post"){
			$data = http_build_query($parameters);
			$params = array('http' => array('method' => 'POST', 'content' => $data));
		} else {
			return "unknown method name";
		}

		$ctx = stream_context_create($params);
		$fp = fopen($uri, 'rb', true, $ctx);

		print_r($fp, true);

		if (!$fp) {
			return "It was not possible to connect to ".$uri.". Reason: ".$php_errormsg;
		}

		$response = @stream_get_contents($fp);
		if ($response === false) {
			return "It was not possible to connect to ".$uri.". Reason: ".$php_errormsg;
		}

		return array($response);
	}

}

?>