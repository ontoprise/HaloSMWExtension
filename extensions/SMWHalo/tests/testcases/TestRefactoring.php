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
global $smwgHaloIP;
require_once($smwgHaloIP.'/includes/refactoring/SMW_RF_RefactoringOperation.php');
require_once($smwgHaloIP.'/includes/refactoring/operations/SMW_RF_RenameProperty.php');
require_once($smwgHaloIP.'/includes/refactoring/operations/SMW_RF_RenameCategory.php');
require_once($smwgHaloIP.'/includes/refactoring/operations/SMW_RF_RenameInstance.php');
require_once($smwgHaloIP.'/DataAPI/PageCRUD_Plus/PCP.php');
require_once($smwgHaloIP.'/DataAPI/PageObjectModel/POM.php');

class TestRefactoring extends PHPUnit_Framework_TestCase {


	function setUp() {

	}

	function tearDown() {

	}

	function testRenameProperty() {
		$r = new SMWRFRenamePropertyOperation("Testproperty", "Newtestproperty", true);
		$wikitext = <<<ENDS
This is a test [[Testproperty::value1]]. No text.
ENDS;
		$wikitext = $r->changeContent('Test', $wikitext);
		$this->assertContains('Newtestproperty', $wikitext);

	}

	function testRenamePropertyAsValue() {
		$r = new SMWRFRenamePropertyOperation("Testproperty", "Newtestproperty", true);
		$wikitext = <<<ENDS
This is a test [[ABC::Property:Testproperty]]. No text.
ENDS;
		$wikitext = $r->changeContent('Test', $wikitext);
		$this->assertContains('Newtestproperty', $wikitext);

	}

	function testRenamePropertyAsLink() {
		$r = new SMWRFRenamePropertyOperation("Testproperty", "Newtestproperty", true);
		$wikitext = <<<ENDS
This is a test [[Property:Testproperty]]. No text.
ENDS;
		$wikitext = $r->changeContent('Test', $wikitext);
		$this->assertContains('Newtestproperty', $wikitext);
		
	}

	function testRenamePropertyInQuery() {
		$r = new SMWRFRenamePropertyOperation("Testproperty", "Newtestproperty", true);
		$wikitext = <<<ENDS
This is a test
{{#ask: [[Category:Testcategory]][[Testproperty::+]] }}
Test text
ENDS;
		$wikitext = $r->changeContent('Test', $wikitext);
		$this->assertContains('Newtestproperty', $wikitext);

	}

	function testRenameCategory() {
		$r = new SMWRFRenameCategoryOperation("Testcategory", "Newtestcategory", true);
		$wikitext = <<<ENDS
This is a test [[Category:Testcategory]]. No text.
ENDS;
		$wikitext = $r->changeContent('Test', $wikitext);
		
		$this->assertContains('Newtestcategory', $wikitext);
	}

	function testRenameCategoryAsValue() {
		$r = new SMWRFRenameCategoryOperation("Testcategory", "Newtestcategory", true);
		$wikitext = <<<ENDS
This is a test [[ABC::Category:Testcategory]]. No text.
ENDS;
		$wikitext = $r->changeContent('Test', $wikitext);
		
		$this->assertContains('Newtestcategory', $wikitext);
	}

	function testRenameCategoryAsLink() {
		$r = new SMWRFRenameCategoryOperation("Testcategory", "Newtestcategory", true);
		$wikitext = <<<ENDS
This is a test [[:Category:Testcategory]]. No text.
ENDS;
		$wikitext = $r->changeContent('Test', $wikitext);
		
		$this->assertContains('Newtestcategory', $wikitext);
	}
	
function testRenameCategoryInQuery() {
        $r = new SMWRFRenameCategoryOperation("Testcategory", "NewTestcategory", true);
        $wikitext = <<<ENDS
This is a test
{{#ask: [[Category:Testcategory]][[Testproperty::+]] }}
Test text
ENDS;
        $wikitext = $r->changeContent('Test', $wikitext);
        
        $this->assertContains('NewTestcategory', $wikitext);

    }
    
function testRenameCategoryInQuery2() {
        $r = new SMWRFRenameCategoryOperation("Testcategory", "NewTestcategory", true);
        $wikitext = <<<ENDS
This is a test
{{#ask: [[Testproperty::Category:Testcategory]] }}
Test text
ENDS;
        $wikitext = $r->changeContent('Test', $wikitext);
        
        $this->assertContains('NewTestcategory', $wikitext);

    }
    
function testRenameCategoryInQuery3() {
        $r = new SMWRFRenameCategoryOperation("Testcategory", "NewTestcategory", true);
        $wikitext = <<<ENDS
This is a test
{{#ask: [[:Category:Testcategory]] }}
Test text
ENDS;
        $wikitext = $r->changeContent('Test', $wikitext);
        
        $this->assertContains('NewTestcategory', $wikitext);

    }
	
function testInstanceAsLink() {
        $r = new SMWRFRenameInstanceOperation("Testinstance", "NewTestinstance", true);
        $wikitext = <<<ENDS
This is a test [[Testinstance]]. No text.
ENDS;
        $wikitext = $r->changeContent('Test', $wikitext);
        
        $this->assertContains('NewTestinstance', $wikitext);
    }
}