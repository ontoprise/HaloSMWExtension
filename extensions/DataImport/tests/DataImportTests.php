<?php

require_once 'testcases/TestWSUpdateBot.php';
require_once 'testcases/TestWSCacheBot.php';
require_once 'testcases/TestWSManagement.php';
require_once 'testcases/TestWSUsage.php';
require_once 'testcases/TestJSONProcessor.php';
require_once 'testcases/TestTIReadPOP3.php';
require_once 'testcases/TestWikipediaUltrapediaMerger.php';
require_once 'testcases/TestLDConnector.php';
require_once 'testcases/TestWSTriplifier.php';


class DataImportTests
{
	public static function suite(){
		$suite = new PHPUnit_Framework_TestSuite('DataImport');

		// add test suites
		$suite->addTestSuite("TestWSUpdateBot");
		$suite->addTestSuite("TestWSCacheBot");
		$suite->addTestSuite("TestWSManagement");
		$suite->addTestSuite("TestWSUsage");
		$suite->addTestSuite("TestJSONProcessor");
		$suite->addTestSuite("TestTIReadPOP3");
//		$suite->addTestSuite("TestWikipediaUltrapediaMerger");
		$suite->addTestSuite("TestLDConnector");
		$suite->addTestSuite("TestWSTriplifier");

		return $suite;
	}
}
?>