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
 * This file provides the access to the SQL database tables that are
 * used by HaloACL.
 *
 * @author Thomas Schweitzer
 *
 */

global $haclgIP;
require_once $haclgIP . '/storage/HACL_DBHelper.php';

/**
 * This class encapsulates all methods that care about the database tables of
 * the HaloACL extension. This is the implementation for the SQL database.
 *
 */
class HACLStorageSQL {

	/**
	 * Initializes the database tables of the HaloACL extensions.
	 * These are:
	 * - halo_acl_pe_rights: 
	 * 		table of materialized inline rights for each protected element
	 * - halo_acl_rights:
	 * 		description of each inline right
	 * - halo_acl_rights_hierarchy:
	 * 		hierarchy of predefined rights
	 * - halo_acl_security_descriptors:
	 * 		table for security descriptors and predefined rights
	 * - halo_acl_groups:
	 * 		stores the ACL groups
	 * - halo_acl_group_members:
	 * 		stores the hierarchy of groups and their users
	 *
	 */
	public function initDatabaseTables() {

		$db =& wfGetDB( DB_MASTER );

		$verbose = true;
		DBHelper::reportProgress("Setting up HaloACL ...\n",$verbose);

		// halo_acl_rights:
		//		description of each inline right
		$table = $db->tableName('halo_acl_rights');

		HACLDBHelper::setupTable($table, array(
				'right_id' 		=> 'INT(8) UNSIGNED NOT NULL PRIMARY KEY',
				'actions' 		=> 'BIT(7) NOT NULL',
				'groups' 		=> 'Text',
				'users' 		=> 'Text',
				'description' 	=> 'Text',
				'origin_id' 	=> 'INT(8) UNSIGNED NOT NULL'),
				$db, $verbose);
		HACLDBHelper::reportProgress("   ... done!\n",$verbose);
		
		// halo_acl_pe_rights: 
		// 		table of materialized inline rights for each protected element
		$table = $db->tableName('halo_acl_pe_rights');

		HACLDBHelper::setupTable($table, array(
				'pe_id' 	=> 'INT(8) UNSIGNED NOT NULL',
				'type' 		=> 'ENUM(\'category\', \'page\', \'namespace\', \'property\') DEFAULT \'page\' NOT NULL',
				'right_id' 	=> 'INT(8) UNSIGNED NOT NULL'), 
				$db, $verbose, "pe_id,type,right_id");				
		HACLDBHelper::reportProgress("   ... done!\n",$verbose);
		
		// halo_acl_rights_hierarchy:
		//		hierarchy of predefined rights
		$table = $db->tableName('halo_acl_rights_hierarchy');

		HACLDBHelper::setupTable($table, array(
				'parent_right_id' 	=> 'INT(8) UNSIGNED NOT NULL',
				'child_id'			=> 'INT(8) UNSIGNED NOT NULL'),
				$db, $verbose, "parent_right_id,child_id");
		HACLDBHelper::reportProgress("   ... done!\n",$verbose, "parent_right_id, child_id");
		
		// halo_acl_security_descriptors:
		//		table for security descriptors and predefined rights
		$table = $db->tableName('halo_acl_security_descriptors');

		HACLDBHelper::setupTable($table, array(
				'sd_id' 	=> 'INT(8) UNSIGNED NOT NULL PRIMARY KEY',
				'pe_id' 	=> 'INT(8) UNSIGNED',
				'type' 		=> 'ENUM(\'category\', \'page\', \'namespace\', \'property\', \'right\') DEFAULT \'page\' NOT NULL',
				'mr_groups' => 'TEXT',
				'mr_users' 	=> 'TEXT'),
				$db, $verbose);
		HACLDBHelper::reportProgress("   ... done!\n",$verbose);
		
		// halo_acl_groups:
		//		stores the ACL groups
		$table = $db->tableName('halo_acl_groups');

		HACLDBHelper::setupTable($table, array(
				'group_id'   => 'INT(8) UNSIGNED NOT NULL PRIMARY KEY',
				'group_name' => 'VARCHAR(255) NOT NULL',
				'mg_groups'  => 'TEXT',
				'mg_users'   => 'TEXT'),
				$db, $verbose);
		HACLDBHelper::reportProgress("   ... done!\n",$verbose);
		
		// halo_acl_group_members:
		//		stores the hierarchy of groups and their users
		$table = $db->tableName('halo_acl_group_members');

		HACLDBHelper::setupTable($table, array(
				'parent_group_id' 	=> 'INT(8) UNSIGNED NOT NULL',
				'child_type' 		=> 'ENUM(\'group\', \'user\') DEFAULT \'user\' NOT NULL',
				'child_id' 			=> 'INT(8) UNSIGNED NOT NULL'),
				$db, $verbose, "parent_group_id,child_type,child_id");
		HACLDBHelper::reportProgress("   ... done!\n",$verbose, "parent_group_id, child_type, child_id");
		
		return true;

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
	public static function groupNameForID($groupID) {
		$db =& wfGetDB( DB_SLAVE );
		$gt = $db->tableName('halo_acl_groups');
		$sql = "SELECT group_name FROM $gt ".
		          "WHERE group_id = '$groupID';";
		$groupName = null;

		$res = $db->query($sql);

		if ($db->numRows($res) == 1) {
			$row = $db->fetchObject($res);
			$groupName = $row->group_name;
		}
		$db->freeResult($res);

		return $groupName;
	}
	
	/**
	 * Saves the given group in the database.
	 *
	 * @param string $groupName
	 * 		Name of the group. This is identical to the name of the article 
	 * 		that defines the group without the namespace identifier ACL:. 
	 *     
	 * @param array(string) $mgGroups
	 * 		The names of all groups that can change the definition of this new group 
	 * 	 
	 * @param array(string) $mgUsers
	 * 		The names of all users who can change the definition of this new group 
	 * 
	 * @throws 
	 * 		Exception
	 * 
	 */
	public function saveGroup(HACLGroup $group) {
		$db =& wfGetDB( DB_MASTER );

		$mgGroups = implode(',', $group->getManageGroups());		
		$mgUsers  = implode(',', $group->getManageUsers());		
		$db->replace($db->tableName('halo_acl_groups'), null, array(
				  'group_id'    =>  $group->getGroupID() ,
				  'group_name'	=>  $group->getGroupName() ,
				  'mg_groups'   =>  $mgGroups,
				  'mg_users'    =>  $mgUsers));

	}
	
	/**
	 * Retrieves the description of the group with the name $groupName from
	 * the database.
	 *
	 * @param string $groupName
	 * 		Name of the requested group.
	 * 
	 * @return HACLGroup
	 * 		A new group object or <null> if there is no such group in the 
	 * 		database.
	 *  
	 */
	public function getGroupByName($groupName) {
		$db =& wfGetDB( DB_SLAVE );
		$gt = $db->tableName('halo_acl_groups');
		$sql = "SELECT * FROM $gt ".
		          "WHERE group_name = '$groupName';";
		$group = null;

		$res = $db->query($sql);

		if ($db->numRows($res) == 1) {
			$row = $db->fetchObject($res);
			$groupID = $row->group_id;
			$mgGroups = $row->mg_groups;
			$mgUsers  = $row->mg_users;
			$group = new HACLGroup($groupID, $groupName, $mgGroups, $mgUsers);
		}
		$db->freeResult($res);

		return $group;
	}

	/**
	 * Retrieves the description of the group with the ID $groupID from
	 * the database.
	 *
	 * @param int $groupID
	 * 		ID of the requested group.
	 * 
	 * @return HACLGroup
	 * 		A new group object or <null> if there is no such group in the 
	 * 		database.
	 *  
	 */
	public function getGroupByID($groupID) {
		$db =& wfGetDB( DB_SLAVE );
		$gt = $db->tableName('halo_acl_groups');
		$sql = "SELECT * FROM $gt ".
		          "WHERE group_id = '$groupID';";
		$group = null;

		$res = $db->query($sql);

		if ($db->numRows($res) == 1) {
			$row = $db->fetchObject($res);
			$groupID = $row->group_id;
			$groupName = $row->group_name;
			$mgGroups = $row->mg_groups;
			$mgUsers  = $row->mg_users;
			$group = new HACLGroup($groupID, $groupName, $mgGroups, $mgUsers);
		}
		$db->freeResult($res);

		return $group;
	}
	
	/**
	 * Adds the user with the ID $userID to the group with the ID $groupID.
	 *
	 * @param int $groupID
	 * 		The ID of the group to which the user is added.  
	 * @param int $userID
	 * 		The ID of the user who is added to the group.
	 *  
	 * @throws 
	 * 		HACLGroupException(HACLGroupException::UNKOWN_USER)
	 * 		HACLGroupException(HACLGroupException::USER_CANT_MODIFY_GROUP) 
	 * 
	 */
	public function addUserToGroup($groupID, $userID) {
		$db =& wfGetDB( DB_MASTER );

		$db->replace($db->tableName('halo_acl_group_members'), null, array(
				  'parent_group_id'    =>  $groupID ,
				  'child_type'	=>  'user' ,
				  'child_id '   =>  $userID));
		
	}
	
	/**
	 * Adds the group with the ID $childGroupID to the group with the ID 
	 * $parentGroupID.
	 * 
	 * @param $parentGroupID
	 * 		The group with this ID gets the new child with the ID $childGroupID.
	 * @param $childGroupID
	 * 		The group with this ID is added as child to the group with the ID
	 *      $parentGroup.
	 *  
	 * @throws 
	 * 		HACLGroupException(HACLGroupException::UNKOWN_USER)
	 * 		HACLGroupException(HACLGroupException::USER_CANT_MODIFY_GROUP) 
	 * 
	 */
	public function addGroupToGroup($parentGroupID, $childGroupID) {
		$db =& wfGetDB( DB_MASTER );

		$db->replace($db->tableName('halo_acl_group_members'), null, array(
				  'parent_group_id'    =>  $parentGroupID ,
				  'child_type'	=>  'group' ,
				  'child_id '   =>  $childGroupID));
		
	}
	
	/**
	 * Removes the user with the ID $userID from the group with the ID $groupID.
	 *
	 * @param $groupID
	 * 		The ID of the group from which the user is removed.  
	 * @param int $userID
	 * 		The ID of the user who is removed from the group.
	 *  
	 */
	public function removeUserFromGroup($groupID, $userID) {
		$db =& wfGetDB( DB_MASTER );

		$db->delete($db->tableName('halo_acl_group_members'), array(
				  'parent_group_id'    => $groupID ,
				  'child_type'	=>  'user' ,
				  'child_id '   =>  $userID));
		
	}
	
	/**
	 * Removes the group with the ID $childGroupID from the group with the ID
	 * $parentGroupID.
	 * 
	 * @param $parentGroupID
	 * 		This group loses its child $childGroupID.
	 * @param $childGroupID
	 * 		This group is removed from $parentGroupID.
	 * 
	 */
	public function removeGroupFromGroup($parentGroupID, $childGroupID) {
		$db =& wfGetDB( DB_MASTER );

		$db->delete($db->tableName('halo_acl_group_members'), array(
				  'parent_group_id'    =>  $parentGroupID,
				  'child_type'	=>  'group' ,
				  'child_id '   =>  $childGroupID));
		
	}
	
	/**
	 * Returns the IDs of all users or groups that are a member of the group 
	 * with the ID $groupID. 
	 *
	 * @param string $memberType
	 * 		'user' => ask for all user IDs
	 *      'group' => ask for all group IDs
	 * @return array(int)
	 * 		List of IDs of all direct users or groups in this group.
	 * 	 
	 */
	public function getMembersOfGroup($groupID, $memberType) {
		$db =& wfGetDB( DB_SLAVE );
		$gt = $db->tableName('halo_acl_group_members');
		$sql = "SELECT child_id FROM $gt ".
		          "WHERE parent_group_id = '$groupID' AND ".
				  "child_type='$memberType';";

		$res = $db->query($sql);

		$members = array();
		while ($row = $db->fetchObject($res)) {
			$members[] = (int) $row->child_id;
		}

		$db->freeResult($res);

		return $members;
		
	}

	/**
	 * Checks if the given user or group with the ID $childID belongs to the 
	 * group with the ID $parentID.
	 * 
	 * @param int $parentID
	 * 		ID of the group that is checked for a member.
	 * 
	 * @param int $childID
	 * 		ID of the group or user that is checked for membership.
	 * 
	 * @param string $memberType
	 * 		HACLGroup::USER  : Checks for membership of a user
	 * 		HACLGroup::GROUP : Checks for membership of a group
	 *  
	 * @param bool recursive
	 * 		<true>, checks recursively among all children of this $parentID if
	 * 				$childID is a member
	 * 		<false>, checks only if $childID is an immediate member of $parentID
	 * 
	 * @return bool
	 * 		<true>, if $childID is a member of $parentID
	 * 		<false>, if not
	 *
	 */
	
	public function hasGroupMember($parentID, $childID, $memberType, $recursive) {
		$db =& wfGetDB( DB_SLAVE );
		$gt = $db->tableName('halo_acl_group_members');
		
		// Ask for the immediate parents of $childID
		$sql = "SELECT parent_group_id FROM $gt ".
		          "WHERE child_id = '$childID' AND ".
				  "child_type='$memberType';";

		$res = $db->query($sql);

		$parents = array();
		while ($row = $db->fetchObject($res)) {
			if ($parentID == (int) $row->parent_group_id) {
				$db->freeResult($res);
				return true;
			}
			$parents[] = (int) $row->parent_group_id;
		}
		$db->freeResult($res);
		
		// $childID is not an immediate child of $parentID
		if (!$recursive || empty($parents)) {
			return false;
		}
		
		// Check recursively, if one of the parent groups of $childID is $parentID

		$ancestors = array();
		while (true) {
			// Check if one of the parent's parent is $parentID
			$sql = "SELECT parent_group_id FROM $gt ".
			          "WHERE parent_group_id='$parentID' AND ".
					  "child_id in (".implode(',', $parents).") AND ".
					  "child_type='group';";
	
			$res = $db->query($sql);
			if ($db->numRows($res) == 1) {
				// The request parent was found
				$db->freeResult($res);
				return true;
			}
			
			// Parent was not found => retrieve all parents of the current set of
			// parents.
			$sql = "SELECT DISTINCT parent_group_id FROM $gt WHERE ".
					  (empty($ancestors) ? ""
					                    : "parent_group_id not in (".implode(',', $ancestors).") AND ").
			          "child_id in (".implode(',', $parents).") AND ".
					  "child_type='group';";
			
			$res = $db->query($sql);
			if ($db->numRows($res) == 0) {
				// The request parent was found
				$db->freeResult($res);
				return false;
			}
			
			$ancestors = array_merge($ancestors, $parents);
			$parents = array();
			while ($row = $db->fetchObject($res)) {
				if ($parentID == (int) $row->parent_group_id) {
					$db->freeResult($res);
					return true;
				}
				$parents[] = (int) $row->parent_group_id;
			}
			$db->freeResult($res);
		}
		
	}
	
}
?><!-- 
	/**
	 * Retrieves the definition of the notification with the name <$name> and
	 * the user with the id or name <$user> from the database.
	 *
	 * @param string $name
	 * 		The unique name of the notification.
	 * @param mixed (int, string) $user
	 * 		The user (id or name) who owns the notification.
	 *
	 * @return SemanticNotification
	 * 		If the notification exists in the database, a new object is created
	 * 		and initialized with the database values. Otherwise <null> is returned.
	 */
	public function getSN($name, $user) {
		$userID = (is_int($user)) ? $user : User::idFromName($user);
		if (!$userID) {
			// invalid user name
			return null;
		}

		$db =& wfGetDB( DB_SLAVE );
		$snt = $db->tableName('smw_sem_notification');
		$sql = "SELECT sn.* FROM $snt sn ".
		          "WHERE user_id = $userID AND query_name = '".$name."';";
		$sn = null;

		$res = $db->query($sql);

		if ($db->numRows($res) == 1) {
			$row = $db->fetchObject($res);
			if (is_int($user)) {
				$u = User::newFromId($user);
				$user = $u->getName();
			}
			$sn = new SemanticNotification($name, $user, $row->query_text,
										   $row->update_interval,
										   $row->query_result,
										   $row->timestamp);
		}
		$db->freeResult($res);
		return $sn;

	}

