<?php
/**
 * @file
 * @ingroup WYSIWYGTests
 * 
 * @defgroup WYSIWYGTests WYSIWYG unit tests
 * @ingroup WYSIWYG
 * 
 * @author OP
 */

require_once 'testcases/TestWYSIWYG.php';

class WYSIWYGTests
{
	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('WYSIWYG');

		$suite->addTestSuite("TestWYSIWYG");
		return $suite;
	}
}
