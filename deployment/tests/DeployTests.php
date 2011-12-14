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


require_once 'testcases/TestDeployDescriptor.php';
require_once 'testcases/TestDeployDescriptorProcessor.php';
require_once 'testcases/TestPackageRepository.php';
require_once 'testcases/TestResourceInstaller.php';
require_once 'testcases/TestOntologyMerger.php';
require_once 'testcases/TestNamespaceMappings.php';
require_once 'testcases/TestVersions.php';
require_once 'testcases/TestTools.php';

class DeployTests
{
	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('DeployFramework');

		$suite->addTestSuite("TestDeployDescriptor");
		$suite->addTestSuite("TestDeployDescriptorProcessor");
		$suite->addTestSuite("TestPackageRepository");
		$suite->addTestSuite("TestResourceInstaller");
		$suite->addTestSuite("TestOntologyMerger");
		$suite->addTestSuite("TestNamespaceMappings");
		$suite->addTestSuite("TestVersions");
		//$suite->addTestSuite("TestTools");
		return $suite;
	}
}
