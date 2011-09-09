<?php

require_once dirname(__FILE__) . '/../../../../tests/tests_halo/SeleniumTestCase_Base.php';

class TestQueries extends SeleniumTestCase_Base
{
    
  public function testQueryWikiToHtmlTransformation()
  {
    $askWikitext = '{{#ask:[[Category:Wiki - kopie 2/Component]]|format = table}}';      
      
    $this->open("/mw156/index.php?title=Testquery1&action=edit");
    $this->setSpeed("3000");
    if($this->isElementPresent("//a[@id='toggle_wpTextbox1'][text()='Show WikiTextEditor']")){
        $this->click("//a[@id='toggle_wpTextbox1'][text()='Show WikiTextEditor']");
    } 
    $this->setSpeed("0");
    $this->type("wpTextbox1", $askWikitext);
    $this->click("id=wpSave");
    $this->waitForPageToLoad("30000");
    $this->click("link=Edit");
    $this->waitForPageToLoad("30000");
    $this->setSpeed("3000");
    $this->runScript("jQuery('iframe').attr('id', 'mytestiframe')");
    $this->selectFrame("mytestiframe");
    $this->setSpeed("0");
    try {
        $this->assertTrue($this->isElementPresent('//img[@class="FCK__SMWquery"]'), 'Rich text output is not as expected');
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->selectWindow("null");
    $this->click("id=toggle_wpTextbox1");
    try {
        $this->assertEquals($askWikitext, $this->getValue('id=wpTextbox1'), 'Wikitext output is not as expected');
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("id=toggle_wpTextbox1");
    try {
        $this->assertTrue($this->isElementPresent('//img[@class="FCK__SMWquery"]'), 'Rich text output is not as expected');
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }
}
?>