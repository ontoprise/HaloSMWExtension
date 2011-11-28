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
 * Test suite for triple store access.
 * Start the triple store with these options before running the test:
 * msgbroker=none client=MyStore driver=ontobroker-quad wsport=8090 console  reasoner=owl restfulws
 * 
 * @author thsc
 *
 */
class TestTripleStoreAccessSuite extends PHPUnit_Framework_TestSuite
{
	const GRAPH = "http://example.com/booksGraph";
	
	public static $mTriples = array(
		array("ex:HitchhikersGuide", "ex:title", "The Hitchhiker's Guide to the Galaxy", "xsd:string"),
		array("ex:HitchhikersGuide", "ex:price", "10.20", "xsd:double"),
		array("ex:HitchhikersGuide", "ex:pages", "224", "xsd:int"),
		array("ex:HitchhikersGuide", "ex:reallyCool", "true", "xsd:boolean"),
		array("ex:HitchhikersGuide", "ex:published", "1979-04-02T13:41:09+01:00", "xsd:dateTime"),
		array("ex:HitchhikersGuide", "ex:amazon", "http://www.amazon.com/Hitchhikers-Guide-Galaxy-25th-Anniversary/dp/1400052920/ref=sr_1_1?ie=UTF8&s=books&qid=1272987287&sr=1-1", "xsd:anyURI")
	);
	
	public static $mTriplesOptional = array(
		array("ex:DirkGently", "ex:title", "Dirk Gently's holistic detective agency", "xsd:string"),
	);
	
	public static function suite() {
		
		$suite = new TestTripleStoreAccessSuite();
		$suite->addTestSuite('TestTripleStoreAccess');
		$suite->addTestSuite('TestPersistentTripleStoreAccess');
		$suite->addTestSuite('TestPrefixManager');
		return $suite;
	}
	
	protected function setUp() {
    	
	}
	
	protected function tearDown() {
		$tsa = new TSCTripleStoreAccess();
		$tsa->dropGraph(self::GRAPH);
		$tsa->flushCommands();
	}

}


/**
 * This class test the TripleStoreAccess without persistence..
 * 
 * @author thsc
 *
 */
