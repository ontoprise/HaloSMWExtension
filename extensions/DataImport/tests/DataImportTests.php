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


require_once 'testcases/TestWSUpdateBot.php';
require_once 'testcases/TestWSCacheBot.php';
require_once 'testcases/TestWSManagement.php';
require_once 'testcases/TestWSUsage.php';
require_once 'testcase
s/TestJSONProcessor.php';
require_once 'testcases/TestTIReadPOP3.php';
require_once 'testcases/TestWikipediaUltrapediaMerger.php';
require_once 'testcases/TestLDConnector.php';
require_once 'testcases/TestWSTriplifier.php';


class DataImportTests
{
	public static function suite(){
		$suite = new PHPUnit_Framework_TestSuite('DataImport');

		//DATA IMPORT TESTS CURRENTLY DO NOT WORK
		
		// add test suites
		//$suite->addTestSuite("TestWSUpdateBot");
		//$suite->addTestSuite("TestWSCacheBot");
		//$suite->addTestSuite("TestWSManagement");
		//$suite->addTestSuite("TestWSUsage");
		//$suite->addTestSuite("TestJSONProcessor");
		//$suite->addTestSuite("TestTIReadPOP3");
//		$suite->addTestSuite("TestWikipediaUltrapediaMerger");
		//$suite->addTestSuite("TestLDConnector");
		//$suite->addTestSuite("TestWSTriplifier");

		return $suite;
	}
}
?>
