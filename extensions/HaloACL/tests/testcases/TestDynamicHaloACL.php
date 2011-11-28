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

/**
 * This suite tests the features of "Dynamic HaloACL". The protection of HaloACL
 * is controlled by the content of articles.
 * 
 * @author thsc
 *
 */
class TestDynamicHaloACLSuite extends PHPUnit_Framework_TestSuite
{
	private $mArticleManager;
	
	public static function suite() {
		if (!defined('UNIT_TEST_RUNNING')) {
			define('UNIT_TEST_RUNNING', true);
		}
		
		$suite = new TestDynamicHaloACLSuite();
		$suite->addTestSuite('TestDynamicSD');
		$suite->addTestSuite('TestDynamicGroup');
		$suite->addTestSuite('TestDynamicSDWithGroup');
		$suite->addTestSuite('TestDynamicGroupStructure');
		$suite->addTestSuite('TestDynamicAssignees');
		$suite->addTestSuite('TestDynamicMembers');
		$suite->addTestSuite('TestDynamicMembersHierarchy');
		$suite->addTestSuite('TestMembersAssigneesExample');
		$suite->addTestSuite('TestShowDynamicMembers');
		return $suite;
	}
	
	protected function setUp() {
    	HACLStorage::reset(HACL_STORE_SQL);
		HACLStorage::getDatabase()->dropDatabaseTables(false);
		HACLStorage::getDatabase()->initDatabaseTables(false);
		
		HaloACLCommon::createUsers(array("U1", "U2"));
        Skin::getSkinNames();
        
        
   		global $wgUser;
    	$wgUser = User::newFromName("U1");
    	
        $this->initArticleContent();
    	$this->createArticles();
	}
	
	protected function tearDown() {
        $this->mArticleManager->deleteArticles("U1");

        HACLStorage::getDatabase()->dropDatabaseTables(false);
		HACLStorage::getDatabase()->initDatabaseTables(false);
        
	}

//	public static function createArticle($title, $content) {
//	
//		HACLParserFunctions::getInstance()->reset();
//		
//		$title = Title::newFromText($title);
//		$article = new Article($title);
//		// Set the article's content
//		
//		$success = $article->doEdit($content, 'Created for test case', 
//		                            $article->exists() ? EDIT_UPDATE : EDIT_NEW);
//		if (!$success) {
//			echo "Creating article ".$title->getFullText()." failed\n";
//		}
//	}
	
	/**
	 * Checks if the article with the given name $title exists.
	 * 
	 * @param string $title
	 * 		Name of the article.
	 * @return
	 * 		<true>, if the article exists
	 * 		<false> otherwise
	 */
	public static function articleExists($title) {
		$title = Title::newFromText($title);
		return $title->exists();
	}
    
	
	
