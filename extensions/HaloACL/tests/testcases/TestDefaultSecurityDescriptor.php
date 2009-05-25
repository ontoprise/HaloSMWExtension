<?php
require_once 'PHPUnit/Framework.php';

class TestDefaultSecurityDescriptorSuite extends PHPUnit_Framework_TestSuite
{
	public static function suite() {
		return new TestDefaultSecurityDescriptorSuite('TestDefaultSecurityDescriptor');
	}
	
	protected function setUp() {
    	User::createNew("U1");
    	User::createNew("U2");
        User::idFromName("U1");  
        User::idFromName("U2");  
        
   		global $wgUser;
    	$wgUser = User::newFromName("U1");
    	
        $this->initArticleContent();
    	$this->createArticles();
	}
	
	protected function tearDown() {
        $this->removeArticles();
	}

	public static function createArticle($title, $content) {
	
		$title = Title::newFromText($title);
		$article = new Article($title);
		// Set the article's content
		
		$success = $article->doEdit($content, 'Created for test case', 
		                            $article->exists() ? EDIT_UPDATE : EDIT_NEW);
		if (!$success) {
			echo "Creating article ".$title->getFullText()." failed\n";
		}
	}
    
	private function initArticleContent() {
		$this->mOrderOfArticleCreation = array(
			'Category:ACL/Group',
			'Category:ACL/Right',
			'Category:ACL/ACL',
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
		);
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
			$this->assertTrue(false, "Unexpected exception while testing ".basename($file)."::createArticles():".$e->getMessage());
		}
    	
    }
 
    
	private function removeArticles() {
		
		$articles = array(
			'Category:ACL/Group',
			'Category:ACL/Right',
			'Category:ACL/ACL',
			'Public',
			'Private',
			'ACL:Page/Private',
			'ACL:Template/U1'			
		);
		
		foreach ($articles as $a) {
		    $t = Title::newFromText($a);
	    	$article = new Article($t);
			$article->doDelete("Testing");
		}
		
	}
	
	
}

class TestDefaultSecurityDescriptor extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = FALSE;
	
    function setUp() {
    }

    function tearDown() {
    }

    function testDefaultSecDescr() {
    	$file = __FILE__;
    	try {
    		global $haclgOpenWikiAccess;
    		
    		// User U1 creates a public article
    		TestDefaultSecurityDescriptorSuite::createArticle("Public", "This is a public article (if the wiki is generally open).");
    		$checkRights = array(
				array('Public', 'U1', 'edit', $haclgOpenWikiAccess),
				array('Public', 'U2', 'edit', $haclgOpenWikiAccess),
			);
			$this->doCheckRights("testDefaultSecurityDescriptor_1", $checkRights);
    		
    		// User U1 creates a default security descriptor
    		HACLParserFunctions::getInstance()->reset();
    		TestDefaultSecurityDescriptorSuite::createArticle("ACL:Template/U1", 
<<<ACL
{{#manage rights: assigned to=User:U1}}

{{#access:
 assigned to =User:U1
|actions=*
|description=Allows * for U1
}}

[[Category:ACL/ACL]]
ACL
    		);
			
    		// User U1 creates an article that is now protected by the default 
    		// security descriptor
    		HACLParserFunctions::getInstance()->reset();
    		TestDefaultSecurityDescriptorSuite::createArticle("Private", "This is a private article for U1.");
    		$checkRights = array(
				array('Private', 'U1', 'edit', true),
				array('Private', 'U2', 'edit', false),
			);
			$this->doCheckRights("testDefaultSecurityDescriptor_2", $checkRights);
    		
    		// Check access to the default security descriptor
    		$checkRights = array(
				array('ACL:Template/U1', 'U1', 'edit', true),
				array('ACL:Template/U1', 'U2', 'read', false),
				array('ACL:Template/U1', 'WikiSysop', 'edit', true),
				array('ACL:Template/U2', 'U1', 'create', false),
				);
			$this->doCheckRights("testDefaultSecurityDescriptor_3", $checkRights);
		} catch (Exception $e) {
			$this->assertTrue(false, "Unexpected exception while testing ".basename($file)."::testDefaultSecurityDescriptor():".$e->getMessage());
		}
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
			
			$user = $user == '*' ? new User() : User::newFromName($user);
			unset($result);
			HACLEvaluator::userCan($article, $user, $action, $result);
			
			$this->assertEquals($res, $result, "Test of rights failed for: $article, $username, $action (Testcase: $testcase)\n");
			
		}
	}
	
}
?>