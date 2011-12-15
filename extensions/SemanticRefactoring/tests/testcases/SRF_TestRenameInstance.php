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
require_once($srefgIP.'/includes/operations/SRF_RenameInstance.php');
require_once($srefgIP.'/tests/resources/SRF_ArticleManager.php');

class SRFTestRenameInstance extends PHPUnit_Framework_TestCase {


	protected $backupGlobals = FALSE;

	static function setUpBeforeClass() {
		global $srfRenameInstanceArticles;
		$articleManager = new ArticleManager();
		$articleManager->createArticles($srfRenameInstanceArticles);
	}


	function tearDown() {

	}

	function testInstanceInAnnotation() {
		$r = new SRFRenameInstanceOperation("Kai", "Kai Kuehn");
		$logMessages=array();
		$r->refactor(false, $logMessages);
		$log = reset($logMessages['Thomas']);
		$this->assertContains('[[Has colleague::Kai Kuehn]]', $log->getWikiText());
	}

	function testInstanceAsLink() {
		$r = new SRFRenameInstanceOperation("Kai", "Kai Kuehn");
		$logMessages=array();
		$r->refactor(false, $logMessages);
		$log = reset($logMessages['People']);
		$this->assertContains('[[Kai Kuehn]]', $log->getWikiText());
	}

	function testInstanceOtherNamespaceAsLink() {
		$r = new SRFRenameInstanceOperation("Help:OntologyBrowser", "Help:DataExplorer");
		$logMessages=array();
		$r->refactor(false, $logMessages);
		$log = reset($logMessages['Help pages']);
		$this->assertContains('[[Help:DataExplorer]]', $log->getWikiText());
	}

	function testInstanceWithWhitespaceAsLink() {
		$r = new SRFRenameInstanceOperation("Help:Query Interface", "Help:QI");
		$logMessages=array();
		$r->refactor(false, $logMessages);
		$log = reset($logMessages['Help pages']);
		$this->assertContains('[[Help:QI]]', $log->getWikiText());
	}


	function testInstanceInQuery1() {
		$r = new SRFRenameInstanceOperation("Kai", "Kai Kuehn");
		$logMessages=array();
		$r->refactor(false, $logMessages);
		$log = reset($logMessages['All colleagues of Kai']);
		$this->assertContains('[[Has colleague::Kai Kuehn]]', $log->getWikiText());
	}

	function testInstanceInQuery2() {
		$r = new SRFRenameInstanceOperation("Kai", "Kai Kuehn");
		$logMessages=array();
		$r->refactor(false, $logMessages);
		$log = reset($logMessages['All colleagues']);
		$this->assertContains('[[Kai Kuehn]]', $log->getWikiText());
	}




}