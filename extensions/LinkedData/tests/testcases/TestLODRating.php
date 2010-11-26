<?php
/**
 * @file
 * @ingroup LinkedData_Tests
 */

/**
 * Test suite for ratings of triples in the triples store.
 * Start the triple store with these options before running the test:
 * msgbroker=none client=MyStore driver=ontobroker-quad wsport=8090 reasoner=owl restfulws
 * 
 * @author thsc
 *
 */
class TestLODRatingSuite extends PHPUnit_Framework_TestSuite
{
	
	const GRAPH = "http://example.com/booksGraph";
	const AUTHOR_GRAPH = "http://example.com/authorGraph";
	
	public static $mTriples = array(
		array("ex:HitchhikersGuide", "ex:title", "The Hitchhiker's Guide to the Galaxy", "xsd:string"),
		array("ex:HitchhikersGuide", "ex:price", "10.20", "xsd:double"),
		array("ex:HitchhikersGuide", "ex:pages", "224", "xsd:int"),
		array("ex:HitchhikersGuide", "ex:reallyCool", "true", "xsd:boolean"),
		array("ex:HitchhikersGuide", "ex:published", "1979-04-02T13:41:09+01:00", "xsd:dateTime"),
		array("ex:HitchhikersGuide", "ex:amazon", "http://www.amazon.com/Hitchhikers-Guide-Galaxy-25th-Anniversary/dp/1400052920/ref=sr_1_1?ie=UTF8&s=books&qid=1272987287&sr=1-1", "xsd:anyURI")
	);
	
	
	public static function suite() {
		
		$suite = new TestTripleStoreAccessSuite();
		$suite->addTestSuite('TestLODRating');
		$suite->addTestSuite('TestLODRatingDatabase');
		return $suite;
	}
	
	protected function setUp() {
    	
	}
	
	protected function tearDown() {
//		$tsa = new LODTripleStoreAccess();
//		$tsa->dropGraph(self::GRAPH);
//		$tsa->flushCommands();
	}

}


/**
 * This class test the creation, retrieval and desctruction of rating data.
 * 
 * @author thsc
 *
 */
