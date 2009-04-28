<?php
require_once 'PHPUnit/Framework.php';

require_once 'testcases/TestSemanticStore.php';
require_once 'testcases/TestAutocompletionStore.php';
require_once 'testcases/TestRuleRewriter.php';
require_once 'testcases/TestWikiJobs.php';
require_once 'testcases/TestQueryPrinters.php';


class HaloTests
{
	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('SMWHalo');

		$suite->addTestSuite("TestSemanticStore");
		$suite->addTestSuite("TestAutocompletionStore");
		$suite->addTestSuite("TestRuleRewriter");
		$suite->addTestSuite("TestWikiJobs");
		$suite->addTestSuite("TestQueryPrinters");
		 

		return $suite;
	}
}
?>