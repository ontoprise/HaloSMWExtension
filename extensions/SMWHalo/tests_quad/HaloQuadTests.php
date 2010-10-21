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


class HaloQuadTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('SMWHaloQuad');

        $suite->addTestSuite("TestQuadStorage");
        return $suite;
    }
}