class TestTripleStoreAccess extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = FALSE;
		
    function setUp() {
    }

    function tearDown() {

    }

    /**
     * Tests the creation a TSCSourceDefinition object.
     */
    function testCreateTSA() {
    	$tsa = new TSCTripleStoreAccess();
    	$this->assertNotNull($tsa);
    }
    
    /**
     * Tests if the triples store is properly connected.
     */
    function testTSConnectionStatus() {
    	global $smwgHaloWebserviceEndpoint;
    	
    	$we = $smwgHaloWebserviceEndpoint;
    	$tsa = new TSCTripleStoreAccess();
    	
    	// Verify that connection with TS fails with invalid connections settings 
    	$smwgHaloWebserviceEndpoint = 'localhost:1234'; 
    	$connected = $tsa->isConnected();
    	$this->assertFalse($connected);
    	
    	// Verify a proper connection
    	$smwgHaloWebserviceEndpoint = $we; 
    	$connected = $tsa->isConnected();
    	$this->assertTrue($connected);
    	
    	
    }
    
    
    /**
     * Tests operations on the triple store.
     *
     */
    function testTripleStore() {
		
    	$namespace = "http://example.com/";
    	$prefixes = "PREFIX ex:<$namespace> ".
    			  TSNamespaces::getW3CPrefixes();
    	$triples = array();
		foreach (TestTripleStoreAccessSuite::$mTriples as $t) {		
			$triples[] = new TSCTriple($t[0], $t[1], $t[2], $t[3]);
		}
		$graph = TestTripleStoreAccessSuite::GRAPH;
		
		// Inserts triples into the triple store
		$tsa = new TSCTripleStoreAccess();
		$tsa->addPrefixes($prefixes);
		$tsa->createGraph($graph);
		$tsa->insertTriples($graph, $triples);
		
		//***testTripleStore#1***
		// Test if SPARUL commands can be sent to triple store
		$this->assertTrue($tsa->flushCommands(), "***testTripleStore#1*** failed.");
		
		// Query inserted triples
		$query = $prefixes."SELECT ?s ?p ?o FROM <$graph> WHERE { ?s ?p ?o . }";
		
		$result = $tsa->queryTripleStore($query, $graph);
		
		//***testTripleStore#2***
		// Test if all expected variables are present
		$variables = $result->getVariables();
		$this->assertContains("s", $variables, "***testTripleStore#2.1*** failed");
		$this->assertContains("p", $variables, "***testTripleStore#2.2*** failed");
		$this->assertContains("o", $variables, "***testTripleStore#2.3*** failed");
		
		//***testTripleStore#3***
		// Test if all expected triples are present
		foreach (TestTripleStoreAccessSuite::$mTriples as $t) {
			$prop = str_replace("ex:", $namespace, $t[1]);
			$row = $result->getRowsWhere("p", $prop);
			$this->assertEquals(1, count($row), "***testTripleStore#3.1*** failed");
			$row = $row[0];
			$this->assertEquals($row->getResult("s")->getValue(), 
								str_replace("ex:", $namespace, $t[0]),
								"***testTripleStore#3.2*** failed");
			$this->assertEquals($row->getResult("o")->getValue(), 
								$t[2],
								"***testTripleStore#3.3*** failed");
		}
		
		//***testTripleStore#4***
		// Test if queries with OPTIONAL parts work
		// Insert an underspecified book into the graph
    	$triples = array();
		foreach (TestTripleStoreAccessSuite::$mTriplesOptional as $t) {		
			$triples[] = new TSCTriple($t[0], $t[1], $t[2], $t[3]);
		}
		$graph = TestTripleStoreAccessSuite::GRAPH;
		
		// Inserts triples into the triple store
		$tsa = new TSCTripleStoreAccess();
		$tsa->addPrefixes($prefixes);
		$tsa->insertTriples($graph, $triples);
		
		//***testTripleStore#4.1***
		// Test if SPARUL commands can be sent to triple store
		$this->assertTrue($tsa->flushCommands(), "***testTripleStore#4.1*** failed.");
		
		// Query inserted triples
		$query = $prefixes.<<<SPARQL
SELECT ?book ?title ?price 
FROM <$graph> 
WHERE { 
	?book ex:title ?title .
	OPTIONAL {
		?book ex:price ?price . 
	} 
}
SPARQL;
		
		$result = $tsa->queryTripleStore($query, $graph);
		
		//***testTripleStore#2***
		// Test if all expected variables are present
		$variables = $result->getVariables();
		$this->assertContains("book", $variables, "***testTripleStore#4.2*** failed");
		$this->assertContains("title", $variables, "***testTripleStore#4.3*** failed");
		$this->assertContains("price", $variables, "***testTripleStore#4.4*** failed");
		
		//***testTripleStore#4.5***
		// Test if all expected triples are present
		$expectedTriples = array(
			array("ex:HitchhikersGuide", "title", "The Hitchhiker's Guide to the Galaxy"),
			array("ex:HitchhikersGuide", "price", "10.20"),
			array("ex:DirkGently", "title", "Dirk Gently's holistic detective agency")
		);
		
		
		$rows = $result->getRows();
		$this->assertEquals(2, count($rows), "***testTripleStore#4.5.1*** failed");
		foreach ($expectedTriples as $ep) {
			$title = str_replace("ex:", $namespace, $ep[0]);
			$rows = $result->getRowsWhere("book", $title);
			$this->assertEquals(1, count($rows), "***testTripleStore#4.5.2*** failed");
			$row = $rows[0];
			$this->assertEquals($row->getResult($ep[1])->getValue(), $ep[2],
								"***testTripleStore#4.5.3*** failed");
		}
		
		//***testTripleStore#5***
		// Test if triples can be deleted
		
		$prop = TestTripleStoreAccessSuite::$mTriples[0][1];
		$tsa->addPrefixes($prefixes);
		$tsa->deleteTriples($graph, "?s $prop ?o", "?s $prop ?o");
		$this->assertTrue($tsa->flushCommands(), "***testTripleStore#5.1*** failed.");
		$query = $prefixes."SELECT ?s ?o FROM <$graph> WHERE { ?s $prop ?o . }";
		
		$result = $tsa->queryTripleStore($query, $graph);
		// Make sure the triple is deleted.
		$this->assertEquals(0, count($result->getRows()), "***testTripleStore#5.2*** failed.");

		// Make sure that another triple is still available
		$prop = TestTripleStoreAccessSuite::$mTriples[1][1];
		$query = $prefixes."SELECT ?s ?o FROM <$graph> WHERE { ?s $prop ?o . }";
		$result = $tsa->queryTripleStore($query, $graph);
		$this->assertNotNull($result, "***testTripleStore#5.3*** failed.");
		$this->assertEquals(1, count($result->getRows()), "***testTripleStore#5.4*** failed.");
		
		//***testTripleStore#6***
		// Test if the complete graph can be deleted.
		$tsa->dropGraph($graph);
		$tsa->flushCommands();
		
		$query = $prefixes."SELECT ?s ?p ?o FROM <$graph> WHERE { ?s ?p ?o . }";
		
		$result = $tsa->queryTripleStore($query, $graph);
		// Make sure the graph is deleted.
		$this->assertTrue($result == null || count($result->getRows()) == 0, "***testTripleStore#6*** failed.");
		
    }
    
}


