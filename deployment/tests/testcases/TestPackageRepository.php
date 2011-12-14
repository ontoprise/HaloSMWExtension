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

define("DEPLOY_FRAMEWORK_INTERNAL_REPO", "http://localhost/mediawiki/deployment/tests/testcases/resources/repository/");
define("DEPLOY_FRAMEWORK_INTERNAL_REPO2", "http://localhost/mediawiki/deployment/tests/testcases/resources/repository2/");

global $rootDir;
$rootDir = dirname(__FILE__);
$rootDir = str_replace("\\", "/", $rootDir);
$rootDir = realpath($rootDir."/../../");

require_once $rootDir.'/tools/smwadmin/DF_PackageRepository.php';
require_once ($rootDir.'/io/DF_PrintoutStream.php');

/**
 * Tests the installer clazz
 *
 */
class TestPackageRepository extends PHPUnit_Framework_TestCase {

	static $rootDir;
	static $instDir;

	function setUp() {
		global $dfgOut;
		$dfgOut = DFPrintoutStream::getInstance(DF_OUTPUT_FORMAT_TEXT);
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
		try {
			PackageRepository::getVersion("smwhalo",170);
			$this->assertTrue(false);
		} catch(RepositoryError $e) {
			$this->assertTrue(true);
		}
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
		$this->assertEquals(new DFVersion("1.4.4"), $dd->getVersion());
	}

	function testGetLatestDeployDescriptor2() {
		//introduce second repository
		$path = defined('DEBUG_MODE') && DEBUG_MODE == true ? "deployment/tests/testcases/resources/repository2/repository2.xml" : "testcases/resources/repository2/repository2.xml";
		PackageRepository::initializePackageRepositoryFromString(file_get_contents($path), DEPLOY_FRAMEWORK_INTERNAL_REPO2);

		$dd = PackageRepository::getLatestDeployDescriptor("smwhalo");
		$this->assertEquals("smwhalo", $dd->getID());
		$this->assertEquals(new DFVersion("1.5.0"), $dd->getVersion());
	}


	function testGetDeployDescriptor() {
		$dd = PackageRepository::getDeployDescriptor("smwhalo", 144);
		$this->assertEquals("smwhalo", $dd->getID());
		$this->assertEquals(new DFVersion("1.4.4"), $dd->getVersion());
	}

	function testGetDeployDescriptor2() {

		//introduce second repository
		$path = defined('DEBUG_MODE') && DEBUG_MODE == true ? "deployment/tests/testcases/resources/repository2/repository2.xml" : "testcases/resources/repository2/repository2.xml";
		PackageRepository::initializePackageRepositoryFromString(file_get_contents($path), DEPLOY_FRAMEWORK_INTERNAL_REPO2);


		$dd = PackageRepository::getDeployDescriptor("smwhalo", 150);
		$this->assertEquals("smwhalo", $dd->getID());
		$this->assertEquals(new DFVersion("1.5.0"), $dd->getVersion());
	}

	function testLocalPackageRepository() {
		$exp_packages = array('smwhalo', 'semanticgardening', 'smw', 'mw');

		$packages = PackageRepository::getLocalPackages(self::$instDir);

		$this->assertTrue(count($packages) >= 4);

			
	}

}
