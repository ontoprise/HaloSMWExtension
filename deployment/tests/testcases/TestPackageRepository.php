<?php

// activate for debugging
//define('DEBUG_MODE', true);

if (defined('DEBUG_MODE') && DEBUG_MODE == true) {
	require_once 'deployment/tools/smwadmin/PackageRepository.php';
} else {
	require_once '../tools/smwadmin/PackageRepository.php';
}
/**
 * Tests the installer clazz
 *
 */
class TestPackageRepository extends PHPUnit_Framework_TestCase {

	static $rootDir;
	static $instDir;

	function setUp() {
		$path = defined('DEBUG_MODE') && DEBUG_MODE == true ? "deployment/tests/testcases/resources/repository/repository.xml" : "testcases/resources/repository/repository.xml";
		PackageRepository::initializePackageRepositoryFromString(file_get_contents($path));
		self::$rootDir = realpath(dirname($path));
		$path = defined('DEBUG_MODE') && DEBUG_MODE == true ? "deployment/tests/testcases/resources/installer/" : "testcases/resources/installer/";
		self::$instDir = realpath($path);
	}

	function tearDown() {

	}


	function testGetVersion() {
		$this->assertNotNull(PackageRepository::getVersion("SMWHalo",150));
	}

	function testGetVersion2() {
		$this->assertNull(PackageRepository::getVersion("SMWHalo",170));
	}

	function testGetVersion3() {
		$versions = PackageRepository::getAllVersions("SMWHalo");

		$this->assertTrue(count($versions) === 3);
		$this->assertEquals(150, $versions[0]);
		$this->assertEquals(144, $versions[1]);
		$this->assertEquals(130, $versions[2]);
	}

	function testLatestVersion() {
		$this->assertNotNull(PackageRepository::getLatestVersion("SMWHalo"));
	}

	function testExistsVersion() {
		$this->assertTrue(PackageRepository::existsPackage("SMWHalo"));
	}

	function testExistsVersion2() {
		$this->assertTrue(PackageRepository::existsPackage("SMWHalo", 150));
	}

	function testExistsVersion3() {
		$this->assertFalse(PackageRepository::existsPackage("SMWHalo", 170));
	}

	function testLocalPackageRepository() {
		$exp_packages = array('smwhalo', 'semanticgardening', 'smw', 'mw');
        
		$packages = PackageRepository::getLocalPackages(self::$instDir.'/extensions');
		$this->assertTrue(count($packages) === 4);
		foreach($packages as $p) {
			$this->assertContains($p->getID(), $exp_packages);
		}

			
	}

}