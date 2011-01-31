<?php 
/**
 * This suite tests the features of "Dynamic HaloACL". The protection of HaloACL
 * is controlled by the content of articles.
 * 
 * @author thsc
 *
 */
class TestDynamicHaloACLSuite extends PHPUnit_Framework_TestSuite
{
	
	public static function suite() {
		define('UNIT_TEST_RUNNING', true);
		
		$suite = new TestDynamicHaloACLSuite();
		$suite->addTestSuite('TestDynamicSD');
		$suite->addTestSuite('TestDynamicGroup');
		$suite->addTestSuite('TestDynamicSDWithGroup');
		$suite->addTestSuite('TestDynamicGroupStructure');
		return $suite;
	}
	
	protected function setUp() {
    	HACLStorage::reset(HACL_STORE_SQL);
		HACLStorage::getDatabase()->dropDatabaseTables(false);
		HACLStorage::getDatabase()->initDatabaseTables(false);
		
		User::createNew("U1");
    	User::createNew("U2");
        User::idFromName("U1");  
        User::idFromName("U2");  
        Skin::getSkinNames();
        
        
   		global $wgUser;
    	$wgUser = User::newFromName("U1");
    	
        $this->initArticleContent();
    	$this->createArticles();
	}
	
	protected function tearDown() {
        $this->removeArticles();

        HACLStorage::getDatabase()->dropDatabaseTables(false);
		HACLStorage::getDatabase()->initDatabaseTables(false);
        
	}

	public static function createArticle($title, $content) {
	
		HACLParserFunctions::getInstance()->reset();
		
		$title = Title::newFromText($title);
		$article = new Article($title);
		// Set the article's content
		
		$success = $article->doEdit($content, 'Created for test case', 
		                            $article->exists() ? EDIT_UPDATE : EDIT_NEW);
		if (!$success) {
			echo "Creating article ".$title->getFullText()." failed\n";
		}
	}
	
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
    
	
    /**
     * Checks the rights of a resource for the given $testcase.
     * 
     * @param string $testcase
     * 		The name of the test case is printed if the test fails. 
     * @param $expectedResults
     * @param $testInstance
     * 		The instance of a test class that performs the test.
     * 		
     */
	public static function doCheckRights($testcase, $expectedResults, $testInstance) {
		foreach ($expectedResults as $er) {
			$articleName = $er[0];
			$user = $username = $er[1];
			$action = $er[2];
			$res = $er[3];
			
			$etc = haclfDisableTitlePatch();			
			$article = Title::newFromText($articleName);
			haclfRestoreTitlePatch($etc);			
			
			$user = $user == '*' ? new User() : User::newFromName($user);
			unset($result);
			HACLEvaluator::userCan($article, $user, $action, $result);
			if (is_null($result)) {
				$result = true;
			}
			
			$testInstance->assertEquals($res, $result, "Test of rights failed for: $article, $username, $action (Testcase: $testcase)\n");
			
		}
	}
	
