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
 * Insert description here
 * 
 * @author Thomas Schweitzer
 * Date: 03.04.2009
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the HaloACL extension. It is not a valid entry point.\n" );
}

 //--- Includes ---
 global $haclgIP;
//require_once("$haclgIP/...");

/**
 * This class describes a group in HaloACL.
 * 
 * A group is always represented by an article in the wiki, so the group's 
 * description contains the page ID of this article and the name of the group.
 * 
 * Only authorized users and groups of users can modify the definition of the
 * group. Their IDs are stored in the group as well.
 * 
 * @author Thomas Schweitzer
 * 
 */
class  HACLGroup  {
	
	//--- Constants ---
	const NAME   = 0;		// Mode parameter for getUsers/getGroups
	const ID     = 1;		// Mode parameter for getUsers/getGroups
	const OBJECT = 2;		// Mode parameter for getUsers/getGroups
	const USER   = 'user';  // Child type for users
	const GROUP  = 'group'; // Child type for groups
	
	//--- Private fields ---
	private $mGroupID;    		// int: Page ID of the article that defines this group
	private $mGroupName;		// string: The name of this group
	private $mManageGroups;		// array(int): IDs of the groups that can modify 
								//		the definition of this group
	private $mManageUsers;		// array(int): IDs of the users that can modify 
								//		the definition of this group
	
	/**
	 * Constructor for HACLGroup
	 *
	 * @param int/string $groupID
	 * 		Article's page ID. If <null>, the class tries to find the correct ID
	 * 		by the given $groupName. Of course, this works only for existing
	 * 		groups.
	 * @param string $groupName
	 * 		Name of the group
	 * @param array<int/string>/string $manageGroups
	 * 		An array or a string of comma separated of group names or IDs that 
	 *      can modify the group's definition. Group names are converted and 
	 *      internally stored as group IDs. Invalid values cause an exception.
	 * @param array<int/string>/string $manageUsers
	 * 		An array or a string of comma separated of user names or IDs that
	 *      can modify the group's definition. User names are converted and 
	 *      internally stored as group IDs. Invalid values cause an exception.
	 * @throws 
	 * 		HACLGroupException(HACLGroupException::UNKOWN_GROUP)
	 * 		HACLGroupException(HACLGroupException::UNKOWN_USER)
	 * 	 
	 */		
	function __construct($groupID, $groupName, $manageGroups, $manageUsers) {
		
		if (is_null($groupID)) {
			$groupID = self::idForGroup($groupName);
		}
		$this->mGroupID = 0+$groupID;
		$this->mGroupName = $groupName;
				
		if (is_string($manageGroups)) {
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
					$gid = self::idForGroup(trim($mg));
					if (!$gid) {
						throw new HACLGroupException(HACLGroupException::UNKOWN_GROUP, $groupName);
					}
					$this->mManageGroups[$i] = $gid; 
				}
			}
		} else {
			$this->mManageGroups = array();
		}

		if (is_string($manageUsers)) {
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
						throw new HACLGroupException(HACLGroupException::UNKOWN_USER, $groupName, $mu);
					}
					$this->mManageUsers[$i] =  $uid;
				}
			}
		} else {
			$this->mManageUsers = array();
		}
		
	}
	
	//--- getter/setter ---

	public function getGroupID()		{return $this->mGroupID;}
	public function getGroupName()		{return $this->mGroupName;}
	public function getManageGroups()	{return $this->mManageGroups;}
	public function getManageUsers()	{return $this->mManageUsers;}
		
