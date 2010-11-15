<?php
/**
 * @file
 * @ingroup HaloACL_Tests
 */

class TestLDAPStorageSuite extends PHPUnit_Framework_TestSuite
{
	public static function suite() {
		
		$suite = new TestLDAPStorageSuite();
		$suite->addTestSuite('TestLDAPStorage');
		$suite->addTestSuite('TestLDAPMixedStorage');
		$suite->addTestSuite('TestLDAPGroupMembers');
		return $suite;
	}
	
	protected function setUp() {
    	User::createNew("U1");
    	User::createNew("U2");
    	User::idFromName("U1");  
    	User::idFromName("U2");  
    	Skin::getSkinNames();

		$_SESSION['wsDomain'] = "TestLDAP";
		
		HACLStorage::reset(HACL_STORE_LDAP);
		HACLStorage::getDatabase();
		
		User::createNew('Bianca');
		User::createNew('Judith');
		User::createNew('Thomas');
		User::createNew('WikiSysop');
				
		// authenticate users from the LDAP store
		global $wgAuth;
		$wgAuth->authenticate('Bianca', 'test');
        $user = User::newFromName('Bianca');
		$wgAuth->initUser($user);
		$wgAuth->authenticate('Judith', 'test');
        $user= User::newFromName('Judith');
		$wgAuth->initUser($user);
		$wgAuth->authenticate('Thomas', 'test');
        $user = User::newFromName('Thomas');
		$wgAuth->initUser($user);
		$wgAuth->authenticate('WikiSysop', 'test');
        $user = User::newFromName('WikiSysop');
		$wgAuth->initUser($user);
		
	}
	
	protected function tearDown() {
		
	}

}


/**
 * This class tests operations on groups from LDAP and the database.
 * 
 * It assumes that an LDAP server (e.g. OpenLDAP) is running and configured 
 * according to the LDAP settings in LocalSettings.php.
 * 
 * See 
 * http://dmwiki.ontoprise.de:8888/dmwiki/index.php/Darkmatter:Software_Design_for_ACL#LDAP_connector 
 * for further information on how to setup OpenLDAP.
 * 
 * @author thsc
 *
 */
class TestLDAPMixedStorage extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = FALSE;
	protected $mDoubleGroup = null;
	protected $mMyGroup = null;
	protected $mSubGroup = null;
	protected $mGAID;  // ID of GROUP_Administration
	protected $mGDID;  // ID of GROUP_Developer
	protected $mGFAID;  // ID of GROUP_FinancialAdministration;
	protected $mGroupNames = array(
		"GROUP_Administration", "GROUP_Developer", "GROUP_FinancialAdministration",
		"MyGroup", "SubGroup"
	);
	
	function setUp() {
		// Add a group with the name of an existing LDAP group. This works
		// only with the HaloACL SQL DB
		HACLStorage::reset(HACL_STORE_SQL);
		$this->mDoubleGroup = new HACLGroup(1, "GROUP_Administration", null, array("U1"));
		$this->mDoubleGroup->save("U1");
		
		// Proceed with the LDAP DB
		HACLStorage::reset(HACL_STORE_LDAP);
		
		// In addition to the LDAP groups, we create two normal HaloACL groups
		$this->mMyGroup = new HACLGroup(42, "MyGroup", null, array("U1"));
		$this->mMyGroup->save("U1");
		$this->mSubGroup = new HACLGroup(43, "SubGroup", null, array("U1"));
		$this->mSubGroup->save("U1");
		$this->mMyGroup->addGroup($this->mSubGroup, "U1");
		
		
		$g = HACLGroup::newFromName('GROUP_Administration');
		$this->mGAID = $g->getGroupID();
		$g = HACLGroup::newFromName('GROUP_Developer');
		$this->mGDID = $g->getGroupID();
		$g = HACLGroup::newFromName('GROUP_FinancialAdministration');
		$this->mGFAID = $g->getGroupID();
		
	}

	function tearDown() {
		$this->mMyGroup->delete("U1");
		$this->mSubGroup->delete("U1");
		
		HACLStorage::reset(HACL_STORE_SQL);
		$this->mDoubleGroup->delete("U1");
		HACLStorage::reset(HACL_STORE_LDAP);
		
		global $haclgAllowLDAPGroupMembers;
		$algm = $haclgAllowLDAPGroupMembers;
		$haclgAllowLDAPGroupMembers = true;
		HACLStorage::getDatabase()->removeGroupFromGroup(42, $this->mGAID);
		$haclgAllowLDAPGroupMembers = $algm;
		
	}
	
	/** ONLY TEMPORARY
	 * Tests the method HACLStorage::getGroups();
	 */
