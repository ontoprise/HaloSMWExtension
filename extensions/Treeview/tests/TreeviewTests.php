<?php
require_once 'PHPUnit/Framework.php';
 
require_once 'testcases/TestGenerateTree.php';
require_once 'testcases/TestGenerateTreeDynamic.php';
require_once 'testcases/TestGenerateTreeAjaxCalls.php'; 
require_once 'testcases/TestParserfunction.php';

class TreeviewTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('PHPUnit');
 
        $suite->addTestSuite("TestGenerateTree");
        $suite->addTestSuite("TestGenerateTreeDynamic");
 		$suite->addTestSuite("TestGenerateTreeAjaxCalls");
 		$suite->addTestSuite("TestParserfunction");
 		
        return $suite;
    }
}
?>
