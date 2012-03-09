<?php
/*
 * Copyright (C) ontoprise GmbH
 *
 * Vulcan Inc. (Seattle, WA) and ontoprise GmbH (Karlsruhe, Germany)
 * expressly waive any right to enforce any Intellectual Property
 * Rights in or to any enhancements made to this program.
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
 * @ingroup WYSIWYGTests
 *
 * @defgroup WYSIWYGTests WYSIWYG unit tests
 * @ingroup WYSIWYG
 *

 */

// check if original file was called from command line or Webserver
if ( isset( $_SERVER ) && array_key_exists( 'REQUEST_METHOD', $_SERVER ) ) {
    die( "This script must be run from the command line\n" );
}

require_once 'testcases/TestAnnotationsAndIcons.php';
require_once 'testcases/TestPropertiesAndCategoriesChanges.php';
require_once 'testcases/TestQueryInterfaceInWysiwyg.php';
require_once 'testcases/TestTransformationOfSemanticData.php';
require_once 'testcases/TestAddingExternalImages.php';
require_once 'testcases/TestWikiMarkup.php';
require_once 'testcases/TestHtmlToWikitextConverion1.php';
require_once 'testcases/TestHtmlToWikitextConverion2.php';
require_once 'testcases/TestHtmlToWikitextConverion3.php';
require_once 'testcases/TestRule.php';
require_once 'testcases/TestWebservice.php';
require_once 'testcases/TestNoinclude.php';
require_once 'testcases/TestQueries.php';
require_once 'testcases/TestExternalImageLinks.php';
require_once 'testcases/TestInternalFileLinks.php';
require_once 'testcases/TestInternalMediaLinks.php';
require_once 'testcases/TestInterwikiLinks.php';


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
    $suite->addTestSuite("TestRule");
    $suite->addTestSuite("TestWebservice");
    $suite->addTestSuite("TestNoinclude");
    $suite->addTestSuite("TestQueries");
    $suite->addTestSuite("TestExternalImageLinks");
    $suite->addTestSuite("TestInternalFileLinks");
    $suite->addTestSuite("TestInternalMediaLinks");
    $suite->addTestSuite("TestInterwikiLinks");

		return $suite;
	}
}
