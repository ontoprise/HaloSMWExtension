<?php
/**
 * @file
 * @ingroup LinkedData_Tests
 */

require_once 'PHPUnit/Framework.php';
 
require_once 'testcases/TestLODSourceDefinition.php';
require_once 'testcases/TestTripleStoreAccess.php';
require_once 'testcases/TestMapping.php';

class LODTests
{ 
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('PHPUnit');
        
        $suite->addTestSuite("TestLODSourceDefinition");
        $suite->addTestSuite("TestTripleStoreAccess");
        $suite->addTestSuite("TestMapping");
        
        return $suite;
    }
}