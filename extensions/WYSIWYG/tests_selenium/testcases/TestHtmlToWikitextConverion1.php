<?php
require_once dirname(__FILE__) . '/../../../../tests/tests_halo/SeleniumTestCase_Base.php';

class TestHtmlToWikitextConverion1 extends SeleniumTestCase_Base
{
  public function testHtmlToWikiConversion()
  {
    $this->open("/mediawiki/index.php?title=Testhtmltowiki&action=edit&mode=wysiwyg");
    $this->runScript("CKEDITOR.instances.wpTextbox1.setData('');");
    $this->setSpeed("2000");
    $this->runScript("CKEDITOR.instances.wpTextbox1.insertHtml('<ul>< LI style=\'color:red; text-decoration:underline;\'>list item 1<br></li><LI>list item 2<br></li><LI>list item 3<br>< / li ></UL>')");
    $this->runScript("ToggleCKEditor('toggle','wpTextbox1');");
    $this->setSpeed("0");  
        
    try {
        $this->assertNotEquals("''", $this->getValue("id=wpTextbox1"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, "Conversion error: the resulting wikitext is empty");
    }
  }
}
?>