<?php
/**
 * @file
 * @ingroup SMWHaloTests
 *
 * @author Kai K�hn
 *
 */
class TestWikiJobs extends PHPUnit_Framework_TestCase {


	function setUp() {

	}

	function tearDown() {

	}

	function testJobForPropertyRenaming() {
		$text = <<<ENDS
This is a test text: [[HasName:: Kai]] , [[Lives in :: Karlsruhe ]]
ENDS;
		$dummyTitle = Title::newFromText("dummy");
		$job = new SMW_UpdatePropertiesAfterMoveJob($dummyTitle, array("HasName", "HasFullName"));
		$newtext = $job->modifyPageContent($text);
		$this->assertTrue(strpos($newtext, "[[HasFullName:: Kai]]") !== false);

		$job = new SMW_UpdatePropertiesAfterMoveJob($dummyTitle, array("Lives in", "Lives at"));
		$newtext = $job->modifyPageContent($text);
		$this->assertTrue(strpos($newtext, "[[Lives at :: Karlsruhe ]]") !== false);

	}

	function testJobForLinkUpdating() {
		$text = <<<ENDS
This is a test text: [[HasName:: Kai]] , [[Lives in :: Karlsruhe ]]
    [[ has adress:: Musterstrasse; 34; 0721 / 43743437463; 76131 ]]
    [[ has domain and range:: Category:Test1; Category:Test2 ]]
ENDS;
		$dummyTitle = Title::newFromText("dummy");
		$job = new SMW_UpdateLinksAfterMoveJob($dummyTitle, array("Karlsruhe", "Adelsheim"));
		$newtext = $job->modifyPageContent($text);
		$this->assertTrue(strpos($newtext, "[[Lives in ::Adelsheim]]") !== false);
		
		$job = new SMW_UpdateLinksAfterMoveJob($dummyTitle, array("Musterstrasse", "Neue Musterstrasse"));
        $newtext = $job->modifyPageContent($text);
        $this->assertTrue(strpos($newtext, "[[ has adress::Neue Musterstrasse;  34;  0721 / 43743437463;  76131]]") !== false);
        
        $job = new SMW_UpdateLinksAfterMoveJob($dummyTitle, array("Category:Test1", "Category:NewTest1"));
        $newtext = $job->modifyPageContent($text);
        $this->assertTrue(strpos($newtext, "[[ has domain and range::Category:NewTest1;  Category:Test2]]") !== false);
	}




	function testJobForCategoryRenaming() {
		$text = <<<ENDS
This is a test text: [[Category: Test1]] , [[category : Test2 ]]
    [[category : Test3 |]] [[category : Test4 | Test ]]
ENDS;
		$dummyTitle = Title::newFromText("dummy");
		$job = new SMW_UpdateCategoriesAfterMoveJob($dummyTitle, array("Test1", "NewTest1"));
		$newtext = $job->modifyPageContent($text);
		$this->assertTrue(strpos($newtext, "[[Category: NewTest1]]") !== false);

		$job = new SMW_UpdateCategoriesAfterMoveJob($dummyTitle, array("Test2", "NewTest2"));
		$newtext = $job->modifyPageContent($text);
		$this->assertTrue(strpos($newtext, "[[category : NewTest2 ]]") !== false);

		$job = new SMW_UpdateCategoriesAfterMoveJob($dummyTitle, array("Test3", "NewTest3"));
		$newtext = $job->modifyPageContent($text);
		$this->assertTrue(strpos($newtext, "[[category : NewTest3 |]]") !== false);

		$job = new SMW_UpdateCategoriesAfterMoveJob($dummyTitle, array("Test4", "NewTest4"));
		$newtext = $job->modifyPageContent($text);
			
		$this->assertTrue(strpos($newtext, "[[category : NewTest4 | Test ]]") !== false);
	}




}
