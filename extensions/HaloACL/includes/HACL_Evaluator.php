<?php
/*  Copyright 2009, ontoprise GmbH
*   This file is part of the HaloACL-Extension.
*
*   The HaloACL-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The HaloACL-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * This is the main class for the evaluation of user rights for a protected object.
 * It implements the function "userCan" that is called from MW for granting or 
 * denying access to articles.
 * 
 * @author Thomas Schweitzer
 * Date: 13.03.2009
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the HaloACL extension. It is not a valid entry point.\n" );
}

 //--- Includes ---
 global $haclgIP;
//require_once("$haclgIP/...");

/**
 * 
 * 
 * @author Thomas Schweitzer
 * 
 */
class HACLEvaluator {
	
	//--- Constants ---
		
	//--- Private fields ---
	
	/**
	 * Constructor for  HACLEvaluator
	 *
	 * @param type $param
	 * 		Name of the notification
	 */		
	function __construct() {
	}
	

	//--- getter/setter ---
	
	//--- Public methods ---
	
	
	/**
	 * This function is called from the userCan-hook of MW. This method decides
	 * if the article for the given title can be accessed.
	 * See  further information at: http://www.mediawiki.org/wiki/Manual:Hooks/userCan  
	 *
	 * @param Title $title
	 * 		The title object for the article that will be accessed.
	 * @param User $user
	 * 		Reference to the current user.
	 * @param string $action
	 * 		Action concerning the title in question
	 * @param boolean $result
	 * 		Reference to the result propagated along the chain of hooks.
	 * 
	 * @return boolean
	 * 		true
	 */
	public static function userCan($title, $user, $action, &$result) {

		$etc = haclfDisableTitlePatch();

		// Check if property access is requested.
		global $haclgProtectProperties;
		if ($haclgProtectProperties) {
			$r = self::checkPropertyAccess($title, $user, $action);
			if ($r !== -1) {
				$result = $r;
				return $r;
			}
		}
		
		//Special handling of action "wysiwyg". This is passed as 
		// "action=edit&mode=wysiwyg"
		if ($action == 'edit') {
			global $wgRequest;
			$action = $wgRequest->getVal('mode', 'edit');
		}
		
		$actionID = HACLRight::getActionID($action);
		if ($actionID == 0) {
			// unknown action => nothing can be said about this
			haclfRestoreTitlePatch($etc);
			return true;
		}
		
		// no access to the page "Permission denied" is allowed.
		// together with the TitlePatch which returns this page, this leads
		// to MediaWiki's "Permission error"
		global $haclgContLang;
		if ($title->getText() == $haclgContLang->getPermissionDeniedPage()) {
//			$r = $actionID == HACLRight::READ;
			$r = false;
			haclfRestoreTitlePatch($etc);
			$result = $r;
			return $r;
	    }
		
		$articleID = (int) $title->getArticleID();
		if ($articleID == 0) {
			$articleID = haclfArticleID($title->getFullText());
		}
		$userID = $user->getId();
		
		if ($articleID == 0) {
			// The article does not exist yet
			if ($actionID == HACLRight::CREATE || $actionID == HACLRight::EDIT) {
				
				// Check right for creation of default SD template. Users
				// can only create their own template. Sysops and bureaucrats
				// can create them for everyone.
				list ($r, $sd) = HACLDefaultSD::userCanModify($title, $user);
				if ($sd) {
					haclfRestoreTitlePatch($etc);
					$result = $r;
					return $r;
				}
										
				// Check if the user is allowed to create an SD
				$allowed = self::checkSDCreation($title, $user);
				if ($allowed == false) {
					haclfRestoreTitlePatch($etc);
					$result = false;
					return false;
				}
			}

			// Check if the article belongs to a namespace with an SD
		    list($r, $sd) = self::checkNamespaceRight($title, $userID, $actionID);
			haclfRestoreTitlePatch($etc);
		    $result = $r;
			return $r;
		}
		
		// Check rights for managing ACLs
		list($r, $sd) = self::checkACLManager($title, $user, $actionID);
		if ($sd) {
			// User tries to access an ACL article
			haclfRestoreTitlePatch($etc);
			$result = $r;
			return $r;
		}
		
		// Check if the article contains protected properties that avert
		// editing the article
		$allowed = self::checkProperties($title, $userID, $actionID);
		if (!$allowed) {
			haclfRestoreTitlePatch($etc);
			$result = false;
			return false;
		}
		
		// Check if there is a security descriptor for the article.
		$hasSD = HACLSecurityDescriptor::getSDForPE($articleID, HACLSecurityDescriptor::PET_PAGE) !== false;
		
		// first check page rights
		if ($hasSD) {
			$r = self::hasRight($articleID, HACLSecurityDescriptor::PET_PAGE,
			                    $userID, $actionID);
			if ($r) {
				haclfRestoreTitlePatch($etc);
				$result = true;
				return true;
			}
		}
		
		// check namespace rights
		list($r, $sd) = self::checkNamespaceRight($title, $userID, $actionID);
		$hasSD = $hasSD ? true : $sd;
		if ($sd && $r) {
			haclfRestoreTitlePatch($etc);
			$result = true;
			return true;
		}
	
		// check category rights
		list($r, $sd) = self::hasCategoryRight($title->getFullText(), $userID, $actionID);
		$hasSD = $hasSD ? true : $sd;
		if ($sd && $r) {
			haclfRestoreTitlePatch($etc);
			$result = true;
			return true;
		}
		
		// check the whitelist
		if (HACLWhitelist::isInWhitelist($articleID)) {
			$r = $actionID == HACLRight::READ;
			// articles in the whitelist can be read
			haclfRestoreTitlePatch($etc);
			$result = $r;
			return $r;
		}
		
		if (!$hasSD) {
			global $haclgOpenWikiAccess;
			// Articles with no SD are not protected if $haclgOpenWikiAccess is
			// true. Otherwise access is denied
			haclfRestoreTitlePatch($etc);
			$result = $haclgOpenWikiAccess;
			return $haclgOpenWikiAccess;
		}
		
		// permission denied
		haclfRestoreTitlePatch($etc);
		$result = false;
		return false;
	}

	
	/**
	 * Checks, if the given user has the right to perform the given action on
	 * the given title. The hierarchy of categories is not considered here.
	 *
	 * @param int $titleID
	 * 		ID of the protected object (which is the namespace index if the type
	 * 		is PET_NAMESPACE)
	 * @param string $peType
	 * 		The type of the protection to check for the title. One of
	 * 		HACLSecurityDescriptor::PET_PAGE
	 * 		HACLSecurityDescriptor::PET_CATEGORY
	 * 		HACLSecurityDescriptor::PET_NAMESPACE
	 * 		HACLSecurityDescriptor::PET_PROPERTY
	 * @param int $userID
	 * 		ID of the user who wants to perform an action
	 * @param int $actionID
	 * 		The action, the user wants to perform. One of the constant defined
	 * 		in HACLRight: READ, FORMEDIT, WYSIWYG, EDIT, ANNOTATE, CREATE, MOVE and DELETE.
	 * @return bool
	 * 		<true>, if the user has the right to perform the action
	 * 		<false>, otherwise
	 */
	public static function hasRight($titleID, $type, $userID, $actionID) {
		// retrieve all appropriate rights from the database
		$rightIDs = HACLStorage::getDatabase()->getRights($titleID, $type, $actionID);
		
		// Check for all rights, if they are granted for the given user
		foreach ($rightIDs as $r) {
			$right = HACLRight::newFromID($r);
			if ($right->grantedForUser($userID)) {
				return true; 
			}
		}
		
		return false;
		
	}

