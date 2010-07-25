<?php
/**
 * @file
 * @ingroup SMWHaloTests
 * 
 * @defgroup SMWHaloTests SMWHalo unit tests
 * @ingroup SMWHalo
 * 
 * @author Kai K�hn
 */
require_once 'PHPUnit/Framework.php';

require_once 'testcases/TestSemanticStore.php';
require_once 'testcases/TestAutocompletionStore.php';
require_once 'testcases/TestWikiJobs.php';
require_once 'testcases/TestDataAPI.php';

require_once 'testcases/TestQueryPrinters.php';
require_once 'testcases/TestQIAjaxAccess.php';

require_once 'testcases/TestQueryResultsCache.php';


class HaloTests
{
	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('SMWHalo');

//		$suite->addTestSuite("TestSemanticStore");
//		$suite->addTestSuite("TestAutocompletionStore");
//		$suite->addTestSuite("TestQueryPrinters");
//		$suite->addTestSuite("TestWikiJobs");
//		$suite->addTestSuite("TestDataAPI");
//        $suite->addTestSuite("TestQIAjaxAccess");
        $suite->addTestSuite("TestQueryResultsCache");
		return $suite;
	}
}