/**
 * This class test the TripleStoreAccess with persistence.
 * 
 * @author thsc
 *
 */
class TestPersistentTripleStoreAccess extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = FALSE;
		
    function setUp() {
    }

    function tearDown() {
		TSCStorage::getDatabase()->deleteAllPersistentTriples();
    }
    
    /**
     * The persistent triple store access is implemented in the class 
     * TSCPersistentTripleStoreAccess. Check if this class can be created.
     */
    function testCreatePTSA() {
    	$ptsa = new TSCPersistentTripleStoreAccess();
    	$this->assertNotNull($ptsa);
    	
    }
    
    /**
     * Persistent data for the triple store is stored in a MySQL table.
     * Check if the table exists.
     * 
     */
    function testPersistenceDB() {
    	$tableName = 'lod_triple_persistence';
    	
		$db =& wfGetDB( DB_SLAVE );
		$sql = "show tables like '$tableName';";

		$res = $db->query($sql);
		$num = $res->numRows();
		$db->freeResult($res);
		
		$this->assertEquals(1, $num, "The database table for persistent triples does not exist.");
    }
    
    /**
     * Check if triples that are added to the triple store are stored in the
     * database.
     */
    function testPersistTriples() {
    	$ptsa = new TSCPersistentTripleStoreAccess();
		
    	$namespace = "http://example.com/";
    	$prefixes = "PREFIX ex:<$namespace> ".
    			  TSNamespaces::getW3CPrefixes();
    	$triples = array();
		foreach (TestTripleStoreAccessSuite::$mTriples as $t) {		
			$triples[] = new TSCTriple($t[0], $t[1], $t[2], $t[3]);
		}
		$graph = TestTripleStoreAccessSuite::GRAPH;
		
		// Inserts triples into the triple store
		$ptsa->addPrefixes($prefixes);
		$ptsa->createGraph($graph);
		$ptsa->insertTriples($graph, $triples);
		
		//***testPersistentTripleStore#1***
		// Test if SPARUL commands can be sent to triple store
		$this->assertTrue($ptsa->flushCommands("TestTripleStoreAccess", "ID-1"), "***testPersistentTripleStore#1*** failed.");
		
		
		//***testPersistentTripleStore#2***
		// Test if the database contains the expected content
		
		$expected = <<<EXP
@prefix ex: <http://example.com/> .
@prefix xsd: <http://www.w3.org/2001/XMLSchema#> .
@prefix owl: <http://www.w3.org/2002/07/owl#> .
@prefix rdfs: <http://www.w3.org/2000/01/rdf-schema#> .
@prefix rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> .

<http://example.com/booksGraph> {
	ex:HitchhikersGuide ex:title "The Hitchhiker's Guide to the Galaxy"^^xsd:string . 
	ex:HitchhikersGuide ex:price "10.20"^^xsd:double . 
	ex:HitchhikersGuide ex:pages "224"^^xsd:int . 
	ex:HitchhikersGuide ex:reallyCool "true"^^xsd:boolean . 
	ex:HitchhikersGuide ex:published "1979-04-02T13:41:09+01:00"^^xsd:dateTime . 
	ex:HitchhikersGuide ex:amazon "http://www.amazon.com/Hitchhikers-Guide-Galaxy-25th-Anniversary/dp/1400052920/ref=sr_1_1?ie=UTF8&s=books&qid=1272987287&sr=1-1"^^xsd:anyURI . 
}
EXP;
		$this->compareContent("TestTripleStoreAccess", "ID-1", $expected, "***testPersistentTripleStore#2*** failed.");
		
    }

    /**
     * Check if triples that are added to the triple store are stored in the
     * database. Different graphs, sets of triples and IDs are tested
     */
    function testPersistTriples2() {
    	$ptsa = new TSCPersistentTripleStoreAccess();
		
    	$namespace = "http://example.com/";
    	$prefixes = "PREFIX ex:<$namespace> ".
    			  TSNamespaces::getW3CPrefixes();
    	$triples = array();
		foreach (TestTripleStoreAccessSuite::$mTriples as $t) {		
			$triples[] = new TSCTriple($t[0], $t[1], $t[2], $t[3]);
		}
		$graph = TestTripleStoreAccessSuite::GRAPH;
		
		// Inserts triples into the triple store
		$ptsa->addPrefixes($prefixes);
		$ptsa->createGraph($graph);
		$ptsa->insertTriples($graph, $triples);
		
		//***testPersistTriples2#1***
		// Test if SPARUL commands can be sent to triple store
		$this->assertTrue($ptsa->flushCommands("TestTripleStoreAccess", "ID-2"), "***testPersistTriples2#1*** failed.");

		$graph = "http://example.com/anotherBooksGraph";
		
		// Inserts triples into the triple store
		$ptsa->addPrefixes($prefixes);
		$ptsa->createGraph($graph);
		// Insert all triples separately
		$triples = array();
		foreach (TestTripleStoreAccessSuite::$mTriples as $t) {		
			$triples[0] = new TSCTriple($t[0], $t[1], $t[2], $t[3]);
			$ptsa->insertTriples($graph, $triples);
		}
		
		//***testPersistTriples2#2***
		// Test if SPARUL commands can be sent to triple store
		$this->assertTrue($ptsa->flushCommands("TestTripleStoreAccess", "ID-2"), "***testPersistTriples2#2*** failed.");
		
		
		//***testPersistTriples2#3***
		// Test if the database contains the expected content
		
		$expected = <<<EXP
@prefix ex: <http://example.com/> .
@prefix xsd: <http://www.w3.org/2001/XMLSchema#> .
@prefix owl: <http://www.w3.org/2002/07/owl#> .
@prefix rdfs: <http://www.w3.org/2000/01/rdf-schema#> .
@prefix rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> .

<http://example.com/booksGraph> {
	ex:HitchhikersGuide ex:title "The Hitchhiker's Guide to the Galaxy"^^xsd:string . 
	ex:HitchhikersGuide ex:price "10.20"^^xsd:double . 
	ex:HitchhikersGuide ex:pages "224"^^xsd:int . 
	ex:HitchhikersGuide ex:reallyCool "true"^^xsd:boolean . 
	ex:HitchhikersGuide ex:published "1979-04-02T13:41:09+01:00"^^xsd:dateTime . 
	ex:HitchhikersGuide ex:amazon "http://www.amazon.com/Hitchhikers-Guide-Galaxy-25th-Anniversary/dp/1400052920/ref=sr_1_1?ie=UTF8&s=books&qid=1272987287&sr=1-1"^^xsd:anyURI . 
}

