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

/**
 * Test suite for the SPARQL parser and visitors of the parsed structure.
 * There are no prerequisites for running this test suite.
 * 
 * @author thsc
 *
 */
class TestSparqlParserSuite extends PHPUnit_Framework_TestSuite
{
	
	public static function suite() {
		
		$suite = new TestSparqlParserSuite();
		$suite->addTestSuite('TestSerializer');
		return $suite;
	}
	
	protected function setUp() {
	}
	
	protected function tearDown() {
	}
}



/**
 * This class tests the serializer of a parsed SPARQL query.
 * 
 * @author thsc
 *
 */
class TestSerializer extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = FALSE;
	private $mRequestURI;
		
    function setUp() {
    }

    function tearDown() {
    }

	/**
	 * Tests the serialization of a filter with an unary operator and a built-in
	 * call.
	 */
	function testSerializeQueryWithFilter1() {
		$query = <<<SPARQL
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX ex:  <http://example.com/book/>
		
SELECT DISTINCT ?a
WHERE {
	?a rdf:type ex:Author .
	FILTER (!REGEX(?a, "Douglas"))
}
SPARQL;

		$expected = <<<SPARQL
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX owl: <http://www.w3.org/2002/07/owl#>
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
PREFIX ex: <http://example.com/book/>

SELECT DISTINCT ?a 
WHERE {
	?a rdf:type ex:Author .
	FILTER (!regex(?a, "Douglas"))
}
SPARQL;
		
		
		$this->compareSerializedQuery($query, $expected);
	}
	
	/**
	 * Tests the serialization of a filter with a built-in call.
	 */
	function testSerializeQueryWithFilter2() {
		$query = <<<SPARQL
BASE <http://example.com/>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX ex:  <book/>
		
SELECT DISTINCT ?a
WHERE {
	?a rdf:type ex:Author .
	FILTER (REGEX(?a, "Douglas"))
}
SPARQL;

		$expected = <<<SPARQL
BASE <http://example.com/>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX owl: <http://www.w3.org/2002/07/owl#>
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
PREFIX ex: <book/>

SELECT DISTINCT ?a 
WHERE {
	?a rdf:type ex:Author .
	FILTER (regex(?a, "Douglas"))
}
SPARQL;
		
		$this->compareSerializedQuery($query, $expected);
	}

	/**
	 * Tests the serialization of a filter with a binary operation.
	 */
	function testSerializeQueryWithFilter3() {
		$query = <<<SPARQL
BASE <http://example.com/>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX ex:  <book/>
		
SELECT DISTINCT ?a ?p
WHERE {
	?a rdf:type ex:Author .
	FILTER (?p < 30)
}
SPARQL;

		$expected = <<<SPARQL
BASE <http://example.com/>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX owl: <http://www.w3.org/2002/07/owl#>
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
PREFIX ex: <book/>

SELECT DISTINCT ?a ?p
WHERE {
	?a rdf:type ex:Author .
	FILTER (?p < "30"^^xsd:integer)
}
SPARQL;
		
		$this->compareSerializedQuery($query, $expected);
	}
	
	/**
	 * Tests the serialization of a graph with variable.
	 */
	function testSerializeQueryWithGraphVariable() {
		$query = <<<SPARQL
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX ex: <http://example.com/book/>
		
SELECT DISTINCT ?a ?p
WHERE {
	GRAPH ?g {
		?a rdf:type ex:Author .
	}
}
SPARQL;

		$expected = <<<SPARQL
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX owl: <http://www.w3.org/2002/07/owl#>
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
PREFIX ex: <http://example.com/book/>

SELECT DISTINCT ?a ?p
WHERE {
	GRAPH ?g {
		?a rdf:type ex:Author .
	}
}
SPARQL;
		
		$this->compareSerializedQuery($query, $expected);
	}
	
	/**
	 * Tests the serialization of a graph with variable.
	 */
	function testSerializeQueryWithSelectAll() {
		$query = <<<SPARQL
SELECT *
WHERE {
	?a ?b ?c .
}
SPARQL;

		$expected = <<<SPARQL
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX owl: <http://www.w3.org/2002/07/owl#>
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>

SELECT *
WHERE {
	?a ?b ?c .
}
SPARQL;
		
		$this->compareSerializedQuery($query, $expected);
	}
	
	/**
	 * Tests the serialization of a query with blank nodes.
	 */
	function testSerializeQueryWithBlankNodes() {
		$query = <<<SPARQL
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX owl: <http://www.w3.org/2002/07/owl#>
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
PREFIX ex: <http://example.com/>

SELECT ?a
WHERE {
	?a ex:ownsBook _:1 .
	_:1 ex:hasTitle ex:HitchhikersGuide .
	_:1 ex:inStoreAt _:2 .
	_:2 ex:name  ex:Amazon .
	_:2 ex:price "10.20"^^xsd:double .
	_:1 ex:pages "224"^^xsd:int .
}
SPARQL;

		$expected = <<<SPARQL
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX owl: <http://www.w3.org/2002/07/owl#>
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
PREFIX ex: <http://example.com/>

SELECT ?a
WHERE {
	?a ex:ownsBook _:1 .
	_:1 ex:hasTitle ex:HitchhikersGuide .
	_:1 ex:inStoreAt _:2 .
	_:2 ex:name  ex:Amazon .
	_:2 ex:price "10.20"^^xsd:double .
	_:1 ex:pages "224"^^xsd:int .
}
SPARQL;
		
		$this->compareSerializedQuery($query, $expected);
	}
	
	
    /**
     * Checks if the meta data for a query is delivered for the triples of a 
     * query result on the level of SPARQL XML.
     */
	function testSerializeQuery() {
		
		$query = <<<SPARQL
BASE <http://example.com/>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
PREFIX ex:  <book/>

SELECT DISTINCT ?a ?p
FROM <http://example.com/authorGraph>
FROM NAMED  <http://example.com/bookGraph>
WHERE {
	GRAPH <http://example.com/authorGraph> {
		?a rdf:type ex:Author .
		?a ex:nationality "english"^^xsd:string .
		?a ex:authorOf ?b .
		?b ex:price ?p .
	}
	{
		?a rdf:type ex:Author .
	} UNION {
		?a ex:nationality "english"^^xsd:string .
	} UNION {
		?a ex:authorOf ?b .
	}
	OPTIONAL {
		?b ex:price ?p .
	}
	FILTER (!REGEX(?a, "Douglas"))
 
}
SPARQL;

		$expected = <<<SPARQL
