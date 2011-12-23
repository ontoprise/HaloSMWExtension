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
 * @file
 * @ingroup TreeView
 *
 * Tests the basic setup of the TreeView extension
 * 
 * @author Thomas Schweitzer
 * Date: 02.12.2011
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the TreeView extension. It is not a valid entry point.\n" );
}
//TODO Create a correct setup of the Selenium 2 webdriver and make the path to it configurable
require_once 'D:\MediaWiki\Selenium2WebDriver\php-webdriver\__init__.php';

class TestTreeviewSeleniumSuite extends PHPUnit_Framework_TestSuite
{
	
	public static function suite() {
		
		$suite = new TestTreeviewSeleniumSuite();
		$suite->addTestSuite('TestTreeviewSelenium');
		return $suite;
	}
	
	protected function setUp() {
   	}
	
	protected function tearDown() {
	}
	
}

/**
 * This class tests the basic setup of the treeview extension
 * For further information on the php-webdriver see:
 * https://github.com/facebook/php-webdriver
 * 
 * For information on the JsonWireProtocol see:
 * http://code.google.com/p/selenium/wiki/JsonWireProtocol
 * 
 * @author thsc
 *
 */
class TestTreeviewSelenium extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = FALSE;
	
	private static $mSession;

	/**
	 */
    public static function setUpBeforeClass() {
		// This would be the url of the host running the server-standalone.jar
		$wd_host = 'http://localhost:4444/wd/hub'; // this is the default
		$web_driver = new WebDriver($wd_host);
		
		// First param to session() is the 'browserName' (default = 'firefox')
		// Second param is a JSON object of additional 'desiredCapabilities'
		
		// POST /session
		self::$mSession = $web_driver->session('firefox');
		self::$mSession->timeouts()->implicit_wait(array('ms' => 3000));
		
    }

    /**
     */
    public static function tearDownAfterClass() {
    	self::$mSession->close();
    }
	
    
    /**
     * Data provider for testParserFunctionGenerator
     */
    function providerParserFunctionGenerator() {
    	return array(
    		// search text input, property name,  expected wikitext
    		array('Grimm', 'Subsection of', "{{#tree:\n*{{#generateTree:\nproperty=Subsection of\n|rootlabel=Enter name of root node here\n|solrquery=q=smwh_search_field%3A(%2Bgrimm*%20) \n}}\n}}"),
    		array('', 'Creator', "{{#tree:\n*{{#generateTree:\nproperty=Creator\n|rootlabel=Enter name of root node here\n|solrquery=q=smwh_search_field%3A(%2B*%20)\n}}\n}}"),
    	);
    }
    
	/**
     * Checks if user input in the search field is correctly translated into the
     * parser function text.
     * @dataProvider providerParserFunctionGenerator
     */
    public function testParserFunctionGenerator($search, $property, $expected) {
		self::$mSession->open('http://localhost/mediawiki/index.php/Main_Page');
    	
    	$this->el('mw-searchButton')->click();

    	sleep(3);
		$this->el('tv_define_tree_link')->click();
		
		$this->type('query',$search);
		
		$this->el('treeViewProperty')->clear();
		$this->type('treeViewProperty', $property);
		
		$this->el('treeViewPropertyButton')->click();
		
		$this->el('tv_show_parser_function')->click();
		
		$wikiText = $this->el('treeViewParserFunction')->text();
		
		$expected = preg_replace("/\s/", "", $expected);
		$wikiText = preg_replace("/\s/", "", $wikiText);
		
		$this->assertEquals($expected, $wikiText);
		
    }
    
    private function el($id) {
    	return self::$mSession->element('id', $id);
    }
    
    private function type($id, $text) {
    	$this->el($id)->value(array('value' => str_split($text)));
    }
    
}

