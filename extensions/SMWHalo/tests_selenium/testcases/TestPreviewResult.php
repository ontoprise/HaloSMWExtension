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
