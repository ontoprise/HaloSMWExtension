<?php

require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class TestSubquery extends PHPUnit_Extensions_SeleniumTestCase
{
  protected function setUp()
  {
    $this->setBrowser("*chrome");
    $this->setBrowserUrl("http://localhost/");
  }

  public function testMyTestCase()
  {
  	//test is incomplete
  	$this->markTestIncomplete("Selenium QueryInterface test is incomplete.");
  	
    $this->open("/mediawiki/index.php/Special:QueryInterface");
    $this->click("//button[@onclick='qihelper.newPropertyDialogue(true)']");
    $this->type("input_p0", "ComingFrom");
    $this->click("//input[@name='input_r0' and @value='1']");
    $this->click("//button[@onclick='qihelper.add()']");
	$this->setSpeed("1000");
    $this->click("//a[@onclick='qihelper.setActiveQuery(1)']");
    $this->setSpeed("0");
    $this->click("//button[@onclick='qihelper.newCategoryDialogue(true)']");
    $this->type("input0", "City");
    $this->click("//button[@onclick='qihelper.add()']");
    try {
        $this->assertTrue($this->isTextPresent("Liverpudlian 	Liverpool"));
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
    $this->click("link=Inhabitants");
    $this->type("input_r1", "200000");
    $this->click("//button[@onclick='qihelper.add()']");
    try {
        $this->assertTrue($this->isTextPresent("Liverpudlian 	Liverpool"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }
}
?>