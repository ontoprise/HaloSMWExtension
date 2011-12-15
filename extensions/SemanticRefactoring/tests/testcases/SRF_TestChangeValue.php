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
require_once($srefgIP.'/includes/operations/SRF_ChangeValue.php');
require_once($srefgIP.'/tests/resources/SRF_ArticleManager.php');

class SRFTestChangeValue extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = FALSE;

	static function setUpBeforeClass() {
		global $srfChangeValueArticles;
		$articleManager = new ArticleManager();
		$articleManager->createArticles($srfChangeValueArticles);
	}

	function tearDown() {

	}


	function testChangeValue() {
		$r = new SRFChangeValueOperation(array("Michael"), "Employee of", "Ontoprise", "Ontoprise GmbH");
		$logMessages=array();
		$r->refactor(false, $logMessages);
		$log = reset($logMessages['Michael']);
		print "\n".$log->asWikiText();
		$this->assertContains('[[Employee of::Ontoprise GmbH]]', $log->getWikiText());
	}

	function testValueRemove() {
		$r = new SRFChangeValueOperation(array("Daniel"), "Has income", "60000", NULL);
		$logMessages=array();
		$r->refactor(false, $logMessages);
		$log = reset($logMessages['Daniel']);
		print "\n".$log->asWikiText();
		$this->assertNotContains('60000', $log->getWikiText());
	}

	function testValueAdd() {
		$r = new SRFChangeValueOperation(array("Dmitry"), "Occupation", NULL, "Software engineer");
		$logMessages=array();
		$r->refactor(false, $logMessages);
		$log = reset($logMessages['Dmitry']);
		print "\n".$log->asWikiText();
		$this->assertContains('[[Occupation::Software engineer]]', $log->getWikiText());
	}

}