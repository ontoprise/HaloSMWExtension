<?php
/*
 * Copyright (C) ontoprise GmbH
 *
 * Vulcan Inc. (Seattle, WA) and ontoprise GmbH (Karlsruhe, Germany)
 * expressly waive any right to enforce any Intellectual Property
 * Rights in or to any enhancements made to this program.
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
// check if original file was called from command line or Webserver
if ( isset( $_SERVER ) && array_key_exists( 'REQUEST_METHOD', $_SERVER ) ) {
    die( "This script must be run from the command line\n" );
}

require_once dirname(__FILE__) . '/../../../../tests/tests_halo/SeleniumTestCase_Base.php';

class TestAutocompletetion extends SeleniumTestCase_Base
{

  public function test_autocompletetion()
  {
    $this->login();
    $this->open("/mediawiki/index.php?title=MyNewTestPage&action=edit");
    $this->type("wpTextbox1", "[[");
    $this->controlKeyDown();
    $this->altKeyDown();
    $this->typeKeys("wpTextbox1", "' '");
    for ($second = 0; ; $second++) {
        if ($second >= 60) $this->fail("Element not present: //*[@id=\"selected0\" and text()='Affiliation']");
        try {
//            if ($this->isElementPresent("//*[@id=\"selected0\" and text()='Affiliation']")) break;
			if ($this->isElementPresent("id=selected0")) break;
        } catch (Exception $e) {}
        sleep(1);
    }

    try {
        $this->assertTrue($this->isElementPresent("id=selected0"), "Element not present: id=selected0");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isElementPresent("id=selected1"), "Element not present: id=selected1");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isElementPresent("id=selected2"), "Element not present: id=selected2");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isElementPresent("id=selected3"), "Element not present: id=selected3");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->altKeyUp();
    $this->controlKeyUp();
    $this->type("wpTextbox1", "");
    $this->type("wpTextbox1", "{{");
    $this->controlKeyDown();
    $this->altKeyDown();
    $this->typeKeys("wpTextbox1", "' '");
    for ($second = 0; ; $second++) {
        if ($second >= 60) $this->fail("Element not present: id=ac_toomuchresults");
        try {
            if ($this->isElementPresent("id=ac_toomuchresults")) break;
        } catch (Exception $e) {}
        sleep(1);
    }

    try {
        $this->assertTrue($this->isElementPresent("id=ac_toomuchresults"), "Element not present: id=ac_toomuchresults");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }
}
?>
