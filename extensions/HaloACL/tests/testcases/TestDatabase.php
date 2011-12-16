<?php
/*
 * Copyright (C) Vulcan Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program.If not, see <http://www.gnu.org/licenses/>.
 *
 */
if ( isset( $_SERVER ) && array_key_exists( 'REQUEST_METHOD', $_SERVER ) ) {
	die( "This script must be run from the command line\n" );
}

/**
 * @file
 * @ingroup HaloACL_Tests
 */

class TestDatabaseSuite extends PHPUnit_Framework_TestSuite
{
	
	private $mOrderOfArticleCreation;
	
	public static function suite() {
		
		$suite = new TestDatabaseSuite();
		$suite->addTestSuite('TestDatabase');
		$suite->addTestSuite('TestDatabaseGroups');
		return $suite;
	}
	
	protected function setUp() {
    	HACLStorage::reset(HACL_STORE_SQL);
		HACLStorage::getDatabase()->dropDatabaseTables(false);
		HACLStorage::getDatabase()->initDatabaseTables(false);
		
    	User::createNew("U1");
    	User::createNew("U2");
        User::createNew("U3");
        User::createNew("U4");
        User::createNew("U5");
        User::createNew("U6");
        
        $this->initArticleContent();
        $this->createArticles();
		
	}
	
	protected function tearDown() {
		$this->removeArticles();
		HACLStorage::getDatabase()->dropDatabaseTables(false);
		HACLStorage::getDatabase()->initDatabaseTables(false);
		
	}

	private function createArticles() {
    	global $wgUser;
    	$wgUser = User::newFromName("U1");
    	
    	$file = __FILE__;
    	try {
	    	foreach ($this->mOrderOfArticleCreation as $title) {
	    		$pf = HACLParserFunctions::getInstance();
	    		$pf->reset();
				self::createArticle($title, $this->mArticles[$title]);
	    	}
    	} catch (Exception $e) {
			echo "Unexpected exception while testing ".basename($file)."::createArticles():".$e->getMessage();
			throw $e;
		}
    	
    }
	
    private function createArticle($title, $content) {
	
    	if (!isset($content)) {
    		return;
    	}
		$title = Title::newFromText($title);
		$article = new Article($title);
		// Set the article's content
		$success = $article->doEdit($content, 'Created for test case', 
		                            $article->exists() ? EDIT_UPDATE : EDIT_NEW);
		if (!$success) {
			echo "Creating article ".$title->getFullText()." failed\n";
		}
	}
    