	/**
	 * Checks, if the given user has the right to perform the given action on
	 * the given property. (This happens only if protection of semantic properties
	 * is enabled (see $haclgProtectProperties in HACL_Initialize.php))
	 *
	 * @param mixed(Title|int) $propertyTitle
	 * 		ID or title of the property whose rights are evaluated
	 * @param int $userID
	 * 		ID of the user who wants to perform an action
	 * @param int $actionID
	 * 		The action, the user wants to perform. One of the constant defined
	 * 		in HACLRight: READ, FORMEDIT, EDIT
	 * @return bool
	 *		<true>, if the user has the right to perform the action
	 * 		<false>, otherwise
	 */
	public static function hasPropertyRight($propertyTitle, $userID, $actionID) {
		global $haclgProtectProperties;
		if (!$haclgProtectProperties) {
			// Protection of properties is disabled.
			return true;
		}
		
		if ($propertyTitle instanceof Title) {
			$propertyTitle = $propertyTitle->getArticleID();
		}
		
		$hasSD = HACLSecurityDescriptor::getSDForPE($propertyTitle, HACLSecurityDescriptor::PET_PROPERTY) !== false;
			
		if (!$hasSD) {
			global $haclgOpenWikiAccess;
			// Properties with no SD are not protected if $haclgOpenWikiAccess is
			// true. Otherwise access is denied
			return $haclgOpenWikiAccess;
		}
		return self::hasRight($propertyTitle, 
							  HACLSecurityDescriptor::PET_PROPERTY,
		                      $userID, $actionID);
		
	}
		
