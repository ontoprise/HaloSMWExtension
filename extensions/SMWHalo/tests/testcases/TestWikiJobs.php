<?php
/**
 * @file
 * @ingroup SMWHaloTests 
 * 
 * @author Kai Kühn
 * 
 */
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

       
        $this->assertTrue(true);
	}


	
	
    function testRunJobsForCategoryRenaming() {
        $category = Title::newFromText("Sports car", NS_CATEGORY);
        $new_category = Title::newFromText("Sports coupe", NS_CATEGORY);

        $category->moveTo($new_category, false, "Automatic test");

        // must be called explicitly, because hook works only on Special:Move
        smwfGenerateUpdateAfterMoveJob($moveForm, $category, $new_category);

     
        $this->assertTrue(true);
    }
    
    function testRunJobs() {
    	global $IP;
    	exec("php $IP/maintenance/runJobs.php", $out, $ret);
    	print_r($out);
    	$this->assertTrue(true);
    }

    
}
