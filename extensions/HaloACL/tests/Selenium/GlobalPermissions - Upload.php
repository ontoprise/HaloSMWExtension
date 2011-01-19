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
		$this->open("/mediawiki/index.php/Special:HaloACL");
		$this->click("//li[@id='globalPermissionsPanel_button']/a/em");
		for ($second = 0; ; $second++) {
				if ($second >= 60) $this->fail("timeout");
				try {
						if ($this->isElementPresent("haclGPPermissionsTitle")) break;
				} catch (Exception $e) {}
				sleep(1);
		}

		$this->select("haclGPPermissionSelector", "label=Upload");
		for ($second = 0; ; $second++) {
				if ($second >= 60) $this->fail("timeout");
				try {
						if ($this->isElementPresent("link=Knowledge consumer")) break;
				} catch (Exception $e) {}
				sleep(1);
		}

		try {
				$this->assertTrue($this->isElementPresent("//li[@id='haclgt-405']/a/span[contains(@class, 'normal')]"));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
				array_push($this->verificationErrors, $e->toString());
		}
		try {
				$this->assertTrue($this->isElementPresent("//li[@id='haclgt-406']/a/span[contains(@class, 'checked')]"));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
				array_push($this->verificationErrors, $e->toString());
		}
		$val = $this->getText("//li[@id='haclgt-44']");
  }
}
?>