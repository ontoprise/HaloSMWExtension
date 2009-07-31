<?php
require_once 'PHPUnit/Framework.php';

class RichMediaTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('RichMedia');

        // add test suites
        $suite->addTestSuite("TestOneClickUpload");

        return $suite;
    }
}
?>