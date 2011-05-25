<?php

require_once './../../../../tests/tests_halo/SeleniumTestCase_Base.php';
require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class TestSubquery extends SeleniumTestCase_Base
{

	public function testMyTestCase()
	{
		$this->open("/mediawiki/index.php/Special:QueryInterface");
		$this->click("//button[@onclick='qihelper.newPropertyDialogue(true)']");
		$this->type("input_p0", "ComingFrom");
		$this->click("//input[@name='input_r0' and @value='1']");
		for ($second = 0; ; $second++) {
			if ($second >= 60) $this->fail("timeout");
			try {
				if ($this->isElementPresent("//button[@onclick='qihelper.add()'][text()='Add']")) break;
			} catch (Exception $e) {}
			sleep(1);
		}

		$this->fireEvent("//button[@onclick='qihelper.add()'][text()='Add']", "click");
		for ($second = 0; ; $second++) {
			if ($second >= 60) $this->fail("timeout");
			try {
				if ($this->isElementPresent("//div[@id='treeanchor']//a[text()='Subquery 1']")) break;
			} catch (Exception $e) {}
			sleep(1);
		}

		$this->click("//div[@id='treeanchor']//a[text()='Subquery 1']");
		$this->click("//button[@onclick='qihelper.newCategoryDialogue(true)']");
		$this->type("input0", "City");
		$this->click("//button[@onclick='qihelper.add()']");
		try {
			$this->assertTrue($this->isTextPresent("Liverpudlian"));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertTrue($this->isTextPresent("Liverpool"));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		$this->click("link=City");
		$this->click("//button[@onclick='qihelper.newPropertyDialogue(true)']");
		$this->type("input_p0", "Inhabitants");
		$this->click("//input[@name='input_r0' and @value='-2']");
		$this->type("input_r1", "100000");
		$this->click("//button[@onclick='qihelper.add()']");
		try {
			$this->assertTrue($this->isTextPresent("Your query did not return any results."));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		for ($second = 0; ; $second++) {
			if ($second >= 60) $this->fail("timeout");
			try {
				if ($this->isElementPresent("//div[@id='treeanchor']//a[text()='Inhabitants']")) break;
			} catch (Exception $e) {}
			sleep(1);
		}

		$this->click("//div[@id='treeanchor']//a[text()='Inhabitants']");
		$this->type("input_r1", "200000");
		$this->click("//button[@onclick='qihelper.add()']");
		try {
			$this->assertTrue($this->isTextPresent("Liverpudlian"));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertTrue($this->isTextPresent("Liverpool"));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
	}
}
?>