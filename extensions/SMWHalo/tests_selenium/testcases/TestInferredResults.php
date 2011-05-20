<?php

require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class TestInferredResults extends PHPUnit_Extensions_SeleniumTestCase
{
  protected function setUp()
  {
    $this->setBrowser("*chrome");
    $this->setBrowserUrl("http://localhost/");
  }

  public function testMyTestCase()
  {
    $this->open("/mediawiki/index.php/Special:QueryInterface");
    $this->click("qiDefTab3");
    $this->type("fullAskText", "{{#sparql: [[Itemnumber::+]]\n| ?Itemnumber\n| format=csv\n| headers=show\n| link=all\n| order=ascending\n| merge=false\n|}}");
    $this->click("//button[@onclick='qihelper.loadFromSource(true)']");
    for ($second = 0; ; $second++) {
        if ($second >= 60) $this->fail("timeout");
        try {
            if ($this->isElementPresent("link=CSV")) break;
        } catch (Exception $e) {}
        sleep(1);
    }

    try {
        $this->assertTrue($this->isElementPresent("link=CSV"), "Element is not present: link=CSV");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("qiDefTab1");
    $this->click("link=Itemnumber");
    $this->click("qidelete");
    $this->click("qiDefTab3");
    $this->type("fullAskText", "{{#sparql: [[Itemnumber::+]]\n| ?Itemnumber\n| format=broadtable\n| headers=show\n| link=all\n| order=ascending\n| merge=false\n|}}");
    $this->click("//button[@onclick='qihelper.loadFromSource(true)']");
    for ($second = 0; ; $second++) {
        if ($second >= 60) $this->fail("timeout");
        try {
            if ($this->isElementPresent("//table[@id=\"querytable0\"][@width=\"100%\"]", "Element is not present: link=CSV")) break;
        } catch (Exception $e) {}
        sleep(1);
    }

    try {
        $this->assertTrue($this->isElementPresent("//table[@id=\"querytable0\"][@width=\"100%\"]"), "Element is not present: //table[@id=\"querytable0\"][@width=\"100%\"]");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isTextPresent("Firstitem"), "Text is not present: Firstitem");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isTextPresent("2"), "Text is not present: 2");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isTextPresent("Seconditem"), "Text is not present: Seconditem");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isTextPresent("3"), "Text is not present: 3");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("qiDefTab1");
    $this->click("link=Itemnumber");
    $this->click("//input[@name='input_r0' and @value='-2']");
    $this->select("//tr[@id='row_r1']/td[2]/select", "label=greater (>=)");
    $this->type("input_r1", "3");
    $this->click("//button[@onclick='qihelper.add()']");
    $this->click("qiDefTab3");
    try {
        $this->assertTrue((bool)preg_match('/^\{\{#ask: \[\[Itemnumber::>3\]\]
													    | [\s\S]Itemnumber 
													    | format=broadtable
													    | headers=show
													    | link=all
													    | order=ascending
													    | merge=false
													    |\}\}$/', 
        $this->getValue("fullAskText")), "Query doesn't match the expected string: " . $this->getValue("fullAskText"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isTextPresent("Seconditem"), "Text is not present: Seconditem");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isTextPresent("3"), "Text is not present: 3");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }
}
?>