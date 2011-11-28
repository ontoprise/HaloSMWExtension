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
