<?php
/**
 * @file
 * @ingroup LinkedData_Tests
 */

/**
 * Test suite for LOD source definitions.
 * Start the triple store with these options before running the test:
 * msgbroker=none client=MyStore driver=ontobroker-quad wsport=8090 console reasoner=owl restfulws
 * 
 * @author thsc
 *
 */
class TestLODSourceDefinitionSuite extends PHPUnit_Framework_TestSuite
{
	
	public static $mLSD = array(
		"id" => "dbpedia",
		"ChangeFreq" => "daily",
		"DataDumpLocations" => array("http://deepblue.rkbexplorer.com/datadDump", "http://deepblue.rkbexplorer.com/datadDump2"),
		"Description" => "This repository contains data supplied from Deep Blue.",
		"Homepage" => "http://deepblue.rkbexplorer.com/",
		"Label" => "deepblue.rkbexplorer.com Linked Data Repository",
		"LastMod" => "2007-11-21T14:41:09+12:34",
		"SampleURIs" => array("http://dbpedia.org/resource/Computer_science", "http://dbpedia.org/resource/Organization"),
		"SparqlEndpointLocation" => "http://deepblue.rkbexplorer.com/sparql/",
		"SparqlGraphName" => "http://example.org/deepblue",
		"SparqlGraphPatterns" => array("FILTER (?p = <http://www.w3.org/1999/02/22-rdf-syntax-ns#type>)"),
		"Vocabularies" => array("http://purl.org/dc/elements/1.1/", "http://xmlns.com/foaf/0.1/", "http://www.w3.org/2000/01/rdf-schema#"),
		"PredicatesToCrawl" => array("http://dbpedia.org/property/deathdate"),
	);
	
	public static function suite() {
		
		$suite = new TestLODSourceDefinitionSuite();
		$suite->addTestSuite('TestLODSourceDefinition');
		$suite->addTestSuite('TestLSDParserFunction');
		return $suite;
	}
	
	/**
	 * Loads the LSD from the triple store and checks its content.
	 */
	public static function checkLSDinTripleStore($testCase) {
		$store = LODAdministrationStore::getInstance();
		$lsd = $store->loadSourceDefinition(self::$mLSD["id"]);
		
		$testCase->assertNotNull($lsd);
		$testCase->assertEquals(self::$mLSD["id"], $lsd->getID());
		$testCase->assertEquals(self::$mLSD["ChangeFreq"], $lsd->getChangeFreq());
		$testCase->assertEquals(self::$mLSD["Description"], $lsd->getDescription());
		$testCase->assertEquals(self::$mLSD["Homepage"], $lsd->getHomepage());
		$testCase->assertEquals(self::$mLSD["Label"], $lsd->getLabel());
		$testCase->assertEquals(self::$mLSD["LastMod"], $lsd->getLastMod());
		$testCase->assertEquals(self::$mLSD["SparqlEndpointLocation"], $lsd->getSparqlEndpointLocation());
		$testCase->assertEquals(self::$mLSD["SparqlGraphName"], $lsd->getSparqlGraphName());

		// Compare results that are arrays
		$r = $lsd->getDataDumpLocations();
		$testCase->assertEquals(count(self::$mLSD["DataDumpLocations"]), count($r));
		foreach (self::$mLSD["DataDumpLocations"] as $v) {
			$testCase->assertContains($v, $r);
		}

		$r =  $lsd->getSampleURIs();
		$testCase->assertEquals(count(self::$mLSD["SampleURIs"]), count($r));
		foreach (self::$mLSD["SampleURIs"] as $v) {
			$testCase->assertContains($v, $r);
		}
		
		$r = $lsd->getVocabularies();
		$testCase->assertEquals(count(self::$mLSD["Vocabularies"]), count($r));
		foreach (self::$mLSD["Vocabularies"] as $v) {
			$testCase->assertContains($v, $r);
		}
		
		$r = $lsd->getSparqlGraphPatterns();
		$testCase->assertEquals(count(self::$mLSD["SparqlGraphPatterns"]), count($r));
		foreach (self::$mLSD["SparqlGraphPatterns"] as $v) {
			$testCase->assertContains($v, $r);
		}

		$r =  $lsd->getPredicatesToCrawl();
		$testCase->assertEquals(count(self::$mLSD["PredicatesToCrawl"]), count($r));
		foreach (self::$mLSD["PredicatesToCrawl"] as $v) {
			$testCase->assertContains($v, $r);
		}
	}
	
