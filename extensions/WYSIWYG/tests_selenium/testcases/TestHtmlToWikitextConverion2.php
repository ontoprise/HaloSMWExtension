<?php
require_once dirname(__FILE__) . '/../../../../tests/tests_halo/SeleniumTestCase_Base.php';

class TestHtmlToWikitextConverion2 extends SeleniumTestCase_Base
{
  public function testHtmlToWikiConversion()
  {
    $this->open("/mediawiki/index.php?title=Testhtmltowiki&action=edit&mode=wysiwyg");
    $this->runScript("CKEDITOR.instances.wpTextbox1.setData('');");
    $this->setSpeed("2000");
    $this->runScript("CKEDITOR.instances.wpTextbox1.insertHtml('<span class=\"fck_mw_noinclude\" _fck_mw_tagname=\"noinclude\" _fck_mw_customtag = \" true \">not included text</span>')");
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