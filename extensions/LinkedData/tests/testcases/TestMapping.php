<?php
/**
 * @file
 * @ingroup LinkedData_Tests
 */


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
	private $mMappingText3;
	
	private $mintNamespace = 'http://halowiki/ob/a#';
	private $mintLabelPredicate = 'http://wiki/GeneSymbol';
	
	function setUp() {
		$this->mMappingText1 = 'r2r:personmapping rdf:type r2r:ClassMapping ;'
			." r2r:sourcePattern \"?s rdf:type source:Person . ?s source:gender 'f'. ?s rdf:typ source:Adult.\" ;"
			.' r2r:targetPattern "?s rdf:type target:FemaleAdult." .';

		$this->mMappingText2 = 'r2r:classMapping a r2r:ClassMapping ;'
			.' r2r:sourcePattern "?s rdf:type foaf:Person" ;'
			.' r2r:prefixDefinitions "foaf: <http://xmlns.com/foaf/0.1/>" ;'
			.' r2r:targetPattern "?s rdf:type <http://other/Person>" .';

		$this->mMappingText3 = 'r2r:classMapping a r2r:ClassMapping ;'
			.' r2r:sourcePattern "?s rdf:type foaf:Automobile" ;'
			.' r2r:prefixDefinitions "foaf: <http://xmlns.com/foaf/0.1/>" ;'
			.' r2r:targetPattern "?s rdf:type <http://other/Automobile>" .';
	}

	function tearDown() {
		$articleName = "Mapping:".ucfirst($this->mMappingSource);
		
		$store = new LODMappingTripleStore();
		$store->removeAllMappingsFromPage($articleName);
		
		$tsa = new LODTripleStoreAccess();
		$tsa->dropGraph("http://www.example.org/smw-lde/smwGraphs/MappingRepository");
		$tsa->flushCommands();
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
	 * Tests the class LODR2RMapping
	 *
	 */
	function testLODR2RMapping() {
		$mapping = new LODR2RMapping($this->mMappingText1, $this->mMappingSource, $this->mMappingTarget);
		$this->assertNotNull($mapping);
			
		// Test mapping with default target.
		$mapping = new LODR2RMapping($this->mMappingText1, $this->mMappingSource);
		$this->assertNotNull($mapping);
			
		$this->assertEquals($this->mMappingText1, $mapping->getMappingText());
		$this->assertEquals($this->mMappingSource, $mapping->getSource());
		global $lodgDefaultMappingTarget;
		$this->assertEquals($lodgDefaultMappingTarget, $mapping->getTarget());
		
		//TODO: SILK
	}

	function testLODMappingsStore() {
		//$this->doTestLODMappingStore(new MockMappingStore());
		$this->doTestLODMappingStore(new LODMappingTripleStore());
	}

	function testMappingsInArticles() {
		//$this->doTestMappingsInArticles(new MockMappingStore());
		$this->doTestMappingsInArticles(new LODMappingTripleStore());
	}

	/**
	 * Tests the life cycle of a mapping article: create with mapping, 
	 * add a mapping, remove a mapping, remove all mappings, delete article.
	 */
	function testMappingArticleLifeCycle() {
		//$this->doTestMappingArticleLifeCycle(new MockMappingStore());
		$this->doTestMappingArticleLifeCycle(new LODMappingTripleStore());
	}

	/**
	 * Tests the class LODMappingStore which stores, loads and deletes mappings
	 *
	 */
	function doTestLODMappingStore($theStore) {
		$articleName = "Mapping:".ucfirst($this->mMappingSource);
		
		// Get the mapping store and configure the actual store.
		// The store is used when the article with a mapping is saved.
		LODMappingStore::setStore($theStore);
		// Get the mapping store
		$store = LODMappingStore::getStore();
		$this->assertNotNull($store);

		// Store three mappings
		$mapping1 = new LODR2RMapping($this->mMappingText1, "dbpedia", "wiki");
		$r = $store->addMapping($mapping1, $articleName);
		$this->assertTrue($r);
		$mapping2 = new LODR2RMapping($this->mMappingText1, "dbpedia", "wiki1");
		$r = $store->addMapping($mapping2, $articleName);
		$this->assertTrue($r);
		$mapping3 = new LODR2RMapping($this->mMappingText1, "dbpedia1", "wiki");
		$r = $store->addMapping($mapping3, $articleName);
		$this->assertTrue($r);	
		
		// Load the mapping with the saved source and target.
		$mappings = $store->getAllMappings("dbpedia", "wiki");
		$this->assertTrue(is_array($mappings));
		$idxs = array_keys($mappings);
		$this->assertTrue($mapping1->equals($mappings[$idxs[0]]));

		// Load all mappings for a source.
		$mappings = $store->getAllMappings("dbpedia");
		$idxs = array_keys($mappings);
		$this->assertTrue(is_array($mappings));
		$this->assertEquals(2, count($mappings));
		$this->assertEquals("dbpedia", $mappings[$idxs[0]]->getSource());
		$this->assertEquals("dbpedia", $mappings[$idxs[1]]->getSource());

		// Load all mappings for a target.
		$mappings = $store->getAllMappings(null, "wiki");
		$idxs = array_keys($mappings);
		$this->assertTrue(is_array($mappings));
		$this->assertEquals(2, count($mappings));
		$this->assertEquals("wiki", $mappings[$idxs[0]]->getTarget());
		$this->assertEquals("wiki", $mappings[$idxs[1]]->getTarget());

		// Load all existing mappings.
		$mappings = $store->getAllMappings();
		$this->assertTrue(is_array($mappings));
		$this->assertEquals(3, count($mappings));

		// Delete a specific mapping
		$store->removeAllMappings("dbpedia", "wiki", $articleName);
		$mappings = $store->getAllMappings("dbpedia", "wiki");
		$this->assertTrue(is_array($mappings));
		$this->assertEquals(0, count($mappings));
		
		//Test adding a SILK mapping
		$mapping = new LODSILKMapping($this->getSILKMappingText(), 
			"dbpedia", "wiki", '<'.$this->mintNamespace.'>', explode(' ', '<'.$this->mintLabelPredicate.'>'));
		$r = $store->addMapping($mapping, $articleName);
		$this->assertTrue($r);
		$mappings = $store->getAllMappings("dbpedia", "wiki");
		$this->assertTrue(is_array($mappings));
		$idxs = array_keys($mappings);
		$this->assertTrue($mapping->equals($mappings[$idxs[0]]));
		
		//Test removing SILK Mapping
		$store->removeAllMappings("dbpedia", "wiki", $articleName);
		$mappings = $store->getAllMappings("dbpedia", "wiki");
		$this->assertTrue(is_array($mappings));
		$this->assertEquals(0, count($mappings));
	}


	/**
	 * This function creates an article with a <mapping> tag. The content of this
	 * tag must be stored, retrieved and deleted.
	 *
	 */
	function doTestMappingsInArticles($store) {
		global $lodgDefaultMappingTarget;
			
		$articleName = "Mapping:".ucfirst($this->mMappingSource);
		$article = new Article(Title::newFromText($articleName));
		
		// Create article with <mapping> tags and make sure its content is stored
		// with the LODMappingStore
		$mySource = 'My'.$this->mMappingSource;
		$myTarget = 'My'.$this->mMappingTarget;
		
		$text = 'This is a mapping without a source attribute \r\n';
		$text .= '<r2rMapping target = "'.$this->mMappingTarget.'">'.
			$this->mMappingText1.'</r2rMapping>';
		
		$text .= 'And this is a mapping with default target\r\n';
		$text .= '<r2rMapping>'.$this->mMappingText2.'</r2rMapping>';

		$text .= 'Finally the last mapping with explicit source and target \r\n';
		$text .='<r2rMapping source="'.$mySource.'" target="'.$myTarget.'">'.
			$this->mMappingText3.'</r2rMapping>';

		$article->doEdit($text, "");

		// Make sure the mappings exist
		$mwSource = ucfirst($this->mMappingSource);
		
		file_put_contents('d://mapi.rtf', $store->getAllMappings($mwSource, $myTarget), true);

		$mapping = new LODR2RMapping($this->mMappingText1, $mwSource, $this->mMappingTarget);
		$this->assertTrue($store->existsMapping($mapping));
		$mapping = new LODR2RMapping($this->mMappingText2, $mwSource, $lodgDefaultMappingTarget);
		$this->assertTrue($store->existsMapping($mapping));
		$mapping = new LODR2RMapping($this->mMappingText3, $mySource, $myTarget);
		$this->assertTrue($store->existsMapping($mapping));
		
		
		// Check that the source-target pair of all mappings of the article
		// is stored in the DB
		$sourceTargetPairs = $store->getMappingsInArticle($articleName);
		$this->assertContains(array($mwSource, $this->mMappingTarget), $sourceTargetPairs); 
		$this->assertContains(array($mwSource, $lodgDefaultMappingTarget), $sourceTargetPairs); 
		$this->assertContains(array($mySource, $myTarget), $sourceTargetPairs); 
		
		// Delete the article and make sure its content is removed from the triple store.
		try {
			$article->doDelete($articleName);
		} catch (MWException $e) {
			// Due to the calling environment an exception is thrown.
		}

		// Load the mapping with the source and target of the mapping that was
		// saved as an article.
		$mappings = $store->getAllMappings($this->mMappingSource, $this->mMappingTarget);
		$this->assertTrue(is_array($mappings));
		$this->assertEquals(0, count($mappings));

		// Make sure source-target-pairs for the article are removed from the DB
		$sourceTargetPairs = $store->getMappingsInArticle($articleName);
		$this->assertTrue(is_array($mappings));
		$this->assertEquals(0, count($sourceTargetPairs));

	}
	/**
	 * Tests the life cycle of a mapping article: create with mapping, 
	 * add a mapping, remove a mapping, remove all mappings, delete article.
	 *
	 */
	function doTestMappingArticleLifeCycle($store) {
		global $lodgDefaultMappingTarget;

		$articleName = "Mapping:".ucfirst($this->mMappingSource);
		$article = new Article(Title::newFromText($articleName));
		
		// Create article with <mapping> tags and make sure its content is stored
		// with the LODMappingStore
		$mySource = 'My'.$this->mMappingSource;
		$myTarget = 'My'.$this->mMappingTarget;

		//-Save the article with one mapping ---		
		$text1 = '<r2rMapping source="'.$mySource.'" target="'.$myTarget.'">'
			.$this->mMappingText1.'</r2rMapping>';
		
		$article->doEdit($text1, "");

		// Make sure the mapping exists
		$mapping = new LODR2RMapping($this->mMappingText1, $mySource, $myTarget);
			$this->assertTrue($store->existsMapping($mapping));
		
		// Check that the source-target pair of the mapping of the article is stored in the DB
		$sourceTargetPairs = $store->getMappingsInArticle($articleName);
		$this->assertEquals(1, count($sourceTargetPairs), "Expected exactly one mapping.");
		$this->assertContains(array($mySource, $myTarget), $sourceTargetPairs); 
		
		// Load the mappings with the saved source and targets.
		$mappings = $store->getAllMappings($mySource, $myTarget);
		$this->assertTrue(is_array($mappings));
		$this->assertEquals(1, count($mappings));

		// Save the article with an additional mapping
		$mySource1 = 'MyOther'.$this->mMappingSource;
		$myTarget1 = 'MyOther'.$this->mMappingTarget;
		
		$text2 = '\r\n <r2rMapping source="'.$mySource1.'" target="'.$myTarget1.'">'
			.$this->mMappingText2.'</r2rMapping>';

		$article->doEdit($text1.$text2, "");

		// Make sure the mappings exists
		$mapping = new LODR2RMapping($this->mMappingText1, $mySource, $myTarget);
		$this->assertTrue($store->existsMapping($mapping));
		$mapping = new LODR2RMapping($this->mMappingText2, $mySource1, $myTarget1);
		$this->assertTrue($store->existsMapping($mapping));
		
		// Check that the source-target pair of the mapping of the article is stored in the DB
		$sourceTargetPairs = $store->getMappingsInArticle($articleName);
		$this->assertEquals(2, count($sourceTargetPairs), "Expected exactly two mapping.");
		$this->assertContains(array($mySource, $myTarget), $sourceTargetPairs); 
		$this->assertContains(array($mySource1, $myTarget1), $sourceTargetPairs); 
		
		// Load the mappings with the saved source and targets.
		$mappings = $store->getAllMappings($mySource, $myTarget);
		$this->assertTrue(is_array($mappings));
		$this->assertEquals(1, count($mappings));

		// Load the mappings with the saved source and targets.
		$mapping = $store->getAllMappings($mySource1, $myTarget1);
		$this->assertTrue(is_array($mappings));
		$this->assertEquals(1, count($mappings));

		
		//Save the article with a changed mapping ---
		$text3 = '\r\n <r2rMapping source="'.$mySource1.'" target="'.$myTarget1.'">'
			.$this->mMappingText3.'</r2rMapping>';
			
		$article->doEdit($text1.$text3, "");

		// Make sure the mappings exists
		$mapping = new LODR2RMapping($this->mMappingText1, $mySource, $myTarget);
		$this->assertTrue($store->existsMapping($mapping));
		$mapping = new LODR2RMapping($this->mMappingText3, $mySource1, $myTarget1);
		$this->assertTrue($store->existsMapping($mapping));
		$mapping = new LODR2RMapping($this->mMappingText2, $mySource1, $myTarget1);
		$this->assertFalse($store->existsMapping($mapping));
		
		// Check that the source-target pair of the mapping of the article is stored in the DB
		$sourceTargetPairs = $store->getMappingsInArticle($articleName);
		$this->assertEquals(2, count($sourceTargetPairs), "Expected exactly two mapping.");
		$this->assertContains(array($mySource, $myTarget), $sourceTargetPairs); 
		$this->assertContains(array($mySource1, $myTarget1), $sourceTargetPairs); 
		
		// Load the mappings with the saved source and targets.
		$mappings = $store->getAllMappings($mySource, $myTarget);
		$this->assertTrue(is_array($mappings));
		$this->assertEquals(1, count($mappings));

		// Load the mappings with the saved source and targets.
		$mappings = $store->getAllMappings($mySource1, $myTarget1);
		$this->assertTrue(is_array($mappings));
		$this->assertEquals(1, count($mappings));

		
		// Save the article with no mappings ---
		$article->doEdit('No mapping', "");

		// Make sure the mappings do no longer exist
		$mapping = new LODR2RMapping($this->mMappingText1, $mySource, $myTarget);
		$this->assertFalse($store->existsMapping($mapping));
		$mapping = new LODR2RMapping($this->mMappingText3, $mySource1, $myTarget1);
		$this->assertFalse($store->existsMapping($mapping));
		
		// Check that the source-target pair of the mapping of the article is no longer stored in the DB
		$sourceTargetPairs = $store->getMappingsInArticle($articleName);
		$this->assertEquals(0, count($sourceTargetPairs), "Expected exactly two mapping.");

		//--- Delete the article ---		
		try {
			$article->doDelete($articleName);
		} catch (MWException $e) {
			// Due to the calling environment an exception is thrown.
		}

		// Load the mapping with the source and target of the mapping that was
		// saved as an article.
		$mappings = $store->getAllMappings($this->mMappingSource, $this->mMappingTarget);
		$this->assertTrue(is_array($mappings));
		$this->assertEquals(0, count($mappings));

		// Make sure source-target-pairs for the article are removed from the DB
		$sourceTargetPairs = $store->getMappingsInArticle($articleName);
		$this->assertEquals(0, count($sourceTargetPairs));
		
		//Add SILK Mapping
		$text4 = '\r\n <silkMapping source="'.$mySource.'" target="'.$myTarget
			.'" mintNamespace="'.$this->mintNamespace.'" mintLabelPredicate="'.$this->mintLabelPredicate.'">'
			.$this->getSILKMappingText().'</silkMapping>';
		
		$article->doEdit($text4, "");

		// Make sure the mappings exists
		$mapping = new LODSILKMapping($this->getSILKMappingText(), $mySource, $myTarget,  
			'<'.$this->mintNamespace.'>', array('<'.$this->mintLabelPredicate.'>'));
		$this->assertTrue($store->existsMapping($mapping));
		
		// Check that the source-target pair of the mapping of the article is stored in the DB
		$sourceTargetPairs = $store->getMappingsInArticle($articleName);
		$this->assertEquals(1, count($sourceTargetPairs), "Expected exactly two mapping.");
		$this->assertContains(array($mySource, $myTarget), $sourceTargetPairs); 
		
		// Load the mappings with the saved source and targets.
		$mappings = $store->getAllMappings($mySource, $myTarget);
		$this->assertTrue(is_array($mappings));
		$this->assertEquals(1, count($mappings));
		
		//test adding SILK mapping with errors
		$text4 = '\r\n <silkMapping '
			.' mintNamespace="Not a URI" mintLabelPredicate="NOT_A_URI prop:ABC '.$this->mintLabelPredicate.'">'
			.$this->getSILKMappingText().'</silkMapping>';
		
		$article->doEdit($text4, "");

		// Make sure the mappings exists
		$mapping = new LODSILKMapping($this->getSILKMappingText(), ucfirst($this->mMappingSource)
			, 'wiki', '<http://halowiki/ob>', 
			array('<'.$this->mintLabelPredicate.'>', '<http://halowiki/ob/property/ABC>'));
		$this->assertTrue($store->existsMapping($mapping));
		
		
		//Delete all mappings again
		try {
			$article->doDelete($articleName);
		} catch (MWException $e) {
			// Due to the calling environment an exception is thrown.
		}
		$mappings = $store->getAllMappings($this->mMappingSource, $this->mMappingTarget);
		$this->assertTrue(is_array($mappings));
		$this->assertEquals(0, count($mappings));
	}
	
	
	private function getSILKMappingText(){
		$text = '';
		$text .= '<?xml version="1.0" encoding="utf-8" ?>';
		$text .= '<Silk>';
		$text .= 'Prefixes>';
		$text .= '<Prefix id="rdf" namespace="http://www.w3.org/1999/02/22-rdf-syntax-ns#" />';
		$text .= '<Prefix id="rdfs" namespace="http://www.w3.org/2000/01/rdf-schema#" />';
		$text .= '<Prefix id="owl" namespace="http://www.w3.org/2002/07/owl#" />';
		$text .= '<Prefix id="genes" namespace="http://wiking.vulcan.com/neurobase/kegg_genes/resource/vocab/" />';
		$text .= '<Prefix id="smwprop" namespace="http://halowiki/ob/property#" />';
		$text .= '<Prefix id="smwcat" namespace="http://halowiki/ob/category#" />';
		$text .= '<Prefix id="wiki" namespace="http://www.example.com/smw#" />';
		$text .= '</Prefixes>';
		$text .= '<Interlinks>';
		$text .= '<Interlink id="genes">';
		$text .= '<LinkType>owl:sameAs</LinkType>';
		$text .= '<SourceDataset dataSource="SOURCE" var="b">';
		$text .= '<RestrictTo>?b rdf:type smwcat:Gene</RestrictTo>';
		$text .= '</SourceDataset>';
		$text .= '<TargetDataset dataSource="TARGET" var="a">';
		$text .= '<RestrictTo>?a rdf:type smwcat:Gene</RestrictTo>';
		$text .= '</TargetDataset>';
		$text .= '<LinkCondition>';
		$text .= '<Aggregate type="max">';
		$text .= '<Compare metric="equality">';
		$text .= '<Input path="?a/smwprop:UniprotId" />';
		$text .= 'Input path="?b/smwprop:UniprotId" />';
		$text .= '</Compare>';
		$text .= '<Compare metric="equality">';
		$text .= '<Input path="?a/smwprop:EntrezGeneId" />';
		$text .= '<Input path="?b/smwprop:EntrezGeneId" />';
		$text .= '</Compare>';
		$text .= '<Compare metric="equality">';
		$text .= '<Input path="?a/smwprop:MgiMarkerAccessionId" />';
		$text .= '<Input path="?b/smwprop:MgiMarkerAccessionId" />';
		$text .= '</Compare>';
		$text .= '</Aggregate>';
		$text .= '</LinkCondition>';
		$text .= '<Filter threshold="1.0" />';
		$text .= '</Interlink>';
		$text .= '<Interlink id="diseases">';
		$text .= '<LinkType>owl:sameAs</LinkType>';
		$text .= '<SourceDataset dataSource="SOURCE" var="b">';
		$text .= '<RestrictTo>?b rdf:type smwcat:Disease</RestrictTo>';
		$text .= '</SourceDataset>';
		$text .= '<TargetDataset dataSource="TARGET" var="a">';
		$text .= '<RestrictTo>?a rdf:type smwcat:Disease</RestrictTo>';
		$text .= '</TargetDataset>';
		$text .= '<LinkCondition>';
		$text .= '<Aggregate type="max">';
		$text .= '<Compare metric="equality">';
		$text .= '<Input path="?a/smwprop:KeggDiseaseId" />';
		$text .= '<Input path="?b/smwprop:KeggDiseaseId" />';
		$text .= '</Compare>';
		$text .= '</Aggregate>';
		$text .= '</LinkCondition>';
		$text .= '<Filter threshold="1.0" />';
		$text .= '</Interlink>';
		$text .= '<Interlink id="pathways">';
		$text .= '<LinkType>owl:sameAs</LinkType>';
		$text .= '<SourceDataset dataSource="SOURCE" var="b">';
		$text .= '<RestrictTo>?b rdf:type smwcat:Pathway</RestrictTo>';
		$text .= '</SourceDataset>';
		$text .= '<TargetDataset dataSource="TARGET" var="a">';
		$text .= '<RestrictTo>?a rdf:type smwcat:Pathway</RestrictTo>';
		$text .= '</TargetDataset>';
		$text .= '<LinkCondition>';
		$text .= '<Aggregate type="max">';
		$text .= '<Compare metric="equality">';
		$text .= '<Input path="?a/smwprop:KeggPathwayId" />';
		$text .= '<Input path="?b/smwprop:KeggPathwayId" />';
		$text .= '</Compare>';
		$text .= '</Aggregate>';
		$text .= '</LinkCondition>';
		$text .= '<Filter threshold="1.0" />';
		$text .= '</Interlink>';
		$text .= '</Interlinks>';
		$text .= '</Silk>';
		return $text;
	}

}



