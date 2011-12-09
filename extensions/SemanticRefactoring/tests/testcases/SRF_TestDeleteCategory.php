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
require_once($srefgIP.'/includes/SRF_RefactoringOperation.php');
require_once($srefgIP.'/includes/operations/SRF_DeleteCategory.php');

class SRFTestDeleteCategory extends PHPUnit_Framework_TestCase {
	protected $backupGlobals = FALSE;

	function setUp() {

	}

	function tearDown() {

	}

	function testRemoveCategory() {
		$r = new SMWRFDeleteCategoryOperation('Person', array('onlyCategory'=>true));
		$logMessages = array();
		$testData = array();
		$r->refactor(false, $logMessages, $testData);
		print_r($testData);
	}

	function testRemoveCategoryWithInstances() {
		$r = new SMWRFDeleteCategoryOperation('Person', array('removeInstances'=>true));
		$logMessages = array();
		$testData = array();
		$r->refactor(false, $logMessages, $testData);
		print_r($testData);
	}



	function testRemoveQueries() {
		$r = new SMWRFDeleteCategoryOperation('Person', array('removeQueries'=>true));
		$logMessages = array();
		$testData = array();
		$r->refactor(false, $logMessages, $testData);
		print_r($testData);
	}

	function testRemoveCategoryAnnotations() {
		$r = new SMWRFDeleteCategoryOperation('Person', array('removeCategoryAnnotations'=>true));
		$logMessages = array();
		$testData = array();
		$r->refactor(false, $logMessages, $testData);
		print_r($testData);
	}
}