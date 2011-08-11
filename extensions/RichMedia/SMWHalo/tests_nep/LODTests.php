<?php
/**
 * @file
 * @ingroup LinkedData_Tests
 */


require_once 'testcases/TestNonExistingPageHandler.php';


class LODTests
{ 
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('PHPUnit');

// IMPORTANT!!
// Ontobroker Quad must be started with the following options:
//   msgbroker=none client=MyStore driver=ontobroker-quad wsport=8090 console 
//   reasoner=owl restfulws        
        
     
        $suite->addTestSuite("TestNonExistinPageSuite");
    
        
        return $suite;
    }
}