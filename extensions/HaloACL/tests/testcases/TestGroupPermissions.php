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
		$suite->addTestSuite('TestMWGroupPermissionsUI');
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
 * This class tests operations on Mediawiki groups and users and group permissions.
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
		
		global $haclgFeature, $wgGroupPermissions;
		$haclgFeature = array();
		$wgGroupPermissions = array();
		
		HACLGroupPermissions::deleteAllPermissions();
	}

	function tearDown() {
		TestGroupPermissionsSuite::$mGroup->removeUser("U1", "U1");
		TestGroupPermissionsSuite::$mSubGroup->removeUser("U2", "U1");
		
		global $haclgFeature, $wgGroupPermissions;
		$haclgFeature = array();
		$wgGroupPermissions = array();
		
		HACLGroupPermissions::deleteAllPermissions();
		
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
	 * Upon startup of the wiki the default permissions in all $haclgFeature
	 * elements have to be translated into values of $wgGroupPermissions.
	 * Test the function HACLGroupPermissions::initGroupPermissions().
	 */
	function testCreateDefaultPermissions1() {
		global $haclgFeature, $wgGroupPermissions;
		
		$haclgFeature['upload']['systemfeatures'] = "upload|reupload|reupload-own|reupload-shared|upload_by_url";
		$haclgFeature['upload']['name'] = "Upload";
		$haclgFeature['upload']['description'] = "This is the feature for uploading files into the wiki.";
		$haclgFeature['upload']['permissibleBy'] = "admin"; // The other alternative would be "all"
		$haclgFeature['upload']['default'] = "permit"; // The other alternative would be "deny"
		
		HACLGroupPermissions::initDefaultPermissions();
		
		// Check if $wgGroupPermissions is correctly initialized
		$this->assertEquals(true, $wgGroupPermissions['*']['upload']);
		$this->assertEquals(true, $wgGroupPermissions['*']['reupload']);
		$this->assertEquals(true, $wgGroupPermissions['*']['reupload-own']);
		$this->assertEquals(true, $wgGroupPermissions['*']['reupload-shared']);
		$this->assertEquals(true, $wgGroupPermissions['*']['upload_by_url']);
		
	}

	/**
	 * Test exception handling of an erroneous $haclgFeature in 
	 * HACLGroupPermissions::initGroupPermissions().
	 * The entry 'systemfeatures' is empty.
	 */
	function testCreateDefaultPermissions2() {
		global $haclgFeature;
		unset($haclgFeature['upload']);
		
		// The system features are missing / empty
		// => expect exception HACLGroupPermissionsException
		$haclgFeature['upload']['systemfeatures'] = "";
		
		$haclgFeature['upload']['name'] = "Upload";
		$haclgFeature['upload']['description'] = "This is the feature for uploading files into the wiki.";
		$haclgFeature['upload']['permissibleBy'] = "admin"; // The other alternative would be "all"
		$haclgFeature['upload']['default'] = "permit"; // The other alternative would be "deny"
		
		try {
			HACLGroupPermissions::initDefaultPermissions();
		} catch (HACLGroupPermissionsException $e) {
			$this->assertEquals(HACLGroupPermissionsException::MISSING_PARAMETER,
								$e->getCode(), "Expected exception HACLGroupPermissionsException::MISSING_SYSTEMFEATURES_ID");
			return;
		}
		$this->fail("Expected exception HACLGroupPermissionsException::MISSING_SYSTEMFEATURES_ID");
		
	}

	/**
	 * Test exception handling of an erroneous $haclgFeature in 
	 * HACLGroupPermissions::initGroupPermissions().
	 * The entry 'default' is missing.
	 */
	function testCreateDefaultPermissions3() {
		global $haclgFeature;
		unset($haclgFeature['upload']);
		
		// The system features are missing / empty
		// => expect exception HACLGroupPermissionsException
		$haclgFeature['upload']['systemfeatures'] = "upload";
		
		$haclgFeature['upload']['name'] = "Upload";
		$haclgFeature['upload']['description'] = "This is the feature for uploading files into the wiki.";
		$haclgFeature['upload']['permissibleBy'] = "admin"; // The other alternative would be "all"
		
		try {
			HACLGroupPermissions::initDefaultPermissions();
		} catch (HACLGroupPermissionsException $e) {
			$this->assertEquals(HACLGroupPermissionsException::MISSING_PARAMETER,
								$e->getCode(), "Expected exception HACLGroupPermissionsException::MISSING_SYSTEMFEATURES_ID");
			return;
		}
		$this->fail("Expected exception HACLGroupPermissionsException::MISSING_SYSTEMFEATURES_ID");
		
	}
	
	/**
	 * Test exception handling of an erroneous $haclgFeature in 
	 * HACLGroupPermissions::initGroupPermissions()
	 * The default value is neither 'permit' nor 'deny'.
	 */
	function testCreateDefaultPermissions4() {
		global $haclgFeature;
		unset($haclgFeature['upload']);
		
		// The system features are missing / empty
		// => expect exception HACLGroupPermissionsException
		$haclgFeature['upload']['systemfeatures'] = "upload";
		
		$haclgFeature['upload']['name'] = "Upload";
		$haclgFeature['upload']['description'] = "This is the feature for uploading files into the wiki.";
		$haclgFeature['upload']['permissibleBy'] = "admin"; // The other alternative would be "all"
		$haclgFeature['upload']['default'] = "invalid"; // The other alternative would be "deny"
		
		try {
			HACLGroupPermissions::initDefaultPermissions();
		} catch (HACLGroupPermissionsException $e) {
			$this->assertEquals(HACLGroupPermissionsException::INVALID_PARAMETER_VALUE,
								$e->getCode(), "Expected exception HACLGroupPermissionsException::INVALID_PARAMETER_VALUE");
			return;
		}
		$this->fail("Expected exception HACLGroupPermissionsException::INVALID_PARAMETER_VALUE");
		
	}
	
	/**
	 * Group permissions for features are related to groups. The storage layer
	 * has to store which feature is permitted/denied for which groups.
	 * This function tests the storage layer.
	 */
	public function testFeaturePermissionStorage() {

		$gid = TestGroupPermissionsSuite::$mGroup->getGroupID();
		
		// Store a permission
		HACLGroupPermissions::storePermission($gid, 'upload', true);
		$permission = HACLGroupPermissions::getPermission($gid, 'upload');
		$this->assertEquals(true, $permission, "Expected permission to be 'true'");
		
		// Overwrite a permission
		HACLGroupPermissions::storePermission($gid, 'upload', false);
		$permission = HACLGroupPermissions::getPermission($gid, 'upload');
		$this->assertEquals(false, $permission, "Expected permission to be 'false'");
		
		// Store some permissions for a group
		HACLGroupPermissions::storePermission($gid, 'read', false);
		HACLGroupPermissions::storePermission($gid, 'edit', true);
		HACLGroupPermissions::storePermission($gid, 'move', true);
		$permissions = HACLGroupPermissions::getPermissionsForGroup($gid);
		$expected = array(
			"upload" => false,
			"read" => false,
			"edit" => true,
			"move" => true,
		);
		$this->assertEquals($expected, $permissions, "Incorrect permissions were retrieved for a group.");
		
		// Delete the permission for a group
		HACLGroupPermissions::deletePermission($gid, 'read');
		$permissions = HACLGroupPermissions::getPermissionsForGroup($gid);
		$this->assertArrayNotHasKey('read', $permissions, "Expected permission <read> to be deleted.");
		
		// Delete all permissions and check if they are gone
		HACLGroupPermissions::deleteAllPermissions();
		$permissions = HACLGroupPermissions::getPermissionsForGroup($gid);
		$this->assertEquals(0, count($permissions), "Expected that all permissions are deleted");
		
	}
	
	/**
	 * After default permissions have been created the group permissions stored 
	 * in the DB have to be read and translated in $wgGroupPermissions. 
	 * This function checks that this is working correctly.
	 */
	public function testInitPermissionsFromDB() {
		$gid = TestGroupPermissionsSuite::$mGroup->getGroupID();
		global $haclgFeature;
		$haclgFeature['read']['systemfeatures'] = "read";
		$haclgFeature['read']['name'] = "Read";
		
		$haclgFeature['upload']['systemfeatures'] = "upload|reupload|reupload-own|reupload-shared|upload_by_url";
		$haclgFeature['upload']['name'] = "Upload";

		$haclgFeature['edit']['systemfeatures'] = "edit|createpage|createtalk";
		$haclgFeature['edit']['name'] = "Edit";
		
		$permissions = array(
			'read'   => array($gid, 'read', true),
			'upload' => array($gid, 'upload', true),
			'edit'   => array($gid, 'edit', false),
		);
		
		// Store all permission for features
		foreach ($permissions as $p) {
			HACLGroupPermissions::storePermission($p[0], $p[1], $p[2]);
		}
		
		// Initialize $wgGroupPermissions from the stored permissions
		HACLGroupPermissions::initPermissionsFromDB();
		
		// Verify $wgGroupPermissions
		$group = TestGroupPermissionsSuite::$mGroup->getGroupName();
		global $wgGroupPermissions;
		foreach ($haclgFeature as $fn => $f) {
			$sysFeatures = explode('|', $f['systemfeatures']);
			$permitted = $permissions[$fn][2];
			
			// Does the group entry exist?
			$this->assertArrayHasKey($group, $wgGroupPermissions);
			
			foreach ($sysFeatures as $sf) {
				// Are the correct permissions set?
				$this->assertEquals($permitted, $wgGroupPermissions[$group][$sf]);
			}
		}
	}

	/**
	 * After default permissions have been created the group permissions stored 
	 * in the DB have to be read and translated in $wgGroupPermissions. 
	 * This function checks that exception is thrown for unknown features.
	 */
	public function testInitPermissionsFromDBException() {
		$gid = TestGroupPermissionsSuite::$mGroup->getGroupID();
		
		// Store an unknown features
		HACLGroupPermissions::storePermission($gid, 'unknown feature', true);
		
		// Initialize $wgGroupPermissions from the stored permissions
		try {
			HACLGroupPermissions::initPermissionsFromDB();
		} catch (HACLGroupPermissionsException $e) {
			$this->assertEquals(HACLGroupPermissionsException::UNKNOWN_FEATURE,
								$e->getCode(), 
								"Caught unexpected exception code.");
			return;
		}
		$this->fail("Expectec exception HACLGroupPermissionsException::UNKNOWN_FEATURE.");
		
	}
	
	/**
	 * Checks the complete functionality of the group permission feature by
	 * checking if the Main page is accessible after group permissions have been
	 * intitializes.
	 * 
	 */
	public function testPermissions() {
		global $wgUser;
		$wgUser = User::newFromName('U1');
		// U1 is member of group "Group"		
		
		global $wgGroupPermissions;
		$wgGroupPermissions['*']['read'] = true;
		
		// No permissions set so far
		// => Main page should be accessible (read)
		$t = Title::newFromText("Main_Page");
		$canRead = $t->userCan('read');
		$this->assertTrue($canRead, "Expected that Main Page is accessible.");
		
		
		// Initialize default permissions
		// => Main page should not be accessible (read)
		global $haclgFeature;
		$haclgFeature['readFeature']['systemfeatures'] = "read";
		$haclgFeature['readFeature']['name'] = "Read";
		$haclgFeature['readFeature']['default'] = "deny";
		
		HACLGroupPermissions::initDefaultPermissions();
		$wgUser = User::newFromName('U1');
		
		$canRead = $t->userCan('read');
		$this->assertFalse($canRead, "Expected that Main Page is not accessible.");
		
		// Read group permissions from DB
		// => Main page should be accessible (read)
		$gid = TestGroupPermissionsSuite::$mGroup->getGroupID();
		
		HACLGroupPermissions::storePermission($gid, 'readFeature', true);
		
		// Initialize $wgGroupPermissions from the stored permissions
		HACLGroupPermissions::initPermissionsFromDB();
		$wgUser = User::newFromName('U1');
		
		$canRead = $t->userCan('read');
		$this->assertTrue($canRead, "Expected that Main Page is accessible.");
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
 * This class tests the backend of the user interface for group permissions.
 * 
 * It assumes that the HaloACL extension is enabled in LocalSettings.php
 * 
 * @author thsc
 *
 */
class TestMWGroupPermissionsUI extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = FALSE;
	
	function setUp() {
		global $haclgFeature, $wgGroupPermissions;
		$haclgFeature = array();
		$wgGroupPermissions = array();
		HACLStorage::reset(HACL_STORE_SQL);
		HACLStorage::getDatabase()->dropDatabaseTables(false);
		HACLStorage::getDatabase()->initDatabaseTables(false);
		
    	global $wgUser;
    	$wgUser = User::newFromName("U1");
    	
    	//-- Set up groups --
    	$c = new HACLGroup(42, "Group/Company", null, array("U1"));
    	$c->save();
    	$m = new HACLGroup(43, "Group/Marketing", null, array("U1"));
    	$m->save();
    	$d = new HACLGroup(44, "Group/Development", null, array("U1"));
    	$d->save();
    	$hd = new HACLGroup(45, "Group/HaloDev", null, array("U1"));
	   	$hd->save();
    	$dn = new HACLGroup(46, "Group/DevNull", null, array("U1"));
	   	$dn->save();
    	$s = new HACLGroup(47, "Group/Services", null, array("U1"));
    	$s->save();
    	$ps = new HACLGroup(48, "Group/ProfessionalServices", null, array("U1"));
    	$ps->save();
    	$ds = new HACLGroup(49, "Group/DilettantishServices", null, array("U1"));
    	$ds->save();
    	
    	
    	$c->addGroup("Group/Marketing");
    	$c->addGroup("Group/Development");
    	$c->addGroup("Group/Services");
    	
    	$d->addGroup("Group/HaloDev");
    	$d->addGroup("Group/DevNull");
    	
    	$s->addGroup("Group/ProfessionalServices");
    	$s->addGroup("Group/DilettantishServices");
		
		HACLGroupPermissions::deleteAllPermissions();
		
		// Setup some group permissions
		global $haclgFeature;
		$haclgFeature['read']['systemfeatures'] = "read";
		$haclgFeature['read']['name'] = "Read";
		
		$haclgFeature['upload']['systemfeatures'] = "upload|reupload|reupload-own|reupload-shared|upload_by_url";
		$haclgFeature['upload']['name'] = "Upload";

		$haclgFeature['edit']['systemfeatures'] = "edit|createpage|createtalk";
		$haclgFeature['edit']['name'] = "Edit";
		
		$permissions = array(
			"Group/Company"					=> array('read|true', 'upload|true', 'edit|true'),
			"Group/Marketing" 				=> array('read|false', 'upload|true'),
			"Group/Development" 			=> array('read|true', 'edit|false'),
			"Group/Services" 				=> array('upload|false', 'edit|true'),
			"Group/DevNull" 				=> array('upload|true'),
			"Group/ProfessionalServices"	=> array('edit|true'),
			"Group/DilettantishServices"	=> array(),
		);
		
		// Store all permission for features
		foreach ($permissions as $gn => $p) {
			$gid = HACLGroup::idForGroup($gn);
			foreach ($p as $allowed) {
				list($feature, $allowed) = explode('|', $allowed);
				$allowed = $allowed === 'true';
				HACLGroupPermissions::storePermission($gid, $feature, $allowed);
			}
		}
		
	}

	function tearDown() {
		HACLStorage::reset(HACL_STORE_SQL);
		HACLStorage::getDatabase()->dropDatabaseTables(false);
		HACLStorage::getDatabase()->initDatabaseTables(false);
						
		global $haclgFeature, $wgGroupPermissions;
		$haclgFeature = array();
		$wgGroupPermissions = array();
		
		HACLGroupPermissions::deleteAllPermissions();
		
	}
	
	/**
	 * Checks the existence of class HACLUIGroupPermissions
	 */
	public function testHACLUIGroupPermissions() {
		$hui = new HACLUIGroupPermissions();
	}
	
	
	/**
	 * Checks getting the child groups of a group in JSON format
	 */
	public function testUIgetGroupChildren() {
		$gid = HACLGroup::idForGroup('Group/Company');
		$sgid = HACLGroup::idForGroup('Group/Marketing');

		// Check generated JSON for ROOT
		$json = HACLUIGroupPermissions::getGroupChildren("---ROOT---", "read");
	
		$expected = <<<JSON
[			
	{
		attributes: {
			id:"haclgt--1"
		},
		data: "*Allusers*<spanclass=\"tree-haloacl-checknormal\"></span>"
	},
	{
		attributes: {
			id: "haclgt--2"
		},
		data: "*Registeredusers*<spanclass=\"tree-haloacl-checknormal\"></span>"
	},
	{
		attributes: { 
			id : "haclgt-$gid" 
		}, 
		data: "Company <span class=\"tree-haloacl-permitted-features\"title=\"edit,read,upload\">
		               </span>
		               <span class=\"tree-haloacl-checkchecked\">
		               </span>", 
		state: "closed"
	}
]
JSON;
		$json = preg_replace("/\s*/", "", $json);
		$expected = preg_replace("/\s*/", "", $expected);
		$this->assertEquals($expected, $json, "Wrong JSON for ROOT group");
		

		// Check generated JSON for group "Group/Company"
		$json = HACLUIGroupPermissions::getGroupChildren($gid, "upload");
		$expected = <<<JSON
[			
	{
		attributes: { 
			id : "haclgt-43" 
		}, 
		data: "Marketing <span class=\"tree-haloacl-permitted-features\" title=\"upload\"></span>
				<span class=\"tree-haloacl-check checked\"></span>"
	},			
	{
		attributes: { 
			id : "haclgt-44" 
		}, 
		data: "Development <span class=\"tree-haloacl-permitted-features\" title=\"read\"></span>
				<span class=\"tree-haloacl-check normal\"></span>"
		,state: "closed"
	},
	{
		attributes: { 
			id : "haclgt-47" 
		}, 
		data: "Services <span class=\"tree-haloacl-permitted-features\" title=\"edit\"></span>
				<span class=\"tree-haloacl-check crossed\"></span>"
		,state: "closed"
	}
]
JSON;
		$json = preg_replace("/\s*/", "", $json);
		$expected = preg_replace("/\s*/", "", $expected);
		$this->assertEquals($expected, $json, "Wrong JSON for groups in <Group/Company>");

		// Check generated JSON for group "Marketing"
		$json = HACLUIGroupPermissions::getGroupChildren($sgid, "edit");
		$expected = <<<JSON
[]
JSON;
		$json = preg_replace("/\s*/", "", $json);
		$expected = preg_replace("/\s*/", "", $expected);
		$this->assertEquals($expected, $json, "Wrong JSON for groups in <Group/Marketing>");

		
	}
	
	/**
	 * Tests finding all groups that match a filter.
	 */
	public function testUIsearchMatchingGroups() {
		 $groups = HACLUIGroupPermissions::searchMatchingGroups('dev');
		 $exp = "haclgt-42,haclgt-42,haclgt-44,haclgt-42,haclgt-44";
		 $this->assertEquals($exp, $groups);

		 $groups = HACLUIGroupPermissions::searchMatchingGroups('prof');
		 $exp = "haclgt-42,haclgt-47";
		 $this->assertEquals($exp, $groups);
		 
		 $groups = HACLUIGroupPermissions::searchMatchingGroups('o');
		 $exp = "haclgt-42,haclgt-42,haclgt-44,haclgt-42,haclgt-47";
		 $this->assertEquals($exp, $groups);

		 $groups = HACLUIGroupPermissions::searchMatchingGroups('group');
		 $exp = "";
		 $this->assertEquals($exp, $groups);
	}
	
}