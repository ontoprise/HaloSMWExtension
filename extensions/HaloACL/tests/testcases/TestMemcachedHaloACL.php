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
 * This suite tests the caching functions of HaloACL with memcached.
 * 
 * @author thsc
 *
 */
class TestMemcachedHaloACLSuite extends PHPUnit_Framework_TestSuite
{
	
	private $mArticleManager;
	private $mArticles;
	
	public static function suite() {
		if (!defined('UNIT_TEST_RUNNING')) {
			define('UNIT_TEST_RUNNING', true);
		}
				
		$suite = new TestMemcachedHaloACLSuite();
		$suite->addTestSuite('TestMemcacheSetup');
		$suite->addTestSuite('TestHACLMemcache');
		$suite->addTestSuite('TestAccessArticlesWithMemcachedHaloACL');
		$suite->addTestSuite('TestHACLMemcacheFillAndPurge');
		return $suite;
	}
	
	protected function setUp() {
		// Enable the HaloACL memcache
		HACLMemcache::getInstance()->enableMemcache(true);
		
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
	
    private function createArticles() {
        $this->mArticleManager = new ArticleManager();
        $this->mArticleManager->createACLBaseArticles("U1");
        $this->mArticleManager->createArticles($this->mArticles, "WikiSysop");
    }
 
    
	private function removeArticles() {
		$this->mArticleManager->deleteArticles("U1");
	}
	
	private function initArticleContent() {
	
		$this->mArticles = array(
//------------------------------------------------------------------------------
			'ArticleWithoutSubpage' =>
<<<ACL
	Some article content
ACL
		,
//------------------------------------------------------------------------------
			'ArticleWithSubpage' =>
<<<ACL
	Some article content
ACL
		,
//------------------------------------------------------------------------------
			'ArticleWithSubpage/Subpage' =>
<<<ACL
	Some article content
ACL
		,
//------------------------------------------------------------------------------
			'AnotherArticleWithoutSubpage' =>
<<<ACL
	Some article content
ACL
		,
//------------------------------------------------------------------------------
			'AnotherArticleWithSubpage' =>
<<<ACL
	Some article content
ACL
		,
//------------------------------------------------------------------------------
			'AnotherArticleWithSubpage/Subpage' =>
<<<ACL
	Some article content
ACL
		,
//------------------------------------------------------------------------------
			'MovingArticle' =>
<<<ACL
	Some article content
ACL
		,
//------------------------------------------------------------------------------
			'Category:SomeCategory' =>
<<<ACL
	Some article content
ACL
		,
//------------------------------------------------------------------------------
			'Category:SomeOtherCategory' =>
<<<ACL
	Some article content
ACL
		,
//------------------------------------------------------------------------------
			'Category:MovingCategory' =>
<<<ACL
	Some article content
ACL
		,
//------------------------------------------------------------------------------
			'Property:AProperty' =>
<<<ACL
	[[has type::Page| ]]
ACL
		,
//------------------------------------------------------------------------------
			'Property:AProperty1' =>
<<<ACL
	[[has type::Page| ]]
ACL
		,
//------------------------------------------------------------------------------
			'ACL:Page/ArticleWithoutSubpage' =>
<<<ACL
{{#manage rights: assigned to=User:U1}}

{{#access:
 assigned to=User:U1
|actions=read
|description= Allow read access for U1
}}

[[Category:ACL/ACL]]
ACL
		,
//------------------------------------------------------------------------------
			'ACL:Page/ArticleWithSubpage' =>
<<<ACL
{{#manage rights: assigned to=User:U1}}

{{#access:
 assigned to=User:U1
|actions=read
|description= Allow read access for U1
}}

[[Category:ACL/ACL]]
ACL
		,
//------------------------------------------------------------------------------
			'ACL:Category/SomeOtherCategory' =>
<<<ACL
{{#manage rights: assigned to=User:U1}}

{{#access:
 assigned to=User:U1
|actions=read
|description= Allow read access for U1
}}

[[Category:ACL/ACL]]
ACL
		,
//------------------------------------------------------------------------------
			'ACL:Namespace/File' =>
<<<ACL
{{#manage rights: assigned to=User:U1}}

{{#access:
 assigned to=User:U1
|actions=read
|description= Allow read access for U1
}}

[[Category:ACL/ACL]]
ACL
		,
//------------------------------------------------------------------------------
			'ACL:Right/ReadOnly' =>
<<<ACL
{{#manage rights: assigned to=User:U1}}

{{#access:
 assigned to=User:U1
|actions=read
|description= Allow read access for U1
}}

[[Category:ACL/Right]]
ACL
		,
//------------------------------------------------------------------------------
			'ACL:Group/AGroup' =>
<<<ACL
{{#member:members=User:U1}}
{{#manage group:assigned to=User:U1}}
[[Category:ACL/Group]]
ACL
		,
//------------------------------------------------------------------------------
			'ACL:Property/AProperty' =>
<<<ACL
{{#property access: assigned to=User:U1
 |actions=read,edit,formedit,wysiwyg,create,move,delete,annotate
 |description=fullaccess for U:U1
 |name=Right}}

{{#manage rights: assigned to=User:U1}}
[[Category:ACL/ACL]]
ACL
		,
//------------------------------------------------------------------------------
			'ACL:Whitelist' =>
<<<ACL
{{#whitelist:pages=Main Page}}
ACL
		,
		);
		
	}
	
	
	
}

/**
 * This class tests if the memcache is set up correctly
 * 
 * It assumes that the HaloACL extension is enabled in LocalSettings.php
 * 
 * @author thsc
 *
 */
class TestMemcacheSetup extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = FALSE;
	
	
    function setUp() {
    }

    /**
     * Delete all articles that were created during a test.
     */
    function tearDown() {
    }
    
    /**
     * Tests if memcache is enabled and if data can be stored and retrieved.
     */
    function testMemcacheEnabled() {
    	global $wgMainCacheType, $wgMemCachedServers;

    	$this->assertEquals(CACHE_MEMCACHED, $wgMainCacheType, "Expected \$wgMainCacheType == CACHE_MEMCACHED");
    	$this->assertTrue(count($wgMemCachedServers) > 0, "Expected \$wgMemCachedServers to contain at least one memcache server.");
    	
    	global $wgMemc;
    	
    	// Test if data can be stored in the cache
    	$testString = "some data";
    	$key = wfMemcKey( 'HaloACL', 'MemCache', 'Test' );
    	$data = $wgMemc->get( $key );
    	$wgMemc->set( $key, $testString, 60 * 15 );
    	$data = $wgMemc->get($key);
    	
    	$this->assertEquals($testString, $data, "Expected that a string was stored in memcache.");
    }

}

/**
* This class tests the basic functionality of the HaloACLMemcache
*
* It assumes that the HaloACL extension is enabled in LocalSettings.php
*
* @author thsc
*
*/
class TestHACLMemcache extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = FALSE;


	function setUp() {
	}

	/**
	 * Teardown after each test
	 */
	function tearDown() {
		$hm = HACLMemcache::getInstance();
		$hm->purgeCache();
	}

	/**
	 * Checks if the class HACLMemcache exists
	 */
	function testClassExists() {
		$this->assertTrue(class_exists('HACLMemcache'), "The class 'HACLMemcache' does not exist.");
	}

	/**
	 * Checks if an instance of the HACLMemcache can be created.
	 */
	function testGetInstance() {
		$hm = HACLMemcache::getInstance();
		$this->assertInstanceOf('HACLMemcache', $hm);
	}
	
	/**
	 * Checks if there are no memcache entries of HaloACL. This is a precondition
	 * for several tests.
	 */
	function testMemcacheEmpty() {
		$hm = HACLMemcache::getInstance();
		$keys = $hm->getHaloACLKeys();
		$this->assertTrue(empty($keys), "Expected the array returned by \$hm->getHaloACLKeys() to be empty.");
	}
	
	/**
	 * Checks if a permission can be stored and retrieved with the HACLMemcache.
	 * @depends testMemcacheEmpty
	 */
	function testStorePermission() {
		$hm = HACLMemcache::getInstance();
		
		$user = User::newFromName("U1");
		$anotheruser = User::newFromName("U2");
		$title = Title::newFromText("SomeArticle");
		$action1 = 'read';
		$action2 = 'edit';
		
		// Store a permission in memcache
		$success = $hm->storePermission($user, $title, $action1, true);
		$this->assertTrue($success, "Failed to store a permission with HACLMemcache.");
		
		// Try to retrieve the permission
		$allowed = $hm->retrievePermission($user, $title, $action1);
		$this->assertTrue($allowed, "Failed to retrieve a permission with HACLMemcache.");
		
		// Try to retrieve a non-existing permission
		$allowed = $hm->retrievePermission($anotheruser, $title, $action1);
		$this->assertEquals(-1, $allowed, "Wrong permission retrieved from HACLMemcache.");
		
		// Store a permission in memcache
		$success = $hm->storePermission($user, $title, $action2, false);
		$this->assertTrue($success, "Failed to store a permission with HACLMemcache.");
		
		// Try to retrieve the permission
		$allowed = $hm->retrievePermission($user, $title, $action2);
		$this->assertFalse($allowed, "Failed to retrieve a permission with HACLMemcache.");
		
	}
	
	/**
	 * Tests retrieving the keys of HaloACL from memcache before and after
	 * storing and purging cached permissions.
	 */
	function testGetKeys() {
		$hm = HACLMemcache::getInstance();
		
		// At the beginning there should be no keys
		$keys = $hm->getHaloACLKeys();
		$this->assertTrue(empty($keys), "Expected the array returned by \$hm->getHaloACLKeys() to be empty.");
		
		// Add a permission to memcache and check again for keys
		$user = User::newFromName("U1");
		$title = Title::newFromText("SomeArticle");
		$action = 'read';
		
		// Store a permission in memcache
		$success = $hm->storePermission($user, $title, $action, true);
		$this->assertTrue($success, "Failed to store a permission with HACLMemcache.");
		
		// now we expect one key
		$keys = $hm->getHaloACLKeys();
		$this->assertEquals(1, count($keys), "Expected the array returned by \$hm->getHaloACLKeys() to contain one element.");
		
		// purge the cache
		$hm->purgeCache();
		
		// At the end there should be no keys
		$keys = $hm->getHaloACLKeys();
		$this->assertTrue(empty($keys), "Expected the array returned by \$hm->getHaloACLKeys() to be empty.");
		
	}
	
	/**
	 * Tests purging the permissions in memcache.
	 */
	public function testPurgeCache() {
		$hm = HACLMemcache::getInstance();
		
		// First store a permission
		$user = User::newFromName("U1");
		$title = Title::newFromText("SomeArticle");
		$action = 'read';
		
		// Store a permission in memcache
		$success = $hm->storePermission($user, $title, $action, true);
		$this->assertTrue($success, "Failed to store a permission with HACLMemcache.");
		
		// Purge the cache
		$hm->purgeCache();

		// Trying to retrieve the permission from memcache should fail
		$allowed = $hm->retrievePermission($user, $title, $action);
		$this->assertEquals(-1, $allowed, "Expected that permission was deleted from memcache.");
		
	}

}



/**
* This class tests if HaloACL evaluates access rights correctly if they are 
* cached in memcache.
*
* It assumes that the HaloACL extension and memcache are enabled in LocalSettings.php
*
* @author thsc
*
*/
class TestAccessArticlesWithMemcachedHaloACL extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = FALSE;


	function setUp() {
		global $wgGroupPermissions;
		$wgGroupPermissions['*']['purge'] = true;
	}

	/**
	 * Delete all articles that were created during a test.
	 */
	function tearDown() {
	}

	/**
	* Data provider for testRenderArticles
	*/
	function providerRenderArticles() {
		return array(
			// $user, $articleName, $expectedHTML
			array('U1', 'Main Page', 'MediaWiki has been successfully installed.')
		);
	}
	
	
	/**
	 * Renders an article and checks if the generated HTML is as expected.
	 * 
	 * @param String $articleName
	 * @param String $user
	 * @param String $expectedHTML
	 * 
     * @dataProvider providerRenderArticles
	 */
	function testRenderArticles($user, $articleName, $expectedHTML) {
		
		// Render an article for the first time
		// Its access rights will be stored in memcache
		$html = HaloACLCommon::renderArticle($articleName, $user, 'purge');
		$this->assertContains($expectedHTML, $html);
		
		// Make sure that the access rights are stored in memcache
		$title = Title::newFromText($articleName);
		$userObj = User::newFromName($user);
		$hm = HACLMemcache::getInstance();
		$allowed = $hm->retrievePermission($userObj, $title, 'read');
		$this->assertTrue($allowed, "Failed to retrieve a permission that should have been cached automatically.");
		
	}


}


/**
* This class tests the behavior of the memcache when accesss rights and groups
* are changed in HaloACL. After certain operations the memcache must be purged.
*
* It assumes that the HaloACL extension is enabled in LocalSettings.php and that
* is memcache is accessible.
*
* @author thsc
*
*/
class TestHACLMemcacheFillAndPurge extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = FALSE;

	private $mArticleManager;
	
	private $mSDContent =
<<<ACL
	{{#manage rights: assigned to=User:U1}}
	
	{{#access:
	 assigned to=User:U1
	|actions=read
	|description= Allow read access for and U1
	}}
	
	[[Category:ACL/ACL]]
ACL;
	
	private $mRightContent =
<<<ACL
	{{#manage rights: assigned to=User:U1}}
	
	{{#access:
	 assigned to=User:U1
	|actions=read
	|description= Allow read access for and U1
	}}
	
	[[Category:ACL/Right]]
ACL;
	
	private $mGroupContent =
<<<ACL
{{#member:members=User:U1}}
{{#manage group:assigned to=User:U1}}
[[Category:ACL/Group]]
ACL;
	
	
	function setUp() {
		$this->mArticleManager = new ArticleManager();
		
		global $wgNamespacesWithSubpages;
		$wgNamespacesWithSubpages[NS_MAIN] = true;
	}

	/**
	 * Teardown after each test
	 */
	function tearDown() {
		$hm = HACLMemcache::getInstance();
		$hm->purgeCache();
		
		$this->mArticleManager->deleteArticles('WikiSysop');
	}

	/**
	 * Checks if the hooks for the HaloACL memcache are in place
	 */
	function testMemCacheHooks() {
		global $wgHooks;
		
		$hooks = $wgHooks['ArticleSaveComplete'];
		$this->assertContains('HACLMemcache::onArticleSaveComplete', $hooks, "Missing hook 'HACLMemcache::onArticleSaveComplete");
		$hooks = $wgHooks['ArticleDelete'];
		$this->assertContains('HACLMemcache::onArticleDelete', $hooks, "Missing hook 'HACLMemcache::onArticleDelete");
		$hooks = $wgHooks['HaloACLAddSecurityDescriptor'];
		$this->assertContains('HACLMemcache::onAddSecurityDescriptor', $hooks, "Missing hook 'HACLMemcache::onAddSecurityDescriptor");
		$hooks = $wgHooks['HaloACLModifySecurityDescriptor'];
		$this->assertContains('HACLMemcache::onModifySecurityDescriptor', $hooks, "Missing hook 'HACLMemcache::onModifySecurityDescriptor");
		$hooks = $wgHooks['HaloACLDeleteSecurityDescriptor'];
		$this->assertContains('HACLMemcache::onDeleteSecurityDescriptor', $hooks, "Missing hook 'HACLMemcache::onDeleteSecurityDescriptor");
		$hooks = $wgHooks['HaloACLAddGroup'];
		$this->assertContains('HACLMemcache::onAddGroup', $hooks, "Missing hook 'HACLMemcache::onAddGroup");
		$hooks = $wgHooks['HaloACLModifyGroup'];
		$this->assertContains('HACLMemcache::onModifyGroup', $hooks, "Missing hook 'HACLMemcache::onModifyGroup");
		$hooks = $wgHooks['HaloACLDeleteGroup'];
		$this->assertContains('HACLMemcache::onDeleteGroup', $hooks, "Missing hook 'HACLMemcache::onDeleteGroup");
	}
	
	/**
	 * Data provider for testACLChange
	 */
	public function providerForACLChange() {
		return array(
			// $articleName, $aclArticleName, $action, $expPurge
		
//--- Articles with or without subpage		
#0 - ACL for a normal article is modified. Cache entries for that article should be deleted 
			array('ArticleWithoutSubpage', 'ACL:Page/ArticleWithoutSubpage', 'modify', false),		
#1 - ACL for an article with subpage is modified. Cache should be purged
			array('ArticleWithSubpage', 'ACL:Page/ArticleWithSubpage', 'modify', true),		
#2 - ACL for a normal article is deleted. Cache entries for that article should be deleted 
			array('ArticleWithoutSubpage', 'ACL:Page/ArticleWithoutSubpage', 'delete', false),		
#3 - ACL for an article with subpage is deleted. Cache should be purged
			array('ArticleWithSubpage', 'ACL:Page/ArticleWithSubpage', 'delete', true),		
#4 - ACL for a normal article is created. Cache entries for that article should be deleted 
			array('AnotherArticleWithoutSubpage', 'ACL:Page/AnotherArticleWithoutSubpage', 'add', false),		
#5 - ACL for an article with subpage is created. Cache should be purged
			array('AnotherArticleWithSubpage', 'ACL:Page/AnotherArticleWithSubpage', 'add', true),		

//--- Categories ---
#6 - ACL for a category is created. Cache should be purged
			array('ArticleWithoutSubpage', 'ACL:Category/SomeCategory', 'add', true),		
#7 - ACL for a category is modified. Cache should be purged
			array('ArticleWithoutSubpage', 'ACL:Category/SomeOtherCategory', 'modify', true),		
#8 - ACL for a category is deleted. Cache should be purged
			array('ArticleWithoutSubpage', 'ACL:Category/SomeOtherCategory', 'delete', true),		

//--- Namespaces ---
#9 - ACL for a namespace is created. Cache should be purged
			array('ArticleWithoutSubpage', 'ACL:Namespace/Help', 'add', true),		
#10 - ACL for a namespace is modified. Cache should be purged
			array('ArticleWithoutSubpage', 'ACL:Namespace/File', 'modify', true),		
#11 - ACL for a namespace is deleted. Cache should be purged
			array('ArticleWithoutSubpage', 'ACL:Namespace/File', 'delete', true),
					
//--- Right template ---
#12 - ACL for a template is created. Cache should be purged
			array('ArticleWithoutSubpage', 'ACL:Right/ReadOnly1', 'add', true),		
#13 - ACL for a template is modified. Cache should be purged
			array('ArticleWithoutSubpage', 'ACL:Right/ReadOnly', 'modify', true),		
#14 - ACL for a template is deleted. Cache should be purged
			array('ArticleWithoutSubpage', 'ACL:Right/ReadOnly', 'delete', true),
				
//--- Groups ---
#15 - A group is created. Cache should be purged
			array('ArticleWithoutSubpage', 'ACL:Group/AGroup1', 'add', true),		
#16 - A group is modified. Cache should be purged
			array('ArticleWithoutSubpage', 'ACL:Group/AGroup', 'modify', true),		
#17 - A group is deleted. Cache should be purged
			array('ArticleWithoutSubpage', 'ACL:Group/AGroup', 'delete', true),
			
//--- Properties ---
#18 - A property ACL is created. Cache should be purged
			array('ArticleWithoutSubpage', 'ACL:Property/AProperty1', 'add', true),		
#19 - A property ACL is modified. Cache should be purged
			array('ArticleWithoutSubpage', 'ACL:Property/AProperty', 'modify', true),		
#20 - A property ACL is deleted. Cache should be purged
			array('ArticleWithoutSubpage', 'ACL:Property/AProperty', 'delete', true),		

//--- Properties ---
#21 - The whitelist is modified. Cache should be purged
			array('ArticleWithoutSubpage', 'ACL:Whitelist', 'modify', true),		
#22 - The whitelist is deleted. Cache should be purged
			array('ArticleWithoutSubpage', 'ACL:Whitelist', 'delete', true),		

		);
	}
	
	/**
	 * Tests if memcache entries are correctly removed after certain actions on
	 * ACL articles.
	 * 
	 * @param string $articleName
	 * 		Name of the article that is affected by the ACL change
	 * @param string $aclArticleName
	 * 		Name of the ACL article that is affected by the ACL change
	 * @param string $action
	 * 		Action to perform on the ACL article
	 * @param boolean $expPurge
	 * 		If true, purging the cache is expected. All access information is 
	 * 		expected to be removed from the cache.
	 * 		If false, only the cache entries for the article are expected to be
	 * 		removed.
	 * 
	 * @dataProvider providerForACLChange
	 */
	public function testACLChange($articleName, $aclArticleName, $action, $expPurge) {
//		$this->markTestSkipped('testACLChange currently disabled');
				
		// Actions are performed as user WikiSysop
		global $wgUser;
		$wgUser = User::newFromName("WikiSysop");
		
		// Now make sure there are some entries in memcache for it
		$t = Title::newFromText($articleName);
		$t->userCan('read');
		$t->userCan('edit');
		$t->userCan('delete');
		// Add some additional info to the cache
		$mpt = Title::newFromText('Main Page');
		$mpt->userCan('read');
		$mpt->userCan('edit');
		$mpt->userCan('delete');
		
		// Make sure there are at least three cache entries for the article
		$hmc = HACLMemcache::getInstance();
		$articleKeys = $hmc->getCacheKeysForArticle($t);
		$this->assertGreaterThanOrEqual(3, count($articleKeys), "Expected at least three cache entries for article '$articleName'.");
		
		$numKeysBeforeAction = count($hmc->getHaloACLKeys());
		
		// Now modify the ACL article
		$tacl = Title::newFromText($aclArticleName);
		$acl = new Article($tacl);
		HACLParserFunctions::getInstance()->reset();
		
		// Now perform the requested action on the ACL article
		switch ($action) {
			case 'add':
				if (strpos($aclArticleName, 'ACL:Right') === 0) {
					$content = $this->mRightContent;
				} else if (strpos($aclArticleName, 'ACL:Group') === 0) {
					$content = $this->mGroupContent;
				} else {
					$content = $this->mSDContent;
				}
				$this->mArticleManager->createArticles(
					array($aclArticleName => $content),
					"U1");
				break;
			case 'modify':
				$content = $acl->getContent();
				$content .= 'Some change\n';
				$acl->doEdit($content, 'Testing.');
				break;
			case 'delete':
				$acl->doDelete('Testing.');
				break;
		}
		
		// Now check the content of the memcache
		$keys = $hmc->getHaloACLKeys();
		if ($expPurge) {
			// Purging the cache was expected.
			$this->assertEquals(0, count($keys), "Expected that the memcache is empty but found some keys.");
		} else {
			// It was expected that only the keys for the article are deleted
			$aKeys = $hmc->getCacheKeysForArticle($t);
			$this->assertEquals(0, count($aKeys), "Expected that the memcache for the article '$articleName' is empty but found some keys.");
			
			// Verify that not too many keys were deleted
			$expNumKeys = $numKeysBeforeAction - count($articleKeys);
			$this->assertEquals($expNumKeys, count($keys), "Expected that the memcache for the article '$articleName' is empty but found some keys.");
		}
		
	}

	/**
	 * Data provider for testACLChange
	 */
	public function providerForWikiChange() {
		return array(
			// $articleName, $action, $expPurge
#0 - Save an article. Cache entries for that article should be deleted 
			array('Article1', 'save', false),		
#1 - Save a category article. Cache should be purged
			array('Category:Category1', 'save', true),		
#2 - Delete a normal article. Cache entries for that article should be deleted 
			array('Article1', 'delete', false),		
#3 - Delete a category article. Cache should be purged
			array('Category:Category1', 'delete', true),		
#4 - An article is moved. Its cache entry should be deleted
			array('MovingArticle', 'move', false),		
#5 - A category article is moved. Cache should be purged
# This normally fails as categories can not be moved. This is only possible with a patch.
#			array('Category:MovingCategory', 'move', true),
		);
	}
	
	/**
	 * Tests if memcache entries are correctly removed after certain actions on
	 * articles.
	 * 
	 * @param string $articleName
	 * 		Name of the article
	 * @param string $action
	 * 		Action on this article
	 * @param boolean $expPurge
	 * 		If true, purging the cache is expected. All access information is 
	 * 		expected to be removed from the cache.
	 * 		If false, only the cache entries for the article are expected to be
	 * 		removed.
	 * 
	 * @dataProvider providerForWikiChange
	 */
	public function testWikiChange($articleName, $action, $expPurge) {
		
		// Check if the article exists
		$exists = $this->mArticleManager->articleExists($articleName);
		
		// If the article does not exist, create it
		if (!$exists) {
			$this->mArticleManager->createArticles(
				array($articleName => "Some content"), "U1");
		}
		
		// Now make sure there are some entries in memcache for it
		$t = Title::newFromText($articleName);
		$t->userCan('read');
		$t->userCan('edit');
		$t->userCan('delete');
		// Add some additional info to the cache
		$mpt = Title::newFromText('Main Page');
		$mpt->userCan('read');
		$mpt->userCan('edit');
		$mpt->userCan('delete');
		
		// Make sure there are at least three cache entries for the article
		$hmc = HACLMemcache::getInstance();
		$articleKeys = $hmc->getCacheKeysForArticle($t);
		$this->assertGreaterThanOrEqual(3, count($articleKeys), "Expected at least three cache entries for article '$articleName'.");
		
		$numKeysBeforeAction = count($hmc->getHaloACLKeys());
		
		$article = new Article($t);
		// Now perform the requested action on the article
		switch ($action) {
			case 'save':
				$article->doEdit('Changed text', 'Testing.');
				break;
			case 'move':
				$newName = $t->getFullText().'Moved';
				$nt = Title::newFromText($newName);
				$this->mArticleManager->addArticle($newName);
				$t->moveTo($nt, false, 'Test', true);
				break;
			case 'delete':
				$article->doDelete('Testing.');
				break;
		}
		
		// Now check the content of the memcache
		$keys = $hmc->getHaloACLKeys();
		if ($expPurge) {
			// Purging the cache was expected.
			$this->assertEquals(0, count($keys), "Expected that the memcache is empty but found some keys.");
		} else {
			// It was expected that only the keys for the article are deleted
			$aKeys = $hmc->getCacheKeysForArticle($t);
			$this->assertEquals(0, count($aKeys), "Expected that the memcache for the article '$articleName' is empty but found some keys.");
			
			// Verify that not too many keys were deleted
			$expNumKeys = $numKeysBeforeAction - count($articleKeys);
			$this->assertEquals($expNumKeys, count($keys), "Expected that the memcache for the article '$articleName' is empty but found some keys.");
		}
		
	}
	
	/**
	 * Data provider for testMemcacheDisabledForDynamicHaloACL
	 */
	public function providerForMemcacheDisabledForDynamicHaloACL() {
		return array(
//          $aclArticle, $articleContent
#0 - A security descriptor with dynamic members 			
			array('ACL:Page/ArticleWithoutSubpage', 
<<<ACL
{{#access: 
 |assigned to={{#ask: [[ProjectA]][[ProjectManager::+]]|?projectManager # =}}
 |actions=read,edit,formedit,wysiwyg,create,move,delete,annotate
 |description=Full access for project manager
 |name=FA
}}

{{#manage rights: assigned to=User:WikiSysop}}
[[Category:ACL/ACL]]
ACL
			),
#1 - A template with dynamic members 			
			array('ACL:Page/ArticleWithoutSubpage', 
<<<ACL
{{#access: 
 |assigned to={{#ask: [[ProjectA]][[ProjectManager::+]]|?projectManager # =}}
 |actions=read,edit,formedit,wysiwyg,create,move,delete,annotate
 |description=Full access for project manager
 |name=FA
}}

{{#manage rights: assigned to=User:WikiSysop}}
[[Category:ACL/Right]]
ACL
			),
#2 - A group with dynamic members 			
			array('ACL:Group/AGroup', 
<<<ACL
{{#member: 
| members={{#sparql: SELECT ?p WHERE { ?p property:WorksFor a:ProjectA .} |?p # =}}
}}

{{#manage group: assigned to=User:WikiSysop}}
[[Category:ACL/Group]]
ACL
			),
		);
	}

	/**
	 * Checks if the memcache is disabled if dynamic HaloACL i.e. dynamic group
	 * membership or dynamic assignees is used.
	 * 
	 * @param string $aclArticle
	 * 		Name of an ACL article that will be created
	 * @param string $articleContent
	 * 		Content of the ACL article
	 * 
	 * @dataProvider providerForMemcacheDisabledForDynamicHaloACL
	 */
	public function testMemcacheDisabledForDynamicHaloACL($aclArticle, $articleContent) {
		// Actions are performed as user WikiSysop
		global $wgUser;
 		$wgUser = User::newFromName("WikiSysop");
		$hmc = HACLMemcache::getInstance();
		
		// In the beginning there is no SD with dynamic members 
		// => Memcache should be active
		
		// Make sure there are some entries in memcache
		$t = Title::newFromText('ArticleWithoutSubpage');
		$t->userCan('read');
		
		// Make sure there are at least three cache entries for the article
		// Try to retrieve the permission
		$allowed = $hmc->retrievePermission($wgUser, $t, 'read');
		$this->assertNotEquals(-1, $allowed, "Failed to retrieve a permission with HACLMemcache.");
		
		// Now save an article with dynamic members
		// => Memcache should be disabled
		$this->mArticleManager->createArticles(
				array($aclArticle => $articleContent), "WikiSysop");

		// Try to cache the access right again
		$hmc->enableMemcache(true);
 		$t->userCan('read');
		$allowed = $hmc->retrievePermission($wgUser, $t, 'read');
		$this->assertEquals(-1, $allowed, "Expected that HACLMemcache is disabled.");
		
		// Delete the ACL article again
		// => Memcache should be active again
		$this->mArticleManager->deleteArticles("WikiSysop");
		$hmc->enableMemcache(true);
		$t->userCan('read');
		$allowed = $hmc->retrievePermission($wgUser, $t, 'read');
		$this->assertNotEquals(-1, $allowed, "Failed to retrieve a permission with HACLMemcache.");
	}
	
	/**
	 * Checks if the memcache is purged when articles are imported.
	 */
	public function testMemcachePurgeOnImport() {
		
		// First add some access rights to the cache
		$mpt = Title::newFromText('Main Page');
		$mpt->userCan('read');
		$mpt->userCan('edit');
		$mpt->userCan('delete');
		
		// Now do an import of articles
		$this->mArticleManager->importArticles(__DIR__."/../pages/HaloACLMemcacheTestPages.xml");
		
		// Check that the cache was purged
		$hmc = HACLMemcache::getInstance();
		$keys = $hmc->getHaloACLKeys();
		// Purging the cache is expected.
		$this->assertEquals(0, count($keys), "Expected that the memcache is empty but found some keys.");
		
	}
	
	/**
	* Checks if the memcache is purged when $wgGroupPermissions are changed.
	*/
	public function testMemcachePurgeOnGroupPermissionsChange() {

		global $wgGroupPermissions, $wgUser;
		// Set some initial group permissions
		$wgGroupPermissions['*']['edit'] = false;
		$hmc = HACLMemcache::getInstance();
		$hmc->enableMemcache(true);
		
		// First add some access rights to the cache
		$mpt = Title::newFromText('Main Page');
		$mpt->userCan('read');
		$mpt->userCan('edit');
		$mpt->userCan('delete');
		$keys = $hmc->getHaloACLKeys();
		// We expect at least three keys for these operations
		$this->assertGreaterThanOrEqual(3, count($keys), "Expected at least three access rights in memcache.");
		
		// Now change $wgGroupPermissions
		$wgGroupPermissions['*']['edit'] = true;
		
		// Check that the cache was purged
		$hmc->enableMemcache(true);
		// Try to retrieve a permission from memcache. It should not be there.
		$allowed = $hmc->retrievePermission($wgUser, $mpt, 'read');
		$this->assertEquals(-1, $allowed, "Expected that permission was deleted from HACLMemcache.");
		
		$keys = $hmc->getHaloACLKeys();
		// Purging the cache is expected.
		$this->assertEquals(0, count($keys), "Expected that the memcache is empty but found some keys.");
		
		// Now the permissions are not changed. Memcache should be active again
		$mpt->userCan('read');
		$mpt->userCan('edit');
		$mpt->userCan('delete');
		$keys = $hmc->getHaloACLKeys();
		// We expect at least three keys for these operations
		$this->assertGreaterThanOrEqual(3, count($keys), "Expected at least three access rights in memcache.");
		
	}
}
