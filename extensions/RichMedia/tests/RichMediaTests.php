<?php
/**
 * @file
 * @ingroup RichMediaTests
 * 
 * @defgroup RichMediaTests Rich Media unit tests
 * @ingroup RichMedia
 * 
 * @author Benjamin Langguth
 */

require_once 'testcases/TestRM.php';

class RichMediaTests
{
	static $PAGE_NAME = 'RM_Test';

	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('PHPUnit');

		// add test suites
		$suite->addTestSuite("TestRM");

		return $suite;
	}
}