	protected function setUp() {
				
    	
	}
	
	protected function tearDown() {
		// Delete the graph in the triple store that contains the source definitions
		$tsa = new LODTripleStoreAccess();
		$tsa->dropGraph(LODAdministrationStore::getDataSourcesGraph());
		$tsa->flushCommands();
		
	}
	
//--- Helper functions ---
    
	/**
	 * Creates a LODSourceDefinition object
	 * @return LODSourceDefinition
	 * 		A sample object
	 */
	public static function createLSD() {
		$sd = new LODSourceDefinition(self::$mLSD["id"]);
		$sd->setChangeFreq(self::$mLSD["ChangeFreq"]);
		$sd->setDataDumpLocations(self::$mLSD["DataDumpLocations"]);
		$sd->setDescription(self::$mLSD["Description"]);
		$sd->setHomepage(self::$mLSD["Homepage"]);
		$sd->setLabel(self::$mLSD["Label"]);
		$sd->setLastMod(self::$mLSD["LastMod"]);
		$sd->setSampleURIs(self::$mLSD["SampleURIs"]);
		$sd->setSparqlEndpointLocation(self::$mLSD["SparqlEndpointLocation"]);
		$sd->setSparqlGraphName(self::$mLSD["SparqlGraphName"]);
		$sd->setSparqlGraphPatterns(self::$mLSD["SparqlGraphPatterns"]);
		$sd->setVocabularies(self::$mLSD["Vocabularies"]);
		$sd->setPredicatesToCrawl(self::$mLSD["PredicatesToCrawl"]);
		return $sd;
	}
	
	/**
     * Compares the content in the database table with persistent triples for 
     * the $id with the $expected result and prints the $errMsg if the strings 
     * do not match.
     */
    public static function checkPersistentTriples($testCase, $id, $expected, $errMsg) {
		
		// Read the generated TriG from the database
		$store = LODStorage::getDatabase();
		$trigs = $store->readPersistentTriples("LODSourceDefinition", $id);
		$trig = "";
		foreach($trigs as $t) {
			$trig .= $t;
		}
		
		// Remove whitespaces
		$trig = preg_replace("/\s*/", "", $trig);
		$expected = preg_replace("/\s*/", "", $expected);
		
		$testCase->assertEquals($expected, $trig, $errMsg);
		
    }
	

}

/**
 * This test case tests the backend of the class LODSourceDefinition. Source
 * Definitions are stored in, retrieved and deleted from the triple store.
 * 
 * The triple store must be running.
 * 
 * @author thsc
 *
 */
