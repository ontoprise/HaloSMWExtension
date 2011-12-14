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
[[has type::String| ]]
ARTICLE
	,
	//------------------------------------------------------------------------------
	"Property:STB_page" =>
<<<ARTICLE
[[has domain and range::; | ]]
[[has type::Page| ]]
ARTICLE
	,
	//------------------------------------------------------------------------------
	"Property:STB_pageWithCat" =>
<<<ARTICLE
[[has domain and range::Category:Domain;Category:Range1| ]]
[[has type::Page| ]]
ARTICLE
	,
	//------------------------------------------------------------------------------
	"Property:ofState" =>
<<<ARTICLE
[[has domain and range::;Category:State| ]]
[[has type::Page| ]]
ARTICLE
	,
	//------------------------------------------------------------------------------
	"Property:from" =>
<<<ARTICLE
[[has type::Number| ]]
ARTICLE
	,
	//------------------------------------------------------------------------------
	"Property:to" =>
<<<ARTICLE
[[has type::Number| ]]
ARTICLE
	,
	//------------------------------------------------------------------------------
	"Property:withVicePresident" =>
<<<ARTICLE
[[has domain and range::;Category:Person| ]]
[[has type::Page| ]]
ARTICLE
	,
	//------------------------------------------------------------------------------
	"Property:isPresident" =>
<<<ARTICLE
[[has domain and range::Category:Person; | ]]
[[has type::Record| ]]
[[has fields::ofState;from;to;withVicePresident| ]]
ARTICLE
	,
	//------------------------------------------------------------------------------
	"Property:isChancellor" =>
<<<ARTICLE
[[has domain and range::Category:Person; | ]]
[[has type::Record| ]]
[[has fields::ofState;from;to;withViceChancellor| ]]
ARTICLE
	,
	//------------------------------------------------------------------------------
	"USA" =>
<<<ARTICLE
[[Category:State]]
ARTICLE
	,

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
				'[{"name":"Property:NonExisting","values":["test"],"accessRequest":"propertyedit","relationExists":"false","accessGranted":"true","valuePageInfo":["redlink"],"rangeCategories":[null],"relationSchema":["_wpg"],"recordProperties":[]}]'),
		array(
				'[{"name": "Property:STB_string", "values": ["test"], "accessRequest": "propertyedit"}]',
				'[{"name":"Property:STB_string","values":["test"],"accessRequest":"propertyedit","relationExists":"true","accessGranted":"true","valuePageInfo":["no page"],"rangeCategories":[null],"relationSchema":["_str"],"recordProperties":[]}]'),
		array(
				'[{"name": "Property:STB_page", "values": ["Non existing page"], "accessRequest": "propertyedit"}]',
				'[{"name":"Property:STB_page","values":["Non existing page"],"accessRequest":"propertyedit","relationExists":"true","accessGranted":"true","valuePageInfo":["redlink"],"rangeCategories":[null],"relationSchema":["_wpg"],"recordProperties":[]}]'),
		array(
				'[{"name": "Property:STB_page", "values": ["Main Page"], "accessRequest": "propertyedit"}]',
				'[{"name":"Property:STB_page","values":["Main Page"],"accessRequest":"propertyedit","relationExists":"true","accessGranted":"true","valuePageInfo":["exists"],"rangeCategories":[null],"relationSchema":["_wpg"],"recordProperties":[]}]'),
		array(
				'[{"name": "Property:STB_pageWithCat", "values": ["Main Page"], "accessRequest": "propertyedit"}]',
				'[{"name":"Property:STB_pageWithCat","values":["Main Page"],"accessRequest":"propertyedit","relationExists":"true","accessGranted":"true","valuePageInfo":["exists"],"rangeCategories":["Range1"],"relationSchema":["_wpg"],"recordProperties":[]}]'),
		array(
				'[{"name": "Property:isPresident", "values": ["USA","1961","1969","Lyndon B. Johnson"], "accessRequest": "propertyedit"}]',
				'[{"name":"Property:isPresident","values":["USA","1961","1969","Lyndon B. Johnson"],"accessRequest":"propertyedit","relationExists":"true","accessGranted":"true","valuePageInfo":["exists","no page","no page","redlink"],"rangeCategories":["State",null,null,"Person"],"relationSchema":["_wpg","_num","_num","_wpg"],"recordProperties":["OfState","true","From","true","To","true","WithVicePresident","true"]}]'),
		array(
				'[{"name": "Property:isChancellor", "values": ["SomeState","1961","1969","Herr Sowieso"], "accessRequest": "propertyedit"}]',
				'[{"name":"Property:isChancellor","values":["SomeState","1961","1969","Herr Sowieso"],"accessRequest":"propertyedit","relationExists":"true","accessGranted":"true","valuePageInfo":["redlink","no page","no page","redlink"],"rangeCategories":["State",null,null,null],"relationSchema":["_wpg","_num","_num","_wpg"],"recordProperties":["OfState","true","From","true","To","true","WithViceChancellor","false"]}]'),

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
