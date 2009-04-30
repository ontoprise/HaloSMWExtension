<?php
/*  Copyright 2009, ontoprise GmbH
*  This file is part of the HaloACL-Extension.
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
 * This file contains the class HACLSecurityDescriptor.
 * 
 * @author Thomas Schweitzer
 * Date: 15.04.2009
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the HaloACL extension. It is not a valid entry point.\n" );
}

 //--- Includes ---
 global $haclgIP;
//require_once("$haclgIP/...");

/**
 * This class describes security descriptors or predefined rights in HaloACL.
 * 
 * Pages, categories, namespaces and properties are protected by a security
 * descriptor (SD). An SD is an article (with certain naming conventions) that
 * contains the rules that are applied to the protected object.
 * 
 * Each SD has an ID (the page ID of the article that contains the SD),
 * the page ID of the protected element, a type (page, category, namespace,
 * property) and a list of users and groups who can modify the SD.
 * 
 * Predefined rights have the same structure as security descriptors. So in the
 * following, SD is used as synonym for predefined right. SDs and PRs can be 
 * distinguished by their type $mPEType. 
 * 
 * @author Thomas Schweitzer
 * 
 */
class  HACLSecurityDescriptor  {

	//--- Constants ---

	//---- Types of protected elements ----
 	const PET_PAGE      = 'page'; 		// Protect pages
	const PET_CATEGORY  = 'category';	// Protect instances of a category
	const PET_NAMESPACE = 'namespace';	// Protect instances of a namespace
	const PET_PROPERTY  = 'property';   // Protect values of a property
	const PET_RIGHT     = 'right';		// This is not an actual security descriptor
								// but a predefined right that aquivalent to
								// an SD by its structure.

	
	//--- Private fields ---
	private $mSDID;    		// int: Page ID of the article that defines this SD
	private $mPEID;			// int: Page ID of the protected element
	private $mSDName;		// string: The name of this SD
	private $mPEType;		// int: Type of the protected element (see constants PET_... above)
	private $mManageGroups;	// array(int): IDs of the groups that can modify 
							//		the definition of this SD
	private $mManageUsers;	// array(int): IDs of the users that can modify 
							//		the definition of this SD
	
