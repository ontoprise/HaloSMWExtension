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
 * This file contains the class FSResultFilter
 * 
 * @author Thomas Schweitzer
 * Date: 28.02.2012
 * 
 */
if ( !defined( 'SOLRPROXY' ) ) {
	die( "This file is part of the FacetedSearch extension. It is not a valid entry point.\n" );
}

//--- Includes ---

require_once 'FS_ResultParser.php';
require_once 'FS_HaloACLMemcache.php';
require_once 'FS_MWAccessControl.php';
require_once 'FS_Messages.php';

/**
 * 
 * The class FSResultFilter filters elements that are protected by HaloACL from
 * the list of results.
 * 
 * @author Thomas Schweitzer
 * 
 */
class FSResultFilter {
	
	//--- Constants ---
	// Constants for the type of the subject for which access rights are checked.
	const FSRF_ARTICLE = 0; 	// Check rights for articles
	const FSRF_CATEGORY = 1;	// Check rights for instances of a category
	const FSRF_PROPERTY = 2;	// Check rights for property values 
	const FSRF_NAMESPACE = 3;	// Check rights for instances of a namespace
		
	//--- Private fields ---
	
	// @staticvar  FSResultFilter The only instance of this class
	private static $mInstance = null;
	
	
	/**
	 * Constructor for FSResultFilter
	 *
	 * @param type $param
	 * 		Name of the notification
	 */		
	function __construct() {
	}
	

	//--- getter/setter ---
	
	//--- Public methods ---
	
	/**
	* Returns the only instance of this class.
	* @return FSResultFilter
	* 		The singleton
	*/
	public static function getInstance() {
		if (is_null(self::$mInstance)) {
			self::$mInstance = new self;
		}
		return self::$mInstance;
	}
	
	/**
	 * Checks if the $user can perform the $action on the $title with the given
	 * $nsNumber.
	 * First this method checks in the HaloACL memcache if it has information
	 * about the permission. If not, it sends a request to MediaWiki to evaluate
	 * the access rights.
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
	 * @return boolean/int
	 * 		true, if access is allowed
	 * 		false, if not
	 * 		-1 if the access right could not be retrieved.
	 * 
	 */
	public function checkRights($user, $titleID, $action, $subjectType = FSRF_ARTICLE) {
		// First try to find the access rights in memcache
		$hmc = FSHaloACLMemcache::getInstance();
		$allowed = $hmc->checkRights($user, $titleID, $action, $subjectType);
		if ($allowed === -1) {
			// Access right was not stored in memcache. 
			// Take the long and slow way and ask the wiki.
			$mwac = FSMWAccessControl::getInstance();
			$allowed = $mwac->checkRights($titleID, $action, $subjectType);
		}
		return $allowed;
	}
	
	/**
	 * Checks if the $user can perform the $action on all $titleIDs.
	 * First this method checks in the HaloACL memcache if it has information
	 * about the permission. If not, it sends a request to MediaWiki to evaluate
	 * the access rights.
	 * The memcache may contain permissions for only some titles and not all of
	 * them. In that case MediaWiki is asked for the missing permissions.
	 *
	 * @access public
	 *
	 * @param string $user
	 * 		Name of the user
	 * @param array(string/int) $titleIDs
	 * 		Array of titleIDs that consist of the namespace number and the dbkey
	 * 		of the title separated by a colon i.e. 0:Main_Page.
	 * 		If the $subjectType is FSRF_NAMESPACE, the array must contain the
	 * 		namespace numbers.
	 * @param string $action
	 * 		The requested action to perform on the title.
	 * @param int $subjectType (optional)
	 * 		The type of the subject for which the right is checked. Possible values
	 * 		are FSRF_ARTICLE, FSRF_CATEGORY, FSRF_PROPERTY, FSRF_NAMESPACE.
	 *
	 * @return array(string => boolean/integer) or integer
	 * 		title => true/false
	 * 		-1, if no permission could be retrieved from MediaWiki
	 *
	 */
	public function checkRightsMulti($user, $titleIDs, $action, $subjectType = FSRF_ARTICLE) {
		// First try to find the access rights in memcache
		$hmc = FSHaloACLMemcache::getInstance();
		$permissions = $hmc->checkRightsMulti($user, $titleIDs, $action, $subjectType);
		
		// Array of titles whose permissions are still missing
		$missingPermissions = array(); 
		
		if ($permissions === -1) {
			// No access right was stored in memcache.
			// Take the long and slow way and ask the wiki.
			$missingPermissions = $titleIDs;
			$permissions = array();
		}
		
		// Could all permissions be retrieved?
		if (count($missingPermissions) === 0) {
			// Check permissions array
			foreach ($permissions as $idx => $permission) {
				foreach ($permission as $title => $p) {
					if ($p === -1) {
						$missingPermissions[] = $title;
						unset($permissions[$idx]);
					}
				}
			}
		}
		if (count($missingPermissions) > 0) {
			$mwac = FSMWAccessControl::getInstance();
			$mwPermissions = $mwac->checkRightsMulti($missingPermissions, $action, $subjectType);
			if ($mwPermissions === -1 && count ($permissions) === 0) {
				// no permissions could be retrieved at all
				return -1;
			}
			$permissions = array_merge($permissions, $mwPermissions);
		}
		
		return $permissions;
		
	}
	
