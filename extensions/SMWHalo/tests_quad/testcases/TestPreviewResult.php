<?php

require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class Example extends PHPUnit_Extensions_SeleniumTestCase
{
  protected function setUp()
  {
    $this->setBrowser("*chrome");
    $this->setBrowserUrl("http://localhost/mediawiki");
  }

  public function testMyTestCase()
  {
    $this->open("/mediawiki/index.php/Main_Page");
    $this->type("searchInput", "Special:QueryInterface");
    $this->click("searchGoButton");
    $this->waitForPageToLoad("30000");
    $this->click("//button[@onclick='qihelper.newCategoryDialogue(true)']");
    $this->type("input0", "P");
    $this->click("//button[@onclick='qihelper.add()']");
    try {
        $this->assertTrue($this->isTextPresent("Person"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
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
    $this->click("link=Person");
    $this->click("//button[@onclick='qihelper.newPropertyDialogue(true)']");
    $this->type("input_p0", "H");
    $this->click("//button[@onclick='qihelper.add()']");
    try {
        $this->assertTrue($this->isTextPresent("Person\r\n	Homepage\r\n	= all values"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isTextPresent("Fred 	Http://en.wikipedia.org/wiki/Fred_Flintstone"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isTextPresent("Henry Morgan 	Http://google.com"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isTextPresent("John Doe 	Http://john.doe.com"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Homepage");
    $this->click("input_c2");
    $this->click("//button[@onclick='qihelper.add()']");
  }
}
?>