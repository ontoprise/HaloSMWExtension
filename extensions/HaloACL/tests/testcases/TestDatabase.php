<?php
require_once 'PHPUnit/Framework.php';

class TestDatabase extends PHPUnit_Framework_TestCase {

	var $saveGlobals = array();
	
	private $mArticles;
	private $mOrderOfArticleCreation;
	
    function setUp() {
    	User::createNew("U1");
    	User::createNew("U2");
        User::createNew("U3");
        User::createNew("U4");
        User::createNew("U5");
        User::createNew("U6");
    }

    function tearDown() {

    }

    function testRunTests() {
    	try {
	    	$this->createArticles();
	    	$this->setupGroups();
	    	$this->setupRights();
	    	$this->checkRights();
	    	$this->removeRights();
	    	$this->removeGroups();
	    	$this->whitelist();
			$this->removeArticles();    	
    	} catch (Exception $e) {
			$this->removeArticles();    
			throw $e;	
    	}
    }
    
    function setupGroups() {
    	$file = __FILE__;
    	global $wgUser;
    	$wgUser = User::newFromName("U1");
		try {

			// Example according to Design document (with small changes):
			// http://dmwiki.ontoprise.com:8888/dmwiki/index.php/Darkmatter:Software_Design_for_ACL#An_example_of_ACLs_in_the_database
			
			//-- Set up groups --
			$g1 = new HACLGroup(null, "Group/G1", null, array("U1"));
			$g1->save();
			$g2 = new HACLGroup(null, "Group/G2", null, array("U1"));
			$g2->save();
			$g3 = new HACLGroup(null, "Group/G3", null, array("U1"));
			$g3->save();
			$g5 = new HACLGroup(null, "Group/G5", null, array("U1","U4","U5"));
			$g5->save();
			$g4 = new HACLGroup(null, "Group/G4", array("Group/G5"), array("U1"));
			$g4->save();
			
			$g1->addGroup("Group/G2");
			$g1->addGroup("Group/G3");
			
			$g2->addGroup("Group/G4");
			$g2->addGroup("Group/G5");
			
			$g3->addGroup("Group/G4");
			$g3->addUser("U6");
			
			$g4->addUser("U4");
			$g4->addUser("U5");
			
			$g5->addUser("U2");
			$g5->addUser("U3");
			$g5->addUser("U4");

			// TD1: Test the settings of the groups
			// Read groups from the database
			$g1 = HACLGroup::newFromName("Group/G1");
			$g2 = HACLGroup::newFromName("Group/G2");
			$g3 = HACLGroup::newFromName("Group/G3");
			$g4 = HACLGroup::newFromName("Group/G4");
			$g5 = HACLGroup::newFromName("Group/G5");
			$this->assertNotNull($g1, "Test TD1a failed in ".basename($file));
			$this->assertNotNull($g2, "Test TD1b failed in ".basename($file));
			$this->assertNotNull($g3, "Test TD1c failed in ".basename($file));
			$this->assertNotNull($g4, "Test TD1d failed in ".basename($file));
			$this->assertNotNull($g5, "Test TD1e failed in ".basename($file));
			
			// TD2: There is no direct user in Group/G1
			$g1u = $g1->getUsers(HACLGroup::NAME);
			$this->assertTrue(count($g1u) == 0, "Test TD2 failed in ".basename($file));
			
			// TD3: There are 2 direct sub-groups in Group/G1
			$g1g = $g1->getGroups(HACLGroup::NAME);
			$this->assertTrue(count($g1g) == 2, "Test TD3 failed in ".basename($file));
			$this->assertContains("Group/G2", $g1g, "Test TD3 failed in ".basename($file));
			$this->assertContains("Group/G3", $g1g, "Test TD3 failed in ".basename($file));
			
			// TD4: U2 is not allowed to modify "Group/G1"
			$exceptionCaught = false;
			try {
				$g1->removeUser("U1", "U2");
			} catch (HACLGroupException $e) {
				if ($e->getCode() == HACLGroupException::USER_CANT_MODIFY_GROUP) {
					$exceptionCaught = true;
				}
			}
			$this->assertTrue($exceptionCaught, "Test TD4 failed in ".basename($file));
			
			// TD 5: Get the users who can modify Group/G1
			//       => expected U1
			$mu = $g1->getManageUsers();
			$this->assertTrue(count($mu) == 1, "Test TD5 failed in ".basename($file));
			$uid = User::idFromName("U1");
			$this->assertTrue($mu[0] == $uid, "Test TD5 failed in ".basename($file));

			// TD 6: Get the groups who can modify Group/G4
			//       => expected Group/G5
			$mg = $g4->getManageGroups();
			$this->assertTrue(count($mg) == 1, "Test TD6 failed in ".basename($file));
			$this->assertTrue($mg[0] == HACLGroup::idForGroup("Group/G5"), $mg, "Test TD5 failed in ".basename($file));
			
			// TD 7: Check group membership
			$this->checkGroupMembers("TD 7-G1", $g1, "group", array("Group/G1", false, "Group/G2", true, "Group/G3", true, "Group/G4", true, "Group/G5", true));
			$this->checkGroupMembers("TD 7-G2", $g2, "group", array("Group/G1", false, "Group/G2", false, "Group/G3", false, "Group/G4", true, "Group/G5", true));
			$this->checkGroupMembers("TD 7-G3", $g3, "group", array("Group/G1", false, "Group/G2", false, "Group/G3", false, "Group/G4", true, "Group/G5", false));
			$this->checkGroupMembers("TD 7-G4", $g4, "group", array("Group/G1", false, "Group/G2", false, "Group/G3", false, "Group/G4", false, "Group/G5", false));
			$this->checkGroupMembers("TD 7-G5", $g5, "group", array("Group/G1", false, "Group/G2", false, "Group/G3", false, "Group/G4", false, "Group/G5", false));

			// TD 8: Check user membership
			$this->checkGroupMembers("TD 8-G1", $g1, "user", array("U1", false, "U2", true, "U3", true, "U4", true, "U5", true, "U6", true));
			$this->checkGroupMembers("TD 8-G2", $g2, "user", array("U1", false, "U2", true, "U3", true, "U4", true, "U5", true, "U6", false));
			$this->checkGroupMembers("TD 8-G3", $g3, "user", array("U1", false, "U2", false, "U3", false, "U4", true, "U5", true, "U6", true));
			$this->checkGroupMembers("TD 8-G4", $g4, "user", array("U1", false, "U2", false, "U3", false, "U4", true, "U5", true, "U6", false));
			$this->checkGroupMembers("TD 8-G5", $g5, "user", array("U1", false, "U2", true, "U3", true, "U4", true, "U5", false, "U6", false));
			
			// TD9: Add unknown user to a group
			$exceptionCaught = false;
			try {
				$g1->addUser("U7");
			} catch (HACLException $e) {
				if ($e->getCode() == HACLException::UNKOWN_USER) {
					$exceptionCaught = true;
				}
			}
			$this->assertTrue($exceptionCaught, "Test TD9 failed in ".basename($file));
			
		} catch (Exception $e) {
			$this->assertTrue(false, "Unexpected exception while testing ".basename($file)."::setupGroups():".$e->getMessage());
		}
    	
    }