	/**
	 * Filters the raw result of a SOLR query according to HaloACL access rules.
	 * 
	 * @access public
	 * @param string $user
	 * 		Name of the user. If NULL, the current user is retrieved from the
	 * 		session data.
	 * @param string $solrResult
	 * 		The raw solr result string
	 * @param string $action
	 * 		The action that may be denied or allowed
	 * 
	 * @return string
	 * 		The filtered solr result.
	 */
	public function filterResult($user, $action, $solrResult) {
		// Convert the result string to objects
 		$resultObjects = FSResultParser::parseResult($solrResult);
 		
 		if (!$resultObjects) {
 			// Parser could not parse the result
 			// => return it as it is.
 			return $solrResult;
 		}
 		
		if (!$user) {
			$user = $this->getCurrentUser();
		}
		
		// Find the titles in the result
		$resultFiltered = false;
		$titleIDs = $this->getTitleIDsFromResult($resultObjects);
		if (count($titleIDs) > 0) {
			// and determine their access rights
			$accessRights = $this->checkRightsMulti($user, $titleIDs, $action);
			$resultObjects = $this->removeDeniedTitles($accessRights, $resultObjects);
			$resultFiltered = true;
		}
		
		// Find titles, categories, properties and namespaces in the facets of
		// the result.
		$titleIDs = $this->getTitlesFromResultFacets($resultObjects);
		if (count($titleIDs) > 0) {
			// and determine their access rights
 			$categoryRights = $this->checkRightsMulti($user, $titleIDs['categories'], $action, self::FSRF_CATEGORY);
 			$propertyRights = $this->checkRightsMulti($user, $titleIDs['properties'], $action, self::FSRF_PROPERTY);
 			$namespaceRights = $this->checkRightsMulti($user, $titleIDs['namespaces'], $action, self::FSRF_NAMESPACE);
 			
 			// Remove protected elements from the facets.
			$resultObjects = $this->removeDeniedTitlesInFacets($categoryRights, 
									$propertyRights, $namespaceRights, 
									$titleIDs, $resultObjects);
			$resultFiltered = true;
		}
		
		if ($resultFiltered) {
	 		$solrResult = FSResultParser::serialize($resultObjects);
		}
		
		return $solrResult;
		
	}
	

	//--- Private methods ---
	
	/**
	* Retrieves the name of the current user from the session data.
	*
	* @return string / Null
	* 		Name of the current user or NULL for an anonymous user.
	*/
	private function getCurrentUser() {
		global $spgHaloACLConfig;
	
		$cookiePath = $spgHaloACLConfig['cookiePath'];
		$cookieDomain = $spgHaloACLConfig['cookieDomain'];
		$cookieSecure = $spgHaloACLConfig['cookieSecure'];
		$cookieHttpOnly = $spgHaloACLConfig['cookieHttpOnly'];
		$dbName = $spgHaloACLConfig['wikiDBname'];
		session_name($dbName. '_session' );
		session_set_cookie_params( 0, $cookiePath, $cookieDomain, $cookieSecure, $cookieHttpOnly );
	
		if (session_start()) {
			// Close the session immediately. We just want to read data.
			session_write_close ();
			if (!array_key_exists('wsUserID', $_SESSION)
			|| $_SESSION['wsUserID'] == 0
			|| !array_key_exists('wsUserName', $_SESSION) ) {
				// If wsUserID is 0, no user is logged in 
				// => return the user's IP address
				return $_SERVER['REMOTE_ADDR'];
			}
			return $_SESSION['wsUserName'];
		}
		return NULL;
	
	}
	
	/**
	 * Returns a list of title IDs that are present in a SOLR result. A title ID
	 * consists of the namespace number and the dbkey of a title e.g. 0:Main_Page.
	 * 
	 * @param Object $solrResult
	 * 		An object that represents a SOLR result.
	 */
	private function getTitleIDsFromResult($solrResult) {
		$titles = array();
		
		$docs = @$solrResult->response->docs;
		if ($docs) {
			foreach ($docs as $doc) {
				if (isset($doc->smwh_title) && isset($doc->smwh_namespace_id)) {
					$titleIDs[] = $doc->smwh_namespace_id.':'.$doc->smwh_title;
				}
			}
		}
		return $titleIDs;
	}
	
