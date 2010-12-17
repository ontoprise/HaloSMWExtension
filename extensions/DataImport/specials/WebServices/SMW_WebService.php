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
 * @author Thomas Schweitzer, Ingo Steinbauer
 */

/**
 * This group contains all parts of the Data Import extension that deal with calling external web services.
 * @defgroup DIWebServices
 * @ingroup DataImport
 */



if ( !defined( 'MEDIAWIKI' ) ) die;
global $smwgDIIP;
require_once("$smwgDIIP/specials/WebServices/SMW_WSStorage.php");
require_once("$smwgDIIP/specials/WebServices/SMW_IWebServiceClient.php");
require_once("$smwgDIIP/specials/WebServices/SMW_XPathProcessor.php");
require_once("$smwgDIIP/specials/WebServices/SMW_JSONProcessor.php");
require_once("$smwgDIIP/specials/WebServices/SMW_RDFProcessor.php");
require_once("$smwgDIIP/specials/WebServices/SMW_SubParameterProcessor.php");


//some special parameters and result parts
define('DI_URL_SUFFIX', '__url__suffix');
define('DI_ACCEPT', '__accept__content');
define('DI_POST_SEPARATOR', '__post__separator');
define('DI_SUBJECT', '__subject');
define('DI_SUBJECT_ALIAS', 'subject');
define('DI_URL_SUFFIX_ALIAS', 'url-suffix');
define("DI_ALL_SUBJECTS", "__triple__subjects");
define("DI_ALL_PROPERTIES", "__triple__properties");
define("DI_ALL_OBJECTS", "__triple__objects");
define("DI_ALL_SUBJECTS_ALIAS", "allSubjects");
define("DI_ALL_PROPERTIES_ALIAS", "allProperties");
define("DI_ALL_OBJECTS_ALIAS", "allObjects");
define("DI_RDF_POSTPROCESS_REQUIRED", "postprocess required");
define("DI_PROPERTIES", "__properties");
define("DI_PROPERTIES_ALIAS", "properties");
define("DI_LANGUAGE", "__language");
define("DI_LANGUAGE_ALIAS", "language");




/**
 * Instances of this class describe a web service.
 *
 * @author Thomas Schweitzer
 *
 */
class WebService {

	//--- Private fields ---
	private $mArticleID = 0;  //int: page ID of the service's WWSD
	private $mName;           //string: name of the web service in the wiki without namespace
	private $mURI;            //string: URI of the web service
	private $mProtocol;       //string: SOAP or REST
	private $mMethod;         //string: name of the method in the service's WSDL
	private $mAuthenticationType; //string "HTTP" or an empty string if no auth is required
	private $mAuthenticationLogin; //string login name or an empty string if no auth is required
	private $mAuthenticationPassword;//string: password or an empty string if no auth is required
	private $mParameters;     //string: description of the parameter structure
	private $mParsedParameters; // The parsed structure of the parameter description
	// in form of nested arrays
	private $mResult;         //string: description of the result structure
	private $mParsedResult;   // The parsed structure of the result description
	// as array if SimpleXMLElements
	private $mDisplayPolicy;  //int: display update policy in minutes. 0 means only once.
	private $mQueryPolicy;    //int: query update policy in minutes. 0 means only once.
	private $mUpdateDelay;    //int: update delay for the query policy in seconds
	private $mSpanOfLife;     //int: span of life in days. 0 means forever.
	private $mExpiresAfterUpdate; //bool: <true> if the span of life starts to expire
	// after an update. Otherwise it starts after the
	// last access.
	private $mConfirmationStatus;      //bool: confirmed by sysop (true) or not (false)

	private $mWSClient;		  //IWebServiceClient: the web service client provides
	//   access to the service
	private $mCallParameters;
	// for memorizing errors occuring during ws-call
	
	private $responseContentType = ''; //for temporarily storing the ws response content type, which is required for RDF parsing  
	
	/**
	 * Constructor for new WebService objects.
	 *
	 * @param string $name
	 * 		Name of the web service in the wiki without namespace
	 * @param string $uri
	 * 		URI of the web service
	 * @param string $protcol
	 * 		SOAP or REST
	 * @param string $method
	 * 		Name of the method in the service's WSDL
	 * @param string authenticationType
	 * 		HTTP or an empty string if no authentication is required
	 * @param string authenticationLogin
	 * 		login name or an empty string if no authentication is required
	 * @param string authenticationPassword
	 * 		password or an empty string if no authentication is required
	 * @param string $parameters
	 * 		Description of the parameter structure
	 * @param string $result
	 * 		Description of the result structure
	 * @param int $dp
	 * 		Display update policy in minutes. 0 means only once.
	 * @param int $qp
	 * 		Query update policy in minutes. 0 means only once.
	 * @param int $updateDelay
	 * 		Update delay for query policy in seconds
	 * @param int $sol
	 * 		Span of life in days. 0 means forever.
	 * @param bool $expiresAfterUpdate
	 * 		If <true>, the span of life begins to expire immediately after an
	 *      update. Otherwise it begins after the last access.
	 * @param bool $confirmed
	 * 		Confirmed by sysop (true) or not (false)
	 */
	function __construct($id = 0, $uri = "", $protcol = "", $method = "",
	$mAuthenticationType = "", $mAuthenticationLogin = "", $mAuthenticationPassword = "",
	$parameters = "", $result = "",
	$dp = 0, $qp = 0, $updateDelay = 0, $sol = 0,
	$expiresAfterUpdate = false, $confirmed = false) {
		$this->mArticleID = $id;
		$this->mName = Title::nameOf($id);
		$this->mURI = $uri;
		$this->mProtocol = $protcol;
		$this->mMethod = $method;
		$this->mAuthenticationType = $mAuthenticationType;
		$this->mAuthenticationLogin = $mAuthenticationLogin;
		$this->mAuthenticationPassword = $mAuthenticationPassword;

		//Add special purpose parameters, if the WS is a LD source. Those special
		//purpose parameters are available to each LD WS. They need not to be
		//specified in the WWSD
		//Please note, the same is done in newFromWWSD
		if(strtolower($this->mProtocol) == "linkeddata"){
			//$parameters .= '<parameter name="'.DI_URL_SUFFIX_ALIAS.'" path="'.DI_URL_SUFFIX.'" optional="true"/>';
			//$parameters .= '<parameter name="'.DI_SUBJECT_ALIAS.'" path="'.DI_SUBJECT.'" optional="true"/>';
		}

		$this->mParameters = $parameters;
		try {
			if(empty($parameters)){
				$this->mParsedParameters = null;
			} else {
				$this->mParsedParameters = new SimpleXMLElement("<p>".$parameters."</p>");
			}

			$this->mResult = $result;
			if (empty($result)) {
				$this->mParsedResult = null;
			} else {
				if(strpos($result, "^") > -1){
					$this->error();
				}
				$this->mParsedResult = new SimpleXMLElement("<res>".$result."</res>");
				$r = array();
				foreach ($this->mParsedResult->result as $rdef) {
					$r[] = $rdef;
				}
				$this->mParsedResult = $r;
			}
		} catch (Exception $e) {
		}
		$this->mDisplayPolicy = $dp;
		$this->mQueryPolicy = $qp;
		$this->mUpdateDelay = $updateDelay;
		$this->mSpanOfLife = $sol;
		$this->mExpiresAfterUpdate = $expiresAfterUpdate;
		$this->mConfirmationStatus = $confirmed;
	}