class TestLODRating extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = FALSE;
	
	private $mGraphsToDelete = array();
	private $mPersistenceToDelete = array();
	
	protected $mFilePath = "file://resources/lod_wiki_tests/Rating/";
		
    function setUp() {
    }

    function tearDown() {
		$tsa = new LODPersistentTripleStoreAccess();
		foreach ($this->mGraphsToDelete as $g) {
			$tsa->dropGraph($g);
		}
		foreach ($this->mPersistenceToDelete as $p) {
			$tsa->deletePersistentTriples($p[0], $p[1]);
		}
		$tsa->flushCommands();
		$mGraphsToDelete = array();
		$mPersistenceToDelete = array();
		
    }

    
    /**
     * Tests the existence of the classes LODRatingAccess and LODRating
     */
    function testExistsLODRatingClass() {
    	$ra = new LODRatingAccess();
    	$this->assertNotNull($ra, "Could not create an instance of LODRatingAccess.");
    	
    }

    /**
     * Test the creation of a rating. Some properties of a rating are set 
     * automatically e.g. author and time of creation.
     */
    function testCreateRating() {
    	global $wgUser;
    	$wgUser = User::newFromName("TestUser");
    	
    	// Create a rating with default values
    	$r = new LODRating("true", "I think this is true.");
    	$this->assertNotNull($r, "Could not create an instance of LODRating.");
    	$this->assertEquals("TestUser", $r->getAuthor());
    	$tsNow = wfTimestamp(TS_ISO_8601);
    	$this->assertEquals($tsNow, $r->getCreationTime());
    	$this->assertEquals("true", $r->getValue());
    	$this->assertEquals("I think this is true.", $r->getComment());
    	
    	// Create a fully specified rating 
    	$r = new LODRating("true", "I think this is true.", "AnotherUser", "2010-10-12T06:07:11Z");
    	$this->assertNotNull($r, "Could not create an instance of LODRating.");
    	$this->assertEquals("AnotherUser", $r->getAuthor());
    	$this->assertEquals("2010-10-12T06:07:11Z", $r->getCreationTime());
    	$this->assertEquals("true", $r->getValue());
    	$this->assertEquals("I think this is true.", $r->getComment());
    	
    }
    
    /**
     * Verifies that an exception is thrown if a wrong time format is passed
     * to the constructor of LODRating.
     */
    function testCreateInvalidRating() {
    	$this->setExpectedException('LODException');
    	// Create a rating with a wrong time format 
    	$r = new LODRating("true", "comment", null, "2010-10-122T06:07:11Z");
    	
    }
    
    /**
     * Tests adding a rating for a triple.
     */
    function testAddRating() {
    	
    	// Needed for tearDown()
    	// The hash values must be changed if the method for hashing triples
    	// in LODRatingAccess is changed.
    	$this->mGraphsToDelete[] = "http://www.example.org/smw-lde/smwGraphs/RatingsGraph";
    	$this->mGraphsToDelete[] = "http://www.example.org/smw-lde/smwGraphs/RatingGraph_957d27efdf7797e127662ff5da52f32b";
    	$this->mPersistenceToDelete[] = array("LODRating", "957d27efdf7797e127662ff5da52f32b");
    	
    	$pm = LODPrefixManager::getInstance();
    	$pm->addPrefix("ex", "http://example.com/");

    	$triple = new LODTriple("ex:HitchhikersGuide", 
    							"ex:title", 
    							"The Hitchhiker's Guide to the Galaxy", 
    							"xsd:string");
    	
    	// Store ratings for the triple
    	$ra = new LODRatingAccess();
    	$r1 = new LODRating("true", "I think this is true.");
    	$r2 = new LODRating("false", "I think this is false.");
    	$ra->addRating($triple, $r1);
    	$ra->addRating($triple, $r2);
    	
    	// Retrieve ratings for the triple
    	$ratings = $ra->getRatings($triple);
    	$this->assertEquals(2, count($ratings), "Unexpected number of ratings found.");
    	
    	if ($ratings[0]->getValue() === "true") {
    		$rr1 = $ratings[0];
    		$rr2 = $ratings[1];
    	} else {
    		$rr1 = $ratings[1];
    		$rr2 = $ratings[0];
       	}
    	
    	$this->assertEquals($r1->getValue(), $rr1->getValue());
    	$this->assertEquals($r1->getAuthor(), $rr1->getAuthor());
    	$this->assertEquals($r1->getComment(), $rr1->getComment());
    	$this->assertEquals($r1->getCreationTime(), $rr1->getCreationTime());

    	$this->assertEquals($r2->getValue(), $rr2->getValue());
    	$this->assertEquals($r2->getAuthor(), $rr2->getAuthor());
    	$this->assertEquals($r2->getComment(), $rr2->getComment());
    	$this->assertEquals($r2->getCreationTime(), $rr2->getCreationTime());

    }
    
    /**
     * Tests if all ratings for a triple are deleted correctly.
     */
    function testDeleteAllRatingsForTriple() {
    	
    	// Needed for tearDown()
    	// The hash values must be changed if the method for hashing triples
    	// in LODRatingAccess is changed.
    	$this->mGraphsToDelete[] = "http://www.example.org/smw-lde/smwGraphs/RatingsGraph";
    	$this->mGraphsToDelete[] = "http://www.example.org/smw-lde/smwGraphs/RatingGraph_957d27efdf7797e127662ff5da52f32b";
    	$this->mPersistenceToDelete[] = array("LODRating", "957d27efdf7797e127662ff5da52f32b");
    	
    	
    	$pm = LODPrefixManager::getInstance();
    	$pm->addPrefix("ex", "http://example.com/");

    	$triple = new LODTriple("ex:HitchhikersGuide", 
    							"ex:title", 
    							"The Hitchhiker's Guide to the Galaxy", 
    							"xsd:string");
    	
    	// Store ratings for the triple
    	$ra = new LODRatingAccess();
    	$r1 = new LODRating("true", "I think this is true.");
    	$r2 = new LODRating("false", "I think this is false.");
    	$ra->addRating($triple, $r1);
    	$ra->addRating($triple, $r2);
    	
    	$ra->deleteAllRatingsForTriple($triple);
    	
    	// Verify that there are no longer ratings for the triple
    	$ratings = $ra->getRatings($triple);
    	$this->assertEquals(0, count($ratings), "Not all ratings were deleted.");
    	
    }
    
    /**
     * Each result of a query has meta-data that describes the XSD type, the 
     * variable binding and the original content that was returned from the
     * triple store.
     * This function checks if this meta-data is correctly attached.
     */
    function testMetaDataOfResult() {
		$this->setupBookExample();
    	
		$queryString = <<<SPARQL
PREFIX ex: <http://example.com/>

SELECT *
WHERE {
  GRAPH <http://example.com/booksGraph> {
    ?subj ?pred ?obj .
  }
}
SPARQL;
		
		$res = self::getQueryResult($queryString);
		
		$this->assertTrue($res instanceof SMWHaloQueryResult, 
				"The query result is not an instance of SMWHaloQueryResult.");
		
		// We expect that all data values of the result are augmented with
		// meta-data i.e. the rating key.
				
		$rowCount = 0;
		$vars = array("subj", "pred", "obj");
		// Iterate all rows
		while ($row = $res->getNext()) {
			// Iterate all cells in a row
			$varIdx = 0;
			foreach ($row as $cell) {
				// Iterate all values in a cell
				while ($value = $cell->getNextObject()) {
					$metaData = $value->getMetadataMap();

					$this->assertTrue(array_key_exists("rating-key", $metaData), 
							"Expected to find the rating key at value with index $varIdx.");
					
					$ratingKey = $metaData["rating-key"][0];
					$var = $vars[$varIdx];
					$expectedRegex = "/\d+\|$rowCount\|$var/";
					$this->assertRegExp($expectedRegex, $ratingKey, 
						"Mismatched rating-key for variable $var in row $rowCount");
					$varIdx++;
				}
			}
			++$rowCount;
		}
		
		// Six rows of data are expected
		$this->assertEquals(6, $rowCount, "Unexpected number of result rows for the query.");
		
		    	
    }
    
    /**
     * Tests the rewriter for SPARQL queries which is needed to find the triples 
     * that will be rated. 
     */
    function testQueryAnalyzer1() {
		
		$query = <<<SPARQL
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
PREFIX ex:  <http://example.com/>

SELECT ?a ?p
WHERE {
  GRAPH <http://example.com/authorGraph> {
    ?a rdf:type ex:Author .
    ?a ex:nationality "english"^^xsd:string .
    ?a ex:authorOf ?b .
    ?b ex:price ?p .
  }
}
SPARQL;

		$bindings = array(
			new LODSparqlResultURI("a", "http://example.com/DouglasAdams"),
			new LODSparqlResultLiteral("p", "10.2", "http://www.w3.org/2001/XMLSchema#double")
		);
		$qa = new LODQueryAnalyzer($query, array(), $bindings);
		$rewritten = $qa->getRewrittenQuery();
		
		$expected = <<<SPARQL
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX owl: <http://www.w3.org/2002/07/owl#>
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
PREFIX ex:  <http://example.com/>

