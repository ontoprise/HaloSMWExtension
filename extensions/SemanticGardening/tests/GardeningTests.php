<?php
require_once 'PHPUnit/Framework.php';
 
require_once 'testcases/TestConsistencyBot.php';
require_once 'testcases/TestAnomaliesBot.php';
require_once 'testcases/TestMissingAnnotationsBot.php';
require_once 'testcases/TestUndefinedEntitiesBot.php';
require_once 'testcases/TestImportOntologyBot.php';
 
class GardeningTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('SemanticGardening');
 
        $suite->addTestSuite("TestConsistencyBot");
        $suite->addTestSuite("TestAnomaliesBot");
        $suite->addTestSuite("TestMissingAnnotationsBot");
        $suite->addTestSuite("TestUndefinedEntitiesBot");
        $suite->addTestSuite("TestImportOntologyBot");
 
        return $suite;
    }
}
