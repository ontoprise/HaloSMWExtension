<?php
require_once 'PHPUnit/Framework.php';

require_once 'testcases/TestWSUpdateBot.php';


class DataImportTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('DataImport');

        // add test suites
        $suite->addTestSuite("TestWSUpdateBot");
                

        return $suite;
    }
}
?>