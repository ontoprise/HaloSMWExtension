<?php
global $sgagIP;
require_once( $sgagIP . '/includes/SGA_GardeningIssues.php');
require_once '../includes/bots/SGA_ImportOntologyBot.php';

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
		$this->assertEquals(306, count($this->bot->getWikiStatements()));

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


