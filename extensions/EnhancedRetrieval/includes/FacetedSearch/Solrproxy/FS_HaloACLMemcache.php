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
 * This file contains the class FSHaloACLMemcache
 * 
 * @author Thomas Schweitzer
 * Date: 29.02.2012
 * 
 */
if ( !defined( 'SOLRPROXY' ) ) {
	die( "This file is part of the FacetedSearch extension. It is not a valid entry point.\n" );
}

//--- Includes ---
if ( !defined( 'MEDIAWIKI' ) ) {
	// We are not running in the context of MediaWiki
	// Add some fake functions 
	require_once 'FS_MWFakeFunctions.php';
}

/**
 * 
 * The class FSHaloACLMemcache retrieves access rights to MediaWiki elements
 * from memcache.
 * It uses the memcached-client library of MediaWiki.
 * 
 * @author Thomas Schweitzer
 * 
 */
class FSHaloACLMemcache {
	
	//--- Constants ---
		
	//--- Private fields ---
	
	// @staticvar  FSHaloACLMemcache The only instance of this class
	private static $mInstance = null;
	
	// @var MWMemcached Object for accessing the memcache server
	private $mMemcache = null;
	
	
	/**
	 * Constructor for FSHaloACLMemcache
	 * Loads the memcached-client library
	 */		
	function __construct() {
		require_once __DIR__.'/../../../../../includes/memcached-client.php';
		
		// Needed internally by memcached-client
		global $wgMemCachedTimeout;
		/**
		* Read/write timeout for MemCached server communication, in microseconds.
		*/
		$wgMemCachedTimeout = 100000;
		
	}
	

	//--- getter/setter ---
	
	//--- Public methods ---
	
	/**
	* Returns the only instance of this class.
	* @return FSHaloACLMemcache
	* 		The singleton
	*/
	public static function getInstance() {
		if (is_null(self::$mInstance)) {
			self::$mInstance = new self;
		}
		return self::$mInstance;
	}
	
	/**
	 * Returns the object for accessing memcache. If it does not exist yet, it
	 * will be created.
	 */
	public function getMemcache() {
		if (!$this->mMemcache) {
			global $spgHaloACLConfig;
			$this->mMemcache = new MWMemcached($spgHaloACLConfig['memcacheconfig']);
		}
		return $this->mMemcache;
	}
	
	/**
	 * Checks if the $user can perform the $action on the $titleID.
	 * It tries to find the permission in the HaloACL memcache.
	 * 
	 * @access public
	 * 
	 * @param string $user
	 * 		Name of the user
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
	 * @return boolean/integer
	 * 		true, if access is allowed
	 * 		false, if not
	 * 		-1, if the information is not stored in memcache
	 * 
	 */
	public function checkRights($user, $titleID, $action, $subjectType = FSResultFilter::FSRF_ARTICLE) {
		$key = $this->makeKey($user, $titleID, $action, $subjectType);
		$memc = $this->getMemcache();
		$permission = $memc->get($key);
		return $permission === '1' ? true
								   : ($permission === '0' ? false : -1);
		
	}
	
	/**
	 * Checks if the $user can perform the $action on all $titles.
	 * It tries to find the permission in the HaloACL memcache.
	 * 
	 * @access public
	 * 
	 * @param string $user
	 * 		Name of the user
	 * @param array(string/int) $titleIDs
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
	 * 		-1, if no information is stored in memcache at all
	 * 
	 */
	public function checkRightsMulti($user, $titleIDs, $action, $subjectType = FSResultFilter::FSRF_ARTICLE) {
		$result = array();
		$permissionFound = false;
		foreach ($titleIDs as $t) {
			$allowed = $this->checkRights($user, $t, $action, $subjectType);
			$result[] = array($t => $allowed);
			if ($allowed !== -1) {
				$permissionFound = true;
			}
		}
		return $permissionFound ? $result : -1;		
	}
	

	//--- Private methods ---
	
	/**
	 * Creates the memcache key for all given parameters.
	 * This method has a variable argument list.
	 * The implementation is mostly copied from MediaWiki's wfMemcKey
	 * 
	 * @return string
	 * 		The memcache key
	 */
	private function makeKey($user, $titleID, $action, $subjectType) {
		$actionPrefix = '';
		if ($subjectType === FSResultFilter::FSRF_CATEGORY) {
			$actionPrefix = 'category-';
		} else if ($subjectType === FSResultFilter::FSRF_PROPERTY) {
			$actionPrefix = 'property-';
		} else if ($subjectType === FSResultFilter::FSRF_NAMESPACE) {
			$actionPrefix = 'namespace-';
		}  
		$key = $user.':'.$titleID.':'.$actionPrefix.$action;
		$key = $this->getWikiID() . ':' . $key;
		$key = str_replace( ' ', '_', $key );
		return $key;
	}
	
	/**
	 * Get an ASCII string identifying this wiki
	 * This is used as a prefix in memcached keys
	 */
	private function getWikiID() {
		global $spgHaloACLConfig;
		if (array_key_exists('wikiDBprefix',$spgHaloACLConfig) && 
			$spgHaloACLConfig['wikiDBprefix']) {
			return "${spgHaloACLConfig['wikiDBname']}-${spgHaloACLConfig['wikiDBprefix']}";
		} else {
			return $spgHaloACLConfig['wikiDBname'];
		}
	}
	
}