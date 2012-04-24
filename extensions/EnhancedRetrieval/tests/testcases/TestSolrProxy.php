<?php 
/*
 * Copyright (C) Vulcan Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program.If not, see <http://www.gnu.org/licenses/>.
 *
 */

if ( isset( $_SERVER ) && array_key_exists( 'REQUEST_METHOD', $_SERVER ) ) {
	die( "This script must be run from the command line\n" );
}

/**
 * This suite tests the features of the Solrproxy. This includes the access control
 * features of HaloACL
 * 
 * @author thsc
 *
 */

require_once 'CommonClasses.php';

// Used to control a valid entry point for some classes that are only used by the
// solrproxy.
define('SOLRPROXY', true);

class TestSolrProxySuite extends PHPUnit_Framework_TestSuite
{
	public static $mSolrConfig = array(
	    'indexer' => 'SOLR',
	    'source'  => 'SMWDB',
	    'proxyHost'    => 'http://localhost',
		'proxyServlet' => "/mediawiki/extensions/EnhancedRetrieval/includes/FacetedSearch/solrproxy.php",
		'indexerHost' => 'localhost',
		'indexerPort' => 8983
	);
	
	private $mArticleManager;
	
	public static function suite() {
		
		$suite = new TestSolrProxySuite();
		$suite->addTestSuite('TestSolrProxyQueries');
 		$suite->addTestSuite('TestResultFilterClasses');
 		$suite->addTestSuite('TestSolrProxyAccessControl');
		return $suite;
	}
	
	protected function setUp() {
		global $fsgIP;
		require_once $fsgIP.'/includes/FacetedSearch/Solrproxy/FS_ResultParser.php';
		require_once $fsgIP.'/includes/FacetedSearch/Solrproxy/FS_ResultFilter.php';
		require_once $fsgIP.'/includes/FacetedSearch/Solrproxy/FS_HaloACLMemcache.php';
		require_once $fsgIP.'/includes/FacetedSearch/Solrproxy/FS_MWAccessControl.php';
		require_once $fsgIP.'/includes/FacetedSearch/Solrproxy/FS_QueryParser.php';
		
		global $spgHaloACLConfig;
		$spgHaloACLConfig = array(
			'wikiDBprefix' => '',
			'wikiDBname'   => 'testdb',
			'memcacheconfig' => array(
				'servers' => array('localhost:11211'),
				'debug'   => false,
				'compress_threshold' => 10240,
				'persistant' => true),
			'mediawikiIndex' => 'http://localhost/mediawiki/index.php',
			'categoryNS'	=> 14,
			'propertyNS'	=> 102,
			'contentlanguage' => 'en'
		);
		
		ERCommon::createUsers(array("U1"));
        Skin::getSkinNames();
        
        // Initialize an internal array for properties. Otherwise the import
        // will fail.
        SMWDIProperty::newFromUserLabel('foo');
    	
        $this->mArticleManager = new ArticleManager();
		$this->mArticleManager->importArticles(__DIR__."/ERTestArticlesDump.xml");
   	}
   	
   	/**
   	 * Sends the $query to the SOLR proxy and returns its result.
   	 * @param String $query
   	 * 		The SOLR query
   	 * @result String
   	 * 		The result as a string
   	 */
   	public static function getResultFromProxy($query) {
    	$host = self::$mSolrConfig['proxyHost'];
    	$servlet = self::$mSolrConfig['proxyServlet'];
    	$url = $host.$servlet.'?'.$query;
    	$fetch = curl_init( $url );
    	ob_start();
    	$ok = curl_exec( $fetch );
    	$qr = ob_get_contents();
    	ob_end_clean();
    		
    	$info = curl_getinfo( $fetch );
    	curl_close( $fetch );
    	
    	$resultCode = $info['http_code']; # ????
		return $qr;
    }
   	
	
	protected function tearDown() {
		// Temporarily disabled for speeding up tests
		$this->mArticleManager->deleteArticles("U1");
	}
	
}

/**
* This class checks if certain classes for filtering SOLR query results are present.
*
* It assumes that the SOLR server is running.
*
* @author thsc
*
*/
class TestResultFilterClasses extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = FALSE;

	/**
	* Creates the full index for imported articles.
	*/
	public static function setUpBeforeClass() {
		$indexer = FSIndexerFactory::create(TestSolrProxySuite::$mSolrConfig);
		$result = $indexer->createFullIndex();
		sleep(2);
	}
	
	/**
	 * Deletes the full index.
	 */
	public static function tearDownAfterClass() {
		// Delete the SOLR index
		$indexer = FSIndexerFactory::create(TestSolrProxySuite::$mSolrConfig);
		$result = $indexer->deleteIndex();
		sleep(2);
	}
	
	/**
	 * Checks if the classes FSResultParser and FSResultFilter are present.
	 */
	public function testClassesExist() {
		$this->assertTrue(class_exists('FSResultParser'), "Expected that class 'FSResultParser' exists.");
		$this->assertTrue(class_exists('FSResultFilter'), "Expected that class 'FSResultFilter' exists.");
		$this->assertTrue(class_exists('FSHaloACLMemcache'), "Expected that class 'FSHaloACLMemcache' exists.");
		$this->assertTrue(class_exists('FSMWAccessControl'), "Expected that class 'FSMWAccessControl' exists.");
		$this->assertTrue(class_exists('FSQueryParser'), "Expected that class 'FSQueryParser' exists.");
		
		$rf = FSResultFilter::getInstance();
		$this->assertTrue($rf instanceof FSResultFilter, "Expected to get an instance of FSResultFilter.");
		$hmc = FSHaloACLMemcache::getInstance();
		$this->assertTrue($hmc instanceof FSHaloACLMemcache, "Expected to get an instance of FSHaloACLMemcache.");
		$hmc = FSMWAccessControl::getInstance();
		$this->assertTrue($hmc instanceof FSMWAccessControl, "Expected to get an instance of FSMWAccessControl.");
	}
	
	/**
	 * Data provider for testResultParser
	 */
	public function providerForResultParser() {
		return array(
			#0
			array("q=*:*&wt=json", 
				array('numFound',169)
			),
			#1
			array("q=*:*&fl=smwh_title&wt=json&indent=on&start=0&sort=smwh_title_s%20asc",
				array(
					'smwh_title','1201_Third_Avenue',
    		      	'smwh_title','1801_California_Street',
    		      	'smwh_title','191_Peachtree_Tower',
    		      	'smwh_title','20_Exchange_Place',
    		      	'smwh_title','300_North_LaSalle',
    		      	'smwh_title','311_South_Wacker_Drive',
    		      	'smwh_title','383_Madison_Avenue',
    		      	'smwh_title','40_Wall_Street',
    		      	'smwh_title','500_Fifth_Avenue',
    		      	'smwh_title','555_California_Street'
				)
			)
		);
		
	}
	
	/**
	 * 
	 * Checks if the result parser is correctly converted to an object
	 * @param string $query
	 * @param array $expected
	 * 		Pairs of keys and values
	 * 
	 * @dataProvider providerForResultParser
	 */
	public function testResultParser($query, $expected) {
    	$qr = TestSolrProxySuite::getResultFromProxy($query);
    	$json = FSResultParser::parseResult($qr);
    	
    	for ($i = 0; $i < count($expected); $i += 2) {
    		$key = $expected[$i];
    		$expValue = $expected[$i+1];
    		
    		switch ($key) {
    			case 'numFound':
    				$val = @$json->response->numFound;
    				$this->assertEquals($expValue, $val, "Expected to find value '$expVal' for field '$key'");
    				break;
    			default:
    				// Any other key is expexted to be found in the 'docs' field.
    				$docs = @$json->response->docs;
    				$this->assertTrue(isset($docs), "Expected field 'docs' in the response.");
    				
    				// Check if we can find the expected value in one of the documents
    				$found = false;
    				foreach ($docs as $doc) {
    					$val = @$doc->$key;
    					if ($val === $expValue) {
    						$found = true;
    						break;
    					}
    				}
    				
    				$this->assertTrue($found, "Expected to find value '$expValue' for field '$key' in at least one document.");
    				break;
    		}
    	}
	}
	
	/**
	 * Data provider for testQueryParser
	 */
	public function providerForQueryParser() {
		return array(
			// $query, $expectedParameters
			#0
			array("q=smwh_search_field%3A(%2Btow*%20)&facet=true&facet.field=smwh_categories&facet.field=smwh_attributes&facet.field=smwh_properties&facet.field=smwh_namespace_id&start=10&rows=20&facet.mincount=1&json.nl=map&fl=smwh_Modification_date_xsdvalue_dt%2Csmwh_categories%2Csmwh_attributes%2Csmwh_properties%2Cid%2Csmwh_title%2Csmwh_namespace_id&hl=true&hl.fl=smwh_search_field&hl.simple.pre=%3Cb%3E&hl.simple.post=%3C%2Fb%3E&hl.fragsize=250&sort=smwh_Modification_date_xsdvalue_dt%20desc&wt=json&json.wrf=_jqjsp&_1332827921888=", 
				array(
					'start' => 10,
					'rows'	=> 20
				)
			),
		);
		
	}
	
	/**
	 * 
	 * Checks if the query parser retrieves the parameters of a query correctly
	 * @param string $query
	 * @param array $expected
	 * 		Pairs of keys and values
	 * 
	 * @dataProvider providerForQueryParser
	 */
	public function testQueryParser($query, $expected) {
		$queryParser = new FSQueryParser($query);

		// Check for expected values
		foreach ($expected as $parameter => $expValue) {
			$value = $queryParser->get($parameter);
			$this->assertEquals($expValue, $value, "Expected the query parameter '$parameter' to have the value '$expValue'.");
    	}
    	
    	// Serialize the query
    	$serialized = $queryParser->serialize();
    	$this->assertEquals($query, $serialized, "Serializing the query failed.");
	}
	
}



