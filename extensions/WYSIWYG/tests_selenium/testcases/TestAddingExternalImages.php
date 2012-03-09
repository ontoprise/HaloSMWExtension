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

class TestAddingExternalImages extends SeleniumTestCase_Base
{

  public function testWithExternalImagesDisabled()
  {
    $this->login();
    $this->open("/mediawiki/index.php?title=Mynewtestpage2&action=edit&mode=wysiwyg");
    $this->runScript("CKEDITOR.instances.wpTextbox1.setData(\"\")");
    for ($second = 0; ; $second++) {
        if ($second >= 60) $this->fail("Element not present: //a[contains(@class, 'cke_button_image')]/span[1]");
        try {
            if ($this->isElementPresent("//a[contains(@class, 'cke_button_image')]/span[1]")) break;
        } catch (Exception $e) {}
        sleep(1);
    }

    $this->click("//a[contains(@class, 'cke_button_image')]/span[1]");
    for ($second = 0; ; $second++) {
        if ($second >= 60) $this->fail("Element not present: //input[@class='cke_dialog_ui_input_text'][@type='text']");
        try {
            if ($this->isElementPresent("//input[@class='cke_dialog_ui_input_text'][@type='text']")) break;
        } catch (Exception $e) {}
        sleep(1);
    }

    $this->type("//input[@class='cke_dialog_ui_input_text'][@type='text']", "http://www.google.de/intl/en_com/images/srpr/logo1w.png");
    $this->click("//span[@class='cke_dialog_ui_button'][text()='OK']");
    $this->click("wpSave");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isElementPresent("link=exact:http://www.google.de/intl/en_com/images/srpr/logo1w.png"), "Element not present: link=exact:http://www.google.de/intl/en_com/images/srpr/logo1w.png");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }
}
?>
