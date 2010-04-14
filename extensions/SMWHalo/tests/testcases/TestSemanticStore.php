<?php

/**
 * @file
 * @ingroup SMWHaloTests 
 * 
 * Tests the semantic storage layer of HALO.
 * @author Kai Kühn
 * 
 */
class TestSemanticStore extends PHPUnit_Framework_TestCase {


	function setUp() {

	}

	function tearDown() {
			
	}

	function testGetRootCategories() {
		$exp_categories = array("Transitive properties","Symmetrical properties", "GardeningLog", "Engine", "Car", "Animal",
		                        "City", "Continent", "Country");
		$rootCategories = smwfGetSemanticStore()->getRootCategories();

		foreach ($rootCategories as $tuple) {
			list($c, $isLeaf) = $tuple;
			$this->assertContains($c->getText(), $exp_categories, $c->getText()." missing");
		}
			
	}

	function testGetRootProperties() {
		$exp_properties = array("Body Form",
								 "Description",
								 "DiscourseState",
								 "Glossary",
								 "Has Child",
								  "Has Engine",
								  "Has Gears",
								  "Has adress",
								  "Has domain and range",
								  "Has max cardinality",
								  "Has min cardinality",
								  "Has name",
								  "Has torsional moment",
								  "Is equal to",
								   "Is inverse of",
								   "Is parent of",
								   "Question", "Gender",
		                             "Has Capital", "Located In", "Population",
		                             "Torsional moment", "Has Voltage");

		$rootProperties = smwfGetSemanticStore()->getRootProperties();

		foreach ($rootProperties as $tuple) {
			  list($p,$isLeaf) = $tuple;
		      $this->assertContains($p->getText(), $exp_properties, $p->getText()." missing");
		}
			
	}

	public function testDirectSubCategories() {
		$exp_categories = array("Electric car", "Sports car");
		$subCategories = smwfGetSemanticStore()->getDirectSubCategories(Title::newFromText("Car", NS_CATEGORY));
		foreach ($subCategories as $tuple) {
			list($c, $isLeaf) = $tuple;
			$this->assertContains($c->getText(), $exp_categories, $c->getText()." missing");
		}
	}

	public function testSubCategories() {
		$exp_categories = array("Electric car", "Sports car", "Hybrid car");
		$subCategories = smwfGetSemanticStore()->getSubCategories(Title::newFromText("Car", NS_CATEGORY));
		foreach ($subCategories as $tuple) {
			list($sc,$isLeaf) = $tuple;
			$this->assertContains($sc->getText(), $exp_categories, $sc->getText()." missing");
		}
	}

	public function testDirectSuperCategories() {
		$exp_categories = array("Electric car", "Sports car");
		$superCategories = smwfGetSemanticStore()->getDirectSuperCategories(Title::newFromText("Hybrid car", NS_CATEGORY));
		foreach ($superCategories as $c) {
			$this->assertContains($c->getText(), $exp_categories, $c->getText()." missing");
		}
	}

	public function testCategoriesForInstance() {
		$exp_categories = array("Electric car", "Sports car");
		$categories = smwfGetSemanticStore()->getCategoriesForInstance(Title::newFromText("Peugeot", NS_MAIN));
		foreach ($categories as $c) {
			$this->assertContains($c->getText(), $exp_categories, $c->getText()." missing");
		}
	}

	public function testInstances() {
		$exp_instances = array("Audi TT", "Peugeot");
		$instances = smwfGetSemanticStore()->getInstances(Title::newFromText("Car", NS_CATEGORY));
		foreach ($instances as $inst) {
			list($i, $c) = $inst;
			$this->assertContains($i->getText(), $exp_instances, $i->getText()." missing");
		}
	}

	public function testDirectInstances() {
		$exp_instances = array("Audi TT");
		$instances = smwfGetSemanticStore()->getDirectInstances(Title::newFromText("Car", NS_CATEGORY));
		foreach ($instances as $inst) {
			list($i, $c) = $inst;
			$this->assertContains($i->getText(), $exp_instances, $i->getText()." missing");
		}
		$instancesAsStrings = self::convertToStringArray($instances);
		$this->assertNotContains("Peugeot", $instancesAsStrings, "Peugeot must not appear as direct instance of Car.");
	}


	public function testPropertiesWithSchemaByCategory() {
		$exp_properties = array("Body Form" , "Is parent of", "Has Engine");
		$exp_schema = array("Has Engine" => array(1,1,'_wpg',NULL,NULL,'Engine', false),
		                    "Is parent of" => array(0,2147483647,'_wpg',NULL,NULL,'Car', false), 
		                    "Body Form" => array(0,2147483647,'_wpg',NULL,NULL,NULL, false));

		$properties = smwfGetSemanticStore()->getPropertiesWithSchemaByCategory(Title::newFromText("Car", NS_CATEGORY));
		foreach ($properties as $prop) {
			list($p, $minCard, $maxCard, $type, $symCat, $transCat, $range, $inherited) = $prop;
			$this->assertContains($p->getText(), $exp_properties, $p->getText()." missing");
			$this->assertEquals($minCard, $exp_schema[$p->getText()][0]);
			$this->assertEquals($maxCard, $exp_schema[$p->getText()][1]);
			$this->assertEquals($type, $exp_schema[$p->getText()][2] );
			$this->assertEquals($symCat, $exp_schema[$p->getText()][3] );
			$this->assertEquals($transCat, $exp_schema[$p->getText()][4] );
			$this->assertEquals($range, $exp_schema[$p->getText()][5] );
			$this->assertEquals($inherited, $exp_schema[$p->getText()][6]);

		}
	}

