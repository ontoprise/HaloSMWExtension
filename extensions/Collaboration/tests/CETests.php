<?php
/**
 * @file
 * @ingroup CollaborationTests
 * 
 * @defgroup CollaborationTests Collaboration extension unit tests
 * @ingroup Collaboration
 * 
 * @author Kai K�hn
 */

require_once 'testcases/TestCE.php';

class CETests
{
	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('Collaboration');

		$suite->addTestSuite("TestCE");
		return $suite;
	}
}
