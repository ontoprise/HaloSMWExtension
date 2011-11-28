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


require_once 'testcases/TestTab.php';

class UltrapediaTests
{
	static $TEST_PAGE_NAME = 'Ultrapedia Test';
	public static function suite(){
		$suite = new PHPUnit_Framework_TestSuite('Ultrapedia');

		/*
		 * add test suites
		 */
		$suite->addTestSuite("TestTab");
		
		return $suite;
	}
}
?>