//	public function setXY($xy)               {$this->mXY = $xy;}
	
	//--- Public methods ---
	
	/**
	 * Creates a new group object based on the name of the group. The group must
	 * exists in the database.
	 * 
	 * @param string $groupName
	 * 		Name of the group.
	 * 
	 * @return HACLGroup
	 * 		A new group object or <null> if there is no such group in the 
	 * 		database.
	 */
	public function newFromName($groupName) {
		return HACLStorage::getDatabase()->getGroupByName($groupName);
	}

	/**
	 * Creates a new group object based on the ID of the group. The group must
	 * exists in the database.
	 * 
	 * @param int $groupID
	 * 		ID of the group i.e. the ID of the article that defines the group.
	 * 
	 * @return HACLGroup
	 * 		A new group object or <null> if there is no such group in the 
	 * 		database.
	 */
	public function newFromID($groupID) {
		return HACLStorage::getDatabase()->getGroupByID($groupID);
	}
	
	/**
	 * Returns the page ID of the article that defines the group with the name
	 * $groupName.
	 *
	 * @param string $groupName
	 * 		Name of the group
	 * 
	 * @return int
	 * 		The ID of the group's article or <null> if the article does not exist.
	 * 
	 */
	public static function idForGroup($groupName) {
		$nt = Title::newFromText($groupName, HACL_NS_ACL);
		if  (is_null($nt)) {
			# Illegal name
			return null;
		}
		
		return $nt->getArticleID();
	}
	
	/**
	 * Returns the name of the group with the ID $groupID.
	 *
	 * @param int $groupID
	 * 		ID of the group whose name is requested
	 * 
	 * @return string
	 * 		Name of the group with the given ID or <null> if there is no such
	 * 		group defined in the database.
	 */
	public static function nameForID($groupID) {
		return HACLStorage::getDatabase()->groupNameForID($groupID);
	}

	
	/**
	 * Checks if the given user can modify this group.
	 *
	 * @param User/string/int $user
	 * 		User-object, name of a user or ID of a user who wants to modify this
	 * 		group. If <null>, the currently logged in user is assumed.
	 * 
	 * @param boolean $throwException
	 * 		If <true>, the exception 
	 * 		HACLGroupException(HACLGroupException::USER_CANT_MODIFY_GROUP)
	 * 		is thrown, if the user can't modify the group.
	 * 
	 * @return boolean
	 * 		One of these values is returned if no exception is thrown:
	 * 		<true>, if the user can modify this group and
	 * 		<false>, if not
	 * 
	 * @throws 
	 * 		HACLGroupException(HACLGroupException::UNKOWN_USER)
	 * 		If requested: HACLGroupException(HACLGroupException::USER_CANT_MODIFY_GROUP) 
	 * 
	 */
	public function userCanModify($user, $throwException = false) {
		// Get the ID of the user who wants to add/modify the group
		list($userID, $userName) = $this->getUserID($user);
		// Check if the user can modify the group
		if (in_array($userID, $this->mManageUsers)) {
			return true;
		}
		
		// Check if the user belongs to a group that can modify the group
		$db = HACLStorage::getDatabase();
		foreach ($this->mManageGroups as $groupID) {
			if ($db->hasGroupMember($groupID, $userID, self::USER, true)) {
				return true;
			}
		}
		if ($throwException) {
			if (empty($userName)) {
				// only user id is given => retrieve the name of the user
				$user = User::newFromId($userID);
				$userName = ($user) ? $user->getId() : "(User-ID: $userID)";
			}
			throw new HACLGroupException(HACLGroupException::USER_CANT_MODIFY_GROUP, 
			                             $this->mGroupName, $userName);
		}
		return false;
	}

	/**
	 * Saves this group in the database. A group needs a name and at least one group 
	 * or user who can modify the definition of this group. If no group or user 
	 * is given, the specified or the current user gets this right. If no user is
	 * logged in, the operation fails.
	 * 
	 * If the group already exists and the given user has the right to modify the
	 * group, the groups definition is changed.    
	 *
	 * 
	 * @param User/string $user
	 * 		User-object or name of the user who wants to save this group. If this 
	 * 		value is empty or <null>, the current user is assumed. 
	 *  
	 * @throws 
	 * 		HACLGroupException(HACLGroupException::NO_GROUP_ID)
	 * 		HACLGroupException(HACLGroupException::UNKOWN_USER)
	 * 		HACLGroupException(HACLGroupException::USER_CANT_MODIFY_GROUP)
	 * 		Exception (on failure in database level) 
	 * 
	 */
	public function save($user = null) {
		
		// Get the page ID of the article that defines the group
		if ($this->mGroupID == 0) {
			throw new HACLGroupException(HACLGroupException::NO_GROUP_ID, $this->mGroupName);
		}
		
		$this->userCanModify($user, true);

		HACLStorage::getDatabase()->saveGroup($this);
		
	}
	
	/**
	 * Adds the user $user to this group. The new user is immediately added
	 * to the group's definition in the database.
	 * 
	 * @param User/string/int $user
	 * 		This can be a User-object, name of a user or ID of a user. This user
	 * 		is added to the group.
	 * @param User/string/int $mgUser
	 * 		User-object, name of a user or ID of a user who wants to modify this
	 * 		group. If <null>, the currently logged in user is assumed.
	 *  
	 * @throws 
	 * 		HACLGroupException(HACLGroupException::UNKOWN_USER)
	 * 		HACLGroupException(HACLGroupException::USER_CANT_MODIFY_GROUP) 
	 * 
	 */
	public function addUser($user, $mgUser=null) {
		// Check if $mgUser can modifiy this group.
		$this->userCanModify($mgUser, true);
		list($userID, $userName) = $this->getUserID($user);
		HACLStorage::getDatabase()->addUserToGroup($this->mGroupID, $userID);
		
	}
	
	/**
	 * Adds the group $group to this group. The new group is immediately added
	 * to the group's definition in the database.
	 * 
	 * @param HACLGroup $group
	 * 		This group is added to the group.
	 * @param User/string/int $mgUser
	 * 		User-object, name of a user or ID of a user who wants to modify this
	 * 		group. If <null>, the currently logged in user is assumed.
	 *  
	 * @throws 
	 * 		HACLGroupException(HACLGroupException::UNKOWN_USER)
	 * 		HACLGroupException(HACLGroupException::USER_CANT_MODIFY_GROUP) 
	 * 
	 */
	public function addGroup(HACLGroup $group, $mgUser=null) {
		// Check if $mgUser can modifiy this group.
		$this->userCanModify($mgUser, true);
		HACLStorage::getDatabase()->addGroupToGroup($this->mGroupID, $group->getGroupID());
	}
	
	
	/**
	 * Removes the user $user from this group. The user is immediately removed
	 * from the group's definition in the database.
	 * 
	 * @param User/string/int $user
	 * 		This can be a User-object, name of a user or ID of a user. This user
	 * 		is removed from the group.
	 * @param User/string/int $mgUser
	 * 		User-object, name of a user or ID of a user who wants to modify this
	 * 		group. If <null>, the currently logged in user is assumed.
	 *  
	 * @throws 
	 * 		HACLGroupException(HACLGroupException::UNKOWN_USER)
	 * 		HACLGroupException(HACLGroupException::USER_CANT_MODIFY_GROUP) 
	 * 
	 */
	public function removeUser($user, $mgUser=null) {
		// Check if $mgUser can modifiy this group.
		$this->userCanModify($mgUser, true);
		list($userID, $userName) = $this->getUserID($user);
		HACLStorage::getDatabase()->removeUserFromGroup($this->mGroupID, $userID);
		
	}
	
	/**
	 * Removes the group $group from this group. The group is immediately removed
	 * from the group's definition in the database.
	 * 
	 * @param HACLGroup $group
	 * 		This group is removed from the group.
	 * @param User/string/int $mgUser
	 * 		User-object, name of a user or ID of a user who wants to modify this
	 * 		group. If <null>, the currently logged in user is assumed.
	 *  
	 * @throws 
	 * 		HACLGroupException(HACLGroupException::UNKOWN_USER)
	 * 		HACLGroupException(HACLGroupException::USER_CANT_MODIFY_GROUP) 
	 * 
	 */
	public function removeGroup(HACLGroup $group, $mgUser=null) {
		// Check if $mgUser can modifiy this group.
		$this->userCanModify($mgUser, true);
		HACLStorage::getDatabase()->removeGroupFromGroup($this->mGroupID, $group->getGroupID());
	}
	
	/**
	 * Returns all users who are member of this group. 
	 *
	 * @param int $mode
	 * 		HACLGroup::NAME:   The names of all users are returned.
	 * 		HACLGroup::ID:     The IDs of all users are returned.
	 * 		HACLGroup::OBJECT: User-objects for all users are returned.
	 * 
	 * @return array(string/int/User)
	 * 		List of all direct users in this group.
	 * 	 
	 */
	public function getUsers($mode) {
		// retrieve the IDs of all users in this group
		$users = HACLStorage::getDatabase()->getMembersOfGroup($this->mGroupID, self::USER);
		
		if ($mode === self::ID) {
			return $users;
		}
		for ($i = 0; $i < count($users); ++$i) {
			if ($mode === self::NAME) {
				$users[$i] = User::whoIs($users[$i]);
			} else if ($mode === self::OBJECT) {
				$users[$i] = User::newFromId($users[$i]);
			}  
		}
		return $users;
	}

	/**
	 * Returns all groups who are member of this group. 
	 *
	 * @param int $mode
	 * 		HACLGroup::NAME:   The names of all groups are returned.
	 * 		HACLGroup::ID:     The IDs of all groups are returned.
	 * 		HACLGroup::OBJECT: HACLGroup-objects for all groups are returned.
	 * 
	 * @return array(string/int/HACLGroup)
	 * 		List of all direct groups in this group.
	 * 	 
	 */
	public function getGroups($mode) {
		// retrieve the IDs of all groups in this group
		$groups = HACLStorage::getDatabase()->getMembersOfGroup($this->mGroupID, self::GROUP);
		
		if ($mode === self::ID) {
			return $groups;
		}
		for ($i = 0; $i < count($groups); ++$i) {
			if ($mode === self::NAME) {
				$groups[$i] = self::nameForID($groups[$i]);
			} else if ($mode === self::OBJECT) {
				$groups[$i] = self::newFromID($groups[$i]);
			}  
		}
		return $groups;
		
	}
	
	/**
	 * Checks if this group has the given group as member.
	 * 
	 * @param mixed (int/string/HACLGroup) $group
	 * 		ID, name or object for the group that is checked for membership.
	 * 
	 * @param bool recursive
	 * 		<true>, checks recursively among all children of this group if
	 * 				$group is a member
	 * 		<false>, checks only if $group is an immediate member of this group
	 * 
	 * @return bool
	 * 		<true>, if $group is a member of this group
	 * 		<false>, if not
	 * @throws 
	 * 		HACLGroupException(HACLGroupException::NO_GROUP_ID)
	 * 			...if the name of the group is invalid
	 * 		HACLGroupException(HACLGroupException::INVALID_GROUP_ID)
	 * 			...if the ID of the group is invalid
	 *
	 */
	public function hasGroupMember($group, $recursive) {
		$groupID = 0;
		if (is_int($group)) {
			// group ID given
			$groupID = $group; 
		} else if (is_string($group)) {
			// Name of group given
			$groupID = self::idForGroup($group);
			if ($groupID === 0) {
				throw new HACLGroupException(HACLGroupException::NO_GROUP_ID, 
				                             $group);
				
			}
		} else if (is_a($group, 'HACLGroup')) {
			// group object given
			$groupID = $group->getGroupID();
		}
		
		if ($groupID === 0) {
			throw new HACLGroupException(HACLGroupException::INVALID_GROUP_ID, 
			                             $groupID);
			
		}
		return HACLStorage::getDatabase()
				->hasGroupMember($this->mGroupID, $groupID, self::GROUP, $recursive);
	}
	
	/**
	 * Checks if this group has the given user as member.
	 * 
	 * @param User/string/int $user
	 * 		ID, name or object for the user that is checked for membership.
	 * 
	 * @param bool recursive
	 * 		<true>, checks recursively among all children of this group if
	 * 				$group is a member
	 * 		<false>, checks only if $group is an immediate member of this group
	 * 
	 * @return bool
	 * 		<true>, if $group is a member of this group
	 * 		<false>, if not
	 * @throws 
	 * 		HACLGroupException(HACLGroupException::UNKOWN_USER)
	 * 			...if the user does not exist.
	 * 
	 */
	public function hasUserMember($user, $recursive) {
		$userID = $this->getUserID($user);
		return HACLStorage::getDatabase()
				->hasGroupMember($this->mGroupID, $userID[0], self::USER, $recursive);
	}
	
	//--- Private methods ---
	
	/**
	 * Returns the ID of the given user.
	 *
	 * @param User/string/int $user
	 * 		User-object, name of a user or ID of a user. If <null> or empty, the
	 *      currently logged in user is assumed.
	 * @return array(int,string)
	 * 		(Database-)ID of the given user and his name. For the sake of 
	 *      performance the name is not retrieved, if the ID of the user is
	 * 		passed in parameter $user.
	 * @throws 
	 * 		HACLGroupException(HACLGroupException::UNKOWN_USER)
	 * 			...if the user does not exist.
	 */
	private function getUserID($user) {
		$userID = 0;
		$userName = '';
		if (is_null($user) || empty($user)) {
			// no user given 
			// => the current user's ID is requested
			global $wgUser; 
			$userID = $wgUser->getId();
			$userName = $wgUser->getName();
		} else if (is_string($user)) {
			// name of user given
			$u = User::newFromName($user);
			$userID = $u->getId();
			$userName = $user;
		} else if (is_a($user, 'User')) {
			// User-object given
			$userID = $user->getId();
			$userName = $user->getName();
		} else if (is_int($user)) {
			// user-id given
			$userID = $user;
		}
		
		if ($userID === 0) {
			// invalid user
			throw new HACLGroupException(HACLGroupException::UNKOWN_USER, 
			                             $this->mGroupName, '"'.$user.'"');
		}
		
		return array($userID, $userName);
		
	}
}