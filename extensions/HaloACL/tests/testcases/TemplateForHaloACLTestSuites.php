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
 * This suite tests ...
 * 
 * @author thsc
 *
 */
class TestSomethingSuite extends PHPUnit_Framework_TestSuite
{
	
	private $mArticleManager;
	
	public static function suite() {
		if (!defined('UNIT_TEST_RUNNING')) {
			define('UNIT_TEST_RUNNING', true);
		}
				
		$suite = new TestSomethingSuite();
		$suite->addTestSuite('TestSomething');
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
	
    private function createArticles() {
        $this->mArticleManager = new ArticleManager();
        $this->mArticleManager->createACLBaseArticles("U1");
    }
 
    
	private function removeArticles() {
		$this->mArticleManager->deleteArticles("U1");
	}
	
	private function initArticleContent() {
	
		$this->mArticles = array(
		//------------------------------------------------------------------------------
				'SomeArticle' =>
<<<ACL
Some article content
ACL
,
		);
	}
	
}

/**
 * This class tests ...
 * 
 * It assumes that the HaloACL extension is enabled in LocalSettings.php
 * 
 * @author thsc
 *
 */
class TestSomething extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = FALSE;
	
	
    function setUp() {
    }

    /**
     * Delete all articles that were created during a test.
     */
    function tearDown() {
    }
    
    function testFoo() {
    	$this->assertTrue(true, "whatswrong");
    }


}