	//--- Private methods ---
	
	/**
	 * Checks, if the given user has the right to perform the given action on
	 * the given title. The hierarchy of categories is evaluated.
	 *
	 * @param mixed string|array<string> $parents
	 * 		If a string is given, this is the name of an article whose parent
	 * 		categories are evaluated. Otherwise it is an array of parent category 
	 * 		names
	 * @param int $userID
	 * 		ID of the user who wants to perform an action
	 * @param int $actionID
	 * 		The action, the user wants to perform. One of the constant defined
	 * 		in HACLRight: READ, FORMEDIT, EDIT, ANNOTATE, CREATE, MOVE and DELETE.
	 * @param array<string> $visitedParents
	 * 		This array contains the names of all parent categories that were already
	 * 		visited.
	 * @return array(bool rightGranted, bool hasSD)
	 * 		rightGranted:
	 *	 		<true>, if the user has the right to perform the action
	 * 			<false>, otherwise
	 * 		hasSD:
	 * 			<true>, if there is an SD for the article
	 * 			<false>, if not
	 */
	private static function hasCategoryRight($parents, $userID, $actionID, 
	                                        $visitedParents = array()) {
	    if (is_string($parents)) {
	    	// The article whose parent categories shall be evaluated is given
	    	$t = Title::newFromText($parents);
	    	return self::hasCategoryRight(array_keys($t->getParentCategories()),$userID, $actionID);
	    } else if (is_array($parents)) {
	    	if (empty($parents)) {
	    		return array(false, false);
	    	}
	    } else {
	    	return array(false, false);
	    }
	    
		// Check for each parent if the right is granted
		$parentTitles = array();
	    $hasSD = false;                   	
	    foreach ($parents as $p) {
	    	$parentTitles[] = $t = Title::newFromText($p);
	    	
			if (!$hasSD) {
				$hasSD = (HACLSecurityDescriptor::getSDForPE($t->getArticleID(), HACLSecurityDescriptor::PET_CATEGORY) !== false);
			}
			$r = self::hasRight($t->getArticleID(), HACLSecurityDescriptor::PET_CATEGORY,
			                    $userID, $actionID);
			if ($r) {
				return array(true, $hasSD);			                    
			}
		}
		
		// No parent category has the required right
		// => check the next level of parents
		$parents = array();
		foreach ($parentTitles as $pt) {
			$ptParents = array_keys($pt->getParentCategories());
			foreach ($ptParents as $p) {
				if (!in_array($p, $visitedParents)) {
			    	$parents[] = $p;
			    	$visitedParents[] = $p;
			    }
			}
		}
		
		// Recursively check all parents
		list($r, $sd) = self::hasCategoryRight($parents, $userID, $actionID, $visitedParents);
		return array($r, $sd ? true : $hasSD);
		
	}
	
	
	/**
	 * This method is important if the mode of the access control is 
	 * "closed wiki access". If the wiki access is open, articles without security
	 * descriptor have full access. If it is closed, nobody can access the article
	 * until a security descriptor is defined. Only the latest author of the article
	 * can do this. This method checks, if a security descriptor can be created.
	 *
	 * @param Title $title
	 * 		Title of the article that will be created
	 * @param User $user
	 * 		User who wants to create the article
	 * @return bool|string
	 * 		<true>, if the user can create the security descriptor
	 * 		<false>, if not
	 * 		"n/a", if this method is not applicable for the given article creation 
	 */
	private static function checkSDCreation($title, $user) {
		global $haclgOpenWikiAccess;
		if ($haclgOpenWikiAccess) {
			// the wiki is open => not applicable
			return "n/a";
		}
		if ($title->getNamespace() != HACL_NS_ACL) {
			// The title is not in the ACL namespace => not applicable
			return "n/a";
		}
		
		list($peName, $peType) = HACLSecurityDescriptor::nameOfPE($title->getText());
		if ($peType != HACLSecurityDescriptor::PET_PAGE &&
		    $peType != HACLSecurityDescriptor::PET_PROPERTY) {
		    // only applicable to pages and properties
		    return "n/a";
		}
		
		// get the latest author of the protected article
		$t = Title::newFromText($peName);
		$article = new Article($t);
		if (!$article->exists()) {
			// article does not exist => no applicable
			return "n/a";
		}
		$authors = $article->getLastNAuthors(1);
		
		return $authors[0] == $user->getName();
		
	}
	