@prefix ex: <http://example.com/> .
@prefix xsd: <http://www.w3.org/2001/XMLSchema#> .
@prefix owl: <http://www.w3.org/2002/07/owl#> .
@prefix rdfs: <http://www.w3.org/2000/01/rdf-schema#> .
@prefix rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> .

<http://example.com/anotherBooksGraph> {
	ex:HitchhikersGuide ex:title "The Hitchhiker's Guide to the Galaxy"^^xsd:string . 
	ex:HitchhikersGuide ex:price "10.20"^^xsd:double . 
	ex:HitchhikersGuide ex:pages "224"^^xsd:int . 
	ex:HitchhikersGuide ex:reallyCool "true"^^xsd:boolean . 
	ex:HitchhikersGuide ex:published "1979-04-02T13:41:09+01:00"^^xsd:dateTime . 
	ex:HitchhikersGuide ex:amazon "http://www.amazon.com/Hitchhikers-Guide-Galaxy-25th-Anniversary/dp/1400052920/ref=sr_1_1?ie=UTF8&s=books&qid=1272987287&sr=1-1"^^xsd:anyURI . 
}
EXP;
		$this->compareContent("TestTripleStoreAccess", "ID-2", $expected, "***testPersistTriples2#3*** failed.");
		
    }


    /**
     * Check if triples that were added persistently to the triple store are 
     * deleted from the database and the triple store. 
     */
    function testDeletePersistentTriples() {
    	$ptsa = new TSCPersistentTripleStoreAccess();
		
    	$namespace = "http://example.com/";
    	$prefixes = "PREFIX ex:<$namespace> ".
    			  TSNamespaces::getW3CPrefixes();
    	$triples = array();
		foreach (TestTripleStoreAccessSuite::$mTriples as $t) {		
			$triples[] = new TSCTriple($t[0], $t[1], $t[2], $t[3]);
		}
		$graph = TestTripleStoreAccessSuite::GRAPH;
		
		// Inserts triples into the triple store
		$ptsa->addPrefixes($prefixes);
		$ptsa->createGraph($graph);
		$ptsa->insertTriples($graph, $triples);
		
		//***testDeletePersistentTriples#1***
		// Test if SPARUL commands can be sent to triple store
		$this->assertTrue($ptsa->flushCommands("TestTripleStoreAccess", "ID-1"),
				 "***testDeletePersistentTriples#1*** failed.");

		// Inserts triples into the triple store
		$graph = "http://example.com/anotherBooksGraph";
		$ptsa->addPrefixes($prefixes);
		$ptsa->createGraph($graph);
		$ptsa->insertTriples($graph, $triples);
		
		//***testDeletePersistentTriples#2***
		// Test if SPARUL commands can be sent to triple store
		$this->assertTrue($ptsa->flushCommands("TestTripleStoreAccess", "ID-2"),
				"***testDeletePersistentTriples#2*** failed.");
		
		$graph = "http://example.com/yetAnotherBooksGraph";
		
		// Inserts triples into the triple store
		$ptsa->addPrefixes($prefixes);
		$ptsa->createGraph($graph);
		// Insert all triples separately
		$triples = array();
		foreach (TestTripleStoreAccessSuite::$mTriples as $t) {		
			$triples[0] = new TSCTriple($t[0], $t[1], $t[2], $t[3]);
			$ptsa->insertTriples($graph, $triples);
		}
		
		//***testDeletePersistentTriples#3***
		// Test if SPARUL commands can be sent to triple store
		$this->assertTrue($ptsa->flushCommands("TestTripleStoreAccess", "ID-2"),
				"***testDeletePersistentTriples#3*** failed.");
		
		//***testDeletePersistentTriples#4***
		// Test if all triples for TestTripleStoreAccess,ID-1 can be deleted
		$ptsa->deletePersistentTriples("TestTripleStoreAccess", "ID-1");
		
		// Test if the persisted triples are deleted from the triple store
		$graph = TestTripleStoreAccessSuite::GRAPH;
		$query = $prefixes."SELECT ?s ?p ?o FROM <$graph> WHERE { ?s ?p ?o . }";
		$result = $ptsa->queryTripleStore($query, $graph);
		// please note that this test will fail until
		// http://smwforum.ontoprise.com/smwbugs/show_bug.cgi?id=12784
		// has been implemented.
		$this->assertEquals(0, count($result->getRows()), 
				"Triples were not deleted from the triple store for $graph.");
		
		// Test if the database contains the expected content i.e. no more triples
		// for ID-1
		$this->compareContent("TestTripleStoreAccess", "ID-1", "", 
				"***testDeletePersistentTriples#4.1*** failed.");
		
		$expected = <<<EXP
@prefix ex: <http://example.com/> .
@prefix xsd: <http://www.w3.org/2001/XMLSchema#> .
@prefix owl: <http://www.w3.org/2002/07/owl#> .
@prefix rdfs: <http://www.w3.org/2000/01/rdf-schema#> .
@prefix rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> .

<http://example.com/anotherBooksGraph> {
	ex:HitchhikersGuide ex:title "The Hitchhiker's Guide to the Galaxy"^^xsd:string . 
	ex:HitchhikersGuide ex:price "10.20"^^xsd:double . 
	ex:HitchhikersGuide ex:pages "224"^^xsd:int . 
	ex:HitchhikersGuide ex:reallyCool "true"^^xsd:boolean . 
	ex:HitchhikersGuide ex:published "1979-04-02T13:41:09+01:00"^^xsd:dateTime . 
	ex:HitchhikersGuide ex:amazon "http://www.amazon.com/Hitchhikers-Guide-Galaxy-25th-Anniversary/dp/1400052920/ref=sr_1_1?ie=UTF8&s=books&qid=1272987287&sr=1-1"^^xsd:anyURI . 
}

