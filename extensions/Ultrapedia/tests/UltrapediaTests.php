<?php

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