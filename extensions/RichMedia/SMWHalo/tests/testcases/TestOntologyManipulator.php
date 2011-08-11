<?php 
/**
 * This suite tests functions of SMW_OntologyManipulator.php
 * 
 * @author thsc
 *
 */

require_once 'CommonClasses.php';

class TestOntologyManipulatorSuite extends PHPUnit_Framework_TestSuite
{
	private $mArticleManager;
	
	private $mArticles = array(
//------------------------------------------------------------------------------		
	"Property:STB_string" =>
<<<ARTICLE
[[has type::Type:String| ]]
ARTICLE
,
//------------------------------------------------------------------------------		
	"Property:STB_page" =>
<<<ARTICLE
[[has domain and range::; | ]]
[[has type::Type:Page| ]]
ARTICLE
,
//------------------------------------------------------------------------------		
	"Property:STB_pageWithCat" =>
<<<ARTICLE
[[has domain and range::Category:Domain;Category:Range1| ]]
[[has type::Type:Page| ]]
ARTICLE
,
//------------------------------------------------------------------------------		
	"Property:STB_nary" =>
<<<ARTICLE
[[has domain and range::; | ]]
[[has domain and range::; | ]]
[[has type::Record| ]]
[[has fields::Type:Page;Type:Number;Type:String;Type:Page| ]]
ARTICLE
,
//------------------------------------------------------------------------------		
	"Property:STB_naryWithCat" =>
<<<ARTICLE
[[has domain and range::Category:Domain; Category:Range1| ]]
[[has domain and range::Category:Domain; Category:Range2| ]]
[[has type::Record| ]]
[[has fields::Type:Page;Type:Number;Type:String;Type:Page| ]]
ARTICLE

	); 
	
	public static function suite() {
		if (!defined('UNIT_TEST_RUNNING')) {
			define('UNIT_TEST_RUNNING', true);
		}
		
		$suite = new TestOntologyManipulatorSuite();
		$suite->addTestSuite('TestMultipleRelationInfo');
		return $suite;
	}
	
	protected function setUp() {
		SMWHaloCommon::createUsers(array("U1"));
        Skin::getSkinNames();
        
   		global $wgUser;
    	$wgUser = User::newFromName("U1");
    	
    	$this->mArticleManager = new ArticleManager();
    	$this->mArticleManager->createArticles($this->mArticles, "U1");
	}
	
	protected function tearDown() {
        $this->mArticleManager->deleteArticles("U1");
	}
 	
}

/**
 * This class tests the function smwf_om_MultipleRelationInfo
 * 
 * @author thsc
 *
 */
class TestMultipleRelationInfo extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = FALSE;
	
	// List of articles that were added during a test.
	private static $mArticleManager;
	
    public static function setUpBeforeClass() {
    	self::$mArticleManager = new ArticleManager();
    }

    /**
     * Delete all articles that were created during a test.
     */
    public static function tearDownAfterClass() {
    	self::$mArticleManager->deleteArticles("U1");
    }
    
    /**
     * Checks that the function smwf_om_MultipleRelationInfo is defined.
     */
	public function testFunctionMultipleRelationInfoExists() {
		$this->assertTrue(function_exists('smwf_om_MultipleRelationInfo'), 
		                  "The function 'smwf_om_MultipleRelationInfo' is not defined.");
	}

	public function providerCorrectRelationInfoIsReturned() {
		return array(
			array(
				'[{"name": "Property:NonExisting", "values": ["test"], "accessRequest": "propertyedit"}]',
				'[{"name":"Property:NonExisting","values":["test"],"accessRequest":"propertyedit","relationExists":"false","accessGranted":"true","valuePageInfo":["redlink"],"rangeCategories":[null],"relationSchema":["_wpg"]}]'),
			array(
				'[{"name": "Property:STB_string", "values": ["test"], "accessRequest": "propertyedit"}]',
				'[{"name":"Property:STB_string","values":["test"],"accessRequest":"propertyedit","relationExists":"true","accessGranted":"true","valuePageInfo":["no page"],"rangeCategories":[null],"relationSchema":["_str"]}]'),
			array(
				'[{"name": "Property:STB_page", "values": ["Non existing page"], "accessRequest": "propertyedit"}]',
				'[{"name":"Property:STB_page","values":["Non existing page"],"accessRequest":"propertyedit","relationExists":"true","accessGranted":"true","valuePageInfo":["redlink"],"rangeCategories":[null],"relationSchema":["_wpg"]}]'),
			array(
				'[{"name": "Property:STB_page", "values": ["Main Page"], "accessRequest": "propertyedit"}]',
				'[{"name":"Property:STB_page","values":["Main Page"],"accessRequest":"propertyedit","relationExists":"true","accessGranted":"true","valuePageInfo":["exists"],"rangeCategories":[null],"relationSchema":["_wpg"]}]'),
			array(
				'[{"name": "Property:STB_pageWithCat", "values": ["Main Page"], "accessRequest": "propertyedit"}]',
				'[{"name":"Property:STB_pageWithCat","values":["Main Page"],"accessRequest":"propertyedit","relationExists":"true","accessGranted":"true","valuePageInfo":["exists"],"rangeCategories":["Range1"],"relationSchema":["_wpg"]}]'),
			array(
				'[{"name": "Property:STB_nary", "values": ["Main Page","1","Some text","Some Page"], "accessRequest": "propertyedit"}]',
				'[{"name":"Property:STB_nary","values":["Main Page","1","Some text","Some Page"],"accessRequest":"propertyedit","relationExists":"true","accessGranted":"true","valuePageInfo":["exists","no page","no page","redlink"],"rangeCategories":[null,null,null,null],"relationSchema":["_wpg","_num","_str","_wpg"]}]'),
			array(
				'[{"name": "Property:STB_naryWithCat", "values": ["Main Page","1","Some text","Some Page"], "accessRequest": "propertyedit"}]',
				'[{"name":"Property:STB_naryWithCat","values":["Main Page","1","Some text","Some Page"],"accessRequest":"propertyedit","relationExists":"true","accessGranted":"true","valuePageInfo":["exists","no page","no page","redlink"],"rangeCategories":["Range1",null,null,"Range2"],"relationSchema":["_wpg","_num","_str","_wpg"]}]'),
						
		);	
	}
	
	/**
	 * 
	 * @param string $relations
	 * 		JSON encoded request for relation info
	 * @param string $expInfo
	 * 		Expected relation info
	 * 
	 * @dataProvider providerCorrectRelationInfoIsReturned
	 */
	public function testCorrectRelationInfoIsReturned($relations, $expInfo) {
		$relInfo = smwf_om_MultipleRelationInfo(json_encode($relations));
		$this->assertEquals($expInfo, $relInfo);
	}
}