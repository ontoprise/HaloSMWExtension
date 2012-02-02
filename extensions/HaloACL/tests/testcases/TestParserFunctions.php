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

require_once 'CommonClasses.php';

/**
 * @file
 * @ingroup HaloACL_Tests
 */

/**
 * This suite tests the parser functions of HaloACL.
 * 
 * @author thsc
 *
 */
class TestParserFunctionsSuite extends PHPUnit_Framework_TestSuite
{
	
	private $mArticleManager;
	
	public static function suite() {
		if (!defined('UNIT_TEST_RUNNING')) {
			define('UNIT_TEST_RUNNING', true);
		}
				
		$suite = new TestParserFunctionsSuite();
		$suite->addTestSuite('TestParserFunctions');
		$suite->addTestSuite('TestParserFunctionsOutput');
		return $suite;
	}
	
	protected function setUp() {
    	HACLStorage::reset(HACL_STORE_SQL);
		HACLStorage::getDatabase()->dropDatabaseTables(false);
		HACLStorage::getDatabase()->initDatabaseTables(false);
		
		HaloACLCommon::createUsers(array("U1", "U2"));
		        
        $this->mArticleManager = new ArticleManager();
    	$this->mArticleManager->createACLBaseArticles("U1");
	}
	
	protected function tearDown() {
        $this->mArticleManager->deleteArticles("U1");

        HACLStorage::getDatabase()->dropDatabaseTables(false);
		HACLStorage::getDatabase()->initDatabaseTables(false);
        
	}
	
    
}

/**
 * This class test the parser functions of HaloACL.
 * 
 * @author thsc
 *
 */
class TestParserFunctions extends PHPUnit_Framework_TestCase {

	private $mArticles;
	private $mArticleManager;
	private $mOrderOfArticleCreation;
	
	protected $backupGlobals = FALSE;
	
    function setUp() {
    	// reset group permissions
    	global $wgGroupPermissions;
    	foreach ($wgGroupPermissions as $group => $permissions) {
    		foreach ($permissions as $p => $value) {
    			$wgGroupPermissions[$group][$p] = true;
    		}
    	}
    	
    	HACLStorage::reset(HACL_STORE_SQL);
    	HaloACLCommon::createUsers(array("U1", "U2", "U3", "U4", "U5", "U6"));
        
        $this->initArticleContent();
        $this->mArticleManager = new ArticleManager();
        $this->mArticleManager->createArticles($this->mArticles, "U1", $this->mOrderOfArticleCreation);
    }

    function tearDown() {
    	$this->mArticleManager->deleteArticles("U1");
    }

    function testACLParserFunctionTest() {
    	global $wgUser;
    	$wgUser = User::newFromName("U1");
    	
    	$this->checkRights();
	   	$this->removeRights();
    	$this->removeGroups();
    	
    }
    
    
    private function removeRights() {
    	$file = __FILE__;
    	try {
    		$t = Title::newFromText("ACL:Right/PR3");
    		global $wgTitle;
    		$wgTitle = $t;
    		$article = new Article($t);
			$article->doDelete("Testing");
    		
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
			HaloACLCommon::checkRights($this, "TPF_CR_2", $checkRights);			
			
    		$t = Title::newFromText("ACL:Category/B");
    		$article = new Article($t);
			$article->doDelete("Testing");
			
    		$t = Title::newFromText("ACL:Right/PR1");
    		$article = new Article($t);
			$article->doDelete("Testing");

			global $haclgOpenWikiAccess;
			
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
			HaloACLCommon::checkRights($this, "TPF_CR_3", $checkRights);			
			
    		$t = Title::newFromText("ACL:Right/PR2");
    		$article = new Article($t);
			$article->doDelete("Testing");
			
			
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
			HaloACLCommon::checkRights($this, "TPF_CR_4", $checkRights);
						
    		$t = Title::newFromText("ACL:Page/A");
    		$article = new Article($t);
			$article->doDelete("Testing");
			
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
			HaloACLCommon::checkRights($this, "TPF_CR_5", $checkRights);
			
		} catch (Exception $e) {
			$this->assertTrue(false, "Unexpected exception while testing ".basename($file)."::removeRights():".$e->getMessage());
		}
    	
    }
    