	/**
	 * Constructor for HACLSecurityDescriptor. If your create a new security
	 * descriptor, you have to call the method <save> to stored it in the 
	 * database. If you create a new predefined right, its is automatically
	 * saved, when it is added to another SD or PR.
	 *
	 * @param int/string $SDID
	 * 		Article's page ID. If <null>, the class tries to find the correct ID
	 * 		by the given $SDName. Of course, this works only for existing
	 * 		security descriptors.
	 * @param string $SDName
	 * 		Name of the SD
	 * @param int/string $peID
	 * 		Page/namespace ID or name of the protected element. This should be
	 * 		0 if $peType is PET_RIGHT.
	 * @param int $peType
	 * 		Type of the protected element (see constants PET_...)
	 * @param array<int/string>/string $manageGroups
	 * 		An array or a string of comma separated group names or IDs that 
	 *      can modify the SD's definition. Group names are converted and 
	 *      internally stored as group IDs. Invalid values cause an exception.
	 * @param array<int/string>/string $manageUsers
	 * 		An array or a string of comma separated user names or IDs that
	 *      can modify the SD's definition. User names are converted and 
	 *      internally stored as user IDs. Invalid values cause an exception.
	 * @throws 
	 * 		HACLSDException(HACLSDException::NO_PE_ID)
	 * 		HACLSDException(HACLSDException::UNKOWN_GROUP)
	 * 		HACLException(HACLException::UNKOWN_USER)
	 * 	 
	 */		
	function __construct($SDID, $SDName, $peID, $peType, $manageGroups, $manageUsers) {
		
		if (is_null($SDID)) {
			$SDID = self::idForSD($SDName);
		}
		$this->mSDID   = 0+$SDID;
		$this->mSDName = $SDName;
		if (is_string($peID)) {
			$peName = $peID;
			$peID = self::peIDforName($peID, $peType);
			if ($peID === false) {
				throw new HACLSDException(HACLSDException::NO_PE_ID, $peName, $peType);
			}
		}
		$this->mPEID   = $peID;
		$this->mPEType = $peType;
		if ($peType == self::PET_RIGHT) {
			$this->mPEID = 0;
		}
				
		if (!empty($manageGroups) && is_string($manageGroups)) {
			// Managing groups are given as comma separated string
			// Split into an array
			$manageGroups = explode(',', $manageGroups);
		}
		if (is_array($manageGroups)) {
			$this->mManageGroups = $manageGroups;
			for ($i = 0; $i < count($manageGroups); ++$i) {
				$mg = $manageGroups[$i];
				if (is_int($mg)) {
					// do nothing
				} else if (is_numeric($mg)) {
					$this->mManageGroups[$i] = (int) $mg;
				} else if (is_string($mg)) {
					// convert a group name to a group ID
					$gid = HACLGroup::idForGroup(trim($mg));
					if (!$gid) {
						throw new HACLSDException(HACLSDException::UNKOWN_GROUP, 
						                          $SDName, $mg);
					}
					$this->mManageGroups[$i] = $gid; 
				}
			}
		} else {
			$this->mManageGroups = array();
		}

		if (!empty($manageUsers) && is_string($manageUsers)) {
			// Managing users are given as comma separated string
			// Split into an array
			$manageUsers = explode(',', $manageUsers);
		}
		if (is_array($manageUsers)) {
			$this->mManageUsers = $manageUsers;
			for ($i = 0; $i < count($manageUsers); ++$i) {
				$mu = $manageUsers[$i];
				if (is_int($mu)) {
					// do nothing
				} else if (is_numeric($mu)) {
					$this->mManageUsers[$i] = (int) $mu;
				} else if (is_string($mu)) {
					// convert a user name to a group ID
					$uid = User::idFromName(trim($mu));
					if (!$uid) {
						throw new HACLException(HACLException::UNKOWN_USER, $mu);
					}
					$this->mManageUsers[$i] =  $uid;
				}
			}
		} else {
			$this->mManageUsers = array();
		}
		
	}
	
	//--- getter/setter ---

	public function getSDID()			{return $this->mSDID;}
	public function getPEID()			{return $this->mPEID;}
	public function getSDName()			{return $this->mSDName;}
	public function getPEType()			{return $this->mPEType;}
	public function getManageGroups()	{return $this->mManageGroups;}
	public function getManageUsers()	{return $this->mManageUsers;}
		
//	public function setXY($xy)               {$this->mXY = $xy;}
	
	//--- Public methods ---
	
	/**
	 * Returns the ID of a protected element that is given by its name. The ID
	 * depends on the type of the protected element:
	 * -PET_PAGE: ID of the article that is protected
	 * -PET_NAMESPACE: ID of the namespace that is protected
	 * -PET_CATEGORY: ID of the category article whose instances are protected
	 * -PET_PROPERTY: ID of the property article whose values are protected
	 * -PET_RIGHT: not applicable
	 *
	 * @param string $peName
	 * 		Name of the protected object. For categories and properties, the 
	 * 		namespace must be given
	 * @param int $peType
	 * 		Type of the protected element. See PET-... constants of this class.
	 * 
	 * @return int/bool
	 * 		ID of the protected element or <false>, if it does not exist.
	 */
	public static function peIDforName($peName, $peType) {
		if ($peType === self::PET_NAMESPACE) {
			// $peName is a namespace => get its ID
			global $wgContLang;
			$idx = $wgContLang->getNsIndex($peName);
			return $idx;
		} else if ($peType === self::PET_RIGHT) {
			return false;
		}
		// return the page id
		$nt = Title::newFromText($peName);
		if  (is_null($nt)) {
			# Illegal name
			return false;
		}
		
		return $nt->getArticleID();
		
	}
	
