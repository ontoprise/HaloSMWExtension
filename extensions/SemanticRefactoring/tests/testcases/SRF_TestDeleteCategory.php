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
 * @file
 * @ingroup SMWHaloTests
 *
 * Tests the refactoring facilities
 *
 * @author Kai Kühn
 *
 */

if ( isset( $_SERVER ) && array_key_exists( 'REQUEST_METHOD', $_SERVER ) ) {
    die( "This script must be run from the command line\n" );
}

global $srefgIP;
require_once($srefgIP.'/includes/SRF_RefactoringOperation.php');
require_once($srefgIP.'/includes/operations/SRF_DeleteCategory.php');
require_once($srefgIP.'/tests/resources/SRF_ArticleManager.php');

class SRFTestDeleteCategory extends PHPUnit_Framework_TestCase {
	protected $backupGlobals = FALSE;

	static function setUpBeforeClass() {
		global $srfDeleteCategoryArticles;
		$articleManager = new ArticleManager();
		$articleManager->createArticles($srfDeleteCategoryArticles);
	}

	function tearDown() {

	}

	function testRemoveCategory() {
		$r = new SRFDeleteCategoryOperation('Person', array('sref_deleteCategory'=>true));
		$logMessages = array();
		
		$r->refactor(false, $logMessages);
		$log = reset($logMessages['Category:Person']);
		$this->assertEquals('Article deleted', $log->getOperation());

	}

	function testRemoveCategoryWithInstances() {
		$r = new SRFDeleteCategoryOperation('Man', array('sref_removeInstances'=>true));
		$logMessages = array();
		$r->refactor(false, $logMessages);
		$log = reset($logMessages['Kai']);
		$this->assertEquals('Article deleted', $log->getOperation());

	}

	function testRemoveQueries() {
		$r = new SRFDeleteCategoryOperation('Man', array('sref_removeQueriesWithCategories'=>true));
		$logMessages = array();
		
		$r->refactor(false, $logMessages);
		$log = reset($logMessages['All men']);
		$this->assertEquals('Removed query', $log->getOperation());
		$this->assertNotContains('#ask', $log->getWikiText());


	}

	function testRemoveCategoryAnnotations() {
		$r = new SRFDeleteCategoryOperation('Man', array('sref_removeCategoryAnnotations'=>true));
		$logMessages = array();
		
		$r->refactor(false, $logMessages);
		$log =  reset($logMessages['Kai']);
		$this->assertEquals('Removed category annotation', $log->getOperation());
		$this->assertNotContains('[[Category:Man]]', $log->getWikiText());

	}

	function testRemoveCategoryFromDomainAndRange() {
		$r = new SRFDeleteCategoryOperation('Person', array('sref_removeFromDomain'=>true));
		$logMessages = array();
		
		$r->refactor(false, $logMessages);

		$log =  reset($logMessages['Property:Has name']);
		$this->assertEquals('Removed from domain and/or range',  $log->getOperation());
		$this->assertNotContains('[[Category:Person]]', $log->getWikiText());
		
	}
}