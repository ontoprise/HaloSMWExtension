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
require_once($srefgIP.'/includes/operations/SRF_ChangeValue.php');

class SRFTestChangeValue extends PHPUnit_Framework_TestCase {


	function setUp() {

	}

	function tearDown() {

	}


	function testChangeValue() {
		$r = new SMWRFChangeValueOperation(array("Testinstance"), "Testproperty", "Old", "New");
		$wikitext = <<<ENDS
This is a test [[Testproperty::old]]. No text.
ENDS;
		$wikitext = $r->changeContent($wikitext);

		$this->assertContains('Testproperty::New', $wikitext);
	}

	function testValueRemove() {
		$r = new SMWRFChangeValueOperation(array("Testinstance"), "Testproperty", "old", NULL);
		$wikitext = <<<ENDS
This is a test [[Testproperty::old]]. No text.
ENDS;
		$wikitext = $r->changeContent($wikitext);

		$this->assertNotContains('Testproperty::', $wikitext);
	}
	function testValueAdd() {
		$r = new SMWRFChangeValueOperation(array("Testinstance"), "Testproperty", NULL, "New");
		$wikitext = <<<ENDS
This is a test [[Testproperty::old]]. No text.
ENDS;
		$wikitext = $r->changeContent($wikitext);

		$this->assertContains('Testproperty::New', $wikitext);
	}

}