	/**
	 * Creates a new SD object based on the name of the SD. The SD must
	 * exists in the database.
	 * 
	 * @param string $SDName
	 * 		Name of the SD.
	 * 
	 * @return HACLSecurityDescriptor
	 * 		A new SD object.
	 * @throws 
	 * 		HACLSDException(HACLSDException::UNKNOWN_SD)
	 * 			...if the requested SD in the not the database.
	 */
	public static function newFromName($SDName) {
		$id = self::idForSD($SDName);
		if (!$id) {
			throw new HACLSDException(HACLSDException::UNKNOWN_SD, $SDName);
		}
		return self::newFromID($id);
	}

	/**
	 * Creates a new SD object based on the ID of the SD. The SD must
	 * exists in the database.
	 * 
	 * @param int $SDID
	 * 		ID of the SD i.e. the ID of the article that defines the SD.
	 * 
	 * @return HACLSecurityDescriptor
	 * 		A new SD object.
	 * @throws 
	 * 		HACLSDException(HACLSDException::UNKNOWN_SD)
	 * 			...if the requested SD in the not the database.
	 */
	public static function newFromID($SDID) {
		
		$sd = HACLStorage::getDatabase()->getSDByID($SDID);
		if ($sd === null) {
			throw new HACLSDException(HACLSDException::UNKNOWN_SD, $SDID);
		}
		return $sd;
	}
	
	/**
	 * Returns the page ID of the article that defines the SD with the name
	 * $SDName.
	 *
	 * @param string $SDName
	 * 		Name of the SD
	 * 
	 * @return int
	 * 		The ID of the SD's article or <null> if the article does not exist.
	 * 
	 */
	public static function idForSD($SDName) {
		$nt = Title::newFromText($SDName, HACL_NS_ACL);
		if  (is_null($nt)) {
			# Illegal name
			return null;
		}
		
		return $nt->getArticleID();
	}
	
	/**
	 * Returns the name of the SD with the ID $SDID. This is the ID of the article
	 * that defines the SD.
	 *
	 * @param int $SDID
	 * 		ID of the SD whose name is requested
	 * 
	 * @return string
	 * 		Name of the SD with the given ID or <null> if there is no article
	 * 		that defines this SD
	 */
	public static function nameForID($SDID) {
		$nt = Title::newFromID($SDID);
		return ($nt) ? $nt->getText() : null;
	}
	
	/**
	 * Saves the given predefined rights (PR) and adds them to this security 
	 * descriptor (SD).
	 * 
	 * PRs and SDs have the same structure and are both managed in the class
	 * HACLSecurityDescriptor. The type of PRs is PET_RIGHT. You can add PRs to PRs
	 * and SDs, but not SDs to SDs. 
	 * 
	 * The hierarchy of rights in the database is immediately updated. 
	 * For each protected element (e.g. a page) all rights are stored in the
	 * database. The hierarchy of rights is materialized in the DB. If a PR is 
	 * added to an SD, add sub-rights of the PR are added to the element that 
	 * is protected by the SD. If a PR is added to a PR, all sub-rights are added
	 * to all elements whose SD includes the parent PR. 
	 * For better performance, all predefined rights should be added at once and
	 * not one after another.
	 * 
	 * @param array<HACLSecurityDescriptor> $rights
	 * 		These rights are added. The type of their security descriptors must be
	 * 		PET_RIGHT.
	 * 
	 * @param User/string/int $user
	 * 		User-object, name of a user or ID of a user who wants to delete this
	 * 		SD. If <null> (default), the currently logged in user is assumed.
	 * 
	 * @throws
	 * 	    HACLSDException(HACLSDException::USER_CANT_MODIFY_SD) 
	 * 		Exception
	 * 		...in case of database failure
	 * 		HACLSDException(HACLSDException::CANNOT_ADD_SD)
	 * 		... if an SD is added to an SD or PR
	 */
	public function addPredefinedRights($rights, $user = null) {
		
		// Check if all rights are predefined rights
		foreach ($rights as $r) {
			if ($r->getPEType() !== self::PET_RIGHT) {
				throw new HACLSDException(HACLSDException::CANNOT_ADD_SD, 
				                          $this->getSDName(), $r->getSDName());
			}
		}

		// Can the user modify this SD/PR
		$this->userCanModify($user, true);
		
		// Update the hierarchy of SDs/PRs
		foreach ($rights as $r) {
			$r->save();
			HACLStorage::getDatabase()->addRightToSD($this->getSDID(), $r->getSDID());
		}

		// Assign rights to all protected elements
		$this->materializeRightsHierarchy();		
	}
	
