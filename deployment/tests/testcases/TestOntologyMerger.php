<?php

require_once ('deployment/io/import/DF_OntologyMerger.php');
require_once ('deployment/languages//DF_Language.php');
/**
 * Tests the OntologyMerger tool
 *
 */
class TestOntologyMerger extends PHPUnit_Framework_TestCase {

	var $om;

	function setUp() {
		global $wgLanguageCode, $dfgLang;
		$langClass = "DF_Language_$wgLanguageCode";
		if (!file_exists("../languages/$langClass.php")) {
			$langClass = "DF_Language_En";
		}
		require_once("../languages/$langClass.php");
		$dfgLang = new $langClass();
	}

	function tearDown() {

	}

	function testPropertyPrefixMappings() {
		$om = new OntologyMerger(array("Knows"));
	    $actual = $om->transformOntologyElements("PR_", "[[Knows::Kai]] [[Name::Kai]]");
	    $this->assertEquals("[[PR_Knows:::PR_Kai]] [[PR_Name::Kai]]", $actual);
	}

	function testPropertyPrefixMappings2() {
		$om = new OntologyMerger(array(), array("Has domain and range"=> array("Type:Page","Type:Page")), array("Has domain and range"));
		$actual = $om->transformOntologyElements("PR_","[[Has domain and range::Category:Test]]");
		$this->assertEquals("[[Has domain and range::Category:PR_Test]]", $actual);
	}
	
    function testPropertyPrefixMappings3() {
        $om = new OntologyMerger(array(), array("Has domain and range"=> array("Type:Page","Type:Page")), array("Has domain and range"));
        $actual = $om->transformOntologyElements("PR_","[[Has domain and range :: Category:Test1; Category:Test2]]");
        $this->assertEquals("[[Has domain and range::Category:PR_Test1; Category:PR_Test2]]", $actual);
    }
    
    function testCategoryPrefix() {
        $om = new OntologyMerger(array(), array("Has domain and range"=> array("Type:Page","Type:Page")), array("Has domain and range"));
        $actual = $om->transformOntologyElements("PR_","[[Category:Test1]]");
        echo $actual;
        $this->assertEquals("[[Category:PR_Test1]]", $actual);
    }
    
    function testCategoryPrefix2() {
        $om = new OntologyMerger(array(), array("Has domain and range"=> array("Type:Page","Type:Page")), array("Has domain and range"));
        $actual = $om->transformOntologyElements("PR_","[[ category : Test2]]");
        echo $actual;
        $this->assertEquals("[[Category:PR_Test2]]", $actual);
    }
    
    function testStripAnnotations() {
        $om = new OntologyMerger(array(), array("Has domain and range"=> array("Type:Page","Type:Page")), array("Has domain and range"));
        $actual = $om->stripAnnotations("The category [[ Category : Test2]] is a test category. Kai has the name [[HasName::Kai]].");
        echo $actual;
        $this->assertEquals("The category  is a test category. Kai has the name .", $actual);
    }
    
    function testExtractAnnotations() {
        $om = new OntologyMerger(array(), array("Has domain and range"=> array("Type:Page","Type:Page")), array("Has domain and range"));
        $actual = $om->extractAnnotations("The category [[ Category : Test2]] is a test category. Kai has the name [[HasName::Kai]].");
        var_dump($actual);
        $this->assertContains("[[ Category : Test2]]", $actual);
    }
}