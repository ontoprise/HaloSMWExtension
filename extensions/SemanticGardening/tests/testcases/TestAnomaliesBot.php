<?php
class TestAnomaliesBot extends PHPUnit_Framework_TestCase {
    var $saveGlobals = array();

    function setUp() {
         exec('runBots smw_anomaliesbot -p "CATEGORY_NUMBER_ANOMALY=Check%20number%20of%20sub%20categories,CATEGORY_LEAF_ANOMALY=Check%20for%20category%20leafs,CATEGORY_RESTRICTION="');
    }

    function tearDown() {
         
    }

    function testSomething() {
       
        $this->assertEquals(true, true);
    }

   
    
}
?>