	/**
	 * Adds several inline rights. The origin-ID of the inline rights are set
	 * and they are saved to the database. The new hierarchy of rights is 
	 * materialized in the database. It is always faster to add all inline rights
	 * at once instead of one after another. 
	 *
	 * @param array<HACLRight> $rights
	 * 		An array of inline rights
	 * @param User/string/int $user
	 * 		User-object, name of a user or ID of a user who wants to delete this
	 * 		SD. If <null> (default), the currently logged in user is assumed.
	 * 
	 * @throws
	 * 	HACLSDException(HACLSDException::USER_CANT_MODIFY_SD) 
	 */
	public function addInlineRights($rights, $user = null) {
		$this->userCanModify($user, true);
		foreach ($rights as $r) {
			$r->setOriginID($this->mSDID);
			$r->save();
		}
		
		$this->materializeRightsHierarchy();
	}

	/**
	 * Returns a list of IDs of all inline rights of this SD. 
	 *
	 * @param bool $recursively
	 * 		If <true>, the whole hierarchy of rights of this SD is considered
	 * 				   and all derived inline rights are returned.
	 * 		If <false>, only the direct inline rights of this SD are returned.
	 * 
	 * @return array<int>
	 * 		An array of inline right IDs. There are no duplicate IDs.
	 * 
	 */
	public function getInlineRights($recursively = true) {
		if ($recursively) {
			// find all derived inline rights as well
			// => first, find all derived predefined rights
			$sdIDs = self::getPredefinedRights(true);
			$sdIDs[] = $this->getSDID();
		} else {
			$sdIDs = array($this->getSDID());
		}
		// Get all direct rights of this SD
		$ir = HACLStorage::getDatabase()->getInlineRightsOfSDs($sdIDs);
		 
		return $ir;
	}
	
	/**
	 * Returns an array of IDs of predefined rights of this SD.
	 *
	 * @param bool $recursively
	 * 		<true>: The whole hierarchy of rights is returned.
	 * 		<false>: Only the direct rights of this SD are returned.
	 * 
	 * @return array<int>
	 * 		Array of unique IDs of rights of this SD.
	 */
	public function getPredefinedRights($recursively = true) {
		
		$pr = HACLStorage::getDatabase()->getPredefinedRightsOfSD($this->getSDID(), $recursively);
		 
		return $pr;
		
	}

	/**
	 * Finds all (real) security descriptors that are related to this
	 * SD or PR. If $this is an SD, it is returned. If 
	 * $this is a PR, all SDs that include this right (via the hierarchy of 
	 * rights) are returned.
	 *
	 * @return array<int>
	 * 		An array of IDs of all SD that include $this SD or PR via the hierarchy
	 *      of PRs.
	 */
	public function getSDsIncludingPR() {
		if ($this->mPEType === self::PET_RIGHT) {
			return HACLStorage::getDatabase()->getSDsIncludingPR($this->mSDID);
		} else {
			// $this is an SD
			return array($this->mSDID);
		}
	}
	
