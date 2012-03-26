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

/**
 * @file
 * @ingroup FacetedSearch
 *
 * This file contains the class FSMWAccessControl
 * 
 * @author Thomas Schweitzer
 * Date: 29.02.2012
 * 
 */
if ( !defined( 'SOLRPROXY' ) ) {
	die( "This file is part of the FacetedSearch extension. It is not a valid entry point.\n" );
}

//--- Includes ---

/**
 * 
 * The class FSMWAccessControl retrieves access rights to MediaWiki elements
 * from MediaWiki.
 * 
 * @author Thomas Schweitzer
 * 
 */
class FSMWAccessControl {
	
	//--- Constants ---
		
	//--- Private fields ---
	
	// @staticvar  FSMWAccessControl The only instance of this class
	private static $mInstance = null;
	
	
	/**
	 * Constructor for FSHaloACLMemcache
	 * Loads the memcached-client library
	 */		
	function __construct() {
	}
	

	//--- getter/setter ---
	
	//--- Public methods ---
	
	/**
	* Returns the only instance of this class.
	* @return FSMWAccessControl
	* 		The singleton
	*/
	public static function getInstance() {
		if (is_null(self::$mInstance)) {
			self::$mInstance = new self;
		}
		return self::$mInstance;
	}
	
	/**
	 * Checks if the current user can perform the $action on the $titleID.
	 * It tries to get the permission from MediaWiki.
	 * 
	 * @access public
	 * 
	 * @param string/int $titleID
	 * 		The titleID consists of the namespace number and the dbkey of the 
	 * 		title separated by a colon i.e. 0:Main_Page.
	 * 		If the $subjectType is FSRF_NAMESPACE, the $titleID is a namespace
	 * 		number.
	 * @param string $action
	 * 		The requested action to perform on the title.
	 * @param int $subjectType (optional)
	 * 		The type of the subject for which the right is checked. Possible values
	 * 		are FSRF_ARTICLE, FSRF_CATEGORY, FSRF_PROPERTY, FSRF_NAMESPACE.
	 * 
	 * 
	 * @return boolean/integer
	 * 		true, if access is allowed
	 * 		false, if not
	 * 		-1, if the information could not be retrieved
	 * 
	 */
	public function checkRights($titleID, $action, $subjectType = FSResultFilter::FSRF_ARTICLE) {
		list($ns, $titleName) = explode(':', $titleID, 2);
		switch ($subjectType) {
			case FSResultFilter::FSRF_ARTICLE:
				$response = $this->mwAjaxCall('smwf_om_userCan', 
								array($titleName, $action, $ns));
				return $response === 'true' ? true
											: ($response === 'false' ? false : -1);
				break;
			case FSResultFilter::FSRF_CATEGORY:
				$response = $this->mwAjaxCall('haclAarCheckCategoryAccessMulti', 
								array($titleName, $action));
				break;
			case FSResultFilter::FSRF_PROPERTY:
				$response = $this->mwAjaxCall('haclAarCheckPropertyAccessMulti', 
								array($titleName, $action));
				break;
			case FSResultFilter::FSRF_NAMESPACE:
				$response = $this->mwAjaxCall('haclAarCheckNamespaceAccessMulti', 
								array($ns, $action));
				break;
			
		}
		$permissions = json_decode($response);
		if (!is_array($permissions)) {
			// Expected and array of permissions
			return -1;
		}
		$permission = $permissions[0];
		return $permission[1] === 'true' ? true : false;
		
	}

	/**
	 * Checks if the current user can perform the $action on all $titleIDs.
	 * It tries to get the permissions from MediaWiki.
	 * 
	 * @access public
	 * 
	 * @param array(string/int $titleID)
	 * 		The titleIDs consists of the namespace number and the dbkey of the 
	 * 		title separated by a colon i.e. 0:Main_Page.
	 * 		If the $subjectType is FSRF_NAMESPACE, the $titleIDs are namespace
	 * 		numbers.
	 * @param string $action
	 * 		The requested action to perform on the titles.
	 * @param int $subjectType (optional)
	 * 		The type of the subject for which the right is checked. Possible values
	 * 		are FSRF_ARTICLE, FSRF_CATEGORY, FSRF_PROPERTY, FSRF_NAMESPACE.
	 * 
	 * @return array(string => boolean/integer) or integer
	 * 		title => true/false
	 * 		-1, if no permission could be retrieved from MediaWiki
	 * 
	 */
	public function checkRightsMulti($titleIDs, $action, $subjectType = FSResultFilter::FSRF_ARTICLE) {
		$ns = "";
		$removeNS = false;
		if ($subjectType === FSResultFilter::FSRF_CATEGORY || 
			$subjectType === FSResultFilter::FSRF_PROPERTY) {
			$removeNS = true;
		}
		
		// Remove namespace prefixes
		foreach ($titleIDs as $idx => $t) {
			if ($removeNS) {
				list($ns, $t) = explode(':', $t);
			}
			// Escape all commas in the titles
			$t = str_replace(',', '\,', $t);
			$titleIDs[$idx] = $t;
		}
		if (!empty($ns)) {
			$ns .= ':';
		}
		
		$t = implode(',', $titleIDs);
		
		switch ($subjectType) {
			case FSResultFilter::FSRF_ARTICLE:
				$response = $this->mwAjaxCall('smwf_om_userCanMultiple',
								array($t, $action, true));
				break;
			case FSResultFilter::FSRF_CATEGORY:
				$response = $this->mwAjaxCall('haclAarCheckCategoryAccessMulti',
								array($t, $action));
				break;
			case FSResultFilter::FSRF_PROPERTY:
				$response = $this->mwAjaxCall('haclAarCheckPropertyAccessMulti',
								array($t, $action));
				break;
			case FSResultFilter::FSRF_NAMESPACE:
				$response = $this->mwAjaxCall('haclAarCheckNamespaceAccessMulti',
								array($t, $action));
				break;
					
		}

		$permissions = json_decode($response);
		if (!is_array($permissions)) {
			// Expected and array of permissions
			return -1;
		}
		
		// Restructure the array
		$result = array();
		foreach ($permissions as $p) {
			$p1 = $p[1] === 'true' ? true : false;
			$result[] = array($ns.$p[0] => $p1);
		}
		return $result;
		
	}

	//--- Private methods ---
	
	/**
	 * Sends an ajax call to MediaWiki as GET request
	 * @param string $method
	 * 		The name of the method to call
	 * @param array(string) $arguments
	 * 		The arguments for the method
	 * @return string
	 * 		Result of the ajax call
	 */
	private function mwAjaxCall($method, $arguments) {
		global $spgHaloACLConfig;
		$mwIdx = $spgHaloACLConfig['mediawikiIndex'];
		$url = "$mwIdx?action=ajax&rs=$method";
		foreach ($arguments as $idx => $arg) {
			$arguments[$idx] = urlencode($arg);
		}
		$args = count($arguments) > 0 
				? '&rsargs[]=' . implode('&rsargs[]=', $arguments)
				: '';
		$url .= $args;
		
		$ch = curl_init($url);
		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$header = array(
					"Accept: ${_SERVER[HTTP_ACCEPT]}",
					"Accept-Language: ${_SERVER[HTTP_ACCEPT_LANGUAGE]}",
					"Connection: ${_SERVER[HTTP_CONNECTION]}",
					"Cookie: ${_SERVER[HTTP_COOKIE]}",
					"Host: ${_SERVER[HTTP_HOST]}",
					"User-Agent: ${_SERVER[HTTP_USER_AGENT]}"
		);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		
		$response = curl_exec($ch);
		curl_close($ch);
		
		return $response;
	}
	
}