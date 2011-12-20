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

if ( isset( $_SERVER ) && array_key_exists( 'REQUEST_METHOD', $_SERVER ) ) {
	die( "This script must be run from the command line\n" );
}

global $sgagIP;
require_once( $sgagIP . '/includes/SGA_GardeningIssues.php');
require_once $sgagIP . '/includes/bots/SGA_ImportOntologyBot.php';

class TestImportOntologyBot extends PHPUnit_Framework_TestCase {

	var $bot;

	function setUp() {
		$this->bot = new ImportOntologyBot();
		$this->bot->initializeRAP();
		// do not run bot
	}

	function tearDown() {

	}

	function testImport_Family() {

		$this->bot->testOntologyImport("testcases/resources/family.owl");
		$ws = $this->bot->getWikiStatements();
		$this->assertEquals(28, count($this->bot->getWikiStatements()));

	}

	function testImport_Pizza() {

		$this->bot->testOntologyImport("testcases/resources/pizza_latest.owl");
		$ws = $this->bot->getWikiStatements();
		$this->assertEquals(308, count($this->bot->getWikiStatements()));

	}

	function testImport_Products() {

		$this->bot->testOntologyImport("testcases/resources/products.owl");
		$ws = $this->bot->getWikiStatements();
		$this->assertEquals(56, count($this->bot->getWikiStatements()));

	}

	function testImport_SWRC() {
		$this->bot->testOntologyImport("testcases/resources/swrc_updated_v0.7.1.owl");
		$ws = $this->bot->getWikiStatements();
		$this->assertEquals(1195, count($this->bot->getWikiStatements()));
	}

	function testImport_Travel() {

		 $this->bot->testOntologyImport("testcases/resources/travel.owl");
		 $ws = $this->bot->getWikiStatements();
		 $this->assertEquals(362, count($this->bot->getWikiStatements()));

	}

	function replaceWhitespaces(& $ws) {
		for($j = 0; $j < count($ws); $j++) {
			for($i = 0; $i < count($ws[$j]['WIKI']); $i++) {
				$ws[$j]['WIKI'][$i] = str_replace("\n", "", $ws[$j]['WIKI'][$i]);
				$ws[$j]['WIKI'][$i] = str_replace("\r", "", $ws[$j]['WIKI'][$i]);
				$ws[$j]['WIKI'][$i] = trim($ws[$j]['WIKI'][$i]);

			}
		}
	}

	function createAssertions() {
		$i = 0;
		$handle = fopen("output1","w");
		foreach($ws as $s) {
			fwrite($handle, '$this->assertEquals($ws['.$i.']["NS"], '.$s["NS"].");\n");
			foreach($s['WIKI'] as $wm) {
				fwrite($handle,  '$this->assertContains(\''.$wm.'\', $ws['.$i.']["WIKI"]);'."\n");
			}
			fwrite($handle,  '$this->assertEquals($ws['.$i.']["PAGENAME"], \''.$s["PAGENAME"]."');\n");
			fwrite($handle,  '$this->assertEquals($ws['.$i.']["ID"], \''.$s["ID"]."');\n");
			$i++;
		}
		fclose($handle);
	}

}


