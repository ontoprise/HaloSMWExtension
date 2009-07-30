<?php
global $smwgHaloIP;
require_once($smwgHaloIP.'/includes/SMW_Autocomplete.php');

/**
 * Tests the auto-completion storage layer
 *
 */
class TestAutocompletionStore extends PHPUnit_Framework_TestCase {


	function setUp() {

	}

	function tearDown() {

	}

	function testGetUnits() {
		$exp_units = array("N", "Newton");
		$p = Title::newFromText("Has_torsional_moment", SMW_NS_PROPERTY);
		$units = smwfGetAutoCompletionStore()->getUnits($p, "N");

		foreach ($units as $u) {
			$this->assertContains($u, $exp_units, $u." missing");
		}
	}

	function testGetPossibleValues() {
		$exp_values = array("Male", "Female");
		$p = Title::newFromText("Gender", SMW_NS_PROPERTY);
		$values = smwfGetAutoCompletionStore()->getPossibleValues($p);

		foreach ($values as $v) {
			$this->assertContains($v, $exp_values, $v." missing");
		}
	}

	function testGetPages() {
		$exp_values = array("Electric car","Elephant","Engine", "Europe");

		$values = smwfGetAutoCompletionStore()->getPages("e");

		foreach ($values as $v) {
			$this->assertContains($v->getText(), $exp_values, $v->getText()." missing");
		}
	}
	
    function testGetPagesWithNamespace() {
        $exp_values = array("Kai","Main Page");

        $values = smwfGetAutoCompletionStore()->getPages("ai", array(NS_INSTANCE));

        foreach ($values as $v) {
            $this->assertContains($v->getText(), $exp_values, $v->getText()." missing");
        }
    }

	function testGetPropertyWithType() {
		$exp_values = array("Has torsional moment");

		$values = smwfGetAutoCompletionStore()->getPropertyWithType("tor", "N");
		 
		foreach ($values as $v) {
			$this->assertContains($v->getText(), $exp_values, $v->getText()." missing");
		}
	}

	function testGetPropertyForInstance() {
		$exp_values = array("Has Engine");

		$values = smwfGetAutoCompletionStore()->getPropertyForInstance("engine", Title::newFromText("5 cylinder", NS_MAIN), false);

		foreach ($values as $v) {
			$this->assertContains($v->getText(), $exp_values, $v->getText()." missing");
		}
	}

	function testGetInstanceAsTarget() {
		 
		$exp_values = array("Kai");
		
		$domainRangeAnnotations = smwfGetStore()->getPropertyValues(Title::newFromText("Has Child", SMW_NS_PROPERTY), smwfGetSemanticStore()->domainRangeHintProp);
		$values = smwfGetAutoCompletionStore()->getInstanceAsTarget("K", $domainRangeAnnotations);
		
		foreach ($values as $v) {
			$this->assertContains($v->getText(), $exp_values, $v->getText()." missing");
		}
	}
}
?>