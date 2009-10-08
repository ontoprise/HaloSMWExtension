<?php
require_once 'PHPUnit/Framework.php';
 
require_once 'testcases/testparser-wiki2html.php';

class TreeviewTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('PHPUnit');
 
        $suite->addTestSuite("TestParserWiki2Html");
        
        return $suite;
    }
}
?>