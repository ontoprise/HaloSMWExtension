<?php
/**
 * @file
 * @ingroup HaloACL_Tests
 */
require_once 'PHPUnit/Framework.php';
 
require_once 'testcases/TestDatabase.php';
require_once 'testcases/TestParserFunctions.php';
require_once 'testcases/TestUserCanHook.php';
require_once 'testcases/TestDefaultSecurityDescriptor.php';
require_once 'testcases/TestLDAPStorage.php';
require_once 'testcases/TestSMWStore.php';
require_once 'testcases/TestGroupPermissions.php';

class HaloACLTests
{ 
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('PHPUnit');
        
        $suite->addTestSuite("TestDatabaseSuite");
        $suite->addTestSuite("TestParserFunctions");
	    $suite->addTestSuite("TestUserCanHookSuite");
        $suite->addTestSuite("TestDefaultSecurityDescriptorSuite");
        $suite->addTestSuite("TestLDAPStorageSuite");
        $suite->addTestSuite("TestSMWStoreSuite"); 
        $suite->addTestSuite("TestGroupPermissionsSuite"); 
        
        return $suite;
    }
}