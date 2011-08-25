<?php
require_once ('deployment/descriptor/DF_Version.php');
/**
 * Tests the DFVersion class
 *
 */
class TestVersions extends PHPUnit_Framework_TestCase {



	function setUp() {

	}

	function tearDown() {

	}

	function testVersionMajorMinor() {
		$v = new DFVersion("1.0");
		$this->assertEquals($v->toVersionString(), '1.0');
	}

	function testVersionMajorMinorSubMinor() {
		$v = new DFVersion("1.0.2");
		$this->assertEquals($v->toVersionString(), '1.0.2');
	}

	function testVersionMajorMinorSubMinor2() {
		$v = new DFVersion("1.16.2");
		$this->assertEquals($v->toVersionString(), '1.16.2');
	}

	function testVersionIsLower() {
		$v1 = new DFVersion("1.0.2");
		$v2 = new DFVersion("1.1.2");
		$this->assertTrue($v1->isLower($v2));
		$this->assertFalse($v2->isLower($v1));
	}

	function testVersionIsLower2() {
		$v1 = new DFVersion("0.1.2");
		$v2 = new DFVersion("0.5");
		$this->assertTrue($v1->isLower($v2));
		$this->assertFalse($v2->isLower($v1));
	}

	function testVersionIsLower3() {
		$v1 = new DFVersion("1.16.2");
		$v2 = new DFVersion("2.0.0");
		$this->assertTrue($v1->isLower($v2));
		$this->assertFalse($v2->isLower($v1));
	}

	function testVersionIsEqual() {
		$v1 = new DFVersion("1.0.2");
		$v2 = new DFVersion("1.0.2");
		$this->assertTrue($v1->isEqual($v2));
	}

	function testVersionIsEqual2() {
		$v1 = new DFVersion("1.0");
		$v2 = new DFVersion("1.0.0");
		$this->assertFalse($v1->isEqual($v2));
	}

}