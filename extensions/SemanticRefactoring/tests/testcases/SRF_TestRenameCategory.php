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
require_once($srefgIP.'/includes/operations/SRF_RenameCategory.php');

class SRFTestRenameCategory extends PHPUnit_Framework_TestCase {


	function setUp() {

	}

	function tearDown() {

	}

	function testRenameCategory() {
		$r = new SRFRenameCategoryOperation("Testcategory", "Newtestcategory", true);
		$wikitext = <<<ENDS
This is a test [[Category:Testcategory]]. No text.
ENDS;
		$wikitext = $r->changeContent($wikitext);

		$this->assertContains('Newtestcategory', $wikitext);
	}

	function testRenameCategoryAsValue() {
		$r = new SRFRenameCategoryOperation("Testcategory", "Newtestcategory", true);
		$wikitext = <<<ENDS
This is a test [[ABC::Category:Testcategory]]. No text.
ENDS;
		$wikitext = $r->changeContent($wikitext);

		$this->assertContains('Newtestcategory', $wikitext);
	}

	function testRenameCategoryAsRecordValue() {
		$r = new SRFRenameCategoryOperation("Testcategory", "Newtestcategory", true);
		$wikitext = <<<ENDS
This is a test [[Has domain and range::Category:Testcategory; Category:TestRange]]. No text.
ENDS;
		$wikitext = $r->changeContent($wikitext);

		$this->assertContains('Newtestcategory', $wikitext);
	}

	function testRenameCategoryAsLink() {
		$r = new SRFRenameCategoryOperation("Testcategory", "Newtestcategory", true);
		$wikitext = <<<ENDS
This is a test [[:Category:Testcategory]]. No text.
ENDS;
		$wikitext = $r->changeContent($wikitext);

		$this->assertContains('Newtestcategory', $wikitext);
	}

	function testRenameCategoryInQuery() {
		$r = new SRFRenameCategoryOperation("Testcategory", "NewTestcategory", true);
		$wikitext = <<<ENDS
This is a test
{{#ask: [[Category:Testcategory]][[Testproperty::+]] }}
Test text
ENDS;
		$wikitext = $r->changeContent($wikitext);

		$this->assertContains('NewTestcategory', $wikitext);

	}

	function testRenameCategoryInQuery2() {
		$r = new SRFRenameCategoryOperation("Testcategory", "NewTestcategory", true);
		$wikitext = <<<ENDS
This is a test
{{#ask: [[Testproperty::Category:Testcategory]] }}
Test text
ENDS;
		$wikitext = $r->changeContent($wikitext);

		$this->assertContains('NewTestcategory', $wikitext);

	}

	function testRenameCategoryInQuery3() {
		$r = new SRFRenameCategoryOperation("Testcategory", "NewTestcategory", true);
		$wikitext = <<<ENDS
This is a test
{{#ask: [[:Category:Testcategory]] }}
Test text
ENDS;
		$wikitext = $r->changeContent($wikitext);

		$this->assertContains('NewTestcategory', $wikitext);

	}



}