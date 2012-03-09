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

class TestWikiMarkup1 extends SeleniumTestCase_Base
{
  public function testMyTestCase()
  {
    $this->open("/mediawiki/index.php?title=Mynewtestpagss&action=edit&mode=wysiwyg");
    $this->runScript("CKEDITOR.instances.wpTextbox1.setData(\"This page is called: {{{PAGENAME}}}<br/><br/>Template Call: 2 < 3<br/>{{template|sdf|param=2|pagea2={{Some Other template}}}} and a nowiki part&lt;nowiki&gt;bla<br/>blub done<br/>&lt;/nowiki&gt;<br/><br/>Tepmplates like this {{TempLateBlub|param1={{{arg_x}}}}} can also use parameters.<br/><br/>Here we have magic words like __SGDGfsar__ and __NOTOC__ and __SOFOR__ that should be<br/>replaced.<br/><br/>We have {{#ask:[[Category:Person]]|format=ol}} in the wiki.\")");
    $this->setSpeed("1000");
    for ($second = 0; ; $second++) {
        if ($second >= 60) $this->fail("timeout");
        try {
            if ($this->isElementPresent("//a[@title='Paragraph Format']")) break;
        } catch (Exception $e) {}
        sleep(1);
    }

    $this->click("//a[@title='Paragraph Format']");
    for ($second = 0; ; $second++) {
        if ($second >= 60) $this->fail("timeout");
        try {
            if ($this->isElementPresent("//a/p[text()='Normal']")) break;
        } catch (Exception $e) {}
        sleep(1);
    }

    $this->click("//a/p[text()='Normal']");
    $this->setSpeed("0");
    $this->click("wpSave");
    $this->waitForPageToLoad("30000");
    $this->verifyTextPresent("This page is called: {{{PAGENAME}}}");
    $this->verifyTextPresent("Template Call: 2 < 3");
    $this->verifyTextPresent("{{template|sdf|param=2|pagea2={{Some Other template}}}} and a nowiki part<nowiki>bla");
    $this->verifyTextPresent("blub done");
    $this->verifyTextPresent("</nowiki>");
    $this->verifyTextPresent("Tepmplates like this {{TempLateBlub|param1={{{arg_x}}}}} can also use parameters.");
    $this->verifyTextPresent("Here we have magic words like __SGDGfsar__ and __NOTOC__ and __SOFOR__ that should be");
    $this->verifyTextPresent("replaced.");
    $this->verifyTextPresent("We have {{#ask:[[Category:Person]]|format=ol}} in the wiki.");
    $this->click("//div[@id='ca-edit']/a");
    $this->waitForPageToLoad("30000");
    for ($second = 0; ; $second++) {
        if ($second >= 60) $this->fail("timeout");
        try {
            if ($this->isElementPresent("//a[contains(@class, 'cke_button_source')]")) break;
        } catch (Exception $e) {}
        sleep(1);
    }

    $this->click("//a[contains(@class, 'cke_button_source')]");
    $this->verifyTextPresent("");
  }
}
?>
