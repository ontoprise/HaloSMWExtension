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

global $smwgHaloIP;
require_once("$smwgHaloIP/specials/SMWWebService/SMW_IWebServiceClient.php");

define('WWS_WSDL_NS', 'http://schemas.xmlsoap.org/wsdl/');
define('WWS_WSDL_NS', 'http://schemas.xmlsoap.org/wsdl/');
define('WWS_SOAP_NS', 'http://schemas.xmlsoap.org/wsdl/soap/');
define('WWS_XSD_NS', 'http://www.w3.org/2001/XMLSchema');
define('WWS_SOAPENC_NS', 'http://schemas.xmlsoap.org/soap/encoding/'); 

/**
 * Class for the access of SOAP web services. It implements the interface 
 * <IWebServiceClient>.
 * 
 * @author Thomas Schweitzer
 * 
 */
class SMWSoapClient implements IWebServiceClient {
	
	
	//--- Private fields ---

	private $mURI;		  // string: the URI of the web service
	private $mClient;	  // SoapClient: an instance of the soap client
	private $mOperations; // array(string=>array(varname,type)): The names of all operations
	private $mWsdl;		  // SimpleXMLElement: The content of the service's WSDL
	private $mTypes;	  // array(string=>string): A mapping from the name of a type's
						  //     field to its type.

	//--- Constructor ---
	
	/**
	 * Constructor
	 * Creates an instance of an SMWSoapClient with the given URI of a WSDL.
	 * If the WSDL is valid, an instance is returned otherwise an exception is 
	 * thrown.
	 * 
	 * @param string $uri
	 * 		URI of a WSDL that can be retrieved with HTTP_GET. 
	 * 
	 * @return SMWSoapClient
	 * 		If the WSDL can be accessed and is valid, a new instance of SMWSoapClient
	 * 		is returned.
	 */
	public function __construct($uri) {
		$this->mURI = $uri;	
		$this->mClient = null;
		if (!$this->getWSDL()) {
			throw new Exception("Invalid WSDL file");
		}
	}
		
	//--- Public functions ---
	
	/**
	 * Returns an array with the names of all operations of the web service.
	 *
	 * @return array<string>
	 * 		Names of all available operations.
	 */
	public function getOperations() {
		return array_keys($this->mOperations);
	}

	/**
	 * Returns the definition of an operation of the web service with its 
	 * result and parameters.
	 *
	 * @param string $opName
	 * 		Name of the requested operation.
	 * @return array
	 * 		Definition of the operation or 
	 *      <null>, if it is not provided by the service.
	 * 		The first element of the array contains the type name of the result. 
	 *      The following elements are arrays with two elements for the parameters. 
	 *      The first element is the parameter name, the second is its type.
	 *      Example:
	 * 		0: ResultType
	 *      1: (param1, TypeOfParam1)   
	 *      2: (param2, TypeOfParam2)
	 *    
	 */
	public function getOperation($opName) {
		return $this->mOperations[$opName];
	}
		
	/**
	 * Checks if the type with the given name is a custom type i.e. if it is
	 * defined in the WSDL file.
	 *
	 * @param string $typename
	 * 		Name of the type. Its namespace is stripped
	 * @return bool
	 * 		<true>, if the type is defined in the WSDL
	 * 		<false> otherwise.
	 */
	public function isCustomType($typename) {
		//Strip the namespace from the type name
		$pos = strpos($typename, ':');
		if ($pos) {
			$typename = substr($typename, $pos+1);
		}
		return $this->mTypes[$typename] !== null;
		
	}
	
	/**
	 * Tries to find the definitions of types in the WSDL.
	 * 
	 * 
	 * @param string $typename
	 * 		Name of the type whose definition is requested. Its namespace is stripped.
	 * 
	 * @return array(fieldname=>type) 
	 * 		The type's definition in an associative array that maps the fields
	 * 		of a (complex) type to its data type. If the type does not exist,
	 *      <null> is returned.
	 * 
	 */
	public function getTypeDefinition($typename) {
		//Strip the namespace from the type name
		$pos = strpos($typename, ':');
		if ($pos) {
			$typename = substr($typename, $pos+1);
		}

		return $this->mTypes[$typename];

	}
	
	/**
	 * Calls the web service
	 *
	 */
	public function call($operationName, $parameters) {
		ini_set("soap.wsdl_cache_enabled", "0"); // to be removed in the release version
 		$this->mClient = new SoapClient($this->mURI);
// 		
 		try {
			$response = $this->mClient->getPoint($parameters);
		} catch(Exception $e) {
 			return "_ws-error: ".print_r($e, true);
 		}
 		return $response;
 	}
	
	//---Private methods ---
	
	/**
	 * Tries to read the WSDL of this object via HTTP_GET from the object's
	 * URI. It is parsed and the operations and types of the web service are 
	 * extracted.
	 *
	 * @return boolean
	 * 		<true>, if the WSDL is successfully read and parsed and contains at
	 * 				least one operation
	 * 		<false> otherwise
	 */
	private function getWSDL() {
		ini_set("soap.wsdl_cache_enabled", "0");
		 
 		$this->mClient = new SoapClient($this->mURI);

 		try {
 			$functions = $this->mClient->__getFunctions();
 			$types = $this->mClient->__getTypes();
 		} catch (Exception $e) {
 			print_r($e);
 			return false;
 		}
 		
 		foreach ($functions as $f) {
 			if (preg_match("/\s*(.+?)\s(.+?)\s*\((.*?)\)/", $f, $matches)) {
 				$retType = $matches[1];
 				$fname = $matches[2];
 				$params = $matches[3];
 				$this->mOperations[$fname] = array($retType);
 				if ($params) {
 					$numParam = preg_match_all("/\s*(.+?)\s+\\$([^ ,]+)(\s|,)*/",$params, $pList);
 					for ($i = 0; $i < $numParam; ++$i) {
 						$this->mOperations[$fname][] = array($pList[2][$i], $pList[1][$i]);
 					} 
 				}
 			}
 			
 		}
 		
 		$this->mTypes = array();
 		foreach ($types as $t) {
 			if (preg_match("/\s*struct\s*(\b.*?)\s*\{([^}]*)\}/", $t, $matches)) {
 				$tname = $matches[1];
 				$fields = $matches[2];
				$numFields = preg_match_all("/\s*(\b.*?)\s+([^;]*);/",$fields, $fList);
 				for ($i = 0; $i < $numFields; ++$i) {
 					$this->mTypes[$tname][$fList[2][$i]] = $fList[1][$i];
 				} 
 			}
 		}
		
 		return true;
	}
	
}
?>