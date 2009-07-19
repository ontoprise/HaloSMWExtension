<?php
require_once 'PHPUnit/Framework.php';

require_once 'testcases/TestDeployDescriptor.php';
require_once 'testcases/TestDeployDescriptorProcessor.php';
require_once 'testcases/TestPackageRepository.php';
require_once 'testcases/TestInstaller.php';

class DeployTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('DeployFramework');

        $suite->addTestSuite("TestDeployDescriptor");
        $suite->addTestSuite("TestDeployDescriptorProcessor");
        $suite->addTestSuite("TestPackageRepository");
        //$suite->addTestSuite("TestInstaller");
        return $suite;
    }
}
?>