<?php
/*
 * Copyright (C) Vulcan Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program.If not, see <http://www.gnu.org/licenses/>.
 *
 */

global $smwgDIIP;
require_once("$smwgDIIP/specials/WebServices/SMW_RESTClient.php");

/**
 * @file
 * @ingroup DIWebServices
 *
 * @author Ingo Steinbauer
 */

/**
 * Class for the access of LD sources. It implements the interface
 * <IWebServiceClient>. This class is only a wrapper for the SMWRestClient.
 *
 * @author Ingo Steinbauer
 *
 */
class SMWLinkeddataClient implements IDIWebServiceClient {

	private $mRESTClient;

	/**
	 * Constructor
	 * Creates an instance of an SMWLinkeddataClient with the given URI.
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
		$this->mRESTClient = new SMWRestClient($uri, $authenticationType, $authenticationLogin, $authenticationPassword); 
	}

	/**
	 * Calls the web service
	 *
	 * @param string $operationName : post or get
	 * @param string [] $parameters : parameters for the web service call
	 */
	public function call($operationName, $parameters) {
		if(!array_key_exists(DI_ACCEPT, $parameters)){
			$parameters[DI_ACCEPT][0] = "*/*, application/rdf+xml";
		}
		$response = $this->mRESTClient->call($operationName, $parameters); 
		return  $response;
	}
	
	public function getURI(){
		return $this->mRESTClient->getURI();
	}
	
	/*
	 * get content-type wich was returned in the HTTP response header
	 */
	public function getContentType(){
		return $this->mRESTClient->getContentType();		
	}
}
