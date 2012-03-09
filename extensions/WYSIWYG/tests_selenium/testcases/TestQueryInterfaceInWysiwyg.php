<?php
/*
 * Copyright (C) ontoprise GmbH
 *
 * Vulcan Inc. (Seattle, WA) and ontoprise GmbH (Karlsruhe, Germany)
 * expressly waive any right to enforce any Intellectual Property
 * Rights in or to any enhancements made to this program.
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

// check if original file was called from command line or Webserver
if ( isset( $_SERVER ) && array_key_exists( 'REQUEST_METHOD', $_SERVER ) ) {
    die( "This script must be run from the command line\n" );
}

require_once dirname(__FILE__) . '/../../../../tests/tests_halo/SeleniumTestCase_Base.php';


class TestQueryInterfaceInWysiwyg extends SeleniumTestCase_Base
{

  public function testMyTestCase()
  {
    $this->login();
    $this->open("/mediawiki/index.php?title=France&action=edit");
    $this->type("wpTextbox1", "[[HasCitizen::33333333]]\n[[HasCapital::Paris]]\n[[Category:State]]");
    $this->click("wpSave");
    $this->waitForPageToLoad("30000");
    $this->open("/mediawiki/index.php?title=Portugal&action=edit");
    $this->type("wpTextbox1", "[[HasCitizen::44444444444]]\n[[HasCapital::Lissabon]]\n[[Category:State]]");
    $this->click("wpSave");
    $this->waitForPageToLoad("30000");
    $this->open("/mediawiki/index.php?title=Germany&action=edit");
    $this->type("wpTextbox1", "[[HasCitizen::55555555]]\n[[HasCapital::Berlin]]\n[[Category:State]]");
    $this->click("wpSave");
    $this->waitForPageToLoad("30000");
    $this->open("/mediawiki/index.php?title=WYSIWYGTest&action=edit&mode=wysiwyg");
    for ($second = 0; ; $second++) {
        if ($second >= 60) $this->fail("timeout");
        try {
            if ($this->isElementPresent("//a[contains(@class, 'SMWqi')]/span[contains(text(), 'Query Interface')]")) break;
        } catch (Exception $e) {}
        sleep(1);
    }

    $this->runScript("CKEDITOR.instances.wpTextbox1.setData(\"\")");
    $this->setSpeed("5000");
    $this->click("//a[contains(@class, 'SMWqi')]/span[contains(text(), 'Query Interface')]");
    $this->selectFrame("CKeditorQueryInterface");
    $this->setSpeed("0");
    $this->click("qiDefTab3");
    $this->type("fullAskText", "{{#ask: [[Category:State]]\n| ?HasCitizen \n| format=table\n| merge=false\n|}}");
    $this->click("//button[@onclick='qihelper.loadFromSource(true)']");
    for ($second = 0; ; $second++) {
        if ($second >= 60) $this->fail("timeout");
        try {
            if ($this->isElementPresent("link=33333333")) break;
        } catch (Exception $e) {}
        sleep(1);
    }

    try {
        $this->assertTrue($this->isElementPresent("link=33333333"), "Element not present: link=33333333");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isElementPresent("link=France"), "Element not present: link=France");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isElementPresent("link=55555555"), "Element not present: link=55555555");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isElementPresent("link=Germany"), "Element not present: link=Germany");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isElementPresent("link=44444444444"), "Element not present: link=44444444444");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isElementPresent("link=Portugal"), "Element not present: link=Portugal");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isElementPresent("link=HasCitizen"), "Element not present: link=HasCitizen");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->selectWindow("null");
    $this->click("//a[contains(@class, 'cke_dialog_ui_button_ok')]");
    try {
        $this->assertTrue($this->isElementPresent("//img[@class='FCK__SMWquery'][@data-cke-real-node-type='1'][@data-cke-real-element-type='span']"), "Element not present: //img[@class='FCK__SMWquery']");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("wpSave");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isElementPresent("link=HasCitizen"), "Element not present: link=HasCitizen");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isElementPresent("link=33333333"), "Element not present: link=33333333");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isElementPresent("link=France"), "Element not present: link=France");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isElementPresent("link=55555555"), "Element not present: link=55555555");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isElementPresent("link=Germany"), "Element not present: link=Germany");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isElementPresent("link=44444444444"), "Element not present: link=44444444444");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isElementPresent("link=Portugal"), "Element not present: link=Portugal");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }
}
?>
