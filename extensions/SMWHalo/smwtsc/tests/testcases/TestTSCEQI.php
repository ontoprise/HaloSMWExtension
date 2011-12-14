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

global $smwgHaloIP;
require_once($smwgHaloIP.'/tests/testcases/TestEQI.php');

/**
 * @file
 * @ingroup SMWHaloTests
 *
 * Tests the external query interface which redirects the queries to the TSC.
 *
 * @author Kai Kühn
 *
 */
class TestTSCEQI extends TestEQI {


	function setUp() {
		$this->params = array('source' => 'tsc'); // change target for queries
	}

	function tearDown() {

	}

	/**
	 * Check general SPARQL query
	 */
	function testSPARQL() {
		$res = $this->makeCall("SELECT ?p WHERE { ?p rdf:type category:Person. }", $this->params);
		echo $res;

		$this->assertContains('<uri>http://publicbuild/ob/a/Hans</uri>', $res);
		$this->assertContains('<uri>http://publicbuild/ob/a/Kai</uri>', $res);
	}

	/**
	 * Check if abbreviated prefixes works
	 */
	function testSPARQL2() {
		$res = $this->makeCall("SELECT ?p ?o WHERE { ?p rdf:type cat:Person. OPTIONAL { ?p prop:Has_Engine ?o. } }", $this->params);
		echo $res;

		$this->assertContains('<uri>http://publicbuild/ob/a/Hans</uri>', $res);
		$this->assertContains('<uri>http://publicbuild/ob/a/Kai</uri>', $res);
		$this->assertContains('<uri>http://publicbuild/ob/a/3_cylinder</uri>', $res);
	}

	/**
	 * Check if property/category prefixes works
	 */
	function testSPARQL3() {
		$res = $this->makeCall("SELECT ?p ?o WHERE { ?p rdf:type category:Person. OPTIONAL { ?p property:Has_Engine ?o. } }", $this->params);
		echo $res;

		$this->assertContains('<uri>http://publicbuild/ob/a/Hans</uri>', $res);
		$this->assertContains('<uri>http://publicbuild/ob/a/Kai</uri>', $res);
		$this->assertContains('<uri>http://publicbuild/ob/a/3_cylinder</uri>', $res);
	}

	/**
	 * Check if additional parameters work.
	 *
	 */
	function testSPARQL4() {
		$this->params['limit'] = 1;
		$res = $this->makeCall("SELECT ?p ?o WHERE { ?p rdf:type category:Person. OPTIONAL { ?p prop:Has_Engine ?o. } } ORDER BY ASC(?p)", $this->params);
		echo $res;

		$this->assertContains('<uri>http://publicbuild/ob/a/Hans</uri>', $res);

	}

	/**
	 * Check new defined prefix.
	 *
	 */
	function testSPARQL5() {

		$res = $this->makeCall("PREFIX test:<http://publicbuild/ob/property/> SELECT ?p ?o WHERE { ?p rdf:type category:Person. OPTIONAL { ?p test:Has_Engine ?o. } } ", $this->params);
		echo $res;

		$this->assertContains('<uri>http://publicbuild/ob/a/Hans</uri>', $res);
		$this->assertContains('<uri>http://publicbuild/ob/a/Kai</uri>', $res);
		$this->assertContains('<uri>http://publicbuild/ob/a/3_cylinder</uri>', $res);

	}

	/**
	 * Check if predefined prefix can be overwritten.
	 */
	function testSPARQL6() {
		$res = $this->makeCall("PREFIX rdf:<http://www.w3.org/1999/02/22-rdf-syntax-ns#> SELECT ?p WHERE { ?p rdf:type category:Person. }", $this->params);
		echo $res;

		$this->assertContains('<uri>http://publicbuild/ob/a/Hans</uri>', $res);
		$this->assertContains('<uri>http://publicbuild/ob/a/Kai</uri>', $res);
	}
}
