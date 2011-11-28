<?php
/*
 * Copyright (C) Vulcan Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program.If not, see <http://www.gnu.org/licenses/>.
 *
 */

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
require_once 'testcases/TestIntermediateResultView.php';

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
        $suite->addTestSuite("TestIntermediateResultView");
        return $suite;
    }
}