@prefix ex: <http://example.com/> .
@prefix xsd: <http://www.w3.org/2001/XMLSchema#> .
@prefix owl: <http://www.w3.org/2002/07/owl#> .
@prefix rdfs: <http://www.w3.org/2000/01/rdf-schema#> .
@prefix rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> .

<http://example.com/yetAnotherBooksGraph> {
	ex:HitchhikersGuide ex:title "The Hitchhiker's Guide to the Galaxy"^^xsd:string . 
	ex:HitchhikersGuide ex:price "10.20"^^xsd:double . 
	ex:HitchhikersGuide ex:pages "224"^^xsd:int . 
	ex:HitchhikersGuide ex:reallyCool "true"^^xsd:boolean . 
	ex:HitchhikersGuide ex:published "1979-04-02T13:41:09+01:00"^^xsd:dateTime . 
	ex:HitchhikersGuide ex:amazon "http://www.amazon.com/Hitchhikers-Guide-Galaxy-25th-Anniversary/dp/1400052920/ref=sr_1_1?ie=UTF8&s=books&qid=1272987287&sr=1-1"^^xsd:anyURI . 
}
EXP;
		$this->compareContent("TestTripleStoreAccess", "ID-2", $expected, 
				"***testDeletePersistentTriples#4.2*** failed.");
		
		//***testDeletePersistentTriples#5***
		// Test if all triples for TestTripleStoreAccess,ID-2 can be deleted
		$ptsa->deletePersistentTriples("TestTripleStoreAccess", "ID-2");
		
		// Test if the database contains the expected content
		$this->compareContent("TestTripleStoreAccess", "ID-1", "", 
				"***testDeletePersistentTriples#5.1*** failed.");
		$this->compareContent("TestTripleStoreAccess", "ID-2", "", 
				"***testDeletePersistentTriples#5.2*** failed.");
		
		// Test if the persisted triples are deleted from the triple store
		$graph = "http://example.com/anotherBooksGraph";
		$query = $prefixes."SELECT ?s ?p ?o FROM <$graph> WHERE { ?s ?p ?o . }";
		$result = $ptsa->queryTripleStore($query, $graph);
		$this->assertEquals(0, count($result->getRows()), 
				"Triples were not deleted from the triple store for $graph.");
		
		$graph = "http://example.com/yetAnotherBooksGraph";
		$query = $prefixes."SELECT ?s ?p ?o FROM <$graph> WHERE { ?s ?p ?o . }";
		$result = $ptsa->queryTripleStore($query, $graph);
		$this->assertEquals(0, count($result->getRows()), 
				"Triples were not deleted from the triple store for $graph.");
				
		
		// Cleanup: delete the book graphs
		$graph = "http://example.com/anotherBooksGraph";
		$ptsa->dropGraph($graph);

		$graph = "http://example.com/yetAnotherBooksGraph";
		$ptsa->dropGraph($graph);
		
		$ptsa->flushCommands();
		
    }
    
    /**
     * Tests the method deleteTriples() of the persistent TSA. Expects an exception
     * to be thrown.
     */
    function testDeleteTriples() {
    	$this->setExpectedException('TSCTSAException');
    	$ptsa = new TSCPersistentTripleStoreAccess();
    	$ptsa->deleteTriples("", "", "");
    	
    }
    
    /**
     * Compares the content in the database for $component and $id with the $expected
     * result and prints the $errMsg if the strings do not match.
     */
    private function compareContent($component, $id, $expected, $errMsg) {
		
		// Read the generated TriG from the database
		$store = TSCStorage::getDatabase();
		$trigs = $store->readPersistentTriples($component, $id);
		$trig = "";
		foreach($trigs as $t) {
			$trig .= $t;
		}
		
		// Remove whitespaces
		$trig = preg_replace("/\s*/", "", $trig);
		$expected = preg_replace("/\s*/", "", $expected);
		
		$this->assertEquals($expected, $trig, $errMsg);
		
    }
}


