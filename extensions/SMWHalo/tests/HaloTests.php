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

if ( isset( $_SERVER ) && array_key_exists( 'REQUEST_METHOD', $_SERVER ) ) {
	die( "This script must be run from the command line\n" );
}

/**
 * @file
 * @ingroup SMWHaloTests
 *
 * @defgroup SMWHaloTests SMWHalo unit tests
 * @ingroup SMWHalo
 *
 * @author Kai K�hn
 */

require_once 'testcases/TestSemanticStore.php';
require_once 'testcases/TestWikiEQI.php';
require_once 'testcases/TestAutocompletionStore.php';
require_once 'testcases/TestDataAPI.php';

require_once 'testcases/TestQueryPrinters.php';
require_once 'testcases/TestQIAjaxAccess.php';
require_once 'testcases/TestBuiltinProperties.php';
require_once 'testcases/TestOntologyManipulator.php';

class HaloTests
{
	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('SMWHalo');

		$suite->addTestSuite("TestSemanticStore");
		$suite->addTestSuite("TestWikiEQI");
		$suite->addTestSuite("TestAutocompletionStore");
		$suite->addTestSuite("TestQueryPrintersSuite");

		$suite->addTestSuite("TestDataAPI");
		$suite->addTestSuite("TestQIAjaxAccess");
		$suite->addTestSuite("TestBuiltinPropertiesSuite");
		$suite->addTestSuite("TestOntologyManipulatorSuite");
		return $suite;
	}
}