	/**
	 * Returns an array of wiki page names and namespace IDs that are present in 
	 * a SOLR result.
	 * 
	 * 
	 * @param Object $solrResult
	 * 		An object that represents a SOLR result.
	 * @return array(string => array(string/int))
	 * 		A map with the keys "categories", "properties" and "namespaces". The
	 * 		arrays for "categories" and "properties" contain the title IDs of the
	 * 		corresponding wiki pages (namespace number:dkkey). 
	 * 		The array for "namespaces" contains the	IDs of the namespaces.
	 * 		If the SOLR result is empty, an empty array will be returned.
	 */
	private function getTitlesFromResultFacets($solrResult) {
		$titles = array();
		
		$fields = @$solrResult->facet_counts->facet_fields;
		if (!$fields) {
			return $titles;
		}
		
		global $spgHaloACLConfig;
		// Search in the facets for categories
		$config = array('smwh_categories' => 'categories',
						'smwh_attributes' => 'properties',
						'smwh_properties' => 'properties',
						'smwh_namespace_id' => 'namespaces');
		foreach ($config as $indexField => $key) {
			$fieldContent = @$fields->$indexField;
			$results = array();
			$map = array(); // Map from title IDs to the original title names 
			if ($fieldContent) {
				$wikiElements = get_object_vars($fieldContent);
				
				if ($key === 'categories') {
					$nsIdx = $spgHaloACLConfig['categoryNS'].':';
				} else if ($key === 'properties') {
					$nsIdx = $spgHaloACLConfig['propertyNS'].':';
				} else {
					$nsIdx = '';
				}
				foreach ($wikiElements as $objField => $we) {
					$plainName = $objField;
					if ($key === 'properties') {
						$plainName = $this->extractPropertyName($plainName);
					}
					$tid = $nsIdx.$plainName;
					$results[] = $tid;
					$map[md5($tid)] = $objField; // Use md5 as key as special characters are not supported as key
				}
			}
			if (!array_key_exists($key, $titles)) {
				$titles[$key] = $results;
				$titles[$key.'_map'] = $map;
			} else {
				$titles[$key] = array_merge($titles[$key], $results);
				$titles[$key.'_map'] = array_merge($titles[$key.'_map'], $map);
			}
		}		
		return $titles;
	}
	
	/**
	 * Removes all articles from the $solrResult which are not accessible 
	 * according to $accessRights
	 * 
	 * @param array(array(string title => boolean allowed)) $accessRights
	 * 		The access rights for the articles
	 * @param Object $solrResult
	 * 		The SOLR result that contains the titles to be removed.
	 * 
	 * @return Object
	 * 		The modified $solrResult
	 */
	private function removeDeniedTitles($accessRights, $solrResult) {
		
		// Is the array of $accessRights valid?
		if (count($accessRights) === 0) {
			return $solrResult;
		}
		
		// Create a map from titleIDs to their index in the $solrResult
		$title2Idx = array();
		$docs = &$solrResult->response->docs;
		if (!$docs) {
			return $solrResult;
		}
		
		foreach ($docs as $idx => $doc) {
			$title2Idx[$doc->smwh_namespace_id.':'.$doc->smwh_title] = $idx;
		}
		
		if (isset($solrResult->highlighting)) {
			$highlights = &$solrResult->highlighting;
		}
		
		// Iterate over all access rights and filter out denied titles
		foreach ($accessRights as $permission) {
			foreach ($permission as $t => $allowed) {
				if (!$allowed) {
					// Remove the protected document and the corresponding snippet
					$idx = $title2Idx[$t];
					$id = $docs[$idx*1]->id;
					unset($docs[$idx]);
					if ($highlights) {
						unset($highlights->$id);
					}
				}
			}
		}
		$solrResult->response->docs = array_values($docs);
		return $solrResult;
		
	}
	
