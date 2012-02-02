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
 * @ingroup RefactoringTests
 *
 * @defgroup RefactoringTests Semantic Refactoring unit tests
 * @ingroup SemanticRefactoring
 *
 * @author Kai
 */
if ( isset( $_SERVER ) && array_key_exists( 'REQUEST_METHOD', $_SERVER ) ) {
	die( "This script must be run from the command line\n" );
}

require_once 'testcases/SRF_TestRenameProperty.php';
require_once 'testcases/SRF_TestRenameCategory.php';
require_once 'testcases/SRF_TestRenameInstance.php';
require_once 'testcases/SRF_TestChangeValue.php';
require_once 'testcases/SRF_TestChangeTemplate.php';
require_once 'testcases/SRF_TestChangeCategory.php';
require_once 'testcases/SRF_TestChangeTemplateParameter.php';
require_once 'testcases/SRF_TestChangeTemplate.php';
require_once 'testcases/SRF_TestDeleteCategory.php';
require_once 'testcases/SRF_TestDeleteProperty.php';
require_once 'testcases/SRF_TestUtil.php';

class SemanticRefactoringTests
{
	protected $backupGlobals = FALSE;
	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('SemanticRefactoring');
		
		/*delete*/
		$suite->addTestSuite("SRFTestDeleteProperty");
		$suite->addTestSuite("SRFTestDeleteCategory");

		/*rename*/
		$suite->addTestSuite("SRFTestRenameCategory");
		$suite->addTestSuite("SRFTestRenameProperty");
		$suite->addTestSuite("SRFTestRenameInstance");
		
		/*change*/
		$suite->addTestSuite("SRFTestChangeTemplate");
		$suite->addTestSuite("SRFTestChangeCategoryValue");
		$suite->addTestSuite("SRFTestChangeValue");
		$suite->addTestSuite("SRFTestChangeTemplateParameter");
		$suite->addTestSuite("SRFTestChangeTemplate");

		$suite->addTestSuite("SRFTestUtil");
		return $suite;
	}
}
