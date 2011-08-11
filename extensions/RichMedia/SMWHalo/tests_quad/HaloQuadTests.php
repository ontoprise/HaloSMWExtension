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

require_once 'testcases/TestQuadStorage.php';
require_once 'testcases/TestRDFRequest.php';


class HaloQuadTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('SMWHaloQuad');

        $suite->addTestSuite("TestQuadStorage");
        $suite->addTestSuite("TestRDFRequest");
        return $suite;
    }
}
