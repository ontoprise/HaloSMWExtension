<?php
/**
 * @file
 * @ingroup HaloACL_Tests
 */

/**
 * This suite test Mediawiki group permissions with HaloACL groups. Groups
 * that are defined in HaloACL can be used in rights defined with 
 * $wgGroupPermissions.
 * 
 * @author thsc
 *
 */
class TestGroupPermissionsSuite extends PHPUnit_Framework_TestSuite
{
	public static $mGroup;
	public static $mSubGroup;
	
	public static function suite() {
		
		$suite = new TestGroupPermissionsSuite();
		$suite->addTestSuite('TestMWGroupsStorage');
		return $suite;
	}
	
	protected function setUp() {
		
    	User::createNew("U1");
    	User::createNew("U2");
    	User::idFromName("U1");  
    	User::idFromName("U2");  
    	Skin::getSkinNames();
    	
    	HACLStorage::reset(HACL_STORE_SQL);
		HACLStorage::getDatabase();
		
		// Create two normal HaloACL groups
		self::$mGroup = new HACLGroup(42, "Group", null, array("U1"));
		self::$mGroup->save("U1");
		self::$mSubGroup = new HACLGroup(43, "SubGroup", null, array("U1"));
		self::$mSubGroup->save("U1");
		self::$mGroup->addGroup(self::$mSubGroup, "U1");
		
	}
	
	protected function tearDown() {
		self::$mGroup->delete("U1");
		self::$mSubGroup->delete("U1");
	}

}


/**
 * This class tests operations on Mediawiki groups and users.
 * 
 * It assumes that the HaloACL extension is enabled in LocalSettings.php
 * 
 * @author thsc
 *
 */
class TestMWGroupsStorage extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = FALSE;
	
	function setUp() {
		// U1 is member of "Group" and U2 is member of "SubGroup"
		TestGroupPermissionsSuite::$mGroup->addUser("U1", "U1");
		TestGroupPermissionsSuite::$mSubGroup->addUser("U2", "U1");
		
	}

	function tearDown() {
		TestGroupPermissionsSuite::$mGroup->removeUser("U1", "U1");
		TestGroupPermissionsSuite::$mSubGroup->removeUser("U2", "U1");
		
	}
	

	/**
	 * Tests the method HACLStorage::getGroups();
	 */
	function testGetGroupsOfUser() {
		
		// We expect that U1 is member of "Group"
		$this->checkGroupsOfMember("U1", array("Group", "*", "user", "autoconfirmed"));
						
		// We expect that U2 is member of "Group" and "SubGroup"
		$this->checkGroupsOfMember("U2", array("Group", "SubGroup", "*", "user", "autoconfirmed"));
				
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

