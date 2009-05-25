<?php
require_once 'PHPUnit/Framework.php';

require_once 'testcases/TestDeployDescriptorParser.php';
require_once 'testcases/TestDeployDescriptorProcessor.php';

class DeployTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('DeployFramework');

        $suite->addTestSuite("TestDeployDescriptorParser");
        $suite->addTestSuite("TestDeployDescriptorProcessor");
        return $suite;
    }
}
?>