SELECT *
WHERE {
  GRAPH <http://example.com/authorGraph> {
    ex:DouglasAdams rdf:type ex:Author .
    ex:DouglasAdams ex:nationality "english"^^xsd:string .
    ex:DouglasAdams ex:authorOf ?b .
    ?b ex:price "10.2"^^xsd:double .
  }
}
SPARQL;
		// Compare original query and expected result (without whitespaces)
		$rewritten = preg_replace("/\s*/", "", $rewritten);
		$expected = preg_replace("/\s*/", "", $expected);
		
		// Verify that the query is correctly rewritten
		$this->assertEquals($expected, $rewritten, "A SPARQL query was not correctly rewritten.");
		
		// Verify bound and unbound variables
		$bound = $qa->getBoundVariables();
		$expected = array("a", "p");
		$diff = array_diff($bound, $expected);
		$this->assertTrue(count($bound) == count($expected) 
						  && count($diff) == 0, "Unexpected bound variables found.");
						  
		$unbound = $qa->getUnboundVariables(); 
		$expected = array("b");
		$diff = array_diff($unbound, $expected);
		$this->assertTrue(count($unbound) == count($expected) 
						  && count($diff) == 0, "Unexpected unbound variables found.");
		
		// Verify the triples where variables were replaced
		
		// Variable 'a'						  
		$ti = $qa->getTripleInfoForVariable("a");
		$this->assertEquals(3, count($ti), "Unexpected number of triples with variable 'a'.");
		
		$var = $ti[0]->getVariable();
		$this->assertEquals("a", $var, "Expected variable 'a' in triple information.");

		$pos = $ti[0]->getPosition();
		$this->assertEquals(LODRatingTripleInfo::SUBJECT, $pos, 
				"Expected variable 'a' at position SUBJECT in triple information.");
		
		$triple = $ti[0]->getTriple();
		$p = $triple->getPredicate();
		$this->assertEquals("http://www.w3.org/1999/02/22-rdf-syntax-ns#type", 
							$p, "Wrong triple with variable 'a'.");
		
		$pos = $ti[1]->getPosition();
		$this->assertEquals(LODRatingTripleInfo::SUBJECT, $pos, 
				"Expected variable 'a' at position SUBJECT in triple information.");
		
		$triple = $ti[1]->getTriple();
		$p = $triple->getPredicate();
		$this->assertEquals("http://example.com/nationality", $p, "Wrong triple with variable 'a'.");
		
		$pos = $ti[2]->getPosition();
		$this->assertEquals(LODRatingTripleInfo::SUBJECT, $pos, 
				"Expected variable 'a' at position SUBJECT in triple information.");
		
		$triple = $ti[2]->getTriple();
		$p = $triple->getPredicate();
		$this->assertEquals("http://example.com/authorOf", $p, "Wrong triple with variable 'a'.");
		
		// Variable 'p'
		$ti = $qa->getTripleInfoForVariable("p");
		$this->assertEquals(1, count($ti), "Unexpected number of triples with variable 'p'.");
		
		$var = $ti[0]->getVariable();
		$this->assertEquals("p", $var, "Expected variable 'p' in triple information.");

		$pos = $ti[0]->getPosition();
		$this->assertEquals(LODRatingTripleInfo::OBJECT, $pos, 
				"Expected variable 'p' at position OBJECT in triple information.");
		
		$triple = $ti[0]->getTriple();
		$p = $triple->getPredicate();
		$this->assertEquals("http://example.com/price", $p, "Wrong triple with variable 'p'.");
		
		
		
		// Verify the triples that still contain unbound variables
		// Variable 'b'						  
		$ti = $qa->getTripleInfoForVariable("b");
		$this->assertEquals(2, count($ti), "Unexpected number of triples with variable 'b'.");
		
		$var = $ti[0]->getVariable();
		$this->assertEquals("b", $var, "Expected variable 'b' in triple information.");

		$pos = $ti[0]->getPosition();
		$this->assertEquals(LODRatingTripleInfo::OBJECT, $pos, 
				"Expected variable 'b' at position OBJECT in triple information.");
		
		$triple = $ti[0]->getTriple();
		$p = $triple->getPredicate();
		$this->assertEquals("http://example.com/authorOf", $p, "Wrong triple with variable 'b'.");
		
		$pos = $ti[1]->getPosition();
		$this->assertEquals(LODRatingTripleInfo::SUBJECT, $pos, 
				"Expected variable 'b' at position SUBJECT in triple information.");
		
		$triple = $ti[1]->getTriple();
		$p = $triple->getPredicate();
		$this->assertEquals("http://example.com/price", $p, "Wrong triple with variable 'b'.");
		
    }

    /**
     * Tests the rewriter for SPARQL queries which is needed to find the triples 
     * that will be rated. This test handles a UNION in a query. In this case an
     * exception is expected.
     */
    function testQueryAnalyzer2() {
		
		$query = <<<SPARQL
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
PREFIX ex:  <http://example.com/>

SELECT ?a ?p
WHERE {
  GRAPH <http://example.com/authorGraph> {
    { ?a rdf:type ex:Author . }
    UNION
    { ?a ex:nationality "english"^^xsd:string . }
  }
}
SPARQL;

		$bindings = array(
			new LODSparqlResultURI("a", "http://example.com/DouglasAdams"),
			new LODSparqlResultLiteral("p", "10.2", "http://www.w3.org/2001/XMLSchema#double")
		);
		
				
		try {
			$qa = new LODQueryAnalyzer($query, array(), $bindings);
		} catch (LODRatingException $e) {
			$this->assertEquals(LODRatingException::QUERY_CONTAINS_UNION, $e->getCode(),
					"Wrong type of exception caught.");
			return;
		}

		$this->fail('An expected exception has not been raised: LODRatingException::QUERY_CONTAINS_UNION');
    }
    
    
    /**
     * Tests the rewriter for SPARQL queries which is needed to find the triples 
     * that will be rated. This test handles OPTIONAL and FILTER in a query.
     * OPTIONALs are treated like normal group patterns.
     * FILTERs will be removed.
     */
    function testQueryAnalyzer3() {
		
		$query = <<<SPARQL
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
PREFIX ex:  <http://example.com/>

SELECT ?a ?p
WHERE {
  GRAPH <http://example.com/authorGraph> {
    ?a rdf:type ex:Author .
    ?a ex:authorOf ?b .
    ?b ex:price ?p .
    OPTIONAL {
    	?a ex:nationality "english"^^xsd:string .
    }
    FILTER (REGEX(?a,"Douglas"))
  }
}
SPARQL;

		$bindings = array(
			new LODSparqlResultURI("a", "http://example.com/DouglasAdams"),
			new LODSparqlResultLiteral("p", "10.2", "http://www.w3.org/2001/XMLSchema#double")
		);
		
		$qa = new LODQueryAnalyzer($query, array(), $bindings);
		$rewritten = $qa->getRewrittenQuery();
						
		$expected = <<<SPARQL
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX owl: <http://www.w3.org/2002/07/owl#>
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
PREFIX ex:  <http://example.com/>

