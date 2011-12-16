<?php
/*
 * Copyright (C) Vulcan Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program.If not, see <http://www.gnu.org/licenses/>.
 *
 */
if ( isset( $_SERVER ) && array_key_exists( 'REQUEST_METHOD', $_SERVER ) ) {
	die( "This script must be run from the command line\n" );
}


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
		global $wgScript;
		$this->open("$wgScript?title=Special:UserLogin");
		$this->type("wpName1", "WikiSysop");
		$this->type("wpPassword1", "root");
		$this->click("wpLoginAttempt");
		$this->waitForPageToLoad("30000");
  }

  public function testGlobalPermissionsUpload()
  {
		global $wgScript;
		$this->login();
    
		$this->open("$wgScript/Special:HaloACL");
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
		global $wgScript;
		$this->open("$wgScript/Special:UserLogout");
  }

}
?>
