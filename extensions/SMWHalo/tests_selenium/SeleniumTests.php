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

require_once 'testcases/TestSubquery.php';

class SeleniumTests
{
    public static function suite()
    {
		define('UNIT_TEST_RUNNING', true);
        $suite = new PHPUnit_Framework_TestSuite('MySelenium');
        $suite->addTestSuite("TestSubquery");
        return $suite;
    }
}
