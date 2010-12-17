<?php
/**
 * @file
 * @ingroup SMWHaloTests
 *
 * Tests the external query interface which redirects the queries to the TSC.
 * 
 * @author Kai Kühn
 *
 */
require_once('TestEQI.php');
class TestTSCEQI extends TestEQI {


    function setUp() {
        $this->params = array('source' => 'tsc'); // change target for queries
    }

    function tearDown() {

    }
    
 
    function testSPARQL() {
        $res = $this->makeCall("SELECT ?p WHERE { ?p rdf:type category:Person. }", $this->params);
        echo $res;

        $this->assertContains('<uri>http://publicbuild/ob/a/Hans</uri>', $res);
        $this->assertContains('<uri>http://publicbuild/ob/a/Kai</uri>', $res);
    }
}