class TestLODSourceDefinition extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = FALSE;
	
    function setUp() {
    }

    function tearDown() {
		LODStorage::getDatabase()->deleteAllPersistentTriples();
    }

    /**
     * Tests the creation a LODSourceDefinition object.
     */
    function testCreateLSD() {
    	$lsd = new LODSourceDefinition("dbpedia");
    	$this->assertNotNull($lsd);
    }
    
    /**
     * Tests the creation of the LODAdministrationStore object.
     *
     */
    function testCreateLODAdministrationStore() {
    	$las = LODAdministrationStore::getInstance();
    	$this->assertNotNull($las);
    }
    
    /**
     * Tests storing a LODSourceDefinition object in the triple store.
     */
    function testStoreLSD() {
		$store = LODAdministrationStore::getInstance();
		$sd = TestLODSourceDefinitionSuite::createLSD();		
		$r = $store->storeSourceDefinition($sd);
		
		$this->assertTrue($r);
	}
		
	
	/**
	 * Tests loading a LODSourceDefinition object from the Triple Store
	 *
	 */
	function testLoadLSD() {
		TestLODSourceDefinitionSuite::checkLSDinTripleStore($this);
		
	}
	
	/**
	 * Tests deleting LODSourceDefinition object from the Triple Store
	 *
	 */
	function testDeleteLSD() {
		$store = LODAdministrationStore::getInstance();
		$store->deleteSourceDefinition(TestLODSourceDefinitionSuite::$mLSD["id"]);
		
		// Make sure that the source definition is no longer available
		$lsd = $store->loadSourceDefinition(TestLODSourceDefinitionSuite::$mLSD["id"]);
		
		$this->assertEquals(null, $lsd);
	}
	
	/**
	 * Test retrieving all IDs of source definitions
	 *
	 */
	function testGetLSDIDs() {
		$store = LODAdministrationStore::getInstance();
		$lsd = new LODSourceDefinition("LSD-1");
		$store->storeSourceDefinition($lsd);
		$lsd = new LODSourceDefinition("LSD-2");
		$store->storeSourceDefinition($lsd);
		$lsd = new LODSourceDefinition("LSD-3");
		$store->storeSourceDefinition($lsd);
		
		$ids = $store->getAllSourceDefinitionIDs();
		$this->assertContains("LSD-1", $ids);
		$this->assertContains("LSD-2", $ids);
		$this->assertContains("LSD-3", $ids);
		
		// cleanup
		$store->deleteAllSourceDefinitions();
	}
	
	
	/**
	 * Tests deleting all LODSourceDefinition objects from the Triple Store
	 *
	 */
	function testDeleteAllLSDs() {
		$store = LODAdministrationStore::getInstance();
		// Create a source definition...
		$store->storeSourceDefinition(TestLODSourceDefinitionSuite::createLSD());
		// ... and delete all definitions
		$store->deleteAllSourceDefinitions();
		
		// Make sure that the source definition no longer exists
		$this->assertEquals(null, $store->loadSourceDefinition(TestLODSourceDefinitionSuite::$mLSD["id"]));
	}
	
    /**
     * Tests storing a LODSourceDefinition object in the triple store with help
     * of the persistency layer of the TS. Different persistency IDs are tested,
     * automatic ones and user defined.
     */
    function testStorePersistentLSD() {
    	$this->checkStorePersistentLSD(true);
    	$this->checkStorePersistentLSD("MyOwnLSDID");
	}
	
	/**
	 * Tests deleting all LODSourceDefinition objects from the Triple Store and
	 * the persistency layer.
	 *
	 */
	function testDeleteAllLPersistentSDs() {
		$store = LODAdministrationStore::getInstance();
		// Create a source definition...
		$store->storeSourceDefinition(TestLODSourceDefinitionSuite::createLSD(), true);
		// ... and delete all definitions
		$store->deleteAllSourceDefinitions();
		
		// Make sure that the source definition no longer exists in the TS
		$this->assertEquals(null, $store->loadSourceDefinition(TestLODSourceDefinitionSuite::$mLSD["id"]));

		// Make sure that the source definition no longer exists in the 
		// persistency layer
		TestLODSourceDefinitionSuite::checkPersistentTriples($this, 
				TestLODSourceDefinitionSuite::$mLSD["id"], "", 
				"testDeleteAllLPersistentSDs failed.");
	}
	
	
        
    /**
     * Tests storing a LODSourceDefinition object in the triple store with help
     * of the persistency layer of the TS. 
     * 
     * @param bool/string $persistencyID
     * 		The persistency ID that is used for storing and deleting the LSD.
     */
    private function checkStorePersistentLSD($persistencyID) {
    	$store = LODAdministrationStore::getInstance();
		$sd = TestLODSourceDefinitionSuite::createLSD();
		// Store the LSD and persist it
		$r = $store->storeSourceDefinition($sd, $persistencyID);
		
		$this->assertTrue($r);
		
		// Test if the LSD was stored in the TS
		$this->testLoadLSD();
		
		// Test if the LSD was saved in the persistency layer
		$expected = <<<EXP
@prefix xsd: <http://www.w3.org/2001/XMLSchema#> .
@prefix owl: <http://www.w3.org/2002/07/owl#> .
@prefix rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> .
@prefix rdfs: <http://www.w3.org/2000/01/rdf-schema#> .
@prefix smw-lde: <http://www.example.org/smw-lde/smw-lde.owl#> .
@prefix smwGraphs: <http://www.example.org/smw-lde/smwGraphs/> .
@prefix smwDatasources: <http://www.example.org/smw-lde/smwDatasources/> .

<http://www.example.org/smw-lde/smwGraphs/DataSourceInformationGraph> {
	smwDatasources:dbpedia rdf:type smw-lde:Datasource . 
	smwDatasources:dbpedia smw-lde:ID "dbpedia"^^xsd:string . 
	smwDatasources:dbpedia smw-lde:description "This repository contains data supplied from Deep Blue."^^xsd:string . 
	smwDatasources:dbpedia smw-lde:label "deepblue.rkbexplorer.com Linked Data Repository"^^xsd:string . 
	smwDatasources:dbpedia smw-lde:homepage <http://deepblue.rkbexplorer.com/> . 
	smwDatasources:dbpedia smw-lde:sampleURI <http://dbpedia.org/resource/Computer_science> . 
	smwDatasources:dbpedia smw-lde:sampleURI <http://dbpedia.org/resource/Organization> . 
	smwDatasources:dbpedia smw-lde:sparqlEndpointLocation <http://deepblue.rkbexplorer.com/sparql/> . 
	smwDatasources:dbpedia smw-lde:sparqlGraphName <http://example.org/deepblue> . 
	smwDatasources:dbpedia smw-lde:sparqlGraphPattern "FILTER (?p = <http://www.w3.org/1999/02/22-rdf-syntax-ns#type>)"^^xsd:string .
	smwDatasources:dbpedia smw-lde:dataDumpLocation <http://deepblue.rkbexplorer.com/datadDump> . 
	smwDatasources:dbpedia smw-lde:dataDumpLocation <http://deepblue.rkbexplorer.com/datadDump2> . 
	smwDatasources:dbpedia smw-lde:lastmod "2007-11-21T14:41:09+12:34"^^xsd:dateTime . 
	smwDatasources:dbpedia smw-lde:changefreq "daily"^^xsd:string . 
	smwDatasources:dbpedia smw-lde:vocabulary <http://purl.org/dc/elements/1.1/> . 
	smwDatasources:dbpedia smw-lde:vocabulary <http://xmlns.com/foaf/0.1/> . 
	smwDatasources:dbpedia smw-lde:vocabulary <http://www.w3.org/2000/01/rdf-schema#> . 
	smwDatasources:dbpedia smw-lde:predicateToCrawl <http://dbpedia.org/property/deathdate> . 
}		
EXP;
		$id = $persistencyID === true ? $sd->getID() : $persistencyID;
		TestLODSourceDefinitionSuite::checkPersistentTriples($this, $id, $expected, 
				"checkStorePersistentLSD#1 failed for ID $id.");
		
		// Delete the LSD and its persistent data
		$store->deleteSourceDefinition($id);
		
		// Verify that the definition no longer exists in the TS
		$lsd = $store->loadSourceDefinition($id);
		$this->assertEquals(null, $lsd);
		
		// Verify that the definition no longer exists in the persistence layer
		TestLODSourceDefinitionSuite::checkPersistentTriples($this, $id, "", 
				"testStorePersistentLSD#2 failed for ID $id.");
		
    	
    }
	
}