	/**
	 * Checks if access is granted to the namespace of the given title.
	 *
	 * @param Title $t
	 * 		Title whose namespace is checked
	 * @param int $userID
	 * 		ID of the user who want to access the namespace
	 * @param int $actionID
	 * 		ID of the action the user wants to perform
	 * 
	 * @return array(bool rightGranted, bool hasSD)
	 * 		rightGranted:
	 *	 		<true>, if the user has the right to perform the action
	 * 			<false>, otherwise
	 * 		hasSD:
	 * 			<true>, if there is an SD for the article
	 * 			<false>, if not
	 *  
	 */
	private static function checkNamespaceRight(Title $t, $userID, $actionID) {
		$nsID = $t->getNamespace();
		$hasSD = HACLSecurityDescriptor::getSDForPE($nsID, HACLSecurityDescriptor::PET_NAMESPACE) !== false;
			
		if (!$hasSD) {
			global $haclgOpenWikiAccess;
			// Articles with no SD are not protected if $haclgOpenWikiAccess is
			// true. Otherwise access is denied
			return array($haclgOpenWikiAccess, false);
		}
		
		return array(self::hasRight($nsID, HACLSecurityDescriptor::PET_NAMESPACE,
		                            $userID, $actionID), $hasSD);
		
	}
	
	/**
	 * This method checks if a user wants to modify an articles in the namespace
	 * ACL.
	 *
	 * @param Title $t
	 * 		The title.
	 * @param User $user
	 * 		User-object of the user.
	 * @param int $actionID
	 * 		ID of the action. The actions FORMEDIT, WYSIWYG, EDIT, ANNOTATE, 
	 *      CREATE, MOVE and DELETE are relevant for managing an ACL object.
	 * 
	 * @return array(bool rightGranted, bool hasSD)
	 * 		rightGranted:
	 *	 		<true>, if the user has the right to perform the action
	 * 			<false>, otherwise
	 * 		hasSD:
	 * 			<true>, if there is an SD for the article
	 * 			<false>, if not
	 */
	private static function checkACLManager(Title $t, $user, $actionID) {
		if ($t->getNamespace() != HACL_NS_ACL) {
			return array(true, false);
		}
		
		$userID = $user->getId();
		if ($userID == 0) {
			// No access for anonymous users
			return array(false, true);
		}

		// Check if this is a default SD template
		list($r, $sd) = HACLDefaultSD::userCanModify($t, $user);
		if ($sd) {
			return array($r, $sd);
		}
		
		if ($actionID == HACLRight::READ) {
			// Read access for all registered users
			return array(true, true);
		}

		// Check for groups
		try {
			$group = HACLGroup::newFromID($t->getArticleID());
			return array($group->userCanModify($userID), true);
		} catch (HACLGroupException $e) {
			// Check for security descriptors
			try {
				$sd = HACLSecurityDescriptor::newFromID($t->getArticleID());
				return array($sd->userCanModify($userID), true);
			} catch (HACLSDException $e) {
				// Check for the Whitelist
				global $haclgContLang;
				if ($t->getText() == $haclgContLang->getWhitelist(false)) {
					// User must be a sysop
					return array(HACLWhitelist::userCanModify($userID), true);
				}
			}
		}
		
		// Sysops and bureaucrats can modify the SD
		$groups = $user->getGroups();
		if (in_array('sysop', $groups) || in_array('bureaucrat', $groups)) {
			return array(true, true);
		}
		return array(false, true);
	}
	