	//--- getter/setter ---
	public function getName()          {return $this->mName;}
	public function getURI()           {return $this->mURI;}
	public function getProtocol()      {return $this->mProtocol;}
	public function getMethod()        {return $this->mMethod;}
	public function getAuthenticationType()		{return $this->mAuthenticationType;}
	public function getAuthenticationLogin()		{return $this->mAuthenticationLogin;}
	public function getAuthenticationPassword()	{return $this->mAuthenticationPassword;}
	public function getParameters()    {return $this->mParameters;}
	public function getResult()        {return $this->mResult;}
	public function getDisplayPolicy() {return $this->mDisplayPolicy;}
	public function getQueryPolicy()   {return $this->mQueryPolicy;}
	public function getUpdateDelay()   {return $this->mUpdateDelay;}
	public function getSpanOfLife()    {return $this->mSpanOfLife;}
	public function doesExpireAfterUpdate()   {return $this->mExpiresAfterUpdate;}
	public function getConfirmationStatus()      {return $this->mConfirmationStatus;}

	//--- Public methods ---


	/**
	 * Creates a new instance of a WebService object that is stored in the
	 * database with the specified name.
	 *
	 * @param string $name
	 * 		The unique name of the web service i.e. article	that contains the
	 *      WWSD of the web service (without the namespace).
	 *
	 * @return WebService
	 * 		If the web service exists in the database, a new object is created
	 * 		and initialized with the database values. Otherwise <null> is returned.
	 */
	public static function newFromName($name) {
		$t = Title::makeTitleSafe(SMW_NS_WEB_SERVICE, $name);

		if($t == null){
			return null;
		}

		while($t->isRedirect()){
			$article = new Article($t);
			$t = $article->getRedirectTarget();
			if($t == null){
				return null;
			}
		}

		$id = $t->getArticleID();
		return ($id < 0) ? null : WSStorage::getDatabase()->getWS($id);
	}

	/**
	 * Creates a new instance of a WebService object that is stored in the
	 * database with the given page ID.
	 *
	 * @param int $id
	 * 		The database ID of the web service i.e. the page ID of the article
	 *      that contains the WWSD of the web service.
	 *
	 * @return WebService
	 * 		If the web service exists in the database a new object is created
	 * 		and initialized with the database values. Otherwise <null> is returned.
	 */
	public static function newFromID($id) {
		$ws = WSStorage::getDatabase()->getWS($id);
		if ($ws) {
			$ws->mArticleID = $id;
		}
		return $ws;
	}

	/**
	 * Creates a new instance of a WebService from the given wiki web service
	 * description <$wwsd>.
	 *
	 * @param string $name
	 * 		The name of the web service (without namespace).
	 * @param string $wwsd
	 * 		This wiki web service definition is parsed and its values are
	 * 		stored in the fields of a new object.
	 *
	 * @return mixed WebService/string
	 * 		A new instance of WebService or
	 * 		an array of error messages, if parsing of the WWSD failed.
	 */
	public static function newFromWWSD($name, $wwsd) {
		global $smwgDIIP;

		try {
			$parser = @ new SimpleXMLElement($wwsd);
		} catch (Exception $e) {
			$msg = $e->getMessage();
			return array($msg);
		}

		// Check if the roor node of the XML structure is 'webservice'
		$rootName = $parser->getName();
		if ($rootName !== 'WebService') {
			return wfMsg('smw_wws_invalid_wwsd');
		}

		$msg = array();
		$valid = true;

		$ws = new WebService();
		$ws->mName = $name;
		$valid &= self::getWWSDElement($parser, '/WebService/protocol', null, $ws->mProtocol, false, 1, 1, $msg);

		//RESTful WSs and Linked Data sources need not define a URI
		$tempMSG = array();
		$tempValid = self::getWWSDElement($parser, '/WebService/uri', 'name', $ws->mURI, false, 1, 1, $tempMSG);
		if(strtolower($ws->mProtocol) == "rest" || strtolower($ws->mProtocol) == "linkeddata"){
			if(!$tempValid){
				$ws->mURI = "";
			}
		} else {
			$valid &= $tempValid;
			$msg = array_merge($msg, $tempMSG);
		}
		$valid &= self::getWWSDElement($parser, '/WebService/method', 'name', $ws->mMethod, false, 1, 1, $msg);

		
		$ignoreMsg = array();
		if(self::getWWSDElement($parser, '/WebService/authentication', null, $ignore, false, 1, 1, $ignoreMsg)){
			$valid &= self::getWWSDElement($parser, '/WebService/authentication', 'type', $ws->mAuthenticationType, false, 1, 1, $msg);
			$valid &= self::getWWSDElement($parser, '/WebService/authentication', 'login', $ws->mAuthenticationLogin, false, 1, 1, $msg);
			$valid &= self::getWWSDElement($parser, '/WebService/authentication', 'password', $ws->mAuthenticationPassword, false, 1, 1, $msg);
		}

		$exists = self::getWWSDElement($parser, '/WebService/parameter', null, $ws->mParsedParameters, false, 1, 100, $msg);
		if($exists){
			if ($ws->mParsedParameters) {
				// store the serialized form of the parameter description
				$path = $parser->xpath('/WebService/parameter');
				foreach ($path as $p) {
					$ws->mParameters .= $p->asXML()."\n";
				}
			}
		} else {
			$ws->mParsedParameters = '';
		}
		//add special purpose parameters for ld protocol
		if(strtolower($ws->mProtocol) == "linkeddata"){
			$ws->mParameters .= '<parameter name="'.DI_URL_SUFFIX_ALIAS.'" path="'.DI_URL_SUFFIX.'" optional="true"/>';
			$ws->mParameters .= '<parameter name="'.DI_SUBJECT_ALIAS.'" path="'.DI_SUBJECT.'" optional="true"/>';
			$ws->mParameters .= '<parameter name="'.DI_PROPERTIES_ALIAS.'" path="'.DI_PROPERTIES.'" optional="true"/>';
			$ws->mParameters .= '<parameter name="'.DI_LANGUAGE_ALIAS.'" path="'.DI_LANGUAGE.'" optional="true"/>';
		}

		// check if indexes used in parameter-paths that contain arrays
		// are valid
		if($ws->mParsedParameters){
			$aParamPaths = array();

			foreach($ws->mParsedParameters as $child){
				$aParamPaths[] = "".$child["path"];
			}
			//this is deprecated because XPath statements are now used instead
			//$valid &= $ws->checkParameterArrayIndexes($aParamPaths, $msg);
			//$ws->checkParameterArrayIndexes($aParamPaths, $msg);
		}

		$valid &= self::getWWSDElement($parser, '/WebService/result', null, $ws->mParsedResult, false, 1, 100, $msg);
		if ($ws->mParsedResult) {
			// store the serialized form of the result description
			$path = $parser->xpath('/WebService/result');
			foreach ($path as $p) {
				$ws->mResult .= $p->asXML()."\n";
			}
		}

		//Add special purpose result parts for ld protocol
		if(strtolower($ws->mProtocol) == "linkeddata"){
			$ws->mResult = str_replace("</result>", "", $ws->mResult);
			$ws->mResult .= '<part name="'.DI_ALL_SUBJECTS_ALIAS.'" property="'.DI_ALL_SUBJECTS.'"/>';
			$ws->mResult .= '<part name="'.DI_ALL_PROPERTIES_ALIAS.'" property="'.DI_ALL_PROPERTIES.'"/>';
			$ws->mResult .= '<part name="'.DI_ALL_OBJECTS_ALIAS.'" property="'.DI_ALL_OBJECTS.'"/>';
			$ws->mResult .= "</result>";
		}

		$tmpMsg = array();
		$v = self::getWWSDElement($parser, '/WebService/displayPolicy/once', null, $temp, false, 1, 1, $tmpMsg);
		if ($v) {
			$ws->mDisplayPolicy = 0;
		} else {
			$valid &= self::getWWSDElement($parser, '/WebService/displayPolicy/maxAge', 'value', $ws->mDisplayPolicy, true, 1, 1, $msg);
		}

		$v = self::getWWSDElement($parser, '/WebService/queryPolicy/once', null, $temp, false, 1, 1, $tmpMsg);
		if ($v) {
			$ws->mQueryPolicy = 0;
		} else {
			$valid &= self::getWWSDElement($parser, '/WebService/queryPolicy/maxAge', 'value', $ws->mQueryPolicy, true, 1, 1, $msg);
		}
		$v = self::getWWSDElement($parser, '/WebService/queryPolicy/delay', 'value', $ws->mUpdateDelay, true, 1, 1, $tmpMsg);
		if (!$v) {
			// The update delay is optional. Its default value is 0.
			$ws->mUpdateDelay = 0;
		}

		$valid &= self::getWWSDElement($parser, '/WebService/spanOfLife', 'value', $ws->mSpanOfLife, false, 1, 1, $msg);
		if ($valid) {
			if (strtolower($ws->mSpanOfLife) == 'forever') {
				$ws->mSpanOfLife = 0;
			} else {
				$ws->mSpanOfLife = intval($ws->mSpanOfLife);
				//$ws->mExpiresAfterUpdate =
			}
		}
		$ws->mExpiresAfterUpdate = "true";

		$ws->mConfirmationStatus = "false";
		$v = self::getWWSDElement($parser, '/WebService/spanOfLife', 'expiresAfterUpdate', $ws->mExpiresAfterUpdate, false, 1, 1, $msg);

		return ($valid) ? $ws : $msg;
	}

