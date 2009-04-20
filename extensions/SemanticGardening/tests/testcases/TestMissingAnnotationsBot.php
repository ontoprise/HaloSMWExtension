<?php
class TestMissingAnnotationsBot extends PHPUnit_Framework_TestCase {
    var $saveGlobals = array();

    function setUp() {
         exec('runBots smw_missingannotationsbot -nolog -p "MA_PART_OF_NAME=,MA_CATEGORY_RESTRICTION="');
    }

    function tearDown() {
         
    }

    function testSomething() {
       
        $this->assertEquals(true, true);
    }

   
    
}
?>