<?php

define(DEPLOY_FRAMEWORK_INTERNAL_REPO, "http://localhost/mediawiki/deployment/tests/testcases/resources/repository/");
define(DEPLOY_FRAMEWORK_INTERNAL_REPO2, "http://localhost/mediawiki/deployment/tests/testcases/resources/repository2/");

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
		PackageRepository::initializePackageRepositoryFromString(file_get_contents($path), DEPLOY_FRAMEWORK_INTERNAL_REPO);
		self::$rootDir = realpath(dirname($path));
		$path = defined('DEBUG_MODE') && DEBUG_MODE == true ? "deployment/tests/testcases/resources/installer/" : "testcases/resources/installer/";
		self::$instDir = realpath($path);
	}

	function tearDown() {
		PackageRepository::clearPackageRepository();
	}


	function testGetVersion() {
		$this->assertNotNull(PackageRepository::getVersion("smwhalo",143));
	}

	function testGetVersion2() {
		$this->assertNull(PackageRepository::getVersion("smwhalo",170));
	}

	function testGetVersion3() {
		$versions = PackageRepository::getAllVersions("smwhalo");

		$this->assertTrue(count($versions) === 2);
		$this->assertEquals(144, $versions[0]);
		$this->assertEquals(143, $versions[1]);
	}

	function testGetVersion4() {

		//introduce second repository
		$path = defined('DEBUG_MODE') && DEBUG_MODE == true ? "deployment/tests/testcases/resources/repository2/repository2.xml" : "testcases/resources/repository2/repository2.xml";
		PackageRepository::initializePackageRepositoryFromString(file_get_contents($path), DEPLOY_FRAMEWORK_INTERNAL_REPO2);
		$versions = PackageRepository::getAllVersions("smwhalo");

		$this->assertTrue(count($versions) === 3);
		$this->assertEquals(150, $versions[0]);
		$this->assertEquals(144, $versions[1]);
		$this->assertEquals(143, $versions[2]);
	}

	function testLatestVersion() {
		$this->assertNotNull(PackageRepository::getLatestVersion("smwhalo"));
	}

	function testExistsVersion() {
		$this->assertTrue(PackageRepository::existsPackage("smwhalo"));
	}

	function testExistsVersion2() {
		$this->assertTrue(PackageRepository::existsPackage("smwhalo", 143));
	}

	function testExistsVersion3() {
		$this->assertFalse(PackageRepository::existsPackage("smwhalo", 170));
	}

	function testExistsVersion4() {

		//introduce second repository
		$path = defined('DEBUG_MODE') && DEBUG_MODE == true ? "deployment/tests/testcases/resources/repository2/repository2.xml" : "testcases/resources/repository2/repository2.xml";
		PackageRepository::initializePackageRepositoryFromString(file_get_contents($path), DEPLOY_FRAMEWORK_INTERNAL_REPO2);

		$this->assertTrue(PackageRepository::existsPackage("smwhalo", 150));
	}

	function testGetAllPackages() {
		$exp_packages = array('smwhalo', 'semanticgardening', 'smw', 'unifiedsearch');
		$allPackages = PackageRepository::getAllPackages();

		foreach($allPackages as $id => $versions) {
			$this->assertContains($id, $exp_packages);
		}

	}

	function testGetAllPackages2() {

		//introduce second repository
		$path = defined('DEBUG_MODE') && DEBUG_MODE == true ? "deployment/tests/testcases/resources/repository2/repository2.xml" : "testcases/resources/repository2/repository2.xml";
		PackageRepository::initializePackageRepositoryFromString(file_get_contents($path), DEPLOY_FRAMEWORK_INTERNAL_REPO2);

		$exp_packages = array('smwhalo', 'semanticgardening', 'smw', 'unifiedsearch', 'richmedia');
		$allPackages = PackageRepository::getAllPackages();

		foreach($allPackages as $id => $versions) {
			$this->assertContains($id, $exp_packages);
		}

	}

	function testGetLatestDeployDescriptor() {
		try {
		$dd = PackageRepository::getLatestDeployDescriptor("smwhalo");
		} catch(HttpError $e) {
			print $e->getHeader();
		}
		$this->assertEquals("smwhalo", $dd->getID());
		$this->assertEquals(144, $dd->getVersion());
	}

	function testGetLatestDeployDescriptor2() {
		//introduce second repository
		$path = defined('DEBUG_MODE') && DEBUG_MODE == true ? "deployment/tests/testcases/resources/repository2/repository2.xml" : "testcases/resources/repository2/repository2.xml";
		PackageRepository::initializePackageRepositoryFromString(file_get_contents($path), DEPLOY_FRAMEWORK_INTERNAL_REPO2);

		$dd = PackageRepository::getLatestDeployDescriptor("smwhalo");
		$this->assertEquals("smwhalo", $dd->getID());
		$this->assertEquals(150, $dd->getVersion());
	}


	function testGetDeployDescriptor() {
		$dd = PackageRepository::getDeployDescriptor("smwhalo", 144);
		$this->assertEquals("smwhalo", $dd->getID());
		$this->assertEquals(144, $dd->getVersion());
	}

	function testGetDeployDescriptor2() {

		//introduce second repository
		$path = defined('DEBUG_MODE') && DEBUG_MODE == true ? "deployment/tests/testcases/resources/repository2/repository2.xml" : "testcases/resources/repository2/repository2.xml";
		PackageRepository::initializePackageRepositoryFromString(file_get_contents($path), DEPLOY_FRAMEWORK_INTERNAL_REPO2);


		$dd = PackageRepository::getDeployDescriptor("smwhalo", 150);
		$this->assertEquals("smwhalo", $dd->getID());
		$this->assertEquals(150, $dd->getVersion());
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