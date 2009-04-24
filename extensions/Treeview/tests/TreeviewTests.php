<?php
require_once 'PHPUnit/Framework.php';
 
require_once 'testcases/TestGenerateTree.php';
 
class TreeviewTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('PHPUnit');
 
        $suite->addTestSuite("TestGenerateTree");
 
        return $suite;
    }
}
?>
