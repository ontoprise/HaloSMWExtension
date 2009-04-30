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
	private $mXY;    		//string: comment
	
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
		
		$articleID = $title->getArticleID();
		$userID = $user->getId();
		
		// first check page rights
		$r = self::hasRight($articleID, HACLSecurityDescriptor::PET_PAGE,
		                    $userID, $action);
		if ($r) {
			$result = true;
			return true;
		}
		
		// check namespace rights
		
		// check category rights
		
		return true;
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
	
}