SELECT *
WHERE {
  GRAPH <http://example.com/authorGraph> {
    ex:DouglasAdams rdf:type ex:Author .
    ex:DouglasAdams ex:authorOf ?b .
    ?b ex:price "10.2"^^xsd:double .
    OPTIONAL {
    	ex:DouglasAdams ex:nationality "english"^^xsd:string .
    }
  }
}
SPARQL;
		// Compare original query and expected result (without whitespaces)
		$rewritten = preg_replace("/\s*/", "", $rewritten);
		$expected = preg_replace("/\s*/", "", $expected);
		
		$this->assertEquals($expected, $rewritten, "A SPARQL query was not correctly rewritten.");
    	
    }
    
    /**
     * Tests the rewriter with a query that contains blank nodes.
     */
    function testQueryAnalyzer4() {
		$query = <<<SPARQL
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

		$bindings = array(
			new LODSparqlResultURI("a", "http://example.com/DouglasAdams"),
		);
		
		$qa = new LODQueryAnalyzer($query, array(), $bindings);
		$rewritten = $qa->getRewrittenQuery();

		$expected = <<<SPARQL
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX owl: <http://www.w3.org/2002/07/owl#>
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
PREFIX ex: <http://example.com/>
SELECT *
WHERE {
	ex:DouglasAdams ex:ownsBook ?__bn1 .
	?__bn1 ex:hasTitle ex:HitchhikersGuide .
	?__bn1 ex:inStoreAt ?__bn2 .
	?__bn2 ex:name  ex:Amazon .
	?__bn2 ex:price "10.20"^^xsd:double .
	?__bn1 ex:pages "224"^^xsd:int .
}
SPARQL;

		// Compare original query and expected result (without whitespaces)
		$rewritten = preg_replace("/\s*/", "", $rewritten);
		$expected = preg_replace("/\s*/", "", $expected);
		
		$this->assertEquals($expected, $rewritten, "A SPARQL query was not correctly rewritten.");
		
    }
    
    /**
     * Tests getting all triples in a query where all variables are bound by the
     * query result
     */
    public function testQueryAnalyzer5() {

    	$this->setupAuthorExample();
    	
		$query = <<<SPARQL
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
PREFIX ex:  <http://example.com/>

SELECT ?a ?b ?p
WHERE {
  GRAPH <http://example.com/authorGraph> {
    ?a rdf:type ex:Author .
    ?a ex:nationality "english"^^xsd:string .
    ?a ex:authorOf ?b .
    ?b ex:price ?p .
  }
}
SPARQL;

		// The following bindings will lead to one result where all variables are
		// bound.
		$bindings = array(
			new LODSparqlResultURI("a", "http://example.com/DouglasAdams"),
			new LODSparqlResultURI("b", "http://example.com/HitchhikersGuide"),
			new LODSparqlResultLiteral("p", "10.2", "http://www.w3.org/2001/XMLSchema#double")
		);
		
		$qa = new LODQueryAnalyzer($query, array(), $bindings);
		$resultSets = $qa->bindAndGetAllTriples();
		
		// There is only one result
		$this->assertEquals(1, count($resultSets), "Expected exactly one result.");
		
		// Verify the triples in the result
		$result = $resultSets[0];
		
		// Three variables are bound		
		$this->assertEquals(3, count($result), "More than one result found.");
		foreach ($result as $variable => $tripleInfo) {
			switch ($variable) {
			case 'a':
				$this->assertEquals(3, count($tripleInfo), "Three triples must be bound to variable a.");
				break;
			case 'b':
				$this->assertEquals(2, count($tripleInfo), "Two triples must be bound to variable b.");
				break;
			case 'p':
				$this->assertEquals(1, count($tripleInfo), "One triple must be bound to variable p.");
				break;
			}
		}
		
    	
    }
    
    /**
     * Tests binding all unbound variables in a query. The unbound variables
     * may lead to more than one result.
     */
    public function testQueryAnalyzer6() {

    	$this->setupAuthorExample();
    	
		$query = <<<SPARQL
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
PREFIX ex:  <http://example.com/>

SELECT ?a ?p
WHERE {
  GRAPH <http://example.com/authorGraph> {
    ?a rdf:type ex:Author .
    ?a ex:nationality "english"^^xsd:string .
    ?a ex:authorOf ?b .
    ?b ex:price ?p .
  }
}
SPARQL;

		// The following bindings will lead to two possible results for the
		// unbound variable ?b i.e. ex:DirkGentlysHolisticDetectiveAgency
		// and ex:HitchhikersGuide.
		$bindings = array(
			new LODSparqlResultURI("a", "http://example.com/DouglasAdams"),
			new LODSparqlResultLiteral("p", "10.2", "http://www.w3.org/2001/XMLSchema#double")
		);
		
		$qa = new LODQueryAnalyzer($query, array(), $bindings);
		$resultSets = $qa->bindAndGetAllTriples();
    	
		// There are two results
		$this->assertEquals(2, count($resultSets), "Expected exactly two results.");
		
		// Verify the triples in the results
		foreach ($resultSets as $result) {
			// Three variables are bound in each result	
			$this->assertEquals(3, count($result), "More than one result found.");
			foreach ($result as $variable => $tripleInfo) {
				switch ($variable) {
				case 'a':
					$this->assertEquals(3, count($tripleInfo), "Three triples must be bound to variable a.");
					break;
				case 'b':
					$this->assertEquals(2, count($tripleInfo), "Two triples must be bound to variable b.");
					foreach ($tripleInfo as $ti) {
						$pos = $ti->getPosition();
						$value = $pos === LODRatingTripleInfo::SUBJECT 
									? $ti->getTriple()->getSubject()
									: $ti->getTriple()->getObject();
						$this->assertRegExp('/http:\/\/example\.com\/DirkGentlysHolisticDetectiveAgency|http:\/\/example\.com\/HitchhikersGuide/', $value);
						
					}
					break;
				case 'p':
					$this->assertEquals(1, count($tripleInfo), "One triple must be bound to variable p.");
					break;
				}
			}
		}
		
    }
    
    /**
     * Tests getting all triples in a query where all variables are bound by the
     * query result but one that appears in an OPTIONAL statement
     */
    public function testQueryAnalyzer7() {

    	$this->setupAuthorExample();
    	
    	// Remove the nationality of HermannHesse
    	$namespace = "http://example.com/";
    	$prefixes = "PREFIX ex:<$namespace> ";
    	$pattern = "ex:HermannHesse ex:nationality ?n";
		
		$graph = TestLODRatingSuite::AUTHOR_GRAPH;
		$tsa = new LODTripleStoreAccess();
		$tsa->addPrefixes($prefixes);
		$tsa->deleteTriples($graph, $pattern, $pattern);
		$tsa->flushCommands();
    	
		$query = <<<SPARQL
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
PREFIX ex:  <http://example.com/>

SELECT ?a ?b ?p
WHERE {
  GRAPH <http://example.com/authorGraph> {
    ?a rdf:type ex:Author .
    ?a ex:authorOf ?b .
    ?b ex:price ?p .
    OPTIONAL {
    	?a ex:nationality ?n .
    }
  }
}
SPARQL;

		// The following bindings will lead to one result where all variables are
		// bound.
		$bindings = array(
			new LODSparqlResultURI("a", "http://example.com/HermannHesse"),
			new LODSparqlResultLiteral("p", "17", "http://www.w3.org/2001/XMLSchema#double")
		);
		
		$qa = new LODQueryAnalyzer($query, array(), $bindings);
		$resultSets = $qa->bindAndGetAllTriples();
		
		// There is only one result
		$this->assertEquals(1, count($resultSets), "Expected exactly one result.");
		
		// Verify the triples in the result
		$result = $resultSets[0];
		
		// Three variables are bound		
		$this->assertEquals(3, count($result), "More than one result found.");
		foreach ($result as $variable => $tripleInfo) {
			$numUnbound = 0;
			foreach ($tripleInfo as $ti) {
				if ($ti->hasUnboundVarInTriple()) {
					++$numUnbound;
				}
			}
			
			switch ($variable) {
			case 'a':
				$this->assertEquals(3, count($tripleInfo), "Three triples must be bound to variable a.");
				$this->assertEquals(1, $numUnbound, "One triple with variable a must contain an unbound variable.");
				break;
			case 'b':
				$this->assertEquals(2, count($tripleInfo), "Two triples must be bound to variable b.");
				$this->assertEquals(0, $numUnbound, "No triple with variable b must contain an unbound variable.");
				break;
			case 'p':
				$this->assertEquals(1, count($tripleInfo), "One triple must be bound to variable p.");
				$this->assertEquals(0, $numUnbound, "No triple with variable p must contain an unbound variable.");
				break;
			}
		}
		
    	
    }
    
    
    
    /**
     * This function tests the whole rating workflow on the backend level.
     * The workflow is:
     * - Execute a query and get the result
     * - Choose a value of the result, the value to be rated (vtbr)
     * - Get all values in the row of the vtbr
     * - Get the meta-data of all values in the row of the vtbr (variable, value, data type)
     * - Rewrite the query with that data
     * - Find all triples that contain the vtbr
     * - If all triples are without a variable the triple to be rated (ttbr) can
     *   be chosen.
     * - Otherwise execute the rewritten query. Rewrite the query again with the
     *   new results. Now all triples will be without variable. Chose the ttbr.
     * - Add the rating of the tbbr.
     * 
     * In this example we will rate the triple: 
     *   ex:HermannHesse ex:nationality "english"^^xsd:string
     */
    function testRatingWorkflow() {
    	
    	$this->setupAuthorExample();
 
		$query = <<<SPARQL
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
PREFIX ex:  <http://example.com/>

SELECT ?a ?p
WHERE {
    ?a rdf:type ex:Author .
    ?a ex:nationality "english"^^xsd:string .
    ?a ex:authorOf ?b .
    ?b ex:price ?p .
}
SPARQL;
		
		// Execute a query and get the result as HTML
		$result = SMWSPARQLQueryProcessor::getResultFromQueryString(
					$query, 
					array("graph" => "http://example.com/authorGraph",
						  "enablerating" => "true",
						  'format' => 'table'), 
					array(), 
					SMW_OUTPUT_WIKI);
    	// Get all values in the row of the vtbr. 
		// The price of the Hermann Hesse book is 17.0. Get all values in that row.
		$dom = simplexml_load_string($result);
		$this->assertTrue($dom !== false, "Query processor returned invalid result.");
		
		$results = $dom->xpath('//span[@class=\'lodMetadata \']');
		$ratingKey = null;
		foreach ($results as $r) {
			if ((string) $r == '17') {
				$xml = $r->asXML();
				$keyFound = preg_match('/<span class="lodRatingKey".*?>(\d+\|\d+\|.*?)<\/span>/', $xml, $ratingKey);
				$this->assertTrue($keyFound == 1, "No rating key found.");
				$ratingKey = $ratingKey[1];
				break;
			}
		}
		
		$this->assertNotNull($ratingKey, "No rating key found.");
		
		// Get the primary and secondary triples for the rating key
		$solutions = LODRatingAccess::getTriplesForRatingKey($ratingKey);
		
		// For the value '17' of variable 'p' we expect one solution with the 
		// following triples:
		// Primary triples (for variable p)
		// 		ex:Demian ex:price "17.00"^^xsd:double.
		// Secondary triples (for all other variables)
		//		ex:HermannHesse ex:nationality "english"^^xsd:string;
		//		ex:HermannHesse rdf:type	ex:Author;
		//		ex:HermannHesse ex:authorOf ex:Demian.
		
		// Verify that there is one solution.
		$this->assertEquals(1, count($solutions), "Expected exactly one solution.");
		
		$solution  = $solutions[0];
		$primary   = $solution[0];
		$secondary = $solution[1];
		
		// Verify that there is one primary triple
		$this->assertEquals(1, count($primary), "Expected exactly one primary triple.");
		
		// Verify that there are three secondary triples
		$this->assertEquals(3, count($secondary), "Expected exactly three secondary triples.");
		
		// Verify the primary triple
		$p = $primary[0];
		$this->assertEquals("http://example.com/Demian", $p->getSubject(), "Unexpected subject found.");
		$this->assertEquals("http://example.com/price", $p->getPredicate(), "Unexpected predicate found.");
		$this->assertEquals("17", $p->getObject(), "Unexpected object found.");

		// Verify the secondary triples
		$ttbr = null;
		foreach ($secondary as $s) {
			$this->assertEquals("http://example.com/HermannHesse", $s->getSubject(), "Unexpected subject found.");
			$pred = $s->getPredicate();
			switch ($pred) {
				case "http://example.com/nationality":
					$this->assertEquals("english", $s->getObject(), "Unexpected object found.");
					$ttbr = $s;
					break;
				case "http://example.com/authorOf":
					$this->assertEquals("http://example.com/Demian", $s->getObject(), "Unexpected object found.");
					break;
				case "http://www.w3.org/1999/02/22-rdf-syntax-ns#type":
					$this->assertEquals("http://example.com/Author", $s->getObject(), "Unexpected object found.");
					break;
				default:
					$this->fail("An expected secondary triple is missing.");
					
			}
			
		}
		
		// Add the rating of the triple to be rated i.e. that Hermann Hesse is
		// an english author
    	
		$ra = new LODRatingAccess();
    	$r = new LODRating("false", "Hermann Hesse is a german author.");
    	$ra->addRating($ttbr, $r);
    	
    	// Retrieve rating for the triple and verify its content
    	$ratings = $ra->getRatings($ttbr);
    	$rating = $ratings[0];
    	
    	$this->assertEquals($r->getValue(), $rating->getValue());
    	$this->assertEquals($r->getAuthor(), $rating->getAuthor());
    	$this->assertEquals($r->getComment(), $rating->getComment());
    	$this->assertEquals($r->getCreationTime(), $rating->getCreationTime());
    	
    }
    
  	/**
	 * Returns the result of the given query.
	 * 
	 * @param string $queryString
	 * 		Teh query
	 * @return 
	 * 		The query result
	 */
	private function getQueryResult($queryString) {
		
		$params = array("enablerating" => "true");
		
		$query  = SMWSPARQLQueryProcessor::createQuery($queryString, $params);
		$store = new SMWTripleStore();
		$res = $store->getQueryResult( $query );
		
		return $res;
			
	}
		
	
	/**
	 * Stores the triples of book example in the triple store.
	 */
	private function setupBookExample() {
    	
		$graph = TestLODRatingSuite::GRAPH;
		// Needed for tearDown()
    	$this->mGraphsToDelete[] = $graph;
    	
    	// Initialise the triple store for this test
    	$namespace = "http://example.com/";
    	$prefixes = "PREFIX ex:<$namespace> ".
    			  TSNamespaces::getW3CPrefixes();
    	$triples = array();
		foreach (TestTripleStoreAccessSuite::$mTriples as $t) {		
			$triples[] = new LODTriple($t[0], $t[1], $t[2], $t[3]);
		}
		
		// Inserts triples into the triple store
		$tsa = new LODTripleStoreAccess();
		$tsa->addPrefixes($prefixes);
		$tsa->createGraph($graph);
		$tsa->insertTriples($graph, $triples);
		$tsa->flushCommands();
		
	}
	private function setupAuthorExample() {
		$graph = TestLODRatingSuite::AUTHOR_GRAPH;
		// Needed for tearDown()
    	$this->mGraphsToDelete[] = $graph;
		
		$tsa = new LODTripleStoreAccess();
		$tsa->createGraph($graph);
		$tsa->loadFileIntoGraph("{$this->mFilePath}authors.n3", $graph, "n3");
		$tsa->flushCommands();
		
	}
    
}


