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
require_once("$smwgHaloIP/specials/SMWWebService/SMW_WSStorage.php");

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
	private $mParameters;     //string: description of the parameter structure
	private $mParsedParameters; // The parsed structure of the parameter description
							    // in form of nested arrays 
	private $mResult;         //string: description of the result structure
	private $mParsedResult;   // The parsed structure of the result description
							  // in form of nested arrays 
	private $mDisplayPolicy;  //int: display update policy in minutes. 0 means only once.
	private $mQueryPolicy;    //int: query update policy in minutes. 0 means only once.
	private $mUpdateDelay;    //int: update delay for the query policy in seconds
	private $mSpanOfLife;     //int: span of life in days. 0 means forever.
	private $mExpiresAfterUpdate; //bool: <true> if the span of life starts to expire
							  // after an update. Otherwise it starts after the
							  // last access.
	private $mConfirmed;      //bool: confirmed by sysop (true) or not (false)
	
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
	function __construct($name = "", $uri = "", $protcol = "", $method = "", 
	                     $parameters = "", $result = "", 
	                     $dp = 0, $qp = 0, $updateDelay = 0, $sol = 0, 
	                     $expiresAfterUpdate = false, $confirmed = false) {
		$this->mName = $name;
		$this->mURI = $uri;	                     	
		$this->mProtocol = $protcol;	                     	
		$this->mMethod = $method;
		$this->mParameters = $parameters;
		$this->mParsedParameters = null;
		$this->mResult = $result;
		$this->mParsedResult = null;
		$this->mDisplayPolicy = $dp;
		$this->mQueryPolicy = $qp;
		$this->mUpdateDelay = $updateDelay;
		$this->mSpanOfLife = $sol;
		$this->mExpiresAfterUpdate = $expiresAfterUpdate;
		$this->mConfirmed = $confirmed;
	}
	

	//--- getter/setter ---
	public function getName()          {return $this->mName;}
	public function getURI()           {return $this->mURI;}
	public function getProtocol()      {return $this->mProtocol;}
	public function getMethod()        {return $this->mMethod;}
	public function getParameters()    {return $this->mParameters;}
	public function getResult()        {return $this->mResult;}
	public function getDisplayPolicy() {return $this->mDisplayPolicy;}
	public function getQueryPolicy()   {return $this->mQueryPolicy;}
	public function getUpdateDelay()   {return $this->mUpdateDelay;}
	public function getSpanOfLife()    {return $this->mSpanOfLife;}
	public function doesExpireAfterUpdate()   {return $this->mExpiresAfterUpdate;}
	public function isConfirmed()      {return $this->mConfirmed;}
		
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
		$ws->mArticleID = $id;
	}
	
	/**
	 * Creates a new instance of a WebService from the given wiki web service
	 * description <$wwsd>.
	 *
	 * @param string $wwsd
	 * 		This wiki web service definition is parsed and its values are
	 * 		stored in the fields of a new object.
	 * 
	 * @return mixed WebService/string
	 * 		A new instance of WebService or
	 * 		an error message, if parsing of the WWSD failed.
	 */
	public static function newFromWWSD($wwsd) {
    	global $smwgHaloIP;
		require_once($smwgHaloIP . '/includes/SMW_XMLParser.php');
		
		$parser = new XMLParser($wwsd);
		$result = $parser->parse();
		if ($result !== true) {
			return $result;
		}
		
		// Check if the roor node of the XML structure is 'webservice'
		if (!$parser->rootIs('webservice')) {
			return wfMsg('smw_wws_invalid_wwsd');
		}
		$wwsd = $parser->getElement(array('webservice'));
		$ws = new WebService();
		$msg = '';
		$valid = true;
		$valid &= self::getWWSDElement($wwsd, 'webservice', 'name', $ws->mName, false, 1, 1, $msg);
		$valid &= self::getWWSDElement($wwsd, 'webservice/ur', 'name', $ws->mURI, false, 1, 1, $msg);
		$valid &= self::getWWSDElement($wwsd, 'webservice/uri', 'nam', $ws->mURI, false, 1, 1, $msg);
		$valid &= self::getWWSDElement($wwsd, 'webservice/uri', 'name', $ws->mURI, false, 1, 1, $msg);
		$valid &= self::getWWSDElement($wwsd, 'webservice/protocol', null, $ws->mProtocol, false, 1, 1, $msg);
		$valid &= self::getWWSDElement($wwsd, 'webservice/method', 'name', $ws->mMethod, false, 1, 1, $msg);
		$valid &= self::getWWSDElement($wwsd, 'webservice/parameter', null, $ws->mParameters, false, 1, 100, $msg);
		$valid &= self::getWWSDElement($wwsd, 'webservice/result', null, $ws->mResult, false, 1, 100, $msg);
		
		$tmpMsg = "";
		$v = self::getWWSDElement($wwsd, 'webservice/displayPolicy/once', null, $temp, false, 1, 1, $tmpMsg);
		if ($v) {
			$ws->mDisplayPolicy = 0;
		} else {
			$valid &= self::getWWSDElement($wwsd, 'webservice/displayPolicy/maxage', 'value', $ws->mDisplayPolicy, true, 1, 1, $msg);
		}

		$v = self::getWWSDElement($wwsd, 'webservice/queryPolicy/once', null, $temp, false, 1, 1, $tmpMsg);
		if ($v) {
			$ws->mQueryPolicy = 0;
		} else {
			$valid &= self::getWWSDElement($wwsd, 'webservice/queryPolicy/maxage', 'value', $ws->mQueryPolicy, true, 1, 1, $msg);
		}
		$v = self::getWWSDElement($wwsd, 'webservice/queryPolicy/delay', 'value', $ws->mUpdateDelay, true, 1, 1, $tmpMsg);
		if (!$v) {
			// The update delay is optional. Its default value is 0.
			$ws->mUpdateDelay = 0;
		}
		
		$valid &= self::getWWSDElement($wwsd, 'webservice/spanoflife', 'value', $ws->mSpanOfLife, false, 1, 1, $msg);
		if ($valid) {
			if (strtolower($ws->mSpanOfLife) == 'forever') {
				$ws->mSpanOfLife = 0;
			} else {
				$ws->mSpanOfLife = intval($ws->mSpanOfLife);
			}
		}
		$ws->mExpiresAfterUpdate = false;
		$v = self::getWWSDElement($wwsd, 'webservice/spanoflife', 'expiresAfterUpdate', $ws->mExpiresAfterUpdate, false, 1, 1, $msg);
		
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
	 * @param string $msg
	 * 		If the element is erroneous, this error message is enhanced.
	 *
	 * @return bool
	 * 		<true>, if the requested WWSD element could be retrieved without an
	 * 			    error
	 * 		<false> otherwise.
	 */
	private static function getWWSDElement(&$wwsd, $wwsdElementPath, 
									$attribute, &$variable, $isNumeric, 
									$min, $max, &$msg) {
		
    	$wwsdElementPath = strtoupper($wwsdElementPath);
		$attribute       = strtoupper($attribute);

		$pathElems = explode('/', $wwsdElementPath);
		$subTree = &$wwsd;
		$numElems = count($pathElems);
		
		for ($i = 0; $i < $numElems; ++$i) {
			$elem = $pathElems[$i];
			$subTree = &$subTree[$elem];
			if (!$subTree) {
				$msg .= wfMsg('smw_ws_wwsd_element_missing', $wwsdElementPath).'<br />';
				return false;
			}
			if ($i != $numElems - 1) {
				$subTree = &$subTree[0]['value'];
			}
		}
		
		if (count($subTree) < $min) {
			$msg .= wfMsg('smw_ws_wwsd_element_missing', $wwsdElementPath).'<br />';
			return false;
		}
		if (count($subTree) > $max) {
			$msg .= wfMsg('smw_ws_too_many_wwsd_elements', $wwsdElementPath).'<br />';
			return false;
		}
		
		if ($min == 1 && $max == 1) {
			// exactly one element is expected and present
			if ($attribute) {
				$val = $subTree[0]['attributes'][$attribute];
				if ($val == null) {
					$msg .= wfMsg('smw_ws_wwsd_attribute_missing', $attribute, $wwsdElementPath).'<br />';
					return false;
				}
			} else {
				$val = $subTree[0]['value'];
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
}

?>