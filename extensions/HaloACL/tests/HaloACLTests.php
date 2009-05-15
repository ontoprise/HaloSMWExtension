<?php
require_once 'PHPUnit/Framework.php';
 
require_once 'testcases/TestDatabase.php';
require_once 'testcases/TestParserFunctions.php';

class HaloACLTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('PHPUnit');
 
        $suite->addTestSuite("TestDatabase");
        $suite->addTestSuite("TestParserFunctions");
        
        return $suite;
    }
}
?>