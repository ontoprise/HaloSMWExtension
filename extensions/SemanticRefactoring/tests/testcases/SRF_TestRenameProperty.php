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
require_once($srefgIP.'/includes/operations/SRF_RenameProperty.php');

class SRFTestRenameProperty extends PHPUnit_Framework_TestCase {


	function setUp() {

	}

	function tearDown() {

	}



	function testRenameProperty() {
		$r = new SMWRFRenamePropertyOperation("Testproperty", "Newtestproperty", true);
		$wikitext = <<<ENDS
This is a test [[Testproperty::value1]]. No text.
ENDS;
		$wikitext = $r->changeContent($wikitext);
		$this->assertContains('Newtestproperty', $wikitext);

	}

	function testRenamePropertyAsValue() {
		$r = new SMWRFRenamePropertyOperation("Testproperty", "Newtestproperty", true);
		$wikitext = <<<ENDS
This is a test [[ABC::Property:Testproperty]]. No text.
ENDS;
		$wikitext = $r->changeContent( $wikitext);
		$this->assertContains('Newtestproperty', $wikitext);

	}

	function testRenamePropertyAsLink() {
		$r = new SMWRFRenamePropertyOperation("Testproperty", "Newtestproperty", true);
		$wikitext = <<<ENDS
This is a test [[Property:Testproperty]]. No text.
ENDS;
		$wikitext = $r->changeContent( $wikitext);
		$this->assertContains('Newtestproperty', $wikitext);

	}

	function testRenamePropertyInQuery() {
		$r = new SMWRFRenamePropertyOperation("Testproperty", "Newtestproperty", true);
		$wikitext = <<<ENDS
This is a test
{{#ask: [[Category:Testcategory]][[Testproperty::+]]|?Testproperty }}
Test text
ENDS;
		$wikitext = $r->changeContent( $wikitext);
		$this->assertContains('Newtestproperty', $wikitext);

	}

	function testRenamePropertyInQuery2() {
		$r = new SMWRFRenamePropertyOperation("Testproperty", "Newtestproperty", true);
		$wikitext = <<<ENDS
This is a test
{{#ask: [[Property:Testproperty]]|?Testproperty }}
Test text
ENDS;
		$wikitext = $r->changeContent( $wikitext);
		$this->assertContains('Newtestproperty', $wikitext);

	}

	function testRenamePropertyInQuery3() {
		$r = new SMWRFRenamePropertyOperation("Testproperty", "Newtestproperty", true);
		$wikitext = <<<ENDS
This is a test
{{#ask: [[Category:Testcategory]]|?Testproperty }}
Test text
ENDS;
		$wikitext = $r->changeContent( $wikitext);
		$this->assertContains('Newtestproperty', $wikitext);

	}
}