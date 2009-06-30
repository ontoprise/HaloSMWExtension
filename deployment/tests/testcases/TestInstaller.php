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

	// temporary directory used for installation
	var $instPath;

	// mediawiki root
	var $local_path;

	function setUp() {
		$repo_path = defined('DEBUG_MODE') && DEBUG_MODE == true ? "deployment/tests/testcases/resources/repository/repository.xml" : "testcases/resources/repository/repository.xml";
		$this->local_path = defined('DEBUG_MODE') && DEBUG_MODE == true ? "deployment/tests/testcases/resources/installer" : "testcases/resources/installer";
		PackageRepository::initializePackageRepositoryFromString(file_get_contents($repo_path));
		$this->installer = new Installer($this->local_path);
		$this->instPath = Tools::isWindows() ? 'c:\temp\install_test' : '/tmp/install_test';
		if (!file_exists($this->instPath)) Tools::mkpath($this->instPath);
		$this->installer->setInstDir($this->instPath);
		copy($this->local_path."/LocalSettings.php", $this->instPath."/LocalSettings.php");
	}

	function tearDown() {
		Tools::remove_dir(Tools::isWindows() ? 'c:/temp/mw_deploy_tool' : '/tmp/mw_deploy_tool');
		Tools::remove_dir(Tools::isWindows() ? 'c:/temp/install_test' : '/tmp/install_test');
	}

	function testMediawikiVersion() {
		$version = Tools::getMediawikiVersion($this->local_path);
		$this->assertEquals('1.13.2', $version);
	}

	

	function testInstallation() {
		$this->installer->installOrUpdate('SemanticGardening', 120);
		$this->assertTrue(file_exists($this->instPath."/extensions/SMWHalo"));
		$this->assertTrue(file_exists($this->instPath."/extensions/SemanticGardening"));
		$lsText = file_get_contents($this->instPath."/LocalSettings.php");
		$this->assertTrue(strpos($lsText, "/*start-SMWHalo*/") !== false);
		$this->assertTrue(strpos($lsText, "/*start-SemanticGardening*/") !== false);
	}

	
}