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
 * @ingroup SMWHaloTests
 *
 * @author Kai K�hn
 *
 */

class TestQueryPrintersSuite extends PHPUnit_Framework_TestSuite
{

	public static function suite() {
		$suite = new TestQueryPrintersSuite();
		$suite->addTestSuite('TestQueryPrinters');
		$suite->addTestSuite('TestFancyTableQuery');
		$suite->addTestSuite('TestFancyTableDistributionQuery');
		return $suite;
	}

	protected function setUp() {
		Skin::getSkinNames();
	}

	protected function tearDown() {
	}

}

class TestQueryPrinters extends PHPUnit_Framework_TestCase {


	function setUp() {

	}

	function tearDown() {

	}

	function testXMLQueryPrinter() {
		global $smwgResultFormats, $smwgHaloIP;
		require_once "$smwgHaloIP/includes/queryprinters/SMW_QP_XML.php";
		$smwgResultFormats['xml'] = 'SMWXMLResultPrinter';
		$params = array();
		$context = SMWQueryProcessor::INLINE_QUERY;
		$format = "xml";
		$extraprintouts = array();
		$querystring = "[[Category:Car]]";
		$query  = SMWQueryProcessor::createQuery($querystring, $params, $context, $format, $extraprintouts);
		$res = smwfGetStore()->getQueryResult($query);
		$result = SMWQueryProcessor::getResultFromQuery($query, $params, $extraprintouts, SMW_OUTPUT_FILE, $context, $format);
		$this->assertFileContentsIgnoringWhitespaces("$smwgHaloIP/tests/testcases/resources/xml_qp_result.dat", $result);

	}

	function testExcelQueryPrinter() {
		global $smwgHaloIP;
		$params = array();
		$context = SMWQueryProcessor::INLINE_QUERY;
		$format = "exceltable";
		$extraprintouts = array();
		$querystring = "[[Category:Car]]";
		$query  = SMWQueryProcessor::createQuery($querystring, $params, $context, $format, $extraprintouts);
		$res = smwfGetStore()->getQueryResult($query);
		$result = SMWQueryProcessor::getResultFromQuery($query, $params, $extraprintouts, SMW_OUTPUT_FILE, $context, $format);

		$this->assertFileContentsIgnoringWhitespaces("$smwgHaloIP/tests/testcases/resources/excel_qp_result.dat", $result);

	}

	function assertFileContentsIgnoringWhitespaces($file, $act_result) {
		$contents = file_get_contents($file);
		$contents = preg_replace("/\\s+|\\n+|\\r+/", "", $contents);
		$act_result = preg_replace("/\\s+|\\n+|\\r+/", "", $act_result);
		$this->assertEquals($contents, $act_result);
	}
}

/**
 * This class tests the LableTableResultPrinter.
 * It needs the pages: LTRPPage1, LTRPPage2, LTRPPage3, LTRPQuery, LTRPNumber
 * LTRPPage and LTRPString
 * The TSC must be running.
 *
 * @author thsc
 *
 */
class TestFancyTableQuery extends PHPUnit_Framework_TestCase {


	function setUp() {
		define('SMWH_FORCE_TS_UPDATE', 1); // We are running in maintenance mode
		// which normally disables the TripleStore

		global $smwgHaloTripleStoreGraph, $IP, $smwgHaloIP,$smwgHaloNEPEnabled;

		$smwgHaloNEPEnabled=true;

		include_once "$IP/extensions/LinkedData/storage/TripleStore/LOD_TripleStoreAccess.php";
		include_once "$IP/extensions/LinkedData/storage/TripleStore/LOD_Triple.php";
		include_once "$IP/extensions/LinkedData/storage/TripleStore/LOD_SparqlQueryResult.php";

		$hitchhiker = "obl:term#%3Chttp://www.NewOnto1.org/dbOntology%23c%3E(HitchhikersGuide)";
		$lord = "obl:term#%3Chttp://www.NewOnto1.org/dbOntology%23c%3E(LordOfTheRings)";
		$adams = "obl:term#%3Chttp://www.NewOnto1.org/dbOntology%23c%3E(Douglas%20Adams)";
		$tolkien = "obl:term#%3Chttp://www.NewOnto1.org/dbOntology%23c%3E(J.R.R%20Tolkien)";

		$mTriples = array(
		array($hitchhiker, "prop:Title", "The Hitchhiker's Guide to the Galaxy", "xsd:string"),
		array($hitchhiker, "prop:Price", "10.20", "xsd:double"),
		array($hitchhiker, "prop:Pages", "224", "xsd:int"),
		array($hitchhiker, "prop:ReallyCool", "true", "xsd:boolean"),
		array($hitchhiker, "prop:Published", "1979-04-02T13:41:09+01:00", "xsd:dateTime"),
		array($hitchhiker, "prop:Amazon", "http://www.amazon.com/Hitchhikers-Guide-Galaxy-25th-Anniversary/dp/1400052920/ref=sr_1_1?ie=UTF8&s=books&qid=1272987287&sr=1-1", "xsd:anyURI"),
		array($hitchhiker, "prop:Author", $adams, "__objectURI"),
		array($hitchhiker, "prop:AuthorName", "Douglas Adams", "xsd:string"),

		array($lord, "prop:Title", "The Lord of the Rings", "xsd:string"),
		array($lord, "prop:Price", "12.19", "xsd:double"),
		array($lord, "prop:Pages", "1178", "xsd:int"),
		array($lord, "prop:ReallyCool", "true", "xsd:boolean"),
		array($lord, "prop:Published", "2005-10-12T14:54:09+01:00", "xsd:dateTime"),
		array($lord, "prop:Amazon", "http://www.amazon.com/Lord-Rings-50th-Anniversary-Vol/dp/0618640150/", "xsd:anyURI"),
		array($lord, "prop:Author", $tolkien, "__objectURI"),
		array($lord, "prop:AuthorName", "J.R.R. Tolkien", "xsd:string"),
			
		array($adams, "prop:FirstName", "Douglas", "xsd:string"),
		array($adams, "prop:LastName", "Adams", "xsd:string"),
			
		array($tolkien, "prop:FirstName", "John", "xsd:string"),
		array($tolkien, "prop:LastName", "Tolkien", "xsd:string"),
			
		);
		$prefixes = TSNamespaces::getW3CPrefixes()
		.TSNamespaces::getAllPrefixes();
		$triples = array();
		foreach ($mTriples as $t) {
			$triples[] = new LODTriple($t[0], $t[1], $t[2], $t[3]);
		}

		// Inserts triples into the triple store
		$tsa = new LODTripleStoreAccess();
		$tsa->addPrefixes($prefixes);
		$tsa->createGraph($smwgHaloTripleStoreGraph);
		$tsa->insertTriples($smwgHaloTripleStoreGraph, $triples);
		$tsa->flushCommands();
	}