    private function removeGroups() {
    	$file = __FILE__;
    	global $wgUser;
    	$wgUser = User::newFromName("U1");
    	
    	try {
			
    		$t = Title::newFromText("ACL:Group/G3");
    		$article = new Article($t);
			$article->doDelete("Testing");
			$this->checkGroupMembers("TPF_RG2-G1", "Group/G1", "user", array("U1", false, "U2", true, "U3", true, "U4", true, "U5", true, "U6", false));
			$this->checkGroupMembers("TPF_RG2-G2", "Group/G2", "user", array("U1", false, "U2", true, "U3", true, "U4", true, "U5", true, "U6", false));
			$this->checkGroupMembers("TPF_RG2-G4", "Group/G4", "user", array("U1", false, "U2", false, "U3", false, "U4", true, "U5", true, "U6", false));
			$this->checkGroupMembers("TPF_RG2-G5", "Group/G5", "user", array("U1", false, "U2", true, "U3", true, "U4", true, "U5", false, "U6", false));
			
    		$t = Title::newFromText("ACL:Group/G2");
    		$article = new Article($t);
			$article->doDelete("Testing");
			$this->checkGroupMembers("TPF_RG3-G1", "Group/G1", "user", array("U1", false, "U2", false, "U3", false, "U4", false, "U5", false, "U6", false));
			$this->checkGroupMembers("TPF_RG3-G4", "Group/G4", "user", array("U1", false, "U2", false, "U3", false, "U4", true, "U5", true, "U6", false));
			$this->checkGroupMembers("TPF_RG3-G5", "Group/G5", "user", array("U1", false, "U2", true, "U3", true, "U4", true, "U5", false, "U6", false));
			
    		$t = Title::newFromText("ACL:Group/G5");
    		$article = new Article($t);
			$article->doDelete("Testing");
			$this->checkGroupMembers("TPF_RG4-G1", "Group/G1", "user", array("U1", false, "U2", false, "U3", false, "U4", false, "U5", false, "U6", false));
			$this->checkGroupMembers("TPF_RG4-G4", "Group/G4", "user", array("U1", false, "U2", false, "U3", false, "U4", true, "U5", true, "U6", false));
			
    		$t = Title::newFromText("ACL:Group/G4");
    		$article = new Article($t);
			$article->doDelete("Testing");
			$this->checkGroupMembers("TPF_RG5-G1", "Group/G1", "user", array("U1", false, "U2", false, "U3", false, "U4", false, "U5", false, "U6", false));
			
    		$t = Title::newFromText("ACL:Group/G1");
    		$article = new Article($t);
			$article->doDelete("Testing");
						
		} catch (Exception $e) {
			$this->assertTrue(false, "Unexpected exception while testing ".basename($file)."::removeGroups():".$e->getMessage());
		}
	}
	