	/**
	 * Deletes the semantic notification with the name <$name> of the user <$userName>
	 * from database.
	 *
	 * @param string $name
	 * 		The unique name of the notification.
	 * @param string $userName
	 * 		The user who owns the notification.
	 */
	public function deleteSN($name, $userName) {
		$userID = User::idFromName($userName);
		if (!$userID) {
			// invalid user name
			return "Invalid user id";
			return null;
		}

		$db =& wfGetDB( DB_MASTER );
		$snt = 'smw_sem_notification';
		try {
			$db->delete($snt, array('user_id' => $userID,
		                            'query_name' => $name), 
		                "SNStorage::deleteSN");
		} catch (Exception $e) {
			return $e->getMessage();
		}
		return "true";

	}

	/**
	 * Returns an array of the names of all notifications of the given user.
	 *
	 * @param string $userName
	 * 		Name of the user, whose notifications are requested.
	 *
	 * @return array<string>
	 * 		The names of all notifications of the given user or
	 * 		<null>, if there are none.
	 *
	 */
	public static function getNotificationsOfUser($userName) {
		$userID = User::idFromName($userName);
		if (!$userID) {
			// invalid user name
			return null;
		}

		$db =& wfGetDB( DB_SLAVE );
		$snt = $db->tableName('smw_sem_notification');
		$sql = "SELECT sn.query_name FROM $snt sn ".
		          "WHERE user_id = $userID;";
		$sn = null;

		$res = $db->query($sql);
		if ($db->numRows($res) == 0) {
			// no notifications
			return null;
		}
		$notifications = array();
		while ($row = $db->fetchObject($res)) {
			$notifications[] = $row->query_name;
		}
		$db->freeResult($res);
		return $notifications;
	}
	