//	function testCreatedUsersFromLDAP() {
//		$_SESSION['wsDomain'] = "OntopriseAD";
//		
//		global $wgAuth;
//		// Reset the ldap connection
//		$wgAuth->ldapconn = null;
//		HACLStorage::getDatabase()->createUsersFromLDAP();
//		
//		$_SESSION['wsDomain'] = "TestLDAP";
//	}

	/**
	 * Tests the method HACLStorage::getGroups();
	 */
	function testGetGroups() {
		
		$groups = HACLStorage::getDatabase()->getGroups();
		// We expect 3 top level groups
		$this->assertEquals(count($groups), 3);
			
		$groupMap = array();
		foreach ($groups as $group) {
			$groupMap[$group->getGroupID()] = $group->getGroupName();
		}
		$this->assertEquals($groupMap[$this->mGAID], 'GROUP_Administration');
		$this->assertEquals($groupMap[$this->mGDID], 'GROUP_Developer');
		$this->assertEquals($groupMap[42], 'MyGroup');
			
	}
	
	/**
	 * Tests the method HACLStorage::getGroupByName();
	 */
	function testGetGroupByName() {
		$group = HACLStorage::getDatabase()->getGroupByName('non existing');
		$this->assertEquals($group, null);
		
		$group = HACLStorage::getDatabase()->getGroupByName('GROUP_Administration');
		// we expect the group from LDAP
		$this->assertEquals($group->getGroupID(), $this->mGAID);
		
		$group = HACLStorage::getDatabase()->getGroupByName('MyGroup');
		// we expect the group from the database
		$this->assertEquals($group->getGroupID(), 42);
			
	}
	
	/**
	 * Tests the method HACLStorage::getGroupByID();
	 */
	function testGetGroupByID() {
		$group = HACLStorage::getDatabase()->getGroupByID(24);
		$this->assertEquals($group, null);
		
		$group = HACLStorage::getDatabase()->getGroupByID($this->mGAID);
		// we expect the group from LDAP
		$this->assertEquals($group->getGroupName(), 'GROUP_Administration');

		$group = HACLStorage::getDatabase()->getGroupByID($this->mGAID+100);
		// we expect no such LDAP group
		$this->assertEquals($group, null);
		
		$group = HACLStorage::getDatabase()->getGroupByID(1);
		// we expect the group from LDAP
		$this->assertEquals($group->getGroupName(), 'GROUP_Administration');
		
		$group = HACLStorage::getDatabase()->getGroupByID(42);
		// we expect the group from the database
		$this->assertEquals($group->getGroupName(), 'MyGroup');
			
	}
	
	/**
	 * Tests the method HACLStorage::addUserToGroup();
	 */
	function testAddUserToGroup() {
		
		$userID = User::idFromName("U1");		
		// Try to add a user to an LDAP group 
		$exceptionCaught = false;
		try {		
			HACLStorage::getDatabase()->addUserToGroup($this->mGAID, $userID);
		} catch (HACLStorageException $e) {
			$this->assertEquals($e->getCode(), HACLStorageException::CANT_MODIFY_LDAP_GROUP );
			$exceptionCaught = true;
		}
		if (!$exceptionCaught) {
			$this->fail('Expected exception: HACLStorageException');
		}
		
		// Try to add a user to an HAloACL group 
		HACLStorage::getDatabase()->addUserToGroup(42, $userID);
		// Verify that the group has a new member
		$group = HACLGroup::newFromID(42);
		$users = $group->getUsers(HACLGroup::NAME);
		$this->assertEquals(count($users), 1);
		$this->assertEquals(array_search("U1", $users), 0);
		
	}
	
	/**
	 * Tests the method HACLStorage::addGroupToGroup();
	 */
	function testAddGroupToGroup() {
		
		// Try to add an LDAP group to an LDAP group 
		$exceptionCaught = false;
		try {		
			HACLStorage::getDatabase()->addGroupToGroup($this->mGAID, $this->mGDID);
		} catch (HACLStorageException $e) {
			$this->assertEquals($e->getCode(), HACLStorageException::CANT_MODIFY_LDAP_GROUP );
			$exceptionCaught = true;
		}
		if (!$exceptionCaught) {
			$this->fail('Expected exception: HACLStorageException');
		}
		
		// Try to add an HaloACL group to an LDAP group 
		$exceptionCaught = false;
		try {		
			HACLStorage::getDatabase()->addGroupToGroup($this->mGAID, 42);
		} catch (HACLStorageException $e) {
			$this->assertEquals($e->getCode(), HACLStorageException::CANT_MODIFY_LDAP_GROUP );
			$exceptionCaught = true;
		}
		if (!$exceptionCaught) {
			$this->fail('Expected exception: HACLStorageException');
		}
		
		// Try to add an LDAP group to an HaloACL group 
		
		// Case 1: $haclgAllowLDAPGroupMembers is false
		global $haclgAllowLDAPGroupMembers;
		$haclgAllowLDAPGroupMembers = false;
		
		$exceptionCaught = false;
		try {		
			HACLStorage::getDatabase()->addGroupToGroup(42, $this->mGAID);
		} catch (HACLStorageException $e) {
			$this->assertEquals($e->getCode(), HACLStorageException::CANT_MODIFY_LDAP_GROUP );
			$exceptionCaught = true;
		}
		if (!$exceptionCaught) {
			$this->fail('Expected exception: HACLStorageException');
		}

		// Case 2: $haclgAllowLDAPGroupMembers is true
		global $haclgAllowLDAPGroupMembers;
		$haclgAllowLDAPGroupMembers = true;
		
		$exceptionCaught = false;
		try {		
			HACLStorage::getDatabase()->addGroupToGroup(42, $this->mGAID);
		} catch (HACLStorageException $e) {
			$this->fail("It should be possible to add an LDAP group to a HaloACL group.");
			$exceptionCaught = true;
			$haclgAllowLDAPGroupMembers = false;
		}
		$this->assertFalse($exceptionCaught);
		$haclgAllowLDAPGroupMembers = false;
		
		// Try to add a HaloACL group to an HAloACL group 
		HACLStorage::getDatabase()->addGroupToGroup(42, 1);
		// Verify that the group has a new member
		$group = HACLGroup::newFromID(42);
		$groups = $group->getGroups(HACLGroup::ID);
		$this->assertEquals(count($groups), 3);
		$this->assertEquals(array_search(1, $groups), 0);
		
	}
	
	/**
	 * Tests the method HACLStorage::removeUserFromGroup();
	 */
	function testRemoveUserFromGroup() {
		
		// Try to remove a user from an LDAP group 
		$exceptionCaught = false;
		try {		
			HACLStorage::getDatabase()->removeUserFromGroup($this->mGAID,
															User::idFromName("U1"));
		} catch (HACLStorageException $e) {
			$this->assertEquals($e->getCode(), HACLStorageException::CANT_MODIFY_LDAP_GROUP );
			$exceptionCaught = true;
		}
		if (!$exceptionCaught) {
			$this->fail('Expected exception: HACLStorageException');
		}
		
		// Try to remove a user from a HaloACL group 
		// (first the user has to be added)
		
		HACLStorage::getDatabase()->addUserToGroup(42, User::idFromName("U1"));
		// Verify that the group has a new member
		$group = HACLGroup::newFromID(42);
		$users = $group->getUsers(HACLGroup::NAME);
		$this->assertEquals(array_search("U1", $users), 0);
		
		// now remove the user
		HACLStorage::getDatabase()->removeUserFromGroup(42, User::idFromName("U1"));
		$users = $group->getUsers(HACLGroup::NAME);
		$this->assertEquals(count($users), 0);
		
	}
	
	/**
	 * Tests the method HACLStorage::removeAllMembersFromGroup();
	 */
	function testRemoveAllMembersFromGroup() {
		
		// Try to remove members from an LDAP group 
		$exceptionCaught = false;
		try {		
			HACLStorage::getDatabase()->removeAllMembersFromGroup($this->mGAID);
		} catch (HACLStorageException $e) {
			$this->assertEquals($e->getCode(), HACLStorageException::CANT_MODIFY_LDAP_GROUP );
			$exceptionCaught = true;
		}
		if (!$exceptionCaught) {
			$this->fail('Expected exception: HACLStorageException');
		}
		// Try to remove members from a HAloACL group 
		// (first add a group and a user)
		HACLStorage::getDatabase()->addGroupToGroup(42, 1);
		HACLStorage::getDatabase()->addUserToGroup(42, User::idFromName("U1"));
		// Verify that the group has a new members
		$group = HACLGroup::newFromID(42);
		$groups = $group->getGroups(HACLGroup::ID);
		$this->assertEquals(array_search(1, $groups), 0);
		$users = $group->getUsers(HACLGroup::NAME);
		$this->assertEquals(array_search("U1", $users), 0);
		// now remove all members of the group
		HACLStorage::getDatabase()->removeAllMembersFromGroup(42);
		// Verify that the group has no members
		$groups = $group->getGroups(HACLGroup::ID);
		$this->assertEquals(count($groups), 0);
		$users = $group->getUsers(HACLGroup::NAME);
		$this->assertEquals(count($users), 0);
		
	}
	
	/**
	 * Tests the method HACLStorage::removeGroupFromGroup();
	 */
	function testRemoveGroupFromGroup() {
		
		// Try to remove an LDAP group from an LDAP group 
		$exceptionCaught = false;
		try {		
			HACLStorage::getDatabase()->removeGroupFromGroup($this->mGAID, 
													    	 $this->mGDID);
		} catch (HACLStorageException $e) {
			$this->assertEquals($e->getCode(), HACLStorageException::CANT_MODIFY_LDAP_GROUP );
			$exceptionCaught = true;
		}
		if (!$exceptionCaught) {
			$this->fail('Expected exception: HACLStorageException');
		}
		
		// Try to remove an HaloACL group from an LDAP group 
		$exceptionCaught = false;
		try {		
			HACLStorage::getDatabase()->removeGroupFromGroup($this->mGAID, 42);
		} catch (HACLStorageException $e) {
			$this->assertEquals($e->getCode(), HACLStorageException::CANT_MODIFY_LDAP_GROUP );
			$exceptionCaught = true;
		}
		if (!$exceptionCaught) {
			$this->fail('Expected exception: HACLStorageException');
		}
		
		// Try to remove an LDAP group from an HaloACL group 
		$exceptionCaught = false;
		try {		
			HACLStorage::getDatabase()->removeGroupFromGroup(42, $this->mGAID);
		} catch (HACLStorageException $e) {
			$this->assertEquals($e->getCode(), HACLStorageException::CANT_MODIFY_LDAP_GROUP );
			$exceptionCaught = true;
		}
		if (!$exceptionCaught) {
			$this->fail('Expected exception: HACLStorageException');
		}
		
		// Try to remove a HaloACL group from a HAloACL group 
		// (first add a group)
		HACLStorage::getDatabase()->addGroupToGroup(42, 1);
		// Verify that the group has a new member
		$group = HACLGroup::newFromID(42);
		$groups = $group->getGroups(HACLGroup::ID);
		$this->assertEquals(array_search(1, $groups), 0);
		// now remove the group
		HACLStorage::getDatabase()->removeGroupFromGroup(42, 1);
		// Verify that the group has only one member left
		$groups = $group->getGroups(HACLGroup::ID);
		$this->assertEquals(count($groups), 1);
		
	}
	
	/**
	 * Tests the method HACLStorage::getMembersOfGroup();
	 */
	function testGetMembersOfGroup() {
		$store = HACLStorage::getDatabase();
		
		// Get the users in group GROUP_Administration
		$parentGroup = HACLGroup::newFromName('GROUP_Administration');
		$users = $store->getMembersOfGroup($parentGroup->getGroupID(), 'user');
		// expect the ID of Bianca
		$this->assertEquals(count($users), 1);
		$this->assertEquals(array_search(User::idFromName('Bianca'), $users), 0);
		
		// Get the groups in group GROUP_Administration
		$groups = $store->getMembersOfGroup($parentGroup->getGroupID(), 'group');
		// expect the ID of GROUP_FinancialAdministration
		$this->assertEquals(count($groups), 1);
		$g = HACLGroup::newFromName('GROUP_FinancialAdministration');
		$this->assertEquals(array_search($g->getGroupID(), $groups), 0);
		
		// Get the users in group GROUP_Developer
		$parentGroup = HACLGroup::newFromName('GROUP_Developer');
		$users = $store->getMembersOfGroup($parentGroup->getGroupID(), 'user');
		// expect the ID of Thomas
		$this->assertEquals(count($users), 1);
		$this->assertEquals(array_search(User::idFromName('Thomas'), $users), 0);
		
		// Get the groups in group GROUP_Developer
		$groups = $store->getMembersOfGroup($parentGroup->getGroupID(), 'group');
		// expect no sub-groups
		$this->assertEquals(count($groups), 0);
		
		// Get the users in group GROUP_FinancialAdministration
		$parentGroup = HACLGroup::newFromName('GROUP_FinancialAdministration');
		$users = $store->getMembersOfGroup($parentGroup->getGroupID(), 'user');
		// expect the ID of Judith
		$this->assertEquals(count($users), 1);
		$this->assertEquals(array_search(User::idFromName('Judith'), $users), 0);
		
		// Get the groups in group GROUP_FinancialAdministration
		$groups = $store->getMembersOfGroup($parentGroup->getGroupID(), 'group');
		// expect no sub-groups
		$this->assertEquals(count($groups), 0);

		// Get the users in HaloACL group MyGroup
		$parentGroup = HACLGroup::newFromName('MyGroup');
		// first add a user and a sub-group
		$parentGroup->addUser("U1", "U1");
		$users = $store->getMembersOfGroup($parentGroup->getGroupID(), 'user');
		// expect the ID of U1
		$this->assertEquals(count($users), 1);
		$this->assertEquals(array_search(User::idFromName('U1'), $users), 0);
		
		// Get the groups in group MyGroup
		$groups = $store->getMembersOfGroup($parentGroup->getGroupID(), 'group');
		// expect no sub-groups
		$this->assertEquals(count($groups), 1);
		$g = HACLGroup::newFromName("SubGroup");
		$this->assertEquals(array_search($g->getGroupID(), $groups), 0);
	}
	
	/**
	 * Tests the method HACLStorage::getGroupsOfMember();
	 */
	function testGetGroupsOfMember() {
		
		// Add 'Bianca' to 'MyGroup'
		$mg = HACLGroup::newFromName("MyGroup");
		$mg->addUser('Bianca', 'U1');
		
		// now make sure Bianca is member of MyGroup and GROUP_Administration
		$groups = HACLStorage::getDatabase()->getGroupsOfMember(User::idFromName('bianca'));
		
		$this->assertEquals(count($groups), 2);
		$groupMap = array();
		foreach ($groups as $g) {
			$groupMap[$g['id']] = $g['name'];
		}
		// Check name and ID of "MyGroup"
		$this->assertEquals($groupMap[$mg->getGroupID()], $mg->getGroupName());

		// Check name and ID of "GROUP_Administration"
		$mg = HACLGroup::newFromName("GROUP_Administration");
		$this->assertEquals($groupMap[$mg->getGroupID()], $mg->getGroupName());
		
	}
	
	/**
	 * Tests the method HACLStorage::hasGroupMember();
	 */
	function testHasGroupMember() {
		
		$this->mMyGroup->addUser("U1", "U1");
		
		$g = HACLGroup::newFromName('GROUP_Administration');

		$this->checkGroupMembers("testHasGroupMember - 1g", $g, "group", 
								 array( "MyGroup", false, 
								 		"SubGroup", false, 
								 		"GROUP_Administration", false, 
								 		"GROUP_Developer", false, 
								 		"GROUP_FinancialAdministration", true));

		$this->checkGroupMembers("testHasGroupMember - 1u", $g, "user", 
								 array( "U1", false, 
								 		"Bianca", true, 
								 		"Thomas", false, 
								 		"Judith", true));
								 
		$g = HACLGroup::newFromName('GROUP_Developer');
		$this->checkGroupMembers("testHasGroupMember - 2g", $g, "group", 
								 array( "MyGroup", false, 
								 		"SubGroup", false, 
								 		"GROUP_Administration", false, 
								 		"GROUP_Developer", false, 
								 		"GROUP_FinancialAdministration", false));
		$this->checkGroupMembers("testHasGroupMember - 2u", $g, "user", 
								 array( "U1", false, 
								 		"Bianca", false, 
								 		"Thomas", true, 
								 		"Judith", false));
								 
		$g = HACLGroup::newFromName('GROUP_FinancialAdministration');
		$this->checkGroupMembers("testHasGroupMember - 3g", $g, "group", 
								 array( "MyGroup", false, 
								 		"SubGroup", false, 
								 		"GROUP_Administration", false, 
								 		"GROUP_Developer", false, 
								 		"GROUP_FinancialAdministration", false));
		$this->checkGroupMembers("testHasGroupMember - 3u", $g, "user", 
								 array( "U1", false, 
								 		"Bianca", false, 
								 		"Thomas", false, 
								 		"Judith", true));
								 
		$g = HACLGroup::newFromName('MyGroup');
		$this->checkGroupMembers("testHasGroupMember - 4g", $g, "group", 
								 array( "MyGroup", false, 
								 		"SubGroup", true, 
								 		"GROUP_Administration", false, 
								 		"GROUP_Developer", false, 
								 		"GROUP_FinancialAdministration", false));
		$this->checkGroupMembers("testHasGroupMember - 4u", $g, "user", 
								 array( "U1", true, 
								 		"Bianca", false, 
								 		"Thomas", false, 
								 		"Judith", false));
								 
		$g = HACLGroup::newFromName('SubGroup');
		$this->checkGroupMembers("testHasGroupMember - 5g", $g, "group", 
								 array( "MyGroup", false, 
								 		"SubGroup", false, 
								 		"GROUP_Administration", false, 
								 		"GROUP_Developer", false, 
								 		"GROUP_FinancialAdministration", false));
		$this->checkGroupMembers("testHasGroupMember - 5u", $g, "user", 
								 array( "U1", false, 
								 		"Bianca", false, 
								 		"Thomas", false, 
								 		"Judith", false));
								 
								 
								 
								 
	}
	
	/**
	 * Tests the method HACLStorage::deleteGroup();
	 */
	function testDeleteGroup() {
		// An LDAP group can not be deleted.
		$exceptionCaught = false;
		try {
			HACLStorage::getDatabase()->deleteGroup($this->mGAID);
		} catch (HACLStorageException $e) {
			$this->assertEquals($e->getCode(), HACLStorageException::CANT_MODIFY_LDAP_GROUP );
			$exceptionCaught = true;
		}
		if (!$exceptionCaught) {
			$this->fail('Expected exception: HACLStorageException');
		}
		
		// Delete a HaloACL group
		$g = new HACLGroup(100, "DeletableGroup", null, array("U1"));
		$g->save("U1");
		// Make sure group exists
		$this->assertTrue(HACLStorage::getDatabase()->groupExists($g->getGroupID()));
		HACLStorage::getDatabase()->deleteGroup($g->getGroupID());
		// Make sure group no longer exists
		$this->assertFalse(HACLStorage::getDatabase()->groupExists($g->getGroupID()));
	}
	
	/**
	 * Tests the method HACLStorage::groupExists();
	 */
	function testGroupExists() {
		// Test existence of LDAP groups
		$this->assertTrue(HACLStorage::getDatabase()->groupExists($this->mGAID));
		$this->assertFalse(HACLStorage::getDatabase()->groupExists($this->mGAID+20));
		
		// Test existance of HaloACL groups
		$this->assertTrue(HACLStorage::getDatabase()->groupExists($this->mMyGroup->getGroupID()));
		$this->assertFalse(HACLStorage::getDatabase()->groupExists($this->mMyGroup->getGroupID()+20));
		
	}
	
	/**
	 * Tests the method HACLStorage::isOverloaded();
	 */
	function testIsOverloaded() {
		// The group GROUP_Administration is overloaded.
		$store = HACLStorage::getDatabase();
		$this->assertTrue($store->isOverloaded("GROUP_Administration"));

		// The groups GROUP_Developer and MyGroup are not overloaded.
		$this->assertFalse($store->isOverloaded("GROUP_Developer"));
		$this->assertFalse($store->isOverloaded("MyGroup"));
				
	}
	
   /**
     * Tests searching for groups whose name contains a string
     */
    function testSearchGroups() {
    	$this->doSearchGroups('e');
    	$this->doSearchGroups('o');
    	$this->doSearchGroups('Dev');
    	$this->doSearchGroups('dev');
    	$this->doSearchGroups('group');
    	$this->doSearchGroups('unknown');
    }

    /**
     * Performs the actual tests, searching for groups whose name contains a string
     */
    function doSearchGroups($search) {
    	$expected = array();
    	foreach ($this->mGroupNames as $gn) {
    		if (preg_match("/.*?$search.*/i", $gn)) {
    			$expected[] = $gn;
    		}
    	}
    	$matchingGroups = HACLGroup::searchGroups($search);
    	$mg = array_keys($matchingGroups);
    	sort($mg);
    	sort($expected);
    	$this->assertEquals($expected, $mg);
    }
	
	
	private function checkGroupMembers($testcase, $group, $mode, $membersAndResults) {
		for ($i = 0; $i < count($membersAndResults); $i+=2) {
			$name = $membersAndResults[$i];
			$result    = $membersAndResults[$i+1];
			if ($mode == "user") {
				$this->assertEquals($result, $group->hasUserMember($name, true),
										"Check for group membership failed. ".
										"Expected ".($result?"true":"false")." for ".
				$group->getGroupName()."->hasUserMember($name) (Testcase: $testcase)");
			} else if ($mode == "group") {
				$this->assertEquals($result, $group->hasGroupMember($name, true),
										"Check for group membership failed. ".
										"Expected ".($result?"true":"false")." for ".
				$group->getGroupName()."->hasGroupMember($name) (Testcase: $testcase)");
			}
		}
	}
	
}

