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

	private $mMappingSource = "dbpedia";
	private $mMappingTarget = "wikipedia";
	private $mMappingText1;
	private $mMappingText2;

	function setUp() {
		$this->mMappingText1 = <<<text
r2r:personmapping
	rdf:type r2r:ClassMapping ;
	r2r:sourcePattern "?s rdf:type source:Person . ?s source:gender 'f'. ?s rdf:typ source:Adult." ;
	r2r:targetPattern "?s rdf:type target:FemaleAdult." .
text;

		$this->mMappingText2 = <<<text
r2r:classMapping
	a r2r:ClassMapping ;
	r2r:sourcePattern "?s rdf:type foaf:Person" ;
	r2r:prefixDefinitions "foaf: <http://xmlns.com/foaf/0.1/>" ;
	r2r:targetPattern "?s rdf:type <http://other/Person>" .
text;
	}

	function tearDown() {
		$store = new LODPersistentMappingStore(new LODMappingTripleStore());
		$store->removeAllMappings();
		
		$store = new LODMappingTripleStore();
		$store->removeAllMappings();
		
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
			
		$mapping = new LODMapping($this->mMappingText1, "dbpedia", "wiki");
		$this->assertNotNull($mapping);
			
		// Test mapping with default target.
		$mapping = new LODMapping($this->mMappingText1, "dbpedia");
		$this->assertNotNull($mapping);
			
		$this->assertEquals($this->mMappingText1, $mapping->getMappingText());
		$this->assertEquals("dbpedia", $mapping->getSource());
		global $lodgDefaultMappingTarget;
		$this->assertEquals($lodgDefaultMappingTarget, $mapping->getTarget());
			
	}

	function testLODMappingsStore() {
		$this->doTestLODMappingStore(new MockMappingStore());
		$this->doTestLODMappingStore(new LODMappingTripleStore());
		$this->doTestLODMappingStore(new LODPersistentMappingStore(new LODMappingTripleStore()));
	}

	function testMappingsInArticles() {
		$this->doTestMappingsInArticles(new MockMappingStore());
		$this->doTestMappingsInArticles(new LODMappingTripleStore());
		$this->doTestMappingsInArticles(new LODPersistentMappingStore(new LODMappingTripleStore()));
	}


	/**
	 * Tests the class LODMappingStore which stores, loads and deletes mappings
	 *
	 */
	function doTestLODMappingStore($theStore) {
			
		// Get the mapping store and configure the actual store.
		// The store is used when the article with a mapping is saved.
		LODMappingStore::setStore($theStore);
		// Get the mapping store
		$store = LODMappingStore::getStore();
		$this->assertNotNull($store);

		// Store mappings
		$mapping = new LODMapping($this->mMappingText1, "dbpedia", "wiki");
		$r = $store->addMapping($mapping);
		$this->assertTrue($r);
		$mapping = new LODMapping($this->mMappingText1, "dbpedia", "wiki1");
		$r = $store->addMapping($mapping);
		$this->assertTrue($r);
		$mapping = new LODMapping($this->mMappingText1, "dbpedia1", "wiki");
		$r = $store->addMapping($mapping);
		$this->assertTrue($r);

		// Load the mapping with the saved source and target.
		$mappings = $store->getAllMappings("dbpedia", "wiki");
		$this->assertNotNull($mappings);
		$mapping = $mappings[0];

		// Make sure that the correct mapping text was saved and loaded.
		//print_r("Exp: ".self::removeWhitespaces($this->mMappingText1));
		//print_r("Actual: ".self::removeWhitespaces($mapping->getMappingText()));

		// FIXME: order may be different
		//$this->assertEquals($this->mMappingText1, $mapping->getMappingText());

		// Load all mappings for a source.
		$mappings = $store->getAllMappings("dbpedia");
		$this->assertNotNull($mappings);
		$this->assertEquals(2, count($mappings));
		$this->assertEquals("dbpedia", $mappings[0]->getSource());
		$this->assertEquals("dbpedia", $mappings[1]->getSource());

		// Load all mappings for a target.
		$mappings = $store->getAllMappings(null, "wiki");
		$this->assertNotNull($mappings);
		$this->assertEquals(2, count($mappings));
		$this->assertEquals("wiki", $mappings[0]->getTarget());
		$this->assertEquals("wiki", $mappings[1]->getTarget());

		// Load all existing mappings.
		$mappings = $store->getAllMappings();
		$this->assertNotNull($mappings);
		$this->assertEquals(3, count($mappings));

		// Delete a specific mapping
		$store->removeAllMappings("dbpedia", "wiki");
			
		// Try to load the deleted mapping and make sure it no longer exists.
		$mappings = $store->getAllMappings("dbpedia", "wiki");
		$this->assertEquals(0, count($mappings));

		// Delete mappings with source wildcard
		$mapping = new LODMapping($this->mMappingText1, "dbpedia", "wiki");
		$r = $store->addMapping($mapping);

		$store->removeAllMappings("dbpedia");
			
		// Try to load the deleted mapping and make sure it no longer exists.
		$mappings = $store->getAllMappings("dbpedia");
		$this->assertEquals(0, count($mappings));

		// Delete mappings with target wildcard
		$mapping = new LODMapping($this->mMappingText1, "dbpedia", "wiki");
		$r = $store->addMapping($mapping);
		$mapping = new LODMapping($this->mMappingText1, "dbpedia", "wiki1");
		$r = $store->addMapping($mapping);

		$store->removeAllMappings(null, "wiki");
			
		// Try to load the deleted mapping and make sure it no longer exists.
		$mappings = $store->getAllMappings(null, "wiki");
		$this->assertEquals(0, count($mappings));

		// make sure to remove all mappings
		$store->removeAllMappings(null, null);
	}


	/**
	 * This function creates an article with a <mapping> tag. The content of this
	 * tag must be stored, retrieved and deleted.
	 *
	 */
	function doTestMappingsInArticles($theStore) {
		global $lodgDefaultMappingTarget;
			
		$articleName = "Mapping:{$this->mMappingSource}";
		// Get the mapping store and configure the actual store.
		// The store is used when the article with a mapping is saved.
		LODMappingStore::setStore($theStore);

		// Get the mapping store
		$store = LODMappingStore::getStore();
		$this->assertNotNull($store);

		// Make sure there are no sources and targets in the store
		$sources = $store->getAllSources();
		$this->assertEquals(0, count($sources));
		$targets = $store->getAllTargets();
		$this->assertEquals(0, count($targets));

		// Make sure the mapping is not stored yet
		$this->assertFalse($store->existsMapping($this->mMappingSource, $this->mMappingTarget));
			
		// Create article with <mapping> tags and make sure its content is stored
		// with the LODMappingStore
		$article = new Article(Title::newFromText($articleName));
		$text = <<<text
This is the first mapping:
<mapping target = "{$this->mMappingTarget}">
		{$this->mMappingText1}
</mapping>

And this is the second mapping with default target:
<mapping>
		{$this->mMappingText2}
</mapping>
text;
		$article->doEdit($text, "");

		// Make sure the mappings exist
		$this->assertTrue($store->existsMapping(ucfirst($this->mMappingSource), $this->mMappingTarget));
		$this->assertTrue($store->existsMapping(ucfirst($this->mMappingSource), $lodgDefaultMappingTarget));

		// Load the mappings with the for the saved source and targets.
		$mapping = $store->getAllMappings(ucfirst($this->mMappingSource), $this->mMappingTarget);
		$this->assertNotNull($mapping);

		// Remove linefeeds from the mappings for comparison
		$mt = preg_replace("/\s/","", $this->mMappingText1);
		$mmt = preg_replace("/\s/","", $mapping[0]->getMappingText());

		// Make sure that the correct mapping text was saved and loaded.
		//$this->assertEquals($mt, $mmt);

		$mapping = $store->getAllMappings(ucfirst($this->mMappingSource), $lodgDefaultMappingTarget);
		$this->assertNotNull($mapping);

		// Remove linefeeds from the mappings for comparison
		$mt = preg_replace("/\s/","", $this->mMappingText2);
		$mmt = preg_replace("/\s/","", $mapping[0]->getMappingText());

		// Make sure that the correct mapping text was saved and loaded.
		//$this->assertEquals($mt, $mmt);

		// Make sure that all sources and targets can be retrieved
		$sources = $store->getAllSources();
		$this->assertContains(ucfirst($this->mMappingSource), $sources);
		$targets = $store->getAllTargets();
		$this->assertContains($this->mMappingTarget, $targets);
		$this->assertContains($lodgDefaultMappingTarget, $targets);
		$this->assertEquals(1, count($sources));
		$this->assertEquals(2, count($targets));

		// Delete the article and make sure its content is removed from the triple
		// store.
		try {
			$article->doDelete($articleName);
		} catch (MWException $e) {
			// Due to the calling environment an exception is thrown.
		}

		// Load the mapping with the source and target of the mapping that was
		// saved as an article.
		$mappings = $store->getAllMappings($this->mMappingSource, $this->mMappingTarget);
		$this->assertEquals(0, count($mappings));

		// Make sure there are no sources and targets in the store
		$sources = $store->getAllSources();
		$this->assertEquals(0, count($sources));
		$targets = $store->getAllTargets();
		$this->assertEquals(0, count($targets));

	}


}

