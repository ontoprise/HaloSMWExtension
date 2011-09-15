<?php
if (!defined("SMWH_FORCE_TS_UPDATE")) define("SMWH_FORCE_TS_UPDATE","1");
class TestQuadStorage extends PHPUnit_Framework_TestCase {

	function setUp() {

	}

	function tearDown() {

	}

	function testGetAllPropertyAnnotations() {

		$exp_values = array("Austria", "Baltimore", "Berlin", "Bern", "Boston",
	"Bremen", "Germany", "Graz", "Hamburg", "Linz", "Stuttgart", "Switzerland",
	"USA", "Vienna");
		$annotations = smwfGetStore()->getAllPropertyAnnotations(
		SMWDIProperty::newFromUserLabel("Located In"));

		$this->assertEquals(14, count($annotations));

		foreach($annotations as $a) {
			$title = reset($a);
			$this->assertContains($title->getTitle()->getText(), $exp_values, $title->getTitle()->getText()." missing");
		}


	}

	function testGetPropertyValues() {
		$annotations = smwfGetStore()->getPropertyValues(SMWDIWikiPage::newFromTitle(Title::newFromText("Berlin")),
		SMWDIProperty::newFromUserLabel("Located In"));

		$this->assertEquals(1, count($annotations));
		$this->assertEquals("Germany", $annotations[0]->getTitle()->getText());
	}

	function testGetPropertySubjectsWithWikiPageValue() {
		$expected = array("Berlin", "Bremen", "Hamburg", "Stuttgart");
		$property = SMWDIProperty::newFromUserLabel("Located In");
		$subjects = smwfGetStore()->getPropertySubjects(
		SMWDIProperty::newFromUserLabel("Located In"), SMWDIWikiPage::newFromTitle(Title::newFromText("Germany")));

		foreach($subjects as $di) {

			$this->assertContains($di->getTitle()->getText(), $expected, $di->getTitle()->getText()." missing");
		}
	}

	function testGetPropertySubjectsWithNumericValue() {
		$property = SMWDIProperty::newFromUserLabel("Population");
		$subjects = smwfGetStore()->getPropertySubjects(
		$property, new SMWDINumber(548000));

		$this->assertEquals("Bremen", $subjects[0]->getTitle()->getText());
	}

	function testGetAllPropertySubjectsWithNumericValue() {
		$expected = array("Baltimore", "Berlin", "Bern",
	"Bremen",  "Graz", "Hamburg", "Linz", "Stuttgart", "Vienna");

		$property = SMWDIProperty::newFromUserLabel("Population");
		$subjects = smwfGetStore()->getAllPropertySubjects(
		$property);

		foreach($subjects as $di) {

			$this->assertContains($di->getTitle()->getText(), $expected, $di->getTitle()->getText()." missing");
		}
	}

	function testGetInProperties() {
		$property = SMWDIProperty::newFromUserLabel("Located In");
		$properties = smwfGetStore()->getInProperties(
		SMWDIWikiPage::newFromTitle(Title::newFromText("Germany")));


		$this->assertEquals("Located In", $properties[0]->getDIWikiPage()->getTitle()->getText());
	}


	function testGetProperties() {
		$expected = array("Located_In", "_MDAT", "Population", "_INST" );
		$subject = Title::newFromText("Berlin");
		$properties = smwfGetStore()->getProperties(SMWDIWikiPage::newFromTitle($subject));

		foreach($properties as $p) {
			$title = reset($p);
			$this->assertContains($p->getKey(), $expected, $p->getKey()." missing");
		}



	}

	function testGetSemanticData() {
		$subject = Title::newFromText("Berlin");
		$sd = smwfGetStore()->getSemanticData(SMWDIWikiPage::newFromTitle($subject));
		$this->assertTrue(array_key_exists('Located_In', $sd->getProperties()));
		$this->assertTrue(array_key_exists('_MDAT', $sd->getProperties()));
		$this->assertTrue(array_key_exists('Population', $sd->getProperties()));
		$properties = $sd->getProperties();
		$locatedIn = $properties['Located_In'];
		$values = $sd->getPropertyValues($locatedIn);


		$this->assertEquals("Germany", reset($values)->getTitle()->getText());

		$population = $properties['Population'];
		$values = $sd->getPropertyValues($population);
		$number = reset($values)->getNumber();
		$this->assertEquals("3450000.0", $number);
			
	}

	function testGetSemanticData2() {
		$subject = Title::newFromText("Body Form", SMW_NS_PROPERTY);
		$sd = smwfGetStore()->getSemanticData(SMWDIWikiPage::newFromTitle($subject));
		$this->assertTrue(array_key_exists('Has_domain', $sd->getProperties()));
		$properties = $sd->getProperties();
		$dmr = $properties['Has_domain'];
		$values = $sd->getPropertyValues($dmr);
		if (count($values) > 0) {
			$domain = reset($values);
				
			$this->assertEquals("Category:Car", $domain->getTitle()->getPrefixedText());

		} else {
			$this->assertTrue(false);
		}


			
			
	}
}