class TestLDAPGroupMembers extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = FALSE;
	protected $mDoubleGroup = null;
	protected $mMyGroup = null;
	protected $mSubGroup = null;
	protected $mGAID;  // ID of GROUP_Administration
	protected $mGDID;  // ID of GROUP_Developer
	protected $mGFAID;  // ID of GROUP_FinancialAdministration;
	
	function setUp() {
		
		// Proceed with the LDAP DB
		HACLStorage::reset(HACL_STORE_LDAP);
		
		// In addition to the LDAP groups, we create two normal HaloACL groups
		$this->mMyGroup = new HACLGroup(42, "MyGroup", null, array("U1"));
		$this->mMyGroup->save("U1");
		$this->mMyGroup->addUser("U1", "U1");
		$this->mSubGroup = new HACLGroup(43, "SubGroup", null, array("U1"));
		$this->mSubGroup->addUser("U2", "U1");
		$this->mSubGroup->save("U1");
		$this->mMyGroup->addGroup($this->mSubGroup, "U1");
		
		
		$g = HACLGroup::newFromName('GROUP_Administration');
		$this->mGAID = $g->getGroupID();
		$g = HACLGroup::newFromName('GROUP_Developer');
		$this->mGDID = $g->getGroupID();
		$g = HACLGroup::newFromName('GROUP_FinancialAdministration');
		$this->mGFAID = $g->getGroupID();
		
		// Make GROUP_Administration subgroup of SubGroup
		
		global $haclgAllowLDAPGroupMembers;
		$algm = $haclgAllowLDAPGroupMembers;
		$haclgAllowLDAPGroupMembers = true;
		HACLStorage::getDatabase()->addGroupToGroup(43, $this->mGAID);
		$haclgAllowLDAPGroupMembers = $algm;
		
		
	}

	function tearDown() {
		$this->mMyGroup->delete("U1");
		$this->mSubGroup->delete("U1");
	}
	
	
	/**
	 * Tests the method HACLStorage::hasGroupMember() or the case that LDAP
	 * groups are members of HaloACL groups.
	 * 
	 */
	function testHasGroupMember() {
		
		$g = HACLGroup::newFromName('GROUP_Administration');

		$this->checkGroupMembers("testHasGroupMember - 1g", $g, "group", 
								 array( "MyGroup", false, 
								 		"SubGroup", false, 
								 		"GROUP_Administration", false, 
								 		"GROUP_Developer", false, 
								 		"GROUP_FinancialAdministration", true));

		$this->checkGroupMembers("testHasGroupMember - 1u", $g, "user", 
								 array( "U1", false,
								 		"U2", false, 
								 		"Bianca", true, 
								 		"Thomas", false, 
								 		"Judith", true));
								 
		$g = HACLGroup::newFromName('GROUP_Developer');
		$this->checkGroupMembers("testHasGroupMember - 2g", $g, "group", 
								 array( "MyGroup", false, 
								 		"SubGroup", false, 
								 		"GROUP_Administration", false, 
								 		"GROUP_Developer", false, 
								 		"GROUP_FinancialAdministration", false));
		$this->checkGroupMembers("testHasGroupMember - 2u", $g, "user", 
								 array( "U1", false, 
								 		"U2", false, 
								 		"Bianca", false, 
								 		"Thomas", true, 
								 		"Judith", false));
								 
		$g = HACLGroup::newFromName('GROUP_FinancialAdministration');
		$this->checkGroupMembers("testHasGroupMember - 3g", $g, "group", 
								 array( "MyGroup", false, 
								 		"SubGroup", false, 
								 		"GROUP_Administration", false, 
								 		"GROUP_Developer", false, 
								 		"GROUP_FinancialAdministration", false));
		$this->checkGroupMembers("testHasGroupMember - 3u", $g, "user", 
								 array( "U1", false, 
								 		"U2", false, 
								 		"Bianca", false, 
								 		"Thomas", false, 
								 		"Judith", true));
								 
		$g = HACLGroup::newFromName('MyGroup');
		$this->checkGroupMembers("testHasGroupMember - 4g", $g, "group", 
								 array( "MyGroup", false, 
								 		"SubGroup", true, 
								 		"GROUP_Administration", true, 
								 		"GROUP_Developer", false, 
								 		"GROUP_FinancialAdministration", true));
		$this->checkGroupMembers("testHasGroupMember - 4u", $g, "user", 
								 array( "U1", true, 
								 		"U2", true, 
								 		"Bianca", true, 
								 		"Thomas", false, 
								 		"Judith", true));
								 
		$g = HACLGroup::newFromName('SubGroup');
		$this->checkGroupMembers("testHasGroupMember - 5g", $g, "group", 
								 array( "MyGroup", false, 
								 		"SubGroup", false, 
								 		"GROUP_Administration", true, 
								 		"GROUP_Developer", false, 
								 		"GROUP_FinancialAdministration", true));
		$this->checkGroupMembers("testHasGroupMember - 5u", $g, "user", 
								 array( "U1", false, 
								 		"U2", true, 
								 		"Bianca", true, 
								 		"Thomas", false, 
								 		"Judith", true));
								 
								 
								 
								 
	}
	
	/**
	 * Tests the method User::getEffectiveGroups() which includes Mediawiki-,
	 * LDAP- and HaloACL-groups.
	 */
	function testGetGroupsOfUser() {
		
		$this->checkGroupsOfMember("Bianca", 
									array("GROUP_Administration", 
										  "SubGroup",
										  "MyGroup",
										  "*", "user", "autoconfirmed"));
						
		$this->checkGroupsOfMember("Judith", 
									array("GROUP_FinancialAdministration",
										  "GROUP_Administration", 
										  "SubGroup",
										  "MyGroup",
										  "*", "user", "autoconfirmed"));

		$this->checkGroupsOfMember("Thomas", 
									array("GROUP_Developer", 
										  "*", "user", "autoconfirmed"));
									
	}
	
	
	private function checkGroupMembers($testcase, $group, $mode, $membersAndResults) {
		for ($i = 0; $i < count($membersAndResults); $i+=2) {
			$name = $membersAndResults[$i];
			$result    = $membersAndResults[$i+1];
			if ($mode == "user") {
				$this->assertEquals($result, $group->hasUserMember($name, true),
										"Check for group membership failed. ".
										"Expected ".($result?"true":"false")." for ".
				$group->getGroupName()."->hasUserMember($name) (Testcase: $testcase)");
			} else if ($mode == "group") {
				$this->assertEquals($result, $group->hasGroupMember($name, true),
										"Check for group membership failed. ".
										"Expected ".($result?"true":"false")." for ".
				$group->getGroupName()."->hasGroupMember($name) (Testcase: $testcase)");
			}
		}
	}
	
	/**
	 * Checks if a user is member of a list of expected groups.
	 * @param String $member
	 * 		Name of user
	 * @param array<string> $expectedGroups
	 * 		Expected groups for $member
	 */
	private function checkGroupsOfMember($member, $expectedGroups) {
		$u = User::newFromName($member);
		$actualGOM = $u->getEffectiveGroups();
		$unexpectedGOM = array_diff($actualGOM, $expectedGroups);
		$msg = "";
		if (!empty($unexpectedGOM)) {
			$msg = "Check for groups of a user failed.\n".
				   "User '$member' is not expected to be member of the following groups:\n";
			foreach ($unexpectedGOM as $gn) {
				$msg .= "* $gn\n";
			} 
		}

		$missingGOM = array_diff($expectedGroups, $actualGOM);
		if (!empty($missingGOM)) {
			$msg .= "\nCheck for groups of a user failed.\n".
				   "User '$member' is expected to be member of the following groups:\n";
			foreach ($missingGOM as $gn) {
				$msg .= "* $gn\n";
			} 
		}
		if (!empty($msg)) {
			$this->fail($msg);
		}
		
	}
	
	
}