	/**
	 * Returns the article ID of this WebService i.e. the page ID of the article
	 * that contains the service's WWSD.
	 *
	 * @return int
	 * 		The article ID of the web service or
	 * 		0, if the article does not exist.
	 *
	 */
	public function getArticleID() {
		if ($this->mArticleID == 0) {
			$t = Title::makeTitleSafe(SMW_NS_WEB_SERVICE, $this->mName);
			$this->mArticleID = ($t) ? $t->getArticleID() : 0;
		}
		return $this->mArticleID;
	}

	/**
	 * Stores the web service in the database. There must be a corresponding
	 * article that contains the service's WWSD.
	 *
	 * @return bool
	 * 	  <true>, if successful
	 *    <false>, otherwise
	 *
	 */
	public function store() {
		if ($this->mArticleID == 0) {
			$this->getArticleID();
		}
		return ($this->mArticleID == 0)
		? false
		: WSStorage::getDatabase()->storeWS($this);
	}

	/**
	 * Checks if the definition of the web service is valid with respect to the
	 * WSDL it refers to.
	 *
	 * @return mixed(boolean, array<string>)
	 * 		<true>, if the definition is correct or an
	 * 		array of error messages otherwise
	 *
	 */
	public function validateWWSD() {
		$msg = $this->createWSClient();
		
		if ($msg === true) {
			// client successfully created
			$msg = array();
		} else {
			return $msg[0];
		}
		
		// Check, if a valid method was chosen
		if(strtolower($this->mProtocol) == "soap"){
			$op = $this->mWSClient->getOperation($this->mMethod);
			if (!$op) {
				$msg[] = wfMsg('smw_wws_invalid_operation', $this->mMethod);
			}
		} else if (strtolower($this->mProtocol) == "rest" || strtolower($this->mProtocol) == "linkeddata"){
			if(strtolower($this->mMethod) != "get" && strtolower($this->mMethod) != "post"){
				$msg[] = wfMsg('smw_wws_invalid_operation', $this->mMethod);
			}
		}

		$res = $this->checkParameters();
		if (is_array($res)) {
			// result contains error messages
			$msg = array_merge($msg, $res);
		}

		$res = $this->checkResult();
		if (is_array($res)) {
			// result contains error messages
			$msg = array_merge($msg, $res);
		}

		return (count($msg) == 0 ? true : $msg);
	}

	/**
	 * calls the webservice and returns the
	 * webservice result. if an appropriate result
	 * allready exists in the cache, then the
	 * result is taken from the cache
	 *
	 * @param string $parameterSetId
	 * @param string $resultParts
	 * @return an array that contains the result
	 */
	public function call($parameterSetId, $resultParts) {
		
		$resultParts = array_keys($resultParts);
		
		//always get parameters although a cached web service result might be used
		//because some features of the result extraction might want to know about
		//special parameters like the Linked Data subject and so on.
		$specParameters = WSStorage::getDatabase()->getParameters($parameterSetId);
		
		$this->initializeCallParameters($specParameters);

		$cacheResult = WSStorage::getDatabase()->getResultFromCache($this->mArticleID, $parameterSetId);
		$response = null;

		//check if an appropriate result allready exists in the cache
		if($cacheResult != null){
			if(($this->mDisplayPolicy == 0) ||
					(wfTime() - wfTimestamp(TS_UNIX, $cacheResult["lastUpdate"])
					< ($this->getDisplayPolicy()*60))){
				$response = @ unserialize($cacheResult["result"]);
				
				WSStorage::getDatabase()->updateCacheLastAccess($this->mArticleID, $parameterSetId);
			}
		}
		
		// get the result from a call to a webservice if there
		// was no appropriate result in the cache
		if(!$response){
			if($this->getConfirmationStatus() == "once"){
				return wfMsg('smw_wws_need_confirmation');
			} else {
				$this->createWSClient();

				if($this->mWSClient){
					$response = $this->mWSClient->call($this->mMethod, $this->mCallParameters);
				} else {
					return "Could not connect to Web Service. Please check your Wiki Web Service Definition.";
				}

				if(is_string($response)){
					return $response;
				} else {
					//the content type which is required for RDF processing is stored together
					//with the cached WS result -> implement better solution
					if($this->isLinkedDataResource()){
						$response['http-content-type'] = $this->getWSClient()->getContentType(); 						
					}
					
					WSStorage::getDatabase()->storeCacheEntry(
						$this->mArticleID,
						$parameterSetId,
						serialize($response),
						wfTimeStamp(TS_MW, wfTime()),
						wfTimeStamp(TS_MW, wfTime()));
				}
			}
		}
		
		//the content-type of the ws result, which is important for RDF parsing,
		//is stored in the cached response. Todo: Implement s.th. more elegant
		if(array_key_exists('http-content-type', $response)){
			$this->responseContentType = $response['http-content-type']; 
			unset($response['http-content-type']);
		}

		$result = $this->getCallResultParts($response[0], $resultParts);
		
		$ws = $this->mArticleID;
		if($this->getConfirmationStatus() == "false"){
			$this->mConfirmationStatus = "once";
			WSStorage::getDatabase()->setWWSDConfirmationStatus($this->mArticleID, "once");
		}
		
		return $result;
	}