	/**
	 * All notifications of all users i.e. the user-id/name-pairs.
	 *
	 * @return array<array<int,string>>
	 * 		An array of arrays where the inner array contains the tuples of
	 * 		user id and notification name.
	 *
	 */
	public static function getAllNotifications() {

		$db =& wfGetDB( DB_SLAVE );
		$snt = $db->tableName('smw_sem_notification');
		$sql = "SELECT sn.user_id, sn.query_name FROM $snt sn;";
		$sn = null;

		$res = $db->query($sql);
		if ($db->numRows($res) == 0) {
			// no notifications
			return null;
		}
		$notifications = array();
		while ($row = $db->fetchObject($res)) {
			$notifications[] = array((int) $row->user_id, $row->query_name);
		}
		$db->freeResult($res);
		return $notifications;
	}
	
	
	/**
	 * Returns the number of all notifications of the given user.
	 *
	 * @param string $userName
	 * 		Name of the user, whose notifications are requested.
	 *
	 * @return int
	 * 		Number of all notifications of the given user.
	 *
	 */
	public static function getNumberOfNotificationsOfUser($userName) {
		$userID = User::idFromName($userName);
		if (!$userID) {
			// invalid user name
			return null;
		}

		$db =& wfGetDB( DB_SLAVE );
		$snt = $db->tableName('smw_sem_notification');
		$sql = "SELECT count(*) AS num FROM $snt sn ".
		          "WHERE user_id = $userID;";
		$res = $db->query($sql);
		$num = $db->fetchObject($res);
		$db->freeResult($res);
		return $num->num;
	}

}
 -->
