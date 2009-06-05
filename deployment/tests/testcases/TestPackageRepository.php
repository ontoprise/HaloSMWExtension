<?php

require_once '../tools/smwadmin/PackageRepository.php';
/**
 * Tests the installer clazz
 *
 */
class TestPackageRepository extends PHPUnit_Framework_TestCase {


	function setUp() {
		PackageRepository::getPackageRepositoryFromString(file_get_contents("testcases/resources/repository.xml"));
	}

	function tearDown() {

	}


	function testGetVersion() {
		$this->assertNotNull(PackageRepository::getVersion("SMWHalo",160));
	}

	function testGetVersion2() {
		$this->assertNull(PackageRepository::getVersion("SMWHalo",170));
	}

	function testLatestVersion() {
		$this->assertNotNull(PackageRepository::getLatestVersion("SMWHalo"));
	}

	function testExistsVersion() {
		$this->assertTrue(PackageRepository::existsPackage("SMWHalo"));
	}

	function testExistsVersion2() {
		$this->assertTrue(PackageRepository::existsPackage("SMWHalo", 160));
	}

	function testExistsVersion3() {
		$this->assertFalse(PackageRepository::existsPackage("SMWHalo", 170));
	}

}