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

require_once 'testcases/TestSemanticStore.php';
require_once 'testcases/TestWikiEQI.php';
require_once 'testcases/TestTSCEQI.php';
require_once 'testcases/TestAutocompletionTSCStore.php';
require_once 'testcases/TestAutocompletionStore.php';
require_once 'testcases/TestWikiJobs.php';
require_once 'testcases/TestDataAPI.php';

require_once 'testcases/TestQueryPrinters.php';
require_once 'testcases/TestQIAjaxAccess.php';
require_once 'testcases/TestBuiltinProperties.php';
require_once 'testcases/TestOntologyManipulator.php';

class HaloTests
{
	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('SMWHalo');

		$suite->addTestSuite("TestSemanticStore");
		$suite->addTestSuite("TestWikiEQI");
		$suite->addTestSuite("TestAutocompletionStore");
		$suite->addTestSuite("TestQueryPrintersSuite");
		$suite->addTestSuite("TestWikiJobs");
		$suite->addTestSuite("TestDataAPI");
		$suite->addTestSuite("TestQIAjaxAccess");
		$suite->addTestSuite("TestBuiltinPropertiesSuite");
		$suite->addTestSuite("TestOntologyManipulatorSuite");
		return $suite;
	}
}
