<?php

require_once './../../../../tests/tests_halo/SeleniumTestCase_Base.php';
require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class TestPreviewResult extends SeleniumTestCase_Base
{
	private $alGore = "Al Gore";
	private $fred = "Fred";
	private $geralsFox = "Gerald Fox";
	private $henryMorgan = "Henry Morgan";
	private $johnDoe = "John Doe";
	private $homePageLabel1 = "It's all about Fred";
	private $homePageLabel2 = "Google";


	public function testMyTestCase()
	{
		$this->open("/mediawiki/index.php/Special:QueryInterface");
		$this->click("//button[@onclick='qihelper.newCategoryDialogue(true)']");
		$this->type("input0", "Person");
		$this->click("//button[@onclick='qihelper.add()']");
		try {
			$this->assertTrue($this->isTextPresent($this->alGore, $this->alGore. " is not present"));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertTrue($this->isTextPresent("Fred"));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertTrue($this->isTextPresent("Gerald Fox"));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertTrue($this->isTextPresent("Henry Morgan"));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertTrue($this->isTextPresent("John Doe"));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		$this->click("link=Person");
		$this->click("//button[@onclick='qihelper.newPropertyDialogue(true)']");
		$this->type("input_p0", "Homepage label");
		$this->click("//button[@onclick='qihelper.add()']");
		for ($second = 0; ; $second++) {
			if ($second >= 60) $this->fail("timeout");
			try {
				if ($this->isElementPresent("Link=Homepage label")) break;
			} catch (Exception $e) {}
			sleep(1);
		}

		try {
			$this->assertTrue($this->isTextPresent("Al Gore"));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertTrue($this->isTextPresent("Fred"));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertTrue($this->isTextPresent("Gerald Fox"));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertTrue($this->isTextPresent("Henry Morgan"));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertTrue($this->isTextPresent("John Doe"));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertTrue($this->isTextPresent("It's all about Fred"));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertTrue($this->isTextPresent("Google"));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		$this->click("link=Homepage label");
		$this->click("input_c2");
		$this->click("//button[@onclick='qihelper.add()']");
		try {
			$this->assertTrue($this->isTextPresent("Fred"));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertTrue($this->isTextPresent("It's all about Fred"));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertTrue($this->isTextPresent("Henry Morgan"));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertTrue($this->isTextPresent("Google"));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
	}
}
?>