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
 * @ingroup HaloACL
 *
 * This file contains several ajax functions for retrieving access rights on
 * certain wiki elements e.g. articles, articles that are instances of categories,
 * properties etc.
 * 
 * @author Thomas Schweitzer
 * Date: 20.03.2012
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the HaloACL module. It is not a valid entry point.\n" );
}


/**
 * Define the list of ajax functions.
 * 
 */
$wgAjaxExportList[] = "haclAarCheckArticleAccess";
$wgAjaxExportList[] = "haclAarCheckArticleAccessMulti";
$wgAjaxExportList[] = "haclAarCheckCategoryAccessMulti";
$wgAjaxExportList[] = "haclAarCheckPropertyAccessMulti";
$wgAjaxExportList[] = "haclAarCheckNamespaceAccessMulti";


/**
* Checks if the current user can perform the given $action on the article with
* the given $title.
*
* @param string $title
* 		Name of the article
* @param string $action
* 		Name of the action
* @param int $namespaceID
* 		ID of the namespace of the title
*
* @return bool/int
* 		<true> if the action is permitted
* 		<false> otherwise
* 		-1 if the title is invalid
*/
function haclAarCheckArticleAccess($title, $action, $namespaceID = 0) {
	$etc = haclfDisableTitlePatch();
	$title = Title::newFromText($title, $namespaceID);
	haclfRestoreTitlePatch($etc);

	if (!$title) {
		return -1;
	}

	// Check all MW access rights including all userCan hooks
	$result = $title->userCan($action);
	return $result ? 'true' : 'false';
}

/**
 * Checks if the current user can perform the given $action on the articles with
 * the given $titles.
 *
 * @param string $titles
 * 		Comma separated list of article names, possibly with namespace number
 * @param string $action
 * 		Name of the action
 * @param bool $titlesHaveNSID
 * 		If true, the $titles have a namespace number e.g. 0:Main_Page
 *
 * @return bool
 * 		A JSON encoded array of results:
 * 		array(
 * 			array(titlename, allowed or not: true/false),
 * 			...
 * 		)
 */
function haclAarCheckArticleAccessMulti($titles, $action, $titlesHaveNSID = false) {
	// Special handling if the extension HaloACL is present
	global $wgUser;

	// Split the $categories at commas if they are not escaped
	$titles = preg_split("#(?<!\\\)\,#", $titles);
	
	$etc = haclfDisableTitlePatch();
	$results = array();
	foreach ($titles as $t) {
		// replace escaped commas
		$t = str_replace('\,', ',', $t);
		
		$result = true;
		if ($titlesHaveNSID) {
			list($ns, $tname) = explode(':', $t, 2);
			$title = Title::newFromText(trim($tname), trim($ns)*1);
		} else {
			$title = Title::newFromText(trim($t));
		}
		if (!$title) {
			// Invalid ID or name given => try the next title
			continue;
		}
		$result = $title->userCan($action);
		if (isset($result) && $result === false) {
			$results[] = array($t, "false");
		} else {
			$results[] = array($t, "true");
		}
			
	}
	haclfRestoreTitlePatch($etc);
	return json_encode($results);
}


/**
* Checks if the current user can perform the given $action on the articles that
* belong to the given categories.
*
* @param string $categories
* 		Comma separated list of category names without namespace.
* @param string $action
* 		Name of the action
*
* @return bool
* 		A JSON encoded array of results:
* 		array(
* 			array(categoryname, allowed or not: true/false),
* 			...
* 		)
*/
function haclAarCheckCategoryAccessMulti($categories, $action) {
	global $wgUser;
	
	// Split the $categories at commas if they are not escaped
	$categories = preg_split("#(?<!\\\)\,#", $categories);
	
	$results = array();
	$hmc = HACLMemcache::getInstance();
	foreach ($categories as $cat) {
		// replace escaped commas
		$cat = str_replace('\,', ',', $cat);
		
		$catTitle = Title::newFromText($cat, NS_CATEGORY);
		$allowed = $hmc->retrievePermission($wgUser, $catTitle, 'category-'.$action);
		if ($allowed === -1) {
			$allowed = HACLEvaluator::getCategoryRight($cat, $action);
			if ($allowed !== -1) {
				$hmc->storePermission($wgUser, $catTitle, 'category-'.$action, $allowed);
			}
		}
		
		if (isset($allowed) && $allowed === false) {
			$results[] = array($cat, "false");
		} else if ($allowed === true){
			$results[] = array($cat, "true");
		}
			
	}
	return json_encode($results);
	
}

/**
* Checks if the current user can perform the given $action on the given
* properties.
*
* @param string $properties
* 		Comma separated list of property names without namespace.
* @param string $action
* 		Name of the action i.e. one of 'read', 'formedit' or 'edit'. 
*
* @return array/string
* 		A JSON encoded array of results:
* 		array(
* 			array(propertyname, allowed or not: true/false),
* 			...
* 		)
* 
* 		'false', if SMW is not initialized
*/
function haclAarCheckPropertyAccessMulti($properties, $action) {
	global $wgUser;
	
	if (!defined('SMW_NS_PROPERTY')) {
		return 'false';
	}
	
	// Split the $properties at commas if they are not escaped
	$properties = preg_split("#(?<!\\\)\,#", $properties);
	
	$results = array();
	$hmc = HACLMemcache::getInstance();
	foreach ($properties as $prop) {
		// replace escaped commas
		$prop = str_replace('\,', ',', $prop);
		
		$propTitle = Title::newFromText($prop, SMW_NS_PROPERTY);
		$allowed = $hmc->retrievePermission($wgUser, $propTitle, 'property-'.$action);
		if ($allowed === -1) {
			$actionID = HACLRight::getActionID($action);
			$allowed = HACLEvaluator::hasPropertyRight($propTitle, $wgUser->getId(), $actionID);
			if ($allowed !== -1) {
				$hmc->storePermission($wgUser, $propTitle, 'property-'.$action, $allowed);
			}
		}
		
		if (isset($allowed) && $allowed === false) {
			$results[] = array($prop, "false");
		} else if ($allowed === true){
			$results[] = array($prop, "true");
		}
			
	}
	return json_encode($results);
	
}


/**
* Checks if the current user can perform the given $action on the given
* namespaces.
*
* @param string $namespaces
* 		Comma separated list of namespace indices.
* @param string $action
* 		Name of the action
*
* @return array
* 		A JSON encoded array of results:
* 		array(
* 			array(namespace index, allowed or not: true/false),
* 			...
* 		)
* 
*/
function haclAarCheckNamespaceAccessMulti($namespaces, $action) {
	global $wgUser;
	
	$namespaces = explode(',', $namespaces);
	$results = array();
	$hmc = HACLMemcache::getInstance();
	foreach ($namespaces as $ns) {
		$ns = $ns + 0;
		$allowed = $hmc->retrievePermission($wgUser, $ns, 'namespace-'.$action);
		if ($allowed === -1) {
			$allowed = HACLEvaluator::getNamespaceRight($ns, $action);
			if ($allowed !== -1) {
				$hmc->storePermission($wgUser, $ns, 'namespace-'.$action, $allowed);
			}
		}
		
		if (isset($allowed) && $allowed === false) {
			$results[] = array($ns, "false");
		} else if ($allowed === true) {
			$results[] = array($ns, "true");
		}
			
	}
	return json_encode($results);
	
}


