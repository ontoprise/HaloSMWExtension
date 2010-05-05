<?php
/**
 * @file
 * @ingroup LinkedData_Tests
 */

require_once 'PHPUnit/Framework.php';

class TestLODSourceDefinition extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = FALSE;
	
	private $mLSD = array(
		"id" => "dbpedia",
		"ChangeFreq" => "daily",
		"DataDumpLocations" => array("http://deepblue.rkbexplorer.com/datadDump", "http://deepblue.rkbexplorer.com/datadDump2"),
		"Description" => "This repository contains data supplied from Deep Blue.",
		"Homepage" => "http://deepblue.rkbexplorer.com/",
		"ImportanceIndex" => 42,
		"Label" => "deepblue.rkbexplorer.com Linked Data Repository",
		"LastMod" => "2007-11-21T14:41:09+12:34",
		"MappingID" => "deepbule-mapping",
		"LinkedDataPrefix" => "http://dbpedia.org/resource/",
		"SampleURIs" => array("http://dbpedia.org/resource/Computer_science", "http://dbpedia.org/resource/Organization"),
		"SparqlEndpointLocation" => "http://deepblue.rkbexplorer.com/sparql/",
		"SparqlGraphName" => "deepblue",
		"UriRegexPattern" => "^http://deepblue.rkbexplorer.com/id/.+",
		"Vocabularies" => array("dc", "foaf", "rdfs"),
	
	);
	
    function setUp() {
    }

    function tearDown() {

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
		$sd = self::createLSD();		
		$r = $store->storeSourceDefinition($sd);
		
		$this->assertTrue($r);
	}
	
	
	/**
	 * Tests loading a LODSourceDefinition object from the Triple Store
	 *
	 */
	function testLoadLSD() {
		$store = LODAdministrationStore::getInstance();
		$lsd = $store->loadSourceDefinition($this->mLSD["id"]);
		
		$this->assertNotNull($lsd);
		$this->assertEquals($this->mLSD["id"], $lsd->getID());
		$this->assertEquals($this->mLSD["ChangeFreq"], $lsd->getChangeFreq());
		$this->assertEquals($this->mLSD["Description"], $lsd->getDescription());
		$this->assertEquals($this->mLSD["Homepage"], $lsd->getHomepage());
		$this->assertEquals($this->mLSD["ImportanceIndex"], $lsd->getImportanceIndex());
		$this->assertEquals($this->mLSD["Label"], $lsd->getLabel());
		$this->assertEquals($this->mLSD["LastMod"], $lsd->getLastMod());
		$this->assertEquals($this->mLSD["MappingID"], $lsd->getMappingID());
		$this->assertEquals($this->mLSD["LinkedDataPrefix"], $lsd->getLinkedDataPrefix());
		$this->assertEquals($this->mLSD["SparqlEndpointLocation"], $lsd->getSparqlEndpointLocation());
		$this->assertEquals($this->mLSD["SparqlGraphName"], $lsd->getSparqlGraphName());
		$this->assertEquals($this->mLSD["UriRegexPattern"], $lsd->getUriRegexPattern());

		// Compare results that are arrays
		$r = $lsd->getDataDumpLocations();
		$this->assertEquals(count($this->mLSD["DataDumpLocations"]), count($r));
		foreach ($this->mLSD["DataDumpLocations"] as $v) {
			$this->assertContains($v, $r);
		}

		$r =  $lsd->getSampleURIs();
		$this->assertEquals(count($this->mLSD["SampleURIs"]), count($r));
		foreach ($this->mLSD["SampleURIs"] as $v) {
			$this->assertContains($v, $r);
		}
		
		$r = $lsd->getVocabularies();
		$this->assertEquals(count($this->mLSD["Vocabularies"]), count($r));
		foreach ($this->mLSD["Vocabularies"] as $v) {
			$this->assertContains($v, $r);
		}
		
	}
	
	/**
	 * Tests deleting LODSourceDefinition object from the Triple Store
	 *
	 */
	function testDeleteLSD() {
		$store = LODAdministrationStore::getInstance();
		$store->deleteSourceDefinition($this->mLSD["id"]);
		
		// Make sure that the source definition is no longer available
		$lsd = $store->loadSourceDefinition($this->mLSD["id"]);
		
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
		$store->storeSourceDefinition(self::createLSD());
		// ... and delete all definitions
		$store->deleteAllSourceDefinitions();
		
		// Make sure that the source definition no longer exists
		$this->assertEquals(null, $store->loadSourceDefinition($this->mLSD["id"]));
	}
	
//--- Helper functions ---
    
	/**
	 * Creates a LODSourceDefinition object
	 * @return LODSourceDefinition
	 * 		A sample object
	 */
	private function createLSD() {
		$sd = new LODSourceDefinition($this->mLSD["id"]);
		$sd->setChangeFreq($this->mLSD["ChangeFreq"]);
		$sd->setDataDumpLocations($this->mLSD["DataDumpLocations"]);
		$sd->setDescription($this->mLSD["Description"]);
		$sd->setHomepage($this->mLSD["Homepage"]);
		$sd->setImportanceIndex($this->mLSD["ImportanceIndex"]);
		$sd->setLabel($this->mLSD["Label"]);
		$sd->setLastMod($this->mLSD["LastMod"]);
		$sd->setMappingID($this->mLSD["MappingID"]);
		$sd->setLinkedDataPrefix($this->mLSD["LinkedDataPrefix"]);
		$sd->setSampleURIs($this->mLSD["SampleURIs"]);
		$sd->setSparqlEndpointLocation($this->mLSD["SparqlEndpointLocation"]);
		$sd->setSparqlGraphName($this->mLSD["SparqlGraphName"]);
		$sd->setUriRegexPattern($this->mLSD["UriRegexPattern"]);
		$sd->setVocabularies($this->mLSD["Vocabularies"]);
		return $sd;
	}
    
}
