<?php
require_once 'PHPUnit/Framework.php';

require_once 'testcases/TestSemanticStore.php';


class HaloTests
{
	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('PHPUnit');

		$suite->addTestSuite("TestSemanticStore");
		$suite->addTestSuite("TestAutocompletionStore");
		$suite->addTestSuite("TestRuleRewriter");
		$suite->addTestSuite("TestWikiJobs");
		$suite->addTestSuite("TestQueryPrinters");
		 

		return $suite;
	}
}
?>