	private function initArticleContent() {
		
		$this->mArticles = array(
//------------------------------------------------------------------------------		
			'ACL:Right/SDForNewProject' =>
<<<ACL
{{#access: assigned to=User:U1
 |actions=read,edit,formedit,wysiwyg,create,move,delete,annotate
 |description=fullaccess for U:U1
 |name=FA}}

{{#manage rights: assigned to=User:U1,User:WikiSysop}}
[[Category:ACL/Right]]

The article {{{articleName}}} belongs to the category {{{category}}} and was created
by user {{{user}}}.
ACL
,
//------------------------------------------------------------------------------		
			'ACL:Right/SDForNewRequest' =>
<<<ACL
{{#access: assigned to=User:U1
 |actions=read,edit,formedit,wysiwyg,create,move,delete,annotate
 |description=fullaccess for U:U1
 |name=FA}}

{{#manage rights: assigned to=User:U1,User:WikiSysop}}
[[Category:ACL/Right]]

The article {{{articleName}}} belongs to the category {{{category}}} and was created
by user {{{user}}}.
ACL
,
//------------------------------------------------------------------------------		
			'ACL:Right/DynamicSD' =>
<<<ACL
{{#access: assigned to={{{user}}}
 |actions=read,edit,formedit,wysiwyg,create,move,delete,annotate
 |description=fullaccess for {{{user}}}
 |name=FA}}

{{#manage rights: assigned to={{{user}}},User:WikiSysop}}
[[Category:ACL/Right]]

The article {{{articleName}}} belongs to the category {{{category}}} and was created
by user {{{user}}}.
ACL
,
//------------------------------------------------------------------------------		
			'ACL:Group/ProjectGroupTemplate' =>
<<<ACL
{{#member:members={{{user}}}}}
{{#manage group:assigned to=User:U1}}
[[Category:ACL/Group]]

The article {{{articleName}}} belongs to the category {{{category}}} and was created
by user {{{user}}}.
ACL
,
//------------------------------------------------------------------------------		
			'ACL:Group/RequestGroupTemplate' =>
<<<ACL
{{#member:members={{{user}}}}}
{{#manage group:assigned to=User:U1}}
[[Category:ACL/Group]]

The article {{{articleName}}} belongs to the category {{{category}}} and was created
by user {{{user}}}.
ACL
,
//------------------------------------------------------------------------------		
			'ACL:Right/SDWithGroup' =>
<<<ACL
{{#access: assigned to=Group/GroupFor{{{articleName}}}
 |actions=read,edit,formedit,wysiwyg,create,move,delete,annotate
 |description=fullaccess for Group/GroupFor{{{articleName}}}
 |name=FA}}

{{#manage rights: assigned to=Group/GroupFor{{{articleName}}},User:WikiSysop}}
[[Category:ACL/Right]]

The article {{{articleName}}} belongs to the category {{{category}}} and was created
by user {{{user}}}.
ACL
,
//------------------------------------------------------------------------------		
			'ACL:Group/DynamicGroupManagers' =>
<<<ACL
{{#member:members=User:U1}}
{{#manage group:assigned to=User:U1}}
[[Category:ACL/Group]]
ACL
,
		);
	}

    private function createArticles() {
        $this->mArticleManager = new ArticleManager();
        $this->mArticleManager->createArticles($this->mArticles, "U1");
        $this->mArticleManager->createACLBaseArticles("U1");
    }
 	
}

/**
 * This class tests the automatic creation of Security Descriptors if an article
 * belongs to a category.
 * 
 * It assumes that the HaloACL extension is enabled in LocalSettings.php
 * 
 * @author thsc
 *
 */
class TestDynamicSD extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = FALSE;
	
	// List of articles that were added during a test.
	private $mArticleManager;
	
    function setUp() {
    	$this->mArticleManager = new ArticleManager();
    }

    /**
     * Delete all articles that were created during a test.
     */
    function tearDown() {
    	$this->mArticleManager->deleteArticles("U1");
    }

    /**
     * Creates a normal article and checks that no security descriptor is created.
     */
    function testCreateNormalArticle() {

    	// User U1 creates a public article
    	$this->mArticleManager->createArticles(
    		array("Normal" => "This is a public article."), "U1");
    	
    	$this->assertFalse(TestDynamicHaloACLSuite::articleExists("ACL:Page/Normal"));
    }
    
    /**
     * Data provider for testCreateDynamicCategorySD
     */
    function providerForCreateDynamicCategorySD() {
		// $userName, $article, $category, $SDexpected    	
    	return array(
    		array('U1', 'ProjectA', 'Project', true),
    		array('U2', 'ProjectA', 'Project', true),
    		array('U1', 'RequestA', 'Request', true),
    		array('U2', 'RequestA', 'Request', false)
    	);
    }

    /**
     * Creates articles with a categories that are associated with dynamic 
     * security descriptors.
     * Checks if the security descriptors are created.
     * 
     * @dataProvider providerForCreateDynamicCategorySD
     */
    function testCreateDynamicCategorySD($userName, $article, $category, $SDexpected) {
    	global $haclgDynamicSD;
    	
    	// Configuration for dynamic SD
    	$haclgDynamicSD = array(
	    	array(
				"user"     => "#",			// dynamic SD is applied for all registered users.
											// Specify an array for a list of users
				"category" => "Project",	// dynamic SD for articles of the category request
				"sd"       => "ACL:Right/SDForNewProject", // template for the new security descriptor
				"allowUnauthorizedSDChange" => true // must be <true> if the current user has no modification rights for the current SD
	    	),
	    	array(
				"user"     => "U1",			// dynamic SD is applied for all registered users.
											// Specify an array for a list of users
				"category" => "Request",	// dynamic SD for articles of the category request
				"sd"       => "ACL:Right/SDForNewRequest", // template for the new security descriptor
				"allowUnauthorizedSDChange" => true // must be <true> if the current user has no modification rights for the current SD
	    	)
    	);

   		$catAnno = is_null($category) ? '' : "[[Category:$category]]";
    	$content = "This is an article. $catAnno";
    	
    	$this->mArticleManager->createArticles(array($article => $content), $userName);
    	$this->mArticleManager->addArticle("ACL:Page/$article");
    	
    	if (!$SDexpected) {
    		// expected that NO SD is created
    		$this->assertFalse(TestDynamicHaloACLSuite::articleExists("ACL:Page/$article"));
    		return;
    	}
    	
    	// expected that an SD is created
    	$this->assertTrue(TestDynamicHaloACLSuite::articleExists("ACL:Page/$article"));
    	
    	// Get the content of the SD
    	$sd = new Article(Title::newFromText("ACL:Page/$article"));
		$content = $sd->getContent();
	
    	// Verify if the variables are replaced in the SD
    	$templText = "The article {{{articleName}}} belongs to the category {{{category}}} and was created by user {{{user}}}.";
		$templText = str_replace("{{{articleName}}}", $article, $templText);
		$templText = str_replace("{{{category}}}", "Category:$category", $templText);
		$templText = str_replace("{{{user}}}", "User:$userName", $templText);
		
		// Remove whitespaces
		$content = preg_replace("/\s*/", "", $content);
		$templText = preg_replace("/\s*/", "", $templText);
		$pos = strpos($content, $templText);
		$this->assertTrue($pos !== false, "SD does not contain the expected string $templText.");

		// Check access to the default security descriptor
    	$checkRights = array(
			array($article, 'U1', 'edit', true),
			array($article, 'U2', 'edit', false),
			);
		HaloACLCommon::checkRights($this, "testCreateDynamicCategorySD", $checkRights);
    }

    /**
     * Data provider for testUnauthorizedSDChange
     */
    function providerForUnauthorizedSDChange() {
    	return array(
    		// user1, user2, allowUnauthorizedSDChange, exceptionExpected
    		array('U1', 'U1', true, false),
    		array('U1', 'U1', false, false),
    		array('U1', 'U1', null, false),
    		array('U1', 'U2', true, false),
    		array('U1', 'U2', false, true),
    		array('U1', 'U2', null, true),
    	);
    }
    
    /**
     * Creates articles with a categories that are associated with dynamic 
     * security descriptors. Then the category of the article is changed so that
     * another SD should be created. This must fail if the user is not authorized
     * to change the SD and "allowUnauthorizedSDChange" is not set <true>.
     * The user U1 is always authorized as he has modification rights for the SD.
     * 
     * @dataProvider providerForUnauthorizedSDChange
     */
    function testUnauthorizedSDChange($user1, $user2, $allowUnauthorizedSDChange,
    								  $exceptionExpected) {
    	global $haclgDynamicSD;
    	
    	// Configuration for dynamic SD
    	$haclgDynamicSD = array(
	    	array(
				"user"     => "#",			// dynamic SD is applied for all registered users.
											// Specify an array for a list of users
				"category" => "Project",	// dynamic SD for articles of the category request
				"sd"       => "ACL:Right/SDForNewProject" // template for the new security descriptor
			)
    	);

    	if (!is_null($allowUnauthorizedSDChange)) {
    		$haclgDynamicSD[0]["allowUnauthorizedSDChange"] = $allowUnauthorizedSDChange;
    	}
    	$content = "This is an article. [[Category:Project]]";

    	// Create article as user 1
    	$this->mArticleManager->createArticles(array("ProjectA" => $content), $user1);
    	$this->mArticleManager->addArticle("ACL:Page/ProjectA");
    	
   		// expected that an SD is created
    	$this->assertTrue(TestDynamicHaloACLSuite::articleExists("ACL:Page/ProjectA"));
    	
    	// Create article as user 2
    	try {
    		$this->mArticleManager->createArticles(array("ProjectA" => $content), $user2);
    	} catch (Exception $e) {
    		if (!$exceptionExpected) {
    			// No exception expected => fail
    			throw $e;
    		}
    		// Verify that exception is the correct one
    		$this->assertTrue($e instanceof HACLSDException, "Expected exception of class HACLSDException");
    		$this->assertEquals(HACLSDException::USER_CANT_MODIFY_SD, $e->getCode(), "Expected exception code USER_CANT_MODIFY_SD");
    		return;
    	}

    	if ($exceptionExpected) {
    		// The expected exception was not raised.
    		$this->fail("Expected exception of type HACLSDException::USER_CANT_MODIFY_SD.");
    	}
    }
    
    /**
     * Data provider for testDynamicSDRuleCompleteness
     */
    function providerForDynamicSDRuleCompleteness() {
    	return array(
    		// dynamic SD rule, exceptionExpected
    		array(array(), true),
    		array(array("user" => "U1"), true),
    		array(array("user" => "U1", "category" => "Project"), true),
    		array(array("user" => "U1", "category" => "Project", "sd" => "ACL:Right/SomeRight"), false),
    	);
    }
    
    
    /**
     * The creation of dynamic SDs is configured by rules in the global variable
     * $haclgDynamicSD. This test verifies that the system throws exceptions if 
     * such a rule is not complete.
     * 
     * @param array $rule
     * 		The description of a rule
     * @param $exceptionExpected
     * 		<true>, if an exception is expected for the rule
     * @dataProvider providerForDynamicSDRuleCompleteness
     */
    function testDynamicSDRuleCompleteness($rule, $exceptionExpected) {
    	global $wgUser, $haclgDynamicSD;
    	
    	$haclgDynamicSD = array($rule);
    	
    	// Create an article that triggers a dynamic SD rule
    	$content = "This is an article. [[Category:Project]]";
    	
    	try {
    		$this->mArticleManager->createArticles(array("ProjectA" => $content), "U1");
    	} catch (Exception $e) {
    		if (!$exceptionExpected) {
    			// No exception expected => fail
    			throw $e;
    		}
    		// Verify that exception is the correct one
    		$this->assertTrue($e instanceof HACLSDException, "Expected exception of class HACLSDException");
    		$this->assertEquals(HACLSDException::INCOMPLETE_DYNAMIC_SD_RULE, $e->getCode(), "Expected exception code INCOMPLETE_DYNAMIC_SD_RULE");
    		return;
    	}

    	if ($exceptionExpected) {
    		// The expected exception was not raised.
    		$this->fail("Expected exception of type HACLSDException::INCOMPLETE_DYNAMIC_SD_RULE.");
    	}
    }
    
    /**
     * Creates an article with a category that is associated with a dynamic SD
     * The article is saved several times by several users. The SD template grants
     * full access to the current user.
     * Checks if the rights are set correctly
     * 
     */
    function testSequenceofSDs() {
    	global $haclgDynamicSD, $wgUser;
    	
    	// Configuration for dynamic SD
    	$haclgDynamicSD = array(
	    	array(
				"user"     => "#",			// dynamic SD is applied for all registered users.
											// Specify an array for a list of users
				"category" => "Project",	// dynamic SD for articles of the category request
				"sd"       => "ACL:Right/DynamicSD", // template for the new security descriptor
				"allowUnauthorizedSDChange" => true // must be <true> if the current user has no modification rights for the current SD
	    	)
    	);

    	$this->mAddedArticles[] = "ProjectA";
    	$this->mAddedArticles[] = "ACL:Page/ProjectA";
    	$content = "This is an article. [[Category:Project]]";

    	$checkRightsU1 = array(
			array("ProjectA", 'U1', 'edit', true),
			array("ProjectA", 'U2', 'edit', false),
			);
		$checkRightsU2 = array(
			array("ProjectA", 'U1', 'edit', false),
			array("ProjectA", 'U2', 'edit', true),
			);
		
		// Make "ProjectA" accessible for U1
    	$this->mArticleManager->createArticles(array("ProjectA" => $content), "U1");
		HaloACLCommon::checkRights($this, "testSequenceofSDs_1", $checkRightsU1);
		
		// Make "ProjectA" accessible for U2
    	$this->mArticleManager->createArticles(array("ProjectA" => $content), "U2");
		HaloACLCommon::checkRights($this, "testSequenceofSDs_2", $checkRightsU2);
		
		// Make "ProjectA" accessible for U1
    	$this->mArticleManager->createArticles(array("ProjectA" => $content), "U1");
		HaloACLCommon::checkRights($this, "testSequenceofSDs_3", $checkRightsU1);
		
		// Make "ProjectA" accessible for U2
    	$this->mArticleManager->createArticles(array("ProjectA" => $content), "U2");
		HaloACLCommon::checkRights($this, "testSequenceofSDs_2", $checkRightsU2);
				
    }
    
    
	
}


/**
 * This class tests the automatic creation of groups if an article
 * belongs to a category.
 * 
 * It assumes that the HaloACL extension is enabled in LocalSettings.php
 * 
 * @author thsc
 *
 */
class TestDynamicGroup extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = FALSE;
	
	private $mArticleManager;
		
    function setUp() {
    	$this->mArticleManager = new ArticleManager();
    }

    /**
     * Delete all articles that were created during a test.
     */
    function tearDown() {
		$this->mArticleManager->deleteArticles("U1");
    }

    /**
     * Creates a normal article and checks that no group is created.
     */
    function testCreateNormalArticle() {
    	global $haclgDynamicGroup;
    	$haclgDynamicGroup = array(
	    	array(
				"user"     => '#',	
			  	"category" => "Project",  // dynamic groups are generated for articles with category "Project"
			    "groupTemplate" => "ACL:Group/ProjectGroupTemplate", // this is the template for all automatically created groups
			    "name" => "MembersOf{{{articleName}}}"
	    	)
		);
    	
    	// User U1 creates a public article
    	$this->mArticleManager->createArticles(
    		array("Normal" => "This is a normal article."), "U1");
    	
    	$this->assertFalse(TestDynamicHaloACLSuite::articleExists("ACL:Group/MembersOfNormal"));
    }
    
    /**
     * Data provider for testCreateDynamicGroup
     */
    function providerForCreateDynamicGroup() {
    	return array(
	    	// $userName, $article, $category, $groupExpected
    		array('U1', 'ProjectA', 'Project', true),
    		array('U2', 'ProjectA', 'Project', true),
    		array('U1', 'RequestA', 'Request', true),
    		array('U2', 'RequestA', 'Request', false)
    	);
    }

    /**
     * Creates articles with categories that are associated with dynamic groups.
     * Checks if the groups are created.
     * 
     * @dataProvider providerForCreateDynamicGroup
     */
    function testCreateDynamicGroup($userName, $article, $category, $groupExpected) {
    	global $haclgDynamicGroup;
    	
    	// Configuration for dynamic groups
    	$haclgDynamicGroup = array(
	    	array(
				"user"     => '#',	
			  	"category" => "Project",  // dynamic groups are generated for articles with category "Project"
			    "groupTemplate" => "ACL:Group/ProjectGroupTemplate", // this is the template for all automatically created groups
			    "name" => "ACL:Group/MembersOf{{{articleName}}}",  // Naming scheme for dynamic groups
	    	),
	    	array(
				"user"     => 'U1',	
			  	"category" => "Request",  // dynamic groups are generated for articles with category "Project"
			    "groupTemplate" => "ACL:Group/RequestGroupTemplate", // this is the template for all automatically created groups
			    "name" => "ACL:Group/MembersOf{{{articleName}}}",  // Naming scheme for dynamic groups
	    	)
	    );
    	
   		$catAnno = is_null($category) ? '' : "[[Category:$category]]";
    	$content = "This is an article. $catAnno";
    	$groupName = "ACL:Group/MembersOf$article";
    	
    	$this->mArticleManager->createArticles(array($article => $content), $userName);
    	$this->mArticleManager->addArticle($groupName);
    	
    	if (!$groupExpected) {
    		// expected that NO group is created
    		$this->assertFalse(TestDynamicHaloACLSuite::articleExists($groupName));
    		return;
    	}
    	
    	// expected that a group is created
    	$this->assertTrue(TestDynamicHaloACLSuite::articleExists($groupName));
    	
    	// Get the content of the group
    	$group = new Article(Title::newFromText($groupName));
		$content = $group->getContent();
	
    	// Verify if the variables are replaced in the group
    	$templText = "The article {{{articleName}}} belongs to the category {{{category}}} and was created by user {{{user}}}.";
		$templText = str_replace("{{{articleName}}}", $article, $templText);
		$templText = str_replace("{{{category}}}", "Category:$category", $templText);
		$templText = str_replace("{{{user}}}", "User:$userName", $templText);
		
		// Remove whitespaces
		$content = preg_replace("/\s*/", "", $content);
		$templText = preg_replace("/\s*/", "", $templText);
		$pos = strpos($content, $templText);
		$this->assertTrue($pos !== false, "Group does not contain the expected string $templText.");

		// Check members of the group
    	$checkMembers = array(
			array($userName, "MembersOf$article", true),
		);
		$this->checkGroupMembers("testCreateDynamicGroup", $checkMembers);
    }

    /**
     * Data provider for testDynamicGroupRuleCompleteness
     */
    function providerForDynamicGroupRuleCompleteness() {
    	return array(
    		// dynamic SD rule, exceptionExpected
    		array(array(), true),
    		array(array("user" => "U1"), true),
    		array(array("user" => "U1", "category" => "Project"), true),
    		array(array("user" => "U1", "category" => "Project", 
    		            "groupTemplate" => "ACL:Group/ProjectGroupTemplate"), true),
    		array(array("user" => "U1", "category" => "Project", 
    		            "groupTemplate" => "ACL:Group/ProjectGroupTemplate",
    		            "name" => "ACL:Group/MemberOf{{{articleName}}}"), false),
    	);
    }
    
    
    /**
     * The creation of dynamic groups is configured by rules in the global variable
     * $haclgDynamicGroup. This test verifies that the system throws exceptions if 
     * such a rule is not complete.
     * 
     * @param array $rule
     * 		The description of a rule
     * @param $exceptionExpected
     * 		<true>, if an exception is expected for the rule
     * @dataProvider providerForDynamicGroupRuleCompleteness
     */
    function testDynamicGroupRuleCompleteness($rule, $exceptionExpected) {
    	global $wgUser, $haclgDynamicGroup;
    	
    	$haclgDynamicGroup = array($rule);
    	
    	// Create an article that triggers a dynamic SD rule
    	$content = "This is an article. [[Category:Project]]";
    	
    	try {
    		$this->mArticleManager->addArticle("ACL:Page/ProjectA");
    		$this->mArticleManager->addArticle("ACL:Group/MemberOfProjectA");
    		$this->mArticleManager->createArticles(array("ProjectA" => $content), "U1");
    		
    	} catch (Exception $e) {
    		if (!$exceptionExpected) {
    			// No exception expected => fail
    			throw $e;
    		}
    		// Verify that exception is the correct one
    		$this->assertTrue($e instanceof HACLSDException, "Expected exception of class HACLSDException");
    		$this->assertEquals(HACLSDException::INCOMPLETE_DYNAMIC_GROUP_RULE, $e->getCode(), "Expected exception code INCOMPLETE_DYNAMIC_GROUP_RULE");
    		return;
    	}

    	if ($exceptionExpected) {
    		// The expected exception was not raised.
    		$this->fail("Expected exception of type HACLSDException::INCOMPLETE_DYNAMIC_GROUP_RULE.");
    	}
    }
    
    /**
     * Tests if a user is member of a group.
     * @param string $testcase
     * 		Name of the test case
     * @param array<array<>> $config
     * 		This array contains arrays with user names, group names and expected
     * 		membership.
     */
    private function checkGroupMembers($testcase, array $config) {
    	foreach ($config as $c) {
    		$userName = $c[0];
    		$groupName = $c[1];
    		$expectedMember = $c[2];
    		
    		$group = HACLGroup::newFromName("Group/$groupName");
			$this->assertEquals($expectedMember, $group->hasUserMember($userName, false), 
				"Test of group membership failed for user $userName in group $groupName\n"
			    ." (Testcase: $testcase)\n");
    	}
    }
}

/**
 * This class tests the automatic creation of Security Descriptors and a group
 * for an article that belongs to a category. The SD contains the group in a 
 * variable
 * 
 * It assumes that the HaloACL extension is enabled in LocalSettings.php
 * 
 * @author thsc
 *
 */
class TestDynamicSDWithGroup extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = FALSE;
	
	private $mArticleManager;
		
    function setUp() {
    	$this->mArticleManager = new ArticleManager();
    }

    /**
     * Delete all articles that were created during a test.
     */
    function tearDown() {
		$this->mArticleManager->deleteArticles("U1");
    }
	
    /**
     * Creates an article that is associated with a dynamic SD and a dynamic group.
     */
    function testCreateSDWithGroup() {
    	global $haclgDynamicSD, $haclgDynamicGroup;
    	
    	// Configuration for dynamic SD
    	$haclgDynamicSD = array(
	    	array(
				"user"     => "#",			// dynamic SD is applied for all registered users.
											// Specify an array for a list of users
				"category" => "Project",	// dynamic SD for articles of the category request
				"sd"       => "ACL:Right/SDWithGroup", // template for the new security descriptor
				"allowUnauthorizedSDChange" => true // must be <true> if the current user has no modification rights for the current SD
	    	),
    	);
    	
    	// Configuration for dynamic groups
    	$haclgDynamicGroup = array(
	    	array(
				"user"     => '#',	
			  	"category" => "Project",  // dynamic groups are generated for articles with category "Project"
			    "groupTemplate" => "ACL:Group/ProjectGroupTemplate", // this is the template for all automatically created groups
			    "name" => "ACL:Group/GroupFor{{{articleName}}}",  // Naming scheme for dynamic groups
	    	),
	    );
	    
    	$article = "My Project";
    	$content = "This is an article. [[Category:Project]]";
    	$groupName = "ACL:Group/GroupFor$article";
    	$sdName = "ACL:Page/$article";
    	
    	$this->mArticleManager->createArticles(array($article => $content), "U1");
    	$this->mArticleManager->addArticle($groupName);
    	$this->mArticleManager->addArticle($sdName);

    	// Verify that group and SD were created according to the rule
    	$this->assertTrue(TestDynamicHaloACLSuite::articleExists($groupName));
    	$this->assertTrue(TestDynamicHaloACLSuite::articleExists($sdName));
    	
    	// Verify the access restrictions work i.e. that the member of the new
    	// group has access
		$checkRights = array(
			array($article, 'U1', 'edit', true),
			array($article, 'U2', 'edit', false),
			);
		
		HaloACLCommon::checkRights($this, "testCreateSDWithGroup", $checkRights);
    	
    }
    
}

/**
 * This class tests the automatic creation of the hierarchical structure of groups
 * for dynamically created groups.
 * 
 * It assumes that the HaloACL extension is enabled in LocalSettings.php
 * 
 * @author thsc
 *
 */
class TestDynamicGroupStructure extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = FALSE;
	
	private $mArticleManager;
		
    function setUp() {
    	LinkCache::singleton()->clear();
    	$this->mArticleManager = new ArticleManager();
    }

    /**
     * Delete all articles that were created during a test.
     */
    function tearDown() {
    	HACLGroup::setAllowUnauthorizedGroupChange(true);
		$this->mArticleManager->deleteArticles("U1");
    	HACLGroup::setAllowUnauthorizedGroupChange(false);
    	unset($GLOBALS['haclgDynamicGroup']); 	
    	unset($GLOBALS['haclgDynamicRootGroup']); 	
    	unset($GLOBALS['haclgDynamicCategoryGroup']); 	
    	unset($GLOBALS['haclgDynamicGroupManager']); 	
    }
    
    /**
     * Data provider for testCreateDynamicGroupStructure
     */
    function providerForCreateDynamicGroupStructure() {
    	return array(
    		// $article, $category, $group, $parentGroup, $grandparentGroup
    		// $groupManagers, $expectGroupsCreated, $expectedGroupManagers

    		array("My Request", "Request", "GroupForMy Request", 
    		      "Dynamic groups for ", "Dynamic groups",
    		      null, false, null),
    		array("My Request", "Request", "GroupForMy Request", 
    		      null, "Dynamic groups",
    		      array("users" => "WikiSysop"), false, null),
    		array("My Request", "Request", "GroupForMy Request", 
    		      "Dynamic groups for ", null,
    		      array("users" => "WikiSysop"), false, null),
    		array("My Request", "Request", "GroupForMy Request", 
    		      "Dynamic groups for ", "Dynamic groups",
    		      array("users" => "U1"), true, 
    		      array(array("WikiSysop", true),
    		            array("U1", true),
    		            array("U2", false))),
    		array("My Request", "Request", "GroupForMy Request", 
    		      "Dynamic groups for ", "Dynamic groups",
    		      array("groups" => "Group/DynamicGroupManagers"), true, 
    		      array(array("WikiSysop", true),
    		            array("U1", true),
    		            array("U2", false))),
    		array("My Request", "Request", "GroupForMy Request", 
    		      "Dynamic groups for ", "Dynamic groups",
    		      array("groups" => "Group/DynamicGroupManagers",
    		            "users" => "U2"), true, 
    		      array(array("WikiSysop", true),
    		            array("U1", true),
    		            array("U2", true))),
    		array("My Request", "Request", "GroupForMy Request", 
    		      "Dynamic groups for ", "Dynamic groups",
    		      array("users" => "U2"), true, 
    		      array(array("WikiSysop", true),
    		            array("U1", false),
    		            array("U2", true))),
    		            
			array("My Project", "Project", "GroupForMy Project", 
				  "Dynamic groups for ", "Dynamic groups",
				  array("users" => "U1,U2"), true,
				  array(array("WikiSysop", true),
    		            array("U1", true),
    		            array("U2", true))),
    	);
    }
    
    /**
     * Creates articles that are associated with a dynamic group and checks
     * if all super groups are created.
     * 
     * @dataProvider providerForCreateDynamicGroupStructure
     */
    function testCreateDynamicGroupStructure($article, $category, $group, 
                                             $parentGroup, $grandparentGroup,
                                             $groupManagers,
                                             $expectGroupsCreated,
                                             $expectedGroupManagers) {
    	global $haclgDynamicGroup, $haclgDynamicRootGroup, 
    	       $haclgDynamicCategoryGroup, $haclgDynamicGroupManager;
    	
    	$haclgDynamicRootGroup = $grandparentGroup;
    	$haclgDynamicCategoryGroup = $parentGroup;
    	$haclgDynamicGroupManager = $groupManagers;
    	
    	// Configuration for dynamic groups
    	$haclgDynamicGroup = array(
	    	array(
				"user"     => '#',	
			  	"category" => "Project",
			    "groupTemplate" => "ACL:Group/ProjectGroupTemplate",
			    "name" => "ACL:Group/GroupFor{{{articleName}}}",
	    	),
	    	array(
				"user"     => '#',	
			  	"category" => "Request",
			    "groupTemplate" => "ACL:Group/ProjectGroupTemplate",
			    "name" => "ACL:Group/GroupFor{{{articleName}}}",
	    	),
	    );
	    
    	$content = "This is an article. [[Category:$category]]";
    	$parentGroup .= "$category";
    	
    	$this->mArticleManager->createArticles(array($article => $content), "U1");
    	$this->mArticleManager->addArticle("ACL:Group/".$grandparentGroup);
    	$this->mArticleManager->addArticle("ACL:Group/".$parentGroup);
    	$this->mArticleManager->addArticle("ACL:Group/".$group);

    	// Verify that group and its parent groups exist
    	$this->assertTrue(TestDynamicHaloACLSuite::articleExists("ACL:Group/".$group));
    	$this->assertEquals($expectGroupsCreated, TestDynamicHaloACLSuite::articleExists("ACL:Group/".$parentGroup));
    	$this->assertEquals($expectGroupsCreated, TestDynamicHaloACLSuite::articleExists("ACL:Group/".$grandparentGroup));
    	
    	// Verify the hierarchy of groups
    	if ($expectGroupsCreated) {
			$checkGroups = array(
				array("Group/".$group, "Group/".$parentGroup, true),
				array("Group/".$parentGroup, "Group/".$grandparentGroup, true),
				);
			
			$this->checkGroupMembers("testCreateDynamicGroupStructure", $checkGroups);
			
			
			$checkGroupManagers = array();
			foreach ($expectedGroupManagers as $egm) {
				$checkGroupManagers[] = array("Group/".$parentGroup, $egm[0], $egm[1]);
				$checkGroupManagers[] = array("Group/".$grandparentGroup, $egm[0], $egm[1]);
			}
			$this->checkGroupManagers("testCreateDynamicGroupStructure", $checkGroupManagers);
    	}
    }
    
    /**
     * Creates several articles that are associated with dynamic groups and checks
     * if all super groups are created.
     * 
     */
    function testCreateDynamicGroupStructure2() {
    	global $haclgDynamicGroup, $haclgDynamicRootGroup, 
    	       $haclgDynamicCategoryGroup, $haclgDynamicGroupManager;
    	
    	$haclgDynamicRootGroup = "Dynamic groups";
    	$haclgDynamicCategoryGroup = "Dynamic groups for ";
    	$haclgDynamicGroupManager = array("users" => "WikiSysop");
    	
    	// Configuration for dynamic groups
    	$haclgDynamicGroup = array(
	    	array(
				"user"     => '#',	
			  	"category" => "Project",
			    "groupTemplate" => "ACL:Group/ProjectGroupTemplate",
			    "name" => "ACL:Group/GroupFor{{{articleName}}}",
	    	),
	    	array(
				"user"     => '#',	
			  	"category" => "Request",
			    "groupTemplate" => "ACL:Group/ProjectGroupTemplate",
			    "name" => "ACL:Group/GroupFor{{{articleName}}}",
	    	),
	    );
	    
    	// Create two projects and two requests
    	$this->mArticleManager->createArticles(
    		array("ProjectA" => "This is an article. [[Category:Project]]",
    		      "ProjectB" => "This is an article. [[Category:Project]]",
    		      "RequestA" => "This is an article. [[Category:Request]]",
    		      "RequestB" => "This is an article. [[Category:Request]]"), "U1");
    	$this->mArticleManager->addArticle("ACL:Group/".$haclgDynamicRootGroup);
    	$this->mArticleManager->addArticle("ACL:Group/{$haclgDynamicCategoryGroup}Project");
    	$this->mArticleManager->addArticle("ACL:Group/{$haclgDynamicCategoryGroup}Request");
    	$this->mArticleManager->addArticle("ACL:Group/GroupForProjectA");
    	$this->mArticleManager->addArticle("ACL:Group/GroupForProjectB");
    	$this->mArticleManager->addArticle("ACL:Group/GroupForRequestA");
    	$this->mArticleManager->addArticle("ACL:Group/GroupForRequestB");

    	// Verify that all articles exist
    	foreach ($this->mArticleManager->getAddedArticles() as $a) {
	    	$this->assertTrue(TestDynamicHaloACLSuite::articleExists($a));
    	} 
    	
    	// Verify the hierarchy of groups
    	$gpa = "Group/GroupForProjectA";
    	$gpb = "Group/GroupForProjectB";
    	$gra = "Group/GroupForRequestA";
    	$grb = "Group/GroupForRequestA";
    	$gp = "Group/{$haclgDynamicCategoryGroup}Project";
    	$gr = "Group/{$haclgDynamicCategoryGroup}Request";
    	$root = "Group/$haclgDynamicRootGroup";
		$checkGroups = array(
			array($gpa, $gp, true),
			array($gpb, $gp, true),
			array($gra, $gr, true),
			array($grb, $gr, true),
			array($gp, $root, true),
			array($gr, $root, true),
			);
		
		$this->checkGroupMembers("testCreateDynamicGroupStructure2", $checkGroups);
    	
    }
    
    /**
     * Tests if a group is member of a group.
     * @param string $testcase
     * 		Name of the test case
     * @param array<array<>> $config
     * 		This array contains arrays with sub group name, parent group name and expected
     * 		membership.
     */
    private function checkGroupMembers($testcase, array $config) {
    	foreach ($config as $c) {
    		$subGroup = $c[0];
    		$parentGroup = $c[1];
    		$expectedMember = $c[2];
    		
    		$group = HACLGroup::newFromName($parentGroup);
			$this->assertEquals($expectedMember, $group->hasGroupMember($subGroup, false), 
				"Test of group membership failed for group $subGroup in group $parentGroup\n"
			    ." (Testcase: $testcase)\n");
    	}
    }
    
    /**
     * Tests if a group can be managed by a user or a group.
     * @param string $testcase
     * 		Name of the test case
     * @param array<array<>> $config
     * 		This array contains arrays with a group name, the name of a user
     * 		that may or may not manage the groups and the expected value.
     */
    private function checkGroupManagers($testcase, array $config) {
    	foreach ($config as $c) {
    		$group = $c[0];
    		$user = $c[1];
    		$expected = $c[2];
    		
    		$g = HACLGroup::newFromName($group);
    		$canModify = $g->userCanModify($user, false);
			$this->assertEquals($expected, $canModify, 
				"Test of group manager failed for user $user in group $group\n"
			    ." (Testcase: $testcase)\n");
    	}
    }

    
}


/**
 * This class tests the dynamic assignees in access rights. 
 * Members are set as result of a query.
 * 
 * 
 * It assumes that the HaloACL extension is enabled in LocalSettings.php and that 
 * the triples store is running.
 * 
 * @author thsc
 *
 */
class TestDynamicAssignees extends PHPUnit_Framework_TestCase {


	private static $mArticles = array(
//------------------------------------------------------------------------------		
			'Property:TeamMembers' =>
<<<ACL
This is the property for team members.

[[has type::Type:Page| ]]
[[has domain and range::Category:Team; Category:User| ]]
[[has domain and range::Category:Team; Category:ACL/Group| ]]

ACL
,	
//------------------------------------------------------------------------------		
			'Property:ProjectMembers' =>
<<<ACL
This is the property for project members.

[[has type::Type:Page| ]]
[[has domain and range::Category:Team; Category:User| ]]
[[has domain and range::Category:Team; Category:ACL/Group| ]]

ACL
,	
//------------------------------------------------------------------------------		
			'ACL:Group/A' =>
<<<ACL
{{#manage group: assigned to=User:U1}}
{{#member:members=User:U2,User:U3}}

[[Category:ACL/Group]]

ACL
,
//------------------------------------------------------------------------------		
			'ACL:Group/B' =>
<<<ACL
{{#manage group: assigned to=User:U1}}
{{#member:members=User:U4,User:U5 }}

[[Category:ACL/Group]]

ACL
,
//------------------------------------------------------------------------------		
			'TeamA' =>
<<<ACL
This is team A.
[[TeamMembers::User:U1]]
[[TeamMembers::ACL:Group/A]]

[[ProjectMembers::User:U1]]
[[ProjectMembers::User:U2]]
[[ProjectMembers::User:U3]]
[[ProjectMembers::ACL:Group/A]]
[[ProjectMembers::ACL:Group/B]]
{{#ask: [[TeamMembers::+]]
| ?TeamMembers = Team 
}}
{{#ask: [[ProjectMembers::+]]
| ?ProjectMembers = Project
}}
ACL
,	
//------------------------------------------------------------------------------		
			'TeamB' =>
<<<ACL
This is the page for Team B.

ACL
,	
//------------------------------------------------------------------------------		
			'ACL:Page/TeamB' =>
<<<ACL
This is the security descriptor for page TeamB.
{{#access:
| assigned to =User:U5, {{#ask: [[TeamA]][[TeamMembers::+]]|?TeamMembers # =}}
|actions=wysiwyg
|description=Allows wysiwyg
}}

{{#access:
| assigned to =User:U5, 
    {{#sparql: SELECT ?m WHERE { a:TeamA property:ProjectMembers ?m .} |?m # =}}
|actions=formedit
|description=Allows formedit
}}

{{#access:
| assigned to =User:U5, 
    {{#ask: [[TeamA]][[TeamMembers::+]]|?TeamMembers # =}},
    {{#sparql: SELECT ?m WHERE { a:TeamA property:ProjectMembers ?m .} |?m # =}}
|actions=read
|description=Allows read
}}

{{#manage rights: assigned to=User:U1}}s

[[Category:ACL/ACL]]
ACL
,	
	);
	
	private static $mArticleManager;
	protected $backupGlobals = FALSE;

	public static function setUpBeforeClass() {
    	// reset group permissions
    	global $wgGroupPermissions;
    	foreach ($wgGroupPermissions as $group => $permissions) {
    		foreach ($permissions as $p => $value) {
    			$wgGroupPermissions[$group][$p] = true;
    		}
    	}
    	
    	HACLStorage::reset(HACL_STORE_SQL);
    	
    	HaloACLCommon::createUsers(array("U1", "U2", "U3", "U4", "U5"));
    	
        self::$mArticleManager = new ArticleManager();
        self::$mArticleManager->createArticles(self::$mArticles, "U1");
    }

	public static function tearDownAfterClass() {
       	self::$mArticleManager->deleteArticles("U1");
    }
    
    /**
     * Checks if the parameter "assigned to" is parsed correctly if it contains
     * queries for dynamic assignees.
     */
    function testAssignedToParameter() {
    	$popts = new ParserOptions();
		$parser = new Parser();

		global $wgTitle;
		$wgTitle = Title::newFromText("Some Page");
    	
		$teamQuery = <<<ASK
{{#ask: [[TeamMembers::+]]
| ?TeamMembers = Team 
}}
ASK
;
		$projectQuery = <<<ASK
{{#sparql:
SELECT ?pm
WHERE {
	?s property:ProjectMembers ?pm .
}
}}
ASK
;
		
		$wikiText = <<<ACL
{{#access:
| assigned to =User:U1,User:U2, $teamQuery, Group/A, $projectQuery
|actions=edit
|description=Allows edit
}}

ACL
;		
		$pf = HACLParserFunctions::getInstance();

		// Parse the wiki text
		global $wgParser;
		$popts = new ParserOptions();
		$po = $wgParser->parse($wikiText, $wgTitle, $popts );
		
		// verify that dynamic assignees are set
		$rights = $pf->getInlineRights();
		
		// expect one inline right
		$this->assertEquals(1, count($rights), "Expected exactly one inline right.");
		
		$right = $rights[0];
		
		// Check for expected queries
		$daqs = $right->getDynamicAssigneeQueries();
		$this->assertEquals(2, count($daqs), "Expected 2 queries for dynamic assignees.");
		
		// Remove whitespaces
		$qe = preg_replace("/\s*/", "", $teamQuery);
		$qa = preg_replace("/\s*/", "", $daqs[0]);
		$this->assertEquals($qe, $qa, "Expected the team query.");
		
		$qe = preg_replace("/\s*/", "", $projectQuery);
		$qa = preg_replace("/\s*/", "", $daqs[1]);
		$this->assertEquals($qe, $qa, "Expected the project query.");
		
		// Check for expected users
		$users = $right->getUsers();
		$this->assertEquals(2, count($users), "Expected 2 users.");
		
		$this->assertContains(User::idFromName("U1"), $users, "Expected ID of user U1");
		$this->assertContains(User::idFromName("U2"), $users, "Expected ID of user U2");

		// Check for expected groups
		$groups = $right->getGroups();
		$this->assertEquals(1, count($groups), "Expected one group.");
		
		$this->assertContains(HACLGroup::idForGroup("Group/A"), $groups, "Expected ID of group Group/A");
    	
    }
    
    /**
     * Data provider for testDynamicAssignees
     */
    function providerForDynamicAssignees() {
		// $daq, $expectedAssignees
    	return array(
    		array(
    			array(
		    		"{{#ask: [[TeamA]][[TeamMembers::+]]|?TeamMembers # =}}",
		    	), 
		    	array("users"  => array("U1"), 
		    	      "groups" => array("Group/A"))
		   ),
    		array(
    			array(
		    		"{{#sparql: SELECT ?tm WHERE { a:TeamA property:TeamMembers ?tm .} |?tm # =}}"
		    	), 
		    	array("users"  => array("U1"), 
		    	      "groups" => array("Group/A"))
		   ),
    		array(
    			array(
		    		"{{#ask: [[TeamA]][[ProjectMembers::+]]|?ProjectMembers # =}}",
		    	), 
		    	array("users"  => array("U1", "U2", "U3"), 
		    	      "groups" => array("Group/A", "Group/B"))
		   ),
    		array(
    			array(
		    		"{{#sparql: SELECT ?tm WHERE { a:TeamA property:ProjectMembers ?tm .} |?tm # =}}"
		    	), 
		    	array("users"  => array("U1", "U2", "U3"), 
		    	      "groups" => array("Group/A", "Group/B"))
		   ),
    	);
    }
    
    
    /**
     * This function tests if the queries for dynamic assignees are correctly
     * evaluated.
     * 
     * @param array(string) $daq
     * 		Array of queries for dynamic assignees
	 * @param array(string/int/object) $expectedAssignees
	 * 		Expected assignees of the right
     * @dataProvider providerForDynamicAssignees
     */
    public function testDynamicAssigneesTest($daq, $expectedAssignees) {
    	
    	$modes = array(HACLRight::NAME, HACLRight::ID, HACLRight::OBJECT);
    	
    	foreach ($modes as $mode) {
	    	// Create a right with dynamic assignees
	    	$right = new HACLRight(HACLRight::READ, null, null, $daq, "Test", "Test");
	    	
	    	$da = $right->queryDynamicAssignees($mode);
	    	$groups = $da['groups'];
	    	$users  = $da['users'];
	    	$expGroups = $expectedAssignees['groups']; 
	    	$expUsers  = $expectedAssignees['users']; 
	    	$numExpected = count($groups);
	    	$this->assertEquals($numExpected, count($expGroups), "Expected $numExpected dynamic groups.");
	    	$numExpected = count($users);
	    	$this->assertEquals($numExpected, count($expUsers), "Expected $numExpected dynamic users.");
	
	    	// Check expected groups
	    	if ($mode == HACLRight::ID || $mode == HACLRight::OBJECT) {
	    		// convert all expected group names to IDs
	    		foreach ($expGroups as $k => $g) {
	    			$expGroups[$k] = HACLGroup::idForGroup($g);	
	    		}
	    	}
	    	if ($mode == HACLRight::OBJECT) {
	    		// convert all actual group objects to IDs
	    		foreach ($groups as $k => $g) {
	    			$this->assertTrue($g instanceof HACLGroup, "Expected an instance of HACLGroup.");
	    			$groups[$k] = $g->getGroupID();	
	    		}
	    	}
	    	// Check expected groups
	    	foreach ($expGroups as $eg) {
	    		if ($mode == HACLRight::NAME) {
	    			$this->assertContains($eg, $groups, "Expected group <$eg> to be a dynamic member.");
	    		} else {
	    			// ID or OBJECT
	    			$this->assertContains($eg, $groups, "Expected group with ID <$eg> to be a dynamic member.");
	    		}
	    	}

	    	// Check expected users
	    	if ($mode == HACLRight::ID || $mode == HACLRight::OBJECT) {
	    		// convert all expected user names to IDs
	    		foreach ($expUsers as $k => $u) {
	    			$expUsers[$k] = User::idFromName($u);	
	    		}
	    	}
	    	if ($mode == HACLRight::OBJECT) {
	    		// convert all actual user objects to IDs
	    		foreach ($users as $k => $u) {
	    			$this->assertTrue($u instanceof User, "Expected an instance of User.");
	    			$users[$k] = $u->getId();	
	    		}
	    	}
	    	// Check expected users
	    	foreach ($expUsers as $eu) {
	    		if ($mode == HACLRight::NAME) {
	    			$this->assertContains($eu, $users, "Expected user <$eu> to be a dynamic member.");
	    		} else {
	    			// ID or OBJECT
	    			$this->assertContains($eu, $users, "Expected user with ID <$eu> to be a dynamic member.");
	    		}
	    	}
    	}
    }
    
    
    /**
     * Data provider for testDynamicAssigneeRights
     */
    function providerForDynamicAssigneeRights() {
		// $daq, $grantedForUsers, $deniedForUsers
    	return array(
    		array(
    			array(
		    		"{{#ask: [[TeamA]][[TeamMembers::+]]|?TeamMembers # =}}",
		    	), 
		    	array("U1", "U2", "U3"),
		    	array("U4", "U5"),
			),
    		array(
    			array(
		    		"{{#sparql: SELECT ?tm WHERE { a:TeamA property:TeamMembers ?tm .} |?tm # =}}"
		    	), 
		    	array("U1", "U2", "U3"),
		    	array("U4", "U5"),
		    ),
    		array(
    			array(
		    		"{{#ask: [[TeamA]][[ProjectMembers::+]]|?ProjectMembers # =}}",
		    	), 
		    	array("U1", "U2", "U3", "U4", "U5"),
		    	array()
		   ),
    		array(
    			array(
		    		"{{#sparql: SELECT ?tm WHERE { a:TeamA property:ProjectMembers ?tm .} |?tm # =}}"
		    	), 
		    	array("U1", "U2", "U3", "U4", "U5"),
		    	array()
			),
    	);
    }
    
    
    
	/**
     * This function tests if rights for dynamic assignees are correctly
     * evaluated.
     * 
     * @param array(string) $daq
     * 		Array of queries for dynamic assignees
	 * @param array(string) $grantedForUsers
	 * 		Users who get the right
	 * @param array(string) $deniedForUsers
	 * 		Users who do not get the right
	 * 
     * @dataProvider providerForDynamicAssigneeRights
     */
    public function testDynamicAssigneeRights($daq, $grantedForUsers, $deniedForUsers) {
    	
    	// Create a right with dynamic assignees
    	$right = new HACLRight(HACLRight::READ, null, null, $daq, "Test", "Test");
    	
    	// Check the granted rights for users
    	foreach ($grantedForUsers as $u) {
    		$this->assertTrue($right->grantedForUser($u), "Expected that the right is granted for user $u");
    	}
    	// Check the denied rights for users
    	foreach ($deniedForUsers as $u) {
    		$this->assertFalse($right->grantedForUser($u), "Expected that the right is denied for user $u");
    	}
    	
    }
    
    /**
     * Checks if the queries for assignees are stored correctly in the database.
     */
    public function testDynamicAssigneeDB() {
    	// Get the Security Descriptor of "TeamB"
    	$sd = HACLSecurityDescriptor::newFromName("ACL:Page/TeamB");
    	
    	// Get all three rights of the SD
    	$rightIDs = $sd->getInlineRights();
    	
    	$r1 = HACLRight::newFromID($rightIDs[0]);
    	$r2 = HACLRight::newFromID($rightIDs[1]);
    	$r3 = HACLRight::newFromID($rightIDs[2]);
    	
    	$daq1 = $r1->getDynamicAssigneeQueries();
    	$daq2 = $r2->getDynamicAssigneeQueries();
    	$daq3 = $r3->getDynamicAssigneeQueries();
    	
    	$this->assertEquals(4, count($daq1) + count($daq2) + count($daq3), 
    						"Expected four queries for dynamic assignees.");
    	
    }
    
    /**
     * Data provider for testDynamicAssigneeRightsUserCan
     */
    function providerForDynamicAssigneeRightsUserCan() {
    	// $action, $grantedForUsers, $deniedForUsers
    	return array(
    		array("read", 
    		      array("U1", "U2", "U3", "U4", "U5"),
    		      array()),
    		array("formedit", 
    		      array("U1", "U2", "U3", "U4", "U5"),
    		      array()),
    		array("wysiwyg", 
    		      array("U1", "U2", "U3", "U5"),
    		      array("U4")),
    	);
    }
    
    
    /**
     * Checks if the rights with dynamic assignees from the database are correctly
     * evaluated in the userCan function for article TeamB.
     * 
     * @param string $action
     * 		The action to test
     * @param array<string> $grantedForUsers
     * 		List of users with granted access
     * @param unknown_type $deniedForUsers
     * 		List of users with denied access
     * 
     * @dataProvider providerForDynamicAssigneeRightsUserCan
     */
    public function testDynamicAssigneeRightsUserCan($action, $grantedForUsers, $deniedForUsers) {
    	$test = array(array("TeamB", null, $action, null));
    	
    	// Check the granted rights for users
    	foreach ($grantedForUsers as $u) {
    		$test[0][1] = $u;
    		$test[0][3] = true;
     		HaloACLCommon::checkRights($this, 'testDynamicAssigneeRightsUserCan-granted', $test);
    	}
    	
    	// Check the denied rights for users
    	foreach ($deniedForUsers as $u) {
    		$test[0][1] = $u;
    		$test[0][3] = false;
     		HaloACLCommon::checkRights($this, 'testDynamicAssigneeRightsUserCan-denied', $test);
    	}
    	
    }
}
        
/**
 * This class tests the dynamic membership of groups and users in groups. 
 * Members are set as result of a query.
 * 
 * 
 * It assumes that the HaloACL extension is enabled in LocalSettings.php and that 
 * the triples store is running.
 * 
 * @author thsc
 *
 */
class TestDynamicMembers extends PHPUnit_Framework_TestCase {


	private static $mArticles = array(
//------------------------------------------------------------------------------		
			'Property:TeamMembers' =>
<<<ACL
This is the property for team members.

[[has type::Type:Page| ]]
[[has domain and range::Category:Team; Category:User| ]]
[[has domain and range::Category:Team; Category:ACL/Group| ]]

ACL
,	
//------------------------------------------------------------------------------		
			'Property:ProjectMembers' =>
<<<ACL
This is the property for project members.

[[has type::Type:Page| ]]
[[has domain and range::Category:Team; Category:User| ]]
[[has domain and range::Category:Team; Category:ACL/Group| ]]

ACL
,	
//------------------------------------------------------------------------------		
			'ACL:Group/A' =>
<<<ACL
{{#manage group: assigned to=User:U1}}
{{#member:members=User:U2,User:U3}}

[[Category:ACL/Group]]

ACL
,
//------------------------------------------------------------------------------		
			'ACL:Group/B' =>
<<<ACL
{{#manage group: assigned to=User:U1}}
{{#member:members=User:U4,User:U5 }}

[[Category:ACL/Group]]

ACL
,
//------------------------------------------------------------------------------		
			'TeamA' =>
<<<ACL
This is team A.
[[TeamMembers::User:U1]]
[[TeamMembers::ACL:Group/A]]

[[ProjectMembers::User:U1]]
[[ProjectMembers::User:U2]]
[[ProjectMembers::User:U3]]
[[ProjectMembers::ACL:Group/A]]
[[ProjectMembers::ACL:Group/B]]
{{#ask: [[TeamMembers::+]]
| ?TeamMembers = Team 
}}
{{#ask: [[ProjectMembers::+]]
| ?ProjectMembers = Project
}}
ACL
,	
	);
	
	private static $mArticleManager;
	protected $backupGlobals = FALSE;

	public static function setUpBeforeClass() {
    	// reset group permissions
    	global $wgGroupPermissions;
    	foreach ($wgGroupPermissions as $group => $permissions) {
    		foreach ($permissions as $p => $value) {
    			$wgGroupPermissions[$group][$p] = true;
    		}
    	}
    	
    	HACLStorage::reset(HACL_STORE_SQL);
    	
    	HaloACLCommon::createUsers(array("U1", "U2", "U3", "U4", "U5"));
    	        
        self::$mArticleManager = new ArticleManager();
        self::$mArticleManager->createArticles(self::$mArticles, "U1");
    }

	public static function tearDownAfterClass() {
       	self::$mArticleManager->deleteArticles("U1");
       	// Delete groups that were create during tests
       	HACLStorage::getDatabase()->deleteGroup(42);
    }
    

    /**
     * Checks if the parameter "members" is parsed correctly if it contains
     * queries for dynamic assignees.
     */
    function testMembersParameter() {
    	$popts = new ParserOptions();
		$parser = new Parser();

		global $wgTitle;
		$wgTitle = Title::newFromText("Some Page");
    	
		$teamQuery = <<<ASK
{{#ask: [[TeamA]][[TeamMembers::+]]|?TeamMembers # =}}
ASK;
		$projectQuery = <<<ASK
{{#sparql: SELECT ?tm WHERE { a:TeamA property:ProjectMembers ?tm .} |?tm # =}}
ASK;
		
		$wikiText = <<<ACL
{{#member:
| members =User:U1,User:U2, $teamQuery, Group/A, $projectQuery
}}

{{#member:
| members =$teamQuery
}}

ACL
;		
		$pf = HACLParserFunctions::getInstance();

		// Parse the wiki text
		global $wgParser;
		$popts = new ParserOptions();
		$po = $wgParser->parse($wikiText, $wgTitle, $popts );
		
		// verify that dynamic members are set
		$dmq = $pf->getDynamicMemberQueries();
		
		// expect three dynamic member queries
		$this->assertEquals(3, count($dmq), "Expected exactly three dynamic member query.");
		
		// Remove whitespaces
		$qe = preg_replace("/\s*/", "", $teamQuery);
		$qa = preg_replace("/\s*/", "", $dmq[0]);
		$this->assertEquals($qe, $qa, "Expected the team query.");
		
		$qe = preg_replace("/\s*/", "", $projectQuery);
		$qa = preg_replace("/\s*/", "", $dmq[1]);
		$this->assertEquals($qe, $qa, "Expected the project query.");
		
		$qe = preg_replace("/\s*/", "", $teamQuery);
		$qa = preg_replace("/\s*/", "", $dmq[2]);
		$this->assertEquals($qe, $qa, "Expected the team query.");
		
		// Check for expected users
		$users = $pf->getUserMembers();
		$this->assertEquals(2, count($users), "Expected 2 users.");
		
		$this->assertContains("U1", $users, "Expected user U1");
		$this->assertContains("U2", $users, "Expected user U2");

		// Check for expected groups
		$groups = $pf->getGroupMembers();
		$this->assertEquals(1, count($groups), "Expected one group.");
		
		$this->assertContains("Group/A", $groups, "Expected group Group/A");
    	
    }
    
    /**
     * Tests adding and retrieving dynamic member queries to/from a group.
     */
    function testDynamicGroupMemberDB() {
    	$group = new HACLGroup(42, "TestGroup", null, array("U1"));
    	$group->save("U1");
    	
    	$queries = array("Query1", "Query2");
    	$group->addDynamicMemberQueries($queries);
    	$queries = array("Query3", "Query4");
    	$group->addDynamicMemberQueries($queries);
    	
    	for ($i = 0; $i < 2; $i++) {
	    	// First iteration: test the original group object
	    	// Second iteration: Recreate the group object from the database	
	    	$dmq = $group->getDynamicMemberQueries();
	    	
	    	$this->assertEquals(4, count($dmq), "Expected four queries in the group (iteration $i).");
	    	$this->assertContains("Query1", $dmq, "Expected group <Query1> to be a dynamic member query (iteration $i).");
	    	$this->assertContains("Query2", $dmq, "Expected group <Query2> to be a dynamic member query (iteration $i).");
	    	$this->assertContains("Query3", $dmq, "Expected group <Query3> to be a dynamic member query (iteration $i).");
	    	$this->assertContains("Query4", $dmq, "Expected group <Query4> to be a dynamic member query (iteration $i).");

	    	$group = HACLGroup::newFromID(42);
    	}
    	
    }
    
    /**
     * Data provider for testDynamicMembers
     */
    public function providerForDynamicMembers() {
		// $daq, $expectedAssignees
    	return array(
    		array(
    			array(
		    		"{{#ask: [[TeamA]][[TeamMembers::+]]|?TeamMembers # =}}",
		    	), 
		    	array("users"  => array("U1"), 
		    	      "groups" => array("Group/A"))
		   ),
    		array(
    			array(
		    		"{{#sparql: SELECT ?tm WHERE { a:TeamA property:TeamMembers ?tm .} |?tm # =}}"
		    	), 
		    	array("users"  => array("U1"), 
		    	      "groups" => array("Group/A"))
		   ),
    		array(
    			array(
		    		"{{#ask: [[TeamA]][[ProjectMembers::+]]|?ProjectMembers # =}}",
		    	), 
		    	array("users"  => array("U1", "U2", "U3"), 
		    	      "groups" => array("Group/A", "Group/B"))
		   ),
    		array(
    			array(
		    		"{{#sparql: SELECT ?tm WHERE { a:TeamA property:ProjectMembers ?tm .} |?tm # =}}"
		    	), 
		    	array("users"  => array("U1", "U2", "U3"), 
		    	      "groups" => array("Group/A", "Group/B"))
		   ),
    	);
    }
    
    /**
     * This function tests if the queries for dynamic members are correctly
     * evaluated.
     * 
     * @param array(string) $daq
     * 		Array of queries for dynamic members
	 * @param array(string/int/object) $expectedMembers
	 * 		Expected members of the group
	 * 
     * @dataProvider providerForDynamicMembers
     */
    public function testDynamicMembersTest($daq, $expectedMembers) {
   	
    	$modes = array(HACLGroup::NAME, HACLGroup::ID, HACLGroup::OBJECT);
    	
    	foreach ($modes as $mode) {
	    	// Create a group with dynamic members
	    	$group = new HACLGroup(42, "TestGroup", null, "U1");
	    	$group->addDynamicMemberQueries($daq);
	    	
	    	$dm = $group->queryDynamicMembers($mode);
	    	$groups = $dm['groups'];
	    	$users  = $dm['users'];
	    	$expGroups = $expectedMembers['groups']; 
	    	$expUsers  = $expectedMembers['users']; 
	    	$numExpected = count($groups);
	    	$this->assertEquals($numExpected, count($expGroups), "Expected $numExpected dynamic groups.");
	    	$numExpected = count($users);
	    	$this->assertEquals($numExpected, count($expUsers), "Expected $numExpected dynamic users.");
	
	    	// Check expected groups
	    	if ($mode == HACLGroup::ID || $mode == HACLGroup::OBJECT) {
	    		// convert all expected group names to IDs
	    		foreach ($expGroups as $k => $g) {
	    			$expGroups[$k] = HACLGroup::idForGroup($g);	
	    		}
	    	}
	    	if ($mode == HACLGroup::OBJECT) {
	    		// convert all actual group objects to IDs
	    		foreach ($groups as $k => $g) {
	    			$this->assertTrue($g instanceof HACLGroup, "Expected an instance of HACLGroup.");
	    			$groups[$k] = $g->getGroupID();	
	    		}
	    	}
	    	// Check expected groups
	    	foreach ($expGroups as $eg) {
	    		if ($mode == HACLGroup::NAME) {
	    			$this->assertContains($eg, $groups, "Expected group <$eg> to be a dynamic member.");
	    		} else {
	    			// ID or OBJECT
	    			$this->assertContains($eg, $groups, "Expected group with ID <$eg> to be a dynamic member.");
	    		}
	    	}

	    	// Check expected users
	    	if ($mode == HACLGroup::ID || $mode == HACLGroup::OBJECT) {
	    		// convert all expected user names to IDs
	    		foreach ($expUsers as $k => $u) {
	    			$expUsers[$k] = User::idFromName($u);	
	    		}
	    	}
	    	if ($mode == HACLGroup::OBJECT) {
	    		// convert all actual user objects to IDs
	    		foreach ($users as $k => $u) {
	    			$this->assertTrue($u instanceof User, "Expected an instance of User.");
	    			$users[$k] = $u->getId();	
	    		}
	    	}
	    	// Check expected users
	    	foreach ($expUsers as $eu) {
	    		if ($mode == HACLGroup::NAME) {
	    			$this->assertContains($eu, $users, "Expected user <$eu> to be a dynamic member.");
	    		} else {
	    			// ID or OBJECT
	    			$this->assertContains($eu, $users, "Expected user with ID <$eu> to be a dynamic member.");
	    		}
	    	}
    	}
    }
    
    /**
     * Tests the life cycle of a group article with dynamic queries:
     * 1. Article solely with static members
     * 2. Article with one dynamic member query
     * 3. Article with two dynamic member queries
     * 4. Article with one dynamic member query
     */
    public function testGroupArticleLifeCycle() {
		$teamQuery = <<<ASK
{{#ask: [[TeamA]][[TeamMembers::+]]|?TeamMembers # =}}
ASK;
		$projectQuery = <<<ASK
{{#sparql: SELECT ?tm WHERE { a:TeamA property:ProjectMembers ?tm .} |?tm # =}}
ASK;
		$groupSkeleton = <<<TEXT
{{#manage group: assigned to=User:U1}}
[[Category:ACL/Group]]
TEXT;
    	
    	global $wgUser;
    	$wgUser = User::newFromName("U1");
		
//--- Create article without dynamic members ---
		$text = <<<text
{{#member:
| members =User:U1,User:U2, Group/A
}}
$groupSkeleton
text;

		$article = array("ACL:Group/TestGroup" => $text);
		self::$mArticleManager->createArticles($article, "U1");
		
		$this->checkGroupMembers("testGroupArticleLifeCycle-1", "Group/TestGroup", 
								 array("U1", "U2"), array("Group/A"), array());
		
//--- Create article with one dynamic member query ---
		$text = <<<text
{{#member:
| members =User:U1,User:U2, $teamQuery, Group/A
}}
$groupSkeleton
text;

		$article = array("ACL:Group/TestGroup" => $text);
		self::$mArticleManager->createArticles($article, "U1");
		
		$this->checkGroupMembers("testGroupArticleLifeCycle-2", "Group/TestGroup", 
								 array("U1", "U2"), array("Group/A"), array($teamQuery));
		
    
//--- Create article with two dynamic member queries ---
		$text = <<<text
{{#member:
| members =User:U1,User:U2, $teamQuery, Group/A
}}
{{#member:
| members = $projectQuery
}}
$groupSkeleton
text;

		$article = array("ACL:Group/TestGroup" => $text);
		self::$mArticleManager->createArticles($article, "U1");
		
		$this->checkGroupMembers("testGroupArticleLifeCycle-3", "Group/TestGroup", 
								 array("U1", "U2", "U3"), array("Group/A", "Group/B"), 
								 array($teamQuery, $projectQuery));
		
//--- Create article with one dynamic member query ---
		$text = <<<text
{{#member:
| members = $projectQuery
}}
$groupSkeleton
text;

		$article = array("ACL:Group/TestGroup" => $text);
		self::$mArticleManager->createArticles($article, "U1");
		
		$this->checkGroupMembers("testGroupArticleLifeCycle-4", "Group/TestGroup", 
								 array("U1", "U2", "U3"), array("Group/A", "Group/B"), 
								 array($projectQuery));
								 
		
    }
    
    /**
     * Checks the members of a group.
     * 
     * @param string $testCaseName
     * @param string $groupName
     * @param array<string> $expUsers
     * @param array<string> $expGroups
     * @param array<string> $expDMQs
     * 		Expected dynamic member queries
     */
    private function checkGroupMembers($testCaseName, $groupName, array $expUsers,
    								   array $expGroups, array $expDMQs) {

		$group = HACLGroup::newFromName($groupName);

		// Compare set of users
		$users = $group->getUsers(HACLGroup::NAME);
		$equal =    count(array_diff($expUsers, $users)) == 0 
		         && count(array_diff($users, $expUsers)) == 0;
		$this->assertTrue($equal, "Wrong set of users in test <$testCaseName>.");

		// Compare set of groups
		$groups = $group->getGroups(HACLGroup::NAME);
		$equal =    count(array_diff($expGroups, $groups)) == 0 
				 && count(array_diff($groups, $expGroups)) == 0;
		$this->assertEquals($equal, "Wrong set of groups in test <$testCaseName>");
		
		// Compare set of dynamic member queries
		$dmqs = $group->getDynamicMemberQueries();
		foreach ($dmqs as $k => $q) {
			$dmqs[$k] = preg_replace("/\s*/", "", $q);
		}
		foreach ($expDMQs as $k => $q) {
			$expDMQs[$k] = preg_replace("/\s*/", "", $q);
		}
		$equal =    count(array_diff($expDMQs, $dmqs)) == 0 
				 && count(array_diff($dmqs, $expDMQs)) == 0;
		$this->assertEquals($equal, "Wrong set of dynamic member queries in test <$testCaseName>");
		
	}
        
}

/**
 * This class tests the dynamic membership of groups and users in groups. 
 * The groups are organized in a dynamic hierarchy that bases on query results.
 * 
 * 
 * It assumes that the HaloACL extension is enabled in LocalSettings.php and that 
 * the triples store is running.
 * 
 * @author thsc
 *
 */
class TestDynamicMembersHierarchy extends PHPUnit_Framework_TestCase {


	private static $mArticles = array(
//------------------------------------------------------------------------------		
			'Property:Members' =>
<<<ACL
This is the property for members.

[[has type::Type:Page| ]]
[[has domain and range::; Category:User| ]]
[[has domain and range::; Category:ACL/Group| ]]

ACL
,	
//------------------------------------------------------------------------------		
			'ACL:Group/A' =>
<<<ACL
{{#member: |members={{#ask: [[A]][[Members::+]]|?Members # =}} }}

{{#manage group: assigned to=User:U1}}
[[Category:ACL/Group]]

ACL
,
//------------------------------------------------------------------------------		
			'ACL:Group/B' =>
<<<ACL
{{#manage group: assigned to=User:U1}}
{{#member: |members={{#sparql: SELECT ?m WHERE { a:B property:Members ?m .} |?m # =}} }}

[[Category:ACL/Group]]

ACL
,
//------------------------------------------------------------------------------		
			'ACL:Group/C' =>
<<<ACL
{{#member: |members={{#ask: [[C]][[Members::+]]|?Members # =}} }}

{{#manage group: assigned to=User:U1}}
[[Category:ACL/Group]]

ACL
,
//------------------------------------------------------------------------------		
			'ACL:Group/D' =>
<<<ACL
{{#manage group: assigned to=User:U1}}
{{#member: |members={{#sparql: SELECT ?m WHERE { a:D property:Members ?m .} |?m # =}} }}

[[Category:ACL/Group]]

ACL
,
//------------------------------------------------------------------------------		
			'ACL:Group/E' =>
<<<ACL
{{#member: |members={{#ask: [[E]][[Members::+]]|?Members # =}} }}

{{#manage group: assigned to=User:U1}}
[[Category:ACL/Group]]

ACL
,
//------------------------------------------------------------------------------		
			'A' =>
<<<ACL
[[Members::ACL:Group/B]]
[[Members::ACL:Group/C]]
ACL
,	
//------------------------------------------------------------------------------		
			'B' =>
<<<ACL
[[Members::User:U1]]
[[Members::ACL:Group/E]]
ACL
,	
//------------------------------------------------------------------------------		
			'C' =>
<<<ACL
[[Members::User:U2]]
[[Members::ACL:Group/D]]
ACL
,	
//------------------------------------------------------------------------------		
			'D' =>
<<<ACL
[[Members::User:U3]]
[[Members::ACL:Group/A]]
ACL
,	
//------------------------------------------------------------------------------		
			'E' =>
<<<ACL
[[Members::User:U4]]
ACL
,	
//------------------------------------------------------------------------------		
			'Protected' =>
<<<ACL
This article is protected.
ACL
,	
//------------------------------------------------------------------------------		
			'ACL:Page/Protected' =>
<<<ACL
{{#access: assigned to=Group/A
 |actions=read
 |description=read for Group/A
 |name=FA}}

{{#manage rights: assigned to=User:U1}}
[[Category:ACL/Right]]
}}
ACL
,	
	);
	
	private static $mArticleManager;
	protected $backupGlobals = FALSE;

	public static function setUpBeforeClass() {
    	// reset group permissions
    	global $wgGroupPermissions;
    	foreach ($wgGroupPermissions as $group => $permissions) {
    		foreach ($permissions as $p => $value) {
    			$wgGroupPermissions[$group][$p] = true;
    		}
    	}
    	
    	HACLStorage::reset(HACL_STORE_SQL);
    	
    	HaloACLCommon::createUsers(array("U1", "U2", "U3", "U4", "U5"));
    	        
        self::$mArticleManager = new ArticleManager();
        self::$mArticleManager->createArticles(self::$mArticles, "U1");
    }

	public static function tearDownAfterClass() {
       	self::$mArticleManager->deleteArticles("U1");
    }
    
    /**
     * Data provider for test testGroupHasUser
     */
    public function providerForGroupHasUser() {
    	return array(
    		array("Group/A",
    		      array("U1" => true,
    		            "U2" => true,
    		            "U3" => true,
    		            "U4" => true,
    		            "U5" => false,
    		      )),
    		array("Group/B",
    		      array("U1" => true,
    		            "U2" => false,
    		            "U3" => false,
    		            "U4" => true,
    		            "U5" => false,
    		      )),
    		array("Group/C",
    		      array("U1" => true,
    		            "U2" => true,
    		            "U3" => true,
    		            "U4" => true,
    		            "U5" => false,
    		      )),
    		array("Group/D",
    		      array("U1" => true,
    		            "U2" => true,
    		            "U3" => true,
    		            "U4" => true,
    		            "U5" => false,
    		      )),
    		array("Group/E",
    		      array("U1" => false,
    		            "U2" => false,
    		            "U3" => false,
    		            "U4" => true,
    		            "U5" => false,
    		      )),
    	);
    }
    /**
     * Tests if the group with the name $groupName has the members (users) given in
     * $memberSpec. Membership is evaluated recursively.
     * 
     * @param $groupName
     * 		Name of the group
     * @param array(string username => bool) $memberSpec
     * 		Expected members
     * 		
     * @dataProvider providerForGroupHasUser
     */
    public function testGroupHasUser($groupName, $memberSpec) {
    	$group = HACLGroup::newFromName($groupName);
    	
    	foreach ($memberSpec as $user => $expMember) {
    		$this->assertEquals($expMember, $group->hasUserMember($user, true), 
    			"Expected that user <$user> is "
    		    .($expMember ? "" : "not ")
    		    ."a member of group <$groupName>.");
    	}
    }
    
    /**
     * Data provider for test testGroupHasGroup
     */
    public function providerForGroupHasGroup() {
    	return array(
    		array("Group/A",
    		      array("Group/A" => true,
    		            "Group/B" => true,
    		            "Group/C" => true,
    		            "Group/D" => true,
    		            "Group/E" => true,
    		      )),
    		array("Group/B",
    		      array("Group/A" => false,
    		            "Group/B" => false,
    		            "Group/C" => false,
    		            "Group/D" => false,
    		            "Group/E" => true,
    		      )),
       		array("Group/C",
    		      array("Group/A" => true,
    		            "Group/B" => true,
    		            "Group/C" => true,
    		            "Group/D" => true,
    		            "Group/E" => true,
    		      )),
    		array("Group/D",
    		      array("Group/A" => true,
    		            "Group/B" => true,
    		            "Group/C" => true,
    		            "Group/D" => true,
    		            "Group/E" => true,
    		      )),
    		array("Group/E",
    		      array("Group/A" => false,
    		            "Group/B" => false,
    		            "Group/C" => false,
    		            "Group/D" => false,
    		            "Group/E" => false,
    		      )),
    		);
    }
    
    /**
     * Tests if the group with the name $groupName has the members (groups) given in
     * $memberSpec. Membership is evaluated recursively.
     * 
     * @param $groupName
     * 		Name of the group
     * @param array(string groupname => bool) $memberSpec
     * 		Expected members
     * 		
     * @dataProvider providerForGroupHasGroup
     */
    public function testGroupHasGroup($groupName, $memberSpec) {
    	$group = HACLGroup::newFromName($groupName);
    	
    	foreach ($memberSpec as $g => $expMember) {
    		$this->assertEquals($expMember, $group->hasGroupMember($g, true), 
    			"Expected that group <$g> is "
    		    .($expMember ? "" : "not ")
    		    ."a member of group <$groupName>.");
    	}
    }
    
    /**
     * Data provider for testDynamicAssigneeRightsUserCan
     */
    function providerForDynamicAssigneeRightsUserCan() {
    	// $action, $grantedForUsers, $deniedForUsers
    	return array(
    		array("read", 
    		      array("U1", "U2", "U3", "U4"),
    		      array("U5")),
    	);
    }
    
    
    /**
     * Checks if the rights with assigned dynamic group members are evaluated
     * correctly
     * 
     * @param string $action
     * 		The action to test
     * @param array<string> $grantedForUsers
     * 		List of users with granted access
     * @param unknown_type $deniedForUsers
     * 		List of users with denied access
     * 
     * @dataProvider providerForDynamicAssigneeRightsUserCan
     */
    public function testDynamicMemberRightsUserCan($action, $grantedForUsers, $deniedForUsers) {
    	$test = array(array("Protected", null, $action, null));
    	
    	// Check the granted rights for users
    	foreach ($grantedForUsers as $u) {
    		$test[0][1] = $u;
    		$test[0][3] = true;
     		HaloACLCommon::checkRights($this, 'testDynamicMemberRightsUserCan-granted', $test);
    	}
    	
    	// Check the denied rights for users
    	foreach ($deniedForUsers as $u) {
    		$test[0][1] = $u;
    		$test[0][3] = false;
     		HaloACLCommon::checkRights($this, 'testDynamicMemberRightsUserCan-denied', $test);
    	}
    	
    }
    
}

/**
 * This class tests a complex example with dynamic group members and dynamic
 * assignees.
 * 
 * 
 * It assumes that the HaloACL extension is enabled in LocalSettings.php and that 
 * the triples store is running.
 * 
 * @author thsc
 *
 */
class TestMembersAssigneesExample extends PHPUnit_Framework_TestCase {


	private static $mArticles = array(
//------------------------------------------------------------------------------		
			'Property:HasProjectManager' =>
<<<ACL
This is the property for managers of a project.

[[has type::Type:Page| ]]
[[has domain and range::Category:Project; Category:Person| ]]

ACL
,	
//------------------------------------------------------------------------------		
			'Property:HasProjectMember' =>
<<<ACL
This is the property for members of a project.

[[has type::Type:Page| ]]
[[has domain and range::Category:Project; Category:Person| ]]
[[has domain and range::Category:Project; Category:Group| ]]

ACL
,
//------------------------------------------------------------------------------		
			'Property:WorksFor' =>
<<<ACL
This is the property for persons who work for something e.g. a project.

[[has type::Type:Page| ]]
[[has domain and range::Category:Person; | ]]
[[has domain and range::Category:Person; | ]]

ACL
,
//------------------------------------------------------------------------------		
			'User:Jane' =>
<<<ACL
This is the page of user Jane.

[[WorksFor::ProjectA]]

[[Category:Person]]
ACL
,
//------------------------------------------------------------------------------		
			'User:John' =>
<<<ACL
This is the page of user John.

[[WorksFor::ProjectA]]

[[Category:Person]]
ACL
,
//------------------------------------------------------------------------------		
			'User:Peter' =>
<<<ACL
This is the page of user Peter.

[[WorksFor::ProjectA]]

[[Category:Person]]
ACL
,
//------------------------------------------------------------------------------		
			'User:Manolo' =>
<<<ACL
This is the page of user Manolo.

[[Category:Dog]]
ACL
,
//------------------------------------------------------------------------------		
			'ProjectA' =>
<<<ACL
This is the page for Project A

[[ProjectManager::User:Jane]]
[[HasProjectMember::ACL:Group/MembersOfProjectA]]

[[Category:Project]]
ACL
,
//------------------------------------------------------------------------------		
			'ACL:Group/MembersOfProjectA' =>
<<<ACL
{{#member: 
| members={{#sparql: SELECT ?p WHERE { ?p property:WorksFor a:ProjectA .} |?p # =}}
}}

{{#manage group: assigned to=User:Jane}}
[[Category:ACL/Group]]

ACL
,
//------------------------------------------------------------------------------		
			'ACL:Page/ProjectA' =>
<<<ACL
{{#access: 
 |assigned to={{#ask: [[ProjectA]][[ProjectManager::+]]|?projectManager # =}}
 |actions=read,edit,formedit,wysiwyg,create,move,delete,annotate
 |description=Full access for project manager
 |name=FA
}}

{{#access: 
 |assigned to={{#sparql: SELECT ?m WHERE { a:ProjectA property:HasProjectMember ?m .} |?m # =}}
 |actions=edit
 |description=Edit right for project members
 |name=Edit
}}

{{#manage rights: assigned to=User:Jane}}
[[Category:ACL/Right]]

ACL
,	
	);
	
	private static $mArticleManager;
	protected $backupGlobals = FALSE;

	public static function setUpBeforeClass() {
    	// reset group permissions
    	global $wgGroupPermissions;
    	foreach ($wgGroupPermissions as $group => $permissions) {
    		foreach ($permissions as $p => $value) {
    			$wgGroupPermissions[$group][$p] = true;
    		}
    	}
    	
    	HACLStorage::reset(HACL_STORE_SQL);
    	
    	HaloACLCommon::createUsers(array("Peter", "John", "Jane", "Manolo"));
        
        self::$mArticleManager = new ArticleManager();
        self::$mArticleManager->createArticles(self::$mArticles, "Jane");
    }

	public static function tearDownAfterClass() {
       	self::$mArticleManager->deleteArticles("Jane");
    }

    /**
     * Data provider for testExampleRights
     */
    function providerForExampleRights() {
    	// $action, $grantedForUsers, $deniedForUsers
    	return array(
    		array("read", 
    		      array("Jane", "John", "Peter"),
    		      array("Manolo")),
    		array("edit", 
    		      array("Jane", "John", "Peter"),
    		      array("Manolo")),
    		array("delete", 
    		      array("Jane"),
    		      array("Manolo", "John", "Peter")),
    	);
    }
    
    
    /**
     * Checks if the rights with assigned dynamic group members are evaluated
     * correctly.
     * 
     * @param string $action
     * 		The action to test
     * @param array<string> $grantedForUsers
     * 		List of users with granted access
     * @param unknown_type $deniedForUsers
     * 		List of users with denied access
     * 
     * @dataProvider providerForExampleRights
     */ 
    public function testExampleRights($action, $grantedForUsers, $deniedForUsers) {
    	$test = array(array("ProjectA", null, $action, null));
    	
    	// Check the granted rights for users
    	foreach ($grantedForUsers as $u) {
    		$test[0][1] = $u;
    		$test[0][3] = true;
     		HaloACLCommon::checkRights($this, 'testExampleRights-granted', $test);
    	}
    	
    	// Check the denied rights for users
    	foreach ($deniedForUsers as $u) {
    		$test[0][1] = $u;
    		$test[0][3] = false;
     		HaloACLCommon::checkRights($this, 'testExampleRights-denied', $test);
    	}
    	
    }
    
}


/**
 * This class tests that dynamic group members and dynamic assignees are shown
 * in the rendered view of security descriptors or group articles.
 * 
 * 
 * It assumes that the HaloACL extension is enabled in LocalSettings.php and that 
 * the triples store is running.
 * 
 * @author thsc
 *
 */
class TestShowDynamicMembers extends PHPUnit_Framework_TestCase {


	private static $mArticles = array(
//------------------------------------------------------------------------------		
			'Property:HasProjectManager' =>
<<<ACL
This is the property for managers of a project.

[[has type::Type:Page| ]]
[[has domain and range::Category:Project; Category:Person| ]]

ACL
,	
//------------------------------------------------------------------------------		
			'Property:HasProjectMember' =>
<<<ACL
This is the property for members of a project.

[[has type::Type:Page| ]]
[[has domain and range::Category:Project; Category:Person| ]]
[[has domain and range::Category:Project; Category:Group| ]]

ACL
,
//------------------------------------------------------------------------------		
			'Property:WorksFor' =>
<<<ACL
This is the property for persons who work for something e.g. a project.

[[has type::Type:Page| ]]
[[has domain and range::Category:Person; | ]]
[[has domain and range::Category:Person; | ]]

ACL
,
//------------------------------------------------------------------------------		
			'User:Jane' =>
<<<ACL
This is the page of user Jane.

[[WorksFor::ProjectA]]

[[Category:Person]]
ACL
,
//------------------------------------------------------------------------------		
			'User:John' =>
<<<ACL
This is the page of user John.

[[WorksFor::ProjectA]]

[[Category:Person]]
ACL
,
//------------------------------------------------------------------------------		
			'User:Peter' =>
<<<ACL
This is the page of user Peter.

[[WorksFor::ProjectA]]

[[Category:Person]]
ACL
,
//------------------------------------------------------------------------------		
			'User:Manolo' =>
<<<ACL
This is the page of user Manolo.

[[Category:Dog]]
ACL
,
//------------------------------------------------------------------------------		
			'ProjectA' =>
<<<ACL
This is the page for Project A

[[ProjectManager::User:Jane]]
[[HasProjectMember::ACL:Group/MembersOfProjectA]]

[[Category:Project]]
ACL
,
//------------------------------------------------------------------------------		
			'ACL:Group/MembersOfProjectA' =>
<<<ACL
{{#member: 
| members={{#ask: [[ProjectA]][[ProjectManager::+]]|?projectManager # =}},
User:John
}}

{{#member: 
| members={{#sparql: SELECT ?pm WHERE { a:ProjectA property:HasProjectMember ?pm .} |?pm # =}}
}}

{{#member: 
| members={{#sparql: SELECT ?p WHERE { ?p property:WorksFor a:ProjectA .} |?p # =}}
}}

{{#manage group: assigned to=User:Jane}}
[[Category:ACL/Group]]

ACL
,
//------------------------------------------------------------------------------		
			'ACL:Page/ProjectA' =>
<<<ACL
{{#access: 
 |assigned to={{#ask: [[ProjectA]][[ProjectManager::+]]|?projectManager # =}},
  {{#sparql: SELECT ?m WHERE { a:ProjectA property:HasProjectMember ?m .} |?m # =}},
  User:John
 |actions=read,edit,formedit,wysiwyg,create,move,delete,annotate
 |description=Full access for project manager
 |name=FA
}}

{{#access: 
 |assigned to={{#sparql: SELECT ?m WHERE { a:ProjectA property:HasProjectMember ?m .} |?m # =}},
 {{#ask: [[ProjectA]][[ProjectManager::+]]|?projectManager # =}}, Group/MembersOfProjectA
 |actions=edit
 |description=Edit right for project members
 |name=Edit
}}

{{#manage rights: assigned to=User:Jane}}
[[Category:ACL/Right]]

ACL
,	
	);
	
	private static $mArticleManager;
	protected $backupGlobals = FALSE;

	public static function setUpBeforeClass() {
    	// reset group permissions
    	global $wgGroupPermissions;
    	foreach ($wgGroupPermissions as $group => $permissions) {
    		foreach ($permissions as $p => $value) {
    			$wgGroupPermissions[$group][$p] = true;
    		}
    	}
    	
    	HACLStorage::reset(HACL_STORE_SQL);
    	
    	HaloACLCommon::createUsers(array("Peter", "John", "Jane", "Manolo"));
        
        self::$mArticleManager = new ArticleManager();
        self::$mArticleManager->createArticles(self::$mArticles, "Jane");
    }

	public static function tearDownAfterClass() {
       	self::$mArticleManager->deleteArticles("Jane");
    }

    /**
     * Data provider for testShowDynamicMembers
     */
    function providerForShowDynamicMembers() {
    	// $articleName, $expectedMembers
    	return array(
    		array('ACL:Group/MembersOfProjectA', 
    		      array(
	<<<EXP
Group members
    Users who are member of this group
        John 
    Dynamic members
        Queries for dynamic members
            {{#ask: [[ProjectA]][[ProjectManager::+]]|?projectManager # =}} 
        Dynamic user members
            Jane 
EXP
,
	<<<EXP
Group members
    Dynamic members
        Queries for dynamic members
            {{#sparql: SELECT ?pm WHERE { a:ProjectA property:HasProjectMember ?pm .} |?pm # =}} 
        Dynamic group members
            Group/MembersOfProjectA 
EXP
,
	<<<EXP
Group members
    Dynamic members
        Queries for dynamic members
            {{#sparql: SELECT ?p WHERE { ?p property:WorksFor a:ProjectA .} |?p # =}} 
        Dynamic user members
            John
            Peter
            Jane 
EXP
    		      )),
    		array("ACL:Page/ProjectA", 
    		      array(
<<<EXP
FA
    Right(s)
        read ,edit ,formedit ,wysiwyg ,create ,move ,delete ,annotate 
    Assigned users
        John 
    Dynamic assignees
        Queries for dynamic assignees
             {{#ask: [[ProjectA]][[ProjectManager::+]]|?projectManager # =}}
             {{#sparql: SELECT ?m WHERE { a:ProjectA property:HasProjectMember ?m .} |?m # =}} 
        Dynamically assigned users
             Jane 
        Dynamically assigned groups
             Group/MembersOfProjectA 
    Description
        Full access for project manager 
EXP
,
	<<<EXP
Edit
    Right(s)
        edit 
    Assigned groups
        Group/MembersOfProjectA 
    Dynamic assignees
        Queries for dynamic assignees
             {{#sparql: SELECT ?m WHERE { a:ProjectA property:HasProjectMember ?m .} |?m # =}}
             {{#ask: [[ProjectA]][[ProjectManager::+]]|?projectManager # =}} 
        Dynamically assigned users
             Jane 
        Dynamically assigned groups
             Group/MembersOfProjectA 
    Description
        Edit right for project members 
EXP

			)),
    	);
    }
    
    
    /**
     * Checks if the dynamic members and assignees are correctly displayed in
     * the given article.
     * 
     * @param string $articleName
     * 		The article to check
     * @param array<string> $expected
     * 		Expected strings
     * 
     * @dataProvider providerForShowDynamicMembers
     */ 
    public function testShowDynamicMembersTest($articleName, $expected) {
    	$t = Title::newFromText($articleName);
    	$article = new Article($t);
    	global $wgOut;
    	$wgOut = new OutputPage();
    	$article->view();
    	$html = $wgOut->getHTML();
    	
    	// remove all HTML tags, whitespaces and &nbsp; from generated HTML
    	$html  = preg_replace("/<\/*.*?>|\s*|&nbsp;/", "", $html);
    	
    	foreach ($expected as $exp) {
    		$exp = preg_replace("/\s*/", "", $exp);
    		$this->assertContains($exp, $html);
    	}
    	
    }
    
}
