<?php
class TestQuadStorage extends PHPUnit_Framework_TestCase {

	function setUp() {

	}

	function tearDown() {

	}

	function testGetAllPropertyAnnotations() {
		$annotations = smwfGetStore()->getAllPropertyAnnotations(
		SMWPropertyValue::makeUserProperty("Located In"));

		$this->assertEquals(14, count($annotations));
		$this->assertEquals("Austria", reset($annotations[0])->getTitle()->getText());
		$this->assertEquals("Baltimore", reset($annotations[1])->getTitle()->getText());
		$this->assertEquals("Berlin", reset($annotations[2])->getTitle()->getText());
		$this->assertEquals("Bern", reset($annotations[3])->getTitle()->getText());
		$this->assertEquals("Boston", reset($annotations[4])->getTitle()->getText());
		$this->assertEquals("Bremen", reset($annotations[5])->getTitle()->getText());
		$this->assertEquals("Germany", reset($annotations[6])->getTitle()->getText());
		$this->assertEquals("Graz", reset($annotations[7])->getTitle()->getText());
		$this->assertEquals("Hamburg", reset($annotations[8])->getTitle()->getText());
		$this->assertEquals("Linz", reset($annotations[9])->getTitle()->getText());
		$this->assertEquals("Stuttgart", reset($annotations[10])->getTitle()->getText());
		$this->assertEquals("Switzerland", reset($annotations[11])->getTitle()->getText());
		$this->assertEquals("USA", reset($annotations[12])->getTitle()->getText());
		$this->assertEquals("Vienna", reset($annotations[13])->getTitle()->getText());
		}

		function testGetPropertyValues() {
		$annotations = smwfGetStore()->getPropertyValues(Title::newFromText("Berlin"),
		SMWPropertyValue::makeUserProperty("Located In"));

		$this->assertEquals(1, count($annotations));
		$this->assertEquals("Germany", $annotations[0]->getTitle()->getText());
		}

		function testGetPropertySubjectsWithWikiPageValue() {
		$property = SMWPropertyValue::makeUserProperty("Located In");
		$subjects = smwfGetStore()->getPropertySubjects(
		$property, SMWDataValueFactory::newPropertyObjectValue($property, "Germany"));
		$this->assertEquals("Berlin", $subjects[0]->getTitle()->getText());
		$this->assertEquals("Bremen", $subjects[1]->getTitle()->getText());
		$this->assertEquals("Hamburg", $subjects[2]->getTitle()->getText());
		$this->assertEquals("Stuttgart", $subjects[3]->getTitle()->getText());
		}

		function testGetPropertySubjectsWithNumericValue() {
		$property = SMWPropertyValue::makeUserProperty("Population");
		$subjects = smwfGetStore()->getPropertySubjects(
		$property, SMWDataValueFactory::newPropertyObjectValue($property, "548000"));
		$this->assertEquals("Bremen", $subjects[0]->getTitle()->getText());
		}

		function testGetAllPropertySubjectsWithNumericValue() {
		$property = SMWPropertyValue::makeUserProperty("Population");
		$subjects = smwfGetStore()->getAllPropertySubjects(
		$property);
			
		$this->assertEquals("Baltimore", $subjects[0]->getTitle()->getText());
		$this->assertEquals("Berlin", $subjects[1]->getTitle()->getText());
		$this->assertEquals("Bern", $subjects[2]->getTitle()->getText());
		$this->assertEquals("Bremen", $subjects[3]->getTitle()->getText());
		$this->assertEquals("Graz", $subjects[4]->getTitle()->getText());
		$this->assertEquals("Hamburg", $subjects[5]->getTitle()->getText());
		$this->assertEquals("Linz", $subjects[6]->getTitle()->getText());
		$this->assertEquals("Stuttgart", $subjects[7]->getTitle()->getText());
		$this->assertEquals("Vienna", $subjects[8]->getTitle()->getText());
		}

		function testGetInProperties() {
		$property = SMWPropertyValue::makeUserProperty("Located In");
		$properties = smwfGetStore()->getInProperties(
		SMWDataValueFactory::newPropertyObjectValue($property, "Germany"));
		$this->assertEquals("Located In", $properties[0]->getWikiPageValue()->getTitle()->getText());
		} 


	function testGetProperties() {
		$subject = Title::newFromText("Berlin");
		$properties = smwfGetStore()->getProperties($subject);
		
		$this->assertEquals("Located In", $properties[0]->getWikiPageValue()->getTitle()->getText());
		$this->assertEquals("Modification date", $properties[1]->getWikiPageValue()->getTitle()->getText());
		$this->assertEquals("Population", $properties[2]->getWikiPageValue()->getTitle()->getText());
		
	}
	
	function testGetSemanticData() {
		$subject = Title::newFromText("Berlin");
        $sd = smwfGetStore()->getSemanticData($subject);
        $this->assertTrue(array_key_exists('Located_In', $sd->getProperties()));
        $this->assertTrue(array_key_exists('Modification_date', $sd->getProperties()));
        $this->assertTrue(array_key_exists('Population', $sd->getProperties()));
        $properties = $sd->getProperties();
        $locatedIn = $properties['Located_In'];
        $values = $sd->getPropertyValues($locatedIn);
        $this->assertEquals("Germany", $values['Germany']->getTitle()->getText());
        
        $population = $properties['Population'];
        $values = $sd->getPropertyValues($population);
        $dbkey = $values['3450000.0']->getDBkeys();
        $this->assertEquals("3450000.0", $dbkey[0]);
       
	}
}