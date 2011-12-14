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
// activate for debugging
//define('DEBUG_MODE', true);

if (defined('DEBUG_MODE') && DEBUG_MODE == true) {
	require_once 'deployment/tools/smwadmin/DF_Installer.php';
	require_once 'deployment/tools/smwadmin/DF_Tools.php';
} else {
	require_once '../tools/smwadmin/DF_Installer.php';
	require_once '../tools/smwadmin/DF_Tools.php';

}

/**
 * Tests the installer clazz
 *
 */
class TestInstaller extends PHPUnit_Framework_TestCase {

	var $installer;

	// temporary directory used for installation
	var $instPath;
	// temporary directory used for downloads
	var $tmpPath;
	// mediawiki root (simulates an existing installation)
	var $local_path;

	function setUp() {

		global $dfgNoAsk;
		$dfgNoAsk = true;
		// repo path points to a directory containing a test repository
		$repo_path = defined('DEBUG_MODE') && DEBUG_MODE == true ? "deployment/tests/testcases/resources/repository/repository.xml" : "testcases/resources/repository/repository.xml";
		// local path points to a directory containing a simulated local installation.
		$this->local_path = defined('DEBUG_MODE') && DEBUG_MODE == true ? "deployment/tests/testcases/resources/installer" : "testcases/resources/installer";
		PackageRepository::initializePackageRepositoryFromString(file_get_contents($repo_path));
		$this->installer = Installer::getInstance($this->local_path, true);

		// create temporary directory for downloads and installation
		$this->instPath = Tools::isWindows() ? 'c:\temp\install_test' : '/tmp/install_test';
		$this->tmpPath = Tools::isWindows() ? 'c:/temp/mw_deploy_tool' : '/tmp/mw_deploy_tool';
		if (!file_exists($this->instPath)) Tools::mkpath($this->instPath);
		if (!file_exists($this->tmpPath)) Tools::mkpath($this->tmpPath);

		// set installation dir and copy a dummy LocalSettings.php in it.
		$this->installer->setInstDir($this->instPath);
		copy($this->local_path."/LocalSettings.php", $this->instPath."/LocalSettings.php");
	}

	function tearDown() {

		Tools::remove_dir($this->tmpPath );
		Tools::remove_dir($this->instPath);
	}
	function testNewInstallation() {
		$this->installer->installOrUpdate('unifiedsearch');
		print "\ninstallation finsish";
		$this->assertTrue(file_exists($this->instPath."/extensions/UnifiedSearch"));
			
		$lsText = file_get_contents($this->instPath."/LocalSettings.php");
		$this->assertTrue(strpos($lsText, "/*start-unifiedsearch*/") !== false);
			
	}

	function testInstallation() {

		$this->installer->installOrUpdate('semanticgardening', 120);
		$this->assertTrue(file_exists($this->instPath."/extensions/SMWHalo"));
		$this->assertTrue(file_exists($this->instPath."/extensions/SemanticGardening"));
		$lsText = file_get_contents($this->instPath."/LocalSettings.php");
		$this->assertTrue(strpos($lsText, "/*start-smwhalo*/") !== false);
		$this->assertTrue(strpos($lsText, "/*start-semanticgardening*/") !== false);
	}


	function testLatestInstallation() {
		$this->installer->installOrUpdate('semanticgardening');
		$this->assertTrue(file_exists($this->instPath."/extensions/SMWHalo"));
		$this->assertTrue(file_exists($this->instPath."/extensions/SemanticGardening"));
		$lsText = file_get_contents($this->instPath."/LocalSettings.php");
		$this->assertTrue(strpos($lsText, "/*start-smwhalo*/") !== false);
		$this->assertTrue(strpos($lsText, "/*start-semanticgardening*/") !== false);
	}

	function testUpdate() {
		$this->installer->updateAll();
		$content = file_get_contents($this->instPath.'/LocalSettings.php');
		$this->assertTrue(strpos($content, '$testforupdate') !== false);
	}
	function testRemoveExtension() {
		// install Unified Search
		$this->installer->installOrUpdate('semanticgardening', 120);
		$this->assertTrue(file_exists($this->instPath."/extensions/SMWHalo"));
		$this->assertTrue(file_exists($this->instPath."/extensions/SemanticGardening"));
		$lsText = file_get_contents($this->instPath."/LocalSettings.php");
		$this->assertTrue(strpos($lsText, "/*start-smwhalo*/") !== false);
		$this->assertTrue(strpos($lsText, "/*start-semanticgardening*/") !== false);


		$this->installer->deInstall('semanticgardening');
		$this->assertTrue(!file_exists($this->instPath."/extensions/SemanticGardening"));

		$lsText = file_get_contents($this->instPath."/LocalSettings.php");
		$this->assertTrue(strpos($lsText, "/*start-semanticgardening*/") === false);
	}

	function testMediawikiVersion() {
		$version = Tools::getMediawikiVersion($this->local_path);
		$this->assertEquals('1.13.2', $version);
	}



}