    function setupRights() {
    	$file = __FILE__;
    	global $wgUser;
    	$wgUser = User::newFromName("U1");
    	
    	try {
    	
			//-- Set up rights --
			$sdA = new HACLSecurityDescriptor(null, "Page/A", "A",
									          HACLSecurityDescriptor::PET_PAGE, 
			                                  array("Group/G1"), array("U1"));
			$sdA->save();

			$sdCatB = new HACLSecurityDescriptor(null, "Category/B", "Category:B",
									             HACLSecurityDescriptor::PET_CATEGORY, 
			                                     null, array("U1", "U1"));
			$sdCatB->save();
			
			$prPR1 = new HACLSecurityDescriptor(null, "Right/PR1", null,
									            HACLSecurityDescriptor::PET_RIGHT, 
			                                    array("Group/G4", "Group/G5"), 
			                                    array("U1"));
			$prPR1->save();

			$ir = new HACLRight(HACLRight::EDIT,
			                    array("Group/G4"), null, 
			                    "IR for PR1");
			$prPR1->addInlineRights(array($ir));
						
			$prPR2 = new HACLSecurityDescriptor(null, "Right/PR2", null,
									            HACLSecurityDescriptor::PET_RIGHT, 
			                                    null, array("U1", "U2"));
			$prPR2->save();
			                                    
			$ir = new HACLRight(HACLRight::DELETE,
			                    null, array("U2"), 
			                    "IR for PR2");
			$prPR2->addInlineRights(array($ir));
			                                    
			$prPR3 = new HACLSecurityDescriptor(null, "Right/PR3", null,
									            HACLSecurityDescriptor::PET_RIGHT, 
			                                    null, array("U1"));
			$prPR3->save();
			                                    
			$sdA->addPredefinedRights(array($prPR1, $prPR2));
			
			$ir = new HACLRight(HACLRight::READ,
			                    array("Group/G1"), array("U1"), 
			                    "IR for page A");
			$sdA->addInlineRights(array($ir));
			
			$sdCatB->addPredefinedRights(array($prPR3));
			
			$prPR1->addPredefinedRights(array($prPR2));
			
			$prPR3->addPredefinedRights(array($prPR1, $prPR2));
						
		} catch (Exception $e) {
			$this->assertTrue(false, "Unexpected exception while testing ".basename($file)."::setupRights():".$e->getMessage());
		}
    	
    }
    
