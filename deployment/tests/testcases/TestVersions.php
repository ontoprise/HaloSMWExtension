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
		$this->assertEquals($v->toVersionString(), '1.0.0');
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
		$this->assertTrue($v1->isEqual($v2));
	}

	function testSorting() {
		$v1 = new DFVersion("1.16.2");
		$v2 = new DFVersion("2.0.0");
		$v3 = new DFVersion("1.3.2");
		$v4 = new DFVersion("0.1.10");
		$versions = array( array($v1, 0), array($v2, 0), array($v3, 0), array($v4, 0));
		DFVersion::sortVersions($versions);
		$this->assertTrue($versions[0][0]->isEqual($v2));
		$this->assertTrue($versions[1][0]->isEqual($v1));
		$this->assertTrue($versions[2][0]->isEqual($v3));
		$this->assertTrue($versions[3][0]->isEqual($v4));
	}

	function testSorting2() {
		// make sure that doubles get removed
		$v1 = new DFVersion("1.16.2");
		$v2 = new DFVersion("2.0.0");
		$v3 = new DFVersion("1.16.2");
		$v4 = new DFVersion("0.1.10");
		$versions = array( array($v1, 0), array($v2, 0), array($v3, 0), array($v4, 0));
		DFVersion::sortVersions($versions);
		$this->assertTrue(count($versions) === 3);
	}

	function testMaxVersion() {
		$v1 = new DFVersion("1.16.2");
		$v2 = new DFVersion("2.0.0");
		$v3 = new DFVersion("1.3.2");
		$v4 = new DFVersion("0.1.10");
		$versions = array( array($v1, 0), array($v2, 0), array($v3, 0), array($v4, 0));
		$max = DFVersion::getMaxVersion($versions);
		$this->assertTrue($max[0]->isEqual($v2));
	}

}
