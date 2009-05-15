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
//	const XY= 0;		// the result has been added since the last time
		
	//--- Private fields ---
	private static $mValidActions = array('read', 'formedit', 'annotate',
	                                      'wysiwyg', 'edit', 'create', 'move',
										  'delete');
	
	/**
	 * Constructor for  HACLEvaluator
	 *
	 * @param type $param
	 * 		Name of the notification
	 */		
	function __construct() {
//		$this->mXY = $xy;
	}
	

	//--- getter/setter ---
//	public function getXY()           {return $this->mXY;}

//	public function setXY($xy)               {$this->mXY = $xy;}
	
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
		// reading the page "Permission denied" is allowed.
		global $haclgContLang;
		if ($title->getText() == $haclgContLang->getPermissionDeniedPage()
			&& $action == 'read') {
			$result = true;
			return true;
	    }
	    
		//Special handling of action "wysiwyg". This is passed as 
		// "action=edit&mode=wysiwyg"
		if ($action == 'edit') {
			global $wgRequest;
			$action = $wgRequest->getVal('mode', 'edit');
		}
		
		// Reject unknown actions
		if (!in_array($action, self::$mValidActions)) {
			$result = false;
			return false;
		}
	    
		$articleID = (int) $title->getArticleID();
		$userID = $user->getId();
		
		if ($articleID == 0) {
			// The article does not exist yet
			if ($action == 'create' || $action == 'edit') {
				// Check if the user is allowed to create an SD
				$allowed = self::checkSDCreation($title, $user);
				if ($allowed == false) {
					$result = false;
					return false;
				}
			}
			//TODO: Check if the article belongs to a namespace with an SD
			$result = true;
			return true;
		}
		// Check if there is a security descriptor for the article.
		$hasSD = HACLSecurityDescriptor::getSDForPE($articleID) !== false;
		
		// first check page rights
		if ($hasSD) {
			$r = self::hasRight($articleID, HACLSecurityDescriptor::PET_PAGE,
			                    $userID, $action);
			if ($r) {
				$result = true;
				return true;
			}
		}
		
		// check namespace rights
		
		// check category rights
		
		// check the whitelist
		if (HACLWhitelist::isInWhitelist($articleID) && $action == 'read') {
			// articles in the whitelist can be read
			$result = true;
			return true;
		}
		
		if (!$hasSD) {
			global $haclgOpenWikiAccess;
			// Articles with no SD are not protected if $haclgOpenWikiAccess is
			// true. Otherwise access is denied
			$result = $haclgOpenWikiAccess;
			return $haclgOpenWikiAccess;
		}
		
//TODO: while the rights are not complete
//		$result = true;
//		return true;

		// permission denied
		$result = false;
		return false;
	}

	
	/**
	 * Checks, if the given user has the right to perform the given action on
	 * the given title. The hierarchy of categories is not considered here.
	 *
	 * @param int $titleID
	 * 		ID of the protected object
	 * @param string $peType
	 * 		The type of the protection to check for the title. One of
	 * 		HACLSecurityDescriptor::PET_PAGE
	 * 		HACLSecurityDescriptor::PET_CATEGORY
	 * 		HACLSecurityDescriptor::PET_NAMESPACE
	 * 		HACLSecurityDescriptor::PET_PROPERTY
	 * @param int $userID
	 * 		ID of the user who wants to perform an action
	 * @param string $action
	 * 		The action, the user wants to perform. One of "read", "formedit", 
	 *      "edit", "annotate", "create", "move" and "delete".
	 * @return bool
	 * 		<true>, if the user has the right to perform the action
	 * 		<false>, otherwise
	 */
	public static function hasRight($titleID, $type, $userID, $action) {
		$actionID = 0;
		// retrieve all appropriate rights from the database
		switch ($action) {
			case "read":
				$actionID = HACLRight::READ;
				break;
			case "formedit":
				$actionID = HACLRight::FORMEDIT;
				break;
			case "wysiwyg":
				$actionID = HACLRight::WYSIWYG;
				break;
			case "edit":
				$actionID = HACLRight::EDIT;
				break;
			case "annotate":
				$actionID = HACLRight::ANNOTATE;
				break;
			case "create":
				$actionID = HACLRight::CREATE;
				break;
			case "move":
				$actionID = HACLRight::MOVE;
				break;
			case "delete":
				$actionID = HACLRight::DELETE;
				break;
			default:
				return false;
		}
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
	
	//--- Private methods ---
	
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
		global $haclgEnableTitleCheck;
		$etc = $haclgEnableTitleCheck;
		$haclgEnableTitleCheck = false;
		$t = Title::newFromText($peName);
		$haclgEnableTitleCheck = $etc;
		$article = new Article($t);
		if (!$article->exists()) {
			// article does not exist => no applicable
			return "n/a";
		}
		$authors = $article->getLastNAuthors(1);
		
		return $authors[0] == $user->getName();
		
	}
}