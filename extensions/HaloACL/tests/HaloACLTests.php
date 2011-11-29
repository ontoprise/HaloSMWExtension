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
 * @ingroup HaloACL_Tests
 */
 
require_once 'testcases/TestDatabase.php';
require_once 'testcases/TestParserFunctions.php';
require_once 'testcases/TestUserCanHook.php';
require_once 'testcases/TestDefaultSecurityDescriptor.php';
require_once 'testcases/TestLDAPStorage.php';
require_once 'testcases/TestSMWStore.php';
require_once 'testcases/TestGroupPermissions.php';
require_once 'testcases/TestDynamicHaloACL.php';
require_once 'testcases/TestRenderArticles.php';

class HaloACLTests
{ 
    public static function suite()
    {
		define('UNIT_TEST_RUNNING', true);
		define('SMWH_FORCE_TS_UPDATE', true);
    	$suite = new PHPUnit_Framework_TestSuite('PHPUnit');
        
//    	$suite->addTestSuite("TestDatabaseSuite");
//    	$suite->addTestSuite("TestParserFunctionsSuite");
//	   	$suite->addTestSuite("TestUserCanHookSuite");
//    	$suite->addTestSuite("TestDefaultSecurityDescriptorSuite");
//    	$suite->addTestSuite("TestLDAPStorageSuite");
//    	$suite->addTestSuite("TestSMWStoreSuite");
//    	$suite->addTestSuite("TestGroupPermissionsSuite");
    	$suite->addTestSuite("TestDynamicHaloACLSuite");
//    	$suite->addTestSuite("TestRenderArticlesSuite");
        
        return $suite;
    }
}