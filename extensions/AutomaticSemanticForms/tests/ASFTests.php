<?php
/**
 * @file
 * @ingroup AutomaticSemanticFormsTests
 * 
 * @defgroup AutomaticSemanticFormsTests Automatic Semantic Forms unit tests
 * @ingroup AutomaticSemanticForms
 * 
 * @author OP
 */

require_once 'testcases/TestASF.php';

class ASFTests
{
	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('AutomaticSemanticForms');

		$suite->addTestSuite("TestASF");
		return $suite;
	}
}
