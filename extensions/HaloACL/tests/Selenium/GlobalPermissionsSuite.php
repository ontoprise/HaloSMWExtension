<?php

require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class Example extends PHPUnit_Extensions_SeleniumTestCase
{
  protected function setUp()
  {
    $this->setBrowser("*chrome");
//    $this->setBrowser("*iexploreproxy");
    $this->setBrowserUrl("http://localhost/");
  }

  public function login()
  {
		$this->open("/mediawiki/index.php?title=Special:UserLogin");
		$this->type("wpName1", "WikiSysop");
		$this->type("wpPassword1", "root");
		$this->click("wpLoginAttempt");
		$this->waitForPageToLoad("30000");
  }

  public function testGlobalPermissionsUpload()
  {
    $this->login();
    
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
    echo "Value:\n";
    echo $val;
  
    $this->logout();
  }
  
  public function logout()
  {
		$this->open("/mediawiki/index.php/Special:UserLogout");
  }

}
?>