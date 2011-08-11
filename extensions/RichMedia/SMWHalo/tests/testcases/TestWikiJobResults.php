<?php
/**
 * @file
 * @ingroup SMWHaloTests 
 * 
 * @author Kai Kühn
 *
 */
class TestWikiJobResults extends PHPUnit_Framework_TestCase {


    function setUp() {
        
    }

    function tearDown() {

    }
    
   


    function testCheckIfPropertyRenamed() {
        // do some checks
        $page = Title::newFromText("5 cylinder", NS_MAIN);
        $prop = SMWPropertyValue::makeUserProperty("Torsional moment");
        $values = smwfGetStore()->getPropertyValues($page, $prop);
        
        $this->assertTrue(count($values) > 0);
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