	/**
	 * This methods returns these parts of the ws-response
	 * that are of interrest
	 *
	 * @param $response the response of the web service call
	 * @param string[] $resultParts : aliases of the requested result parts
	 * @return array the result of interrest
	 */
	public function getCallResultParts($response, $resultParts){
		$results = array();
		
		$postProcess = array();

		//this variable is used, so that the web service result
		//needs not to be parsed over and over again for each
		//result part.
		$lastRootPart = "";
		
		foreach ($resultParts as $rp) {
			$parts = explode(".", $rp);
			$rdef = $this->getResultDefinition($parts[0]);

			//the lastRootPart condition is required, so that the web service
			//result is not parsed for each result part by the RDFProcessor
			if($lastRootPart != $parts[0]){
				//parse the web service result with the RDFProcessor
				
				$this->initializeLinkedDataResultPartExtraction($response, $rdef);
				$lastRootPart = $parts[0];
			}

			if (count($parts) == 1) { //complete result is requested
				foreach ($rdef->part as $part) {
					$part = ''.$part['name'];
					$results[$part] = $this->getResults($response, $rdef, $part);
					$tmpResult = $this->evaluateAdditionalPathAttribute(
					$rdef, $part, $results[$part]);
					if($tmpResult = DI_RDF_POSTPROCESS_REQUIRED){
						$postProcess[$part] = $this->getPropertyForAlias($part, $rdef);
					} else {
						$results[$part] = $tmpResult;
					}
				}
			} else {
				$parts[1] = strtolower($parts[1]);
				$results[$parts[1]] = $this->getResults($response, $rdef, $parts[1]);
				
				$tmpResult = $this->evaluateAdditionalPathAttribute(
					$rdef, $parts[1], $results[$parts[1]]);
				
				if($tmpResult == DI_RDF_POSTPROCESS_REQUIRED){
					$postProcess[$parts[1]] = $this->getPropertyForAlias($parts[1], $rdef);
				} else {
					$results[$parts[1]] = $tmpResult;
				}
			}
		}
		
		//deal with the propertys special purpose parameter
		if(array_key_exists(DI_PROPERTIES, $this->mCallParameters)){
			$this->initializeLinkedDataResultPartExtraction($response, $rdef);
			$propertys = explode(";", $this->mCallParameters[DI_PROPERTIES][0]);
			foreach($propertys as $p){
				$p = explode("=", $p, 2);
				$alias = $p[0];
				$property = $p[1];
				SMWRDFProcessor::getInstance()->preprocessProperty($property);
				$postProcess[$alias] = $property;
			}
		}

		if(count($postProcess) > 0){
			foreach ($postProcess as $alias => $property){
				$results[$alias] = SMWRDFProcessor::getInstance()->getFinalResult($property);
			}
		}


		return $results;
	}

	/**
	 * This method initializes the RDFParser
	 * if resulz parts which use property subpaths
	 * must be extracted from the web service result
	 *
	 * @param unknown_type $wsResponse
	 * @param unknown_type $resultDef
	 * @return unknown_type
	 */
	private function initializeLinkedDataResultPartExtraction($wsResponse, $resultDef){
		if($this->isLinkedDataResource()){
			$subject = "";
			if(array_key_exists(DI_SUBJECT, $this->mCallParameters)){
				$subject = $this->mCallParameters[DI_SUBJECT][0];
			}

			$language = "";
			if(array_key_exists(DI_LANGUAGE, $this->mCallParameters)){
				$language = $this->mCallParameters[DI_LANGUAGE][0];
			}

			SMWRDFProcessor::getInstance()->parse($this->getWSClient()->getURI(), $subject, $wsResponse,
				$this->responseContentType, $language);

			//setup namespace prefixes
			SMWRDFProcessor::getInstance()->setNamespacePrefixes($resultDef->namespace);


		}
	}

	/*
	 * Determine whether this WS connects to a Linked Data resource
	 */
	private function isLinkedDataResource(){
		//this is a LD resource if the LD protocol was chosen
		if(strtolower($this->mProtocol) == 'linkeddata'){
			return true;
		}

		//this is a LD resource if the REST protocol was chosen
		//and if the result part definitions contain property subpaths
		if(strtolower($this->mProtocol) == 'rest'){
			if(strpos($this->mResult, "property=") > 0) return true;
			if(strpos($this->mResult, "property =") > 0) return true;
		}

		return false;
	}

	private function evaluateAdditionalPathAttribute($rdef, $alias, $value){
		$xpath = $this->getXPathForAlias($alias, $rdef);
		$json = $this->getJSONForAlias($alias, $rdef);
		$property = $this->getPropertyForAlias($alias, $rdef);
		
		if($xpath != null){
			$newValue = array();
			foreach($value as $v){
				
				$xpathProcessor = new XPathProcessor($v);
				$newValue = array_merge($newValue, $xpathProcessor->evaluateQuery($xpath));
			}
			$value = $newValue;
		} else if ($json != null){
			$newValue = array();
			foreach($value as $v){
				$jsonProcessor = new JSONProcessor();
				$xmlString = $jsonProcessor->convertJSON2XML($v);
				$xpathProcessor = new XPathProcessor($xmlString);
				$newValue = array_merge($newValue, $xpathProcessor->evaluateQuery($json));
			}
			$value = $newValue;
		} else if ($property != null){
			$value = SMWRDFProcessor::getInstance()->preprocessProperty($property);
		}
		return $value;
	}


	/**
	 * Returns the results for the alias $alias of the result definition
	 * $resultDef based on the complete result set $response.
	 *
	 * @param Object $response
	 * @param SimpleXMLElement $resultDef
	 * @param string $alias
	 */
	private function getResults($response, $resultDef, $alias) {
		$path = $this->getPathForAlias($alias, $resultDef);

		if (empty($path) || $path == "//") {
			return array($response);
		}

		$xpathProcessor = new XPathProcessor($response);

		$xpR = $xpathProcessor->evaluateQuery($path);
		for($i=0; $i < count($xpR); $i++){
			$xpR[$i] = str_replace("####CDATAEND####", "]]>", $xpR[$i]);
		}
		return $xpR;
	}