    function checkRights() {
    	$file = __FILE__;
    	try {
			$checkRights = array(
				array('A', 'U1', 'read', true),
				array('A', 'U1', 'formedit', false),
				array('A', 'U1', 'annotate', false),
				array('A', 'U1', 'wysiwyg', false),
				array('A', 'U1', 'edit', false),
				array('A', 'U1', 'create', false),
				array('A', 'U1', 'delete', false),
				array('A', 'U1', 'move', false),
				
				array('A', 'U2', 'read', true),
				array('A', 'U2', 'formedit', true),
				array('A', 'U2', 'annotate', true),
				array('A', 'U2', 'wysiwyg', true),
				array('A', 'U2', 'edit', true),
				array('A', 'U2', 'create', false),
				array('A', 'U2', 'delete', true),
				array('A', 'U2', 'move', false),
				
				array('A', 'U3', 'read', true),
				array('A', 'U3', 'formedit', false),
				array('A', 'U3', 'annotate', false),
				array('A', 'U3', 'wysiwyg', false),
				array('A', 'U3', 'edit', false),
				array('A', 'U3', 'create', false),
				array('A', 'U3', 'delete', false),
				array('A', 'U3', 'move', false),
				
				array('A', 'U4', 'read', true),
				array('A', 'U4', 'formedit', true),
				array('A', 'U4', 'annotate', true),
				array('A', 'U4', 'wysiwyg', true),
				array('A', 'U4', 'edit', true),
				array('A', 'U4', 'create', false),
				array('A', 'U4', 'delete', false),
				array('A', 'U4', 'move', false),
				
				array('A', 'U5', 'read', true),
				array('A', 'U5', 'formedit', true),
				array('A', 'U5', 'annotate', true),
				array('A', 'U5', 'wysiwyg', true),
				array('A', 'U5', 'edit', true),
				array('A', 'U5', 'create', false),
				array('A', 'U5', 'delete', false),
				array('A', 'U5', 'move', false),
				
				array('A', 'U6', 'read', true),
				array('A', 'U6', 'formedit', false),
				array('A', 'U6', 'annotate', false),
				array('A', 'U6', 'wysiwyg', false),
				array('A', 'U6', 'edit', false),
				array('A', 'U6', 'create', false),
				array('A', 'U6', 'delete', false),
				array('A', 'U6', 'move', false),
				
				array('B', 'U1', 'read', false),
				array('B', 'U1', 'formedit', false),
				array('B', 'U1', 'annotate', false),
				array('B', 'U1', 'wysiwyg', false),
				array('B', 'U1', 'edit', false),
				array('B', 'U1', 'create', false),
				array('B', 'U1', 'delete', false),
				array('B', 'U1', 'move', false),
				
				array('B', 'U2', 'read', true),
				array('B', 'U2', 'formedit', true),
				array('B', 'U2', 'annotate', true),
				array('B', 'U2', 'wysiwyg', true),
				array('B', 'U2', 'edit', true),
				array('B', 'U2', 'create', false),
				array('B', 'U2', 'delete', true),
				array('B', 'U2', 'move', false),
				
				array('B', 'U3', 'read', false),
				array('B', 'U3', 'formedit', false),
				array('B', 'U3', 'annotate', false),
				array('B', 'U3', 'wysiwyg', false),
				array('B', 'U3', 'edit', false),
				array('B', 'U3', 'create', false),
				array('B', 'U3', 'delete', false),
				array('B', 'U3', 'move', false),
				
				array('B', 'U4', 'read', true),
				array('B', 'U4', 'formedit', true),
				array('B', 'U4', 'annotate', true),
				array('B', 'U4', 'wysiwyg', true),
				array('B', 'U4', 'edit', true),
				array('B', 'U4', 'create', false),
				array('B', 'U4', 'delete', false),
				array('B', 'U4', 'move', false),
				
				array('B', 'U5', 'read', true),
				array('B', 'U5', 'formedit', true),
				array('B', 'U5', 'annotate', true),
				array('B', 'U5', 'wysiwyg', true),
				array('B', 'U5', 'edit', true),
				array('B', 'U5', 'create', false),
				array('B', 'U5', 'delete', false),
				array('B', 'U5', 'move', false),
				
				array('B', 'U6', 'read', false),
				array('B', 'U6', 'formedit', false),
				array('B', 'U6', 'annotate', false),
				array('B', 'U6', 'wysiwyg', false),
				array('B', 'U6', 'edit', false),
				array('B', 'U6', 'create', false),
				array('B', 'U6', 'delete', false),
				array('B', 'U6', 'move', false),
			);
			$this->doCheckRights("TD_CR_1", $checkRights);
		} catch (Exception $e) {
			$this->assertTrue(false, "Unexpected exception while testing ".basename($file)."::checkRights():".$e->getMessage());
		}
			
    }
    
