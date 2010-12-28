<?php

require_once 'testcases/TestSCProcessor.php';
require_once 'testcases/TestSCMapping.php';
require_once 'testcases/TestSCLookup.php';
require_once 'testcases/TestPageMapping.php';
require_once 'testcases/TestSCRest.php';
require_once 'testcases/TestSCAjax.php';

class SemanticConnectorTests
{
	static $TEST_PAGE_NAME = 'Semantic Connector Test';
	public static function suite(){
		$suite = new PHPUnit_Framework_TestSuite('SemanticConnector');

		/*
		 * add test suites
		 */
		$suite->addTestSuite("TestSCProcessor");
		
		// have to be the first testcase using DB, PHPUnit_Framework_TestCase::setUpBeforeClass defined here
		$suite->addTestSuite("TestSCMapping");
				
		$suite->addTestSuite("TestSCLookup");
		$suite->addTestSuite("TestPageMapping");
		$suite->addTestSuite("TestSCRest");
		$suite->addTestSuite("TestSCAjax");
		
		return $suite;
	}
}
?>