	private function getPathForAlias($alias, $resultDef) {
		foreach ($resultDef->part as $part) {
			if (strtolower($alias) == strtolower(''.$part['name'])) {
				return ''.$part['path'];
			}
		}
		return null;

	}

	/**
	 * This methods prepares the parameters for the ws-call
	 *
	 * @param the parameters
	 * @return array the prepared parameters
	 */
	public function initializeCallParameters($specParameters){
		//init call-parameters with respect to default values
		$this->mCallParameters	= array();
		//this flag is necessary so that the SOAPClient later
		//can call the ws method with array_call_user_func
		$embedInArray = false;

		if($this->mParsedParameters != null){
			foreach($this->mParsedParameters->children() as $child){
				if(strrpos("".$child["path"], "/") > 0){
					$embedInArray = true;
				}
				$value = "".$child["defaultValue"];
				if(array_key_exists(strtolower("".$child["name"]), $specParameters)){
					$value = $specParameters[strtolower("".$child["name"])];
					if(strtolower($this->mProtocol) == "soap"){
						$this->getPathStepsSoap("".$child["path"], $value);
					} else {
						if(array_key_exists("".$child["path"], $this->mCallParameters)){
							$this->mCallParameters["".$child["path"]][] = $value;
						} else {
							$this->mCallParameters["".$child["path"]] = array($value);
						}
					}
				} else if (strtolower($this->mProtocol) == "soap") {
					$this->getPathStepsSoap("".$child["path"], $value);
				}else if("".$child["optional"] != "true"){
					$this->mCallParameters["".$child["path"]] = array($value);
				}
			}
		}

		if($embedInArray && strtolower($this->mProtocol) == "soap"){
			$this->mCallParameters = array($this->mCallParameters);
		}
		
		return $this->mCallParameters;
	}

	/**
	 * helper function for building the ws-call object
	 *
	 * @param string $path the path to the part of the ws-call object
	 * @param string $value the value of the call parameter with the given path
	 */
	private function getPathStepsSoap($path, $value){
		$walkedParameters = explode("/", $path);
		$temp = array();
		for($i=0; $i < count($walkedParameters);$i++){
			if($walkedParameters[$i] != ""){
				$temp[] = $walkedParameters[$i];
			}
		}
		$walkedParameters = $temp;

		$temp = &$this->mCallParameters;

		for($i=1; $i < sizeof($walkedParameters)-1; $i++){
			if($this->getReturnPartBracketValue($walkedParameters[$i]) === false){
				if(!array_key_exists($walkedParameters[$i], $temp)){
					$temp[$walkedParameters[$i]] = array();
				}
				$temp = &$temp[$walkedParameters[$i]];
			} else {
				if(!$temp[$this->getReturnPartPathStep($walkedParameters[$i])]){
					$temp[$this->getReturnPartPathStep($walkedParameters[$i])] = array();
				}

				$temp = &$temp[$this->getReturnPartPathStep($walkedParameters[$i])];
				if(!$temp[$this->getReturnPartBracketValue($walkedParameters[$i])]){
					$index = $this->getReturnPartBracketValue($walkedParameters[$i])*1;
					$temp[$index] = array();
					ksort($temp);
				}
				$temp = &$temp[$this->getReturnPartBracketValue($walkedParameters[$i])];
			}

		}

		$temp[$walkedParameters[sizeof($walkedParameters)-1]] = $value;

	}

	//--- Private methods ---

	/**
	 * Gets an element from the WWSD and assigns it to a variable that is passed
	 * by reference.
	 *
	 * @param array $wwsd
	 * 		The parsed WWSD structure
	 * @param string $wwsdElementPath
	 * 		Path to the WWSD element e.g. 'webservice/method'
	 * @param string $attribute
	 * 	 	Name of an attribute in the WWSD element. If <null>, the content
	 * 		of the element is assigned.
	 * @param mixed $variable
	 * 		The content of the WWSD element is assigned to this variable.
	 * @param bool $isNumeric
	 * 		<true>, if the value is a numeric
	 * @param int $min
	 * 		Minimal number of occurrences of the element
	 * @param int $max
	 * 		Maximal number of occurrences of the element
	 * @param array<string> $msg
	 * 		If the element is erroneous, an error message is added to this array.
	 *
	 * @return bool
	 * 		<true>, if the requested WWSD element could be retrieved without an
	 * 			    error
	 * 		<false> otherwise.
	 */
	private static function getWWSDElement(&$wwsd, $wwsdElementPath,
	$attribute, &$variable, $isNumeric,
	$min, $max, &$msg) {

		$subTree = $wwsd->xpath($wwsdElementPath);
		if (!$subTree) {
			$msg[] = wfMsg('smw_wws_wwsd_element_missing', $wwsdElementPath).'<br />';
			return false;
		}

		if (count($subTree) < $min) {
			$msg[] = wfMsg('smw_wws_wwsd_element_missing', $wwsdElementPath).'<br />';
			return false;
		}
		if (count($subTree) > $max) {
			$msg[] = wfMsg('smw_wws_too_many_wwsd_elements', $wwsdElementPath).'<br />';
			return false;
		}

		if ($min == 1 && $max == 1) {
			// exactly one element is expected and present
			if ($attribute) {
				$val = (string) $subTree[0]->attributes()->$attribute;
				if ($val == null) {
					$msg[] = wfMsg('smw_wws_wwsd_attribute_missing', $attribute, $wwsdElementPath).'<br />';
					return false;
				}
			} else {
				$val = (string) $subTree[0]."";
			}
			if ($val && $isNumeric) {
				$val = floatval($val);
			}
			$variable = $val;
		} else {
			// element appears several times
			$variable = $subTree;
		}
		return true;
	}

	/**
	 * Creates the web service client according to the protocol of this WebService
	 * object.
	 *
	 * @return mixed (bool/array<string>)
	 * 		<true>, if successfull or an
	 * 		array of error messages otherwise
	 *
	 */
	private function createWSClient() {
		// include the correct client
		global $smwgDIIP;

		$protocol = strtoupper($this->mProtocol);
		if(!$this->mWSClient){
			try {
				include_once($smwgDIIP . "/specials/WebServices/SMW_".
					$protocol."Client.php");
				
				$classname = "SMW".ucfirst(strtolower($protocol))."Client";
				
				if (!class_exists($classname)) {
					return array(wfMsg("smw_wws_invalid_protocol"));
				}
				
				$this->mWSClient = new $classname($this->mURI, $this->mAuthenticationType,
					$this->mAuthenticationLogin, $this->mAuthenticationPassword);
			} catch (Exception $e) {
				// The wwsd is erroneous
				$this->mWSClient = null;
				return array(wfMsg("smw_wws_invalid_wwsd"));
			}
		}
		return true;
	}