    function removeRights() {
    	$file = __FILE__;
    	global $haclgOpenWikiAccess;
    	
    	try {
    		$prPR3 = HACLSecurityDescriptor::newFromName("Right/PR3");
			$prPR3->delete();

			$checkRights = array(
				array('A', 'U1', 'read', true),
				array('A', 'U1', 'formedit', false),
				array('A', 'U1', 'annotate', false),
				array('A', 'U1', 'wysiwyg', false),
				array('A', 'U1', 'edit', false),
				array('A', 'U1', 'create', false),
				array('A', 'U1', 'delete', false),
				array('A', 'U1', 'move', false),
				
				array('A', 'U2', 'read', true),
				array('A', 'U2', 'formedit', true),
				array('A', 'U2', 'annotate', true),
				array('A', 'U2', 'wysiwyg', true),
				array('A', 'U2', 'edit', true),
				array('A', 'U2', 'create', false),
				array('A', 'U2', 'delete', true),
				array('A', 'U2', 'move', false),
				
				array('A', 'U3', 'read', true),
				array('A', 'U3', 'formedit', false),
				array('A', 'U3', 'annotate', false),
				array('A', 'U3', 'wysiwyg', false),
				array('A', 'U3', 'edit', false),
				array('A', 'U3', 'create', false),
				array('A', 'U3', 'delete', false),
				array('A', 'U3', 'move', false),
				
				array('A', 'U4', 'read', true),
				array('A', 'U4', 'formedit', true),
				array('A', 'U4', 'annotate', true),
				array('A', 'U4', 'wysiwyg', true),
				array('A', 'U4', 'edit', true),
				array('A', 'U4', 'create', false),
				array('A', 'U4', 'delete', false),
				array('A', 'U4', 'move', false),
				
				array('A', 'U5', 'read', true),
				array('A', 'U5', 'formedit', true),
				array('A', 'U5', 'annotate', true),
				array('A', 'U5', 'wysiwyg', true),
				array('A', 'U5', 'edit', true),
				array('A', 'U5', 'create', false),
				array('A', 'U5', 'delete', false),
				array('A', 'U5', 'move', false),
				
				array('A', 'U6', 'read', true),
				array('A', 'U6', 'formedit', false),
				array('A', 'U6', 'annotate', false),
				array('A', 'U6', 'wysiwyg', false),
				array('A', 'U6', 'edit', false),
				array('A', 'U6', 'create', false),
				array('A', 'U6', 'delete', false),
				array('A', 'U6', 'move', false),
				
				array('B', 'U1', 'read', false),
				array('B', 'U1', 'formedit', false),
				array('B', 'U1', 'annotate', false),
				array('B', 'U1', 'wysiwyg', false),
				array('B', 'U1', 'edit', false),
				array('B', 'U1', 'create', false),
				array('B', 'U1', 'delete', false),
				array('B', 'U1', 'move', false),
				
				array('B', 'U2', 'read', false),
				array('B', 'U2', 'formedit', false),
				array('B', 'U2', 'annotate', false),
				array('B', 'U2', 'wysiwyg', false),
				array('B', 'U2', 'edit', false),
				array('B', 'U2', 'create', false),
				array('B', 'U2', 'delete', false),
				array('B', 'U2', 'move', false),
				
				array('B', 'U3', 'read', false),
				array('B', 'U3', 'formedit', false),
				array('B', 'U3', 'annotate', false),
				array('B', 'U3', 'wysiwyg', false),
				array('B', 'U3', 'edit', false),
				array('B', 'U3', 'create', false),
				array('B', 'U3', 'delete', false),
				array('B', 'U3', 'move', false),
				
				array('B', 'U4', 'read', false),
				array('B', 'U4', 'formedit', false),
				array('B', 'U4', 'annotate', false),
				array('B', 'U4', 'wysiwyg', false),
				array('B', 'U4', 'edit', false),
				array('B', 'U4', 'create', false),
				array('B', 'U4', 'delete', false),
				array('B', 'U4', 'move', false),
				
				array('B', 'U5', 'read', false),
				array('B', 'U5', 'formedit', false),
				array('B', 'U5', 'annotate', false),
				array('B', 'U5', 'wysiwyg', false),
				array('B', 'U5', 'edit', false),
				array('B', 'U5', 'create', false),
				array('B', 'U5', 'delete', false),
				array('B', 'U5', 'move', false),
				
				array('B', 'U6', 'read', false),
				array('B', 'U6', 'formedit', false),
				array('B', 'U6', 'annotate', false),
				array('B', 'U6', 'wysiwyg', false),
				array('B', 'U6', 'edit', false),
				array('B', 'U6', 'create', false),
				array('B', 'U6', 'delete', false),
				array('B', 'U6', 'move', false),
			);
			$this->doCheckRights("TD_CR_2", $checkRights);			
			
    		$sdCatB = HACLSecurityDescriptor::newFromName("Category/B");
			$sdCatB->delete();
			
    		$prPR1 = HACLSecurityDescriptor::newFromName("Right/PR1");
			$prPR1->delete();
						
			$checkRights = array(
				array('A', 'U1', 'read', true),
				array('A', 'U1', 'formedit', false),
				array('A', 'U1', 'annotate', false),
				array('A', 'U1', 'wysiwyg', false),
				array('A', 'U1', 'edit', false),
				array('A', 'U1', 'create', false),
				array('A', 'U1', 'delete', false),
				array('A', 'U1', 'move', false),
				
				array('A', 'U2', 'read', true),
				array('A', 'U2', 'formedit', true),
				array('A', 'U2', 'annotate', true),
				array('A', 'U2', 'wysiwyg', true),
				array('A', 'U2', 'edit', true),
				array('A', 'U2', 'create', false),
				array('A', 'U2', 'delete', true),
				array('A', 'U2', 'move', false),
				
				array('A', 'U3', 'read', true),
				array('A', 'U3', 'formedit', false),
				array('A', 'U3', 'annotate', false),
				array('A', 'U3', 'wysiwyg', false),
				array('A', 'U3', 'edit', false),
				array('A', 'U3', 'create', false),
				array('A', 'U3', 'delete', false),
				array('A', 'U3', 'move', false),
				
				array('A', 'U4', 'read', true),
				array('A', 'U4', 'formedit', false),
				array('A', 'U4', 'annotate', false),
				array('A', 'U4', 'wysiwyg', false),
				array('A', 'U4', 'edit', false),
				array('A', 'U4', 'create', false),
				array('A', 'U4', 'delete', false),
				array('A', 'U4', 'move', false),
				
				array('A', 'U5', 'read', true),
				array('A', 'U5', 'formedit', false),
				array('A', 'U5', 'annotate', false),
				array('A', 'U5', 'wysiwyg', false),
				array('A', 'U5', 'edit', false),
				array('A', 'U5', 'create', false),
				array('A', 'U5', 'delete', false),
				array('A', 'U5', 'move', false),
				
				array('A', 'U6', 'read', true),
				array('A', 'U6', 'formedit', false),
				array('A', 'U6', 'annotate', false),
				array('A', 'U6', 'wysiwyg', false),
				array('A', 'U6', 'edit', false),
				array('A', 'U6', 'create', false),
				array('A', 'U6', 'delete', false),
				array('A', 'U6', 'move', false),
				
				array('B', 'U1', 'read', $haclgOpenWikiAccess),
				array('B', 'U1', 'formedit', $haclgOpenWikiAccess),
				array('B', 'U1', 'annotate', $haclgOpenWikiAccess),
				array('B', 'U1', 'wysiwyg', $haclgOpenWikiAccess),
				array('B', 'U1', 'edit', $haclgOpenWikiAccess),
				array('B', 'U1', 'create', $haclgOpenWikiAccess),
				array('B', 'U1', 'delete', $haclgOpenWikiAccess),
				array('B', 'U1', 'move', $haclgOpenWikiAccess),
				
				array('B', 'U2', 'read', $haclgOpenWikiAccess),
				array('B', 'U2', 'formedit', $haclgOpenWikiAccess),
				array('B', 'U2', 'annotate', $haclgOpenWikiAccess),
				array('B', 'U2', 'wysiwyg', $haclgOpenWikiAccess),
				array('B', 'U2', 'edit', $haclgOpenWikiAccess),
				array('B', 'U2', 'create', $haclgOpenWikiAccess),
				array('B', 'U2', 'delete', $haclgOpenWikiAccess),
				array('B', 'U2', 'move', $haclgOpenWikiAccess),
				
				array('B', 'U3', 'read', $haclgOpenWikiAccess),
				array('B', 'U3', 'formedit', $haclgOpenWikiAccess),
				array('B', 'U3', 'annotate', $haclgOpenWikiAccess),
				array('B', 'U3', 'wysiwyg', $haclgOpenWikiAccess),
				array('B', 'U3', 'edit', $haclgOpenWikiAccess),
				array('B', 'U3', 'create', $haclgOpenWikiAccess),
				array('B', 'U3', 'delete', $haclgOpenWikiAccess),
				array('B', 'U3', 'move', $haclgOpenWikiAccess),
				
				array('B', 'U4', 'read', $haclgOpenWikiAccess),
				array('B', 'U4', 'formedit', $haclgOpenWikiAccess),
				array('B', 'U4', 'annotate', $haclgOpenWikiAccess),
				array('B', 'U4', 'wysiwyg', $haclgOpenWikiAccess),
				array('B', 'U4', 'edit', $haclgOpenWikiAccess),
				array('B', 'U4', 'create', $haclgOpenWikiAccess),
				array('B', 'U4', 'delete', $haclgOpenWikiAccess),
				array('B', 'U4', 'move', $haclgOpenWikiAccess),
				
				array('B', 'U5', 'read', $haclgOpenWikiAccess),
				array('B', 'U5', 'formedit', $haclgOpenWikiAccess),
				array('B', 'U5', 'annotate', $haclgOpenWikiAccess),
				array('B', 'U5', 'wysiwyg', $haclgOpenWikiAccess),
				array('B', 'U5', 'edit', $haclgOpenWikiAccess),
				array('B', 'U5', 'create', $haclgOpenWikiAccess),
				array('B', 'U5', 'delete', $haclgOpenWikiAccess),
				array('B', 'U5', 'move', $haclgOpenWikiAccess),
				
				array('B', 'U6', 'read', $haclgOpenWikiAccess),
				array('B', 'U6', 'formedit', $haclgOpenWikiAccess),
				array('B', 'U6', 'annotate', $haclgOpenWikiAccess),
				array('B', 'U6', 'wysiwyg', $haclgOpenWikiAccess),
				array('B', 'U6', 'edit', $haclgOpenWikiAccess),
				array('B', 'U6', 'create', $haclgOpenWikiAccess),
				array('B', 'U6', 'delete', $haclgOpenWikiAccess),
				array('B', 'U6', 'move', $haclgOpenWikiAccess),
			);
			$this->doCheckRights("TD_CR_3", $checkRights);			
			
    		$prPR2 = HACLSecurityDescriptor::newFromName("Right/PR2");
			$prPR2->delete();
			
			$checkRights = array(
				array('A', 'U1', 'read', true),
				array('A', 'U1', 'formedit', false),
				array('A', 'U1', 'annotate', false),
				array('A', 'U1', 'wysiwyg', false),
				array('A', 'U1', 'edit', false),
				array('A', 'U1', 'create', false),
				array('A', 'U1', 'delete', false),
				array('A', 'U1', 'move', false),
				
				array('A', 'U2', 'read', true),
				array('A', 'U2', 'formedit', false),
				array('A', 'U2', 'annotate', false),
				array('A', 'U2', 'wysiwyg', false),
				array('A', 'U2', 'edit', false),
				array('A', 'U2', 'create', false),
				array('A', 'U2', 'delete', false),
				array('A', 'U2', 'move', false),
				
				array('A', 'U3', 'read', true),
				array('A', 'U3', 'formedit', false),
				array('A', 'U3', 'annotate', false),
				array('A', 'U3', 'wysiwyg', false),
				array('A', 'U3', 'edit', false),
				array('A', 'U3', 'create', false),
				array('A', 'U3', 'delete', false),
				array('A', 'U3', 'move', false),
				
				array('A', 'U4', 'read', true),
				array('A', 'U4', 'formedit', false),
				array('A', 'U4', 'annotate', false),
				array('A', 'U4', 'wysiwyg', false),
				array('A', 'U4', 'edit', false),
				array('A', 'U4', 'create', false),
				array('A', 'U4', 'delete', false),
				array('A', 'U4', 'move', false),
				
				array('A', 'U5', 'read', true),
				array('A', 'U5', 'formedit', false),
				array('A', 'U5', 'annotate', false),
				array('A', 'U5', 'wysiwyg', false),
				array('A', 'U5', 'edit', false),
				array('A', 'U5', 'create', false),
				array('A', 'U5', 'delete', false),
				array('A', 'U5', 'move', false),
				
				array('A', 'U6', 'read', true),
				array('A', 'U6', 'formedit', false),
				array('A', 'U6', 'annotate', false),
				array('A', 'U6', 'wysiwyg', false),
				array('A', 'U6', 'edit', false),
				array('A', 'U6', 'create', false),
				array('A', 'U6', 'delete', false),
				array('A', 'U6', 'move', false),
				
				array('B', 'U1', 'read', $haclgOpenWikiAccess),
				array('B', 'U1', 'formedit', $haclgOpenWikiAccess),
				array('B', 'U1', 'annotate', $haclgOpenWikiAccess),
				array('B', 'U1', 'wysiwyg', $haclgOpenWikiAccess),
				array('B', 'U1', 'edit', $haclgOpenWikiAccess),
				array('B', 'U1', 'create', $haclgOpenWikiAccess),
				array('B', 'U1', 'delete', $haclgOpenWikiAccess),
				array('B', 'U1', 'move', $haclgOpenWikiAccess),
				
				array('B', 'U2', 'read', $haclgOpenWikiAccess),
				array('B', 'U2', 'formedit', $haclgOpenWikiAccess),
				array('B', 'U2', 'annotate', $haclgOpenWikiAccess),
				array('B', 'U2', 'wysiwyg', $haclgOpenWikiAccess),
				array('B', 'U2', 'edit', $haclgOpenWikiAccess),
				array('B', 'U2', 'create', $haclgOpenWikiAccess),
				array('B', 'U2', 'delete', $haclgOpenWikiAccess),
				array('B', 'U2', 'move', $haclgOpenWikiAccess),
				
				array('B', 'U3', 'read', $haclgOpenWikiAccess),
				array('B', 'U3', 'formedit', $haclgOpenWikiAccess),
				array('B', 'U3', 'annotate', $haclgOpenWikiAccess),
				array('B', 'U3', 'wysiwyg', $haclgOpenWikiAccess),
				array('B', 'U3', 'edit', $haclgOpenWikiAccess),
				array('B', 'U3', 'create', $haclgOpenWikiAccess),
				array('B', 'U3', 'delete', $haclgOpenWikiAccess),
				array('B', 'U3', 'move', $haclgOpenWikiAccess),
				
				array('B', 'U4', 'read', $haclgOpenWikiAccess),
				array('B', 'U4', 'formedit', $haclgOpenWikiAccess),
				array('B', 'U4', 'annotate', $haclgOpenWikiAccess),
				array('B', 'U4', 'wysiwyg', $haclgOpenWikiAccess),
				array('B', 'U4', 'edit', $haclgOpenWikiAccess),
				array('B', 'U4', 'create', $haclgOpenWikiAccess),
				array('B', 'U4', 'delete', $haclgOpenWikiAccess),
				array('B', 'U4', 'move', $haclgOpenWikiAccess),
				
				array('B', 'U5', 'read', $haclgOpenWikiAccess),
				array('B', 'U5', 'formedit', $haclgOpenWikiAccess),
				array('B', 'U5', 'annotate', $haclgOpenWikiAccess),
				array('B', 'U5', 'wysiwyg', $haclgOpenWikiAccess),
				array('B', 'U5', 'edit', $haclgOpenWikiAccess),
				array('B', 'U5', 'create', $haclgOpenWikiAccess),
				array('B', 'U5', 'delete', $haclgOpenWikiAccess),
				array('B', 'U5', 'move', $haclgOpenWikiAccess),
				
				array('B', 'U6', 'read', $haclgOpenWikiAccess),
				array('B', 'U6', 'formedit', $haclgOpenWikiAccess),
				array('B', 'U6', 'annotate', $haclgOpenWikiAccess),
				array('B', 'U6', 'wysiwyg', $haclgOpenWikiAccess),
				array('B', 'U6', 'edit', $haclgOpenWikiAccess),
				array('B', 'U6', 'create', $haclgOpenWikiAccess),
				array('B', 'U6', 'delete', $haclgOpenWikiAccess),
				array('B', 'U6', 'move', $haclgOpenWikiAccess),
			);
			$this->doCheckRights("TD_CR_4", $checkRights);
						
    		$sdA = HACLSecurityDescriptor::newFromName("Page/A");
			$sdA->delete();

			global $haclgOpenWikiAccess;
			$checkRights = array(
				array('A', 'U1', 'read', $haclgOpenWikiAccess),
				array('A', 'U1', 'formedit', $haclgOpenWikiAccess),
				array('A', 'U1', 'annotate', $haclgOpenWikiAccess),
				array('A', 'U1', 'wysiwyg', $haclgOpenWikiAccess),
				array('A', 'U1', 'edit', $haclgOpenWikiAccess),
				array('A', 'U1', 'create', $haclgOpenWikiAccess),
				array('A', 'U1', 'delete', $haclgOpenWikiAccess),
				array('A', 'U1', 'move', $haclgOpenWikiAccess),
				
				array('A', 'U2', 'read', $haclgOpenWikiAccess),
				array('A', 'U2', 'formedit', $haclgOpenWikiAccess),
				array('A', 'U2', 'annotate', $haclgOpenWikiAccess),
				array('A', 'U2', 'wysiwyg', $haclgOpenWikiAccess),
				array('A', 'U2', 'edit', $haclgOpenWikiAccess),
				array('A', 'U2', 'create', $haclgOpenWikiAccess),
				array('A', 'U2', 'delete', $haclgOpenWikiAccess),
				array('A', 'U2', 'move', $haclgOpenWikiAccess),
				
				array('A', 'U3', 'read', $haclgOpenWikiAccess),
				array('A', 'U3', 'formedit', $haclgOpenWikiAccess),
				array('A', 'U3', 'annotate', $haclgOpenWikiAccess),
				array('A', 'U3', 'wysiwyg', $haclgOpenWikiAccess),
				array('A', 'U3', 'edit', $haclgOpenWikiAccess),
				array('A', 'U3', 'create', $haclgOpenWikiAccess),
				array('A', 'U3', 'delete', $haclgOpenWikiAccess),
				array('A', 'U3', 'move', $haclgOpenWikiAccess),
				
				array('A', 'U4', 'read', $haclgOpenWikiAccess),
				array('A', 'U4', 'formedit', $haclgOpenWikiAccess),
				array('A', 'U4', 'annotate', $haclgOpenWikiAccess),
				array('A', 'U4', 'wysiwyg', $haclgOpenWikiAccess),
				array('A', 'U4', 'edit', $haclgOpenWikiAccess),
				array('A', 'U4', 'create', $haclgOpenWikiAccess),
				array('A', 'U4', 'delete', $haclgOpenWikiAccess),
				array('A', 'U4', 'move', $haclgOpenWikiAccess),
				
				array('A', 'U5', 'read', $haclgOpenWikiAccess),
				array('A', 'U5', 'formedit', $haclgOpenWikiAccess),
				array('A', 'U5', 'annotate', $haclgOpenWikiAccess),
				array('A', 'U5', 'wysiwyg', $haclgOpenWikiAccess),
				array('A', 'U5', 'edit', $haclgOpenWikiAccess),
				array('A', 'U5', 'create', $haclgOpenWikiAccess),
				array('A', 'U5', 'delete', $haclgOpenWikiAccess),
				array('A', 'U5', 'move', $haclgOpenWikiAccess),
				
				array('A', 'U6', 'read', $haclgOpenWikiAccess),
				array('A', 'U6', 'formedit', $haclgOpenWikiAccess),
				array('A', 'U6', 'annotate', $haclgOpenWikiAccess),
				array('A', 'U6', 'wysiwyg', $haclgOpenWikiAccess),
				array('A', 'U6', 'edit', $haclgOpenWikiAccess),
				array('A', 'U6', 'create', $haclgOpenWikiAccess),
				array('A', 'U6', 'delete', $haclgOpenWikiAccess),
				array('A', 'U6', 'move', $haclgOpenWikiAccess),
				
				array('B', 'U1', 'read', $haclgOpenWikiAccess),
				array('B', 'U1', 'formedit', $haclgOpenWikiAccess),
				array('B', 'U1', 'annotate', $haclgOpenWikiAccess),
				array('B', 'U1', 'wysiwyg', $haclgOpenWikiAccess),
				array('B', 'U1', 'edit', $haclgOpenWikiAccess),
				array('B', 'U1', 'create', $haclgOpenWikiAccess),
				array('B', 'U1', 'delete', $haclgOpenWikiAccess),
				array('B', 'U1', 'move', $haclgOpenWikiAccess),
				
				array('B', 'U2', 'read', $haclgOpenWikiAccess),
				array('B', 'U2', 'formedit', $haclgOpenWikiAccess),
				array('B', 'U2', 'annotate', $haclgOpenWikiAccess),
				array('B', 'U2', 'wysiwyg', $haclgOpenWikiAccess),
				array('B', 'U2', 'edit', $haclgOpenWikiAccess),
				array('B', 'U2', 'create', $haclgOpenWikiAccess),
				array('B', 'U2', 'delete', $haclgOpenWikiAccess),
				array('B', 'U2', 'move', $haclgOpenWikiAccess),
				
				array('B', 'U3', 'read', $haclgOpenWikiAccess),
				array('B', 'U3', 'formedit', $haclgOpenWikiAccess),
				array('B', 'U3', 'annotate', $haclgOpenWikiAccess),
				array('B', 'U3', 'wysiwyg', $haclgOpenWikiAccess),
				array('B', 'U3', 'edit', $haclgOpenWikiAccess),
				array('B', 'U3', 'create', $haclgOpenWikiAccess),
				array('B', 'U3', 'delete', $haclgOpenWikiAccess),
				array('B', 'U3', 'move', $haclgOpenWikiAccess),
				
				array('B', 'U4', 'read', $haclgOpenWikiAccess),
				array('B', 'U4', 'formedit', $haclgOpenWikiAccess),
				array('B', 'U4', 'annotate', $haclgOpenWikiAccess),
				array('B', 'U4', 'wysiwyg', $haclgOpenWikiAccess),
				array('B', 'U4', 'edit', $haclgOpenWikiAccess),
				array('B', 'U4', 'create', $haclgOpenWikiAccess),
				array('B', 'U4', 'delete', $haclgOpenWikiAccess),
				array('B', 'U4', 'move', $haclgOpenWikiAccess),
				
				array('B', 'U5', 'read', $haclgOpenWikiAccess),
				array('B', 'U5', 'formedit', $haclgOpenWikiAccess),
				array('B', 'U5', 'annotate', $haclgOpenWikiAccess),
				array('B', 'U5', 'wysiwyg', $haclgOpenWikiAccess),
				array('B', 'U5', 'edit', $haclgOpenWikiAccess),
				array('B', 'U5', 'create', $haclgOpenWikiAccess),
				array('B', 'U5', 'delete', $haclgOpenWikiAccess),
				array('B', 'U5', 'move', $haclgOpenWikiAccess),
				
				array('B', 'U6', 'read', $haclgOpenWikiAccess),
				array('B', 'U6', 'formedit', $haclgOpenWikiAccess),
				array('B', 'U6', 'annotate', $haclgOpenWikiAccess),
				array('B', 'U6', 'wysiwyg', $haclgOpenWikiAccess),
				array('B', 'U6', 'edit', $haclgOpenWikiAccess),
				array('B', 'U6', 'create', $haclgOpenWikiAccess),
				array('B', 'U6', 'delete', $haclgOpenWikiAccess),
				array('B', 'U6', 'move', $haclgOpenWikiAccess),
			);
			$this->doCheckRights("TD_CR_5", $checkRights);
			
		} catch (Exception $e) {
			$this->assertTrue(false, "Unexpected exception while testing ".basename($file)."::removeRights():".$e->getMessage());
		}
    	
    }
    
