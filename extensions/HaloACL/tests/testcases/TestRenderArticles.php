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
 * This suite tests rendering articles and verifies that they are correctly
 * protected by HaloACL
 * 
 * @author thsc
 *
 */
class TestRenderArticlesSuite extends PHPUnit_Framework_TestSuite
{
	
	public static function suite() {
		if (!defined('UNIT_TEST_RUNNING')) {
			define('UNIT_TEST_RUNNING', true);
		}
				
		$suite = new TestRenderArticlesSuite();
		$suite->addTestSuite('TestTransclusion');
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
			'IncludedPage',
			'IncludingPage',
			'ACL:Page/IncludedPage'
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
			'ACL:Page/IncludedPage' =>
<<<ACL
{{#access: assigned to=User:U1
 |actions=read,edit,formedit,wysiwyg,create,move,delete,annotate
 |description=fullaccess for U:U1
 |name=Right}}

{{#manage rights: assigned to=User:U1}}

[[Category:ACL/ACL]]

ACL
,
//------------------------------------------------------------------------------		
			'IncludedPage' =>
<<<ACL
This page is included.

ACL
,
//------------------------------------------------------------------------------		
			'IncludingPage' =>
<<<ACL
This page includes IncludedPage:
{{:IncludedPage}}

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
 * This class tests if protected transcluded pages are correctly hidden.
 * 
 * It assumes that the HaloACL extension is enabled in LocalSettings.php
 * 
 * @author thsc
 *
 */
class TestTransclusion extends PHPUnit_Framework_TestCase {

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
     * Data provider for testTransclusionProtected
     */
    function providerTransclusionProtected() {
    	return array(
	    	// $user, $articleName, $contentVisible
    		array('U1', 'IncludingPage', true),
    		array('U2', 'IncludingPage', false),
    	);
    }

    /**
     * Checks if a protected article that is transcluded into another article
     * is protected correctly.
     * 
     * @dataProvider providerTransclusionProtected
     */
    function testTransclusionProtected($user, $articleName, $contentVisible) {

    	global $wgUser;
    	
    	$wgUser = User::newFromName($user);
    	
    	$t = Title::newFromText($articleName);
    	$article = new Article($t);
    	global $wgOut;
    	$wgOut = new OutputPage();
    	$article->view();
    	$html = $wgOut->getHTML();
    	
    	$expectedMsg = $contentVisible 
    					? "This page is included."
    					: ">IncludedPage<";
    	$pos = strpos($html, $expectedMsg);
    	$this->assertTrue($pos !== false, "Protection for transclusion failed.");
    }
    
}