/**
 * This class tests the results received from the solr proxy.
 * 
 * It assumes that the SOLR server is running.
 * 
 * @author thsc
 *
 */
class TestSolrProxyQueries extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = FALSE;

	/**
	 * Creates the full index for imported articles.
	 */
    public static function setUpBeforeClass() {
    	$indexer = FSIndexerFactory::create(TestSolrProxySuite::$mSolrConfig);
		$result = $indexer->createFullIndex();
    	sleep(2);
    }

    /**
     * Deletes the full index.
     */
    public static function tearDownAfterClass() {
    	// Delete the SOLR index
    	$indexer = FSIndexerFactory::create(TestSolrProxySuite::$mSolrConfig);
		$result = $indexer->deleteIndex();
    	sleep(2);
    }
    
    
    public function providerForIndexContent() {
    	return array(
    		#0
    		array("q=*:*", array('numFound="169"')),
    		#1
    		array("q=*:*&fl=smwh_title&wt=json&indent=on&start=0&sort=smwh_title_s%20asc",
    		      array(
    		      	'"smwh_title":"1201_Third_Avenue"',
    		      	'"smwh_title":"1801_California_Street"',
    		      	'"smwh_title":"191_Peachtree_Tower"',
    		      	'"smwh_title":"20_Exchange_Place"',
    		      	'"smwh_title":"300_North_LaSalle"',
    		      	'"smwh_title":"311_South_Wacker_Drive"',
    		      	'"smwh_title":"383_Madison_Avenue"',
    		      	'"smwh_title":"40_Wall_Street"',
    		      	'"smwh_title":"500_Fifth_Avenue"',
    		      	'"smwh_title":"555_California_Street"'
    		      )
    		),
    		#2
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
    		#3
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
    		#4
    		array("q=*:*&fl=smwh_title&facet=true&json.nl=map&wt=json&indent=on&facet.field=smwh_properties&facet.field=smwh_attributes&facet.field=smwh_categories",
    		      array(
    		      	'"smwh_Located_in_t":34',
    		      	'"smwh_Located_in_state_t":23',
    		        '"numFound":169',
    		      	'"smwh_Modification_date_xsdvalue_dt":169',
    		      	'"smwh_Building_name_xsdvalue_t":34',
    		      	'"smwh_Image_xsdvalue_t":34',
    		      	'"smwh_Height_stories_xsdvalue_d":34',
    		      	'"smwh_Year_built_xsdvalue_dt":8',
    		        '"smwh_Description_xsdvalue_t";152',
    		      	'"Building":34',
    		      	'"Bank_of_America_buildings":10',
    		      )
    		),
    		#5
    		array("q=smwh_title_s:*Wells*&fl=smwh_title&facet=true&json.nl=map&wt=json&indent=on&facet.field=smwh_properties&facet.field=smwh_attributes&facet.field=smwh_categories",
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
    		#6 - Do a search in full text
    		array("q=smwh_search_field:seattle*&json.nl=map&wt=json&indent=on&hl.fl=smwh_search_field&hl.simple.pre=<b>&hl.simple.post=<%2Fb>&hl.fragsize=250&fl=smwh_full_text",
    		      array(
    		      	"Category:Office_buildings_in_Seattle,_Washington",
    		      	"Category:Skyscrapers_in_Seattle,_Washington",
    		      	"Located_in::Seattle",
    		      	"Image::Seattle_Washington_Mutual_Tower_2004-08-30.jpg",
    		      	"Description::This is the description of Seattle.",
    		      	"Category:Cities_in_the_Seattle_metropolitan_area",
    		      	"Category:Geography_of_Seattle,_Washington",
    		      	"Category:Neighborhoods_in_Seattle,_Washington",
    		      	"Category:Seattle,_Washington",
    		      	"Description::This is the description of Seattle_Municipal_Tower.",
    		      	"Building_name::Seattle Municipal Tower",
    		      	"Description::This is the description of Union_Square_(Seattle).",
    		      	'"numFound":5'
    		      )
    		),
    		#7 - Search for a category with special characters
    		array("q=*:*&facet=true&fl=smwh_title&fq=smwh_categories%3A%C3%9Cbung&json.nl=map&wt=json&indent=on",
    		      array(
    		      	'"smwh_title":"Zweite_Übung"',
    		      	'"smwh_title":"Übung_1"',
    		      	'"numFound":2'
    		      )
    		),
   			#8 - Search for a attribute with special characters
    		array("q=*:*&facet=true&fl=smwh_title,smwh_attributes,smwh_properties,smwh_categories&fq=smwh_attributes%3Asmwh_Hat_%C3%9Cberschrift_xsdvalue_t&json.nl=map&wt=json",
    		      array(
					'"smwh_title":"Zweite_Übung"',
					'"smwh_categories":["Übung"]',
					'smwh_Hat_Überschrift_xsdvalue_t',
					'"smwh_title":"Übung_1"',
					'"smwh_categories":["Übung"]',
					'smwh_Hat_Überschrift_xsdvalue_t',
					'smwh_Nächste_Übung_t',
					'numFound":2'
    		      )
    		),
   			#9 - Search for a relation with special characters
    		array("q=*:*&facet=true&fl=smwh_title,smwh_attributes,smwh_properties,smwh_categories&fq=smwh_properties:smwh_Nächste_Übung_t&json.nl=map&wt=json",
    		      array(
					'"smwh_title":"Übung_1"',
					'"smwh_categories":["Übung"]',
					'smwh_Hat_Überschrift_xsdvalue_t',
					'smwh_Nächste_Übung_t',
					'numFound":1'
    		      )
    		),
    		#10 - Search for full text with special characters
    		array("q=smwh_search_field:übung*&json.nl=map&wt=json&indent=on&hl.fl=smwh_search_field&hl.simple.pre=<b>&hl.simple.post=<%2Fb>&hl.fragsize=250&fl=smwh_full_text",
    		      array(
					'"smwh_full_text":"Dies ist die zweite Übung.\n[[Hat Überschrift::Übung 2]]\n[[Category:Übung]]"',
					'"smwh_full_text":"Dies ist Übung 1.\n[[Nächste Übung::Zweite Übung]]\n[[Hat Überschrift::Übung 1]]\n[[Category:Übung]]"',
					'"smwh_full_text":"Dies ist die Kategorie Übung."',
					'"smwh_full_text":"Dieses Property verweist auf die nächste Übung.\n\n[[has type::Page]]"',
					'"numFound":4'
    		      )
    		),
    		#11 - Search for a property that links to a page in the user namespace
    		array("q=smwh_title_s:ThomasPage&json.nl=map&fl=smwh_Created_By_t%2Csmwh_Modification_date_xsdvalue_dt&wt=json",
    		      array(
					'"smwh_Created_By_t":["User:Thomas","User_talk:Thomas","Thomas"]',
					'"numFound":1'
    		      )
    		),
    		
    	);	
    }
        
    /**
     * Tests the content of the index by asking several queries via the solr 
     * proxy.
     * @dataProvider providerForIndexContent
     */
    public function testIndexProxy($query, $expResults) {
    	$qr = TestSolrProxySuite::getResultFromProxy($query);
    	$qr = str_replace(array('\u00dc', '\u00e4'), 
    					  array('Ü', 'ä'), 
    					  $qr);
    	foreach ($expResults as $er) {
    		$this->assertContains($er, $qr, "Could not find expected string <$er> in result:\n$qr");
    	}
    }
    
}


