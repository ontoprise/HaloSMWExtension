<?php
require_once dirname(__FILE__) . '/../../../../tests/tests_halo/SeleniumTestCase_Base.php';
require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class TestUnitsInQuery extends SeleniumTestCase_Base
{

	public function testMyTestCase()
	{
		$this->open("/mediawiki/index.php/Special:QueryInterface");
		$this->click("qiDefTab3");
		$this->type("fullAskText", "{{#ask: [[Category:Person]]\n| ?Height #cm \n| format=table\n| merge=false\n|}}");
		$this->click("//button[@onclick='qihelper.loadFromSource(true)']");
		for ($second = 0; ; $second++) {
			if ($second >= 60) $this->fail("timeout");
			try {
				if ($this->isTextPresent("Al Gore")) break;
			} catch (Exception $e) {}
			sleep(1);
		}

		try {
			$this->assertTrue($this->isTextPresent("Al Gore"), "Text not present: Al Gore");
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertTrue($this->isTextPresent("Fred"), "Text not present: Fred");
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertTrue($this->isTextPresent("Gerald Fox"), "Text not present: Gerald Fox");
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertTrue($this->isTextPresent("182 cm"), "Text not present: 182 cm");
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertTrue($this->isTextPresent("Henry Morgan"), "Text not present: Henry Morgan");
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertTrue($this->isTextPresent("175 cm"), "Text not present: 175 cm");
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertTrue($this->isTextPresent("John Doe"), "Text not present: John Doe");
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		$this->click("qiDefTab3");
		$this->type("fullAskText", "{{#ask: [[Category:Person]]\n| ?Height #inch \n| format=table\n| headers=show\n| link=all\n| order=ascending\n| merge=false\n|}}");
		$this->click("//button[@onclick='qihelper.loadFromSource(true)']");
		for ($second = 0; ; $second++) {
			if ($second >= 60) $this->fail("timeout");
			try {
				if ($this->isTextPresent("Al Gore")) break;
			} catch (Exception $e) {}
			sleep(1);
		}

		try {
			$this->assertTrue($this->isTextPresent("Al Gore"), "Text not present: Al Gore");
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertTrue($this->isTextPresent("Fred"), "Text not present: Fred");
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertTrue($this->isTextPresent("Gerald Fox"), "Text not present: Gerald Fox");
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertTrue($this->isTextPresent("71.653 inch"), "Text not present: 71.653 inch");
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertTrue($this->isTextPresent("Henry Morgan"), "Text not present: Henry Morgan");
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertTrue($this->isTextPresent("68.898 inch"), "Text not present: 68.898 inch");
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertTrue($this->isTextPresent("John Doe"), "Text not present: John Doe");
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		$this->click("qiDefTab3");
		$this->type("fullAskText", "{{#ask: [[Category:Person]]\n[[Height::<1.8 m]]\n| ?Height #cm \n| format=table\n| headers=show\n| link=all\n| order=ascending\n| merge=false\n|}}");
		$this->click("//button[@onclick='qihelper.loadFromSource(true)']");
		for ($second = 0; ; $second++) {
			if ($second >= 60) $this->fail("timeout");
			try {
				if ($this->isTextPresent("Henry Morgan")) break;
			} catch (Exception $e) {}
			sleep(1);
		}

		try {
			$this->assertTrue($this->isTextPresent("Henry Morgan"), "Text not present: Henry Morgan");
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertTrue($this->isTextPresent("175 cm"), "Text not present: 175 cm");
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertFalse($this->isTextPresent("Al Gore"), "Text present: Al Gore");
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertFalse($this->isTextPresent("Fred"), "Text present: Fred");
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertFalse($this->isTextPresent("Gerald Fox"), "Text present: Gerald Fox");
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertFalse($this->isTextPresent("John Doe"), "Text present: John Doe");
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
	}
}
?>