	private function removeArticles() {
		global $wgUser;
		$wgUser = User::newFromName("WikiSysop");
		
	    foreach ($this->mOrderOfArticleCreation as $a) {
	    	$t = Title::newFromText($a);
	    	$article = new Article($t);
			$article->doDeleteArticle("Testing");
		}
		
	}
    
	
	private function initArticleContent() {
		$this->mOrderOfArticleCreation = array(
			'ACL:Category/B',
			'ACL:Group/G1',
			'ACL:Group/G2',
			"ACL:Group/G'3",
			'ACL:Group/G4',
			'ACL:Group/G5',
			'ACL:Page/A',
			'ACL:Right/PR1',
			'ACL:Right/PR2',
			'ACL:Right/PR3',
			'A',
			'B',
			'C',
			'Whitelist',
			'User:U1',
			'Category:B',
			'Category:C',
			'Category:D',
			'Category:ACL/Group',
			'Category:ACL/Right',
			'Category:ACL/ACL',
			'Permission denied'
		
		);
		
		$this->mArticles = array(
//------------------------------------------------------------------------------		
			'ACL:Category/B' =>
<<<ACL
PR: PR3
ACL
,
//------------------------------------------------------------------------------		
			'ACL:Group/G1' =>
<<<ACL
Manage: U1

Groups:Group/G2, Group/G'3

Users:
ACL
,
//------------------------------------------------------------------------------		
			'ACL:Group/G2' =>
<<<ACL
Manage: U1
Groups:Group/G4, Group/G5
Users:
ACL
,
//------------------------------------------------------------------------------		
			"ACL:Group/G'3" =>
<<<ACL
Manage: U1

Groups:Group/G4

Users:U6
ACL
,
//------------------------------------------------------------------------------		
			'ACL:Group/G4' =>
<<<ACL
Manage: U1, U4, U5

Group:

Users:U4, U5
ACL
,
//------------------------------------------------------------------------------		
			'ACL:Group/G5' =>
<<<ACL
Manage: U1,G5

Groups:

Users:U2,U3,U4
ACL
,
//------------------------------------------------------------------------------		
			'ACL:Page/A' =>
<<<ACL
Manage: G1

IR:

R1(r, G1, U1)

PR: PR1, PR2
ACL
,
//------------------------------------------------------------------------------		
			'ACL:Right/PR1' =>
<<<ACL
Manage: G4, G5

IR:

R1(r|ef|e|a, G4)

PR: PR2
ACL
,
//------------------------------------------------------------------------------		
			'ACL:Right/PR2' =>
<<<ACL
Manage: U1, U2

IR:

R3(r|ef|e|d, U2)
ACL
,
//------------------------------------------------------------------------------		
			'ACL:Right/PR3' =>
<<<ACL
Manage: U1

PR: PR1, PR2
ACL
,
//------------------------------------------------------------------------------		
			'A' =>
<<<ACL
This page is protected by [[ACL:Page/A]].
ACL
,
//------------------------------------------------------------------------------		
			'B' =>
<<<ACL
This page is protected by [[ACL:Category/B]]

[[Category:B]]
ACL
,
//------------------------------------------------------------------------------		
			'C' =>
<<<ACL
This is page C.

[[Category:C]]
ACL
,
//------------------------------------------------------------------------------		
			'Whitelist' =>
<<<ACL
This is the article Whitelist.
ACL
,
//------------------------------------------------------------------------------		
			'User:U1' =>
<<<ACL
This is the article User:U1
ACL
,
//------------------------------------------------------------------------------		
			'Category:D' =>
<<<ACL
This is category D.
      [[Category:C]]
ACL
,
//------------------------------------------------------------------------------		
			'Category:B' =>
<<<ACL
This is category B.
ACL
,
//------------------------------------------------------------------------------		
			'Category:C' =>
<<<ACL
This is category C.
      [[Category:B]]
      [[Category:D]]
ACL
,
//------------------------------------------------------------------------------		
			'Category:ACL/Group' =>
<<<ACL
This is the category for groups.
ACL
,
//------------------------------------------------------------------------------		
			'Category:ACL/Right' =>
<<<ACL
This is the category for rights.
ACL
,
//------------------------------------------------------------------------------		
			'Category:ACL/ACL' =>
<<<ACL
This is the category for security descriptors.
ACL
,
//------------------------------------------------------------------------------		
			'Permission denied' =>
<<<ACL
You are not allowed to perform the requested action on this page.

Return to [[Main Page]].      
ACL

		);
	}
	
}