	/**
	 * Checks if the given user can modify this SD.
	 *
	 * @param User/string/int $user
	 * 		User-object, name of a user or ID of a user who wants to modify this
	 * 		SD. If <null>, the currently logged in user is assumed.
	 * 
	 * @param boolean $throwException
	 * 		If <true>, the exception 
	 * 		HACLSDException(HACLSDException::USER_CANT_MODIFY_SD)
	 * 		is thrown, if the user can't modify the SD.
	 * 
	 * @return boolean
	 * 		One of these values is returned if no exception is thrown:
	 * 		<true>, if the user can modify this SD and
	 * 		<false>, if not
	 * 
	 * @throws 
	 * 		HACLException(HACLException::UNKOWN_USER)
	 * 		If requested: HACLSDException(HACLSDException::USER_CANT_MODIFY_SD) 
	 * 
	 */
	public function userCanModify($user, $throwException = false) {
		// Get the ID of the user who wants to add/modify the SD
		list($userID, $userName) = haclfGetUserID($user);
		// Check if the user can modify the SD
		if (in_array($userID, $this->mManageUsers)) {
			return true;
		}
		
		// Check if the user belongs to a SD that can modify the SD
		$db = HACLStorage::getDatabase();
		foreach ($this->mManageGroups as $groupID) {
			if ($db->hasGroupMember($groupID, $userID, HACLGroup::USER, true)) {
				return true;
			}
		}
		if ($throwException) {
			if (empty($userName)) {
				// only user id is given => retrieve the name of the user
				$user = User::newFromId($userID);
				$userName = ($user) ? $user->getId() : "(User-ID: $userID)";
			}
			throw new HACLSDException(HACLSDException::USER_CANT_MODIFY_SD, 
			                             $this->mSDName, $userName);
		}
		return false;
	}

	/**
	 * Saves this SD in the database. A SD needs a name and at least one group 
	 * or user who can modify the definition of this group. If no group or user 
	 * is given, the specified or the current user gets this right. If no user is
	 * logged in, the operation fails.
	 * 
	 * If the SD already exists and the given user has the right to modify the
	 * SD, the SDs definition is changed.    
	 *
	 * 
	 * @param User/string $user
	 * 		User-object or name of the user who wants to save this SD. If this 
	 * 		value is empty or <null>, the current user is assumed. 
	 *  
	 * @throws 
	 * 		HACLSDException(HACLSDException::NO_SD_ID)
	 * 		HACLException(HACLException::UNKOWN_USER)
	 * 		HACLSDException(HACLSDException::USER_CANT_MODIFY_SD)
	 * 		Exception (on failure in database level) 
	 * 
	 */
	public function save($user = null) {
		
		// Get the page ID of the article that defines the SD
		if ($this->mSDID == 0) {
			throw new HACLSDException(HACLSDException::NO_SD_ID, $this->mSDName);
		}
		
		$this->userCanModify($user, true);

		HACLStorage::getDatabase()->saveSD($this);
		
	}
	
	
	/**
	 * Deletes this security descriptor from the database.
	 *
	 * @param User/string/int $user
	 * 		User-object, name of a user or ID of a user who wants to delete this
	 * 		SD. If <null>, the currently logged in user is assumed.
	 * 
	 * @throws
	 * 	HACLSDException(HACLSDException::USER_CANT_MODIFY_SD) 
	 *
	 */
	public function delete($user = null) {
		$this->userCanModify($user, true);
		//TODO: Do I have to delete all inline rights that point to this SD?
		return HACLStorage::getDatabase()->deleteSD($this->mSDID);
	}
	
	/**
	 * Rights are organized in a hierarchy. Security descriptors (SD) and predefined
	 * rights (PR) (which are almost the same) can contain PRs and inline rights.
	 * SDs protect elements (e.g. pages). For faster evaluation of rights, all
	 * inline rights that belong to an SD through the hierarchy of rights are 
	 * assigned to the protected element in the database.
	 *
	 */
	public function materializeRightsHierarchy() {
		// Recursively find all inline rights that belong to right
		$ir = $this->getInlineRights(true);

		// Recursively find all security descriptors that get the new right
		if (!empty($ir)) {
			$sd = $this->getSDsIncludingPR();
		}
		
		// Add all inline rights to all protected elements (i.e. materialize the
		// hierarchy of rights)
		if (!empty($ir) && !empty($sd)) {
			HACLStorage::getDatabase()->setInlineRightsForProtectedElements($ir, $sd);
		}
		
	}
	
	//--- Private methods ---
	
	
}