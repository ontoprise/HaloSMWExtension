<?php
/**
 * @file
 * @ingroup LinkedData_Tests
 */

require_once 'PHPUnit/Framework.php';

class TestTripleStoreAccess extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = FALSE;
	
	private $mTriples = array(
		array("ex:HitchhickersGuide", "ex:title", "The Hitchhiker's Guide to the Galaxy", "xsd:string"),
		array("ex:HitchhickersGuide", "ex:price", "10.20", "xsd:double"),
		array("ex:HitchhickersGuide", "ex:pages", "224", "xsd:int"),
		array("ex:HitchhickersGuide", "ex:reallyCool", "true", "xsd:boolean"),
		array("ex:HitchhickersGuide", "ex:published", "1979-04-02T13:41:09+01:00", "xsd:dateTime"),
		array("ex:HitchhickersGuide", "ex:amazon", "http://www.amazon.com/Hitchhikers-Guide-Galaxy-25th-Anniversary/dp/1400052920/ref=sr_1_1?ie=UTF8&s=books&qid=1272987287&sr=1-1", "xsd:anyURI")
	);
	
    function setUp() {
    }

    function tearDown() {

    }

    /**
     * Tests the creation a LODSourceDefinition object.
     */
    function testCreateTSA() {
    	$tsa = new LODTripleStoreAccess();
    	$this->assertNotNull($tsa);
    }
    
    /**
     * Tests if the triples store is properly connected.
     */
    function testTSConnectionStatus() {
    	global $smwgWebserviceEndpoint;
    	
    	$we = $smwgWebserviceEndpoint;
    	$tsa = new LODTripleStoreAccess();
    	
    	// Verify that connection with TS fails with invalid connections settings 
    	$smwgWebserviceEndpoint = 'localhost:1234'; 
    	$connected = $tsa->isConnected();
    	$this->assertFalse($connected);
    	
    	// Verify a proper connection
    	$smwgWebserviceEndpoint = $we; 
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
		foreach ($this->mTriples as $t) {		
			$triples[] = new LODTriple($t[0], $t[1], $t[2], $t[3]);
		}
		$graph = "http://example.com/booksGraph";
		
		// Inserts triples into the triple store
		$tsa = new LODTripleStoreAccess();
		$tsa->addPrefixes($prefixes);
		$tsa->createGraph($graph);
		$tsa->insertTriples($graph, $triples);
		
		//***testTripleStore#1***
		// Test if SPARUL commands can be send to triple store
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
		foreach ($this->mTriples as $t) {
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
		// Test if triples can be deleted
		
		$prop = $this->mTriples[0][1];
		$tsa->addPrefixes($prefixes);
		$tsa->deleteTriples($graph, "?s $prop ?o", "?s $prop ?o");
		$this->assertTrue($tsa->flushCommands(), "***testTripleStore#4.1*** failed.");
		$query = $prefixes."SELECT ?s ?o FROM <$graph> WHERE { ?s $prop ?o . }";
		
		$result = $tsa->queryTripleStore($query, $graph);
		// Make sure the triple is deleted.
		$this->assertEquals(0, count($result->getRows()), "***testTripleStore#4.2*** failed.");

		// Make sure that another triple is still available
		$prop = $this->mTriples[1][1];
		$query = $prefixes."SELECT ?s ?o FROM <$graph> WHERE { ?s $prop ?o . }";
		$result = $tsa->queryTripleStore($query, $graph);
		$this->assertNotNull($result, "***testTripleStore#4.3*** failed.");
		$this->assertEquals(1, count($result->getRows()), "***testTripleStore#4.4*** failed.");
		
		//***testTripleStore#5***
		// Test if the complete graph can be deleted.
		$tsa->dropGraph($graph);
		$tsa->flushCommands();
		
		$query = $prefixes."SELECT ?s ?p ?o FROM <$graph> WHERE { ?s ?p ?o . }";
		
		$result = $tsa->queryTripleStore($query, $graph);
		// Make sure the graph is deleted.
		$this->assertTrue($result == null || count($result->getRows()) == 0, "***testTripleStore#5*** failed.");
		
    }
    
}
