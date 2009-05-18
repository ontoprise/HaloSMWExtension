<?php
require_once 'PHPUnit/Framework.php';

require_once 'testcases/TestDeployDescriptor.php';

class DeployTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('DeployFramework');

        $suite->addTestSuite("TestDeployDescriptor");
  
        return $suite;
    }
}
?>