	/**
	 * Checks if the parameters that are defined in the WWSD are compatible with
	 * the parameters in the WSDL. This comprises:
	 * - Does the WWSD contain parameters without name?
	 * - Does the WWSD contain parameters without path?
	 * - Does the WWSD contain several parameters with the same name?
	 * - Does a type of the WSDL cause an overflow (e.g. struct List { next: List}) ?
	 * - Is there a definition for each parameter of the WSDL?
	 * - Are the obsolete parameters in the definition?
	 *
	 * @return mixed(boolean, array<string>)
	 * 		<true>, if everything is correct or an
	 * 		array of error messages otherwise
	 *
	 */
	private function checkParameters() {
		// check if there are duplicate parameters in the wwsd
		$msg = array();

		$pNames = array();
		$wwsdPaths = array();
		if($this->mParsedParameters == null){
			$this->mParsedParameters = array();
		}
		foreach ($this->mParsedParameters as $p) {
			$name = (string) $p->attributes()->name;
			$path = (string) $p->attributes()->path;
			if ($name == null) {
				// parameter has no name
				$msg[] = wfMsg('smw_wws_parameter_without_name');
				continue;
			}
			if ($path == null) {
				// parameter has no path
				$msg[] = wfMsg('smw_wws_parameter_without_path', $name);
				continue;
			}
			if (array_key_exists($name, $pNames)) {
				if ($pNames[$name]++ == 1) {
					$msg[] = wfMsg('smw_wws_duplicate_parameter', $name);
				}
				continue;
			} else {
				$pNames[$name] = 1;
				$wwsdPaths[$name] = $path;
			}
		}

		// Check if there is an alias for every parameter of the WSDL.
		// $wsdlParams = $this->mWSClient->getOperation($this->mMethod);
		// if ($wsdlParams != null) {
		// examine parameters
		// $names = array();
		// Collect the components of all parameters
		// $numParam = count($wsdlParams);
		// for ($i = 1; $i < $numParam; ++$i) {
		//	$pName = $wsdlParams[$i][0];
		//	$pType = $wsdlParams[$i][1];
		//	$names = array_merge($names, $this->flattenParam($pName, $pType));
		//$names = array_merge($names, $this->getFlatParameters($pName, $pType, false));
		//}
		// find elements that lead to overflows (e.g. potentially endless lists)
		//foreach ($names as $idx=>$name) {
		//	$pos = strpos($name, '##overflow##');
		//	if ($pos) {
		//		$msg[] = wfMsg('smw_wwsd_overflow', substr($name, 0, $pos));
		//		unset($names[$idx]);
		//	}
		//}
		// find undefined parameters
		//foreach($wwsdPaths as $key => $path){
		//	$pathSteps = explode(".", $path);
		//	for($z=0; $z < sizeof($pathSteps); $z++){
		//		if(!($this->getReturnPartBracketValue($pathSteps[$z]) === false)){
		//			$pathSteps[$z] = $this->getReturnPartPathStep($pathSteps[$z])."[]";
		//		}
		//	}
		//	$wwsdPaths[$key] = implode(".", $pathSteps);
		//}
		//$inWsdl = array_diff($names, $wwsdPaths);
		//foreach ($inWsdl as $p) {
		//	$msg[] = wfMsg('smw_wwsd_undefined_param', $p);
		//}
		// find obsolete parameters
		//$inWwsd = array_diff($wwsdPaths, $names);
		//foreach ($inWwsd as $p) {
		//	$p = array_search($p, $wwsdPaths);
		//	$msg[] = wfMsg('smw_wwsd_obsolete_param', $p);
		//}
		//}
		return count($msg) == 0 ? true : $msg;
	}

	/**
	 * Checks if the results that are defined in the WWSD are compatible with
	 * the result in the WSDL. This comprises:
	 * - Does the WWSD contain results without name?
	 * - Does the WWSD contain result parts without path?
	 * - Does the WWSD contain several results with the same name?
	 * - Does a type of the WSDL cause an overflow (e.g. struct List { next: List}) ?
	 * - Is there a definition for each result of the WSDL?
	 *
	 * @return mixed(boolean, array<string>)
	 * 		<true>, if everything is correct or an
	 * 		array of error messages otherwise
	 *
	 */
	private function checkResult() {
		// check if there are duplicate results in the wwsd
		$msg = array();

		$rNames = array(); // Names of results
		$wwsdPaths = array();
		foreach ($this->mParsedResult as $r) {
			$rName = (string) $r->attributes()->name;
			if ($rName == null) {
				// result has no name
				$msg[] = wfMsg('smw_wws_result_without_name');
				continue;
			}

			//fro validating triplification instructions
			$rememberedSubject = false;

			// Check all parts of a result
			$pNames = array();
			foreach ($r->children() as $part) {
				if ($part->getName() == 'part') {
					$pName = (string) $part->attributes()->name;
					if ($pName == null) {
						// result part has no name
						$msg[] = wfMsg('smw_wws_result_part_without_name', $rName);
						continue;
					}
					$path = (string) $part->attributes()->path;
					if (array_key_exists($pName, $pNames)) {
						if ($pNames[$pName]++ == 1) {
							$msg[] = wfMsg('smw_wws_duplicate_result_part', $pName, $rName);
						}
						continue;
					} else {
						$pNames[$pName] = 1;
						$wwsdPaths[$rName.'.'.$pName] = $path;
					}
				} else if ($part->getName() == 'namespace') {
					$prefix = (string) $part->attributes()->prefix;
					if ($prefix == null) {
						$msg[] = wfMsg('smw_wws_ns_without_prefix');
						continue;
					}
					$uri = (string) $part->attributes()->uri;
					if ($uri == null) {
						$msg[] = wfMsg('smw_wws_ns_without_uri');
						continue;
					}
				} else if ($part->getName() == 'triplification') {
					if($rememberedSubject){
						//only one triplification instruction is allowed
						$msg[] = wfMsg('smw_wws_triplification_instruction_twice');
						continue;
					}
					$subject = (string) $part->attributes()->subject;
					if ($subject == null) {
						$msg[] = wfMsg('smw_wws_triplification_without_subject');
						continue;
					} else {
						$rememberedSubject = $subject;
					}
				}
			}
			
			// no further validation of the subject creation pattern is done
			//	if($rememberedSubject && !array_key_exists($rememberedSubject, $pNames)){
			//		$msg[] = wfMsg('smw_wws_wrong_triplification_subject', $rName);
			//	}

			if (array_key_exists($rName, $rNames)) {
				if ($rNames[$rName]++ == 1) {
					$msg[] = wfMsg('smw_wws_duplicate_result', $rName);
				}
				continue;
			} else {
				$rNames[$rName] = 1;
			}

		}

		// This is deprecated
		// Check if there is a result in the WSDL for each alias.
		//		$wsdlResult = $this->mWSClient->getOperation($this->mMethod);
		//		if ($wsdlResult != null) {
		//			// Collect the components of the result
		//			$rType = $wsdlResult[0];
		//			$names = $this->flattenParam("", $rType);
		//			//$names = $this->getFlatParameters("", $rType, true);
		//
		//			// examine parameters
		//			// find elements that lead to overflows (e.g. potentially endless lists)
		//			foreach ($names as $idx=>$name) {
		//				$pos = strpos($name, '##overflow##');
		//				if ($pos) {
		//					$msg[] = wfMsg('smw_wwsd_overflow', substr($name, 0, $pos));
		//					unset($names[$idx]);
		//				}
		//			}
		//			// find undefined results
		//
		//			// this is a quick fix in order to allow brackets in result parts
		//			foreach($wwsdPaths as $key => $path){
		//				$pathSteps = explode(".", $path);
		//				for($z=0; $z < sizeof($pathSteps); $z++){
		//					if(!($this->getReturnPartBracketValue($pathSteps[$z]) === false)){
		//						$pathSteps[$z] = $this->getReturnPartPathStep($pathSteps[$z])."[]";
		//					}
		//				}
		//				$wwsdPaths[$key] = implode(".", $pathSteps);
		//			}
		//
		//			$inWwsd = array_diff($wwsdPaths, $names);
		//			foreach ($inWwsd as $r) {
		//				$r = array_search($r, $wwsdPaths);
		//				$msg[] = wfMsg('smw_wwsd_undefined_result', $r);
		//			}
		//		}
		
		if(count($rNames) > 1){
			$msg[] = wfMsg('smw_wws_too_many_results');
		}
		
		return count($msg) == 0 ? true : $msg;
	}

