<?php
class TestWikiJobs extends PHPUnit_Framework_TestCase {


	function setUp() {
		
	}

	function tearDown() {

	}
	
	function testRunJobsForPropertyRenaming() {
		$property = Title::newFromText("Has torsional moment", SMW_NS_PROPERTY);
        $new_property = Title::newFromText("Torsional moment", SMW_NS_PROPERTY);

        $property->moveTo($new_property, false, "Automatic test");

        // must be called explicitly, because hook works only on Special:Move
        smwfGenerateUpdateAfterMoveJob($moveForm, $property, $new_property);

        exec('php ../../../maintenance/runJobs.php');
	}


	function testCheckIfPropertyRenamed() {
		// do some checks
		$page = Title::newFromText("5 cylinder", NS_MAIN);
		$prop = SMWPropertyValue::makeUserProperty("Torsional moment");
		$values = smwfGetStore()->getPropertyValues($page, $prop);
		
		$this->assertTrue(count($values) > 0);
	}
	
    function testRunJobsForCategoryRenaming() {
        $category = Title::newFromText("Sports car", NS_CATEGORY);
        $new_category = Title::newFromText("Sports coupe", NS_CATEGORY);

        $category->moveTo($new_category, false, "Automatic test");

        // must be called explicitly, because hook works only on Special:Move
        smwfGenerateUpdateAfterMoveJob($moveForm, $category, $new_category);

        exec('php ../../../maintenance/runJobs.php');
    }

    function testCheckIfCategoryRenamed() {
        // do some checks
        $exp_category = array("Sports coupe", "Coupé");
        $page = Title::newFromText("Audi TT", NS_MAIN);
        
        $values = smwfGetSemanticStore()->getCategoriesForInstance($page);
        
        foreach($values as $v) {
            $this->assertContains(utf8_decode($v->getText()), $exp_category);
        }
    }
}
?>