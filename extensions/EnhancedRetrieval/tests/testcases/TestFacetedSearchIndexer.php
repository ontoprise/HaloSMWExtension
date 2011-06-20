<?php 
/**
 * This suite tests the features of the Faceted Search Indexer.
 * 
 * @author thsc
 *
 */

require_once 'CommonClasses.php';
require_once 'TestArticles.php';

class TestFacetedSearchIndexerSuite extends PHPUnit_Framework_TestSuite
{
	private $mArticleManager;
	
	public static function suite() {
		
		$suite = new TestFacetedSearchIndexerSuite();
		$suite->addTestSuite('TestSolrIndexer');
		$suite->addTestSuite('TestSolrFullIndexContent');
		$suite->addTestSuite('TestSolrIncrementalIndex');
		return $suite;
	}
	
	protected function setUp() {
		ERCommon::createUsers(array("U1"));
        Skin::getSkinNames();
    	
        $this->mArticleManager = new ArticleManager();
//        $this->mArticleManager->createArticles(ERTestArticles::$mArticles, "U1");
        $this->mArticleManager->importArticles(__DIR__."/ERTestArticlesDump.xml");
   	}
	
	protected function tearDown() {
		// Temporarily disabled for speeding up tests
        //$this->mArticleManager->deleteArticles("U1");
        
	}
	
}

/**
 * This class tests the creation of a full index with SOLR.
 * 
 * It assumes that the SOLR server is running.
 * 
 * @author thsc
 *
 */
class TestSolrIndexer extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = FALSE;
	private static $mSolrConfig = array(
		    		'indexer' => 'SOLR',
		    		'source'  => 'SMWDB',
		    		'host'    => 'localhost',
		    		'port'    => 8983
		    	);
	
	
	public static function setUpBeforeClass() {
    	// Delete the initial index that was created while the pages were 
    	// imported.
    	$indexer = FSIndexerFactory::create(self::$mSolrConfig);
    	$result = $indexer->deleteIndex();
    	sleep(1);
    }

    public static function tearDownAfterClass() {
    	// Delete the SOLR index
    	$indexer = FSIndexerFactory::create(self::$mSolrConfig);
    	$result = $indexer->deleteIndex();
    	sleep(1);
    }
    
    
    public function providerForIndexerFactory() {
    	return array(
    		array(
    			array(
		    		'indexer' => 'SOLR',
		    		'source'  => 'SMWDB',
		    		'host'    => 'localhost',
		    		'port'    => 8983
		    	), 'FSSolrSMWDB', null
    		),
    		array(
    			array(
		    		'source'  => 'SMWDB',
		    		'host'    => 'localhost',
		    		'port'    => 8983
		    	), '', ERFSException::INCOMPLETE_CONFIG
		    ),
    		array(
    			array(
		    		'indexer' => 'SOLR',
		    		'host'    => 'localhost',
		    		'port'    => 8983
		    	), '', ERFSException::INCOMPLETE_CONFIG
		   	),
			array(
    			array(
		    		'indexer' => 'SOLR',
		    		'source'  => 'SMWDB',
		    		'port'    => 8983
		    	), '', ERFSException::INCOMPLETE_CONFIG
		   	),
			array(
    			array(
		    		'indexer' => 'SOLR',
		    		'source'  => 'SMWDB',
		    		'host'    => 'localhost'
		    	), '', ERFSException::INCOMPLETE_CONFIG
		    ),
			array(
    			array(), '', ERFSException::INCOMPLETE_CONFIG
		    ),
    		array(
    			array(
		    		'indexer' => 'Lucene',
		    		'source'  => 'SMWDB',
		    		'host'    => 'localhost',
		    		'port'    => 8983
		    	), '', ERFSException::UNSUPPORTED_VALUE
    		),
    		array(
    			array(
		    		'indexer' => 'SOLR',
		    		'source'  => 'whatever',
		    		'host'    => 'localhost',
		    		'port'    => 8983
		    	), '', ERFSException::UNSUPPORTED_VALUE
    		),
    		array(
    			array(
		    		'indexer' => 'Lucene',
		    		'source'  => 'whatever',
		    		'host'    => 'localhost',
		    		'port'    => 8983
		    	), '', ERFSException::UNSUPPORTED_VALUE
    		),
		    
    	);
    }
    
    /**
     * Checks if the indexer factory creates the expected objects or if it throws
     * the expected exceptions.
     * @param array $config
     * @param string $expClass
     * @param int $expException
     * 
     * @dataProvider providerForIndexerFactory
     */
    public function testIndexerFactory($config, $expClass, $expException) {
    	try {
	    	$indexer = FSIndexerFactory::create($config);
	    	$this->assertNotNull($indexer, 'Creation of indexer failed.');
	    	
	    	$this->assertTrue($indexer instanceof $expClass, 'Wrong class of indexer created.');
    	} catch (Exception $e) {
    		if (is_null($expException) || !($e instanceof ERFSException)) {
    			// No exception expected or no ERFSException
    			throw $e;
    		}
    		// Exception expected => check the exception code
    		$this->assertEquals($expException, $e->getCode(), "Caught wrong exception.");
    		return;
    	}
    	if (!is_null($expException)) {
    		// Expected an exception but is was not thrown
    		$this->fail("Expected exception was not thrown.");
    	}
    }

    /**
     * Checks if the SOLR server is responding.
     */
    public function testSolrPresent() {
    	$indexer = FSIndexerFactory::create(self::$mSolrConfig);
    	$result = $indexer->ping();
    	$this->assertTrue($result, "SOLR server does not answer.");
    }

    /**
     * Tests sending a raw query to the SOLR server.
     */
    public function testRawQuery() {
    	$indexer = FSIndexerFactory::create(self::$mSolrConfig);
    	$result = $indexer->sendRawQuery("q=*:*");
    	$this->assertTrue(is_string($result), "SOLR server does not answer.");
    }
    
    /**
     * Tests creating the full index for the imported articles.
     */
    public function testCreateFullIndex() {
    	$indexer = FSIndexerFactory::create(self::$mSolrConfig);
    	$result = $indexer->createFullIndex();
    	$this->assertTrue($result, "SOLR server does not answer.");
    	
    	sleep(2);
    	
    	// Send a query for all documents and asserts that all articles were added.
    	$qr = $indexer->sendRawQuery("q=*:*");
    	$expResult = 'numFound="160"';
    	$this->assertContains($expResult, $qr, "The index does not contain the expected number of documents.");
    }

	/**
     * Tests deleting the full index.
     */
    public function testDeleteIndex() {
    	$indexer = FSIndexerFactory::create(self::$mSolrConfig);
    	$result = $indexer->deleteIndex();
    	$this->assertTrue($result, "SOLR server does not answer.");
    	
    	sleep(1);
    	
    	// Send a query for all documents and asserts that all articles were added.
    	$qr = $indexer->sendRawQuery("q=*:*");
    	$expResult = 'numFound="0"';
    	$this->assertContains($expResult, $qr, "The index should contain no documents.");
    	
    }
    
}
/**
 * This class tests the creation of a full index with SOLR.
 * 
 * It assumes that the SOLR server is running.
 * 
 * @author thsc
 *
 */
