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
require_once($srefgIP.'/includes/operations/SRF_DeleteProperty.php');
require_once($srefgIP.'/tests/resources/SRF_ArticleManager.php');

class SRFTestDeleteProperty extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = FALSE;

	static function setUpBeforeClass() {
		global $srfDeletePropertyArticles;
		$articleManager = new ArticleManager();
		$articleManager->createArticles($srfDeletePropertyArticles);
	}

	function tearDown() {

	}

	function testRemoveProperty() {
		$r = new SRFDeletePropertyOperation('Has child', array('onlyProperty'=>true));
		$logMessages = array();
		$testData = array();
		$r->refactor(false, $logMessages, $testData);
		$this->assertEquals('Article deleted', $logMessages['Property:Has child']->getOperation());
		
		//print_r($testData);
	}

	function testRemovePropertyWithInstances() {
		$r = new SRFDeletePropertyOperation('Has son', array('removeInstancesUsingProperty'=>true));
		$logMessages = array();
		$testData = array();
		$r->refactor(false, $logMessages, $testData);
		
		$this->assertEquals('Article deleted', $logMessages['Bernd']->getOperation());
		//print_r($testData);
	}



	function testRemoveQueries() {
		$r = new SRFDeletePropertyOperation('Has son', array('removeQueries'=>true));
		$logMessages = array();
		$testData = array();
		$r->refactor(false, $logMessages, $testData);
		$log = $logMessages['All sons'];
        $this->assertEquals('Removed query', $log->getOperation());
        $this->assertNotContains('#ask', $log->getWikiText());
		//print_r($testData);
	}

	function testRemovePropertyAnnotations() {
		$r = new SRFDeletePropertyOperation('Has son', array('removePropertyAnnotations'=>true));
		$logMessages = array();
		$testData = array();
		$r->refactor(false, $logMessages, $testData);
		$log = $logMessages['Bernd'];
        $this->assertEquals('Removed property annotation', $log->getOperation());
        $this->assertNotContains('[[Has son::Kai]]', $log->getWikiText());
		//print_r($testData);
	}
}