/**
 * This class test the database that stores queries and their results for rating them.
 * 
 * @author thsc
 *
 */
class TestLODRatingDatabase extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = FALSE;
	
	const ARTICLE_WITH_QUERIES = "AnArticleWithAQuery";
	
	
    function setUp() {
    }

    function tearDown() {
    	$db = LODStorage::getDatabase();
    	$db->deleteQueries(self::ARTICLE_WITH_QUERIES);
    }

    
    /**
     * Tests storing and retrieving a query in the database
     */
    function testStoreQuery() {
   		$queryString1 = <<<SPARQL
SELECT *
WHERE {
    ?s ?p ?o .
}
SPARQL;
		$params1 = array("dataspace" => "source1", 
						 "someparam" => "somevalue");

   		$queryString2 = <<<SPARQL
SELECT ?s ?p ?o
WHERE {
    ?s ?p ?o .
}
SPARQL;
		$params2 = array("dataspace" => "source2", 
						 "p" => "some other value");
   		
   		
    	$db = LODStorage::getDatabase();
    	
    	$articleName = self::ARTICLE_WITH_QUERIES;
    	
    	// Store queries
    	$queryID1 = $db->addQuery($queryString1, $params1, $articleName);
    	$queryID2 = $db->addQuery($queryString2, $params2, $articleName);

    	// Retrieve queries
    	
    	// Retrieve a query with an invalid ID
    	$q = $db->getQueryByID(-1);
    	$this->assertNull($q);
    	
    	$q1 = $db->getQueryByID($queryID1);
    	$q2 = $db->getQueryByID($queryID2);

		// Verify that the query was correctly stored and retrieved
		$this->assertEquals($queryString1, $q1, "Storing and retrieving a query failed.");
		$this->assertEquals($queryString2, $q2, "Storing and retrieving a query failed.");

    	$p1 = $db->getQueryParamsByID($queryID1);
    	$p2 = $db->getQueryParamsByID($queryID2);
		// Verify that the query parameters were correctly stored and retrieved
		$this->assertEquals($params1, $p1, "Storing and retrieving query parameters failed.");
		$this->assertEquals($params2, $p2, "Storing and retrieving query parameters failed.");
    	
		
		// Delete queries
		$db->deleteQueries($articleName);
		
		// Make sure that queries are deleted
    	$q1 = $db->getQueryByID($queryID1);
    	$this->assertNull($q1);
    	$q2 = $db->getQueryByID($queryID2);
    	$this->assertNull($q2);
    	
    }
    
    /**
     * Test storing and retrieving a query result in the database.
     */
    function testStoreQueryResult() {
   		$queryString1 = <<<SPARQL
SELECT *
WHERE {
    ?s ?p ?o .
}
SPARQL;
    	$db = LODStorage::getDatabase();
    	
    	$articleName = "AnArticleWithAQuery";
    	
    	$queryID = $db->addQuery($queryString1, array(), $articleName);
    	    	
    	$result = array(
    		array(array("a", "http://example.com/DouglasAdams", ""),
    			  array("b", "http://example.com/HitchhikersGuide", ""),
    			  array("p", "10.20", "http://www.w3.org/2001/XMLSchema#double")),
    		array(array("a", "http://example.com/JonathanSwift", ""),
    			  array("b", "http://example.com/GulliversTravels", ""),
    			  array("p", "15.00", "http://www.w3.org/2001/XMLSchema#double"))
       	);
       	
       	// Store a result
       	$row = 0;
       	foreach ($result as $r) {
       		foreach ($r as $b) {
       			$variable = $b[0];
       			$value    = $b[1];
       			$datatype = $b[2];
    			$db->storeQueryResultRow($queryID, $row, $variable, $value, $datatype);
       		}
    		++$row;
       	}
       	
       	// Retrieve a result
       	$row = 0;
       	while ($rowContent = $db->readQueryResultRow($queryID, $row)) {
       		$expRC = $result[$row];
       		// A row is returned as a map from variable names to their binding
       		// (value and data type)
       		// e.g. "p" => array("10.20", "http://www.w3.org/2001/XMLSchema#double")
       		$this->assertEquals(3, count($rowContent), "Expected 3 variable bindings in result row.");
       		foreach ($rowContent as $var => $binding) {
       			$rc = array($var, $binding[0], $binding[1]);
       			
       			// Compare result with expected
       			$bindingFound = false;
       			foreach ($expRC as $varBinding) {
       				if ($varBinding[0] == $var) {
	       				$bindingFound = true;
       					$diff = array_diff($varBinding, $rc);
       					$this->assertEquals(0, count($diff), "Unexpected variable binding found in result row.");
       				}
       			}
       			$this->assertTrue($bindingFound, "An expected variable binding is missing.");
       		}
       		++$row;
       	}
       	
       	// Delete queries
		$db->deleteQueries($articleName);
		
		// Make sure all result are deleted
		$res = $db->readQueryResultRow($queryID, 0);
		$this->assertNull($res, "Query results are not deleted.");
       	
    }
}