class MockMappingStore implements ILODMappingStore {

	// array(string=>string)
	// Maps from a mapping ID to the mapping text
	private static $mMapping;

	function __construct() {
		self::$mMapping = array();
	}

	public function existsMapping($source, $target) {
		return array_key_exists(strtolower($source).'---'.strtolower($target), self::$mMapping);
	}

	public function addMapping(LODMapping $mapping) {
		$key = strtolower($mapping->getSource()).'---'.strtolower($mapping->getTarget());
		if (array_key_exists($key, self::$mMapping)) {
			self::$mMapping[$key] = self::$mMapping[$key]."\n".$mapping->getMappingText();
		} else {
			self::$mMapping[$key] = $mapping->getMappingText();
		}
		return true;
	}

	public function getAllMappings($source = null, $target = null) {
		$mappings = array();
		foreach (self::$mMapping as $key => $mapping) {
			$addMapping = false;
			$s = substr($key, 0, strpos($key, "---"));
			$t = substr($key, strpos($key, "---")+3);

			if ($source == null && $target == null) {
				$addMapping = true;
			} else if ($source == null && $target != null) {
				$addMapping = $t == strtolower($target);
			} else if ($source !== null && $target == null) {
				$addMapping = $s == strtolower($source);
			} else {
				$source = strtolower($source);
				$target = strtolower($target);
				$addMapping = ($key == "$source---$target");
			}
			if ($addMapping) {
				$mappings[] = new LODMapping(self::$mMapping[$key], $s, $t);
			}
		}
		return $mappings;
	}

	public function removeAllMappings($source = null, $target = null) {
		foreach (self::$mMapping as $key => $mapping) {
			$delMapping = false;
			$s = substr($key, 0, strpos($key, "---"));
			$t = substr($key, strpos($key, "---")+3);
			if ($source == null && $target == null) {
				$delMapping = true;
			} else if ($source == null && $target != null) {
				$delMapping = $t == strtolower($target);
			} else if ($source !== null && $target == null) {
				$delMapping = $s == strtolower($source);
			} else {
				$source = strtolower($source);
				$target = strtolower($target);

				$delMapping = ($key == "$source---$target");
			}
			if ($delMapping) {
				unset(self::$mMapping[$key]);
			}
		}

	}

	public function getAllSources() {
		$sources = array();
		foreach (self::$mMapping as $key => $m) {
			$s = substr($key, 0, strpos($key, "---"));
			if (!in_array($s, $sources)) {
				$sources[] = ucfirst($s);
			}
		}
		return array_unique($sources);
	}

	public function getAllTargets() {
		$targets = array();
		foreach (self::$mMapping as $key => $m) {
			$t = substr($key, strpos($key, "---")+3);
			if (!in_array($t, $targets)) {
				$targets[] = $t;
			}
		}
		return $targets;
	}



}