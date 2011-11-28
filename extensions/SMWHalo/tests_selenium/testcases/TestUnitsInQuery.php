<?php
/*
 * Copyright (C) Vulcan Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program.If not, see <http://www.gnu.org/licenses/>.
 *
 */

require_once dirname(__FILE__) . '/../../../../tests/tests_halo/SeleniumTestCase_Base.php';


class TestUnitsInQuery extends SeleniumTestCase_Base
{

	public function testMyTestCase()
	{
		$this->open("/mediawiki/index.php/Special:QueryInterface");
		$this->click("//td[contains(@id, 'qiDefTab') and text()='Query source']");
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
		$this->click("//td[contains(@id, 'qiDefTab') and text()='Query source']");
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
		$this->click("//td[contains(@id, 'qiDefTab') and text()='Query source']");
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
