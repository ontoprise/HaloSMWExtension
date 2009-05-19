<?php

require_once ('../descriptor/DeployDescriptorParser.php');


/**
 * Tests the deploy descriptor processor
 *
 */
class TestDeployDescriptorProcessor extends PHPUnit_Framework_TestCase {

    var $ddp;

    function setUp() {
        $this->ddp = new DeployDescriptorParser('testcases/resources/test_deploy.xml');
    }

    function tearDown() {

    }

    function testVariableInsertion() {
        $res = $this->ddp->applyConfigurations("testcases/resources/TestSettings.php");
        global $testvar;
        eval($res);
        $this->assertTrue(isset($testvar));
    }

}
?>