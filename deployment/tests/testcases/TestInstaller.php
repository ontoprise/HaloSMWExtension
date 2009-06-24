<?php
// activate for debugging
//define('DEBUG_MODE', true);

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
	var $instPath;

	function setUp() {
		$path = defined('DEBUG_MODE') && DEBUG_MODE == true ? "deployment/tests/testcases/resources/installer/repository.xml" : "testcases/resources/installer/repository.xml";
		PackageRepository::initializePackageRepositoryFromString(file_get_contents($path));
		$this->installer = new Installer(dirname($path));
		$this->instPath = Tools::isWindows() ? 'c:\temp\install_test' : '/tmp/install_test';
		if (!file_exists($this->instPath)) Tools::mkpath($this->instPath);
		$this->installer->setInstDir($this->instPath);
		copy(dirname($path)."/LocalSettings.php", $this->instPath."/LocalSettings.php");
	}

	function tearDown() {
		Tools::remove_dir(Tools::isWindows() ? 'c:/temp/mw_deploy_tool' : '/tmp/mw_deploy_tool');
		Tools::remove_dir(Tools::isWindows() ? 'c:/temp/install_test' : '/tmp/install_test');
	}

	function testNewInstallation() {
		$this->installer->installPackage('UnifiedSearch');
		$this->assertTrue(file_exists($this->instPath."/extensions/UnifiedSearch"));
		 
		$lsText = file_get_contents($this->instPath."/LocalSettings.php");
		$this->assertTrue(strpos($lsText, "/*start-UnifiedSearch*/") !== false);
		 
	}

	function testInstallation() {
		$this->installer->installPackage('SemanticGardening', 120);
		$this->assertTrue(file_exists($this->instPath."/extensions/SMWHalo"));
		$this->assertTrue(file_exists($this->instPath."/extensions/SemanticGardening"));
		$lsText = file_get_contents($this->instPath."/LocalSettings.php");
		$this->assertTrue(strpos($lsText, "/*start-SMWHalo*/") !== false);
		$this->assertTrue(strpos($lsText, "/*start-SemanticGardening*/") !== false);
	}

	function testLatestInstallation() {
		$this->installer->installPackage('SemanticGardening');
		$this->assertTrue(file_exists($this->instPath."/extensions/SMWHalo"));
		$this->assertTrue(file_exists($this->instPath."/extensions/SemanticGardening"));
		$lsText = file_get_contents($this->instPath."/LocalSettings.php");
		$this->assertTrue(strpos($lsText, "/*start-SMWHalo*/") !== false);
		$this->assertTrue(strpos($lsText, "/*start-SemanticGardening*/") !== false);
	}

}