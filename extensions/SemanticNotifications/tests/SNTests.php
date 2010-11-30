<?php
/**
 * @file
 * @ingroup SemanticNotificationsTests
 * 
 * @defgroup SemanticNotificationsTests Semantic Notifications unit tests
 * @ingroup SemanticNotifications
 * 
 * @author OP
 */

require_once 'testcases/TestSN.php';

class SNTests
{
	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('SemanticNotifications');

		$suite->addTestSuite("TestSN");
		return $suite;
	}
}
