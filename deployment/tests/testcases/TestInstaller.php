<?php
// activate for debugging
define('DEBUG_MODE', true);

if (defined('DEBUG_MODE') && DEBUG_MODE == true) {
	require_once 'deployment/tools/smwadmin/Installer.php';
	require_once 'deployment/tools/smwadmin/Tools.php';
} else {
	require_once '../tools/smwadmin/Installer.php';
	require_once '../tools/smwadmin/Tools.php';

}

/**
 * Tests the installer clazz
 *
 */
class TestInstaller extends PHPUnit_Framework_TestCase {

	var $installer;

	function setUp() {
		$path = defined('DEBUG_MODE') && DEBUG_MODE == true ? "deployment/tests/testcases/resources/installer/repository.xml" : "testcases/resources/installer/repository.xml";
		PackageRepository::initializePackageRepositoryFromString(file_get_contents($path));
		$this->installer = new Installer(dirname($path));
		$instPath = Tools::isWindows() ? 'c:\temp\install_test' : '/tmp/install_test';
		Tools::mkpath($instPath);
		$this->installer->setInstDir($instPath);
	}

	function tearDown() {
		Tools::remove_dir(Tools::isWindows() ? 'c:/temp/mw_deploy_tool' : '/tmp/mw_deploy_tool');
		Tools::remove_dir(Tools::isWindows() ? 'c:/temp/install_test' : '/tmp/install_test');
	}


	function testInstallation() {
		$this->installer->installPackage('SemanticGardening', 120);
	}

	 

}