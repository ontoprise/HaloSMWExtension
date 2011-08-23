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
 * 
 * @author Thomas Schweitzer
 */

if ( !defined( 'MEDIAWIKI' ) ) die;
global $smwgDIIP;
require_once("$smwgDIIP/specials/WebServices/SMW_IWebServiceClient.php");

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
	private $mOperations = array(); // array(string=>array(varname,type)): The names of all operations
	private $mWsdl;		  // SimpleXMLElement: The content of the service's WSDL
	private $mTypes;	  // array(string=>string): A mapping from the name of a type's
	//     field to its type.
	private $mAuthenticationType;
	private $mAuthenticationLogin;
	private $mAuthenticationPassword;

	private $duplicates;
	private $wsdl;
	private $xs;

	//--- Constructor ---

	/**
	 * Constructor
	 * Creates an instance of an SMWSoapClient with the given URI of a WSDL.
	 * If the WSDL is valid, an instance is returned otherwise an exception is
	 * thrown.
	 *
	 * @param string $uri
	 * 		URI of a WSDL that can be retrieved with HTTP_GET.
	 * @param string $authenticationType
	 * @param string $authenticationPassword
	 * @param string $authenticationlogin
	 * @return SMWSoapClient
	 * 		If the WSDL can be accessed and is valid, a new instance of SMWSoapClient
	 * 		is returned.
	 */
	public function __construct($uri, $authenticationType = "",
			$authenticationLogin = "", $authenticationPassword = "") {
		
		$this->mURI = $uri;
		$this->mAuthenticationType = $authenticationType;
		$this->mAuthenticationLogin = $authenticationLogin;
		$this->mAuthenticationPassword = $authenticationPassword;
		
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
		return array_key_exists($typename, $this->mTypes);

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
	 */
	public function call($operationName, $parameters) {
		//ini_set("soap.wsdl_cache_enabled", "0"); // to be removed in the release version
			
		if($this->mAuthenticationType == "http"){
			$this->mClient = new SoapClient($this->mURI,
			array("login" => $this->mAuthenticationLogin,
				"password" => $this->mAuthenticationPassword,
				"trace" => true));
		} else {
			$this->mClient = new SoapClient($this->mURI);
		}
		
		try {
			if($parameters == null){
				$response = $this->mClient->$operationName();
			} else {	
				$client = $this->mClient; //$perationName
				$response = call_user_func_array(array(&$client, $operationName), $parameters);
				if(!is_array($response) && !is_object($response)){
					return array($response);
				}
			}
		} catch(Exception $e) {
			return print_r($e, true) . $this->mClient->__getLastResponse();
		}
		
		//construct xml-result if the old dot-syntax is not used
		global $smwgWSOldDotSyntax;
		if(!$smwgWSOldDotSyntax){
			$response = array($this->constructXML($response, $this->mOperations[$operationName][0]));
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

		$this->duplicates = array();
		
		//if xdebug is enabled, verify wsdl uri
		if(function_exists('xdebug_disable') && !$this->validateWSDLURI($this->mURI)){
			return false;
		}
		
		$options = array('exceptions' => true);
		if($this->mAuthenticationType == 'http'){
			$options['login'] = $this->mAuthenticationLogin;
			$options['password'] = $this->mAuthenticationPassword;
		}
				
		try {
			$this->mClient = new SoapClient($this->mURI, $options);
			
			$functions = $this->mClient->__getFunctions();
			
			$types = $this->mClient->__getTypes();
		} catch (Exception $e) {
			//print_r($e);
			return false;
		}
		
		foreach ($functions as $f) {
			if (preg_match("/\s*(.+?)\s(.+?)\s*\((.*?)\)/", $f, $matches)) {
				$retType = $matches[1];
				$fname = $matches[2];
				$params = $matches[3];
				if(!array_key_exists($fname, $this->mOperations)){
					$this->mOperations[$fname] = array($retType);
					if ($params) {
						$numParam = preg_match_all("/\s*(.+?)\s+\\$([^ ,]+)(\s|,)*/",$params, $pList);
						for ($i = 0; $i < $numParam; ++$i) {
							$this->mOperations[$fname][] = array($pList[2][$i], $pList[1][$i]);
						}
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
				$add = true;
				if(array_key_exists($tname, $this->mTypes)){
					$this->duplicates[$tname] = $tname;
				}
				for ($i = 0; $i < $numFields; ++$i) {
					$this->mTypes[$tname][$fList[2][$i]] = $fList[1][$i];
				}
			}
		}

		foreach($this->duplicates as $duplicate){
			$temp = $this->mTypes[$duplicate];
			unset($this->mTypes[$duplicate]);
			$duplicate = $duplicate."##duplicate";
			$this->mTypes[$duplicate] = $temp;
		}

		foreach($this->mTypes as $typeN => $typeD){
			foreach($typeD as $typeName => $typeDef){
				if(array_key_exists($typeDef, $this->duplicates)){
					//$temp = $this->mTypes[$typeN][$typeName];
					unset($this->mTypes[$typeN][$typeName]);
					//$typeName = $typeName."##duplicate";
					$this->mTypes[$typeN][$typeName."##duplicate"] = $typeDef."##duplicate";
				}
			}
		}


		//		$this->mTypes = array();
		//		foreach ($types as $t) {
		//			if (preg_match("/\s*struct\s*(\b.*?)\s*\{([^}]*)\}/", $t, $matches)) {
		//				$tname = $matches[1];
		//				$fields = $matches[2];
		//				$numFields = preg_match_all("/\s*(\b.*?)\s+([^;]*);/",$fields, $fList);
		//				$add = true;
		//				if($this->mTypes[$tname] || count(array_keys($this->duplicates, $tname)) > 0){
		//					if(!$this->duplicates[$tname]){
		//						$this->duplicates[$tname."##".count($this->duplicates)] = $tname;
		//						$temp = $this->mTypes[$tname];
		//						unset($this->mTypes[$tname]);
		//						$tname = $tname."##".(count($this->duplicates)-1);
		//						$this->mTypes[$tname] = $temp;
		//					} else {
		//						foreach(array_keys($this->duplicates, $tname) as $dupType){
		//							$found = true;
		//							if($numFields != count($this->mTypes[$dupType])){
		//								$found = false;
		//							} else {
		//								for ($i = 0; $i < $numFields; ++$i) {
		//									if($this->mTypes[$dupType][$fList[2][$i]] != $fList[1][$i]){
		//										$found = false;
		//										break;
		//									}
		//								}
		//							}
		//							if($found){
		//								$add = false;
		//								break;
		//							}
		//						}
		//						if($add){
		//							$this->duplicates[$tname."##".count($this->duplicates)] = $tname;
		//							$tname = $tname."##".(count($this->duplicates)-1);
		//						}
		//					}
		//				}
		//				if($add){
		//					for ($i = 0; $i < $numFields; ++$i) {
		//						$this->mTypes[$tname][$fList[2][$i]] = $fList[1][$i];
		//					}
		//				}
		//			}
		//
		//		}

		//		$this->wsdl = new SimpleXMLElement($this->mURI, null, true);
		//
		//		$namespaces = $this->wsdl->getNamespaces(true);
		//		foreach($namespaces as $prefix => $ns){
		//			$this->wsdl->registerXPathNamespace($prefix, $ns);
		//			if($ns == "http://www.w3.org/2001/XMLSchema"){
		//				$this->xs = $prefix;
		//			}
		//		}

		//		foreach(array_unique(array_values($this->duplicates)) as $duplicate){
		//			$this->detectTypeRelations($duplicate);
		//		}

		return true;
	}

	/**
	 * This methods updates the type information of types
	 * that have been found several times in the wsdl
	 *
	 * @param string $typeName
	 */
	private function detectTypeRelations($typeName){
		$unprocessedTypes = array();

		$dupTypes = array_keys($this->duplicates, $typeName);
		if(count($dupTypes) > 0){
			foreach($dupTypes as $dupType){

				//echo("get:: ".$typeName." :: ".$dupType ."\n");
				$parents = $this->getParentsOfDuplicate($typeName, $dupType);

				if(count($parents) == 0){
					$unprocessedTypes[] = $dupType;
					continue;
				}

				foreach($parents as $parent){
					if(strlen($parent["name"]) > 0){
						$parentName = substr($parent["name"], 0);
						if(count(array_keys($this->duplicates, $parentName)) > 1){
							//echo("updateDuplicateParent: ".$parentName." :: ". $dupType." :: ".$typeName."\n");
							$this->updateDuplicateParent($parent, $dupType, $typeName);
						} else {
							//echo("updated parent: ".$parentName." :: ".$typeName." :: ".$dupType."\n");
							$temp = $this->mTypes[$parentName][$typeName];
							unset($this->mTypes[$parentName][$typeName]);
							$this->mTypes[$parentName][$dupType] = $temp;
						}
					}
				}

			}
		}

		foreach($unprocessedTypes as $uType){
			//echo("unprocessed: ".$uType."\n");
			$temp = $this->mTypes[$uType];
			unset($this->mTypes[$uType]);
			$uType = substr($uType, 0, strrpos($uType, "##"));
			$this->mTypes[$uType] = $temp;
		}
	}

	/**
	 * this method finds the parents of a type
	 *
	 * @param string $typeName : the original type name
	 * @param string $dupType : the new type name
	 * @return array of SimpleXMLType
	 */
	private function getParentsOfDuplicate($typeName, $dupType){
		$xPathE = "//".$this->xs.":element[./".$this->xs.":complexType/".$this->xs.":sequence/".$this->xs.":element[@name=\"".$typeName."\"";
		$xPathC = "//".$this->xs.":complexType[./".$this->xs.":sequence/".$this->xs.":element[@name=\"".$typeName."\"";
		foreach(array_keys($this->mTypes[$dupType]) as $child){
			$xPathE.= " and ./".$this->xs.":complexType/".$this->xs.":sequence/".$this->xs.":element[@name=\"".$child."\"]";
			$xPathC.= " and ./".$this->xs.":complexType/".$this->xs.":sequence/".$this->xs.":element[@name=\"".$child."\"]";
		}
		$xPathE.= " ]]";
		$xPathC.= " ]]";

		$parentsE = $this->wsdl->xpath($xPathE);
		$parentsC = $this->wsdl->xpath($xPathC);

		if(!$parentsE){
			$parentsE = array();
		}
		if(!$parentsC){
			$parentsC = array();
		}

		$parents = array_merge($parentsE, $parentsC);

		return $parents;
	}

	/**
	 * This method finds the corresponding parent of a type and updates
	 * the field information of the parent
	 *
	 * @param SimpleXMLElement_$parent
	 * @param string $dupType
	 * @param string $typeName
	 */
	private function updateDuplicateParent($parent, $dupType, $typeName){
		$parentName = substr($parent["name"], 0);
		@$parent = new SimpleXMLElement($parent->asXML());
		$childTypes = array();
		foreach($parent->children() as $c1){
			foreach($c1->children() as $c2){
				if($c2["ref"]){
					$childTypes[] = substr($c2["ref"], 0);
				} else if($c2["type"]){
					$childTypes[] = substr($c2["type"], 0);
				} else if($c2["name"]){
					$childTypes[] = substr($c2["name"], 0);
				}
				foreach($c2->children() as $c3){
					if($c3["ref"]){
						$childTypes[] = substr($c3["ref"], 0);
					} else if($c3["type"]){
						$childTypes[] = substr($c3["type"], 0);
					} else if($c3["name"]){
						$childTypes[] = substr($c3["name"], 0);
					}
				}
			}
		}

		foreach(array_keys($this->duplicates, $parentName) as $dup){
			$add = true;
			if(count($this->mTypes[$dup]) == count($childTypes)){
				foreach($childTypes as $cType){
					if(!$this->mTypes[$dup][$cType]){
						$add = false;
					}
				}
			} else {
				$add = false;
			}
			if($add){
				$temp = $this->mTypes[$dup][$typeName];
				unset($this->mTypes[$dup][$typeName]);
				$this->mTypes[$dup][$dupType] = $temp;
			}
		}
	}

	/**
	 * Checks if the given type is defined in the wsdl
	 *
	 * @param string $typeName
	 * @return boolean
	 */
	public function isExistingType($typeName){
		if(array_key_exists($typeName, $this->mTypes)){
			return(true);
		}
		return false;
	}

	private function constructXML($result, $tagName){
		$xmlString = "";
		if(is_array($result) || is_object($result)){
			if(!$this->is_vector($result)){
				foreach($result as $k=>$v){
					$xmlString .= $this->constructXML($v, $tagName);
				}
			} else {
				$xmlString .= "<".$tagName.">";
				foreach($result as $k=>$v){
					$xmlString .= $this->constructXML($v, $k);
				}
				$xmlString .= "</".$tagName.">";
			}
		} else {
			$result = str_replace("]]>", "####CDATAEND####", $result);
			$xmlString = $xmlString .= "<".$tagName."><![CDATA[".$result."]]></".$tagName.">";;
		}
		return $xmlString;
	}

	private function is_vector( &$array ) {
		if (empty($array) ) {
			return false;
		}
		$next = 0;
		foreach ( $array as $k => $v ) {
			if ( $k !== $next ) return true;
			$next++;
		}
		return false;
	}
	
	public function getURI(){
		return $this->mURI;
	}
	
	/*
	 * Verify if a valid wsdl uri was given. Required if xdebug
	 * is enabled.
	 */
	private function validateWSDLURI($uri){
		
		//todo: implement a better validation method, thsi one does not work with the bzc
		return true;
		
		if($this->mAuthenticationType == "http"){
			$protocol = substr($uri, 0, strpos($uri, "://") +3 );
			$host = substr($uri, strpos($uri, "://") +3 );
			$uri = $protocol.$this->mAuthenticationLogin.":".$this->mAuthenticationPassword."@".$host;
		}
		
		$params = array('http' => array('method' => 'GET'));
		
		$ctx = stream_context_create($params);

		$fp = @ fopen($uri, 'rb', true, $ctx);
		
		if (!$fp) {
			return false;
		}

		$response = stream_get_contents($fp);
		
		try{		
			@ $xml = new SimpleXMLElement($response);
		} catch (Exception $e){
			return false;
		}

		return true;
	}
}











