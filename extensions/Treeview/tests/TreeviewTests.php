<?php

require_once 'testcases/TestGenerateTree.php';
require_once 'testcases/TestGenerateTreeDynamic.php';
require_once 'testcases/TestGenerateTreeAjaxCalls.php'; 

class TreeviewTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('PHPUnit');
 
        $suite->addTestSuite("TestGenerateTree");
        $suite->addTestSuite("TestGenerateTreeDynamic");
 		$suite->addTestSuite("TestGenerateTreeAjaxCalls");
 		
        return $suite;
    }
}