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
		$this->open("http://localhost/mediawiki/index.php/Special:SpecialPages");
		$this->click("link=FacetedSearch");
		$this->waitForPageToLoad("30000");
		try {
				$this->assertTrue($this->isTextPresent("Faceted Search"));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
				array_push($this->verificationErrors, $e->toString());
		}
  }
}
?>