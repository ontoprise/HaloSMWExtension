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
 * Tests the parser functions of the TreeView extension
 * 
 * @author Thomas Schweitzer
 * Date: 02.12.2011
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the TreeView extension. It is not a valid entry point.\n" );
}

class TestTreeviewParserFunctionsSuite extends PHPUnit_Framework_TestSuite
{
	
	public static function suite() {
		
		$suite = new TestTreeviewParserFunctionsSuite();
		$suite->addTestSuite('TestTreeviewParserFunctionsBasics');
		return $suite;
	}
	
	protected function setUp() {
   	}
	
	protected function tearDown() {
	}
	
}

/**
 * This class tests the basic setup of the parser functions
 * 
 * @author thsc
 *
 */
class TestTreeviewParserFunctionsBasics extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = FALSE;

	/**
     * Checks if the parser functions are set up correctly.
     * 
     */
    public function testParserFunctionClass() {
    	
    	// Check if parser function class is defined
    	$this->assertTrue(class_exists('TVParserFunctions'), 
    	                  "The class 'TVParserFunctions' is not defined.");
    	
    }
    
    /**
     * Data provider for testTreeParserFunction
     */
    function providerForTreeParserFunction() {
    	return array(
    		// wikitext, expected html
#0 - Test a simple, flat tree	
    		array(
<<<TEXT
{{#tree:
*a
*b
}}
TEXT
, 
			array('TreeView_ID_0',
<<<JSON
{
	"data" : [
		{
			"data" : {
				"title": "a",
				"attr": {}
			}
		},
		{ 
			"data" : {
				"title": "b",
				"attr": {}
			} 
		}
	]
}
JSON
			),
    	),
#1 - Test a simple, hierarchical tree with    	
    		array(
<<<TEXT
{{#tree:
*a
**b
}}
TEXT
, 
			array('TreeView_ID_1',
<<<JSON
{
	"data" : [
		{
			"data" : {
				"title": "a",
				"attr": {}
			},
			"children" : [
				{ 
					"data" : {
						"title": "b",
						"attr": {}
					} 
   				}
			]
		}
	]
}
JSON
			),
    	),
# 2 - Test a more complex tree structure
    	array(
<<<TEXT
{{#tree:
* 1-a
** 2-a
** 2-b
*** 3-a
**** 4-a
*** 3-b
** 2-c
* 1-b
}}
TEXT
, 
			array('TreeView_ID_2',
<<<JSON
{
	"data" : [
		{
			"data" : {
				"title": "1-a",
				"attr": {}
			},
			"children" : [
				{
					"data" : {
						"title": "2-a",
						"attr": {}
					}
    			},
				{ 
					"data" : {
						"title": "2-b",
						"attr": {}
					},
					"children" : [
						{
							"data" : {
								"title": "3-a",
								"attr": {}
							},
							"children" : [
								{
									"data" : {
										"title": "4-a",
										"attr": {}
									}
    							}
							]
						},
						{
							"data" : {
								"title": "3-b",
								"attr": {}
							}
						}
					] 
    			},
				{ 
					"data" : {
						"title": "2-c",
						"attr": {}
					}
    			}
			]
		},
		{ 
			"data" : {
				"title": "1-b",
				"attr": {}
			}
    	}
	]
}
JSON
				),
			),
#3 - Error message for missing label on level 3
			array(
<<<TEXT
{{#tree:
* 1-a
** 2-a
**** 4-a
}}
TEXT
, 
			array('TreeView_ID_3',
<<<JSON
{
	"data" : [
		{
			"data" : {
				"title": "1-a",
				"attr": {}
			},
			"children" : [
				{
					"data" : {
						"title": "2-a",
						"attr": {}
					},
					"children" : [
						{
							"data" : {
								"title": "***Warning: Missing label for this tree level***",
								"attr": {}
							},
							"children" : [
								{ 
									"data" : {
										"title": "4-a",
										"attr": {}
									}
    							}
							]
						}
					]
    			}
			]
		}
	]
}
JSON
				),
			),
#4 - Test tree structure for the error that no tree was provided.			
    		array('{{#tree: no tree here}}',
    			array('TreeView_ID_4',
<<<JSON
{
	"data" : "*** Warning: No tree structure found! ***"
}
JSON
    			) 
    		),
#5 - Test tree nodes with characters used in JSON    
    		array(
<<<TEXT
{{#tree:
*a"b
**b[]c
}}
TEXT
, 
			array('TreeView_ID_5',
<<<JSON
{
	"data" : [
		{
			"data" : {
				"title": "a\"b",
				"attr": {}
			},
			"children" : [
				{ 
					"data" : {
						"title":"b[]c",
						"attr": {}
					} 
    			}
			]
		}
	]
}
JSON
			),
    	),
#6 - Test theme parameter 
    		array(
<<<TEXT
{{#tree:
|theme=classic
|*a
}}
TEXT
, 
			array('TreeView_ID_6',
<<<HTML
theme: 'classic'
HTML
			),
    	),
#7 - Test a link in the tree
    		array(
<<<TEXT
{{#tree:
*[[Main Page]]
*[[Main Page|Main]]
* [[Main Page]]
* [[Main Page|Main]]
*http://localhost/mediawiki/index.php/Main_Page
*https://localhost/mediawiki/index.php/Main_Page
* http://localhost/mediawiki/index.php/Main_Page  
* https://localhost/mediawiki/index.php/Main_Page 
* [http://localhost/mediawiki/index.php/Main_Page Main Page]
* [https://localhost/mediawiki/index.php/Main_Page Main Page]
}}
TEXT
, 
			array('TreeView_ID_7',
<<<JSON
{
	"data" : [
		{
			"data" : {
				"title": "Main Page",
				"attr": {"href":"http:\/\/localhost\/mediawiki\/index.php\/Main_Page"}
			}
		},
		{
			"data" : {
				"title": "Main",
				"attr": {"href":"http:\/\/localhost\/mediawiki\/index.php\/Main_Page"}
			}
		},
		{
			"data" : {
				"title": "Main Page",
				"attr": {"href":"http:\/\/localhost\/mediawiki\/index.php\/Main_Page"}
			}
		},
		{
			"data" : {
				"title": "Main",
				"attr": {"href":"http:\/\/localhost\/mediawiki\/index.php\/Main_Page"}
			}
		},
		{
			"data" : {
				"title": "http:\/\/localhost\/mediawiki\/index.php\/Main_Page",
				"attr": {"href":"http:\/\/localhost\/mediawiki\/index.php\/Main_Page"}
			}
		},
		{
			"data" : {
				"title": "https:\/\/localhost\/mediawiki\/index.php\/Main_Page",
				"attr": {"href":"https:\/\/localhost\/mediawiki\/index.php\/Main_Page"}
			}
		},
		{
			"data" : {
				"title": "http:\/\/localhost\/mediawiki\/index.php\/Main_Page",
				"attr": {"href":"http:\/\/localhost\/mediawiki\/index.php\/Main_Page"}
			}
		},
		{
			"data" : {
				"title": "https:\/\/localhost\/mediawiki\/index.php\/Main_Page",
				"attr": {"href":"https:\/\/localhost\/mediawiki\/index.php\/Main_Page"}
			}
		},
		{
			"data" : {
				"title": "Main Page",
				"attr": {"href":"http:\/\/localhost\/mediawiki\/index.php\/Main_Page"}
			}
		},
		{
			"data" : {
				"title": "Main Page",
				"attr": {"href":"https:\/\/localhost\/mediawiki\/index.php\/Main_Page"}
			}
		}
	]
}
JSON
						),
    	),
#8 - Test links with namespaces in the tree
    		array(
<<<TEXT
{{#tree:
*[[:Category:Foo]]
*[[:Category:Foo|Foo]]
*[[User:Bar]]
*[[User:Bar|Bar]]
}}
TEXT
, 
			array('TreeView_ID_8',
<<<JSON
{
	"data" : [
		{
			"data" : {
				"title": "Category:Foo",
				"attr": {"href":"http:\/\/localhost\/mediawiki\/index.php\/Category:Foo"}
			}
		},
		{
			"data" : {
				"title": "Foo",
				"attr": {"href":"http:\/\/localhost\/mediawiki\/index.php\/Category:Foo"}
			}
		},
		{
			"data" : {
				"title": "User:Bar",
				"attr": {"href":"http:\/\/localhost\/mediawiki\/index.php\/User:Bar"}
			}
		},
		{
			"data" : {
				"title": "Bar",
				"attr": {"href":"http:\/\/localhost\/mediawiki\/index.php\/User:Bar"}
			}
		}
	]
}
JSON
						),
    	),
    	
#9 - Test the special command generateTreeJSON() as label
    		array(
<<<TEXT
{{#tree:
*generateTreeJSON:{"data": {"title": "GenerateTree", "attr": {}	} }
}}
TEXT
, 
			array('TreeView_ID_9',
<<<JSON
{
	"data" : [
		{
			"data" : {
				"title": "GenerateTree",
				"attr": {}
			}
		}
	]
}
JSON
			),
    	),
    	
#10 - Test the parameter filter - Expect the filter field in JSON to be true
    		array(
<<<TEXT
{{#tree:
filter=true
}}
TEXT
, 
			array('TreeView_ID_10',
<<<JSON
filter:true
JSON
			),
    	),
    	
#11 - Test the parameter 'filter' - Expect the filter field in JSON to be false
    		array(
<<<TEXT
{{#tree:
filter=false
}}
TEXT
, 
			array('TreeView_ID_11',
<<<JSON
filter:false
JSON
			),
    	),
    	
#12 - Test the parameter 'width' - Expect the width to be set in a css style
    		array(
<<<TEXT
{{#tree:
width=123
}}
TEXT
, 
			array('TreeView_ID_12',
<<<JSON
style="width:123px"
JSON
			),
    	),
    	
#13 - Test the parameter 'height' - Expect the height to be set in a css style
    		array(
<<<TEXT
{{#tree:
height=456
}}
TEXT
, 
			array('TreeView_ID_13',
<<<JSON
style="height:456px"
JSON
			),
    	),
    	
#14 - Test the parameters 'width' and 'height' - Expect width and height to be set in a css style
    		array(
<<<TEXT
{{#tree:
width=123|
height=456
}}
TEXT
, 
			array('TreeView_ID_14',
<<<JSON
style="width:123px;height:456px"
JSON
			),
    	),
    	
    	
    	);
    }
    
    /**
     * Tests the parser function {{#tree: }}
     * 
     * @param {String} $wikiText
     * 		The wikitext that will be parsed
     * @param {array(String)} $expected
     * 		[0] => an ID that must appear in the generated HTML
     * 		[1] => JSON or HTML code the must appear
     * 
     * @dataProvider providerForTreeParserFunction
     */
    public function testTreeParserFunction($wikiText, $expected) {
		$popts = new ParserOptions();
		$parser = new Parser();
		$title = Title::newFromText("NormalPage");
		$po = $parser->parse($wikiText, $title, $popts );
		$html = $po->getText();
    	
		$treeID = 'id="'.$expected[0].'"';
		$jsonID = "id:'".$expected[0]."'";
		$json   = $expected[1];
		$json = preg_replace("/\s/", "", $json);
		$html = preg_replace("/\s/", "", $html);
		$this->assertContains($treeID, $html, "The HTML for the tree does not contain an the ID $treeID");
		$this->assertContains($jsonID, $html, "The JSON for the tree does not contain an the ID $jsonID");
		$this->assertContains($json, $html, "Could not find the expected JSON for the tree.");
    }
    
    /**
     * Data provider for testGenerateTreeParserFunction
     */
    function providerForGenerateTreeParserFunction() {
    	return array(
    		// wikitext, expected html
#0 - Test generateTree with the parameters property and root
    		array(
<<<TEXT
{{#generateTree:
property=someProperty
|rootlabel=Root Node
}}
TEXT
, 
<<<JSON
generateTreeJSON:
{
	"data": {
		"title": "Root Node",
		"attr": { "generateTree": true, "property": "someProperty" }
	}
}
JSON
		),
		
    	);
    }
    
    /**
     * Tests the parser function {{#generateTree: }}
     * 
     * @dataProvider providerForGenerateTreeParserFunction
     */
    public function testGenerateTreeParserFunction($wikiText, $expected) {
		$popts = new ParserOptions();
		$parser = new Parser();
		$title = Title::newFromText("NormalPage");
		$po = $parser->parse($wikiText, $title, $popts );
		$html = $po->getText();
    	
		$expected = preg_replace("/\s/", "", $expected);
		$html = preg_replace("/\s/", "", $html);
		$this->assertContains($expected, $html, "Could not find the expected JSON for the tree.");
    }
    
    /**
     * Data provider for testTreeAndGenerateTreeParserFunction
     */
    function providerForTreeAndGenerateTreeParserFunction() {
    	return array(
    		// wikitext, expected html
#0 - Test a simple tree with generateTree	
    		array(
<<<TEXT
{{#tree:
*{{#generateTree:
property=someProperty
|rootlabel=Root Node
}}
}}
TEXT
, 
<<<JSON
{
	"data" : [
		{
			"data": {
				"title": "Root Node",
				"attr": { "generateTree": true, "property": "someProperty" }
			}
		}
	]
}
JSON
		),
#1 - Test a simple tree with generateTree with solrQuery parameter
    		array(
<<<TEXT
{{#tree:
*{{#generateTree:
property=someProperty
|solrquery=q=smwh_search_field%3A(%2Bfoo*%20)
|rootlabel=Root Node
}}
}}
TEXT
, 
<<<JSON
{
	"data" : [
		{
			"data": {
				"title": "Root Node",
				"attr": { "generateTree": true,
						  "solrQuery": "q=smwh_search_field%3A(%2Bfoo*%20)", 
						  "property": "someProperty" }
			}
		}
	]
}
JSON
		),
		
    	);
    }
    
    /**
     * Tests the parser function {{#tree:}} and {{#generateTree: }}
     * 
     * @dataProvider providerForTreeAndGenerateTreeParserFunction
     */
    public function testTreeAndGenerateTreeParserFunction($wikiText, $expected) {
		$popts = new ParserOptions();
		$parser = new Parser();
		$title = Title::newFromText("NormalPage");
		$po = $parser->parse($wikiText, $title, $popts );
		$html = $po->getText();
    	
		$expected = preg_replace("/\s/", "", $expected);
		$html = preg_replace("/\s/", "", $html);
		$this->assertContains($expected, $html, "Could not find the expected JSON for the tree.");
    }
    
    
}

