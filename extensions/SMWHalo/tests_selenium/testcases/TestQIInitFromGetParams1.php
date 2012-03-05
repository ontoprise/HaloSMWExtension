<?php
require_once dirname(__FILE__) . '/../../../../tests/tests_halo/SeleniumTestCase_Base.php';

class TestQIInitFromGetParams1 extends SeleniumTestCase_Base
{

  public function testMyTestCase()
  {
    $this->open("/mediawiki/index.php?title=Special:QueryInterface&query=%5B%5BCategory%3APerson%5D%5D");
    try {
        $this->assertTrue($this->isElementPresent("link=Person"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }
}
?>