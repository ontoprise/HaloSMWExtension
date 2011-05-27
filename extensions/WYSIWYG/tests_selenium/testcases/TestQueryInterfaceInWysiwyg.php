<?php

require_once dirname(__FILE__) . '/../../../../tests/tests_halo/SeleniumTestCase_Base.php';


class TestQueryInterfaceInWysiwyg extends SeleniumTestCase_Base
{

  public function testMyTestCase()
  {
    $this->open("/mediawiki/index.php?title=France&action=edit&mode=wikitext");
    $this->type("wpTextbox1", "[[HasCitizen::33333333]]\n[[HasCapital::Paris]]\n[[Category:State]]");
    $this->click("wpSave");
    $this->waitForPageToLoad("30000");
    $this->open("/mediawiki/index.php?title=Portugal&action=edit&mode=wikitext");
    $this->type("wpTextbox1", "[[HasCitizen::44444444444]]\n[[HasCapital::Lissabon]]\n[[Category:State]]");
    $this->click("wpSave");
    $this->waitForPageToLoad("30000");
    $this->open("/mediawiki/index.php?title=Germany&action=edit&mode=wikitext");
    $this->type("wpTextbox1", "[[HasCitizen::55555555]]  [[HasCapital::Berlin]] [[Category:State]]");
    $this->click("wpSave");
    $this->waitForPageToLoad("30000");
    $this->open("/mediawiki/index.php?title=WYSIWYGTest&action=edit&mode=wysiwyg");
    for ($second = 0; ; $second++) {
        if ($second >= 60) $this->fail("timeout");
        try {
            if ($this->isElementPresent("//*[@id=\"cke_36\"]")) break;
        } catch (Exception $e) {}
        sleep(1);
    }

    $this->runScript("CKEDITOR.instances.wpTextbox1.setData(\"\")");
    $this->setSpeed("5000");
    $this->click("//*[@id=\"cke_36\"]");
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
    $this->click("//span[@class='cke_dialog_ui_button'][text()='OK']");
    try {
        $this->assertTrue($this->isElementPresent("//img[@class='FCK__SMWquery'][@align=''][@src='http://localhost/mediawiki/extensions/WYSIWYG/ckeditor/images/spacer.gif?t=AA4E4NT'][@_cke_realelement='%3Cspan%20class%3D%22fck_smw_query%22%3E%7B%7B%23ask%3A%20%5B%5BCategory%3AState%5D%5DfckLRfckLR%7C%20%3FHasCitizen%20fckLRfckLR%7C%20format%3DtablefckLRfckLR%7C%20merge%3DfalsefckLRfckLR%7C%7D%7D%3C%2Fspan%3E'][@_cke_real_node_type='1'][@alt='Unknown Object'][@_cke_real_element_type='span']"), "Element not present: //img[@class='FCK__SMWquery'][@align=''][@src='http://localhost/mediawiki/extensions/WYSIWYG/ckeditor/images/spacer.gif?t=AA4E4NT'][@_cke_realelement='%3Cspan%20class%3D%22fck_smw_query%22%3E%7B%7B%23ask%3A%20%5B%5BCategory%3AState%5D%5DfckLRfckLR%7C%20%3FHasCitizen%20fckLRfckLR%7C%20format%3DtablefckLRfckLR%7C%20merge%3DfalsefckLRfckLR%7C%7D%7D%3C%2Fspan%3E'][@_cke_real_node_type='1'][@alt='Unknown Object'][@_cke_real_element_type='span']");
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