/**
 * This test case tests the parser function for LOD source definitions.
 * 
 * The triple store must be running.
 * 
 * @author thsc
 *
 */
class TestLSDParserFunction extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = FALSE;
	
	public static $mOrderOfArticleCreation;
	public static $mArticles;
	
	
	private $mLSD = array(
		"id" => "dbpedia",
		"ChangeFreq" => "daily",
		"DataDumpLocations" => array("http://deepblue.rkbexplorer.com/datadDump", "http://deepblue.rkbexplorer.com/datadDump2"),
		"Description" => "This repository contains data supplied from Deep Blue.",
		"Homepage" => "http://deepblue.rkbexplorer.com/",
		"Label" => "deepblue.rkbexplorer.com Linked Data Repository",
		"LastMod" => "2007-11-21T14:41:09+12:34",
		"SampleURIs" => array("http://dbpedia.org/resource/Computer_science", "http://dbpedia.org/resource/Organization"),
		"SparqlEndpointLocation" => "http://deepblue.rkbexplorer.com/sparql/",
		"SparqlGraphName" => "http://example.org/deepblue",
		"SparqlGraphPatterns" => array("FILTER (?p = <http://www.w3.org/1999/02/22-rdf-syntax-ns#type>)"),
		"Vocabularies" => array("http://purl.org/dc/elements/1.1/", "http://xmlns.com/foaf/0.1/", "http://www.w3.org/2000/01/rdf-schema#"),
		"PredicatesToCrawl" => array("http://dbpedia.org/property/deathdate"),
	);
	
    function setUp() {
    	// Create articles
    	$this->initArticleContent();
    }

    function tearDown() {
    	$this->removeArticles();
		LODStorage::getDatabase()->deleteAllPersistentTriples();
    }
    
    /**
     * Stores an article with a LOD source definition and checks if the triple
     * store contains the expected data.
     */
    function testLODParser() {
    	$this->createArticle("TestLODSourceDefinition", self::$mArticles["TestLODSourceDefinition"]);
    	
    	// Check the content of the triple store
    	TestLODSourceDefinitionSuite::checkLSDinTripleStore($this);
    	
    	// Check the content of the persistent store
    	$id = "TestLODSourceDefinition";
    	
		$expected = <<<EXP
@prefix xsd: <http://www.w3.org/2001/XMLSchema#> .
@prefix owl: <http://www.w3.org/2002/07/owl#> .
@prefix rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> .
@prefix rdfs: <http://www.w3.org/2000/01/rdf-schema#> .
@prefix smw-lde: <http://www.example.org/smw-lde/smw-lde.owl#> .
@prefix smwGraphs: <http://www.example.org/smw-lde/smwGraphs/> .
@prefix smwDatasources: <http://www.example.org/smw-lde/smwDatasources/> .

<http://www.example.org/smw-lde/smwGraphs/DataSourceInformationGraph> {
	smwDatasources:dbpedia rdf:type smw-lde:Datasource . 
	smwDatasources:dbpedia smw-lde:ID "dbpedia"^^xsd:string . 
	smwDatasources:dbpedia smw-lde:description "This repository contains data supplied from Deep Blue."^^xsd:string . 
	smwDatasources:dbpedia smw-lde:label "deepblue.rkbexplorer.com Linked Data Repository"^^xsd:string . 
	smwDatasources:dbpedia smw-lde:homepage <http://deepblue.rkbexplorer.com/> . 
	smwDatasources:dbpedia smw-lde:sampleURI <http://dbpedia.org/resource/Computer_science> . 
	smwDatasources:dbpedia smw-lde:sampleURI <http://dbpedia.org/resource/Organization> . 
	smwDatasources:dbpedia smw-lde:sparqlEndpointLocation <http://deepblue.rkbexplorer.com/sparql/> . 
	smwDatasources:dbpedia smw-lde:sparqlGraphName <http://example.org/deepblue> . 
	smwDatasources:dbpedia smw-lde:sparqlGraphPattern "FILTER (?p = <http://www.w3.org/1999/02/22-rdf-syntax-ns#type>)"^^xsd:string .
	smwDatasources:dbpedia smw-lde:dataDumpLocation <http://deepblue.rkbexplorer.com/datadDump> . 
	smwDatasources:dbpedia smw-lde:dataDumpLocation <http://deepblue.rkbexplorer.com/datadDump2> . 
	smwDatasources:dbpedia smw-lde:lastmod "2007-11-21T14:41:09+12:34"^^xsd:dateTime . 
	smwDatasources:dbpedia smw-lde:changefreq "daily"^^xsd:string . 
	smwDatasources:dbpedia smw-lde:vocabulary <http://purl.org/dc/elements/1.1/> . 
	smwDatasources:dbpedia smw-lde:vocabulary <http://xmlns.com/foaf/0.1/> . 
	smwDatasources:dbpedia smw-lde:vocabulary <http://www.w3.org/2000/01/rdf-schema#> . 
	smwDatasources:dbpedia smw-lde:predicateToCrawl <http://dbpedia.org/property/deathdate> . 
}
EXP;
    	TestLODSourceDefinitionSuite::checkPersistentTriples($this, $id, $expected, 
				"testLODParser failed for ID $id.");
    	
    }

    /**
     * Stores an article with a LOD source definition and then changes the content
     * of that article so that the LSD is removed. Verifies that the LSD is
     * removed from the triples store and the persistency layer.
     */
    function testRemoveLSDFromArticle() {
    	$this->createArticle("TestLODSourceDefinition", self::$mArticles["TestLODSourceDefinition"]);
    	$this->createArticle("TestLODSourceDefinition", "empty");
    	
    	// Check the content of the persistent store
    	$id = "TestLODSourceDefinition";
    	
		$expected = "";
    	TestLODSourceDefinitionSuite::checkPersistentTriples($this, $id, $expected, 
				"testRemoveLSDFromArticle failed for ID $id.");

    	// Check the content of the triple store. It must be empty.
		$store = LODAdministrationStore::getInstance();
		$lsd = $store->loadSourceDefinition(TestLODSourceDefinitionSuite::$mLSD["id"]);
		// please note that this test will fail until
		// http://smwforum.ontoprise.com/smwbugs/show_bug.cgi?id=12784
		// has been implemented.
		$this->assertNull($lsd);
   	
    }
    
    /**
     * Stores an article with a LOD source definition and then deletes it. 
     * Verifies that the LSD is removed from the triples store and the 
     * persistency layer.
     */
    function testDeleteArticleWithLSD() {
    	$this->createArticle("TestLODSourceDefinition", self::$mArticles["TestLODSourceDefinition"]);
		$this->removeArticles();
		    	
    	// Check the content of the persistent store
    	$id = "TestLODSourceDefinition";
    	
		$expected = "";
    	TestLODSourceDefinitionSuite::checkPersistentTriples($this, $id, $expected, 
				"testDeleteArticleWithLSD failed for ID $id.");

    	// Check the content of the triple store. It must be empty.
		$store = LODAdministrationStore::getInstance();
		$lsd = $store->loadSourceDefinition(TestLODSourceDefinitionSuite::$mLSD["id"]);
		// please note that this test will fail until
		// http://smwforum.ontoprise.com/smwbugs/show_bug.cgi?id=12784
		// has been implemented.
		$this->assertNull($lsd);
   	
    }
    
