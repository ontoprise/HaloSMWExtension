<?php
/**
 * @file
 * @ingroup LinkedData_Tests
 */

class TestSparqlDataspaceRewriter extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = FALSE;
	protected static $mBaseURI = 'http://www.example.org/smw-lde/';
	protected $mGraph1;
	protected $mGraph2;
	protected $mGraph3;
	protected $mProvGraph;
	protected $mDSIGraph;

	protected $mFilePath = "file://resources/sparql_rewriter_tests/";
	protected $mGraph1N3 = "Graph1.n3";
	protected $mGraph2N3 = "Graph2.n3";
	protected $mGraph3N3 = "Graph3.n3";
	protected $mProvGraphN3 = "ProvenanceGraph.n3";
	protected $mDSIGraphN3 = "DataSourceInformationGraph.n3";
	
    function setUp() {
		$this->mGraph1 = self::$mBaseURI."smwGraphs/Graph1";
		$this->mGraph2 = self::$mBaseURI."smwGraphs/Graph2";
		$this->mGraph3 = self::$mBaseURI."smwGraphs/Graph3";
		$this->mProvGraph = self::$mBaseURI."smwGraphs/ProvenanceGraph";
		$this->mDSIGraph = self::$mBaseURI."smwGraphs/DataSourceInformationGraph";
	    	
    	$tsa = new TSCTripleStoreAccess();
		$tsa->createGraph($this->mGraph1);
		$tsa->createGraph($this->mGraph2);
		$tsa->createGraph($this->mGraph3);
		$tsa->createGraph($this->mProvGraph);
		$tsa->createGraph($this->mDSIGraph);
		$tsa->loadFileIntoGraph("{$this->mFilePath}Graph1.n3", $this->mGraph1, "n3");
		$tsa->loadFileIntoGraph("{$this->mFilePath}Graph2.n3", $this->mGraph2, "n3");
		$tsa->loadFileIntoGraph("{$this->mFilePath}Graph3.n3", $this->mGraph3, "n3");
		$tsa->loadFileIntoGraph("{$this->mFilePath}ProvenanceGraph.n3", $this->mProvGraph, "n3");
		$tsa->loadFileIntoGraph("{$this->mFilePath}DataSourceInformationGraph.n3", $this->mDSIGraph, "n3");
		$tsa->flushCommands();
    	
    }

    function tearDown() {
    	$tsa = new TSCTripleStoreAccess();
		$tsa->dropGraph($this->mGraph1);
		$tsa->dropGraph($this->mGraph2);
		$tsa->dropGraph($this->mGraph3);
		$tsa->dropGraph($this->mProvGraph);
		$tsa->dropGraph($this->mDSIGraph);
		$tsa->flushCommands();
    	
    }

    /**
     * Tests the creation a TSCSourceDefinition object.
     */
    function testQueryForDataspaces() {
    	$tsa = new TSCTripleStoreAccess();
		$query = "
		  PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#> 
		  SELECT ?s ?l ?g
		  WHERE { 
		  		GRAPH ?g {
		    		?s rdfs:label ?l .
		    	} 
		  }
		";    	
		
		// Ask the query for all graphs
    	$qr = $tsa->queryTripleStore($query);
    	
    	$rows = $qr->getRows();
    	$this->assertEquals(count($rows), 4);
    	// Verify results of graph 1
    	$r = $qr->getRowsWhere("g", self::$mBaseURI."smwGraphs/Graph1");
    	$this->assertEquals(count($r), 1);
    	$val = $r[0]->getResult("s")->getValue();
    	$this->assertEquals($val,"http://smw/_047897855");
    	$val = $r[0]->getResult("l")->getValue();
    	$this->assertEquals($val,"Intel, Inc");
    	
    	// Verify results of graph 2
    	$r = $qr->getRowsWhere("g", self::$mBaseURI."smwGraphs/Graph2");
    	$this->assertEquals(count($r), 2);
    	
    	$val1 = $r[0]->getResult("s")->getValue();
    	$l1 = $r[0]->getResult("l")->getValue();
    	$val2 = $r[1]->getResult("s")->getValue();
    	$l2 = $r[1]->getResult("l")->getValue();
    	    	
    	$this->assertTrue($val1 == "http://smw/_047897855" || $val1 == "http://smw/_316067164");
    	if ($val1 == "http://smw/_047897855") {
    		$this->assertEquals($l1,"Intel Cooperation");
    		$this->assertEquals($l2,"Siemens AG");
    	} else {
    		$this->assertEquals($l2,"Intel Cooperation");
    		$this->assertEquals($l1,"Siemens AG");
    	}

    	// Verify results of graph 3
    	$r = $qr->getRowsWhere("g", self::$mBaseURI."smwGraphs/Graph3");
    
    	$this->assertEquals(count($r), 1);
    	$val = $r[0]->getResult("s")->getValue();
    	$this->assertEquals($val,"http://smw/_316067164");
    	$val = $r[0]->getResult("l")->getValue();
    	$this->assertEquals($val,"Siemens AG");
    	
    	
    	// Ask the query in dataspace "Wikicompany" i.e. Graph2
    	$qr = $tsa->queryTripleStore($query, $this->mDSIGraph, "dataspace = wiki");
    	$rows = $qr->getRows();
    	$this->assertEquals(count($rows), 2);

    	$r = $qr->getRowsWhere("s", "http://smw/_047897855");
    	$this->assertEquals(count($r), 1);
    	$val = $r[0]->getResult("l")->getValue();
    	$this->assertEquals($val, "Intel Cooperation"); 

    	$r = $qr->getRowsWhere("s", "http://smw/_316067164");
    	$this->assertEquals(count($r), 1);
    	$val = $r[0]->getResult("l")->getValue();
    	$this->assertEquals($val, "Siemens AG");     	
    	
    	// Ask the query in dataspace "dbpedia" i.e. Graph1 and Graph6 (which does not exist)
    	$qr = $tsa->queryTripleStore($query, $this->mDSIGraph, "dataspace = dbpedia");
    	$rows = $qr->getRows();
    	$this->assertEquals(count($rows), 1);

    	$r = $qr->getRowsWhere("s", "http://smw/_047897855");
    	$this->assertEquals(count($r), 1);
    	$val = $r[0]->getResult("l")->getValue();
    	$this->assertEquals($val, "Intel, Inc"); 
    	
    	// Ask the query in an unknown dataspace "unknown"
    	$qr = $tsa->queryTripleStore($query, $this->mDSIGraph, "dataspace = unknown");
    	$rows = $qr->getRows();
    	$this->assertEquals(count($rows), 0);
    	
    }
    
    
    /**
     * Tests the creation a TSCSourceDefinition object.
     */
    function testQueryForMetadata() {
        $tsa = new TSCTripleStoreAccess();
        $query = "
          PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#> 
          SELECT ?s ?l ?g
          WHERE { 
                GRAPH ?g {
                    ?s rdfs:label ?l .
                } 
          }
        ";      
        
        // Ask the query for all graphs and request metadata
        $qr = $tsa->queryTripleStore($query, "", "metadata=(SWP2_AUTHORITY)");
    
        $rows = $qr->getRows();
        
        $r = $qr->getRowsWhere("g", self::$mBaseURI."smwGraphs/Graph1");
        $this->assertEquals(count($r), 1);
        $val = $r[0]->getResult("s")->getValue();
      
        $this->assertEquals($val,"http://smw/_047897855");
        $val = $r[0]->getResult("l")->getValue();
        $this->assertEquals($val,"Intel, Inc");
       
        $metadata = $r[0]->getResult("s")->getMetadata();
       
        $this->assertEquals($metadata['swp2_authority'],"http://www.example.org/smw-lde/smwDatasources/DataSource1");
        
        $r = $qr->getRowsWhere("g", self::$mBaseURI."smwGraphs/Graph2");
        $metadata = $r[1]->getResult("s")->getMetadata();
        $this->assertEquals($metadata['swp2_authority'],"http://www.example.org/smw-lde/smwDatasources/DataSource2");
        
        
    }
}
