<?php
require_once 'PHPUnit/Framework.php';

require_once 'testcases/TestWSUpdateBot.php';
require_once 'testcases/TestWSCacheBot.php';
require_once 'testcases/TestWSManagement.php';
require_once 'testcases/TestWSUsage.php';
require_once 'testcases/TestJSONProcessor.php';


class DataImportTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('DataImport');

        // add test suites
        $suite->addTestSuite("TestWSUpdateBot");
        $suite->addTestSuite("TestWSCacheBot");
        $suite->addTestSuite("TestWSManagement");
        $suite->addTestSuite("TestWSUsage");        
        $suite->addTestSuite("TestJSONProcessor");

        return $suite;
    }
}
?>