	function tearDown() {
	}


	/**
	 * Tests if the printer is registered
	 */
	function testPrinterRegistered() {
		global $smwgResultFormats;
		$this->assertArrayHasKey('fancytable', $smwgResultFormats);
		$this->assertEquals('SMWFancyTableResultPrinter', $smwgResultFormats['fancytable']);
	}

	/**
	 * Tests if the class for the label table printer can be instantiated.
	 */
	function testFancyTablePrinterClassPresent() {
		try {
			$inst = new SMWFancyTableResultPrinter('fancytable', true);
		} catch (Exception $e) {
			$this->fail("Class SMWFancyTableResultPrinter does not exist.");
		}
		$this->assertTrue(true);
	}


	/**
	 * Data provider for testFancyTablePrinterResult
	 */
	function providerForFancyTablePrinterResult() {
		global $wgScriptPath;
		return array(
		#0
		//--- A normal query ---
		array(array(
					'[[author::+]]',
					'?title',
					'?price',
					'?pages',
    				'?reallyCool',
    				'?published',
    				'?amazon',
					'?author',	
					'format=fancytable'
					),
<<<TABLE
<table class="smwtable" id="querytable0">
	<tr>
		<th></th>
		<th>[[:Property:Title|Title]]</th>
		<th class="numericsort">[[:Property:Price|Price]]</th>
		<th class="numericsort">[[:Property:Pages|Pages]]</th>
		<th class="numericsort">[[:Property:ReallyCool|ReallyCool]]</th>
		<th class="numericsort">[[:Property:Published|Published]]</th>
		<th>[[:Property:Amazon|Amazon]]</th>
		<th>[[:Property:Author|Author]]</th>
	</tr>
	<tr>
		<td><ilink label="C(HitchhikersGuide)_8136fa64c0e6a157e87d992e53358f80" wikititle="C(HitchhikersGuide)_8136fa64c0e6a157e87d992e53358f80">http://localhost$wgScriptPath/index.php/C%28HitchhikersGuide%29_8136fa64c0e6a157e87d992e53358f80?action=edit&uri=obl%3Aterm%23%253Chttp%3A%2F%2Fwww.NewOnto1.org%2FdbOntology%2523c%253E%28HitchhikersGuide%29&redlink=1</ilink></td>
		<td>The Hitchhiker's Guide to the Galaxy</td>
		<td><span class="smwsortkey">10.2</span>10.2</td>
		<td><span class="smwsortkey">224</span>224</td>
		<td><span class="smwsortkey">1</span>true</td>
		<td><span class="smwsortkey">2443966.0702431</span>2 April 1979 13:41:09</td>
		<td>[http://www.amazon.com/Hitchhikers-Guide-Galaxy-25th-Anniversary/dp/1400052920/ref=sr_1_1?ie=UTF8&s=books&qid=1272987287&sr=1-1 http://www.amazon.com/Hitchhikers-Guide-Galaxy-25th-Anniversary/dp/1400052920/ref=sr_1_1?ie=UTF8&s=books&qid=1272987287&sr=1-1]</td>
		<td><ilink label="C(Douglas_Adams)_b3cc4c5b6fabeabeadadeb3f402f4664" wikititle="C(Douglas_Adams)_b3cc4c5b6fabeabeadadeb3f402f4664">http://localhost$wgScriptPath/index.php/C%28Douglas_Adams%29_b3cc4c5b6fabeabeadadeb3f402f4664?action=edit&uri=obl%3Aterm%23%253Chttp%3A%2F%2Fwww.NewOnto1.org%2FdbOntology%2523c%253E%28Douglas%2520Adams%29&redlink=1</ilink></td>
	</tr>
	<tr>
		<td><ilink label="C(LordOfTheRings)_5732c051f377c01107c32ac5794f60af" wikititle="C(LordOfTheRings)_5732c051f377c01107c32ac5794f60af">http://localhost$wgScriptPath/index.php/C%28LordOfTheRings%29_5732c051f377c01107c32ac5794f60af?action=edit&uri=obl%3Aterm%23%253Chttp%3A%2F%2Fwww.NewOnto1.org%2FdbOntology%2523c%253E%28LordOfTheRings%29&redlink=1</ilink></td>
		<td>The Lord of the Rings</td>
		<td><span class="smwsortkey">12.19</span>12.19</td>
		<td><span class="smwsortkey">1178</span>1,178</td>
		<td><span class="smwsortkey">1</span>true</td>
		<td><span class="smwsortkey">2453656.1209375</span>12 October 2005 14:54:09</td>
		<td>[http://www.amazon.com/Lord-Rings-50th-Anniversary-Vol/dp/0618640150/ http://www.amazon.com/Lord-Rings-50th-Anniversary-Vol/dp/0618640150/]</td>
		<td><ilink label="C(J.R.R_Tolkien)_777be031e54e1fb8b8d542f2dc072079" wikititle="C(J.R.R_Tolkien)_777be031e54e1fb8b8d542f2dc072079">http://localhost$wgScriptPath/index.php/C%28J.R.R_Tolkien%29_777be031e54e1fb8b8d542f2dc072079?action=edit&uri=obl%3Aterm%23%253Chttp%3A%2F%2Fwww.NewOnto1.org%2FdbOntology%2523c%253E%28J.R.R%2520Tolkien%29&redlink=1</ilink></td>
	</tr>
</table>
TABLE
					),
					#1
					//--- Assigning a new label for the subject column ---
					array(array(
					'[[author::+]]',
					'mainlabel=Book',
					'?title',
					'?price',
					'?pages',
    				'?reallyCool',
    				'?published',
    				'?amazon',
					'?author',	
					'format=fancytable'
					),
<<<TABLE
<table class="smwtable" id="querytable0">
	<tr>
		<th>Book</th>
		<th>[[:Property:Title|Title]]</th>
		<th class="numericsort">[[:Property:Price|Price]]</th>
		<th class="numericsort">[[:Property:Pages|Pages]]</th>
		<th class="numericsort">[[:Property:ReallyCool|ReallyCool]]</th>
		<th class="numericsort">[[:Property:Published|Published]]</th>
		<th>[[:Property:Amazon|Amazon]]</th>
		<th>[[:Property:Author|Author]]</th>
	</tr>
	<tr>
		<td><ilink label="C(HitchhikersGuide)_8136fa64c0e6a157e87d992e53358f80" wikititle="C(HitchhikersGuide)_8136fa64c0e6a157e87d992e53358f80">http://localhost$wgScriptPath/index.php/C%28HitchhikersGuide%29_8136fa64c0e6a157e87d992e53358f80?action=edit&uri=obl%3Aterm%23%253Chttp%3A%2F%2Fwww.NewOnto1.org%2FdbOntology%2523c%253E%28HitchhikersGuide%29&redlink=1</ilink></td>
		<td>The Hitchhiker's Guide to the Galaxy</td>
		<td><span class="smwsortkey">10.2</span>10.2</td>
		<td><span class="smwsortkey">224</span>224</td>
		<td><span class="smwsortkey">1</span>true</td>
		<td><span class="smwsortkey">2443966.0702431</span>2 April 1979 13:41:09</td>
		<td>[http://www.amazon.com/Hitchhikers-Guide-Galaxy-25th-Anniversary/dp/1400052920/ref=sr_1_1?ie=UTF8&s=books&qid=1272987287&sr=1-1 http://www.amazon.com/Hitchhikers-Guide-Galaxy-25th-Anniversary/dp/1400052920/ref=sr_1_1?ie=UTF8&s=books&qid=1272987287&sr=1-1]</td>
		<td><ilink label="C(Douglas_Adams)_b3cc4c5b6fabeabeadadeb3f402f4664" wikititle="C(Douglas_Adams)_b3cc4c5b6fabeabeadadeb3f402f4664">http://localhost$wgScriptPath/index.php/C%28Douglas_Adams%29_b3cc4c5b6fabeabeadadeb3f402f4664?action=edit&uri=obl%3Aterm%23%253Chttp%3A%2F%2Fwww.NewOnto1.org%2FdbOntology%2523c%253E%28Douglas%2520Adams%29&redlink=1</ilink></td>
	</tr>
	<tr>
		<td><ilink label="C(LordOfTheRings)_5732c051f377c01107c32ac5794f60af" wikititle="C(LordOfTheRings)_5732c051f377c01107c32ac5794f60af">http://localhost$wgScriptPath/index.php/C%28LordOfTheRings%29_5732c051f377c01107c32ac5794f60af?action=edit&uri=obl%3Aterm%23%253Chttp%3A%2F%2Fwww.NewOnto1.org%2FdbOntology%2523c%253E%28LordOfTheRings%29&redlink=1</ilink></td>
		<td>The Lord of the Rings</td>
		<td><span class="smwsortkey">12.19</span>12.19</td>
		<td><span class="smwsortkey">1178</span>1,178</td>
		<td><span class="smwsortkey">1</span>true</td>
		<td><span class="smwsortkey">2453656.1209375</span>12 October 2005 14:54:09</td>
		<td>[http://www.amazon.com/Lord-Rings-50th-Anniversary-Vol/dp/0618640150/ http://www.amazon.com/Lord-Rings-50th-Anniversary-Vol/dp/0618640150/]</td>
		<td><ilink label="C(J.R.R_Tolkien)_777be031e54e1fb8b8d542f2dc072079" wikititle="C(J.R.R_Tolkien)_777be031e54e1fb8b8d542f2dc072079">http://localhost$wgScriptPath/index.php/C%28J.R.R_Tolkien%29_777be031e54e1fb8b8d542f2dc072079?action=edit&uri=obl%3Aterm%23%253Chttp%3A%2F%2Fwww.NewOnto1.org%2FdbOntology%2523c%253E%28J.R.R%2520Tolkien%29&redlink=1</ilink></td>
	</tr>
</table>
TABLE
					),
					#2
					//--- Assigning a new label for the subject column and replace the subject values and author values ---
					array(array(
					'[[author::+]]',
					'mainlabel=Book',
					'?title',
					'?price',
					'?pages',
    				'?reallyCool',
    				'?published',
    				'?amazon',
					'?author',	
					'?AuthorName',	
					'format=fancytable',
    			    'replace(?)=?title',
    			    'replace(?author)=?AuthorName'
    			    ),
<<<TABLE
<table class="smwtable" id="querytable0">
	<tr>
		<th>Book</th>
		<th class="numericsort">[[:Property:Price|Price]]</th>
		<th class="numericsort">[[:Property:Pages|Pages]]</th>
		<th class="numericsort">[[:Property:ReallyCool|ReallyCool]]</th>
		<th class="numericsort">[[:Property:Published|Published]]</th>
		<th>[[:Property:Amazon|Amazon]]</th>
		<th>[[:Property:Author|Author]]</th>
	</tr>
	<tr>
		<td><ilink label="The Hitchhiker&#039;s Guide to the Galaxy" wikititle="C(HitchhikersGuide)_8136fa64c0e6a157e87d992e53358f80">http://localhost$wgScriptPath/index.php/C%28HitchhikersGuide%29_8136fa64c0e6a157e87d992e53358f80?action=edit&uri=obl%3Aterm%23%253Chttp%3A%2F%2Fwww.NewOnto1.org%2FdbOntology%2523c%253E%28HitchhikersGuide%29&redlink=1</ilink></td>
		<td><span class="smwsortkey">10.2</span>10.2</td>
		<td><span class="smwsortkey">224</span>224</td>
		<td><span class="smwsortkey">1</span>true</td>
		<td><span class="smwsortkey">2443966.0702431</span>2 April 1979 13:41:09</td>
		<td>[http://www.amazon.com/Hitchhikers-Guide-Galaxy-25th-Anniversary/dp/1400052920/ref=sr_1_1?ie=UTF8&s=books&qid=1272987287&sr=1-1 http://www.amazon.com/Hitchhikers-Guide-Galaxy-25th-Anniversary/dp/1400052920/ref=sr_1_1?ie=UTF8&s=books&qid=1272987287&sr=1-1]</td>
		<td><ilink label="Douglas Adams" wikititle="C(Douglas_Adams)_b3cc4c5b6fabeabeadadeb3f402f4664">http://localhost$wgScriptPath/index.php/C%28Douglas_Adams%29_b3cc4c5b6fabeabeadadeb3f402f4664?action=edit&uri=obl%3Aterm%23%253Chttp%3A%2F%2Fwww.NewOnto1.org%2FdbOntology%2523c%253E%28Douglas%2520Adams%29&redlink=1</ilink></td>
	</tr>
	<tr>
		<td><ilink label="The Lord of the Rings" wikititle="C(LordOfTheRings)_5732c051f377c01107c32ac5794f60af">http://localhost$wgScriptPath/index.php/C%28LordOfTheRings%29_5732c051f377c01107c32ac5794f60af?action=edit&uri=obl%3Aterm%23%253Chttp%3A%2F%2Fwww.NewOnto1.org%2FdbOntology%2523c%253E%28LordOfTheRings%29&redlink=1</ilink></td>
		<td><span class="smwsortkey">12.19</span>12.19</td>
		<td><span class="smwsortkey">1178</span>1,178</td>
		<td><span class="smwsortkey">1</span>true</td>
		<td><span class="smwsortkey">2453656.1209375</span>12 October 2005 14:54:09</td>
		<td>[http://www.amazon.com/Lord-Rings-50th-Anniversary-Vol/dp/0618640150/ http://www.amazon.com/Lord-Rings-50th-Anniversary-Vol/dp/0618640150/]</td>
		<td><ilink label="J.R.R. Tolkien" wikititle="C(J.R.R_Tolkien)_777be031e54e1fb8b8d542f2dc072079">http://localhost$wgScriptPath/index.php/C%28J.R.R_Tolkien%29_777be031e54e1fb8b8d542f2dc072079?action=edit&uri=obl%3Aterm%23%253Chttp%3A%2F%2Fwww.NewOnto1.org%2FdbOntology%2523c%253E%28J.R.R%2520Tolkien%29&redlink=1</ilink></td>
	</tr>
</table>
TABLE
    			    ),
    			    #3
    			    //--- Assigning a new label for the subject column do some invalid replacements.
    			    //--- This leads to a warning at the end of the table.
    			    array(array(
					'[[author::+]]',
					'mainlabel=Book',
					'?title',
					'?price',
					'?pages',
    				'?reallyCool',
    				'?published',
    				'?amazon',
					'?author',	
					'format=fancytable',
    			    'replace(?)=?xtitle',
    			    'replace(?xauthor)=?xAuthorName',
    			    'replace(?xtitle)=?title',
    			    'replace(?author)=?xAuthorName',
				    'replace(?price)=?pages'
				    ),
<<<TABLE
<table class="smwtable" id="querytable0">
	<tr>
		<th>Book</th>
		<th>[[:Property:Title|Title]]</th>
		<th class="numericsort">[[:Property:Price|Price]]</th>
		<th class="numericsort">[[:Property:ReallyCool|ReallyCool]]</th>
		<th class="numericsort">[[:Property:Published|Published]]</th>
		<th>[[:Property:Amazon|Amazon]]</th>
		<th>[[:Property:Author|Author]]</th>
	</tr>
	<tr>
		<td><ilink label="C(HitchhikersGuide)_8136fa64c0e6a157e87d992e53358f80" wikititle="C(HitchhikersGuide)_8136fa64c0e6a157e87d992e53358f80">http://localhost$wgScriptPath/index.php/C%28HitchhikersGuide%29_8136fa64c0e6a157e87d992e53358f80?action=edit&uri=obl%3Aterm%23%253Chttp%3A%2F%2Fwww.NewOnto1.org%2FdbOntology%2523c%253E%28HitchhikersGuide%29&redlink=1</ilink></td>
		<td>The Hitchhiker's Guide to the Galaxy</td>
		<td><span class="smwsortkey">10.2</span>224</td>
		<td><span class="smwsortkey">1</span>true</td>
		<td><span class="smwsortkey">2443966.0702431</span>2 April 1979 13:41:09</td>
		<td>[http://www.amazon.com/Hitchhikers-Guide-Galaxy-25th-Anniversary/dp/1400052920/ref=sr_1_1?ie=UTF8&s=books&qid=1272987287&sr=1-1 http://www.amazon.com/Hitchhikers-Guide-Galaxy-25th-Anniversary/dp/1400052920/ref=sr_1_1?ie=UTF8&s=books&qid=1272987287&sr=1-1]</td>
		<td><ilink label="C(Douglas_Adams)_b3cc4c5b6fabeabeadadeb3f402f4664" wikititle="C(Douglas_Adams)_b3cc4c5b6fabeabeadadeb3f402f4664">http://localhost$wgScriptPath/index.php/C%28Douglas_Adams%29_b3cc4c5b6fabeabeadadeb3f402f4664?action=edit&uri=obl%3Aterm%23%253Chttp%3A%2F%2Fwww.NewOnto1.org%2FdbOntology%2523c%253E%28Douglas%2520Adams%29&redlink=1</ilink></td>
	</tr>
	<tr>
		<td><ilink label="C(LordOfTheRings)_5732c051f377c01107c32ac5794f60af" wikititle="C(LordOfTheRings)_5732c051f377c01107c32ac5794f60af">http://localhost$wgScriptPath/index.php/C%28LordOfTheRings%29_5732c051f377c01107c32ac5794f60af?action=edit&uri=obl%3Aterm%23%253Chttp%3A%2F%2Fwww.NewOnto1.org%2FdbOntology%2523c%253E%28LordOfTheRings%29&redlink=1</ilink></td>
		<td>The Lord of the Rings</td>
		<td><span class="smwsortkey">12.19</span>1,178</td>
		<td><span class="smwsortkey">1</span>true</td>
		<td><span class="smwsortkey">2453656.1209375</span>12 October 2005 14:54:09</td>
		<td>[http://www.amazon.com/Lord-Rings-50th-Anniversary-Vol/dp/0618640150/ http://www.amazon.com/Lord-Rings-50th-Anniversary-Vol/dp/0618640150/]</td>
		<td><ilink label="C(J.R.R_Tolkien)_777be031e54e1fb8b8d542f2dc072079" wikititle="C(J.R.R_Tolkien)_777be031e54e1fb8b8d542f2dc072079">http://localhost$wgScriptPath/index.php/C%28J.R.R_Tolkien%29_777be031e54e1fb8b8d542f2dc072079?action=edit&uri=obl%3Aterm%23%253Chttp%3A%2F%2Fwww.NewOnto1.org%2FdbOntology%2523c%253E%28J.R.R%2520Tolkien%29&redlink=1</ilink></td>
	</tr>
</table>
<div>
Found invalid replace statements with unknown properties:
<ul>
<li>replace(?)=?<b>Xtitle</b>
<li>replace(?<b>xauthor</b>)=?<b>XAuthorName</b>
<li>replace(?<b>xtitle</b>)=?Title
<li>replace(?author)=?<b>XAuthorName</b>
</ul>
</div>
TABLE
				    ),

				    #4
				    //--- Replace the price by pages ---
				    array(array(
					'[[author::+]]',
					'mainlabel=Book',
					'?title',
					'?price',
					'?pages',
    				'?reallyCool',
    				'?published',
    				'?amazon',
					'?author',	
					'format=fancytable',
    			    'replace(?price)=?pages'
    			    ),
<<<TABLE
<table class="smwtable" id="querytable0">
	<tr>
		<th>Book</th>
		<th>[[:Property:Title|Title]]</th>
		<th class="numericsort">[[:Property:Price|Price]]</th>
		<th class="numericsort">[[:Property:ReallyCool|ReallyCool]]</th>
		<th class="numericsort">[[:Property:Published|Published]]</th>
		<th>[[:Property:Amazon|Amazon]]</th>
		<th>[[:Property:Author|Author]]</th>
	</tr>
	<tr>
		<td><ilink label="C(HitchhikersGuide)_8136fa64c0e6a157e87d992e53358f80" wikititle="C(HitchhikersGuide)_8136fa64c0e6a157e87d992e53358f80">http://localhost$wgScriptPath/index.php/C%28HitchhikersGuide%29_8136fa64c0e6a157e87d992e53358f80?action=edit&uri=obl%3Aterm%23%253Chttp%3A%2F%2Fwww.NewOnto1.org%2FdbOntology%2523c%253E%28HitchhikersGuide%29&redlink=1</ilink></td>
		<td>The Hitchhiker's Guide to the Galaxy</td>
		<td><span class="smwsortkey">10.2</span>224</td>
		<td><span class="smwsortkey">1</span>true</td>
		<td><span class="smwsortkey">2443966.0702431</span>2 April 1979 13:41:09</td>
		<td>[http://www.amazon.com/Hitchhikers-Guide-Galaxy-25th-Anniversary/dp/1400052920/ref=sr_1_1?ie=UTF8&s=books&qid=1272987287&sr=1-1 http://www.amazon.com/Hitchhikers-Guide-Galaxy-25th-Anniversary/dp/1400052920/ref=sr_1_1?ie=UTF8&s=books&qid=1272987287&sr=1-1]</td>
		<td><ilink label="C(Douglas_Adams)_b3cc4c5b6fabeabeadadeb3f402f4664" wikititle="C(Douglas_Adams)_b3cc4c5b6fabeabeadadeb3f402f4664">http://localhost$wgScriptPath/index.php/C%28Douglas_Adams%29_b3cc4c5b6fabeabeadadeb3f402f4664?action=edit&uri=obl%3Aterm%23%253Chttp%3A%2F%2Fwww.NewOnto1.org%2FdbOntology%2523c%253E%28Douglas%2520Adams%29&redlink=1</ilink></td>
	</tr>
	<tr>
		<td><ilink label="C(LordOfTheRings)_5732c051f377c01107c32ac5794f60af" wikititle="C(LordOfTheRings)_5732c051f377c01107c32ac5794f60af">http://localhost$wgScriptPath/index.php/C%28LordOfTheRings%29_5732c051f377c01107c32ac5794f60af?action=edit&uri=obl%3Aterm%23%253Chttp%3A%2F%2Fwww.NewOnto1.org%2FdbOntology%2523c%253E%28LordOfTheRings%29&redlink=1</ilink></td>
		<td>The Lord of the Rings</td>
		<td><span class="smwsortkey">12.19</span>1,178</td>
		<td><span class="smwsortkey">1</span>true</td>
		<td><span class="smwsortkey">2453656.1209375</span>12 October 2005 14:54:09</td>
		<td>[http://www.amazon.com/Lord-Rings-50th-Anniversary-Vol/dp/0618640150/ http://www.amazon.com/Lord-Rings-50th-Anniversary-Vol/dp/0618640150/]</td>
		<td><ilink label="C(J.R.R_Tolkien)_777be031e54e1fb8b8d542f2dc072079" wikititle="C(J.R.R_Tolkien)_777be031e54e1fb8b8d542f2dc072079">http://localhost$wgScriptPath/index.php/C%28J.R.R_Tolkien%29_777be031e54e1fb8b8d542f2dc072079?action=edit&uri=obl%3Aterm%23%253Chttp%3A%2F%2Fwww.NewOnto1.org%2FdbOntology%2523c%253E%28J.R.R%2520Tolkien%29&redlink=1</ilink></td>
	</tr>
</table>
TABLE
    			    ),
    			    #5
    			    //--- A query for wiki pages. ---
    			    array(array(
					'[[LTRPString::+]]',
					'?LTRPString',
					'?LTRPNumber',
					'?LTRPPage',
					'format=fancytable'
					),
<<<TABLE
<table class="smwtable" id="querytable0">
	<tr>
		<th></th>
		<th>[[:Property:LTRPString|LTRPString]]</th>
		<th class="numericsort">[[:Property:LTRPNumber|LTRPNumber]]</th>
		<th>[[:Property:LTRPPage|LTRPPage]]</th>
	</tr>
	<tr>
		<td>[[:LTRPPage1|LTRPPage1]]</td>
		<td>This is page 1</td>
		<td><span class="smwsortkey">1</span>1</td>
		<td>[[:LTRPPage1|LTRPPage1]]</td>
	</tr>
	<tr>
		<td>[[:LTRPPage2|LTRPPage2]]</td>
		<td>This is page 2</td>
		<td><span class="smwsortkey">2</span>2</td>
		<td>[[:LTRPPage2|LTRPPage2]]</td>
	</tr>
	<tr>
		<td>[[:LTRPPage3|LTRPPage3]]</td>
		<td>This is page 3</td>
		<td><span class="smwsortkey">3</span>3</td>
		<td>[[:LTRPPage3|LTRPPage3]]</td>
	</tr>
</table>
TABLE
					),
					#6
					//--- A query for wiki pages with replacement of the subject by a string ---
					array(array(
					'[[LTRPString::+]]',
					'?LTRPString',
					'?LTRPNumber',
					'?LTRPPage',
					'format=fancytable',
					'replace(?)=?LTRPString'
					),
<<<TABLE
<table class="smwtable" id="querytable0">
	<tr>
		<th></th>
		<th class="numericsort">[[:Property:LTRPNumber|LTRPNumber]]</th>
		<th>[[:Property:LTRPPage|LTRPPage]]</th>
	</tr>
	<tr>
		<td>[[:LTRPPage1|This is page 1]]</td>
		<td><span class="smwsortkey">1</span>1</td>
		<td>[[:LTRPPage1|LTRPPage1]]</td>
	</tr>
	<tr>
		<td>[[:LTRPPage2|This is page 2]]</td>
		<td><span class="smwsortkey">2</span>2</td>
		<td>[[:LTRPPage2|LTRPPage2]]</td>
	</tr>
	<tr>
		<td>[[:LTRPPage3|This is page 3]]</td>
		<td><span class="smwsortkey">3</span>3</td>
		<td>[[:LTRPPage3|LTRPPage3]]</td>
	</tr>
</table>
TABLE
					),
					#7
					//--- A query for wiki pages with replacement of the subject by a Number ---
					array(array(
					'[[LTRPString::+]]',
					'?LTRPString',
					'?LTRPNumber',
					'?LTRPPage',
					'format=fancytable',
					'replace(?)=?LTRPNumber'
					),
<<<TABLE
<table class="smwtable" id="querytable0">
	<tr>
		<th></th>
		<th>[[:Property:LTRPString|LTRPString]]</th>
		<th>[[:Property:LTRPPage|LTRPPage]]</th>
	</tr>
	<tr>
		<td>[[:LTRPPage1|1]]</td>
		<td>This is page 1</td>
		<td>[[:LTRPPage1|LTRPPage1]]</td>
	</tr>
	<tr>
		<td>[[:LTRPPage2|2]]</td>
		<td>This is page 2</td>
		<td>[[:LTRPPage2|LTRPPage2]]</td>
	</tr>
	<tr>
		<td>[[:LTRPPage3|3]]</td>
		<td>This is page 3</td>
		<td>[[:LTRPPage3|LTRPPage3]]</td>
	</tr>
</table>
TABLE
					),
					#8
					//--- A query for wiki pages with replacement of the subject by a Page ---
					array(array(
					'[[LTRPString::+]]',
					'?LTRPString',
					'?LTRPNumber',
					'?LTRPPage',
					'format=fancytable',
					'replace(?)=?LTRPPage'
					),
<<<TABLE
<table class="smwtable" id="querytable0">
	<tr>
		<th></th>
		<th>[[:Property:LTRPString|LTRPString]]</th>
		<th class="numericsort">[[:Property:LTRPNumber|LTRPNumber]]</th>
	</tr>
	<tr>
		<td>[[:LTRPPage1|LTRPPage1]]</td>
		<td>This is page 1</td>
		<td><span class="smwsortkey">1</span>1</td>
	</tr>
	<tr>
		<td>[[:LTRPPage2|LTRPPage2]]</td>
		<td>This is page 2</td>
		<td><span class="smwsortkey">2</span>2</td>
	</tr>
	<tr>
		<td>[[:LTRPPage3|LTRPPage3]]</td>
		<td>This is page 3</td>
		<td><span class="smwsortkey">3</span>3</td>
	</tr>
</table>
TABLE
					),
					#9
					//--- A query for wiki pages. Replace the subject by a number and a string by a page---
					array(array(
					'[[LTRPString::+]]',
					'?LTRPString',
					'?LTRPNumber',
					'?LTRPPage',
					'replace(?)=?LTRPNumber',
					'replace(?LTRPString)=?LTRPPage',
					'format=fancytable'
					),
<<<TABLE
<table class="smwtable" id="querytable0">
	<tr>
		<th></th>
		<th>[[:Property:LTRPString|LTRPString]]</th>
	</tr>
	<tr>
		<td>[[:LTRPPage1|1]]</td>
		<td>LTRPPage1</td>
	</tr>
	<tr>
		<td>[[:LTRPPage2|2]]</td>
		<td>LTRPPage2</td>
	</tr>
	<tr>
		<td>[[:LTRPPage3|3]]</td>
		<td>LTRPPage3</td>
	</tr>
</table>
TABLE
					),
					#10
					//--- A normal query with 'style' parameter ---
					array(array(
					'[[author::+]]',
					'?title',
					'format=fancytable',
	    			'style=fancystyle'
	    			),
<<<TABLE
<table class="fancystyle" id="querytable0">
	<tr>
		<th></th>
		<th>[[:Property:Title|Title]]</th>
	</tr>
	<tr>
		<td><ilink label="C(HitchhikersGuide)_8136fa64c0e6a157e87d992e53358f80" wikititle="C(HitchhikersGuide)_8136fa64c0e6a157e87d992e53358f80">http://localhost$wgScriptPath/index.php/C%28HitchhikersGuide%29_8136fa64c0e6a157e87d992e53358f80?action=edit&uri=obl%3Aterm%23%253Chttp%3A%2F%2Fwww.NewOnto1.org%2FdbOntology%2523c%253E%28HitchhikersGuide%29&redlink=1</ilink></td>
		<td>The Hitchhiker's Guide to the Galaxy</td>
	</tr>
	<tr>
		<td><ilink label="C(LordOfTheRings)_5732c051f377c01107c32ac5794f60af" wikititle="C(LordOfTheRings)_5732c051f377c01107c32ac5794f60af">http://localhost$wgScriptPath/index.php/C%28LordOfTheRings%29_5732c051f377c01107c32ac5794f60af?action=edit&uri=obl%3Aterm%23%253Chttp%3A%2F%2Fwww.NewOnto1.org%2FdbOntology%2523c%253E%28LordOfTheRings%29&redlink=1</ilink></td>
		<td>The Lord of the Rings</td>
	</tr>
</table>
TABLE
	    			),
	    			#11
	    			//--- A query with a property path i.e. author.FirstName as replacement
	    			array(array(
				'[[author::+]]',
				'mainlabel=Author',
				'?author.FirstName',
				'?author.LastName',
				'?AuthorName',
				'replace(?)=?author.FirstName',
				'format=fancytable'
				),
<<<TABLE
<table class="smwtable" id="querytable0">
	<tr>
		<th>Author</th>
		<th>[[:Property:LastName|LastName]]</th>
		<th>[[:Property:AuthorName|AuthorName]]</th>
	</tr>
	<tr>
		<td><ilink label="Douglas" wikititle="C(HitchhikersGuide)_8136fa64c0e6a157e87d992e53358f80">http://localhost$wgScriptPath/index.php/C%28HitchhikersGuide%29_8136fa64c0e6a157e87d992e53358f80?action=edit&uri=obl%3Aterm%23%253Chttp%3A%2F%2Fwww.NewOnto1.org%2FdbOntology%2523c%253E%28HitchhikersGuide%29&redlink=1</ilink></td>
		<td>Adams</td>
		<td>Douglas Adams</td>
	</tr>
	<tr>
		<td><ilink label="John" wikititle="C(LordOfTheRings)_5732c051f377c01107c32ac5794f60af">http://localhost$wgScriptPath/index.php/C%28LordOfTheRings%29_5732c051f377c01107c32ac5794f60af?action=edit&uri=obl%3Aterm%23%253Chttp%3A%2F%2Fwww.NewOnto1.org%2FdbOntology%2523c%253E%28LordOfTheRings%29&redlink=1</ilink></td>
		<td>Tolkien</td>
		<td>J.R.R. Tolkien</td>
	</tr>
</table>			
TABLE
				)
				);
	}

	/**
	 * Tests the correct output of the label table printer.
	 *
	 * @dataProvider providerForFancyTablePrinterResult
	 *
	 */
	function testFancyTablePrinterResult($query, $expResult) {

		$actualResult = SMWQueryProcessor::getResultFromFunctionParams( $query, SMW_OUTPUT_WIKI );
		// Remove whitespaces for comparison
		$actualResult = preg_replace("/\s*/", "", $actualResult);
		$expResult = preg_replace("/\s*/", "", $expResult);

		$this->assertEquals($expResult, $actualResult);

	}
}

/**
 * This class tests the distribution feature of the FancyTableResultPrinter.
 *
 * @author thsc
 *
 */
class TestFancyTableDistributionQuery extends PHPUnit_Framework_TestCase {

	private static $mArticleManager;

	public static function setUpBeforeClass() {
		SMWHaloCommon::createUsers(array("U1"));
		 
		self::$mArticleManager = new ArticleManager();
		self::$mArticleManager->importArticles(__DIR__."/resources/FancyTableDistributionTestArticlesDump.xml");
		// Existing articles are not overwritten => do the import twice
		self::$mArticleManager->deleteArticles("U1");
		self::$mArticleManager->importArticles(__DIR__."/resources/FancyTableDistributionTestArticlesDump.xml");
	}

	public static function tearDownAfterClass() {
		// Temporarily disabled for speeding up tests
		self::$mArticleManager->deleteArticles("U1");
	}

	protected function setUp() {
	}

	protected function tearDown() {

	}

	/**
	 * Data provider for testFancyTablePrinterResult
	 */
	function providerForFancyTableDistribution() {

		return array(
		#0 --- A normal query without distribution ---
		array(array(
					'[[Category:City]]',
					'[[Located In::+]]',
					'?Located In',
					'format=fancytable'
					),
<<<TABLE
<table class="smwtable" id="querytable0">
	<tr>
		<th></th>
		<th>[[:Property:Located In|Located In]]</th>
	</tr>
	<tr>
		<td>[[:Baltimore|Baltimore]]</td>
		<td>[[:USA|USA]]</td>
	</tr>
	<tr>
		<td>[[:Berlin|Berlin]]</td>
		<td>[[:Germany|Germany]]</td>
	</tr>
	<tr>
		<td>[[:Bern|Bern]]</td>
		<td>[[:Switzerland|Switzerland]]</td>
	</tr>
	<tr>
		<td>[[:Boston|Boston]]</td>
		<td>[[:USA|USA]]</td>
	</tr>
	<tr>
		<td>[[:Bremen|Bremen]]</td>
		<td>[[:Germany|Germany]]</td>
	</tr>
	<tr>
		<td>[[:Graz|Graz]]</td>
		<td>[[:Austria|Austria]]</td>
	</tr>
	<tr>
		<td>[[:Hamburg|Hamburg]]</td>
		<td>[[:Germany|Germany]]</td>
	</tr>
	<tr>
		<td>[[:Linz|Linz]]</td>
		<td>[[:Austria|Austria]]</td>
	</tr>
	<tr>
		<td>[[:Stuttgart|Stuttgart]]</td>
		<td>[[:Germany|Germany]]</td>
	</tr>
	<tr>
		<td>[[:Vienna|Vienna]]</td>
		<td>[[:Austria|Austria]]</td>
	</tr>
</table>

TABLE
					),
					#1 --- A normal query without the subject column ---
					array(array(
					'[[Category:City]]',
					'[[Located In::+]]',
					'?Located In',
					'mainlabel=-',
					'format=fancytable'
					),
<<<TABLE
<table class="smwtable" id="querytable0">
	<tr>
		<th>[[:Property:Located In|Located In]]</th>
	</tr>
	<tr>
		<td>[[:USA|USA]]</td>
	</tr>
	<tr>
		<td>[[:Germany|Germany]]</td>
	</tr>
	<tr>
		<td>[[:Switzerland|Switzerland]]</td>
	</tr>
	<tr>
		<td>[[:USA|USA]]</td>
	</tr>
	<tr>
		<td>[[:Germany|Germany]]</td>
	</tr>
	<tr>
		<td>[[:Austria|Austria]]</td>
	</tr>
	<tr>
		<td>[[:Germany|Germany]]</td>
	</tr>
	<tr>
		<td>[[:Austria|Austria]]</td>
	</tr>
	<tr>
		<td>[[:Germany|Germany]]</td>
	</tr>
	<tr>
		<td>[[:Austria|Austria]]</td>
	</tr>
</table>
TABLE
					),
					#2 --- A distribution query without the subject column ---
					array(array(
					'[[Category:City]]',
					'[[Located In::+]]',
					'?Located In',
					'mainlabel=-',
					'format=fancytable',
					'distribution=on'
					),
<<<TABLE
<table class="smwtable" id="querytable0">
	<tr>
		<th>[[:Property:Located In|Located In]]</th>
		<th></th>
	</tr>
	<tr>
		<td>[[:USA|USA]]</td>
		<td>2</td>
	</tr>
	<tr>
		<td>[[:Germany|Germany]]</td>
		<td>4</td>
	</tr>
	<tr>
		<td>[[:Switzerland|Switzerland]]</td>
		<td>1</td>
	</tr>
	<tr>
		<td>[[:Austria|Austria]]</td>
		<td>3</td>
	</tr>
</table>
TABLE
					),
					#3 --- A distribution query without the subject column, with title for the distribution column ---
					array(array(
					'[[Category:City]]',
					'[[Located In::+]]',
					'?Located In',
					'mainlabel=-',
					'format=fancytable',
					'distribution=on',
    			  	'distributiontitle=Occurrences'
    			  	),
<<<TABLE
<table class="smwtable" id="querytable0">
	<tr>
		<th>[[:Property:Located In|Located In]]</th>
		<th>Occurrences</th>
	</tr>
	<tr>
		<td>[[:USA|USA]]</td>
		<td>2</td>
	</tr>
	<tr>
		<td>[[:Germany|Germany]]</td>
		<td>4</td>
	</tr>
	<tr>
		<td>[[:Switzerland|Switzerland]]</td>
		<td>1</td>
	</tr>
	<tr>
		<td>[[:Austria|Austria]]</td>
		<td>3</td>
	</tr>
</table>
TABLE
    			  	),
    			  	#4 --- A distribution query without the subject column, with title for the
    			  	#      distribution column, with two columns ---
    			  	array(array(
					'[[Category:City]]',
					'[[Located In::+]]',
					'?Located In',
    			    '?NorthOf50DegreesN',
					'mainlabel=-',
					'format=fancytable',
					'distribution=on',
    			  	'distributiontitle=Occurrences'
    			  	),
<<<TABLE
<table class="smwtable" id="querytable0">
	<tr>
		<th>[[:Property:Located In|Located In]]</th>
		<th>[[:Property:NorthOf50DegreesN|NorthOf50DegreesN]]</th>
		<th>Occurrences</th>
	</tr>
	<tr>
		<td>[[:USA|USA]]</td>
		<td>[[:False|False]]</td>
		<td>2</td>
	</tr>
	<tr>
		<td>[[:Germany|Germany]]</td>
		<td>[[:True|True]]</td>
		<td>3</td>
	</tr>
	<tr>
		<td>[[:Switzerland|Switzerland]]</td>
		<td>[[:False|False]]</td>
		<td>1</td>
	</tr>
	<tr>
		<td>[[:Austria|Austria]]</td>
		<td>[[:False|False]]</td>
		<td>3</td>
	</tr>
	<tr>
		<td>[[:Germany|Germany]]</td>
		<td>[[:False|False]]</td>
		<td>1</td>
	</tr>
</table>
TABLE
    			  	),
    			  	#5 --- A distribution query without the subject column, with title for the
    			  	#      distribution column, with two columns, order ascending ---
    			  	array(array(
					'[[Category:City]]',
					'[[Located In::+]]',
					'?Located In',
    			    '?NorthOf50DegreesN',
					'mainlabel=-',
					'format=fancytable',
					'distribution=on',
					'distributionsort=asc',
    			  	'distributiontitle=Occurrences'
    			  	),
<<<TABLE
<table class="smwtable" id="querytable0">
	<tr>
		<th>[[:Property:Located In|Located In]]</th>
		<th>[[:Property:NorthOf50DegreesN|NorthOf50DegreesN]]</th>
		<th>Occurrences</th>
	</tr>
	<tr>
		<td>[[:Germany|Germany]]</td>
		<td>[[:False|False]]</td>
		<td>1</td>
	</tr>
	<tr>
		<td>[[:Switzerland|Switzerland]]</td>
		<td>[[:False|False]]</td>
		<td>1</td>
	</tr>
	<tr>
		<td>[[:USA|USA]]</td>
		<td>[[:False|False]]</td>
		<td>2</td>
	</tr>
	<tr>
		<td>[[:Germany|Germany]]</td>
		<td>[[:True|True]]</td>
		<td>3</td>
	</tr>
	<tr>
		<td>[[:Austria|Austria]]</td>
		<td>[[:False|False]]</td>
		<td>3</td>
	</tr>
</table>
TABLE
    			  	),
    			  	#6 --- A distribution query without the subject column, with title for the
    			  	#      distribution column, with two columns, order descending ---
    			  	array(array(
					'[[Category:City]]',
					'[[Located In::+]]',
					'?Located In',
    			    '?NorthOf50DegreesN',
					'mainlabel=-',
					'format=fancytable',
					'distribution=on',
					'distributionsort=desc',
    			  	'distributiontitle=Occurrences'
    			  	),
<<<TABLE
<table class="smwtable" id="querytable0">
	<tr>
		<th>[[:Property:Located In|Located In]]</th>
		<th>[[:Property:NorthOf50DegreesN|NorthOf50DegreesN]]</th>
		<th>Occurrences</th>
	</tr>
	<tr>
		<td>[[:Austria|Austria]]</td>
		<td>[[:False|False]]</td>
		<td>3</td>
	</tr>
	<tr>
		<td>[[:Germany|Germany]]</td>
		<td>[[:True|True]]</td>
		<td>3</td>
	</tr>
	<tr>
		<td>[[:USA|USA]]</td>
		<td>[[:False|False]]</td>
		<td>2</td>
	</tr>
	<tr>
		<td>[[:Germany|Germany]]</td>
		<td>[[:False|False]]</td>
		<td>1</td>
	</tr>
	<tr>
		<td>[[:Switzerland|Switzerland]]</td>
		<td>[[:False|False]]</td>
		<td>1</td>
	</tr>
</table>
TABLE
    			  	),
    			  	#7 --- A distribution query without the subject column, with title for the
    			  	#      distribution column, with two columns, limit = 2 ---
    			  	array(array(
					'[[Category:City]]',
					'[[Located In::+]]',
					'?Located In',
    			    '?NorthOf50DegreesN',
					'mainlabel=-',
					'format=fancytable',
					'distribution=on',
					'distributionlimit=2',
    			  	'distributiontitle=Occurrences'
    			  	),
<<<TABLE
<table class="smwtable" id="querytable0">
	<tr>
		<th>[[:Property:Located In|Located In]]</th>
		<th>[[:Property:NorthOf50DegreesN|NorthOf50DegreesN]]</th>
		<th>Occurrences</th>
	</tr>
	<tr>
		<td>[[:USA|USA]]</td>
		<td>[[:False|False]]</td>
		<td>2</td>
	</tr>
	<tr>
		<td>[[:Germany|Germany]]</td>
		<td>[[:True|True]]</td>
		<td>3</td>
	</tr>
</table>
TABLE
    			  	),
    			  	#8 --- A distribution query without the subject column, with title for the
    			  	#      distribution column, with two columns, order ascending, limit=2 ---
    			  	array(array(
					'[[Category:City]]',
					'[[Located In::+]]',
					'?Located In',
    			    '?NorthOf50DegreesN',
					'mainlabel=-',
					'format=fancytable',
					'distribution=on',
					'distributionlimit=2',
    			    'distributionsort=asc',
    			  	'distributiontitle=Occurrences'
    			  	),
<<<TABLE
<table class="smwtable" id="querytable0">
	<tr>
		<th>[[:Property:Located In|Located In]]</th>
		<th>[[:Property:NorthOf50DegreesN|NorthOf50DegreesN]]</th>
		<th>Occurrences</th>
	</tr>
	<tr>
		<td>[[:Germany|Germany]]</td>
		<td>[[:False|False]]</td>
		<td>1</td>
	</tr>
	<tr>
		<td>[[:Switzerland|Switzerland]]</td>
		<td>[[:False|False]]</td>
		<td>1</td>
	</tr>
</table>
TABLE
    			  	),
    			  	);
	}

	/**
	 * Tests the correct output of the fancy table printer.
	 *
	 * @dataProvider providerForFancyTableDistribution
	 *
	 */
	function testFancyTableDistribution($query, $expResult) {
		$actualResult = SMWQueryProcessor::getResultFromFunctionParams( $query, SMW_OUTPUT_WIKI );
		//echo $actualResult;
		// Remove whitespaces for comparison
		$actualResult = preg_replace("/\s*/", "", $actualResult);
		$expResult = preg_replace("/\s*/", "", $expResult);

		$this->assertEquals($expResult, $actualResult);

	}
}