    function removeGroups() {
    	$file = __FILE__;
    	global $wgUser;
    	$wgUser = User::newFromName("U1");
    	
    	try {
			$g1 = HACLGroup::newFromName("Group/G1");
			$g2 = HACLGroup::newFromName("Group/G2");
			$g3 = HACLGroup::newFromName("Group/G3");
			$g4 = HACLGroup::newFromName("Group/G4");
			$g5 = HACLGroup::newFromName("Group/G5");
   			
			$g5->removeUser("U4");
			$this->checkGroupMembers("TD_RG1-G1", $g1, "user", array("U1", false, "U2", true, "U3", true, "U4", true, "U5", true, "U6", true));
			$this->checkGroupMembers("TD_RG1-G2", $g2, "user", array("U1", false, "U2", true, "U3", true, "U4", true, "U5", true, "U6", false));
			$this->checkGroupMembers("TD_RG1-G3", $g3, "user", array("U1", false, "U2", false, "U3", false, "U4", true, "U5", true, "U6", true));
			$this->checkGroupMembers("TD_RG1-G4", $g4, "user", array("U1", false, "U2", false, "U3", false, "U4", true, "U5", true, "U6", false));
			$this->checkGroupMembers("TD_RG1-G5", $g5, "user", array("U1", false, "U2", true, "U3", true, "U4", false, "U5", false, "U6", false));
			
			$g3->delete();
			$this->checkGroupMembers("TD_RG2-G1", $g1, "user", array("U1", false, "U2", true, "U3", true, "U4", true, "U5", true, "U6", false));
			$this->checkGroupMembers("TD_RG2-G2", $g2, "user", array("U1", false, "U2", true, "U3", true, "U4", true, "U5", true, "U6", false));
			$this->checkGroupMembers("TD_RG2-G4", $g4, "user", array("U1", false, "U2", false, "U3", false, "U4", true, "U5", true, "U6", false));
			$this->checkGroupMembers("TD_RG2-G5", $g5, "user", array("U1", false, "U2", true, "U3", true, "U4", false, "U5", false, "U6", false));
			
			$g2->delete();
			$this->checkGroupMembers("TD_RG3-G1", $g1, "user", array("U1", false, "U2", false, "U3", false, "U4", false, "U5", false, "U6", false));
			$this->checkGroupMembers("TD_RG3-G4", $g4, "user", array("U1", false, "U2", false, "U3", false, "U4", true, "U5", true, "U6", false));
			$this->checkGroupMembers("TD_RG3-G5", $g5, "user", array("U1", false, "U2", true, "U3", true, "U4", false, "U5", false, "U6", false));
			
			$g5->delete();
			$this->checkGroupMembers("TD_RG4-G1", $g1, "user", array("U1", false, "U2", false, "U3", false, "U4", false, "U5", false, "U6", false));
			$this->checkGroupMembers("TD_RG4-G4", $g4, "user", array("U1", false, "U2", false, "U3", false, "U4", true, "U5", true, "U6", false));
			
			$g4->delete();
			$this->checkGroupMembers("TD_RG5-G1", $g1, "user", array("U1", false, "U2", false, "U3", false, "U4", false, "U5", false, "U6", false));
			
			$g1->delete();
			
		} catch (Exception $e) {
			$this->assertTrue(false, "Unexpected exception while testing ".basename($file)."::removeGroups():".$e->getMessage());
		}
	}
	
