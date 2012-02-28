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
		$exp_deps = array(array("semanticmediawiki", "1.0.0", "1.4.0"), array("smwhalo", "1.0.0", "1.4.0"));
		$this->assertEquals("smwhalo", $this->ddp->getID());
		$this->assertEquals("Ontoprise GmbH", $this->ddp->getVendor());
		$this->assertEquals("extensions/SMWHalo", $this->ddp->getInstallationDirectory());
		$this->assertEquals("Enhances your Semantic Mediawiki", $this->ddp->getDescription());
		$deps = $this->ddp->getDependencies();
		$d0 = reset($deps);
		$d1 = next($deps);
		$this->assertDependency($d0, $exp_deps[0]);
		$this->assertDependency($d1, $exp_deps[1]);

	}

	private function assertDependency($act_dep, $exp_dep) {
		
		$this->assertEquals($exp_dep[0], reset($act_dep->getIDs()));
		$this->assertEquals($exp_dep[1], $act_dep->getMinVersion()->toVersionString());
		$this->assertEquals($exp_dep[2], $act_dep->getMaxVersion()->toVersionString());
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

		$this->assertEquals("patch.txt", $patches[0]->getPatchfile());

	}

	function testUpdateSection() {
		$xml = file_get_contents('testcases/resources/test_deploy_variables.xml');
		$this->ddp = new DeployDescriptor($xml, new DFVersion("1.4.2"));
			
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
	
	function testFromJSON() {
		$json = <<<ENDS
		{
  "deploydescriptor": {
    "id": "http://myontology111xxx",
    "dependencies": [
      [
        "smw",
        "1.6.1",
        "1.6.2",
        "true"
      ],
      [
        "smwhalo",
        "1.6.1",
        "1.6.2",
        "true"
      ]
    ],
    "description": "description sample text: 777",
    "vendor": "555 GmbH",
    "instdir": "extensions/wiki666xxx",
    "patchlevel": 333,
    "license": "GPL-v999",
    "maintainer": "444 GmbH",
    "helpURL": "http://smwplus.com/888",
    "version": "222.0.0xxx"
  },
  "ontology_uri": "http://xmlns.com/foaf/0.1/",
  "bundle_id": "http://myontology111xxx",
  "base_uri": "http://halowiki/ob"
}
ENDS;
    $o = json_decode($json);
     
     $dd = DeployDescriptor::fromJSON($o->deploydescriptor);
     $this->assertEquals($dd->getID(), "http://myontology111xxx");
      $this->assertTrue(count($dd->getDependencies()) > 0);
	}
}
