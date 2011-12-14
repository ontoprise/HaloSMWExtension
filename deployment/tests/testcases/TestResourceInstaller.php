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

define('LOD_NS_MAPPING', 250);

global $rootDir;
$rootDir = dirname(__FILE__);
$rootDir = str_replace("\\", "/", $rootDir);
$rootDir = realpath($rootDir."/../../");

require_once $rootDir.'/descriptor/DF_DeployDescriptor.php';
require_once $rootDir.'/tools/smwadmin/DF_ResourceInstaller.php';
require_once ($rootDir.'/io/DF_PrintoutStream.php');

/**
 * Tests the resource installer
 *
 */
class TestResourceInstaller extends PHPUnit_Framework_TestCase {
	var $ddp;
	var $ri;


	function setUp() {
		global $dfgOut;
		$dfgOut = DFPrintoutStream::getInstance(DF_OUTPUT_FORMAT_TEXT);
		$xml = file_get_contents('testcases/resources/test_deploy_variables.xml');
		$this->ddp = new DeployDescriptor($xml);
		$path = defined('DEBUG_MODE') && DEBUG_MODE == true ? "deployment/tests/testcases/resources/installer/" : "testcases/resources/installer/";
		$this->ri = ResourceInstaller::getInstance(realpath($path));
	}

	public function testInstallMappings() {
		$importedMappings = $this->ri->installOrUpdateMappings($this->ddp, true);

		list($source, $target, $content) = $importedMappings[0];
		$this->assertEquals("dbpedia", $source);
		list($source, $target, $content) = $importedMappings[1];
		$this->assertEquals("freebase", $source);
	}

	//TODO: add tests for other functionality
}
