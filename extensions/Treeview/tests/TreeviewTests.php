<?php
require_once 'PHPUnit/Framework.php';
 
require_once 'testcases/TestGenerateTree.php';
require_once 'testcases/TestGenerateTreeDynamic.php';
 
class TreeviewTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('PHPUnit');
 
        $suite->addTestSuite("TestGenerateTree");
        $suite->addTestSuite("TestGenerateTreeDynamic");
 
        return $suite;
    }
}
?>