	function whitelist() {
		$wl = new HACLWhitelist(array('Main Page'));
		$wl->save();

		$inWL = HACLWhitelist::isInWhitelist('Main Page');
		$this->assertEquals($inWL, true, "Testing the whitelist failed - TC 1\n");
		$inWL = HACLWhitelist::isInWhitelist('Category:B');
		$this->assertEquals($inWL, false, "Testing the whitelist failed - TC 2\n");
		
		$wl = HACLWhitelist::newFromDB();
		$pages = $wl->getPages();

		$exceptionCaught = false;
		try {
			$wl = new HACLWhitelist(array('ACL:Group/G2', 'ACL:Group/G3', 'ACL:Group/G7'));
			$wl->save();
		} catch (HACLWhitelistException $e) {
			if ($e->getCode() == HACLWhitelistException::PAGE_DOES_NOT_EXIST) {
				$exceptionCaught = true;
			}
		}
		$this->assertTrue($exceptionCaught, "Testing the whitelist failed - TC 3\n");
		
		// Clean up the whitelist
		$wl = new HACLWhitelist();
		$wl->save();
		$inWL = HACLWhitelist::isInWhitelist('A');
		$this->assertEquals($inWL, false, "Testing the whitelist failed - TC 4\n");
		$inWL = HACLWhitelist::isInWhitelist('Category:B');
		$this->assertEquals($inWL, false, "Testing the whitelist failed - TC 5\n");
		
	}
    
