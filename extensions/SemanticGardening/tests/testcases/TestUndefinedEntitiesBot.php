<?php
class TestUndefinedEntitiesBot extends PHPUnit_Framework_TestCase {
    var $saveGlobals = array();

    function setUp() {
         exec('runBots smw_undefinedentitiesbot');
    }

    function tearDown() {
         
    }

    function testSomething() {
       
        $this->assertEquals(true, true);
    }

   
    
}
?>