    private function checkRights() {
    	$file = __FILE__;
    	try {
			$checkRights = array(
//				array('anonymous', 'U1', 'read', false),
//				array('anonymous', 'U1', 'formedit', false),
//				array('anonymous', 'U1', 'annotate', false),
//				array('anonymous', 'U1', 'wysiwyg', false),
//				array('anonymous', 'U1', 'edit', false),
//				array('anonymous', 'U1', 'create', false),
//				array('anonymous', 'U1', 'delete', false),
//				array('anonymous', 'U1', 'move', false),
//
//				array('anonymous', '*', 'read', true),
//				array('anonymous', '*', 'formedit', false),
//				array('anonymous', '*', 'annotate', false),
//				array('anonymous', '*', 'wysiwyg', false),
//				array('anonymous', '*', 'edit', false),
//				array('anonymous', '*', 'create', false),
//				array('anonymous', '*', 'delete', false),
//				array('anonymous', '*', 'move', false),
//				
//				array('registered', 'U1', 'read', true),
//				array('registered', 'U1', 'formedit', false),
//				array('registered', 'U1', 'annotate', false),
//				array('registered', 'U1', 'wysiwyg', false),
//				array('registered', 'U1', 'edit', false),
//				array('registered', 'U1', 'create', false),
//				array('registered', 'U1', 'delete', false),
//				array('registered', 'U1', 'move', false),
//
//				array('registered', '*', 'read', false),
//				array('registered', '*', 'formedit', false),
//				array('registered', '*', 'annotate', false),
//				array('registered', '*', 'wysiwyg', false),
//				array('registered', '*', 'edit', false),
//				array('registered', '*', 'create', false),
//				array('registered', '*', 'delete', false),
//				array('registered', '*', 'move', false),
//				
//				array('A', 'U1', 'read', true),
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
			HaloACLCommon::checkRights($this, "TPF_CR_1", $checkRights);
		} catch (Exception $e) {
			$this->assertTrue(false, "Unexpected exception while testing ".basename($file)."::checkRights():".$e->getMessage());
		}
			
    }
	
	private function initArticleContent() {
		$this->mOrderOfArticleCreation = array(
			'A',
			'B',
			'Category:B',
			'anonymous',
			'registered',
			'ACL:Page/anonymous',
			'ACL:Page/registered',	
			'ACL:Whitelist',
			'ACL:Group/G4',
			'ACL:Group/G5',
			'ACL:Group/G3',
			'ACL:Group/G2',
			'ACL:Group/G1',
			'ACL:Right/PR2',
			'ACL:Right/PR1',
			'ACL:Right/PR3',
			'ACL:Page/A',
			'ACL:Category/B',
		);
		
		$this->mArticles = array(
//------------------------------------------------------------------------------		
			'Category:B' =>
<<<ACL
This is category B.
ACL
,
//------------------------------------------------------------------------------		
			'ACL:Right/PR1' =>
<<<ACL
{{#manage rights: assigned to=Group/G4, Group/G5, User:U1}}

{{#access:
 assigned to =Group/G4
|actions=edit
|description=Allows read, formedit, edit and annotate
}}

{{#predefined right: rights= ACL:Right/PR2}}

[[Category:ACL/Right]]
ACL
,
//------------------------------------------------------------------------------
			'ACL:Right/PR2' =>
<<<ACL
{{#manage rights: assigned to=User:U1, User:U2}}

{{#access:
 assigned to =User:U2
|actions=delete
|description=Allows read, formedit, edit and delete
}}


[[Category:ACL/Right]]

ACL
,
		
//------------------------------------------------------------------------------		
			'ACL:Right/PR3' =>
<<<ACL
{{#manage rights: assigned to=User:U1}}

{{#predefined right: rights= ACL:Right/PR1}}
{{#predefined right: rights= ACL:Right/PR2}}


[[Category:ACL/Right]]

ACL
,
			
//------------------------------------------------------------------------------		

			'ACL:Page/A' =>
<<<ACL
{{#manage rights: assigned to=Group/G1, User:U1}}

{{#access:
 assigned to=Group/G1,User:U1
|actions=read
|description= Allow read access for G1 and U1
}}

{{#predefined right:rights=ACL:Right/PR1, ACL:Right/PR2}}

[[Category:ACL/ACL]]

ACL
,
//------------------------------------------------------------------------------		

			'anonymous' =>
<<<ACL
This page can only be accessed by anonymous users.
ACL
,
//------------------------------------------------------------------------------		

			'ACL:Page/anonymous' =>
<<<ACL
{{#manage rights: assigned to=User:U1}}

{{#access:
 assigned to=*
|actions=read
|description= Allow read access anonymous users
}}

[[Category:ACL/ACL]]

ACL
,

			'registered' =>
<<<ACL
This page can only be accessed by registered users.
ACL
,
//------------------------------------------------------------------------------		

			'ACL:Page/registered' =>
<<<ACL
{{#manage rights: assigned to=User:U1}}

{{#access:
 assigned to=#
|actions=read
|description= Allow read access registered users
}}

[[Category:ACL/ACL]]

ACL
,

//------------------------------------------------------------------------------		
			'ACL:Category/B' =>
<<<ACL
{{#manage rights: assigned to=User:U1}}

{{#predefined right: rights= ACL:Right/PR3}}

[[Category:ACL/ACL]]

ACL
,			
//------------------------------------------------------------------------------		
			'ACL:Group/G1' =>
<<<ACL
{{#manage group: assigned to=User:U1}}
{{#member:members=Group/G2, Group/G3}}

[[Category:ACL/Group]]

ACL
,

//------------------------------------------------------------------------------		
			'ACL:Group/G2' =>
<<<ACL
{{#manage group: assigned to=User:U1}}
{{#member:members=Group/G4, Group/G5}}

[[Category:ACL/Group]]

ACL
,
			
//------------------------------------------------------------------------------		
			'ACL:Group/G3' =>
<<<ACL
{{#manage group: assigned to=User:U1}}
{{#member:members=Group/G4, User:U6}}

[[Category:ACL/Group]]

ACL
,
			
//------------------------------------------------------------------------------		
			'ACL:Group/G4' =>
<<<ACL
{{#manage group: assigned to=User:U1,User:U5, User:U4}}
{{#member:members=User:U4,User:U5}}

[[Category:ACL/Group]]

ACL
,

//------------------------------------------------------------------------------		
			'ACL:Group/G5' =>
<<<ACL
{{#manage group: assigned to=User:U1}}
{{#member:members=User:U2,User:U4,User:U3}}

[[Category:ACL/Group]]

ACL
,

//------------------------------------------------------------------------------		
			'ACL:Whitelist' =>
<<<ACL
This is the whitelist.

{{#whitelist: pages=Main Page, ACL:Group/G1}}

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

		);
	}

	private function checkGroupMembers($testcase, $group, $mode, $membersAndResults) {
		$group = HACLGroup::newFromName($group);
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
	
	
}

/**
* This class test the page output that is generated by the parser functions of HaloACL.
*
* @author thsc
*
*/
class TestParserFunctionsOutput extends PHPUnit_Framework_TestCase {

	private $mArticles;
	private $mArticleManager;
	private $mOrderOfArticleCreation;

	protected $backupGlobals = FALSE;

	function setUp() {
		 
		HACLStorage::reset(HACL_STORE_SQL);
		 
		HaloACLCommon::createUsers(array("U1"));
		
		$this->initArticleContent();
		$this->mArticleManager = new ArticleManager();
		$this->mArticleManager->createArticles($this->mArticles, "U1", $this->mOrderOfArticleCreation);
	}

	function tearDown() {
		$this->mArticleManager->deleteArticles("U1");
	}
	
    /**
     * Data provider for testRenderedPageContent
     */
    function providerRenderedPageContent() {
    	return array(
	    	// $user, $pagename, $content, $unexpectedContent
    		array('U1', 'ACL:Whitelist', 
    			  array('Special:UserLogin'),
    		      array('The whitelist in this article contains nonexistent articles.')
    		),
    	);
    }

    /**
     * Checks if rendered articles contain the expected content and do not contain
     * unexpected content.
     * 
     * @dataProvider providerRenderedPageContent
     */
    function testRenderedPageContent($user, $pagename, $content, $unexpectedContent) {

    	global $wgUser, $wgOut;
    	
    	$wgUser = User::newFromName($user);
    	
    	$t = Title::newFromText($pagename);
    	$article = new Article($t);
    	$wgOut = new OutputPage();
    	$article->view();
    	$html = $wgOut->getHTML();
    	    	
    	// Check for expected content
    	if ($content) {
	    	foreach ($content as $c) {
	    		$pos = strpos($html, $c);
	    		$this->assertTrue($pos !== false, "The expected content was not found:\n$c\n");
	    	}
    	}
    	// Check for unexpected content
    	if ($unexpectedContent) {
	    	foreach ($unexpectedContent as $c) {
	    		$pos = strpos($html, $c);
	    		$this->assertTrue($pos === false, "Unexpected content was found:\n$c\n");
	    	}
    	}
    }
		
	private function initArticleContent() {
		$this->mOrderOfArticleCreation = array(
				'ACL:Whitelist',
		);
	
		$this->mArticles = array(
		//------------------------------------------------------------------------------
				'ACL:Whitelist' =>
<<<ACL
This is the whitelist.

{{#whitelist: pages=Main Page, Special:UserLogin}}

ACL
		,
		);
	}
	
}