	/**
	* Removes protected categories, properties and namespaces from the facets
	* of the given $solrResult.
	*
	 * @param array(string=>bool) $categoryRights
	 * 		Map from category names to the corresponding access right
	 * @param array(string=>bool) $propertyRights
	 * 		Map from property names to the corresponding access right
	 * @param array(int=>bool) $namespaceRights
	 * 		Map from namespace indices to the corresponding access right
	 * @param array $titleIDs
	 * 		Ids of the titles in the facets as returned by getTitlesFromResultFacets
	 * @param Object $solrResult
	 * 		A SOLR result with facets
	 */
	private function removeDeniedTitlesInFacets($categoryRights, $propertyRights, $namespaceRights, $titleIDs, $solrResult) {
	
		$fields = @$solrResult->facet_counts->facet_fields;
		if (!$fields) {
			// No facets in the result i.e. nothing to filter
			// => do not modify the result
			return $solrResult;
		}
		
		// Search for rights that are not allowed
		$deniedCategories = $this->findDeniedElements($categoryRights);
		$deniedProperties = $this->findDeniedElements($propertyRights);
		$deniedNamespaces = $this->findDeniedElements($namespaceRights);
		
		global $spgHaloACLConfig;
		// Search in the facets for categories
		$config = array('smwh_categories' => 'categories',
						'smwh_attributes' => 'properties',
						'smwh_properties' => 'properties',
						'smwh_namespace_id' => 'namespaces');
		foreach ($config as $indexField => $key) {
			$fieldContent = @$fields->$indexField;
			$results = array();
			if ($fieldContent) {
				if ($key === 'categories') {
					$titleMap = $titleIDs['categories_map'];
					// Remove protected categories from the facet
					foreach ($deniedCategories as $cat) {
						// The original category name is stored in $titleMap
						$cat = $titleMap[md5($cat)];
						if (isset($fieldContent->$cat)) {
							unset($fieldContent->$cat);
						}
					}
				} else if ($key === 'properties') {
					$titleMap = $titleIDs['properties_map'];
					// Remove protected properties from the facet
					foreach ($deniedProperties as $prop) {
						// The original property name is stored in $titleMap
						$prop = $titleMap[md5($prop)];
						if (isset($fieldContent->$prop)) {
							unset($fieldContent->$prop);
						}
					}
				} else {
					// Remove protected namespaces from the facet
					foreach ($deniedNamespaces as $ns) {
						if (isset($fieldContent->$ns)) {
							unset($fieldContent->$ns);
						}
					}
				}
			}
		}
		
		// The remaining results may still contain protected properties in their
		// facets.
		if (count($deniedProperties) > 0) {
			$docs = $solrResult->response->docs;
			$titleMap = $titleIDs['properties_map'];
			foreach ($deniedProperties as $prop) {
				// The original property name is stored in $titleMap
				$propsToRemove[] = $titleMap[md5($prop)];
			}
			
			if (isset($solrResult->highlighting)) {
				$highlights = &$solrResult->highlighting;
			}
			foreach ($docs as $doc) {
				// Iterate over all attributes/properties that are protected and
				// must be removed.
				foreach (array('smwh_attributes', 'smwh_properties') as $field) {
					$docField = $doc->$field;
					if ($docField) {
						// Remove protected properties from the document field
						$numProps = count($docField);
						$doc->$field = array_values(array_diff($docField, $propsToRemove));
						if (count($doc->$field) !== $numProps && $highlights) {
							// Properties were removed.
							// => Place a notice in the snippet for the document
							$docID = $doc->id;
							$snippet = @$highlights->$docID;
							$snippetFields = array_keys(get_object_vars($snippet));
							foreach ($snippetFields as $sf) {
								foreach ($snippet->$sf as $key => &$value) {
									$value = FSMessages::msg('snippet_removed');
								}
							}
						}
					}
				}
			}
		}
		
		// Remove snippets if properties were filtered from the result. 
				
		return $solrResult;
	}
	
	
	/**
	 * The name of a SOLR field may contain a property name. This method tries
	 * to extract this name and returns it.
	 * 
	 * @param string $solrFieldName
	 * 		Name of the SOLR field
	 * 
	 * @return mixed string / bool
	 * 		The name of the property of false if the $solrFieldName does not
	 * 		contain a property name.
	 * 
	 */ 
	private function extractPropertyName($solrFieldName) {
		if (preg_match("/smwh_(.*?)_xsdvalue.*/", $solrFieldName, $match)) {
			return $match[1];
		} else if (preg_match("/smwh_(.*?)_t.*/", $solrFieldName, $match)) {
			return $match[1];
		} else {
			return false;
		}
	}
	
	/**
	 * Returns the names of elements (categories, properties or namespaces)
	 * for whom access is denied as given in the array $rights.
	 * 
	 * @param array(array(string=>permission)) $rights
	 * 		An array of access rights with the names of the elements and the
	 * 		corresponding permission.
	 * @return array(string)
	 * 		Names of elements with denied access.
	 */
	private function findDeniedElements(array $rights) {
		$denied = array();
		foreach ($rights as $right) {
			foreach ($right as $elem => $permission) {
				if ($permission === false) {
					// Access denied => store the name
					$denied[] = $elem;
				}
			}
		}
		return $denied;
	}
	
}