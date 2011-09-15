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


require_once 'testcases/TestTSCEQI.php';
require_once 'testcases/TestAutocompletionTSCStore.php';


class HaloTests
{
	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('SMWTSC');

	
		$suite->addTestSuite("TestTSCEQI");
		$suite->addTestSuite("TestAutocompletionTSCStore");
	
		return $suite;
	}
}
