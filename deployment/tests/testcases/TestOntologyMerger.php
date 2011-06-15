<?php

require_once ('deployment/io/import/DF_OntologyMerger.php');
require_once ('deployment/languages//DF_Language.php');
/**
 * Tests the OntologyMerger tool
 *
 */
class TestOntologyMerger extends PHPUnit_Framework_TestCase {

	var $om;

	function setUp() {
		global $wgLanguageCode, $dfgLang;
		$langClass = "DF_Language_$wgLanguageCode";
		if (!file_exists("../languages/$langClass.php")) {
			$langClass = "DF_Language_En";
		}
		require_once("../languages/$langClass.php");
		$dfgLang = new $langClass();
	}

	function tearDown() {

	}

	function testAddBundle() {
		$om = new OntologyMerger();
		$bundleID = "wiki1";
		$wikiText = <<<ENDS
=== A wiki text ===
Loremp ipsum
ENDS
		;
		$actual = $om->addBundle($bundleID, $wikiText);
		$this->assertContains("<!-- BEGIN ontology: $bundleID -->", $actual);
		$this->assertContains("<!-- END ontology: $bundleID -->", $actual);
	}

	function testContainsBundle() {
		$om = new OntologyMerger();
		$bundleID = "wiki1";
		$wikiText = <<<ENDS
=== A wiki text ===
<!-- BEGIN ontology: $bundleID -->some annotations...<!-- END ontology: $bundleID -->
ENDS
		;
		$actual = $om->containsBundle($bundleID, $wikiText);
		$this->assertTrue($actual);
	}

	function testRemoveBundle() {
		$om = new OntologyMerger();
		$bundleID = "wiki1";
		$wikiText = <<<ENDS
=== A wiki text ===
<!-- BEGIN ontology: $bundleID -->
some annotations...
<!-- END ontology: $bundleID -->
ENDS
		;
		$actual = $om->removeBundle($bundleID, $wikiText);
		$this->assertNotContains("BEGIN ontology: $bundleID", $actual);
	}

	function testGetSemanticData() {
		$om = new OntologyMerger();
		$bundleID = "wiki1";
		$wikiText = <<<ENDS
=== A wiki text ===
<!-- BEGIN ontology: $bundleID -->
[[Has domain and range::Category:Person]] [[Category:Transitive property]]
<!-- END ontology: $bundleID -->
ENDS
		;
		$actual = $om->getSemanticData($bundleID, $wikiText);
	
		$this->assertArrayHasKey(0, $actual);
		$this->assertArrayHasKey(1, $actual);
		list($properties, $categories) = $actual;
		$this->assertEquals($properties[0][0], "Has domain and range");
		$this->assertEquals($properties[0][1], "Category:Person");
		$this->assertEquals($categories[0], "Transitive property");
		
	}

}