<?php


require_once 'testcases/TestDeployDescriptor.php';
require_once 'testcases/TestDeployDescriptorProcessor.php';
require_once 'testcases/TestPackageRepository.php';
require_once 'testcases/TestResourceInstaller.php';
require_once 'testcases/TestOntologyMerger.php';
require_once 'testcases/TestNamespaceMappings.php';
require_once 'testcases/TestVersions.php';
require_once 'testcases/TestTools.php';

class DeployTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('DeployFramework');

        $suite->addTestSuite("TestDeployDescriptor");
        $suite->addTestSuite("TestDeployDescriptorProcessor");
        $suite->addTestSuite("TestPackageRepository");
        $suite->addTestSuite("TestResourceInstaller");
        $suite->addTestSuite("TestOntologyMerger");
        $suite->addTestSuite("TestNamespaceMappings");
        $suite->addTestSuite("TestVersions");
        //$suite->addTestSuite("TestTools");
        return $suite;
    }
}
