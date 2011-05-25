<?php

require_once dirname(__FILE__) . '/../../../../tests/tests_halo/SeleniumTestCase_Base.php';
require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class TestFormatQueryPreview extends SeleniumTestCase_Base
{

  public function testMyTestCase()
  {
    $this->open("/mediawiki/index.php/Special:QueryInterface");
    $this->click("//button[@onclick='qihelper.newCategoryDialogue(true)']");
    $this->type("input0", "Person");
    $this->click("//button[@onclick='qihelper.add()']");
    $this->setSpeed("1000");
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
        if ($second >= 60) $this->fail("timeout");
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
        if ($second >= 60) $this->fail("timeout");
        try {
            if ($this->isTextPresent("5")) break;
        } catch (Exception $e) {}
        sleep(1);
    }

    try {
        $this->assertTrue($this->isTextPresent("5"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("layout_format");
    $this->select("layout_format", "label=Category (category)");
    $this->click("//option[@value='category']");
    for ($second = 0; ; $second++) {
        if ($second >= 60) $this->fail("timeout");
        try {
            if ($this->isElementPresent("//span[@id='A']")) break;
        } catch (Exception $e) {}
        sleep(1);
    }

    try {
        $this->assertTrue($this->isElementPresent("//span[@id='A']"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isElementPresent("link=Al Gore"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isElementPresent("//span[@id='G']"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isElementPresent("link=Gerald Fox"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isElementPresent("//span[@id='J']"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isElementPresent("link=John Doe"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isElementPresent("//span[@id='F']"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isElementPresent("link=Fred"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isElementPresent("//span[@id='H']"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isElementPresent("link=Henry Morgan"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->select("layout_format", "label=Table (table)");
    $this->click("//option[@value='table']");
  }
}
?>