	public function testPropertiesWithSchemaByName() {
		$exp_properties = array("Is parent of");
		$exp_schema = array("Is parent of" => array(0,2147483647,'_wpg',NULL,NULL,'Car', false));
		$requestoptions = new SMWAdvRequestOptions();
		$requestoptions->addStringCondition("parent", SMWStringCondition::STRCOND_MID);
		$properties = smwfGetSemanticStore()->getPropertiesWithSchemaByName($requestoptions);
		foreach ($properties as $prop) {
			list($p, $minCard, $maxCard, $type, $symCat, $transCat, $range, $inherited) = $prop;
			$this->assertContains($p->getText(), $exp_properties, $p->getText()." missing");
			$this->assertEquals($minCard, $exp_schema[$p->getText()][0]);
			$this->assertEquals($maxCard, $exp_schema[$p->getText()][1]);
			$this->assertEquals($type, $exp_schema[$p->getText()][2] );
			$this->assertEquals($symCat, $exp_schema[$p->getText()][3] );
			$this->assertEquals($transCat, $exp_schema[$p->getText()][4] );
			$this->assertEquals($range, $exp_schema[$p->getText()][5] );
			$this->assertEquals($inherited, $exp_schema[$p->getText()][6]);

		}
	}

	public function testPropertiesWithDomain() {
		$exp_properties = array("Body Form" , "Is parent of", "Has Engine");


		$properties = smwfGetSemanticStore()->getPropertiesWithDomain(Title::newFromText("Car", NS_CATEGORY));
		foreach ($properties as $prop) {

			$this->assertContains($prop->getText(), $exp_properties, $prop->getText()." missing");


		}
	}

	public function testPropertiesWithRange() {
		$exp_properties = array("Is parent of");

		$properties = smwfGetSemanticStore()->getPropertiesWithRange(Title::newFromText("Car", NS_CATEGORY));
		foreach ($properties as $prop) {

			$this->assertContains($prop->getText(), $exp_properties, $prop->getText()." missing");


		}
	}

	public function testDomainCategories() {
		$exp_categories = array("Car");
		$requestoptions = new SMWRequestOptions();
		$categories = smwfGetSemanticStore()->getDomainCategories(Title::newFromText("Body Form", SMW_NS_PROPERTY), $requestoptions);
		foreach ($categories as $c) {

			$this->assertContains($c->getText(), $exp_categories, $c->getText()." missing");


		}
	}

	public function testDirectSubProperties() {
		$exp_properties = array("Has Son", "Has Daughter");
		$subProperties = smwfGetSemanticStore()->getDirectSubProperties(Title::newFromText("Has Child", SMW_NS_PROPERTY));
		foreach ($subProperties as $tuple) {
			list($p, $isLeaf) = $tuple;
			$this->assertContains($p->getText(), $exp_properties, $p->getText()." missing");
		}
	}

	public function testDirectSuperProperties() {
		$exp_properties = array("Has Child");
		$subProperties = smwfGetSemanticStore()->getDirectSuperProperties(Title::newFromText("Has Son", SMW_NS_PROPERTY));
		foreach ($subProperties as $p) {
			$this->assertContains($p->getText(), $exp_properties, $p->getText()." missing");
		}
	}


	public function testRedirectPages() {
		$exp_redirectPages = array("Parent of");
		$redirectPages = smwfGetSemanticStore()->getRedirectPages(Title::newFromText("Is parent of", SMW_NS_PROPERTY));
		foreach ($redirectPages as $p) {
			$this->assertContains($p->getText(), $exp_redirectPages, $p->getText()." missing");
		}
	}
	public function testRedirectTarget() {
		$exp_target = "Is parent of";
		$redirectTarget = smwfGetSemanticStore()->getRedirectTarget(Title::newFromText("Parent of", NS_MAIN));
		$this->assertEquals($exp_target, $redirectTarget->getText());
			
	}




	public function testNumberOfUsage() {
		$exp_usage = 4;
		$usage = smwfGetSemanticStore()->getNumberOfUsage(Title::newFromText("Has Engine", SMW_NS_PROPERTY));
		$this->assertEquals($exp_usage, $usage);
			
	}

	public function testNumberOfInstancesAndSubcategories() {
		$exp_usage = 2;
		$usage = smwfGetSemanticStore()->getNumberOfInstancesAndSubcategories(Title::newFromText("Car", SMW_NS_PROPERTY));
		$this->assertEquals($exp_usage, $usage);
			
	}

	public function testNumberOfProperties() {
		$exp_usage = 3;
		$usage = smwfGetSemanticStore()->getNumberOfProperties(Title::newFromText("Car", NS_CATEGORY));
			
		$this->assertEquals($exp_usage, $usage);
			
	}

	public function testNumberOfPropertiesForTarget() {
		$exp_usage = 2;
		$usage = smwfGetSemanticStore()->getNumberOfPropertiesForTarget(Title::newFromText("Kai", NS_MAIN));
		$this->assertEquals($exp_usage, $usage);
			
	}

	public function testNumber() {
		$exp_usage = 17;
		$usage = smwfGetSemanticStore()->getNumber(NS_CATEGORY);
		$this->assertEquals($exp_usage, $usage);
			
	}

	/*
	TODO: write tests for unit methods of SemanticStore
	public abstract function getDistinctUnits(Title $type);


	public abstract function getAnnotationsWithUnit(Title $type, $unit);*/

	private static function convertToStringArray(array & $titles) {
		$result = array();
		foreach($titles as $t) {
			$result[] = $t->getText();
		}
		return $result;
	}
}
