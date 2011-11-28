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



require_once ('deployment/descriptor/DF_DeployDescriptor.php');

/**
 * Tests the deploy descriptor processor
 *
 */
class TestDeployDescriptor extends PHPUnit_Framework_TestCase {

	var $ddp;

	function setUp() {
		$xml = file_get_contents('testcases/resources/test_deploy_variables.xml');
		$this->ddp = new DeployDescriptor($xml);
	}

	function tearDown() {

	}

	function testGlobals() {
		$exp_deps = array(array("semanticmediawiki", 100, 140), array("smwhalo", 100, 140));
		$this->assertEquals("smwhalo", $this->ddp->getID());
		$this->assertEquals("Ontoprise GmbH", $this->ddp->getVendor());
		$this->assertEquals("extensions/SMWHalo", $this->ddp->getInstallationDirectory());
		$this->assertEquals("Enhances your Semantic Mediawiki", $this->ddp->getDescription());
		$deps = $this->ddp->getDependencies();
		$this->assertDependency($deps[0], $exp_deps[0]);
		$this->assertDependency($deps[1], $exp_deps[1]);

	}

	private function assertDependency($exp_dep, $act_dep) {
		list($depID, $depFrom, $depTo) = $exp_dep;
		list($depID2, $depFrom2, $depTo2) = $act_dep;
		$this->assertEquals($depID, $depID2);
		$this->assertEquals($depFrom, $depFrom2);
		$this->assertEquals($depTo, $depTo2);
	}

	function testCodeFiles() {
		$exp_files = array("extensions/SMWHalo/SMW_Initialize.php", "extensions/SMWHalo/SMW_QP_XML.php");
		foreach($this->ddp->getCodefiles() as $loc) {

			$this->assertContains($loc, $exp_files);
		}
	}

	function testWikidumps() {
		$exp_files = array("wikidumps/ontology1.xml");
		foreach($this->ddp->getWikidumps() as $cf) {
			$this->assertContains($cf, $exp_files);
		}
	}

	function testResources() {
		$exp_files = array("resources/img1.png");
		foreach($this->ddp->getResources() as $cf) {
			$this->assertContains($cf, $exp_files);
		}
	}

	function testOnlyCopyResources() {
		$exp_locs = array("resources/thumb/pic-300px.png");
		$exp_dests = array("thumb/pic.300px.png");
		foreach($this->ddp->getOnlyCopyResources() as $loc => $dest) {

			$this->assertContains($loc, $exp_locs);
			$this->assertContains($dest, $exp_dests);
		}
	}

	function testUserRequirements() {
		$reqs = $this->ddp->getUserRequirements() ;

		$this->assertEquals("string", $reqs['avalue'][0]);
		$this->assertEquals("Required value", $reqs['avalue'][1]);
	}

	function testSetups() {
		$setups = $this->ddp->getInstallScripts();
		$this->assertEquals("maintenance/setup.php", $setups[0]['script']);
		$this->assertEquals("param1 param2", $setups[0]['params']);
	}

	function testPatches() {
		$patches = $this->ddp->getPatches(array('smwhalo' => $this->ddp));
		 
		$path = $patches[0]->getPatchfile();
		$mayFail = $patches[0]->mayFail();
		$this->assertEquals("patch.txt", $path);

	}

	function testUninstallPatches() {

		$patches = $this->ddp->getUninstallPatches(array('smwhalo' => $this->ddp));

		$this->assertEquals("patch.txt", $patches[0]);

	}

	function testUpdateSection() {
		$xml = file_get_contents('testcases/resources/test_deploy_variables.xml');
		$this->ddp = new DeployDescriptor($xml, 142);
			
		$configs = $this->ddp->getConfigs();
		$this->assertTrue(count($configs) > 0);
	}

	function testMappings() {
		$xml = file_get_contents('testcases/resources/test_deploy_variables.xml');
		$this->ddp = new DeployDescriptor($xml);

		$mappings = $this->ddp->getMappings();
			
		$this->assertTrue(count($mappings) == 2);
		list($loc, $target) = $mappings['dbpedia'][0];
		$this->assertEquals("mappings/mapping1.map", $loc);
		$this->assertEquals("wiki", $target);
		list($loc, $target) = $mappings['dbpedia'][1];
		$this->assertEquals("mappings/mapping2.map", $loc);
		$this->assertEquals("wiki", $target);
		list($loc, $target) = $mappings['freebase'][0];
		$this->assertEquals("mappings/mapping3.map", $loc);
		$this->assertEquals("wiki", $target);
	}
}