class MockMappingStore { //implements ILODMappingStore {

	// array(string=>string)
	// Maps from a mapping ID to the mapping text
	private static $mMapping;
	
	// array(string articlename => array(string source, string target))
	private $mMappingsPerPage; 

	function __construct() {
		self::$mMapping = array();
		$this->mMappingsPerPage = array();
	}

	public function existsMapping($source, $target) {
		return array_key_exists($source.'---'.$target, self::$mMapping)
			|| array_key_exists(strtolower($source).'---'.strtolower($target), self::$mMapping);
	}

	public function addMapping(LODMapping $mapping) {
		$key = $mapping->getSource().'---'.$mapping->getTarget();
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
				$addMapping = $t == $target;
			} else if ($source !== null && $target == null) {
				$addMapping = $s == $source;
			} else {
				$source = $source;
				$target = $target;
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
				$delMapping = $t == $target;
			} else if ($source !== null && $target == null) {
				$delMapping = $s == $source;
			} else {
				$source = $source;
				$target = $target;

				$delMapping = ($key == "$source---$target");
			}
			if ($delMapping) {
				unset(self::$mMapping[$key]);
			}
		}

	}
	
	public function getMappingsInArticle($articleName) {
		return $this->mMappingsPerPage[$articleName];
	}
	
	public function removeAllMappingsFromPage($articleName) {
		$sourceTargetPairs = @$this->mMappingsPerPage[$articleName];
		if (isset($sourceTargetPairs)) {
			foreach ($sourceTargetPairs as $stp) {
				$source = $stp[0];
				$target = $stp[1];
				$this->removeAllMappings($source, $target);
			}
		}
		$this->mMappingsPerPage[$articleName] = array();
	}
	
	public function addMappingToPage($articleName, $source, $target) {
		$this->mMappingsPerPage[$articleName][] = array($source, $target);		
	}

	public function getAllSources() {
		$sources = array();
		foreach (self::$mMapping as $key => $m) {
			$s = substr($key, 0, strpos($key, "---"));
			if (!in_array($s, $sources)) {
				$sources[] = $s;
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