class TestSolrFullIndexContent extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = FALSE;
	private static $mSolrConfig = array(
		    		'indexer' => 'SOLR',
		    		'source'  => 'SMWDB',
		    		'host'    => 'localhost',
		    		'port'    => 8983
		    	);
	

	/**
	 * Creates the full index for imported articles.
	 */
    public static function setUpBeforeClass() {
    	$indexer = FSIndexerFactory::create(self::$mSolrConfig);
    	$result = $indexer->createFullIndex();
    	sleep(2);
    }

    /**
     * Deletes the full index.
     */
    public static function tearDownAfterClass() {
    	// Delete the SOLR index
    	$indexer = FSIndexerFactory::create(self::$mSolrConfig);
    	$result = $indexer->deleteIndex();
    	sleep(2);
    }
    
    
    public function providerForIndexContent() {
    	return array(
    		array("q=*:*", array('numFound="160"')),
    		array("q=*:*&fl=smwh_title&wt=json&indent=on&start=0",
    		      array(
    		      	'"smwh_title":"Main_Page"',
    		      	'"smwh_title":"Image"',
    		      	'"smwh_title":"Located_in"',
    		      	'"smwh_title":"Located_in_state"',
    		      	'"smwh_title":"Height_stories"',
    		      	'"smwh_title":"Building_name"',
    		      	'"smwh_title":"Year_built"',
    		      	'"smwh_title":"Description"',
    		      	'"smwh_title":"1201_Third_Avenue"',
    		      	'"smwh_title":"1801_California_Street"'
    		      )
    		),
    		array("q=smwh_title_s:*Wells*&fl=smwh_title&wt=json&indent=on",
    		      array(
    		      	'"smwh_title":"Wells_Fargo_Tower"',
    		      	'"smwh_title":"Wells_Fargo_Plaza_(Houston)"',
    		      	'"smwh_title":"Wells_Fargo_Plaza"',
    		      	'"smwh_title":"Wells_Fargo_Center_(Minneapolis)"',
    		      	'"smwh_title":"Wells_Fargo_Center"',
    		        '"numFound":5'
    		      )
    		),
    		array("q=smwh_title:*wells*&fl=smwh_title&wt=json&indent=on",
    		      array(
    		      	'"smwh_title":"Wells_Fargo_Tower"',
    		      	'"smwh_title":"Wells_Fargo_Plaza_(Houston)"',
    		      	'"smwh_title":"Wells_Fargo_Plaza"',
    		      	'"smwh_title":"Wells_Fargo_Center_(Minneapolis)"',
    		      	'"smwh_title":"Wells_Fargo_Center"',
    		        '"numFound":5'
    		      )
    		),
    		array("q=*:*&fl=smwh_title&facet=true&wt=json&indent=on&facet.field=smwh_properties&facet.field=smwh_attributes&facet.field=smwh_categories",
    		      array(
    		      	'"smwh_Located_in_t",34',
    		      	'"smwh_Located_in_state_t",22',
    		        '"numFound":160',
    		      	'"smwh_Modification_date_xsdvalue_dt",160',
    		      	'"smwh_Building_name_xsdvalue_t",34',
    		      	'"smwh_Image_xsdvalue_t",34',
    		      	'"smwh_Height_stories_xsdvalue_d",34',
    		      	'"smwh_Year_built_xsdvalue_dt",8',
    		        '"smwh_Description_xsdvalue_t",152',
    		      	'"Building",34',
    		      	'"Bank_of_America_buildings",10',
    		      )
    		),
    		array("q=smwh_title_s:*Wells*&fl=smwh_title&facet=true&wt=json&indent=on&facet.field=smwh_properties&facet.field=smwh_attributes&facet.field=smwh_categories",
    		      array(
    		      	'"smwh_title":"Wells_Fargo_Tower"',
    		      	'"smwh_title":"Wells_Fargo_Plaza_(Houston)"',
    		      	'"smwh_title":"Wells_Fargo_Plaza"',
    		      	'"smwh_title":"Wells_Fargo_Center_(Minneapolis)"',
    		      	'"smwh_title":"Wells_Fargo_Center"',
    		      	'"smwh_Located_in_t",2',
    		      	'"smwh_Located_in_state_t",0',
    		        '"numFound":5',
    		      	'"smwh_Modification_date_xsdvalue_dt",5',
    		      	'"smwh_Building_name_xsdvalue_t",2',
    		      	'"smwh_Image_xsdvalue_t",2',
    		      	'"smwh_Height_stories_xsdvalue_d",2',
    		      	'"smwh_Year_built_xsdvalue_dt",1',
    		        '"smwh_Description_xsdvalue_t",5',
    		      	'"Building",2',
    		      	'"Bank_of_America_buildings",0',
    		      )
    		),
    	);	
    }
    
    /**
     * Tests the content of the index by asking several queries
     * @dataProvider providerForIndexContent
     */
    public function testIndexContent($query, $expResults) {
    	$indexer = FSIndexerFactory::create(self::$mSolrConfig);
    	$qr = $indexer->sendRawQuery($query);
    	foreach ($expResults as $er) {
    		$this->assertContains($er, $qr, "Could not find expected string <$er> in result:\n$qr");
    	}
    }
    
    
    
}


