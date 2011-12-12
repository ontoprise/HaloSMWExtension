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
global $srefgIP;
require_once($srefgIP.'/includes/SRFRefactoringOperation.php');
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
		$r = new SRFDeleteCategoryOperation('Person', array('onlyCategory'=>true));
		$logMessages = array();
		$testData = array();
		$r->refactor(false, $logMessages, $testData);
		$this->assertEquals('deleted', $testData['Category:Person']);
		//print_r($testData);
	}

	function testRemoveCategoryWithInstances() {
		$r = new SRFDeleteCategoryOperation('Man', array('removeInstances'=>true));
		$logMessages = array();
		$testData = array();
		$r->refactor(false, $logMessages, $testData);
		$this->assertEquals('deleted', $testData['Kai']);
		//print_r($testData);
	}



	function testRemoveQueries() {
		$r = new SRFDeleteCategoryOperation('Man', array('removeQueries'=>true));
		$logMessages = array();
		$testData = array();
		$r->refactor(false, $logMessages, $testData);
		list($op, $wikitext) = $testData['All men'];
		$this->assertEquals('removeCategoryAnnotations', $op);
		$this->assertNotContains('#ask', $wikitext);
		
		//print_r($testData);
	}

	function testRemoveCategoryAnnotations() {
		$r = new SRFDeleteCategoryOperation('Man', array('removeCategoryAnnotations'=>true));
		$logMessages = array();
		$testData = array();
		$r->refactor(false, $logMessages, $testData);
		list($op, $wikitext) = $testData['Kai'];
		$this->assertEquals('removeCategoryAnnotations', $op);
        $this->assertNotContains('[[Category:Man]]', $wikitext);
		//print_r($testData);
	}
}