<?php 
/**
 * This suite tests the features of the Facetted Search Indexer.
 * 
 * @author thsc
 *
 */

require_once 'CommonClasses.php';
require_once 'TestArticles.php';

class TestFacettedSearchIndexerSuite extends PHPUnit_Framework_TestSuite
{
	private $mArticleManager;
	
	public static function suite() {
		
		$suite = new TestFacettedSearchIndexerSuite();
		$suite->addTestSuite('TestSolrFullIndex');
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
//        $this->mArticleManager->deleteArticles("U1");
        
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
class TestSolrFullIndex extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = FALSE;
	
	
    function setUp() {
    }

    function tearDown() {
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

    public function testSolrPresent() {
http://localhost:8983/solr/admin/ping    	
//    	$url = "http://localhost:8983/solr/dataimport?command=full-import&clean=true";
//    	$result = $this->fetchURLviaCURL($url, $resultCode);
//    	
//    	$url = "http://localhost:8983/solr/select/?q=*:*&facet=true&wt=json&indent=on&facet.field=smwh_title&facet.field=smwh_properties&facet.field=smwh_categories&fq=smwh_properties:*Rational*";
//    	$result = $this->fetchURLviaCURL($url, $resultCode);
    }
    
	function fetchURLviaCURL( $url, &$resultCode ) {
		$fetch = curl_init( $url );
		if( defined( 'ERDEBUG' ) ) {
			curl_setopt( $fetch, CURLOPT_VERBOSE, 1 );
		}
		
		ob_start();
		$ok = curl_exec( $fetch );
		$result = ob_get_contents();
		ob_end_clean();
		
		$info = curl_getinfo( $fetch );
		if( !$ok ) {
			echo "Something went awry...\n";
			var_dump( $info );
			die();
		}
		curl_close( $fetch );
		
		$resultCode = $info['http_code']; # ????
		return $result;
	}
    
}