BASE <http://example.com/>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX owl: <http://www.w3.org/2002/07/owl#>
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
PREFIX ex: <book/>
		
SELECT DISTINCT ?a ?p
FROM <http://example.com/authorGraph>
FROM NAMED  <http://example.com/bookGraph>
WHERE {
	GRAPH <http://example.com/authorGraph> {
		?a rdf:type ex:Author .
		?a ex:nationality "english"^^xsd:string .
		?a ex:authorOf ?b .
		?b ex:price ?p .
	}
	{
		?a rdf:type ex:Author .
	} UNION {
		?a ex:nationality "english"^^xsd:string .
	} UNION {
		?a ex:authorOf ?b .
	}
	OPTIONAL {
		?b ex:price ?p .
	}
	FILTER (!regex(?a, "Douglas"))
 
}
SPARQL;

		
		$this->compareSerializedQuery($query, $expected);
	}
	
	private function compareSerializedQuery($query, $expected) {
		// Parse the query
		$parser = new TSCSparqlQueryParser($query);
		
		// Serialize the parsed query
		$serializer = new TSCSparqlSerializerVisitor();
		$parser->visitQuery($serializer);
		$s = $serializer->getSerialization();
		
		// Compare original query and expected result (without whitespaces)
		$s = preg_replace("/\s*/", "", $s);
		$expected = preg_replace("/\s*/", "", $expected);
		
		$this->assertEquals($expected, $s);
	}
	
}
	
