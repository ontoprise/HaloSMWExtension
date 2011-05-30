<?php

require_once dirname(__FILE__) . '/../../../../tests/tests_halo/SeleniumTestCase_Base.php';


class TestIntermediateResultView extends SeleniumTestCase_Base
{

	public function testMyTestCase()
	{
		$this->open("/mediawiki/index.php/Special:QueryInterface");
		$this->click("//button[@onclick='qihelper.newCategoryDialogue(true)']");
		$this->type("input0", "Person1");
		$this->click("//button[@onclick='qihelper.add()']");
		$this->click("//button[@onclick='qihelper.newPropertyDialogue(true)']");
		$this->focus("//input[@id=\"input_p0\"]");
		$this->controlKeyDown();
		$this->altKeyDown();
		$this->typeKeys("//input[@id=\"input_p0\"]", "' '");
		//    $this->controlKeyUp();
		//    $this->altKeyUp();
		for ($second = 0; ; $second++) {
			if ($second >= 60) $this->fail("Autocomplete list failed to open");
			try {
				if ($this->isElementPresent("//p[@id='selected0']")) break;
			} catch (Exception $e) {
				array_push($this->verificationErrors, "Autocomplete list failed to open");
			}
			sleep(1);
		}

		try {
			$this->assertTrue($this->isElementPresent("//p[text()='Born in']"), "Element is not present: Born in");
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertTrue($this->isElementPresent("//p[text()='Has name']"), "Element is not present: Has name");
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertTrue($this->isElementPresent("//p[@class='matchedSmartInputItem' and text()='Lives in']"), "Element is not present: Lives in");
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertTrue($this->isElementPresent("//p[@class='matchedSmartInputItem inferredSmartInputItem' and text()='Has hobbies']"), "Element is not present: Has hobbies");
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertTrue($this->isElementPresent("//p[@class='matchedSmartInputItem inferredSmartInputItem' and text()='Modification date']"), "Element is not present: Modification date");
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertTrue($this->isElementPresent("//p[@class='matchedSmartInputItem inferredSmartInputItem' and text()='Birth date']"), "Element is not present: Birth date");
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
	}
}
?>