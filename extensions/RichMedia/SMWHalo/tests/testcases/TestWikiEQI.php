<?php
/**
 * @file
 * @ingroup SMWHaloTests
 *
 * Tests the external query interface provided by the Wiki itself.
 * 
 * @author Kai Kühn
 *
 */
require_once('TestEQI.php');
class TestWikiEQI extends TestEQI {


    function setUp() {
        $this->params = array();
    }

    function tearDown() {

    }
   
}