<?php
/**
 * @file
 * @ingroup LinkedData_Tests
 */

require_once 'PHPUnit/Framework.php';


/**
 * This class tests the
 * - namespace "Mapping"
 * - class "LODMapping"
 * - mapping store
 * - mapping function for articles in the namespace "Mapping" that contain the
 *   tag <mapping> 
 *
 */
class TestMapping extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = FALSE;
	
	private $mMappingID = "TestMapping";
    private $mMappingText1;
    private $mMappingText2;
		
    function setUp() {
		$this->mMappingText1 = <<<text
somewhere:personmapping
	rdf:type r2r:ClassMapping ;
	r2r:sourcePattern "?s rdf:type source:Person .
	                   ?s source:gender 'f'.
                           ?s rdf:typ source:Adult." ;
	r2r:targetPattern "?s rdf:type target:FemaleAdult." .
text;

		$this->mMappingText2 = <<<text
    p:classMapping
        a r2r:ClassMapping ;
        r2r:sourcePattern "?s rdf:type foaf:Person" ;
        r2r:prefixDefinitions "foaf: <http://xmlns.com/foaf/0.1/>" ;
        r2r:targetPattern "?s rdf:type <http://other/Person>" .
text;
    }

    function tearDown() {

    }

    /**
     * Verifies the existens of the namespace "Mapping"
     */
    function testMappingNamespace() {
    	global $wgExtraNamespaces, $lodgContLang;
    	
    	// Test existence of namespace indices
    	$this->assertArrayHasKey(LOD_NS_LOD, $wgExtraNamespaces);
    	$this->assertArrayHasKey(LOD_NS_LOD_TALK, $wgExtraNamespaces);

    	$this->assertArrayHasKey(LOD_NS_MAPPING, $wgExtraNamespaces);
    	$this->assertArrayHasKey(LOD_NS_MAPPING_TALK, $wgExtraNamespaces);
    	
    	// Make sure, the correct language strings are assigned to the namespace
    	// indices. Otherwise another extension might have overwritten the namespace.
    	$namespaces = $lodgContLang->getNamespaces();
    	$this->assertEquals($wgExtraNamespaces[LOD_NS_LOD], $namespaces[LOD_NS_LOD]);
    	$this->assertEquals($wgExtraNamespaces[LOD_NS_LOD_TALK], $namespaces[LOD_NS_LOD_TALK]);
    	$this->assertEquals($wgExtraNamespaces[LOD_NS_MAPPING], $namespaces[LOD_NS_MAPPING]);
    	$this->assertEquals($wgExtraNamespaces[LOD_NS_MAPPING_TALK], $namespaces[LOD_NS_MAPPING_TALK]);
    }
    
    /**
     * Tests the class LODMapping
     *
     */
    function testLODMapping() {
    	
    	$mapping = new LODMapping($this->mMappingID, $this->mMappingText1);
    	$this->assertNotNull($mapping);
    	
    	$this->assertEquals($this->mMappingText1, $mapping->getMappingText());    	
    	
    }
    
    /**
     * Tests the class LODMappingStore which stores, loads and deletes mappings
     *
     */
    function testLODMappingStore() {
    	
    	$mapping = new LODMapping($this->mMappingID, $this->mMappingText1);
        // Get the mapping store and configure its I/O strategy.
        // The store is used when the article with a mapping is saved.
        LODMappingStore::setIOStrategy(new MockMappingIOStrategy());
        // Get the mapping store
        $store = LODMappingStore::getInstance();
        $this->assertNotNull($store);
        
        // Store a mapping
        $r = $store->saveMapping($mapping);
        $this->assertTrue($r);
        
        // Load the mapping with the ID of the mapping that was saved.
        $mapping = $store->loadMapping($this->mMappingID);
        $this->assertNotNull($mapping);
        
        // Make sure that the correct mapping text was saved and loaded.
        $this->assertEquals($this->mMappingText1, $mapping->getMappingText());
    	
    	// Delete the mapping
    	$store->deleteMapping($this->mMappingID);
    	
        // Try to load the deleted mapping and make sure it no longer exists.
        $mapping = $store->loadMapping($this->mMappingID);
        $this->assertEquals(null, $mapping);
        
    }
    
    
    /**
     * This function creates an article with a <mapping> tag. The content of this
     * tag must be stored, retrieved and deleted.
     *
     */
    function testMappingsInArticles() {
    	$articleName = "Mapping:{$this->mMappingID}";
        // Get the mapping store and configure its I/O strategy.
        // The store is used when the article with a mapping is saved.
        LODMappingStore::setIOStrategy(new MockMappingIOStrategy());
        
        // Get the mapping store
        $store = LODMappingStore::getInstance();
        $this->assertNotNull($store);
        
        // Make sure the mapping is not stored yet
        $this->assertFalse($store->existsMapping($this->mMappingID));
    	
    	// Create article with <mapping> tags and make sure its content is stored
    	// with the LODMappingStore
    	$article = new Article(Title::newFromText($articleName));
    	$text = <<<text
This is the first mapping:
<mapping>{$this->mMappingText1}</mapping>

And this is the second mapping:
<mapping>{$this->mMappingText2}</mapping>
text;
        $article->doEdit($text, "");
                
        // Make sure the mapping exists
        $this->assertTrue($store->existsMapping($this->mMappingID));
        
        // Load the mapping with the ID of the mapping that was saved as an
        // article.
        $mapping = $store->loadMapping($this->mMappingID);
        $this->assertNotNull($mapping);
        
        // Remove linefeeds from the mappings for comparison
        $mt = str_replace("\n", "", $this->mMappingText1.$this->mMappingText2);
        $mt = str_replace("\r", "", $mt);
        $mmt = str_replace("\n", "", $mapping->getMappingText());
        $mmt = str_replace("\r", "", $mmt);
        
        // Make sure that the correct mapping text was saved and loaded.
        $this->assertEquals($mt, $mmt);
    	
    	// Delete the article and make sure its content is removed from the triple
    	// store.
    	try {
        	$article->doDelete($articleName);
    	} catch (MWException $e) {
    		// Due to the calling environment an exception is thrown.	
    	}
    	
        // Load the mapping with the ID of the mapping that was saved as an
        // article.
        $mapping = $store->loadMapping($this->mMappingID);
        $this->assertEquals(null, $mapping);
    	
    }
    
        
}

class MockMappingIOStrategy implements ILODMappingIOStrategy {
	
	// array(string=>string)
	// Maps from a mapping ID to the mapping text
	private static $mMapping;
	
	function __construct() {
		self::$mMapping = array();
	}

	public function existsMapping($mappingID) {
		return array_key_exists($mappingID, self::$mMapping);
	}
	
	public function saveMapping(LODMapping $mapping) {
		self::$mMapping[$mapping->getID()] = $mapping->getMappingText();
		return true;
	}

	public function loadMapping($mappingID) {
		if (array_key_exists($mappingID, self::$mMapping)) {
			return new LODMapping($mappingID, self::$mMapping[$mappingID]);
		}
		return  null;
	}
	
	public function deleteMapping($mappingID) {
		unset(self::$mMapping[$mappingID]);
	}
	
}
