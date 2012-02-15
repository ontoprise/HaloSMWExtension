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
	
    function testGetAllBundle() {
        $om = new OntologyMerger();
       
        $wikiText = <<<ENDS
=== A wiki text ===
<!-- BEGIN ontology: bundleA -->some annotations...<!-- END ontology: bundleA -->
<!-- BEGIN ontology: bundleB-test -->some annotations...<!-- END ontology: bundleB-test -->
<!-- BEGIN ontology: bundleC test -->some annotations...<!-- END ontology: bundleC test -->
ENDS
        ;
        $actual = $om->getAllBundles($wikiText);
        
        $this->assertEquals("bundleA", trim($actual[0]));
        $this->assertEquals("bundleB-test", $actual[1]);
        $this->assertEquals("bundleC test", $actual[2]);
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
