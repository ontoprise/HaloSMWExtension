<?php
global $srefgIP;
require_once($srefgIP.'/includes/SRF_RefactoringOperation.php');
class SRFTestUtil extends PHPUnit_Framework_TestCase {


    function setUp() {

    }

    function tearDown() {

    }

    function testTitleUnique() {
    	$titles = array();
    	$titles[] = Title::newFromText("Property:Has Owner");
    	$titles[] = Title::newFromText("Property:Height");
    	$titles[] = Title::newFromText("Category:Person");
    	$titles[] = Title::newFromText("Property:Has Owner");
    	$titles[] = Title::newFromText("Category:Person");
    	$titles[] = Title::newFromText("Hans");
    	$results = SMWRFRefactoringOperation::makeTitleListUnique($titles);
    	$this->assertEquals(4, count($results));
    	$this->assertEquals("Category:Person", $results[0]->getPrefixedText());
    	$this->assertEquals("Hans", $results[1]->getPrefixedText());
    	$this->assertEquals("Property:Has Owner", $results[2]->getPrefixedText());
    	$this->assertEquals("Property:Height", $results[3]->getPrefixedText());
    	
    }
}