/**
 * This class test the prefix manager.
 * 
 * @author thsc
 *
 */
class TestPrefixManager extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = FALSE;
		
    function setUp() {
    }

    function tearDown() {
    }
    
    /**
     * Gets the singleton of the prefix manager
     */
    function testGetInstance() {
    	$pm = TSCPrefixManager::getInstance();
    	$this->assertNotNull($pm, "The instance of TSCPrefixManager could not be retrieved.");
    }
    
    /**
     * Tests getting some namespace URIs for prefixes from the prefix manager.
     */
    function testGetNamespaceURIs() {
    	$pm = TSCPrefixManager::getInstance();
    	$p = $pm->getNamespaceURI("xsd");
    	$this->assertEquals("http://www.w3.org/2001/XMLSchema#", $p);
    	$p = $pm->getNamespaceURI("unknown");
    	$this->assertNull($p);
    	
    	$pm->addPrefix("ex", "http://example.com/");
    	$p = $pm->getNamespaceURI("ex");
    	$this->assertEquals("http://example.com/", $p);
    	
    }
    
    /**
     * Tests the methof TSCPrefixManager::makeAbsoluteURI.
     */
    function testMakeAbsoluteURI() {
    	$pm = TSCPrefixManager::getInstance();
    	$pm->addPrefix("ex", "http://example.com/");

    	$auri = $pm->makeAbsoluteURI("ex:HitchhikersGuide");
    	$this->assertEquals("<http://example.com/HitchhikersGuide>", $auri);
    	
    	$exceptionCaught = false;
    	try {
    		$auri = $pm->makeAbsoluteURI("exa:HitchhikersGuide");
    	} catch (TSCPrefixManagerException $e) {
			$this->assertEquals(TSCPrefixManagerException::UNKNOWN_PREFIX_IN_URI,
								$e->getCode(), "Expected exception UNKNOWN_PREFIX_IN_URI");
			$exceptionCaught = true; 		
    	}
    	if (!$exceptionCaught) {
    		$this->fail("An expected exception was not caught: TSCPrefixManagerException::UNKNOWN_PREFIX");
    	}

    	$exceptionCaught = false;
    	try {
    		$auri = $pm->makeAbsoluteURI("HitchhikersGuide");
    	} catch (TSCPrefixManagerException $e) {
			$this->assertEquals(TSCPrefixManagerException::MISSING_COLON,
								$e->getCode(), "Expected exception MISSING_COLON");    		
			$exceptionCaught = true; 		
    	}
    	if (!$exceptionCaught) {
    		$this->fail("An expected exception was not caught: TSCPrefixManagerException::MISSING_COLON");
    	}
								    	
    }
}