	private function initArticleContent() {
		$this->mOrderOfArticleCreation = array(
			'Category:ACL/Group',
			'Category:ACL/Right',
			'Category:ACL/ACL',
			'ACL:Right/SDForNewProject',
			'ACL:Right/SDForNewRequest',
			'ACL:Right/DynamicSD',
			'ACL:Group/ProjectGroupTemplate',
			'ACL:Group/RequestGroupTemplate',
			'ACL:Right/SDWithGroup',
			'ACL:Group/DynamicGroupManagers'
		);
		
		$this->mArticles = array(
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
    	global $wgUser;
    	$wgUser = User::newFromName("U1");
    	
    	$file = __FILE__;
    	foreach ($this->mOrderOfArticleCreation as $title) {
    		$pf = HACLParserFunctions::getInstance();
    		$pf->reset();
			self::createArticle($title, $this->mArticles[$title]);
    	}
    	
    }
 
    
	private function removeArticles() {
		
		$articles = $this->mOrderOfArticleCreation;
		
		foreach ($articles as $a) {
		    $t = Title::newFromText($a);
	    	$article = new Article($t);
			$article->doDeleteArticle("Testing");
		}
		
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
	private $mAddedArticles = array();
	
    function setUp() {
    }

    /**
     * Delete all articles that were created during a test.
     */
    function tearDown() {
   		global $wgUser, $wgOut;
    	$wgUser = User::newFromName("U1");
    	
		foreach ($this->mAddedArticles as $a) {
		    $t = Title::newFromText($a);
		    $wgOut->setTitle($t); // otherwise doDelete() will throw an exception
	    	$article = new Article($t);
			$article->doDelete("Testing");
		}
    }

    /**
     * Creates a normal article and checks that no security descriptor is created.
     */
    function testCreateNormalArticle() {

    	// User U1 creates a public article
    	TestDynamicHaloACLSuite::createArticle("Normal", "This is a public article.");
    	$this->mAddedArticles[] = "Normal";
    	
    	$this->assertFalse(TestDynamicHaloACLSuite::articleExists("ACL:Page/Normal"));
    }
    
    /**
     * Data provider for testCreateDynamicCategorySD
     */
    function providerForCreateDynamicCategorySD() {
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

    	global $wgUser;
    	$wgUser = User::newFromName($userName);
    	
   		$catAnno = is_null($category) ? '' : "[[Category:$category]]";
    	$content = "This is an article. $catAnno";
    	
    	TestDynamicHaloACLSuite::createArticle($article, $content);
    	$this->mAddedArticles[] = $article;
    	$this->mAddedArticles[] = "ACL:Page/$article";
    	
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
		TestDynamicHaloACLSuite::doCheckRights("testCreateDynamicCategorySD", $checkRights, $this);
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
    	global $wgUser;
    	// Create article as user 1
    	$wgUser = User::newFromName($user1);
    	TestDynamicHaloACLSuite::createArticle("ProjectA", $content);
    	$this->mAddedArticles[] = "ProjectA";
    	$this->mAddedArticles[] = "ACL:Page/ProjectA";
   		// expected that an SD is created
    	$this->assertTrue(TestDynamicHaloACLSuite::articleExists("ACL:Page/ProjectA"));
    	
    	// Create article as user 2
    	$wgUser = User::newFromName($user2);
    	try {
    		TestDynamicHaloACLSuite::createArticle("ProjectA", $content);
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
    	$wgUser = User::newFromName("U1");
    	
    	try {
    		TestDynamicHaloACLSuite::createArticle("ProjectA", $content);
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
    	$wgUser = User::newFromName("U1");
    	TestDynamicHaloACLSuite::createArticle("ProjectA", $content);
		TestDynamicHaloACLSuite::doCheckRights("testSequenceofSDs_1", $checkRightsU1, $this);
		
		// Make "ProjectA" accessible for U2
    	$wgUser = User::newFromName("U2");
    	TestDynamicHaloACLSuite::createArticle("ProjectA", $content);
		TestDynamicHaloACLSuite::doCheckRights("testSequenceofSDs_2", $checkRightsU2, $this);
		
		// Make "ProjectA" accessible for U1
    	$wgUser = User::newFromName("U1");
    	TestDynamicHaloACLSuite::createArticle("ProjectA", $content);
		TestDynamicHaloACLSuite::doCheckRights("testSequenceofSDs_3", $checkRightsU1, $this);
		
		// Make "ProjectA" accessible for U2
    	$wgUser = User::newFromName("U2");
    	TestDynamicHaloACLSuite::createArticle("ProjectA", $content);
		TestDynamicHaloACLSuite::doCheckRights("testSequenceofSDs_2", $checkRightsU2, $this);
				
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
	
	// List of articles that were added during a test.
	private $mAddedArticles = array();
	
    function setUp() {
    }

    /**
     * Delete all articles that were created during a test.
     */
    function tearDown() {
		
   		global $wgUser, $wgOut;
    	$wgUser = User::newFromName("U1");
    	
		foreach ($this->mAddedArticles as $a) {
		    $t = Title::newFromText($a);
		    $wgOut->setTitle($t); // otherwise doDelete() will throw an exception
	    	$article = new Article($t);
			$article->doDelete("Testing");
		}
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
    	TestDynamicHaloACLSuite::createArticle("Normal", "This is a normal article.");
    	$this->mAddedArticles[] = "Normal";
    	
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
    	
    	global $wgUser;
    	$wgUser = User::newFromName($userName);
    	
   		$catAnno = is_null($category) ? '' : "[[Category:$category]]";
    	$content = "This is an article. $catAnno";
    	$groupName = "ACL:Group/MembersOf$article";
    	
    	TestDynamicHaloACLSuite::createArticle($article, $content);
    	$this->mAddedArticles[] = $article;
    	$this->mAddedArticles[] = $groupName;
    	
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
    		            "name" => "MemberOf{{{articleName}}}"), false),
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
    	$wgUser = User::newFromName("U1");
    	
    	try {
    		$this->mAddedArticles[] = "ACL:Page/ProjectA";
    		TestDynamicHaloACLSuite::createArticle("ProjectA", $content);
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
	
	// List of articles that were added during a test.
	private $mAddedArticles = array();
	
    function setUp() {
    }

    /**
     * Delete all articles that were created during a test.
     */
    function tearDown() {
   		global $wgUser, $wgOut;
    	$wgUser = User::newFromName("U1");
    	
		foreach ($this->mAddedArticles as $a) {
		    $t = Title::newFromText($a);
		    $wgOut->setTitle($t); // otherwise doDelete() will throw an exception
	    	$article = new Article($t);
			$article->doDelete("Testing");
		}
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
	    
    	global $wgUser;
    	$wgUser = User::newFromName('U1');
    	
    	$article = "My Project";
    	$content = "This is an article. [[Category:Project]]";
    	$groupName = "ACL:Group/GroupFor$article";
    	$sdName = "ACL:Page/$article";
    	
    	TestDynamicHaloACLSuite::createArticle($article, $content);
    	$this->mAddedArticles[] = $article;
    	$this->mAddedArticles[] = $groupName;
    	$this->mAddedArticles[] = $sdName;

    	// Verify that group and SD were created according to the rule
    	$this->assertTrue(TestDynamicHaloACLSuite::articleExists($groupName));
    	$this->assertTrue(TestDynamicHaloACLSuite::articleExists($sdName));
    	
    	// Verify the access restrictions work i.e. that the member of the new
    	// group has access
		$checkRights = array(
			array($article, 'U1', 'edit', true),
			array($article, 'U2', 'edit', false),
			);
		
		TestDynamicHaloACLSuite::doCheckRights("testCreateSDWithGroup", $checkRights, $this);
    	
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
	
	// List of articles that were added during a test.
	private $mAddedArticles = array();
	
    function setUp() {
    	LinkCache::singleton()->clear();
    }

    /**
     * Delete all articles that were created during a test.
     */
    function tearDown() {
   		global $wgUser, $wgOut;
    	$wgUser = User::newFromName("U1");
    	HACLGroup::setAllowUnauthorizedGroupChange(true);
		foreach ($this->mAddedArticles as $a) {
		    $t = Title::newFromText($a);
		    $wgOut->setTitle($t); // otherwise doDelete() will throw an exception
	    	$article = new Article($t);
			$article->doDelete("Testing");
		}
    	HACLGroup::setAllowUnauthorizedGroupChange(false);
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
	    
    	global $wgUser;
    	$wgUser = User::newFromName('U1');
    	
    	$content = "This is an article. [[Category:$category]]";
    	$parentGroup .= "$category";
    	
    	TestDynamicHaloACLSuite::createArticle($article, $content);
    	$this->mAddedArticles[] = $article;
    	$this->mAddedArticles[] = "ACL:Group/".$grandparentGroup;
    	$this->mAddedArticles[] = "ACL:Group/".$parentGroup;
    	$this->mAddedArticles[] = "ACL:Group/".$group;

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
	    
    	global $wgUser;
    	$wgUser = User::newFromName('U1');
    	
    	// Create two projects and two requests
    	TestDynamicHaloACLSuite::createArticle("ProjectA", "This is an article. [[Category:Project]]");
    	TestDynamicHaloACLSuite::createArticle("ProjectB", "This is an article. [[Category:Project]]");
    	TestDynamicHaloACLSuite::createArticle("RequestA", "This is an article. [[Category:Request]]");
    	TestDynamicHaloACLSuite::createArticle("RequestB", "This is an article. [[Category:Request]]");
    	$this->mAddedArticles[] = "ProjectA";
    	$this->mAddedArticles[] = "ProjectB";
    	$this->mAddedArticles[] = "RequestA";
    	$this->mAddedArticles[] = "RequestB";
    	$this->mAddedArticles[] = "ACL:Group/".$haclgDynamicRootGroup;
    	$this->mAddedArticles[] = "ACL:Group/{$haclgDynamicCategoryGroup}Project";
    	$this->mAddedArticles[] = "ACL:Group/{$haclgDynamicCategoryGroup}Request";
    	$this->mAddedArticles[] = "ACL:Group/GroupForProjectA";
    	$this->mAddedArticles[] = "ACL:Group/GroupForProjectB";
    	$this->mAddedArticles[] = "ACL:Group/GroupForRequestA";
    	$this->mAddedArticles[] = "ACL:Group/GroupForRequestB";
    	

    	// Verify that all articles exist
    	foreach ($this->mAddedArticles as $a) {
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

