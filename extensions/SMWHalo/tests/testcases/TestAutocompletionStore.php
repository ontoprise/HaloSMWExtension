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

global $smwgHaloIP;
require_once($smwgHaloIP.'/includes/SMW_Autocomplete.php');

/**
 *
 * @file
 * @ingroup SMWHaloTests
 *
 * Tests the auto-completion storage layer
 * @author Kai K�hn
 */
class TestAutocompletionStore extends PHPUnit_Framework_TestCase {


	function setUp() {
		global $smwgHaloWebserviceEndpoint;
		unset($smwgHaloWebserviceEndpoint);
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
		$exp_values = array("Electric car","Elephant","Has Electric engine");

		$values = smwfGetAutoCompletionStore()->getPages("el");

		foreach ($values as $v) {
			$title = $this->getTitle($v);
			$this->assertContains($title->getText(), $exp_values, $title->getText()." missing");
		}
	}

	function testGetPagesWithNamespace() {
		$exp_values = array("Has domain", "Has domain and range");

		$values = smwfGetAutoCompletionStore()->getPages("ai", array(SMW_NS_PROPERTY));

		foreach ($values as $v) {
			$title = $this->getTitle($v);
			$this->assertContains($title->getText(), $exp_values, $title->getText()." missing");
		}
	}

	function testGetPropertyWithType() {
		$exp_values = array("Has torsional moment");

		$values = smwfGetAutoCompletionStore()->getPropertyWithType("tor", "N");
			
		foreach ($values as $v) {
			$title = $this->getTitle($v);
			$this->assertContains($title->getText(), $exp_values, $title->getText()." missing");
		}
	}


	function testGetInstanceAsTarget() {
			
		$exp_values = array("Kai");
        $propertyDi = SMWDIProperty::newFromUserLabel(SMWHaloPredefinedPages::$HAS_DOMAIN_AND_RANGE->getText());
		$domainRangeAnnotations = smwfGetStore()->getPropertyValues(SMWDIWikiPage::newFromTitle(Title::newFromText("Has Child", SMW_NS_PROPERTY)), $propertyDi);
		$values = smwfGetAutoCompletionStore()->getInstanceAsTarget("K", $domainRangeAnnotations);

		foreach ($values as $v) {
				$title = $this->getTitle($v);
			$this->assertContains($title->getText(), $exp_values, $title->getText()." missing");
		}
	}

	function testGetPropertyForInstance() {
		$exp_values = array("Has Engine");
		$exp_values2 = array("Has Engine" => false);

		$values = smwfGetAutoCompletionStore()->getPropertyForInstance("engine", Title::newFromText("5 cylinder", NS_MAIN), false);

		foreach ($values as $v) {
			list($t, $inferred) = $v;
			$this->assertContains($t->getText(), $exp_values, $t->getText()." missing");
			$this->assertEquals($exp_values2[$t->getText()], $inferred);
		}
	}

	function testGetPropertyForInstanceInferred() {
		$exp_values = array("Has Engine");
		$exp_values2 = array("Has Engine" => true);

		$values = smwfGetAutoCompletionStore()->getPropertyForInstance("engine", Title::newFromText("Peugeot", NS_MAIN), true);

		foreach ($values as $v) {
			list($t, $inferred) = $v;
			$this->assertContains($t->getText(), $exp_values, $t->getText()." missing");
			$this->assertEquals($exp_values2[$t->getText()], $inferred);
		}
	}

	function testGetPropertyForCategory() {
		$exp_values = array("Has Engine", "Has Voltage");
		$exp_values2 = array("Has Engine" => false, "Has Voltage" => true);
		$values = smwfGetAutoCompletionStore()->getPropertyForCategory("Has", Title::newFromText("Car", NS_CATEGORY));

		foreach ($values as $v) {
			list($t, $inferred) = $v;
			$this->assertContains($t->getText(), $exp_values, $t->getText()." missing");
			$this->assertEquals($exp_values2[$t->getText()], $inferred);
		}

	}

	function testGetPropertyForAnnotation() {
		$exp_values = array("Has Engine", "Has Voltage");
		$exp_values2 = array("Has Engine" => false, "Has Voltage" => false);
		$values = smwfGetAutoCompletionStore()->getPropertyForAnnotation("Has", Title::newFromText("Electric car", NS_CATEGORY));
		 
		foreach ($values as $v) {
			$t = $v['title'];
			$inferred = $v['inferred'];
			$this->assertContains($t->getText(), $exp_values, $t->getText()." missing");
			$this->assertEquals($exp_values2[$t->getText()], $inferred);
		}

	}

	function testGetPropertyForAnnotationInferred() {
		$exp_values = array("Has Engine", "Has Voltage");
		$exp_values2 = array("Has Engine" => true, "Has Voltage" => true);
		$values = smwfGetAutoCompletionStore()->getPropertyForAnnotation("Has", Title::newFromText("Hybrid car", NS_CATEGORY));
		 
		foreach ($values as $v) {
			$t = $v['title'];
            $inferred = $v['inferred'];
			$this->assertContains($t->getText(), $exp_values, $t->getText()." missing");
			$this->assertEquals($exp_values2[$t->getText()], $inferred);
		}

	}

	function testGetValueForAnnotation() {
		$exp_values = array("3 cylinder", "4 cylinder", "5 cylinder");
		$values = smwfGetAutoCompletionStore()->getValueForAnnotation("cyl", Title::newFromText("Has Engine", SMW_NS_PROPERTY));
		foreach ($values as $v) {
			$t = $v['title'];
            $inferred = $v['inferred'];
			$text = is_string($v) ? $v : $t->getText();
			$this->assertContains($text, $exp_values, $text." missing");
		}

	}

	function testGetValueForAnnotationInferred() {
		$exp_values = array("Jack");
		$values = smwfGetAutoCompletionStore()->getValueForAnnotation("jack", Title::newFromText("Has Child", SMW_NS_PROPERTY));
		foreach ($values as $v) {
			$t = $v['title'];
            $inferred = $v['inferred'];
			$text = is_string($v) ? $v : $t->getText();
			$this->assertContains($text, $exp_values, $text." missing");
		}

	}

	private function getTitle($acResult) {
		if ($acResult instanceof Title) {
			$title = $acResult;
		} else {
			return $acResult['title'];
		}
		return $title;
	}
}