    private function createArticles() {
    	global $wgUser;
    	$wgUser = User::newFromName("U1");
		$this->initArticleContent();
    	
    	$file = __FILE__;
    	try {
	    	foreach ($this->mOrderOfArticleCreation as $title) {
				self::createArticle($title, $this->mArticles[$title]);
	    	}
    	} catch (Exception $e) {
			$this->assertTrue(false, "Unexpected exception while testing ".basename($file)."::createArticles():".$e->getMessage());
		}
    	
    }

	private function createArticle($title, $content) {
	
		$title = Title::newFromText($title);
		$article = new Article($title);
	
		// Set the article's content
		$success = $article->doEdit($content, 'Created for test case');
	}

	private function initArticleContent() {
		$this->mOrderOfArticleCreation = array(
//			'Category:B'
		);
		
		$this->mArticles = array(
//------------------------------------------------------------------------------		
			'Category:B' =>
<<<ACL
This is the category B.
ACL
);
	}
	
	
	private function doCheckRights($testcase, $expectedResults) {
		foreach ($expectedResults as $er) {
			$articleName = $er[0];
			$user = $username = $er[1];
			$action = $er[2];
			$res = $er[3];
			$etc = haclfDisableTitlePatch();			
			$article = Title::newFromText($articleName);
			haclfRestoreTitlePatch($etc);
			$user = User::newFromName($user);
			unset($result);
			HACLEvaluator::userCan($article, $user, $action, $result);
			
			$this->assertEquals($res, $result, "Test of rights failed for: $article, $username, $action (Testcase: $testcase)\n");
			
		}
	}
	
	private function checkGroupMembers($testcase, $group, $mode, $membersAndResults) {
		for ($i = 0; $i < count($membersAndResults); $i+=2) {
			$name = $membersAndResults[$i];
			$result    = $membersAndResults[$i+1];
			if ($mode == "user")
				$this->assertEquals($result, $group->hasUserMember($name, true),
									"Check for group membership failed. ".
									"Expected ".($result?"true":"false")." for ".
				                    $group->getGroupName()."->hasUserMember($name) (Testcase: $testcase)");
			else if ($mode == "group")
				$this->assertEquals($result, $group->hasGroupMember($name, true),
									"Check for group membership failed. ".
									"Expected ".($result?"true":"false")." for ".
				                    $group->getGroupName()."->hasGroupMember($name) (Testcase: $testcase)");
		}
	}
	
	private function removeArticles() {
		
		$articles = array(
//			'Category:B'
		);
		
		foreach ($articles as $a) {
		    $t = Title::newFromText($a);
	    	$article = new Article($t);
			$article->doDelete("Testing");
		}
		
	}
	
	
}
?>