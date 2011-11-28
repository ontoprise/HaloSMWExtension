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
 * @ingroup LinkedData_Tests
 */

define("LOD_ML_TEST_NS1", "http://www4.wiwiss.fu-berlin.de/R2Rmappings/");
define("LOD_ML_TEST_NS2", "http://www.example.org/smw-lde/smwTransformations/");

/**
 * This class tests the Mapping Language API
 * Author: Christian Becker
 */
class TestMappingLanguageAPI extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = FALSE;
	private $previousErrorLevel;

	function setUp() {
		global $previousErrorLevel;
		/*
		 * ARC2 throws some deprecation errors under PHP 5.3 so that PHPUnit fails the testcases.
		 * In the following, deprecated errors are temporarily ok'ed until ARC2 is updated
		 */
		$previousErrorLevel = error_reporting();
		error_reporting($previousErrorLevel^ E_DEPRECATED);
	}

	function tearDown() {
		global $previousErrorLevel;
		error_reporting($previousErrorLevel);
	}

	/**
	 * Verifies results for R2R mappings
	 */
	function testR2RMapping() {
		$mappings = LODMLMappingLanguageAPI::parse('testcases/resources/mappings.ttl');
		$this->assertFalse(empty($mappings));

		$this->assertArrayHasKey(LOD_ML_TEST_NS1 . "DBpediaToFoafPersonMapping", $mappings);		
		$m = $mappings[LOD_ML_TEST_NS1 . "DBpediaToFoafPersonMapping"];
		$this->assertType("LODMLClassMapping", $m);
		$this->assertEquals(LOD_ML_TEST_NS1 . "DBpediaToFoafPersonMapping", $m->getUri());
		$this->assertEquals("?SUBJ a dbpedia:Person", $m->getSourcePattern());
		$this->assertEquals(array("?SUBJ a foaf:Person"), $m->getTargetPatterns());
		$this->assertEquals(array(LOD_ML_RDF_TYPE), $m->getTargetProperties());		
		$this->assertEquals(array(), $m->getTransformations());
		
		$this->assertArrayHasKey(LOD_ML_TEST_NS1 . "labelToNameMapping", $mappings);		
		$m = $mappings[LOD_ML_TEST_NS1 . "labelToNameMapping"];
		$this->assertType("LODMLPropertyMapping", $m);
		$this->assertEquals(LOD_ML_TEST_NS1 . "labelToNameMapping", $m->getUri());
		$this->assertEquals($mappings[LOD_ML_TEST_NS1 . "DBpediaToFoafPersonMapping"], $m->getClassMapping());
		$this->assertEquals("?SUBJ rdfs:label ?o . FILTER(lang(?o)='en')", $m->getSourcePattern());
		$this->assertEquals(array("?SUBJ foaf:name ?o", "?SUBJ <http://www.w3.org/2006/vcard/ns#n> ?o"), $m->getTargetPatterns());
		$this->assertEquals(array("http://xmlns.com/foaf/0.1/name", "http://www.w3.org/2006/vcard/ns#n"), $m->getTargetProperties());		
		$this->assertEquals(array(), $m->getTransformations());
		
		$this->assertArrayHasKey(LOD_ML_TEST_NS1 . "concatFirstAndLastNameMapping", $mappings);		
		$m = $mappings[LOD_ML_TEST_NS1 . "concatFirstAndLastNameMapping"];
		$this->assertType("LODMLPropertyMapping", $m);
		$this->assertEquals(LOD_ML_TEST_NS1 . "concatFirstAndLastNameMapping", $m->getUri());
		$this->assertNull($m->getClassMapping());
		$this->assertEquals("?SUBJ foaf:firstName ?f . ?SUBJ foaf:lastName ?l", $m->getSourcePattern());
		$this->assertEquals(array("?SUBJ v:n ?name"), $m->getTargetPatterns());
		$this->assertEquals(array("http://www.w3.org/2006/vcard/ns#n"), $m->getTargetProperties());		
		$this->assertEquals(array("?name = concat(?l, ', ', ?f)"), $m->getTransformations());
	}
	
	/**
	 * Verifies complex target property lookup
	 */
	function testTargetProperties() {
		/* Test paths */
		$mappings = LODMLMappingLanguageAPI::parse('testcases/resources/Drugbank-to-Uniprot.r2r.ttl');
		$this->assertFalse(empty($mappings));
		$this->assertArrayHasKey(LOD_ML_TEST_NS2 . "GeneName", $mappings);		
		$m = $mappings[LOD_ML_TEST_NS2 . "GeneName"];
		$this->assertEquals(array("http://purl.uniprot.org/core/encodedBy", LOD_ML_RDF_TYPE, "http://www.w3.org/2004/02/skos/core#prefLabel"), $m->getTargetProperties());		
	}
	
	/**
	 * Verifies results for statement-based mappings
	 */
	function testStatementBasedMapping() {
		$mappings = LODMLMappingLanguageAPI::parse('testcases/resources/mappings.ttl');
		$this->assertFalse(empty($mappings));

		$this->assertArrayHasKey("http://xmlns.com/foaf/0.1/Person", $mappings);		
		$m = $mappings["http://xmlns.com/foaf/0.1/Person"];
		$this->assertType("LODMLEquivalentClassMapping", $m);
		$this->assertEquals("http://xmlns.com/foaf/0.1/Person", $m->getUri());
		$this->assertEquals("http://xmlns.com/foaf/0.1/Person", $m->getSourceEntity());
		$this->assertEquals("http://dbpedia.org/ontology/Person", $m->getTargetEntity());

		$this->assertArrayHasKey("http://data.linkedmdb.org/resource/movie/director", $mappings);		
		$m = $mappings["http://data.linkedmdb.org/resource/movie/director"];
		$this->assertType("LODMLEquivalentPropertyMapping", $m);
		$this->assertEquals("http://data.linkedmdb.org/resource/movie/director", $m->getUri());
		$this->assertEquals("http://data.linkedmdb.org/resource/movie/director", $m->getSourceEntity());
		$this->assertEquals("http://dbpedia.org/ontology/director", $m->getTargetEntity());
	}

}
