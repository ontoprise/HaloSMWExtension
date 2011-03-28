<?php

require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class Example extends PHPUnit_Extensions_SeleniumTestCase
{
  protected function setUp()
  {
    $this->setBrowser("*chrome");
    $this->setBrowserUrl("http://localhost/");
  }

  public function testMyTestCase()
  {
		$this->open("/mediawiki/index.php/Main_Page");
		$this->click("personal_logout");
		$this->waitForPageToLoad("30000");
  }
}
?>