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
require_once 'testcases/TestTransformationOfSemanticData.php';
require_once 'testcases/TestAddingExternalImages.php';
require_once 'testcases/TestWikiMarkup.php';
require_once 'testcases/TestHtmlToWikitextConverion1.php';
require_once 'testcases/TestHtmlToWikitextConverion2.php';
require_once 'testcases/TestHtmlToWikitextConverion3.php';

class WYSIWYGSeleniumTests
{
	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('WYSIWYGSeleniumTestSuite');
		$suite->addTestSuite("TestAnnotationsAndIcons");
		$suite->addTestSuite("TestPropertiesAndCategoriesChanges");
		$suite->addTestSuite("TestQueryInterfaceInWysiwyg");
		$suite->addTestSuite("TestTransformationOfSemanticData");
		$suite->addTestSuite("TestAddingExternalImages");
		$suite->addTestSuite("TestWikiMarkup");
                $suite->addTestSuite("TestHtmlToWikitextConverion1");
                $suite->addTestSuite("TestHtmlToWikitextConverion2");
                $suite->addTestSuite("TestHtmlToWikitextConverion3");
		return $suite;
	}
}
