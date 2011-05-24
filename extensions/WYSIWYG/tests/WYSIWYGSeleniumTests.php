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

require_once 'testcases/TestAnnotationsAndIcons.php';

class WYSIWYGSeleniumTests
{
	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('WYSIWYGSeleniumTestSuite');
		$suite->addTestSuite("TestAnnotationsAndIcons");
		return $suite;
	}
}
