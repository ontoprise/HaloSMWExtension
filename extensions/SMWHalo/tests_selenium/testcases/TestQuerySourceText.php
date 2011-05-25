<?php

require_once './../../../../tests/tests_halo/SeleniumTestCase_Base.php';
require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class TestQuerySourceText extends SeleniumTestCase_Base
{

  public function testMyTestCase()
  {
    $this->open("/mediawiki/index.php/Special:QueryInterface");
    $this->click("qiDefTab3");
    $this->type("fullAskText", "{{#ask: [[Category:Project]]\n[[Start date::<2010-01-01]]\n[[Has member::<q>\n[[Category:Person]]\n[[Knows::Joe Mystery]]\n</q>]]\n| format=table\n| merge=false\n|}}");
    $this->click("//button[@onclick='qihelper.loadFromSource(true)']");
    for ($second = 0; ; $second++) {
        if ($second >= 60) $this->fail("timeout");
        try {
            if ($this->isTextPresent("Your query did not return any results.")) break;
        } catch (Exception $e) {}
        sleep(1);
    }

    try {
        $this->assertTrue($this->isTextPresent("Your query did not return any results."), "Text not present: Your query did not return any results.");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("//button[@onclick='qihelper.loadFromSource(true)']");
    $this->type("fullAskText", "{{#ask: [[Category:Project]]\n[[Start date::<2010-01-01]]\n[[Project member::<q>\n[[Category:Person]]\n[[Knows::Joe Mystery]]\n</q>]] \n| format=table\n| headers=show\n| link=all\n| order=ascending\n| merge=false\n|}}");
    $this->click("//button[@onclick='qihelper.loadFromSource(true)']");
    for ($second = 0; ; $second++) {
        if ($second >= 60) $this->fail("timeout");
        try {
            if ($this->isTextPresent("Another complex project")) break;
        } catch (Exception $e) {}
        sleep(1);
    }

    try {
        $this->assertTrue($this->isTextPresent("Another complex project"), "Text not present: Another complex project");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("fullAskText", "{{#ask: [[Category:Project]]\n[[Start date::<2010-01-01]]\n[[Project member::<q>\n[[Category:Person]]\n[[Knows::Joe Mystery]]\n</q>]] \n| ?Start date = Beginning\n| format=table\n| headers=show\n| link=all\n| order=ascending\n| merge=false\n|}}");
    $this->click("//button[@onclick='qihelper.loadFromSource(true)']");
    for ($second = 0; ; $second++) {
        if ($second >= 60) $this->fail("timeout");
        try {
            if ($this->isTextPresent("1 January 2010")) break;
        } catch (Exception $e) {}
        sleep(1);
    }

    try {
        $this->assertTrue($this->isTextPresent("Another complex project"), "Text not present: Another complex project");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isTextPresent("1 January 2010"), "Text not present: 1 January 2010");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("fullAskText", "{{#ask: [[Category:Project]]\n[[Start date::<2010-01-01]]\n[[Project member::<q>\n[[Category:Person]]\n[[Knows::Joe Mystery]]\n</q>]]\n| ?End date \n| format=table\n| headers=show\n| link=all\n| order=ascending\n| merge=false\n|}}");
    $this->click("//button[@onclick='qihelper.loadFromSource(true)']");
    for ($second = 0; ; $second++) {
        if ($second >= 60) $this->fail("timeout");
        try {
            if ($this->isTextPresent("31 December 2010")) break;
        } catch (Exception $e) {}
        sleep(1);
    }

    try {
        $this->assertTrue($this->isTextPresent("Another complex project"), "Text not present: Another complex project");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isTextPresent("31 December 2010"), "Text not present: 31 December 2010");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }
}
?>