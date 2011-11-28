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


class TestIntermediateResultView extends SeleniumTestCase_Base
{

	public function testMyTestCase()
	{
		$this->open("/mediawiki/index.php/Special:QueryInterface");
		$this->click("//button[text()='Add Category']");
		$this->type("input0", "Person1");
		$this->click("//button[@onclick='qihelper.add()']");
		$this->click("//button[text()='Add Property']");
		$this->focus("//input[@id=\"input_p0\"]");
		$this->controlKeyDown();
		$this->altKeyDown();
		$this->typeKeys("//input[@id=\"input_p0\"]", "' '");
		
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
