<?php

require_once dirname(__FILE__) . '/../../../../tests/tests_halo/SeleniumTestCase_Base.php';

class TestRule extends SeleniumTestCase_Base
{
    
  public function testRuleWikiToHtmlTransformation()
  {
    $this->open("/mw156/index.php?title=Testrule1&action=edit");
    $this->setSpeed("3000");
    if($this->isElementPresent("//a[@id='toggle_wpTextbox1'][text()='Show WikiTextEditor']")){
        $this->click("//a[@id='toggle_wpTextbox1'][text()='Show WikiTextEditor']");
    } 
    $this->setSpeed("0");
    $this->type("wpTextbox1", "<rule name=\"AgeRule\" type=\"Calculation\" formula=\"2010-a\" variablespec=\"a#prop#BirthYear;\">?_XRES[prop#Age->?_RESULT] :- ?_XRES[prop#BirthYear->?A] AND ?_RESULT = (2010 - ?A).</rule>");
    $this->click("id=wpSave");
    $this->waitForPageToLoad("30000");
    $this->click("link=Edit");
    $this->waitForPageToLoad("30000");
    $this->setSpeed("3000");
    $this->runScript("jQuery('iframe').attr('id', 'mytestiframe')");
    $this->selectFrame("mytestiframe");
    $this->setSpeed("0");
    try {
        $this->assertTrue($this->isElementPresent("//img[@class=\"FCK__SMWrule\"]"), 'Rule image is not present in rich text');
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->selectWindow("null");
    $this->click("id=toggle_wpTextbox1");
    try {
        $this->assertTrue(
                (bool)preg_match('/^exact:<rule name="AgeRule" type="Calculation" formula="2010-a" variablespec="a#prop#BirthYear;">[\s\S]_XRES\[prop#Age->[\s\S]_RESULT\] :- [\s\S]_XRES\[prop#BirthYear->[\s\S]A\] AND [\s\S]_RESULT = \(2010 - [\s\S]A\)\.<\/rule>$/', $this->getValue("id=wpTextbox1")),
                'Rule wikitext is not as expected');
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("id=toggle_wpTextbox1");
    try {
        $this->assertTrue($this->isElementPresent("//img[@class=\"FCK__SMWrule\"]"), 'Rule image is not present in rich text');
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }
}
?>