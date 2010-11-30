<?php
/**
 * @file
 * @ingroup SemanticRulesTests
 * 
 * @defgroup SemanticRulesTests SemanticRules unit tests
 * @ingroup SemanticRules
 * 
 * @author OP
 */

require_once 'testcases/TestSR.php';

class SRTests
{
	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('SemanticRules');

		$suite->addTestSuite("TestSR");
		return $suite;
	}
}
