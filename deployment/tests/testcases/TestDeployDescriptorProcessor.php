<?php

require_once ('deployment/descriptor/DeployDescriptorParser.php');


/**
 * Tests the deploy descriptor processor
 *
 */
class TestDeployDescriptorProcessor extends PHPUnit_Framework_TestCase {

    var $xml_variables;
    var $xml_function;
    var $xml_function2;
    var $xml_require;
    var $xml_php;
    

	function setUp() {
		$this->xml_variables = file_get_contents('testcases/resources/test_deploy_variables.xml');
		$this->xml_function = file_get_contents('testcases/resources/test_deploy_function.xml');
		$this->xml_function2 = file_get_contents('testcases/resources/test_deploy_function2.xml');
		$this->xml_require = file_get_contents('testcases/resources/test_deploy_require.xml');
		$this->xml_php = file_get_contents('testcases/resources/test_deploy_php.xml');

	}

	function tearDown() {

	}

	function testPrecedings() {
		$exp_precedings = array("SemanticMediawiki", "SemanticGardening");
		$ddp = new DeployDescriptorParser($this->xml_variables);
		$precedings = $ddp->getPrecedings();
		foreach($precedings as $exp) {
			$this->assertContains($exp, $exp_precedings);
		}

	}

	function testVariableInsertion() {
		$ddp = new DeployDescriptorParser($this->xml_variables);
		$res = $ddp->applyConfigurations("testcases/resources", true);
		global $testvar;
		
		eval($res);
		$this->assertTrue(isset($testvar));
	}

	function testVariableRemoval() {
		$ddp = new DeployDescriptorParser($this->xml_variables);
		$res = $ddp->applyConfigurations("testcases/resources", true);
			
		global $testvar2;
		eval($res);
		$this->assertTrue(!isset($testvar2));
	}

	function testVariableReplacement() {
		$ddp = new DeployDescriptorParser($this->xml_variables);
		$res = $ddp->applyConfigurations("testcases/resources", true);
			
		global $testvar3;
		eval($res);
		$this->assertTrue(isset($testvar3));
		$this->assertEquals($testvar3, "Halo is so cool");
	}

	function testVariableReplacementWithNumber() {
		$ddp = new DeployDescriptorParser($this->xml_variables);
		$res = $ddp->applyConfigurations("testcases/resources", true);
			
		global $testvar4;
		eval($res);
		$this->assertTrue(isset($testvar4));
		$this->assertTrue(is_numeric($testvar4));
		$this->assertEquals($testvar4, 25);
	}

	function testVariableReplacementWithBoolean() {
		$ddp = new DeployDescriptorParser($this->xml_variables);
		$res = $ddp->applyConfigurations("testcases/resources", true);
			
		global $testvar5;
		eval($res);
		$this->assertTrue(isset($testvar5));
		$this->assertTrue(is_bool($testvar5));
		$this->assertEquals($testvar5, true);
	}

	function testVariableReplacementWithInternal() {
		$ddp = new DeployDescriptorParser($this->xml_variables);
		$res = $ddp->applyConfigurations("testcases/resources", true);
			
		global $testvar6;
		eval($res);
			
		$this->assertTrue(isset($testvar6));
		$this->assertEquals($testvar6, "Halo"); // must not changed to: Halo is cool
	}

	function testVariableComplexInsertion() {
		$ddp = new DeployDescriptorParser($this->xml_variables);
		$res = $ddp->applyConfigurations("testcases/resources", true);
			
		global $testvar7;
		eval($res);

		$this->assertTrue(isset($testvar7));
		$this->assertEquals($testvar7[0], "Halo is cool");
		$this->assertEquals($testvar7[1][0], 1);
		$this->assertEquals($testvar7[1][1], "Halo");
		$this->assertEquals($testvar7[1][2], true);
	}

	function testFunctionCallInsertion() {
		$ddp2 = new DeployDescriptorParser($this->xml_function);
		$res = $ddp2->applyConfigurations("testcases/resources", true);

		global $ret1;
		eval($res);

		$this->assertTrue(isset($ret1));
		$this->assertEquals("http://localhost:8080", $ret1);
	}

	function testFunctionCallInsertion2() {
		$ddp2 = new DeployDescriptorParser($this->xml_function);
		$res = $ddp2->applyConfigurations("testcases/resources", true);

		global $server;
		global $port;
		global $protocol;
		
		eval($res);

		$this->assertTrue(isset($server) && isset($port) && isset($protocol));
		$this->assertEquals("localhost", $server);
		$this->assertEquals("80", $port);
		$this->assertEquals("http", $protocol);
	}

	function testFunctionCallRemoval() {
		$ddp2 = new DeployDescriptorParser($this->xml_function);
		$res = $ddp2->applyConfigurations("testcases/resources", true);

		global $ret2;
		unset($ret2); // hack: Linux needs this, global variables got not deleted!
		eval($res);

		$this->assertTrue(!isset($ret2));
	}

	function testFunctionCallReplacement() {
		$ddp2 = new DeployDescriptorParser($this->xml_function2);
		$res = $ddp2->applyConfigurations("testcases/resources", true);

			
		global $ret2, $ret3;
		eval($res);

		$this->assertTrue(isset($ret2));
		$this->assertEquals($ret3[0], 1);
		$this->assertEquals($ret3[1], 2);
	}

	function testRequireStatementInsert() {
		$ddp2 = new DeployDescriptorParser($this->xml_require);
		$res = $ddp2->applyConfigurations("testcases/resources", true);
			
		global $testInclude;
		eval($res);

		$this->assertTrue(isset($testInclude));

	}

	function testRequireStatementRemove() {
		$ddp2 = new DeployDescriptorParser($this->xml_require);
		$res = $ddp2->applyConfigurations("testcases/resources", true);
			
		global $testInclude2;

		eval($res);

		$this->assertTrue(!isset($testInclude2));

	}

	function testPHPInsert() {
		$ddp2 = new DeployDescriptorParser($this->xml_php);
		$res = $ddp2->applyConfigurations("testcases/resources", true);
			
		global $testphp;
		eval($res);

		$this->assertTrue(isset($testphp));

	}

	function testPHPRemove() {
		$ddp2 = new DeployDescriptorParser($this->xml_php);
		$res = $ddp2->applyConfigurations("testcases/resources", true);
			
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

function testfunc3($arr) {
	global $server;
	global $port;
	global $protocol;
	$server = $arr['server'];
	$port = $arr['port'];
	$protocol = $arr['protocol'];
}
?>