/**
 * This class tests the maintenance of an incremental index with SOLR. Each time
 * a document is changed, deleted or moved, the index has to be updated.
 * 
 * It assumes that the SOLR server is running.
 * 
 * @author thsc
 *
 */
class TestSolrIncrementalIndex extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = FALSE;
	private static $mSolrConfig = array(
		    		'indexer' => 'SOLR',
		    		'source'  => 'SMWDB',
		    		'host'    => 'localhost',
		    		'port'    => 8983
		    	);
		    	
	private static $mArticleManager;
	
	
	/**
	 * Creates the full index for imported articles.
	 */
    public static function setUpBeforeClass() {
    	global $fsgFacetedSearchConfig;
    	$fsgFacetedSearchConfig = self::$mSolrConfig;
    	$indexer = FSIndexerFactory::create();
    	$result = $indexer->createFullIndex();
    	sleep(2);
    	self::$mArticleManager = new ArticleManager();
    }
    
    /**
     * Delete all articles that were created in a test.
     */
    public function tearDown() {
    	self::$mArticleManager->deleteArticles("U1");
    }

    /**
     * Deletes the full index.
     */
    public static function tearDownAfterClass() {
    	// Delete the SOLR index
    	$indexer = FSIndexerFactory::create(self::$mSolrConfig);
    	$result = $indexer->deleteIndex();
    	sleep(2);
    }
    
    
    public function providerForChangePage() {
    	return array(
    		// $actionsAndArticles:array($action => $articles), $query, $expResults, $unexpectedResults
    		#0
    		array(
    			array("edit" =>
    			      array("Article without relations" => "No annotations here.")),
    			"q=smwh_title_s:*Wells*&fl=smwh_title&wt=json&indent=on",
    		    array(
    		      	'"smwh_title":"Wells_Fargo_Tower"',
    		      	'"smwh_title":"Wells_Fargo_Plaza_(Houston)"',
    		      	'"smwh_title":"Wells_Fargo_Plaza"',
    		      	'"smwh_title":"Wells_Fargo_Center_(Minneapolis)"',
    		      	'"smwh_title":"Wells_Fargo_Center"',
    		        '"numFound":5'
    		    )
    		),
    		#1
    		array(
    			array("edit" =>
    				  array("Orson Wells" => "A famous author. [[Category:Author]][[Description::Orson Wells was a famous author]]")),
    			"q=smwh_title_s:*Wells*&fl=smwh_title&wt=json&indent=on",
    		    array(
    		      	'"smwh_title":"Wells_Fargo_Tower"',
    		      	'"smwh_title":"Wells_Fargo_Plaza_(Houston)"',
    		      	'"smwh_title":"Wells_Fargo_Plaza"',
    		      	'"smwh_title":"Wells_Fargo_Center_(Minneapolis)"',
    		      	'"smwh_title":"Wells_Fargo_Center"',
    		      	'"smwh_title":"Orson_Wells"',
    		    	'"numFound":6'
    		    )
    		),
    		#2
    		array(
    			array("edit" =>
    				  array("Orson Wells" => "A famous author. [[Category:Author]][[Description::Orson Wells was a famous author]]"),
    				  "delete" =>
    				  array("Orson Wells")
    				  ),
    			"q=smwh_title_s:*Wells*&fl=smwh_title&wt=json&indent=on",
    		    array(
    		      	'"smwh_title":"Wells_Fargo_Tower"',
    		      	'"smwh_title":"Wells_Fargo_Plaza_(Houston)"',
    		      	'"smwh_title":"Wells_Fargo_Plaza"',
    		      	'"smwh_title":"Wells_Fargo_Center_(Minneapolis)"',
    		      	'"smwh_title":"Wells_Fargo_Center"',
    		    	'"numFound":5'
    		    )
    		),
    		#3
    		array(
    			array("edit" =>
    				  array("Orson Wells" => "A famous author. [[Category:Author]][[Description::Orson Wells was a famous author]]"),
    				),
    			"q=smwh_title_s:*Or*Wells*&wt=json",
    		    array(
    				'"smwh_namespace_id":0',
    				'"smwh_title":"Orson_Wells"',
    				'"smwh_categories":["Author"]',
    				'"smwh_Description_xsdvalue_t":["Orson Wells was a famous author"]',
    				'"smwh_Modification_date_xsdvalue_dt"',
    				'"smwh_attributes":["smwh_Modification_date_xsdvalue_dt"',
    				'"smwh_Description_xsdvalue_t"]',
    				'"smwh_Description_xsdvalue_s":["Orson Wells was a famous author"]'
    		    )
    		),
    		#4
    		array(
    			array("edit" =>
				      array("Wells Fargo NewYork" => "[[Category:Building]][[Height stories::42]][[Located in::New York]][[Located in::Manhattan]][[Located in state::New York]][[Description::This building does not exist.]]")),
    			"q=smwh_title_s:*Wells*&fl=smwh_title&wt=json&indent=on",
    		    array(
    		      	'"smwh_title":"Wells_Fargo_Tower"',
    		      	'"smwh_title":"Wells_Fargo_Plaza_(Houston)"',
    		      	'"smwh_title":"Wells_Fargo_Plaza"',
    		      	'"smwh_title":"Wells_Fargo_Center_(Minneapolis)"',
    		      	'"smwh_title":"Wells_Fargo_Center"',
    		      	'"smwh_title":"Wells_Fargo_NewYork"',
    		    	'"numFound":6'
    		    )
    		),
    		#5
    		array(
    			array("edit" =>
				      array("Wells Fargo NewYork" => "[[Category:Building]][[Height stories::42]][[Located in::New York]][[Located in::Manhattan]][[Located in state::New York]][[Description::This building does not exist.]]")),
    			"q=smwh_title_s:*Wells_Fargo_NewYork*&wt=json",
    		    array(
    				'"smwh_namespace_id":0',
    				'"smwh_title":"Wells_Fargo_NewYork"',
					'"smwh_Located_in_s":["New_York","Manhattan"]',
					'"smwh_Located_in_t":["New_York","Manhattan"]',
					'"smwh_categories":["Building"]',
					'"smwh_Description_xsdvalue_t":["This building does not exist."]',
					'"smwh_Height_stories_numvalue_d":[42.0]',
					'"smwh_Height_stories_xsdvalue_d":[42.0]',
					'"smwh_attributes":["smwh_Height_stories_xsdvalue_d","smwh_Modification_date_xsdvalue_dt","smwh_Description_xsdvalue_t"]',
					'"smwh_properties":["smwh_Located_in_t","smwh_Located_in_state_t"]',
					'"smwh_Located_in_state_s":["New_York"]',
					'"smwh_Description_xsdvalue_s":["This building does not exist."]',
					'"smwh_Located_in_state_t":["New_York"]'
    		    )
    		),
    		#6
    		array(
    			array("edit1" =>
				      array("Wells Fargo NewYork" => "[[Category:Building]][[Category:Tower]][[Height stories::42]][[Located in::New York]][[Located in::Manhattan]][[Located in state::New York]][[Description::This building does not exist.]]"),
				      "edit2" =>
				      array("Wells Fargo NewYork" => "Removed some annotations: [[Category:Building]][[Located in::New York]][[Description::This building does really not exist.]]")
				      ),
    			"q=smwh_title_s:*Wells_Fargo_NewYork*&wt=json",
    		    array(
    				'"smwh_namespace_id":0',
    				'"smwh_title":"Wells_Fargo_NewYork"',
					'"smwh_Located_in_s":["New_York"]',
					'"smwh_Located_in_t":["New_York"]',
					'"smwh_categories":["Building"]',
					'"smwh_attributes":["smwh_Modification_date_xsdvalue_dt","smwh_Description_xsdvalue_t"]',
					'"smwh_properties":["smwh_Located_in_t"]',
					'"smwh_Description_xsdvalue_s":["This building does really not exist."]'
    		    )
    		),
    		#7
    		array(
    			array("edit" =>
    				  array("Orson Wells" => "A famous author. [[Category:Author]][[Description::Orson Wells was a famous author]]"),
    				  "move" =>
				      array("Orson_Wells" => "O_Wells")),
    			"q=smwh_title_s:*Or*Wells*&wt=json",
    		    array(
    				'"numFound":0'
    		    )
    		),
    		#8
    		array(
    			array("edit" =>
    				  array("Orson Wells" => "A famous author. [[Category:Author]][[Description::Orson Wells was a famous author]]"),
    				  "move" =>
				      array("Orson_Wells" => "O_Wells")),
    		    "q=smwh_title_s:*O_Wells*&wt=json",
    		    array(
    				'"smwh_namespace_id":0',
    				'"smwh_title":"O_Wells"',
    				'"smwh_categories":["Author"]',
    				'"smwh_Description_xsdvalue_t":["Orson Wells was a famous author"]',
    				'"smwh_Modification_date_xsdvalue_dt"',
    				'"smwh_attributes":["smwh_Modification_date_xsdvalue_dt"',
    				'"smwh_Description_xsdvalue_t"]',
    				'"smwh_Description_xsdvalue_s":["Orson Wells was a famous author"]'
    		    )
    		),
    	);
    }
    
    /**
     * Tests changes of a page with semantic data and verifies that the index is
     * updated immediately.
     * 
     * @dataProvider providerForChangePage
     */
    public function testChangePage($actionsAndArticles, $query, $expResults,
                                   $unexpectedResults = null) {
    	foreach ($actionsAndArticles as $action => $articles) {
	    	if (strpos($action, 'edit') === 0) {
		    	// Create the articles
		    	self::$mArticleManager->createArticles($articles, "U1");
	    	} else if (strpos($action, 'move') === 0) {
		    	// move the articles
		    	self::$mArticleManager->moveArticles($articles);
	    	} else if (strpos($action, 'delete') === 0) {
		    	// delete the articles
		    	self::$mArticleManager->deleteArticles("U1", $articles);
	    	}
    	}
    	
    	// Check content of index
    	$indexer = FSIndexerFactory::create(self::$mSolrConfig);
    	$qr = $indexer->sendRawQuery($query);
    	foreach ($expResults as $er) {
    		$this->assertContains($er, $qr, "Could not find expected string <$er> in result:\n$qr");
    	}
    	
    	if (!is_null($unexpectedResults)) {
	    	foreach ($unexpectedResults as $uer) {
	    		$this->assertNotContains($uer, $qr, "Found unexpected string <$uer> in result:\n$qr");
	    	}
    	}
    	
    }
		    	
}