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
require_once 'testcases/TestPropertiesAndCategoriesChanges.php';
require_once 'testcases/TestQueryInterfaceInWysiwyg.php';

class WYSIWYGSeleniumTests
{
	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('WYSIWYGSeleniumTestSuite');
		$suite->addTestSuite("TestAnnotationsAndIcons");
		$suite->addTestSuite("TestPropertiesAndCategoriesChanges");
		$suite->addTestSuite("TestQueryInterfaceInWysiwyg");
		return $suite;
	}
}
