<?php
require_once ('deployment/io/DF_BundleTools.php');
/**
 * Tests the NamespaceMappings tool
 *
 */
class TestNamespaceMappings extends PHPUnit_Framework_TestCase {

   

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
    	$this->assertEquals($namespaceMappings['mywiki'], 'http://mywiki');
    	$this->assertEquals($namespaceMappings['category'], 'http://category:wiki/test');
    }
    
  function testLoadAndStoreNamespaceMappings() {
        $text = <<<ENDS
        
*foaf: http://foaf.namespace
mywiki: http://mywiki

*category: http://category:wiki/test

ENDS
;
               
        $namespaceMappings = DFBundleTools::parseRegisteredPrefixes($text);
        DFBundleTools::storeRegisteredPrefixes($namespaceMappings);
        $namespaceMappings = DFBundleTools::getRegisteredPrefixes();
        
        $this->assertEquals($namespaceMappings['foaf'], 'http://foaf.namespace');
        $this->assertEquals($namespaceMappings['mywiki'], 'http://mywiki');
        $this->assertEquals($namespaceMappings['category'], 'http://category:wiki/test');
    }
}