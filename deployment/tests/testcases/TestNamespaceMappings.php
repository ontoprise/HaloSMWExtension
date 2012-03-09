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

require_once ('deployment/io/DF_BundleTools.php');
require_once ('deployment/io/import/DF_OntologyMerger.php');
/**
 * Tests the NamespaceMappings tool
 *
 */
class TestNamespaceMappings extends PHPUnit_Framework_TestCase {

	 protected $backupGlobals = FALSE;

	function setUp() {

	}

	function tearDown() {

	}

	function testParseNamespaceMappings() {
		$text = <<<ENDS
    	
*foaf: http://foaf.namespace
mywiki: http://mywiki

*category: http://category:wiki/test

ENDS
		;
		 
		$namespaceMappings = DFBundleTools::parseRegisteredPrefixes($text);
		$this->assertEquals($namespaceMappings['foaf'], 'http://foaf.namespace');
		//$this->assertEquals($namespaceMappings['mywiki'], 'http://mywiki');
		$this->assertEquals($namespaceMappings['category'], 'http://category:wiki/test');
	}
	
	
function testParseNamespaceMappings2() {
        $text = <<<ENDS
        
<!-- BEGIN ontology: testID -->

*foaf : http://foaf.namespace
*category : http://category:wiki/test
<!-- END ontology: testID -->

ENDS
        ;
         
        $namespaceMappings = DFBundleTools::parseRegisteredPrefixes($text);
        $this->assertEquals($namespaceMappings['foaf'], 'http://foaf.namespace');
        $this->assertEquals($namespaceMappings['category'], 'http://category:wiki/test');
        $this->assertEquals(count($namespaceMappings), 2);
    }
    
	function testLoadAndStoreNamespaceMappings() {
		$text = <<<ENDS
        
*foaf: http://foaf.namespace
mywiki: http://mywiki

*category: http://category:wiki/test

ENDS
		;
		 
		$namespaceMappings = DFBundleTools::parseRegisteredPrefixes($text);
		print_r($namespaceMappings);
		DFBundleTools::storeRegisteredPrefixes($namespaceMappings, "testID");
		$namespaceMappings = DFBundleTools::getRegisteredPrefixes();

		$this->assertEquals($namespaceMappings['foaf'], 'http://foaf.namespace');
		//$this->assertEquals($namespaceMappings['mywiki'], 'http://mywiki');
		$this->assertEquals($namespaceMappings['category'], 'http://category:wiki/test');
	}
}
