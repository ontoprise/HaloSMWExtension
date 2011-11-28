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


class TestFormatQueryPreview extends SeleniumTestCase_Base
{

  public function testMyTestCase()
  {
    $this->open("/mediawiki/index.php/Special:QueryInterface");
    $this->click("//button[text()='Add Category']");
    $this->type("input0", "Person");
    $this->click("//button[@onclick='qihelper.add()']");
    $this->setSpeed("1000");
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
        $this->assertTrue($this->isTextPresent("Henry Morgan"), "Text not present: Henry Morgan");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isTextPresent("John Doe"), "Text not present: John Doe");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->setSpeed("0");
    $this->click("layouttitle-link");
    $this->assertTrue($this->isElementPresent("//option[@value='broadtable']"));
    $this->assertTrue($this->isElementPresent("//option[@value='csv']"));
    $this->assertTrue($this->isElementPresent("//option[@value='category']"));
    $this->assertTrue($this->isElementPresent("//option[@value='count']"));
    $this->assertTrue($this->isElementPresent("//option[@value='embedded']"));
    $this->assertTrue($this->isElementPresent("//option[@value='ol']"));
    $this->assertTrue($this->isElementPresent("//option[@value='ul']"));
    $this->assertTrue($this->isElementPresent("//option[@value='list']"));
    $this->assertTrue($this->isElementPresent("//option[@value='rdf']"));
    $this->assertTrue($this->isElementPresent("//option[@value='table']"));
    $this->assertTrue($this->isElementPresent("//option[@value='tabularform']"));
    $this->select("layout_format", "label=CSV export (csv)");
    $this->click("//option[@value='csv']");
    for ($second = 0; ; $second++) {
        if ($second >= 60) $this->fail("Element not present: link=CSV");
        try {
            if ($this->isElementPresent("link=CSV")) break;
        } catch (Exception $e) {}
        sleep(1);
    }

    try {
        $this->assertTrue($this->isElementPresent("link=CSV"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->select("layout_format", "label=Count results (count)");
    $this->click("//option[@value='count']");
    for ($second = 0; ; $second++) {
        if ($second >= 60) $this->fail("Text not present: 5");
        try {
            if ($this->isTextPresent("5")) break;
        } catch (Exception $e) {}
        sleep(1);
    }

    try {
        $this->assertTrue($this->isTextPresent("5"), "Text not present: 5");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("layout_format");
    $this->select("layout_format", "label=Category (category)");
    $this->click("//option[@value='category']");
    for ($second = 0; ; $second++) {
        if ($second >= 60) $this->fail("Element not present: //span[@id='A']");
        try {
            if ($this->isElementPresent("//span[@id='A']")) break;
        } catch (Exception $e) {}
        sleep(1);
    }

    try {
        $this->assertTrue($this->isElementPresent("//span[@id='A']"), "Element not present: //span[@id='A']");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isElementPresent("link=Al Gore"), "Element not present: link=Al Gore");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isElementPresent("//span[@id='G']"), "Element not present: //span[@id='G']");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isElementPresent("link=Gerald Fox"), "Element not present: link=Gerald Fox");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isElementPresent("//span[@id='J']"), "Element not present: //span[@id='J']");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isElementPresent("link=John Doe"), "Element not present: link=John ");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isElementPresent("//span[@id='F']"), "Element not present: //span[@id='F']");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isElementPresent("link=Fred"), "Element not present: link=Fred");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isElementPresent("//span[@id='H']"), "Element not present: //span[@id='H']");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isElementPresent("link=Henry Morgan"), "Element not present: link=Henry Morgan");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }
}
?>
