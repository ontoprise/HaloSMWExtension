<?php

require_once ('deployment/descriptor/DeployDescriptorParser.php');


/**
 * Tests the deploy descriptor processor
 *
 */
class TestDeployDescriptorProcessor extends PHPUnit_Framework_TestCase {



	function setUp() {


	}

	function tearDown() {

	}

	function testPrecedings() {
		$exp_precedings = array("SemanticMediawiki", "SemanticGardening");
		$ddp = new DeployDescriptorParser('testcases/resources/test_deploy_variables.xml');
		$precedings = $ddp->getPrecedings();
		foreach($precedings as $exp) {
			$this->assertContains($exp, $exp_precedings);
		}

	}

	function testVariableInsertion() {
		$ddp = new DeployDescriptorParser('testcases/resources/test_deploy_variables.xml');
		$res = $ddp->applyConfigurations("testcases/resources/TestSettings.php");
		global $testvar;
		eval($res);
		$this->assertTrue(isset($testvar));
	}

	function testVariableRemoval() {
		$ddp = new DeployDescriptorParser('testcases/resources/test_deploy_variables.xml');
		$res = $ddp->applyConfigurations("testcases/resources/TestSettings.php");
			
		global $testvar2;
		eval($res);
		$this->assertTrue(!isset($testvar2));
	}

	function testVariableReplacement() {
		$ddp = new DeployDescriptorParser('testcases/resources/test_deploy_variables.xml');
		$res = $ddp->applyConfigurations("testcases/resources/TestSettings.php");
			
		global $testvar3;
		eval($res);
		$this->assertTrue(isset($testvar3));
		$this->assertEquals($testvar3, "Halo is so cool");
	}

	function testVariableReplacementWithNumber() {
		$ddp = new DeployDescriptorParser('testcases/resources/test_deploy_variables.xml');
		$res = $ddp->applyConfigurations("testcases/resources/TestSettings.php");
			
		global $testvar4;
		eval($res);
		$this->assertTrue(isset($testvar4));
		$this->assertTrue(is_numeric($testvar4));
		$this->assertEquals($testvar4, 25);
	}

	function testVariableReplacementWithBoolean() {
		$ddp = new DeployDescriptorParser('testcases/resources/test_deploy_variables.xml');
		$res = $ddp->applyConfigurations("testcases/resources/TestSettings.php");
			
		global $testvar5;
		eval($res);
		$this->assertTrue(isset($testvar5));
		$this->assertTrue(is_bool($testvar5));
		$this->assertEquals($testvar5, true);
	}

	function testVariableReplacementWithInternal() {
		$ddp = new DeployDescriptorParser('testcases/resources/test_deploy_variables.xml');
		$res = $ddp->applyConfigurations("testcases/resources/TestSettings.php");
			
		global $testvar6;
		eval($res);
			
		$this->assertTrue(isset($testvar6));
		$this->assertEquals($testvar6, "Halo"); // must not changed to: Halo is cool
	}

	function testVariableComplexInsertion() {
		$ddp = new DeployDescriptorParser('testcases/resources/test_deploy_variables.xml');
		$res = $ddp->applyConfigurations("testcases/resources/TestSettings.php");
			
		global $testvar7;
		eval($res);

		$this->assertTrue(isset($testvar7));
		$this->assertEquals($testvar7[0], "Halo is cool");
		$this->assertEquals($testvar7[1][0], 1);
		$this->assertEquals($testvar7[1][1], "Halo");
		$this->assertEquals($testvar7[1][2], true);
	}

	function testFunctionCallInsertion() {
		$ddp2 = new DeployDescriptorParser('testcases/resources/test_deploy_function.xml');
		$res = $ddp2->applyConfigurations("testcases/resources/TestSettings.php");

		global $ret1;
		eval($res);

		$this->assertTrue(isset($ret1));
		$this->assertEquals("http://localhost:8080", $ret1);
	}

	function testFunctionCallRemoval() {
		$ddp2 = new DeployDescriptorParser('testcases/resources/test_deploy_function.xml');
		$res = $ddp2->applyConfigurations("testcases/resources/TestSettings.php");

		global $ret2;
		unset($ret2); // hack: Linux needs this, global variables got not deleted!
		eval($res);

		$this->assertTrue(!isset($ret2));
	}

	function testFunctionCallReplacement() {
		$ddp2 = new DeployDescriptorParser('testcases/resources/test_deploy_function2.xml');
		$res = $ddp2->applyConfigurations("testcases/resources/TestSettings.php");

			
		global $ret2, $ret3;
		eval($res);

		$this->assertTrue(isset($ret2));
		$this->assertEquals($ret3[0], 1);
		$this->assertEquals($ret3[1], 2);
	}

	function testRequireStatementInsert() {
		$ddp2 = new DeployDescriptorParser('testcases/resources/test_deploy_require.xml');
		$res = $ddp2->applyConfigurations("testcases/resources/TestSettings.php");
			
		global $testInclude;
		eval($res);

		$this->assertTrue(isset($testInclude));

	}

	function testRequireStatementRemove() {
		$ddp2 = new DeployDescriptorParser('testcases/resources/test_deploy_require.xml');
		$res = $ddp2->applyConfigurations("testcases/resources/TestSettings.php");
			
		global $testInclude2;

		eval($res);

		$this->assertTrue(!isset($testInclude2));

	}

	function testPHPInsert() {
		$ddp2 = new DeployDescriptorParser('testcases/resources/test_deploy_php.xml');
		$res = $ddp2->applyConfigurations("testcases/resources/TestSettings.php");
			
		global $testphp;
		eval($res);

		$this->assertTrue(isset($testphp));

	}

	function testPHPRemove() {
		$ddp2 = new DeployDescriptorParser('testcases/resources/test_deploy_php.xml');
		$res = $ddp2->applyConfigurations("testcases/resources/TestSettings.php");
		 
		global $testphp2;
		eval($res);

		$this->assertTrue(!isset($testphp2));

	}
}

// testfunctions needed
function testfunc($url, $arr) {
	global $ret1;
	$ret1 = $url;
}
function testfunc2($url, $arr) {
	global $ret2;
	global $ret3;
	$ret2 = $url;
	$ret3 = $arr;
}
?>