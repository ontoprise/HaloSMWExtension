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

//require_once 'testcases/TestSubquery.php';
require_once 'testcases/TestPreviewResult_short.php';
require_once 'testcases/TestFormatQueryPreview.php';
require_once 'testcases/TestInferredResults.php';
require_once 'testcases/TestUnitsInQuery.php';
require_once 'testcases/TestQuerySourceText.php';

class SeleniumTests
{
    public static function suite()
    {
		define('UNIT_TEST_RUNNING', true);
        $suite = new PHPUnit_Framework_TestSuite('SMWHaloSeleniumTestSuite');
//        $suite->addTestSuite("TestSubquery");
        $suite->addTestSuite("TestPreviewResult_short");
        $suite->addTestSuite("TestFormatQueryPreview");
        $suite->addTestSuite("TestInferredResults");
        $suite->addTestSuite("TestUnitsInQuery");
        $suite->addTestSuite("TestQuerySourceText");
        return $suite;
    }
}