	/**
	 * Takes all parts of the given type and appends its fields to the given name.
	 * This happend recursively down to builtin types.
	 * Example:
	 * $name = point
	 * $type = Point (with the fields x and y)
	 * result:
	 *    - point.x
	 *    - point.y
	 *
	 * @param string $name
	 * 		The fields of the type are added to this name, separated by a dot.
	 * @param string $type
	 * 		The name of an XSD base type or a type defined in the WSDL.
	 * @param SoapClient $wsClient
	 *
	 * @param array<string> $typePath
	 * 		This array contains all types that were encountered in the recursion.
	 * 		To avoid an inifinite loop, the recursion stops if $type is already
	 * 		in the $typePath. This parameter is omitted in the top level call.
	 * @return array<string>
	 * 		All resulting paths. If a path causes an endless recursion, the
	 * 		keyword ##overflow## is appended to the path.
	 */
	public static function flattenParam($name, $type, $wsClient, &$typePath=null) {
		//I made this method public and static and also
		//added the parameter $wsClient so that it is accessible
		//via the ajax interface

		//add initial xpath root
		if(strpos($name, "/") === false){
			$name = "//".$name;
		}

		$flatParams = array();

		if (!$wsClient->isCustomType($type) && substr($type,0, 7) != "ArrayOf") {
			// $type is a simple type
			$flatParams[] = $name;
			return $flatParams;
		}

		if (substr($type,0, 7) == "ArrayOf") {
			if ($wsClient->isCustomType(substr($type, 7))) {
				//$flatParams[] = $name."[*]";
				$flatParams[] = $name;
				return $flatParams;
			}
		}

		$tp = $wsClient->getTypeDefinition($type);
		foreach ($tp as $var => $type) {
			if(substr($type,0, 7) == "ArrayOf"){
				$type = substr($type, 7);
				//$fname = ($name == "//") ? "//".$var."[*]" : $name.'/'.$var."[*]";
				$fname = ($name == "//") ? "//".$var : $name.'/'.$var;
			} else {
				$fname = ($name == "//") ? "//".$var : $name.'/'.$var;
			}
			if ($wsClient->isCustomType($type)) {
				if (!$typePath) {
					$typePath = array();
				}
				if (in_array($type, $typePath)) {
					// stop recursion
					$flatParams[] = $fname."##overflow##";
					continue;
				}
				$typePath[] = $type;
				$names = WebService::flattenParam($fname, $type, $wsClient, $typePath);
				$flatParams = array_merge($flatParams,$names);
				array_pop($typePath);
			} else {
				$flatParams[] = $fname;
			}
		}
		return $flatParams;
	}

	/**
	 * remove this ws from the database
	 *
	 */
	public function removeFromDB() {
		WSStorage::getDatabase()->removeWS($this->getArticleID());
	}

	/**
	 * validate the parameters used in the #ws-syntax
	 *
	 * @param array (parameter-name => value) $specifiedParameters
	 * @return array of error messages
	 */
	public function validateSpecifiedParameters($specifiedParameters){
		$messages = array();
		foreach($specifiedParameters as $pName => $pValue){
			$exists = false;
			if($this->mParsedParameters != null){
				foreach($this->mParsedParameters->children() as $child){
					if(strtolower("".$child["name"]) == strtolower($pName)){
						$exists = true;
					}
				}
			}
			if(!$exists){
				$messages[] = wfMsg('smw_wsuse_wrong_parameter', $pName);
			}
		}
		if($this->mParsedParameters != null){
			foreach($this->mParsedParameters->children() as $child){
				if("".$child["optional"] == "false" && "".$child["defaultValue"] == null){
					$exists = false;
					foreach($specifiedParameters as $pName => $pValue){
						if(strtolower("".$child["name"]) == strtolower($pName)){
							$exists = true;
						}
					}
					if(!$exists){
						$messages[] = wfMsg('smw_wsuse_parameter_missing', "".$child["name"]);
					}
				}
			}
		}
		return $messages;
	}


	/**
	 * validate the result parts requested in the #ws-syntax
	 *
	 * @param array (resultname => default value) $specifiedResults
	 * @return array of error-messages
	 */
	public function validateSpecifiedResults($specifiedResults){
		$messages = array();
		foreach($specifiedResults as $rName => $rValue){
			$rPathSteps = explode(".",$rName);
			$exists = false;
			$resultDef = $this->getResultDefinition($rPathSteps[0]);
			if($resultDef != null){
				if (count($rPathSteps) == 1 ) {
					// Only the name of the result definition is given
					return $messages;
				}
				foreach($resultDef->children() as $child){
					if(strtolower("".$child["name"]) == strtolower($rPathSteps[1])){
						$exists = true;
					}
				}
			}
			if(!$exists){
				$messages[] = wfMsg('smw_wsuse_wrong_resultpart', $rName);
			}
		}
		return $messages;
	}

	/**
	 * returns values for the last brackets used in resultparts
	 *
	 * @param string $name
	 * @return string value or false
	 */
	private function getReturnPartBracketValue($name){
		$strpos = strrpos($name, "[");

		if($strpos){
			if(strpos($name, "]")){
				$return = substr($name, $strpos+1, strpos($name, "]")-strpos($name, "[")-1);
				if($return == ""){
					return true;
				} else {
					return $return;
				}
			}
		}
		return false;
	}


	/**
	 * returns the pathstep of a resultpart without brackets
	 *
	 * @param string $name
	 * @return string name without the last brackets
	 */
	private function getReturnPartPathStep($name){
		$strpos = strrpos($name, "[");
		return $strpos ? substr($name, 0, $strpos) : $name;
	}

	/**
	 * Returns an instance of IWebServiceClient
	 *
	 * @return IWebServiceClient
	 *
	 *
	 */
	public function getWSClient() {
		$this->createWSClient();
		return $this->mWSClient;
	}

