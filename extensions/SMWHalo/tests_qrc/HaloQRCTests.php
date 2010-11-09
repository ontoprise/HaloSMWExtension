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

require_once 'testcases/TestQueryResultsCache.php';

class HaloQRCTests
{
	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('SMWHaloQRC');
		$suite->addTestSuite("TestQueryResultsCache");
		return $suite;
	}
}
