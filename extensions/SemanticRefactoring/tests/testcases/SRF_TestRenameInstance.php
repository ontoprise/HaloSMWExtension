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
require_once($srefgIP.'/includes/operations/SRF_RenameInstance.php');

class SRFTestRenameInstance extends PHPUnit_Framework_TestCase {


	function setUp() {

	}

	function tearDown() {

	}

	function testInstanceAsLink() {
		$r = new SRFRenameInstanceOperation("Testinstance", "NewTestinstance", true);
		$wikitext = <<<ENDS
This is a test [[Testinstance]]. No text.
ENDS;
		$wikitext = $r->changeContent($wikitext);

		$this->assertContains('NewTestinstance', $wikitext);
	}

	function testInstanceOtherNamespaceAsLink() {
		$r = new SRFRenameInstanceOperation("Help:Testinstance", "Help:NewTestinstance", true);
		$wikitext = <<<ENDS
This is a test [[Help:Testinstance]]. No text.
ENDS;
		$wikitext = $r->changeContent($wikitext);

		$this->assertContains('Help:NewTestinstance', $wikitext);
	}

	function testInstanceWithWhitespaceAsLink() {
		$r = new SRFRenameInstanceOperation("Test instance", "NewTest instance", true);
		$wikitext = <<<ENDS
This is a test [[Test instance]]. No text.
ENDS;
		$wikitext = $r->changeContent($wikitext);

		$this->assertContains('NewTest instance', $wikitext);
	}

	function testInstanceInAnnotation() {
		$r = new SRFRenameInstanceOperation("Testinstance", "NewTestinstance", true);
		$wikitext = <<<ENDS
This is a test [[ABC::Testinstance]]. No text.
ENDS;
		$wikitext = $r->changeContent($wikitext);

		$this->assertContains('NewTestinstance', $wikitext);
	}

	function testInstanceInRecordAnnotation() {
		$r = new SRFRenameInstanceOperation("Testinstance", "NewTestinstance", true);
		$wikitext = <<<ENDS
This is a test [[ABC::Testinstance; CDE; FGH]]. No text.
ENDS;
		$wikitext = $r->changeContent($wikitext);

		$this->assertContains('NewTestinstance', $wikitext);
	}

	function testInstanceInQuery() {
		$r = new SRFRenameInstanceOperation("Testinstance", "NewTestinstance", true);
		$wikitext = <<<ENDS
This is a test {{#ask: [[Testinstance]][[Testproperty::+]] }}. No text.
ENDS;
		$wikitext = $r->changeContent($wikitext);

		$this->assertContains('NewTestinstance', $wikitext);
	}

	function testInstanceInQuery2() {
		$r = new SRFRenameInstanceOperation("Testinstance", "NewTestinstance", true);
		$wikitext = <<<ENDS
This is a test {{#ask: [[Category:Testcategory]][[ABC::Testinstance]] }}. No text.
ENDS;
		$wikitext = $r->changeContent($wikitext);

		$this->assertContains('NewTestinstance', $wikitext);
	}


}