	/**
	 * This method checks if a user wants to edit an article with protected 
	 * properties. (This happens only if protection of semantic properties
	 * is enabled (see $haclgProtectProperties in HACL_Initialize.php))
	 *
	 * @param Title $t
	 * 		The title.
	 * @param int $userID
	 * 		ID of the user.
	 * @param int $actionID
	 * 		ID of the action. The actions FORMEDIT, WYSIWYG, EDIT, ANNOTATE, 
	 *      CREATE, MOVE and DELETE are relevant for managing an ACL object.
	 * 
	 * @return bool
	 * 		rightGranted:
	 *	 		<true>, if the user has the right to perform the action
	 * 			<false>, otherwise
	 */
	private static function checkProperties(Title $t, $userID, $actionID) {
		global $haclgProtectProperties;
		if (!$haclgProtectProperties) {
			// Properties are not protected.
			return true;
		}
		
		// Articles with protected properties are protected if an unauthorized
		// user wants to edit it
		if ($actionID != HACLRight::WYSIWYG &&
			$actionID != HACLRight::EDIT &&
			$actionID != HACLRight::ANNOTATE) {

			global $wgRequest;
			$a = @$wgRequest->data['action'];
			if (isset($a)) {
				// Some web request are translated to other actions before they
				// are passed to the userCan hook. E.g. action=history is passed
				// as action=read.
				// Articles with protected properties can be viewed, because the
				// property values are replaced by dummy text but showing the wikitext
				// (e.g. in the history) must be prohibited.
				
				// Define exceptions for actions that display only rendered text
				static $actionExceptions = array("purge","render","raw");
				if (in_array($a,$actionExceptions)) {
					return true;
				}
				
			} else {
				return true;
			}	
		}
		// Get all properties of the page
		$semdata = smwfGetStore()->getSemanticData($t);
		$props = $semdata->getProperties();
		foreach ($props as $p) {
			if (!$p->isShown()) {
				// Ignore invisible(internal) properties
				continue;
			}
			// Check if a property is protected
			$t = $p->getWikiPageValue()->getTitle();
			if (!self::hasPropertyRight($t, $userID, $actionID)) {
				return false;
			}
		}
		return true;
	}
	
	/**
	 * Checks if access to a property should be evaluated. This is the case if
	 * the string $action is one of 'propertyread', 'propertyformedit' or 
	 * 'propertyedit'. 
	 *
	 * @param Title $title
	 * 		Title object for the property whose rights are checked.
	 * @param User $user
	 * 		User who wants to access the property
	 * @param string $action
	 * 		If this is one of 'propertyread', 'propertyformedit' or 'propertyedit'
	 * 		property rights are checked
	 * @return bool / int
	 * 		<true>:  Access to the property is granted.
	 * 		<false>: Access to the property is denied.
	 *      -1: $action is not concerned with properties.
	 */
	private static function checkPropertyAccess(Title $title, User $user, $action) {
		switch ($action) {
			case 'propertyread':
				$actionID = HACLRight::READ;
				break;
			case 'propertyformedit':
				$actionID = HACLRight::FORMEDIT;
				break;
		    case 'propertyedit':
				$actionID = HACLRight::EDIT;
		    	break;
		    default:
		    	// No property access requested
		    	return -1;
		}
		return self::hasPropertyRight($title, $user->getId(), $actionID);
	}
}