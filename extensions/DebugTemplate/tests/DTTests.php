<?php
/**
 * @file
 * @ingroup DebugTemplateTests
 * 
 * @defgroup DebugTemplateTests DebugTemplate Forms unit tests
 * @ingroup DebugTemplate
 * 
 * @author OP
 */

require_once 'testcases/TestDT.php';

class DTTests
{
	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('DebugTemplate');

		$suite->addTestSuite("TestDT");
		return $suite;
	}
}
