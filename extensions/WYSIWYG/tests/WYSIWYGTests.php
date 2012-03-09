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

require_once 'testcases/TestWYSIWYGparser.php';

class WYSIWYGTests
{
	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('WYSIWYG');

		$suite->addTestSuite("TestWYSIWYGparser");
		return $suite;
	}
}