class TestDatabase extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = FALSE;
	
    function setUp() {
		HACLStorage::reset(HACL_STORE_SQL);
    }

    function tearDown() {

    }

    function testACLDatabaseTest() {
    	$this->setupGroups();
    	$this->groupsOfMember();
    	$this->setupRights();
    	$this->checkRights();
    	$this->removeRights();
    	$this->removeGroups();
    	$this->whitelist();
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
			$g3 = new HACLGroup(null, "Group/G'3", null, array("U1"));
			$g3->save();
			$g5 = new HACLGroup(null, "Group/G5", null, array("U1","U4","U5"));
			$g5->save();
			$g4 = new HACLGroup(null, "Group/G4", array("Group/G5"), array("U1"));
			$g4->save();
			
			$g1->addGroup("Group/G2");
			$g1->addGroup("Group/G'3");
			
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
			$g3 = HACLGroup::newFromName("Group/G'3");
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
			$this->assertContains("Group/G'3", $g1g, "Test TD3 failed in ".basename($file));
			
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
			$this->checkGroupMembers("TD 7-G1", $g1, "group", array("Group/G1", false, "Group/G2", true, "Group/G'3", true, "Group/G4", true, "Group/G5", true));
			$this->checkGroupMembers("TD 7-G2", $g2, "group", array("Group/G1", false, "Group/G2", false, "Group/G'3", false, "Group/G4", true, "Group/G5", true));
			$this->checkGroupMembers("TD 7-G3", $g3, "group", array("Group/G1", false, "Group/G2", false, "Group/G'3", false, "Group/G4", true, "Group/G5", false));
			$this->checkGroupMembers("TD 7-G4", $g4, "group", array("Group/G1", false, "Group/G2", false, "Group/G'3", false, "Group/G4", false, "Group/G5", false));
			$this->checkGroupMembers("TD 7-G5", $g5, "group", array("Group/G1", false, "Group/G2", false, "Group/G'3", false, "Group/G4", false, "Group/G5", false));

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
    
    
    function groupsOfMember() {
    	$this->checkGroupsOfMember("U1", false, array());
    	$this->checkGroupsOfMember("U1", true, array());

    	$this->checkGroupsOfMember("U2", false, array("Group/G5"));
    	$this->checkGroupsOfMember("U2", true, array("Group/G5", "Group/G2", "Group/G1"));

    	$this->checkGroupsOfMember("U3", false, array("Group/G5"));
    	$this->checkGroupsOfMember("U3", true, array("Group/G5", "Group/G2", "Group/G1"));

    	$this->checkGroupsOfMember("U4", false, array("Group/G4", "Group/G5"));
    	$this->checkGroupsOfMember("U4", true, array("Group/G5", "Group/G2", "Group/G1", "Group/G4", "Group/G'3"));

    	$this->checkGroupsOfMember("U5", false, array("Group/G4"));
    	$this->checkGroupsOfMember("U5", true, array("Group/G1", "Group/G2", "Group/G'3", "Group/G4"));
    	
    	$this->checkGroupsOfMember("U6", false, array("Group/G'3"));
    	$this->checkGroupsOfMember("U6", true, array("Group/G'3", "Group/G1"));
    	
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
			                    array("Group/G4"), null, null,
			                    "IR for PR1", "Right 1");
			$prPR1->addInlineRights(array($ir));
						
			$prPR2 = new HACLSecurityDescriptor(null, "Right/PR2", null,
									            HACLSecurityDescriptor::PET_RIGHT, 
			                                    null, array("U1", "U2"));
			$prPR2->save();
			                                    
			$ir = new HACLRight(HACLRight::DELETE,
			                    null, array("U2"), null,
			                    "IR for PR2", "Right 2");
			$prPR2->addInlineRights(array($ir));
			                                    
			$prPR3 = new HACLSecurityDescriptor(null, "Right/PR3", null,
									            HACLSecurityDescriptor::PET_RIGHT, 
			                                    null, array("U1"));
			$prPR3->save();
			                                    
			$sdA->addPredefinedRights(array($prPR1, $prPR2));
			
			$ir = new HACLRight(HACLRight::READ,
			                    array("Group/G1"), array("U1"), null,
			                    "IR for page A", "Right 3");
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
			$g3 = HACLGroup::newFromName("Group/G'3");
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
			$wl = new HACLWhitelist(array('ACL:Group/G2', "ACL:Group/G'3", 'ACL:Group/G7'));
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
			if (is_null($result)) {
				$result = true;
			}
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
	
	private function checkGroupsOfMember($member, $recursive, $expectedGroups) {
    	$gom = HACLGroup::getGroupsOfMember(User::idFromName($member), HACLGroup::USER, $recursive);
    	$actualGOM = array();
		foreach ($gom as $g) {
			$actualGOM[] = $g['name'];
		}
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
 * Tests some functions on groups
 * 
 * @author thsc
 *
 */
class TestDatabaseGroups extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = FALSE;
	
	protected $mGroupNames;
	
    function setUp() {
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
    	$ds = new HACLGroup(50, "Group/Peter's Group", null, array("U1"));
    	$ds->save();
    	
    	
    	$c->addGroup("Group/Marketing");
    	$c->addGroup("Group/Development");
    	$c->addGroup("Group/Services");
    	
    	$d->addGroup("Group/HaloDev");
    	$d->addGroup("Group/DevNull");
    	
    	$s->addGroup("Group/ProfessionalServices");
    	$s->addGroup("Group/DilettantishServices");
		
    	$this->mGroupNames = array(
    		"Group/Company", "Group/Marketing",	"Group/Development",
    		"Group/HaloDev", "Group/DevNull",	"Group/Services",
    		"Group/ProfessionalServices", "Group/DilettantishServices",
    		"Group/Peter's Group"
    	);
    	
    }

    function tearDown() {
		HACLStorage::getDatabase()->dropDatabaseTables(false);
		HACLStorage::getDatabase()->initDatabaseTables(false);
    }
    
    /**
     * Tests searching for groups whose name contains a string
     */
    function testSearchGroups() {
    	$this->doSearchGroups('e');
    	$this->doSearchGroups('o');
    	$this->doSearchGroups('dev');
    	$this->doSearchGroups('services');
    	$this->doSearchGroups('group');
    	$this->doSearchGroups("Peter's");
		$this->doSearchGroups('Dev');
    }

    /**
     * Performs the actual tests, searching for groups whose name contains a string
     */
    function doSearchGroups($search) {
    	$expected = array();
    	foreach ($this->mGroupNames as $gn) {
    		if (preg_match("/Group\/.*?$search.*/i", $gn)) {
    			$expected[] = $gn;
    		}
    	}
    	$matchingGroups = HACLGroup::searchGroups($search);
    	$mg = array_keys($matchingGroups);
    	$this->assertEquals($expected, $mg);
    }
    
}