/**
 * This class tests the access control functionality in the class FSResultFilter.
 * 
 * It assumes that the SOLR server is running and HaloACL is enabled.
 * 
 * @author thsc
 *
 */
class TestSolrProxyAccessControl extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = FALSE;
	
	private static $mArticles;
	private static $mArticleManager;
	

	/**
	 * Creates the full index for articles in the wiki and creates some access
	 * rights.
	 */
    public static function setUpBeforeClass() {
    	$indexer = FSIndexerFactory::create(TestSolrProxySuite::$mSolrConfig);
		$result = $indexer->createFullIndex();
    	sleep(2);
    	
    	ERCommon::createUsers(array("U1", "U2"));
    	
    	self::initArticleContent();
    	self::$mArticleManager = new ArticleManager();
    	self::$mArticleManager->createArticles(self::$mArticles, "U1");
    	 
    }

    /**
     * Deletes the full index and removes the access rights.
     */
    public static function tearDownAfterClass() {
    	// Delete the SOLR index
    	$indexer = FSIndexerFactory::create(TestSolrProxySuite::$mSolrConfig);
		$result = $indexer->deleteIndex();
    	sleep(2);
    	
    	self::$mArticleManager->deleteArticles("U1");
    }
    
    /**
     * This function is called before each test.
     */
    public function setUp() {
    	HACLMemcache::getInstance()->purgeCache();
    }
    
    /**
     * This function is called after each test.
     */
    public function tearDown() {
    	HACLMemcache::getInstance()->purgeCache();
    }
    
    private static function initArticleContent() {
    	self::$mArticles = array(
//------------------------------------------------------------------------------		
			'Category:ACL/Group' =>
<<<ACL
This is the category for groups.
ACL
,
//------------------------------------------------------------------------------		
			'Category:ACL/Right' =>
<<<ACL
This is the category for rights.
ACL
,
//------------------------------------------------------------------------------		
			'Category:ACL/ACL' =>
<<<ACL
This is the category for security descriptors.
ACL
,
//------------------------------------------------------------------------------
    		'MyPage' =>
<<<ACL
    This is my page.
ACL
,
//------------------------------------------------------------------------------
    		'ACL:Page/MyPage' =>
<<<ACL
    {{#manage rights: assigned to=User:U1}}
    
    {{#access:
     assigned to=User:U1
    |actions=read
    |description= Allow read access U1
    }}
    
    [[Category:ACL/ACL]]
    		
ACL
,
//------------------------------------------------------------------------------
    		'MyOtherPage' =>
<<<ACL
    This is my other page.
ACL
,
//------------------------------------------------------------------------------
    		'ACL:Page/MyOtherPage' =>
<<<ACL
    {{#manage rights: assigned to=User:U1}}
    
    {{#access:
     assigned to=User:U2
    |actions=read
    |description= Allow read access U2
    }}
    
    [[Category:ACL/ACL]]
    		
ACL
,
//------------------------------------------------------------------------------
    		'Category:MyCategory' =>
<<<ACL
This is my category.
ACL
,
//------------------------------------------------------------------------------
    		'Category:MySubCategory' =>
<<<ACL
This is my category.
[[Category:MyCategory]]
ACL
,
//------------------------------------------------------------------------------
    		'ACL:Category/MyCategory' =>
<<<ACL
    {{#manage rights: assigned to=User:U1}}
    
    {{#access:
     assigned to=User:U1
    |actions=read
    |description= Allow read access U1
    }}
    
    [[Category:ACL/ACL]]
    		
ACL
,
//------------------------------------------------------------------------------
    		'Property:MyProperty' =>
<<<ACL
This is my property.
[[has type::Page]]
ACL
,
//------------------------------------------------------------------------------
    		'ACL:Property/MyProperty' =>
<<<ACL
{{#property access: assigned to=User:U1
 |actions=read
 |description=read for U:U1
 |name=Right}}

{{#manage rights: assigned to=User:U1}}
[[Category:ACL/ACL]]
ACL
,
//------------------------------------------------------------------------------
    	    'User_talk:MyTalk' =>
<<<ACL
This is my a user talk article which contains a tower.
ACL
,
//------------------------------------------------------------------------------
    		'ACL:Namespace/User_talk' =>
<<<ACL
{{#access: assigned to=User:U1
 |actions=read
 |description=read for U1
 |name=Right}}

{{#manage rights: assigned to=User:U1}}
[[Category:ACL/ACL]]
ACL
,
//------------------------------------------------------------------------------
    		'ACL:Page/Texas' =>
<<<ACL
{{#manage rights: assigned to=User:U1}}
    
{{#access:
     assigned to=User:U1
    |actions=read,edit
    |description= Allow read,edit access U1
}}
    
[[Category:ACL/ACL]]
   		
ACL
,
//------------------------------------------------------------------------------
			'Category:Skyscrapers_between_200_and_249_meters' =>
<<<ACL
This is the category Skyscrapers_between_200_and_249_meters.
ACL
,
//------------------------------------------------------------------------------
			'Category:Skyscrapers_between_250_and_299_meters' =>
<<<ACL
This is the category Skyscrapers_between_250_and_299_meters.
ACL
,
//------------------------------------------------------------------------------
			'ACL:Category/Skyscrapers_between_200_and_249_meters' =>
<<<ACL
{{#manage rights: assigned to=User:U1}}
{{#access:
	assigned to=User:U1
	|actions=read
	|description= Allow read access U1
}}
[[Category:ACL/ACL]]
ACL
,
//------------------------------------------------------------------------------
			'ACL:Category/Skyscrapers_between_250_and_299_meters' =>
<<<ACL
{{#manage rights: assigned to=User:U1}}
{{#access:
	assigned to=User:U1
	|actions=read
	|description= Allow read access U1
}}
[[Category:ACL/ACL]]
ACL
,
//------------------------------------------------------------------------------
    	    'ACL:Property/Height_stories' =>
<<<ACL
{{#property access: assigned to=User:U1
    	 |actions=read
    	 |description=read for U:U1
    	 |name=Right}}
    	
{{#manage rights: assigned to=User:U1}}
[[Category:ACL/ACL]]
ACL
,
//------------------------------------------------------------------------------
    	    'ACL:Property/Year_built' =>
<<<ACL
{{#property access: assigned to=User:U1
    	 |actions=read
    	 |description=read for U:U1
    	 |name=Right}}
    	
{{#manage rights: assigned to=User:U1}}
[[Category:ACL/ACL]]
ACL
,
//------------------------------------------------------------------------------
    	    'ACL:Property/Building_name' =>
<<<ACL
{{#property access: assigned to=User:U1
    	 |actions=read
    	 |description=read for U:U1
    	 |name=Right}}
    	
{{#manage rights: assigned to=User:U1}}
[[Category:ACL/ACL]]
ACL
,
//------------------------------------------------------------------------------
    	    'ACL:Property/Located_in' =>
<<<ACL
{{#property access: assigned to=User:U1
    	 |actions=read
    	 |description=read for U:U1
    	 |name=Right}}
    	
{{#manage rights: assigned to=User:U1}}
[[Category:ACL/ACL]]
ACL
,
    	 
    	);
	}
    
    /**
     * Dataprovider for 
     * 	- testHaloACLMemcacheFilterAccess
     */
    public function providerForResultFilterAccess() {
    	return array(
    		// $user, $title, $action, $expected
    		array('U1', 'MyPage', 'read', true),
    		array('U2', 'MyPage', 'read', false),
    	);
    }

    /**
     * Checks if the FSHaloACLMemcache retrieves the correct access rights.
     * 
     * @dataProvider providerForResultFilterAccess
     */
    public function testHaloACLMemcacheFilterAccess($user, $title, $action, $expected) {
//    	$this->markTestSkipped();
    	
    	// First ask MediaWiki for the permission. This should store the permission
    	// in memcache.
    	global $wgUser;
    	$wgUser = User::newFromName($user);
    	$t = Title::newFromText($title);
    	$allowed = $t->userCan($action);
    	$this->assertEquals($expected, $allowed, 
    		"Expected to get the permission '$expected' from MediaWiki.");
    	
    	// Now ask the memcache for the permission.
    	$hmc = FSHaloACLMemcache::getInstance();
    	
    	$allowed = $hmc->checkRights($user, $t->getNamespace().':'.$t->getDBkey(), $action);
    	
    	$this->assertEquals($expected, $allowed, 
    		"Expected to find the permission '$expected' in memcache.");
    }
    
    /**
     * 
     * Data provider for testSpecialSubjectAccess
     */
    public function providerForSpecialSubjectAccess() {
    	return array(
    		// $user, $subjectName, $action, $subjectType, $expected
    		array('U1', '14:MyCategory', 'read', 'category', true),
    		array('U1', '14:MySubCategory', 'read', 'category', true),
    		array('U2', '14:MyCategory', 'read', 'category', false),
    		array('U2', '14:MySubCategory', 'read', 'category', false),
    		array('U1', '102:MyProperty', 'read', 'property', true),
    		array('U2', '102:MyProperty', 'read', 'property', false),
    		array('U1', '102:MyProperty', 'read', 'property', true),
    		array('U2', '102:MyProperty', 'read', 'property', false),
    		array('U1', NS_USER_TALK, 'read', 'namespace', true),
    		array('U2', NS_USER_TALK, 'read', 'namespace', false),
    	);
    }
    
    /**
     * Checks access rights for properties and instances of categories and 
     * namespaces. 
     * 
     * @param string $user
     * 		Name of the user.
     * @param string/int $subjectName
     * 		The name of a category or property (starting with the namespace index)
     * 		or the numeric ID of a namespace
     * @param string $action
     * 		The action to check e.g. 'read'
     * @param string $subjectType
     * 		The type of the subject i.e. 'category', 'property' or 'namespace'
     * @param bool $expected
     * 		Expected access right
     * 
     * @dataProvider providerForSpecialSubjectAccess
     */
    public function testSpecialSubjectAccess($user, $subjectName, $action, $subjectType, $expected) {
//    	$this->markTestSkipped();
    	
    	switch ($subjectType) {
    		case 'category':
    			$stype = FSResultFilter::FSRF_CATEGORY;
    			break;	
    		case 'property':
    			$stype = FSResultFilter::FSRF_PROPERTY;
    			break;	
    		case 'namespace':
    			$stype = FSResultFilter::FSRF_NAMESPACE;
    			break;	
    	}
    	
    	// Try to find the access right in memcache. Do not expect to find it.
    	$hmc = FSHaloACLMemcache::getInstance();
    	$allowed = $hmc->checkRights($user, $subjectName, $action, $stype);
    	$this->assertEquals(-1, $allowed,
    		"Did not expect to find a permission in memcache.");
    	
    	// Try to retrieve the access right from MediaWiki. Right should be retrieved.
    	// First we have to setup some cookies as the solrproxy would receive them
    	// from the faceted search page.
    	$this->setupHttpSettingsForMWAccessControl($user);
    	$mwac = FSMWAccessControl::getInstance();
    	$allowed = $mwac->checkRights($subjectName, $action, $stype);
    	 
    	$this->assertEquals($expected, $allowed,
    		"Expected to find the permission '$expected' with mediawiki ajax call.");
    	    	
    	// Try to get the right with the result filter. The right should be available.
    	$rf = FSResultFilter::getInstance();
    	$allowed = $rf->checkRights($user, $subjectName, $action, $stype);
    	$this->assertEquals($expected, $allowed,
    		"Expected to find the permission '$expected' with the result filter.");
    	 
    	 
    	// Try again with memcache. The right should now be available.
    	$allowed = $hmc->checkRights($user, $subjectName, $action, $stype);
    	$this->assertEquals($expected, $allowed,
    	    		"Expected to find the permission '$expected' in memcache.");
    	
    }
    
    /**
     * 
     * Data provider for testSpecialSubjectAccessMulti
     */
    public function providerForSpecialSubjectAccessMulti() {
    	return array(
    		// $user, $subjectName, $action, $subjectType, $expected
    		array('U1', 
    				array('14:MyCategory', '14:MySubCategory'), 
    				'read', 'category', 
    				array(array('14:MyCategory' => true), 
    					  array('14:MySubCategory' => true))),
    		array('U2', 
    				array('14:MyCategory', '14:MySubCategory'), 
    				'read', 'category', 
    				array(array('14:MyCategory' => false),
    					  array('14:MySubCategory' => false))),
    		array('U1', 
    				array('102:MyProperty'),
    				'read', 'property', 
    				array(array('102:MyProperty' => true))),
    		array('U2', 
    				array('102:MyProperty'),
    				'read', 'property', 
    				array(array('102:MyProperty' => false))),
    		array('U1', 
    				array(NS_USER_TALK),
    				'read', 'namespace',
    				array(array(NS_USER_TALK => true))),
    		array('U2', 
    				array(NS_USER_TALK),
    				'read', 'namespace',
    				array(array(NS_USER_TALK => false))),
    	);
    }
    
    /**
     * Checks access rights for multiple properties and instances of categories and 
     * namespaces. 
     * 
     * @param string $user
     * 		Name of the user.
     * @param array(string/int) $subjectNames
     * 		The name of a category or property (starting with the namespace index)
     * 		or the numeric ID of a namespace
     * @param string $action
     * 		The action to check e.g. 'read'
     * @param string $subjectType
     * 		The type of the subject i.e. 'category', 'property' or 'namespace'
     * @param array(bool) $expected
     * 		Expected access rights
     * 
     * @dataProvider providerForSpecialSubjectAccessMulti
     */
    public function testSpecialSubjectAccessMulti($user, $subjectNames, $action, $subjectType, $expected) {
//    	$this->markTestSkipped();
    	
    	switch ($subjectType) {
    		case 'category':
    			$stype = FSResultFilter::FSRF_CATEGORY;
    			break;	
    		case 'property':
    			$stype = FSResultFilter::FSRF_PROPERTY;
    			break;	
    		case 'namespace':
    			$stype = FSResultFilter::FSRF_NAMESPACE;
    			break;	
    	}
    	
    	// Try to find the access rights in memcache. Do not expect to find them.
    	$hmc = FSHaloACLMemcache::getInstance();
    	$allowed = $hmc->checkRightsMulti($user, $subjectNames, $action, $stype);
    	$this->assertEquals(-1, $allowed,
    		"Did not expect to find a permission in memcache.");
    	
    	// Try to retrieve the access rights from MediaWiki. Rights should be retrieved.
    	// First we have to setup some cookies as the solrproxy would receive them
    	// from the faceted search page.
    	$this->setupHttpSettingsForMWAccessControl($user);
    	$mwac = FSMWAccessControl::getInstance();
    	$allowed = $mwac->checkRightsMulti($subjectNames, $action, $stype);
    	 
    	$this->assertEquals($expected, $allowed,
    		"Expected to find the permission '$expected' with mediawiki ajax call.");
    	    	
    	// Try to get the rights with the result filter. The rights should be available.
    	$rf = FSResultFilter::getInstance();
    	$allowed = $rf->checkRightsMulti($user, $subjectNames, $action, $stype);
    	$this->assertEquals($expected, $allowed,
    		"Expected to find the permission '$expected' with the result filter.");
    	 
    	// Try again with memcache. The rights should now be available.
    	$allowed = $hmc->checkRightsMulti($user, $subjectNames, $action, $stype);
    	$this->assertEquals($expected, $allowed,
    	    		"Expected to find the permission '$expected' in memcache.");
    	
    }
    
    /**
     * Checks if the FSMWAccessControl retrieves the correct access rights.
     * 
     * @dataProvider providerForResultFilterAccess
     */
    public function testMWAccessControlFilterAccess($user, $title, $action, $expected) {
//    	$this->markTestSkipped();
    	
    	// First ask MediaWiki directly for the permission.
    	global $wgUser;
    	$wgUser = User::newFromName($user);
    	$t = Title::newFromText($title);
    	$allowed = $t->userCan($action);
    	$this->assertEquals($expected, $allowed, 
    		"Expected to get the permission '$expected' from MediaWiki.");
    	
    	// Now ask the Mediawiki via an ajax call for the permission.
    	// First we have to setup some cookies as the solrproxy would receive them
    	// from the faceted search page.
    	$this->setupHttpSettingsForMWAccessControl($user);
    	$mwac = FSMWAccessControl::getInstance();
    	$allowed = $mwac->checkRights($t->getNamespace().':'.$t->getDBkey(), $action);
    	
    	$this->assertEquals($expected, $allowed, 
    		"Expected to find the permission '$expected' with mediawiki ajax call.");
    	
    }
    
    /**
     * Checks if the result filter retrieves the correct access rights.
     * 
     * @dataProvider providerForResultFilterAccess
     */
    public function testResultFilterAccess($user, $title, $action, $expected) {
//    	$this->markTestSkipped();
    	
    	$rf = FSResultFilter::getInstance();
    	
    	// First we have to setup some cookies as the solrproxy would receive them
    	// from the faceted search page.
    	$this->setupHttpSettingsForMWAccessControl($user);
    	$t = Title::newFromText($title);
    	
    	$allowed = $rf->checkRights($user, $t->getNamespace().':'.$t->getDBkey(), $action);
    	$this->assertEquals($expected, $allowed, 
    		"Expected to find the permission '$expected' with the result filter.");
    	
    	// Check again. Now the result should come from memcache.
    	$allowed = $rf->checkRights($user, $t->getNamespace().':'.$t->getDBkey(), $action);
    	$this->assertEquals($expected, $allowed, 
    		"Expected to find the permission '$expected' with the result filter.");
    }
    
    /**
     * Dataprovider for
     * 	- testHaloACLMemcacheFilterAccessMulti
     * 	- testResultFilterAccessMulti
     * 	- testMWAccessControlFilterAccessMulti
     */
    public function providerForResultFilterAccessMulti() {
    	return array(
	    	// $user, $titles, $action, $expected
	    	array('U1', array('MyPage','MyOtherPage'), 'read', array(true, false)),
	    	array('U2', array('MyPage','MyOtherPage'), 'read', array(false, true)),
    	);
    }
    
    /**
     * Checks if the FSHaloACLMemcache retrieves the correct access rights for
     * multiples pages at once.
     *
     * @dataProvider providerForResultFilterAccessMulti
     */
    public function testHaloACLMemcacheFilterAccessMulti($user, $titles, $action, $expected) {
//    	$this->markTestSkipped();
    	
    	// First ask MediaWiki for the permission. This should store the permission
    	// in memcache.
    	global $wgUser;
    	$wgUser = User::newFromName($user);
    	$tIDs = array();
    	foreach ($titles as $key => $title) {
	    	$t = Title::newFromText($title);
	    	$allowed = $t->userCan($action);
	    	$this->assertEquals($expected[$key], $allowed,
		    	"Expected to get the permission '$expected[$key]' from MediaWiki for title $title.");
	    	$tIDs[] = $t->getNamespace().':'.$t->getDBkey();
    	}
    	 
    	// Now ask the memcache for the permission.
    	$hmc = FSHaloACLMemcache::getInstance();
   		$permissions = $hmc->checkRightsMulti($user, $tIDs, $action);
    	
   		$this->assertTrue($permissions != -1, "Memcache could not be accessed by FSHaloACLMemcache::checkRightsMulti");
   		foreach ($permissions as $p) {
   			$t = array_keys($p);
   			$t = $t[0];
   			$idx = array_search($t, $tIDs);
	    	$this->assertEquals($expected[$idx], $p[$t],
	    		"Expected to find the permission '$expected[$idx]' in memcache for title with page ID$t.");
   		}
    }

    /**
    * Checks if the FSMWAccessControl retrieves the correct access rights for several
    * titles.
    *
    * @dataProvider providerForResultFilterAccessMulti
    */
    public function testMWAccessControlFilterAccessMulti($user, $titles, $action, $expected) {
//    	$this->markTestSkipped();
    	
    	// First ask MediaWiki directly for the permission.
    	global $wgUser;
    	$wgUser = User::newFromName($user);
    	$tIDs = array();
    	foreach ($titles as $key => $title) {
	    	$t = Title::newFromText($title);
	    	$allowed = $t->userCan($action);
	    	$this->assertEquals($expected[$key], $allowed,
		    	"Expected to get the permission '$expected[$key]' from MediaWiki for title $title.");
	    	$tIDs[] = $t->getNamespace().':'.$t->getDBkey();
    	}
    	    	 
    	// Now ask the Mediawiki via an ajax call for the permission.
    	// First we have to setup some cookies as the solrproxy would receive them
    	// from the faceted search page.
    	$this->setupHttpSettingsForMWAccessControl($user);
    	$mwac = FSMWAccessControl::getInstance();
    	$permissions = $mwac->checkRightsMulti($tIDs, $action);
    	 
       	foreach ($permissions as $p) {
   			$t = array_keys($p);
   			$t = $t[0];
   			$idx = array_search($t, $tIDs);
	    	$this->assertEquals($expected[$idx], $p[$t],
	    		"Expected to find the permission '$expected[$idx]' with mediawiki ajax call for title with page ID $t.");
   		}
    	        	
    }
    
    /**
    * Checks if the result filter retrieves the correct access rights for several 
    * titles.
    *
    * @dataProvider providerForResultFilterAccessMulti
    */
    public function testResultFilterAccessMulti($user, $titles, $action, $expected) {
//    	$this->markTestSkipped();
    	
    	$rf = FSResultFilter::getInstance();
    	 
    	// First we have to setup some cookies as the solrproxy would receive them
    	// from the faceted search page.
    	$this->setupHttpSettingsForMWAccessControl($user);
    	
    	// Convert titles to their IDs
    	$tIDs = array();
    	foreach ($titles as $title) {
    		$t = Title::newFromText($title);
    		$tIDs[] = $t->getArticleID();
    	}
    	
    	$permissions = $rf->checkRightsMulti($user, $tIDs, $action);
       	foreach ($permissions as $p) {
   			$t = array_keys($p);
   			$t = $t[0];
   			$idx = array_search($t, $tIDs);
	    	$this->assertEquals($expected[$idx], $p[$t],
	    		"Expected to find the permission '$expected[$idx]' in result filter for title with page ID $t.");
   		}
    	    	 
    	// Check again. Now the result should come from memcache.
    	$permissions = $rf->checkRightsMulti($user, $tIDs, $action);
		foreach ($permissions as $p) {
   			$t = array_keys($p);
   			$t = $t[0];
   			$idx = array_search($t, $tIDs);
	    	$this->assertEquals($expected[$idx], $p[$t],
	    		"Expected to find the permission '$expected[$idx]' in result filter for title with page ID $t.");
   		}
   		
   		// Now only a part of the permissions should come from memcache.
   		HACLMemcache::getInstance()->purgeCache();
		// Put the permission for the first title in memcache
   		$rf->checkRights($user, $tIDs[0], $action);
   		
   		// Now retrieve permissions for all titles
    	$permissions = $rf->checkRightsMulti($user, $tIDs, $action);
   		foreach ($permissions as $p) {
   			$t = array_keys($p);
   			$t = $t[0];
   			$idx = array_search($t, $tIDs);
	    	$this->assertEquals($expected[$idx], $p[$t],
	    		"Expected to find the permission '$expected[$idx]' in result filter for title with page ID $t.");
   		}
    	
    }

    /**
     * Dataprovider for testResultFilter
     */
    public function providerForResultFilter() {
    	return array(
	    	// $user, $query, $expectedBeforeFilter, $expectedAfterFilter 
	    	array('U1', "q=smwh_search_field:(%2Bmy*)&wt=json&indent=on",
	    		array(  '"smwh_title":"MyPage"', 
	    				'"smwh_title":"Page/MyPage"',
	    				'"smwh_title":"MyOtherPage"',
	    				'"smwh_title":"Page/MyOtherPage"'),
	    		array(  '"smwh_title":"MyPage"', 
	    				'"smwh_title":"Page/MyPage"',
	    				'"smwh_title":"Page/MyOtherPage"')
	    	),
	    	array('U2', "q=smwh_search_field:(%2Bmy*)&wt=json&indent=on", 
	    		array(  '"smwh_title":"MyPage"', 
	    				'"smwh_title":"Page/MyPage"',
	    				'"smwh_title":"MyOtherPage"',
	    				'"smwh_title":"Page/MyOtherPage"'),
	    		array(  '"smwh_title":"MyOtherPage"', 
	    				'"smwh_title":"Page/MyPage"',
	    				'"smwh_title":"Page/MyOtherPage"')
	    	),
    	);
    }
    
    /**
     * Tests the result filter for SOLR queries. Results that are protected by
     * HaloACL have to be removed by the filter.
     * 
     * @param string $user
     * 		Name of the user who makes the request
     * @param string $query
     * 		A SOLR query
     * @param string $expectedBeforeFilter
     * 		Expected result before the filter is applied
     * @param string $expectedAfterFilter
     * 		Expected result after the filter is applied
     * 
     * @dataProvider providerForResultFilter
     */
    public function testResultFilter($user, $query, $expectedBeforeFilter, 
    								 $expectedAfterFilter ) {
//    	$this->markTestSkipped();
    	
    	// First we have to setup some cookies as the solrproxy would receive them
    	// from the faceted search page.
    	$this->setupHttpSettingsForMWAccessControl($user);
    	
    	// Send the query to SOLR
    	$indexer = FSIndexerFactory::create(TestSolrProxySuite::$mSolrConfig);
    	$qr = $indexer->sendRawQuery($query);
    	
    	// Check the result before filtering
    	foreach ($expectedBeforeFilter as $er) {
    		$this->assertContains($er, $qr, "Could not find expected string <$er> in result:\n$qr");
    	}
    	
    	// Now filter the result with the result filter
    	$rf = FSResultFilter::getInstance();
    	$fqr = $rf->filterResult($user, 'read', $qr);
    	
    	// json_encode escapes '/' as '\/' => replace it for the tests
    	$fqr = str_replace('\/', '/', $fqr);
    	
    	// Check the result after filtering for articles that should be still there
    	foreach ($expectedAfterFilter as $er) {
    		$this->assertContains($er, $fqr, "Could not find expected string <$er> in result:\n$fqr");
    	}
    	
    	// Check the result after filtering for articles that should be removed
    	$removed = array_diff($expectedBeforeFilter, $expectedAfterFilter);
    	foreach ($removed as $er) {
    		$this->assertNotContains($er, $fqr, "Did not expect to find the string <$er> in result:\n$fqr");
    	}
    	 
    }
    
    
    /**
     * Data provider for testFacetFilter
     */
    public function providerForFacetFilter() {
    	return array(
	    	// $user, $query, $expectedBeforeFilter, $expectedAfterFilter
#0 - Ask for all documents that contain "tower".    	
	    	array(
	    		'U2', 
	    		"q=smwh_search_field%3A(%2Btower*%20)&facet=true&facet.field=smwh_categories&facet.field=smwh_attributes&facet.field=smwh_properties&facet.field=smwh_namespace_id&facet.mincount=1&json.nl=map&fl=smwh_Modification_date_xsdvalue_dt%2Csmwh_categories%2Csmwh_attributes%2Csmwh_properties%2Cid%2Csmwh_title%2Csmwh_namespace_id&hl=true&hl.fl=smwh_search_field&hl.simple.pre=%3Cb%3E&hl.simple.post=%3C%2Fb%3E&hl.fragsize=250&json.nl=map&wt=json&json.wrf=_jqjsp&_1331909354996=",
	    		// Expected facet values
    			array('smwh_categories' => 
    				array('Building',
    					  'Skyscrapers_between_200_and_249_meters',
    					  'Skyscrapers_between_250_and_299_meters'),
    				  'smwh_attributes' =>
    				array('smwh_Building_name_xsdvalue_t',
    					  'smwh_Height_stories_xsdvalue_d',
    					  'smwh_Year_built_xsdvalue_dt'),
    				  'smwh_properties' =>
    				array('smwh_Located_in_t'),
    				  'smwh_namespace_id' =>
    				array(NS_MAIN."", NS_USER_TALK."")
    			),
    			
    			// Expected facet values that will be removed
    			array('smwh_categories' => 
    				array('Skyscrapers_between_200_and_249_meters',
    					  'Skyscrapers_between_250_and_299_meters'),
    				  'smwh_attributes' =>
    				array('smwh_Building_name_xsdvalue_t',
    					  'smwh_Height_stories_xsdvalue_d',
    					  'smwh_Year_built_xsdvalue_dt'),
    				  'smwh_properties' =>
    				array('smwh_Located_in_t'),
    				  'smwh_namespace_id' =>
    				array(NS_USER_TALK.""),
    				  'highlight' => 
    				array('Bank_of_America_Tower_(New_York_City)')
    			)
    		),
#1 - Ask for all documents and the facet property and "Located_in_state"   	
	    	array(
	    		'U2', 
	    		"q=smwh_search_field%3A(%2Bd*%20)&facet=true&facet.field=smwh_categories&facet.field=smwh_attributes&facet.field=smwh_properties&facet.field=smwh_namespace_id&facet.field=smwh_Located_in_state_s&facet.mincount=1&json.nl=map&fl=smwh_Modification_date_xsdvalue_dt%2Csmwh_categories%2Csmwh_attributes%2Csmwh_properties%2Cid%2Csmwh_title%2Csmwh_namespace_id&hl=true&hl.fl=smwh_search_field&hl.simple.pre=%3Cb%3E&hl.simple.post=%3C%2Fb%3E&hl.fragsize=250&wt=json&json.wrf=_jqjsp&_1331909354996=",
	    		// Expected facet values
     			array('smwh_categories' => 
    				array('United_States_City'),
    				  'smwh_properties' =>
    				array('smwh_Located_in_state_t'),
    				  'smwh_Located_in_state_s' =>
    				array('Texas',
    					  'California'),
    			),
    			
    			// Expected facet values that will be removed
     			array('smwh_Located_in_state_s' =>
    				array('Texas'),
    			),
   			)
    	);
    }
        
    /**
     * The result of a query may also contain the facets of all result documents
     * i.e. categories, namespaces and properties that are annotated in the 
     * documents. The items that are protected must be removed from the facets.
     * 
     * @param string $user
     * 		Name of the user who makes the request
     * @param string $query
     * 		A SOLR query
     * @param array(string=>array(string)) $expectedBeforeFilter
     * 		A map from facet names to an array of strings that must appear in the
     * 		facet.
     * @param array(string=>array(string)) $expectedRemovals
     * 		A map from facet names to an array of strings that must be removed 
     * 		from the facet by the filter.
     * 
     * @dataProvider providerForFacetFilter
     */
    public function testFacetFilter($user, $query, $expectedBeforeFilter, 
    								$expectedRemovals) {
//    	$this->markTestSkipped();
    	
    	// First we have to setup some cookies as the solrproxy would receive them
    	// from the faceted search page.
    	$this->setupHttpSettingsForMWAccessControl($user);
    	
    	// Send the query to SOLR
    	$indexer = FSIndexerFactory::create(TestSolrProxySuite::$mSolrConfig);
    	$qr = $indexer->sendRawQuery($query);
    	
    	// Check the result before filtering. First decode the returned json
    	$qrCopy = $qr;
		$prefix = '_jqjsp(';
		if (strpos($qrCopy, $prefix) === 0) {
			$qrCopy = substr($qrCopy, strlen($prefix), 
								 strlen($qrCopy) - strlen($prefix) - 1);
		}
    	$resultObj = json_decode($qrCopy);
    	
    	// Store the IDs of documents in the result
    	$docs = $resultObj->response->docs;
    	$unfilteredDocIDs = array();
    	foreach ($docs as $doc) {
    		$unfilteredDocIDs[] = $doc->id;
    	}
    	
    	// Now check for the expected results
    	foreach ($expectedBeforeFilter as $facet => $facetValues) {
    		$actualValues = @$resultObj->facet_counts->facet_fields->$facet;	
    		foreach ($facetValues as $value) {
	    		$this->assertObjectHasAttribute($value, $actualValues, 
	    			"Could not find expected value <$value> in the facet <$facet> of result:\n$qr");
    		}
    	}
    	
    	// Now filter the result with the result filter
    	$rf = FSResultFilter::getInstance();
    	$fqr = $rf->filterResult($user, 'read', $qr);
    	
		if (strpos($fqr, $prefix) === 0) {
			$fqr = substr($fqr, strlen($prefix), 
						  strlen($fqr) - strlen($prefix) - 1);
		}
    	$resultObj = json_decode($fqr);
    	
    	// Now check for the expected removed results
    	foreach ($expectedRemovals as $facet => $facetValues) {
    		$actualValues = @$resultObj->facet_counts->facet_fields->$facet;
    		if ($actualValues) {	
	    		foreach ($facetValues as $value) {
		    		$this->assertObjectNotHasAttribute($value, $actualValues, 
		    			"Expected that value <$value> in the facet <$facet> was removed from result:\n$qr");
	    		}
    		}
    	}
    	
    	// Verify that protected properties/attributes are removed from all 
    	// documents
    	$docs = $resultObj->response->docs;
    	foreach ($docs as $doc) {
    		// Iterate over all attributes/properties that are expected to be
    		// removed.
    		foreach (array('smwh_attributes', 'smwh_properties') as $field) {
    			$expRemoved = $expectedRemovals[$field];
    			$docField = $doc->$field;
    			if ($docField && $expRemoved) {
	    			foreach ($expRemoved as $removed) {
	    				$this->assertNotContains($removed, $docField, 
	    					"Expected that the value '$removed' is removed from field '$field' in document {$doc->smwh_title}.");
	    			}
    			}
    		}
    	}
    	
    	// Verify that highlights (snippets) for removed documents are removed as well
    	// Store the IDs of documents in the filtered result
    	$docs = $resultObj->response->docs;
    	$filteredDocIDs = array();
    	foreach ($docs as $doc) {
    		$filteredDocIDs[] = $doc->id;
    	}
    	$removedDocIDs = array_diff($unfilteredDocIDs, $filteredDocIDs);
    	$highlights = @$resultObj->highlighting;
    	if ($highlights && array_key_exists('highlight', $expectedRemovals)) {
    		$highlightDocIDs = array_keys(get_object_vars($highlights));
    		$intersect = array_intersect($removedDocIDs, $highlightDocIDs);
    		$errMsg = implode(',', $intersect);
    		$this->assertEmpty($intersect, 
    			"Expected that all snippets for filtered documents are removed as well. Remaining IDs are: $errMsg");
    	
    		// Expected that the remaining highlights (snippets) are replaced.
    		foreach ($expectedRemovals['highlight'] as $docName) {
	    		foreach ($docs as $doc) {
	    			if ($doc->smwh_title === $docName) {
	    				$hid = $doc->id;
		    			$snippet = $highlights->$hid;
		    			$text = $snippet->smwh_search_field[0];
		    			$expMsg = FSMessages::msg('snippet_removed');
		    			$this->assertEquals($expMsg, $text, "Expected that the snippet is replaced by a notice.");
	    			}
	    		}
    		}
    	
    	}
    }
    
    
    /**
     * Data provider for testFindExpectedNumberOfResults
     */
    public function providerForFindExpectedNumberOfResults() {
    	return array(
	    	// $user, $action, $query, $expNumPermittedResults, $expNumNeededResults, $expFurtherResultsAvailable
	    	#0
	    	array("U1", "read", 
	    		"q=smwh_search_field%3A(%2Btow*%20)&start=0&rows=10&facet=true&facet.field=smwh_categories&facet.field=smwh_attributes&facet.field=smwh_properties&facet.field=smwh_namespace_id&facet.mincount=1&json.nl=map&fl=smwh_Modification_date_xsdvalue_dt%2Csmwh_categories%2Csmwh_attributes%2Csmwh_properties%2Cid%2Csmwh_title%2Csmwh_namespace_id&hl=true&hl.fl=smwh_search_field&hl.simple.pre=%3Cb%3E&hl.simple.post=%3C%2Fb%3E&hl.fragsize=250&sort=smwh_Modification_date_xsdvalue_dt%20desc&wt=json&json.wrf=_jqjsp&_1332827921888=",
		    	10, 10, true),
	    	#1
	    	array("U1", "read", 
	    		"q=smwh_search_field%3A(%2Btow*%20)&start=0&rows=20&facet=true&facet.field=smwh_categories&facet.field=smwh_attributes&facet.field=smwh_properties&facet.field=smwh_namespace_id&facet.mincount=1&json.nl=map&fl=smwh_Modification_date_xsdvalue_dt%2Csmwh_categories%2Csmwh_attributes%2Csmwh_properties%2Cid%2Csmwh_title%2Csmwh_namespace_id&hl=true&hl.fl=smwh_search_field&hl.simple.pre=%3Cb%3E&hl.simple.post=%3C%2Fb%3E&hl.fragsize=250&sort=smwh_Modification_date_xsdvalue_dt%20desc&wt=json&json.wrf=_jqjsp&_1332827921888=",
		    	20, 10, true),
	    	#2
	    	array("U2", "read", 
	    		"q=smwh_search_field%3A(%2Btow*%20)&start=0&rows=10&facet=true&facet.field=smwh_categories&facet.field=smwh_attributes&facet.field=smwh_properties&facet.field=smwh_namespace_id&facet.mincount=1&json.nl=map&fl=smwh_Modification_date_xsdvalue_dt%2Csmwh_categories%2Csmwh_attributes%2Csmwh_properties%2Cid%2Csmwh_title%2Csmwh_namespace_id&hl=true&hl.fl=smwh_search_field&hl.simple.pre=%3Cb%3E&hl.simple.post=%3C%2Fb%3E&hl.fragsize=250&sort=smwh_Modification_date_xsdvalue_dt%20desc&wt=json&json.wrf=_jqjsp&_1332827921888=",
		    	2, -1, true),
	    	#3
	    	array("U2", "read", 
	    		"q=smwh_search_field%3A(%2Btow*%20)&start=0&rows=20&facet=true&facet.field=smwh_categories&facet.field=smwh_attributes&facet.field=smwh_properties&facet.field=smwh_namespace_id&facet.mincount=1&json.nl=map&fl=smwh_Modification_date_xsdvalue_dt%2Csmwh_categories%2Csmwh_attributes%2Csmwh_properties%2Cid%2Csmwh_title%2Csmwh_namespace_id&hl=true&hl.fl=smwh_search_field&hl.simple.pre=%3Cb%3E&hl.simple.post=%3C%2Fb%3E&hl.fragsize=250&sort=smwh_Modification_date_xsdvalue_dt%20desc&wt=json&json.wrf=_jqjsp&_1332827921888=",
		    	5, -1, true),
	    	#4
	    	array("U2", "read", 
	    		"q=smwh_search_field%3A(%2Btow*%20)&start=0&rows=30&facet=true&facet.field=smwh_categories&facet.field=smwh_attributes&facet.field=smwh_properties&facet.field=smwh_namespace_id&facet.mincount=1&json.nl=map&fl=smwh_Modification_date_xsdvalue_dt%2Csmwh_categories%2Csmwh_attributes%2Csmwh_properties%2Cid%2Csmwh_title%2Csmwh_namespace_id&hl=true&hl.fl=smwh_search_field&hl.simple.pre=%3Cb%3E&hl.simple.post=%3C%2Fb%3E&hl.fragsize=250&sort=smwh_Modification_date_xsdvalue_dt%20desc&wt=json&json.wrf=_jqjsp&_1332827921888=",
		    	6, -1, true),
	    	#5
	    	array("U2", "read", 
	    		"q=smwh_search_field%3A(%2Btow*%20)&start=0&rows=40&facet=true&facet.field=smwh_categories&facet.field=smwh_attributes&facet.field=smwh_properties&facet.field=smwh_namespace_id&facet.mincount=1&json.nl=map&fl=smwh_Modification_date_xsdvalue_dt%2Csmwh_categories%2Csmwh_attributes%2Csmwh_properties%2Cid%2Csmwh_title%2Csmwh_namespace_id&hl=true&hl.fl=smwh_search_field&hl.simple.pre=%3Cb%3E&hl.simple.post=%3C%2Fb%3E&hl.fragsize=250&sort=smwh_Modification_date_xsdvalue_dt%20desc&wt=json&json.wrf=_jqjsp&_1332827921888=",
		    	8, -1, true),
	    	#6
	    	array("U2", "read", 
	    		"q=smwh_search_field%3A(%2Btow*%20)&start=0&rows=50&facet=true&facet.field=smwh_categories&facet.field=smwh_attributes&facet.field=smwh_properties&facet.field=smwh_namespace_id&facet.mincount=1&json.nl=map&fl=smwh_Modification_date_xsdvalue_dt%2Csmwh_categories%2Csmwh_attributes%2Csmwh_properties%2Cid%2Csmwh_title%2Csmwh_namespace_id&hl=true&hl.fl=smwh_search_field&hl.simple.pre=%3Cb%3E&hl.simple.post=%3C%2Fb%3E&hl.fragsize=250&sort=smwh_Modification_date_xsdvalue_dt%20desc&wt=json&json.wrf=_jqjsp&_1332827921888=",
		    	8, -1, false),
	    	#7
	    	array("U2", "read", 
	    		"q=smwh_search_field%3A(%2Bw*%20)&start=0&rows=20&facet=true&facet.field=smwh_categories&facet.field=smwh_attributes&facet.field=smwh_properties&facet.field=smwh_namespace_id&facet.mincount=1&json.nl=map&fl=smwh_Modification_date_xsdvalue_dt%2Csmwh_categories%2Csmwh_attributes%2Csmwh_properties%2Cid%2Csmwh_title%2Csmwh_namespace_id&hl=true&hl.fl=smwh_search_field&hl.simple.pre=%3Cb%3E&hl.simple.post=%3C%2Fb%3E&hl.fragsize=250&sort=smwh_Modification_date_xsdvalue_dt%20desc&wt=json&json.wrf=_jqjsp&_1332827921888=",
		    	6, -1, true),
	    	#8
	    	array("U2", "read", 
	    		"q=smwh_search_field%3A(%2Bw*%20)&start=0&rows=40&facet=true&facet.field=smwh_categories&facet.field=smwh_attributes&facet.field=smwh_properties&facet.field=smwh_namespace_id&facet.mincount=1&json.nl=map&fl=smwh_Modification_date_xsdvalue_dt%2Csmwh_categories%2Csmwh_attributes%2Csmwh_properties%2Cid%2Csmwh_title%2Csmwh_namespace_id&hl=true&hl.fl=smwh_search_field&hl.simple.pre=%3Cb%3E&hl.simple.post=%3C%2Fb%3E&hl.fragsize=250&sort=smwh_Modification_date_xsdvalue_dt%20desc&wt=json&json.wrf=_jqjsp&_1332827921888=",
		    	15, 29, true),
	    	#9
	    	array("U2", "read", 
	    		"q=smwh_search_field%3A(%2Bw*%20)&start=0&rows=29&facet=true&facet.field=smwh_categories&facet.field=smwh_attributes&facet.field=smwh_properties&facet.field=smwh_namespace_id&facet.mincount=1&json.nl=map&fl=smwh_Modification_date_xsdvalue_dt%2Csmwh_categories%2Csmwh_attributes%2Csmwh_properties%2Cid%2Csmwh_title%2Csmwh_namespace_id&hl=true&hl.fl=smwh_search_field&hl.simple.pre=%3Cb%3E&hl.simple.post=%3C%2Fb%3E&hl.fragsize=250&sort=smwh_Modification_date_xsdvalue_dt%20desc&wt=json&json.wrf=_jqjsp&_1332827921888=",
		    	10, 29, true),
	    	#10
	    	array("U2", "read", 
	    		"q=smwh_search_field%3A(%2Bw*%20)&start=0&rows=28&facet=true&facet.field=smwh_categories&facet.field=smwh_attributes&facet.field=smwh_properties&facet.field=smwh_namespace_id&facet.mincount=1&json.nl=map&fl=smwh_Modification_date_xsdvalue_dt%2Csmwh_categories%2Csmwh_attributes%2Csmwh_properties%2Cid%2Csmwh_title%2Csmwh_namespace_id&hl=true&hl.fl=smwh_search_field&hl.simple.pre=%3Cb%3E&hl.simple.post=%3C%2Fb%3E&hl.fragsize=250&sort=smwh_Modification_date_xsdvalue_dt%20desc&wt=json&json.wrf=_jqjsp&_1332827921888=",
		    	9, -1, true),
    	);
    	 
    }
    
    /**
     * When SOLR query results are filtered some document might get lost. However,
     * the user expects a constant number of results as he is flipping through 
     * the pages of results. The solrproxy has to make sure that the expected 
     * number of results is returned for each page if there are enough permitted
     * results left.
     * 
     * @param string $user
     * 		Name of the user who makes the request
     * @param string $query
     * 		A SOLR query
     * 
     * @dataProvider providerForFindExpectedNumberOfResults
     */
    public function testFindExpectedNumberOfResults($user, $action, $query, 
    						$expNumPermittedResults, $expNumNeededResults,
    						$expFurtherResultsAvailable) {
    	$this->markTestSkipped();
    	
    	// First we have to setup some cookies as the solrproxy would receive them
    	// from the faceted search page.
    	$this->setupHttpSettingsForMWAccessControl($user);
    	
    	// Send the query to SOLR
    	$indexer = FSIndexerFactory::create(TestSolrProxySuite::$mSolrConfig);
    	$qr = $indexer->sendRawQuery($query);
    	
    	// Now check how many permitted results are present
    	$resultFilter = FSResultFilter::getInstance();
    	$numExpectedResults = 10;
    	list($numPermittedResults, $numNeededResults, $furtherResultsAvailable) =
	    	$resultFilter->countPermittedResults($user, 'read', $qr, $numExpectedResults);
    	
    	$this->assertEquals($expNumPermittedResults, $numPermittedResults, 
    		"The expected number of permitted results does not match the actual number.");
    	$this->assertEquals($expNumNeededResults, $numNeededResults, 
    		"The expected number of needed results does not match the actual number.");
    	$this->assertEquals($expFurtherResultsAvailable, $furtherResultsAvailable, 
    		"The evaluation if further results are available failed.");
    	    	
    }
    
    
    /**
     * Needed to setup a fake environment for the class FSMWAccessControl which
     * does an ajax call for the user who is currently logged in.
     * 
     * @param string $user
     * 		Name of a user
     */
    private function setupHttpSettingsForMWAccessControl($user) {
	 
    	$u = User::newFromName($user);
    	$uid = $u->getId();

    	// Login the wiki
    	$cc = new cURL();
    	$loginXML = $cc->post("http://localhost/mediawiki/api.php","action=login&lgname=$user&lgpassword=test&format=xml");
    	
    	preg_match("/cookieprefix=\"(.*?)\"/", $loginXML, $cookiePrefix);
    	preg_match("/sessionid=\"(.*?)\"/", $loginXML, $sessionID);
    	preg_match("/token=\"(.*?)\"/", $loginXML, $token);

    	$cookiePrefix = $cookiePrefix[1];
    	$sessionID = $sessionID[1];
    	$token = $token[1];
    	
    	$loginXML = $cc->post("http://localhost/mediawiki/api.php","action=login&lgname=$user&lgpassword=test&format=xml&lgtoken=$token");
    	
    	// Now set the server variables
    	$_SERVER[HTTP_ACCEPT] = 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';
		// Accept-Language: 
		$_SERVER[HTTP_ACCEPT_LANGUAGE] = 'de-de,de;q=0.8,en-us;q=0.5,en;q=0.3';
		// Connection: 
		$_SERVER[HTTP_CONNECTION] = 'keep-alive';
		// Cookie: 
		$_SERVER[HTTP_COOKIE] = "{$cookiePrefix}UserName=$user; {$cookiePrefix}UserID=$uid; {$cookiePrefix}_session=$sessionID;";
		// Host: 
		$_SERVER[HTTP_HOST] = 'localhost';
		// User-Agent: 
		$_SERVER[HTTP_USER_AGENT] = 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:10.0.2) Gecko/20100101 Firefox/10.0.2';
		
		
    	   
    }
    
}


class cURL {
	var $headers;
	var $user_agent;
	var $compression;
	var $cookie_file;
	var $proxy;
	function cURL($cookies=TRUE,$cookie='cookies.txt',$compression='gzip',$proxy='') {
		$this->headers[] = 'Accept: image/gif, image/x-bitmap, image/jpeg, image/pjpeg';
		$this->headers[] = 'Connection: Keep-Alive';
		$this->headers[] = 'Content-type: application/x-www-form-urlencoded;charset=UTF-8';
		$this->user_agent = 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.0.3705; .NET CLR 1.1.4322; Media Center PC 4.0)';
		$this->compression=$compression;
		$this->proxy=$proxy;
		$this->cookies=$cookies;
		if ($this->cookies == TRUE) $this->cookie($cookie);
	}
	function cookie($cookie_file) {
		if (file_exists($cookie_file)) {
			$this->cookie_file=$cookie_file;
		} else {
			$file = fopen($cookie_file,'w') or $this->error('The cookie file could not be opened. Make sure this directory has the correct permissions');
			$this->cookie_file=$cookie_file;
			fclose($file);
		}
	}
	function get($url) {
		$process = curl_init($url);
		curl_setopt($process, CURLOPT_HTTPHEADER, $this->headers);
		curl_setopt($process, CURLOPT_HEADER, 0);
		curl_setopt($process, CURLOPT_USERAGENT, $this->user_agent);
		if ($this->cookies == TRUE) curl_setopt($process, CURLOPT_COOKIEFILE, $this->cookie_file);
		if ($this->cookies == TRUE) curl_setopt($process, CURLOPT_COOKIEJAR, $this->cookie_file);
		curl_setopt($process,CURLOPT_ENCODING , $this->compression);
		curl_setopt($process, CURLOPT_TIMEOUT, 30);
		if ($this->proxy) curl_setopt($cUrl, CURLOPT_PROXY, 'proxy_ip:proxy_port');
		curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);
		$return = curl_exec($process);
		curl_close($process);
		return $return;
	}
	function post($url,$data) {
		$process = curl_init($url);
		curl_setopt($process, CURLOPT_HTTPHEADER, $this->headers);
		curl_setopt($process, CURLOPT_HEADER, 1);
		curl_setopt($process, CURLOPT_USERAGENT, $this->user_agent);
		if ($this->cookies == TRUE) curl_setopt($process, CURLOPT_COOKIEFILE, $this->cookie_file);
		if ($this->cookies == TRUE) curl_setopt($process, CURLOPT_COOKIEJAR, $this->cookie_file);
		curl_setopt($process, CURLOPT_ENCODING , $this->compression);
		curl_setopt($process, CURLOPT_TIMEOUT, 30);
		if ($this->proxy) curl_setopt($process, CURLOPT_PROXY, $this->proxy);
		curl_setopt($process, CURLOPT_POSTFIELDS, $data);
		curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($process, CURLOPT_POST, 1);
		$return = curl_exec($process);
		curl_close($process);
		return $return;
	}
	function error($error) {
		echo "<center><div style='width:500px;border: 3px solid #FFEEFF; padding: 3px; background-color: #FFDDFF;font-family: verdana; font-size: 10px'><b>cURL Error</b><br>$error</div></center>";
		die;
	}
}