	/**
	 * A WWSD can contain several result definitions that have a unique name.
	 * This method returns the parsed XML structure of the result definition
	 * with the given name.
	 *
	 * @param string $defName
	 * 		Name of the requested result definition
	 * @return SimpleXMLElement
	 * 		The request definition or <null>, if the definition does not exist.
	 */
	private function getResultDefinition($defName) {
		foreach ($this->mParsedResult as $rdef) {
			if (strtolower($defName) == strtolower($rdef['name'])) {
				return $rdef;
			}
		}
		return null;
	}

	/**
	 * checks if array indexes in parameter paths are appropriate
	 *
	 * @param array_type $paths : the paths to check
	 * @param array msg : error messages and warnings
	 * @return boolean check result
	 */
	public function checkParameterArrayIndexes($paths, &$msg){
		$ok = true;
		$aPaths = array();

		foreach($paths as $path){
			$pathSteps = explode(".", $path);
			$aPath = "";

			foreach($pathSteps as $pathStep){
				if(!(WebService::getReturnPartBracketValue($pathStep) === false)){
					if(WebService::getReturnPartBracketValue($pathStep) !== true){
						$index = WebService::getReturnPartBracketValue($pathStep);
						$tAPath = $aPath.".".WebService::getReturnPartPathStep($pathStep);

						if(strpos(".".$path, $aPath.".".$pathStep) > -1){
							$tAPath .= "[].".substr($path, strlen($aPath.".".$pathStep));
						}

						if(!array_key_exists($tAPath, $aPaths)){
							$aPaths[$tAPath] = array();
						}

						$aPaths[$tAPath][] = $index;
					}
					else {
						$msg[] = wfMsg('smw_wwsd_array_index_missing', $path);
						$ok = false;
					}
				}
				$aPath .= ".".$pathStep;
			}
		}

		foreach($aPaths as $key => $aPath){
			sort($aPath);

			$index = 0;
			foreach($aPath as $aIndex){

				if($aIndex != $index){
					$ok = false;
					$msg[] = wfMsg('smw_wwsd_array_index_incorrect', $key);
					break;
				}
				$index += 1;
			}
		}

		return $ok;
	}

	/*
	 * Returns the value of the xpath attribute
	 * of a result part with a given alias
	 */
	private function getXPathForAlias($alias, $resultDef) {
		foreach ($resultDef->part as $part) {
			if (strtolower($alias) == strtolower(''.$part['name'])) {
				return ''.$part['xpath'];
			}
		}
		return null;

	}

	/*
	 * Returns the value of the json attribute
	 * of a result part with a given alias
	 */
	private function getJSONForAlias($alias, $resultDef) {
		foreach ($resultDef->part as $part) {
			if (strtolower($alias) == strtolower(''.$part['name'])) {
				return ''.$part['json'];
			}
		}
		return null;

	}

	/*
	 * Returns the value of the property attribute
	 * of a result part with a given alias
	 */
	private function getPropertyForAlias($alias, $resultDef) {
		foreach ($resultDef->part as $part) {
			if (strtolower($alias) == strtolower(''.$part['name'])) {
				return ''.$part['property'];
			}
		}
		return null;

	}

	/**
	 * Validate subparameters and fill default values
	 *
	 * @param $subParameterBundle : <parameterName : <subParameterName : value>>
	 * @return : an array which contains an array of error messages and an
	 * 	array which contains the subparameters together with their values
	 */
	public function validateSpecifiedSubParameters($subParameterBundle){
		$messages = array();
		$response = array();

		// search for parameters that were not passed and add
		// them to the subParametersBundle, so that also their
		// missing subparameters will be recognized
		if($this->mParsedParameters != null){
			foreach($this->mParsedParameters->children() as $child){
				$found = false;
				foreach($subParameterBundle as $parameterName => $subParameters){
					if(strtolower("".$child["name"]) == strtolower($parameterName)){
						$found = true;
					}
				}
				if(!$found){
					$subParameterBundle["".$child["name"]] = array();
				}
			}
		}
		
		foreach($subParameterBundle as $parameterName => $subParameters){
			$parameterDefinition = "";
			if($this->mParsedParameters != null){
				foreach($this->mParsedParameters->children() as $child){
					if(strtolower("".$child["name"]) == strtolower($parameterName)){
						$parameterDefinition = $child->asXML();
					}
				}
			}
			
			if($parameterDefinition == ""){
				$messages[] = wfMsg('smw_wsuse_wrong_parameter', $parameterName);
				//handle this!!!
				//return array($messages, null);
			}

			$subParameterProcessor = new SMWSubParameterProcessor(
				$parameterDefinition, $subParameters);

			$subParameterProcessor->getMissingSubParameters();

			$missingSP = $subParameterProcessor->getMissingSubParameters();
			foreach($missingSP as $key => $dontCare){
				$messages[] = wfMsg('smw_wsuse_parameter_missing', $parameterName.".".$key);
			}

			$unavailableSP = $subParameterProcessor->getUnavailableSubParameters();
			foreach($unavailableSP as $key => $dontCare){
				$messages[] = wfMsg('smw_wsuse_wrong_parameter', $parameterName.".".$key);
			}

			$computedParameterValue = $subParameterProcessor->createParameterValue();
			if(count($subParameterProcessor->getDefaultSubParameters()) > 0
			|| count($subParameterProcessor->getPassedSubParameters()) > 0){
				$response = array_merge($response,
				array($parameterName => $subParameterProcessor->createParameterValue()));
			}
		}

		if(count($messages) > 0){
			return array($messages, array());
		}

		return array(null, $response);
	}

	/**
	 * get a comma separated list of default values for results
	 *
	 * @param $resultParts
	 * @return unknown_type
	 */
	private function getResultDefaultValues($resultParts){
		$response = array();

		foreach ($resultParts as $rp => $defaultValue) {
			$parts = explode(".", $rp);

			if(count($parts) > 1 && strlen($defaultValue) > 0){
				$response[$parts[0]] = null;
				$response[$rp] = $defaultValue;
			} else {
				if(!array_key_exists($parts[0], $response)){
					$rdef = $this->getResultDefinition($parts[0]);
					$defaultValue = @ "".$rdef["defaultValue"];
					if($defaultValue != null){
						$response[$parts[0]] = $defaultValue;
					}
				}
			}
		}

		foreach($response as $key => $value){
			if($value == null){
				unset($response[$key]);
			}
		}

		return implode(", ", $response);
	}

	/**
	 * Get the triplification subject, which has been defined for that WS
	 */
	public function getTriplificationSubject(){
		$xml = new SimpleXMLElement($this->mResult);
		$tmp = $xml->xpath("//triplification/@subject");
		if((is_array($tmp) || is_object($tmp)) && array_key_exists(0, $tmp)){
			return ''.$tmp[0];
		}
		return null;
	}

	/*
	 * returns an array, which contains the aliases of all result parts
	 * which have been defined in the WWSD
	 */
	public function getAllResultPartAliases(){
		$aliases = array();

		foreach($this->mParsedResult as $result){
			$name = ''.$result['name'];
			foreach($result->children() as $p){
				if(strlen($p['name']) > 0){
					$aliases[$name.".".$p['name']] = '';
				}
			}
		}

		return $aliases;
	}
}
