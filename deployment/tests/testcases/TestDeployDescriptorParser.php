<?php

require_once ('../descriptor/DeployDescriptorParser.php');

/**
 * Tests the deploy descriptor processor
 *
 */
class TestDeployDescriptorParser extends PHPUnit_Framework_TestCase {

	var $ddp;

	function setUp() {
		$this->ddp = new DeployDescriptorParser('testcases/resources/test_deploy.xml');
	}

	function tearDown() {

	}

	function testGlobals() {
		$exp_deps = array("SemanticMediawiki");
		$this->assertEquals("SMWHalo", $this->ddp->getID());
		$this->assertEquals("Ontoprise GmbH", $this->ddp->getVendor());
		$this->assertEquals("extensions\SMWHalo", $this->ddp->getInstallationDirectory());
		$this->assertEquals("Enhances your Semantic Mediawiki", $this->ddp->getDescription());
		foreach($this->ddp->getDependencies() as $dep) {
			$this->assertContains($dep, $exp_deps);
		}
	}

	function testCodeFiles() {
		$exp_files = array("extensions/SMWHalo/SMW_Initialize.php", "extensions/SMWHalo/SMW_QP_XML.php");
		foreach($this->ddp->getCodefiles() as $cf) {
			$this->assertContains($cf, $exp_files);
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
}
?>