//--- Private functions --------------------------------------------------------
    
	private function createArticles() {
    	global $wgUser;
    	$wgUser = User::newFromName("WikiSysop");
    	
    	$file = __FILE__;
    	try {
	    	foreach (self::$mOrderOfArticleCreation as $title) {
				self::createArticle($title, self::$mArticles[$title]);
	    	}
    	} catch (Exception $e) {
			$this->assertTrue(false, "Unexpected exception while testing ".basename($file)."::createArticles():".$e->getMessage());
		}
    	
    }

	private function createArticle($title, $content) {
	
		$title = Title::newFromText($title);
		$article = new Article($title);
		// Set the article's content
		$success = $article->doEdit($content, 'Created for test case', 
		                            $article->exists() ? EDIT_UPDATE : EDIT_NEW);
		if (!$success) {
			echo "Creating article ".$title->getFullText()." failed\n";
		}
	}
     
	private function removeArticles() {
				
		foreach (self::$mOrderOfArticleCreation as $a) {
			global $wgTitle;
		    $wgTitle = $t = Title::newFromText($a);
	    	$article = new Article($t);
			$article->doDelete("Testing");
		}
		
	}
        
    private function initArticleContent() {
		self::$mOrderOfArticleCreation = array(
			'TestLODSourceDefinition',
		);
		
		self::$mArticles = array(
//------------------------------------------------------------------------------		
			'TestLODSourceDefinition' =>
<<<LODMD
{{#sourcedefinition:
 | id = dbpedia
 | ChangeFreq = daily
 | DataDumpLocation = http://deepblue.rkbexplorer.com/datadDump 
 | DataDumpLocation = http://deepblue.rkbexplorer.com/datadDump2
 | Description = This repository contains data supplied from Deep Blue.
 | Homepage = http://deepblue.rkbexplorer.com/
 | Label = deepblue.rkbexplorer.com Linked Data Repository
 | LastMod = 2007-11-21T14:41:09+12:34
 | SampleURI = http://dbpedia.org/resource/Computer_science 
 | SampleURI = http://dbpedia.org/resource/Organization
 | SparqlEndpointLocation = http://deepblue.rkbexplorer.com/sparql/
 | SparqlGraphName = http://example.org/deepblue
 | SparqlGraphPattern = FILTER (?p = <http://www.w3.org/1999/02/22-rdf-syntax-ns#type>)
 | Vocabulary = http://purl.org/dc/elements/1.1/ 
 | Vocabulary = http://xmlns.com/foaf/0.1/ 
 | Vocabulary = http://www.w3.org/2000/01/rdf-schema#
 | PredicateToCrawl = http://dbpedia.org/property/deathdate
}}
LODMD

		);
	}
	
    
}