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

class TestWebservice extends SeleniumTestCase_Base {

  public function testWebserviceWikiToHtmlTransformation() {
    $wsWikitext = "{{#ws:EvriPersonData| urlSuffix = paul allen| ?result.birthPlace| ?result.educationInfo| ?result.sourceURL| ?result.occupation| ?result.birthDate| ?result.wikipediaParagraph| _format=list}}";

    $this->login();
    $this->open("/mediawiki/index.php?title=Testws1&action=edit");
    $this->setSpeed("3000");
    if ($this->isElementPresent("//a[@id='toggle_wpTextbox1'][text()='Show WikiTextEditor']")) {
      $this->click("//a[@id='toggle_wpTextbox1'][text()='Show WikiTextEditor']");
    }
    $this->setSpeed("0");
    $this->type("wpTextbox1", $wsWikitext);
    $this->click("id=wpSave");
    $this->waitForPageToLoad("30000");
    $this->click("link=Edit");
    $this->waitForPageToLoad("30000");
    $this->setSpeed("3000");
    $this->runScript("jQuery('iframe').attr('id', 'mytestiframe')");
    $this->selectFrame("mytestiframe");
    $this->setSpeed("0");
    try {
      $this->assertTrue($this->isElementPresent("//img[@class=\"FCK__SMWwebservice\"]"), 'Webservice image is not present in rich text');
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
      array_push($this->verificationErrors, $e->toString());
    }
    $this->selectWindow("null");
    $this->click("id=toggle_wpTextbox1");
    try {
      $this->assertEquals($wsWikitext, $this->getValue("id=wpTextbox1"), 'Webservice wikitext is not as expected');
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
      array_push($this->verificationErrors, $e->toString());
    }
    $this->click("id=toggle_wpTextbox1");
    try {
      $this->assertTrue($this->isElementPresent("//img[@class=\"FCK__SMWwebservice\"]"), 'Webservice image is not present in rich text');
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
      array_push($this->verificationErrors, $e->toString());
    }
  }

}

?>