/**
 * @author thsc
 *
 * This class tests the functions of the class HACLStorageLDAP. It assumes that
 * an LDAP server (e.g. OpenLDAP) is running and configured according to the
 * LDAP settings in LocalSettings.php.
 *
 */
class TestLDAPStorage extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = FALSE;
	
	protected $mGAID;  // ID of GROUP_Administration
	protected $mGDID;  // ID of GROUP_Developer
	protected $mGFAID;  // ID of GROUP_FinancialAdministration;
	

	function setUp() {
		$g = HACLGroup::newFromName('GROUP_Administration');
		$this->mGAID = $g->getGroupID();
		$g = HACLGroup::newFromName('GROUP_Developer');
		$this->mGDID = $g->getGroupID();
		$g = HACLGroup::newFromName('GROUP_FinancialAdministration');
		$this->mGFAID = $g->getGroupID();
		
	}

	function tearDown() {

	}

	/**
	 * Tests the method HACLStorage::groupNameForID();
	 */
	function testGroupNameForID() {
		$name = HACLStorage::getDatabase()->groupNameForID($this->mGAID);
		$this->assertEquals($name, 'GROUP_Administration');
		$name = HACLStorage::getDatabase()->groupNameForID($this->mGDID);
		$this->assertEquals($name, 'GROUP_Developer');
		$name = HACLStorage::getDatabase()->groupNameForID($this->mGFAID);
		$this->assertEquals($name, 'GROUP_FinancialAdministration');
	}

	/**
	 * Tests the method HACLStorage::saveGroup();
	 */
	function testSaveGroup() {
		// Retrieve all groups
		$groups = HACLStorage::getDatabase()->getGroups();
			
		$groupMap = array();
		foreach ($groups as $group) {
			$groupMap[$group->getGroupID()] = $group;
		}
		
		// An LDAP group can not be saved.
		$exceptionCaught = false;
		try {
			HACLStorage::getDatabase()->saveGroup($groupMap[$this->mGAID]);
		} catch (HACLStorageException $e) {
			$this->assertEquals($e->getCode(), HACLStorageException::CANT_SAVE_LDAP_GROUP );
			$exceptionCaught = true;
		}
		if (!$exceptionCaught) {
			$this->fail('Expected exception: HACLStorageException');
		}
		
		// A HaloACL group can not be saved with the same name as an existing 
		// LDAP group.
		$exceptionCaught = false;
		$g = new HACLGroup(42, "GROUP_Administration", null, array("U1"));
		try {
			$g->save("U1");
		} catch (HACLStorageException $e) {
			$this->assertEquals($e->getCode(), HACLStorageException::SAME_GROUP_IN_LDAP);
			$exceptionCaught = true;
		}
		if (!$exceptionCaught) {
			$this->fail('Expected exception: HACLStorageException');
		}
		
	}

}
