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
		$suite->addTestSuite('TestMWSpecialPages');
 		$suite->addTestSuite('TestQueryPages');
		return $suite;
	}
	
	protected function setUp() {
    	HACLStorage::reset(HACL_STORE_SQL);
		HACLStorage::getDatabase()->dropDatabaseTables(false);
		HACLStorage::getDatabase()->initDatabaseTables(false);
		
		HaloACLCommon::createUsers(array("U1", "U2"));
        
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
			'Category:Super',
			'Category:Sub',
			'Category:AnotherSub',
			'ArticleInSub',
			'ACL:Category/Super',
			'IncludedPage',
			'IncludingPage',
			'ACL:Page/IncludedPage',
			'Category:Person',
			'Daniel',
			'Manolo',
			'SimpleQueryWiki',
			'SimpleQueryTSC',
			'ComplexQueryWiki',
			'ComplexQueryTSC',
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
			'Category:Super' =>
<<<ACL
This is a super category.
ACL
,
//------------------------------------------------------------------------------		
			'Category:Sub' =>
<<<ACL
This is a sub category.
[[Category:Super]]
ACL
,
//------------------------------------------------------------------------------		
			'Category:AnotherSub' =>
<<<ACL
This is a another sub category.
[[Category:Super]]
ACL
,
//------------------------------------------------------------------------------		
			'ArticleInSub' =>
<<<ACL
This is an article in sub category.
[[Category:Sub]]
ACL
,
//------------------------------------------------------------------------------		
			'ACL:Category/Super' =>
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
//------------------------------------------------------------------------------		
			'Category:Person' =>
<<<ACL
This is the category for persons. 
ACL
,
//------------------------------------------------------------------------------		
			'Daniel' =>
<<<ACL
Daniel [[owns::Manolo]]. 
[[Category:Person]]
ACL
,
//------------------------------------------------------------------------------		
			'Manolo' =>
<<<ACL
Manolo is a dog.
ACL
,
//------------------------------------------------------------------------------		
			'SimpleQueryWiki' =>
<<<ACL
{{#ask: [[Category:Person]] |?owns|source=wiki}}
ACL
,
//------------------------------------------------------------------------------		
			'SimpleQueryTSC' =>
<<<ACL
{{#ask: [[Category:Person]] |?owns|source=tsc}}
ACL
,
//------------------------------------------------------------------------------		
			'ComplexQueryWiki' =>
<<<ACL
{{#ask: [[Category:Person]][[Owns::Manolo]] |?owns|source=wiki}}
ACL
,
//------------------------------------------------------------------------------		
			'ComplexQueryTSC' =>
<<<ACL
{{#ask: [[Category:Person]][[Owns::Manolo]] |?owns|source=tsc}}
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

/**
 * This class tests if Mediawiki's special pages for listing articles hide 
 * protected articles. Tests are applied to:
 * - Special:UnusedCategories
 * - Special:Categories
 * 
 * It assumes that the HaloACL extension is enabled in LocalSettings.php
 * 
 * @author thsc
 *
 */
class TestMWSpecialPages extends PHPUnit_Framework_TestCase {

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
     * Data provider for testSpecialPages
     */
    function providerSpecialPages() {
    	return array(
	    	// $user, $specialPage, $content, $expectedContentPresent
    		array('U1', 'Special:Categories', 'title="Category:Sub"', true),
    		array('U2', 'Special:Categories', 'title="Category:Sub"', false),
    		array('U1', 'Special:UnusedCategories', 'title="Category:AnotherSub"', true),
    		array('U2', 'Special:UnusedCategories', 'title="Category:AnotherSub"', false),
    	);
    }

    /**
     * Checks if a protected article appears on a special page
     * 
     * @dataProvider providerSpecialPages
     */
    function testSpecialPages($user, $specialPage, $content, $expectedContentPresent) {

    	global $wgUser;
    	
    	$wgUser = User::newFromName($user);
    	
    	global $wgOut;
    	$wgOut = new OutputPage();
    	$t = Title::newFromText($specialPage);
    	SpecialPage::executePath($t);
    	$html = $wgOut->getHTML();
    	
    	$pos = strpos($html, $content);
    	if ($expectedContentPresent) {
    		// We expect the $content to be present
    		$this->assertTrue($pos !== false, "The expected content was not found.");
    	} else {
    		// We expect the $content to be absent
    		$this->assertTrue($pos === false, "The content was found but supposed to be missing.");
    	}
    }
    
    
}
/**
 * This class tests if some pages with queries are rendered correctly.
 * 
 * It assumes that the HaloACL extension is enabled in LocalSettings.php and that
 * the triple store is available.
 * 
 * @author thsc
 *
 */
class TestQueryPages extends PHPUnit_Framework_TestCase {

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
     * Data provider for testRenderQueryPages
     */
    function providerRenderQueryPages() {
    	return array(
	    	// $user, $queryPage, array(array($contentRegExp, $numOccurrences),...)
#0 - Check that no columns are duplicated in the result of a simple ask query addressing the SMW store    	
    		array('U1', 'SimpleQueryWiki', array( 
    					array('>Owns<', 1),
    					array('<td>.*?>Daniel<.*?<\/td>', 1),
    					array('<td>.*?>Manolo<.*?<\/td>', 1),
    				)
    			  ),
#1 - Check that no columns are duplicated in the result of a complex ask query addressing the SMW store    	
    		array('U1', 'ComplexQueryWiki',  array( 
    					array('>Owns<', 1),
    					array('<td>.*?>Daniel<.*?<\/td>', 1),
    					array('<td>.*?>Manolo<.*?<\/td>', 1),
    				)),
#2 - Check that no columns are duplicated in the result of a simple ask query addressing the TSC   	
    		array('U1', 'SimpleQueryTSC',  array( 
    					array('>Owns<', 1),
    					array('<td>.*?>Daniel<.*?<\/td>', 1),
    					array('<td>.*?>Manolo<.*?<\/td>', 1),
    				)),
#3 - Check that no columns are duplicated in the result of a complex ask query addressing the TSC   	
    		array('U1', 'ComplexQueryTSC',  array( 
    					array('>Owns<', 1),
    					array('<td>.*?>Daniel<.*?<\/td>', 1),
    					array('<td>.*?>Manolo<.*?<\/td>', 1),
    				)),
    	);
    }

    /**
     * Checks if a query returns and renders the expected result.
     * 
     * @dataProvider providerRenderQueryPages
     */
    function testRenderQueryPages($user, $queryPage, $content) {

    	global $wgUser;
    	
    	$wgUser = User::newFromName($user);
    	
    	$t = Title::newFromText($queryPage);
    	$article = new Article($t);
    	global $wgOut;
    	$wgOut = new OutputPage();
    	$article->view();
    	$html = $wgOut->getHTML();
    	
    	foreach ($content as $contAndOcc) {
    		$contentRegExp = $contAndOcc[0];
    		$numOccurrences = $contAndOcc[1];
	    	$numFound = preg_match_all('/'.$contentRegExp.'/', $html, $matches);
	   		$this->assertEquals($numOccurrences, $numFound, "Expected $numOccurrences occurrence of:\n$contentRegExp\nFound $numFound");
    	}
    }
    
    
}
