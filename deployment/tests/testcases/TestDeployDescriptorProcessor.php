<?php
/*
 * Copyright (C) Vulcan Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program.If not, see <http://www.gnu.org/licenses/>.
 *
 */
if ( isset( $_SERVER ) && array_key_exists( 'REQUEST_METHOD', $_SERVER ) ) {
	die( "This script must be run from the command line\n" );
}

require_once ('deployment/settings.php');
require_once ('deployment/tools/smwadmin/DF_Tools.php');
require_once ('deployment/io/DF_Log.php');
require_once ('deployment/io/DF_PrintoutStream.php');
require_once ('deployment/descriptor/DF_DeployDescriptor.php');


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
	var $xml_replace;


	function setUp() {
		global $dfgOut;
		$dfgOut = DFPrintoutStream::getInstance(DF_OUTPUT_FORMAT_TEXT);
		$this->xml_variables = file_get_contents('testcases/resources/test_deploy_variables.xml');
		$this->xml_function = file_get_contents('testcases/resources/test_deploy_function.xml');
		$this->xml_function2 = file_get_contents('testcases/resources/test_deploy_function2.xml');
		$this->xml_require = file_get_contents('testcases/resources/test_deploy_require.xml');
		$this->xml_php = file_get_contents('testcases/resources/test_deploy_php.xml');
		$this->xml_replace = file_get_contents('testcases/resources/test_deploy_replace.xml');

	}

	function tearDown() {

	}

	function testSuccessors() {
		$exp_precedings = array("SemanticMediawiki", "SemanticGardening");
		$ddp = new DeployDescriptor($this->xml_variables);
		$successors = $ddp->getSuccessors();

		foreach($successors as $succ) {
			$this->assertContains($succ, $exp_precedings);
		}

	}

	function testVariableInsertion() {
		$ddp = new DeployDescriptor($this->xml_variables);
		$res = $ddp->applyConfigurations("testcases/resources", true);
		global $testvar;

		eval($res);
		$this->assertTrue(isset($testvar));
	}

	function testVariableRemoval() {
		$ddp = new DeployDescriptor($this->xml_variables);
		$res = $ddp->applyConfigurations("testcases/resources", true);
			
		global $testvar2;
		eval($res);
		$this->assertTrue(!isset($testvar2));
	}

	function testVariableReplacement() {
		$ddp = new DeployDescriptor($this->xml_variables);
		$res = $ddp->applyConfigurations("testcases/resources", true);
			
		global $testvar3;
		eval($res);
		$this->assertTrue(isset($testvar3));
		$this->assertEquals($testvar3, "Halo is so cool");
	}

	function testVariableReplacementWithNumber() {
		$ddp = new DeployDescriptor($this->xml_variables);
		$res = $ddp->applyConfigurations("testcases/resources", true);
			
		global $testvar4;
		eval($res);
		$this->assertTrue(isset($testvar4));
		$this->assertTrue(is_numeric($testvar4));
		$this->assertEquals($testvar4, 25);
	}

	function testVariableReplacementWithBoolean() {
		$ddp = new DeployDescriptor($this->xml_variables);
		$res = $ddp->applyConfigurations("testcases/resources", true);
			
		global $testvar5;
		eval($res);
		$this->assertTrue(isset($testvar5));
		$this->assertTrue(is_bool($testvar5));
		$this->assertEquals($testvar5, true);
	}

	function testVariableReplacementWithInternal() {
		$ddp = new DeployDescriptor($this->xml_variables);
		$res = $ddp->applyConfigurations("testcases/resources", true);
			
		global $testvar6;
		eval($res);
			
		$this->assertTrue(isset($testvar6));
		$this->assertEquals($testvar6, "Halo"); // must not changed to: Halo is cool
	}

	function testVariableComplexInsertion() {
		$ddp = new DeployDescriptor($this->xml_variables);
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
		$ddp2 = new DeployDescriptor($this->xml_function);
		$res = $ddp2->applyConfigurations("testcases/resources", true);


		global $ret1;
		eval($res);

		$this->assertTrue(isset($ret1));
		$this->assertEquals("http://localhost:8080", $ret1);
	}

	function testFunctionCallInsertion2() {
		$ddp2 = new DeployDescriptor($this->xml_function);
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
		$ddp2 = new DeployDescriptor($this->xml_function);
		$res = $ddp2->applyConfigurations("testcases/resources", true);

		global $ret2;
		unset($ret2); // hack: Linux needs this, global variables got not deleted!
		eval($res);

		$this->assertTrue(!isset($ret2));
	}

	function testFunctionCallReplacement() {
		$ddp2 = new DeployDescriptor($this->xml_function2);
		$res = $ddp2->applyConfigurations("testcases/resources", true);

			
		global $ret2, $ret3;
		eval($res);

		$this->assertTrue(isset($ret2));
		$this->assertEquals($ret3[0], 1);
		$this->assertEquals($ret3[1], 2);
	}

	function testRequireStatementInsert() {
		$ddp2 = new DeployDescriptor($this->xml_require);
		$res = $ddp2->applyConfigurations("testcases/resources", true);
			
		global $testInclude;
		eval($res);

		$this->assertTrue(isset($testInclude));

	}

	function testRequireStatementRemove() {
		$ddp2 = new DeployDescriptor($this->xml_require);
		$res = $ddp2->applyConfigurations("testcases/resources", true);
			
		global $testInclude2;

		eval($res);

		$this->assertTrue(!isset($testInclude2));

	}

	function testPHPInsert() {
		$ddp2 = new DeployDescriptor($this->xml_php);
		$res = $ddp2->applyConfigurations("testcases/resources", true);
			
		global $testphp;
		eval($res);

		$this->assertTrue(isset($testphp));

	}

	function testPHPRemove() {
		$ddp2 = new DeployDescriptor($this->xml_php);
		$res = $ddp2->applyConfigurations("testcases/resources", true);
			
		global $testphp2;
		eval($res);

		$this->assertTrue(!isset($testphp2));

	}

	function testReplacement() {
		$ddp2 = new DeployDescriptor($this->xml_replace);
		$res = $ddp2->applyConfigurations("testcases/resources", true);

		global $testvar2;
		eval($res);

		$this->assertTrue(isset($testvar2));
		$this->assertEquals("Halo rockt echt", $testvar2);

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
