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


class TestTransformationOfSemanticData extends SeleniumTestCase_Base
{

	public function testMyTestCase()
	{        

    $this->login();
		$this->open("/mediawiki/index.php?title=Helium&action=edit&mode=wikitext");
		$this->type("wpTextbox1", "Helium is the chemical element with atomic number 2, which is represented by the symbol He.It is a  [[has color::none|colorless]], [[smells like::odorless]], tasteless, non-toxic, inert monatomic gas that heads the noble gas group in the periodic table.");
		$this->click("wpSave");
		$this->waitForPageToLoad("30000");
		$this->click("link=Edit");
		$this->waitForPageToLoad("30000");
		for ($second = 0; ; $second++) {
			if ($second >= 60) $this->fail("Element not present: //span[@class='fck_mw_property'][@property='has color::none'][text()='colorless']");
			try {
				if ($this->isElementPresent("//span[@class='fck_mw_property'][@property='has color::none'][text()='colorless']")) break;
			} catch (Exception $e) {}
			sleep(1);
		}

		try {
			$this->assertTrue($this->isElementPresent("//span[@class='fck_mw_property'][@property='has color::none'][text()='colorless']"), "Element not present: //span[@class='fck_mw_property'][@property='has color::none'][text()='colorless']");
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertTrue($this->isElementPresent("//span[@class='fck_mw_property'][@property='smells like'][text()='odorless']"), "Element not present: //span[@class='fck_mw_property'][@property='smells like'][text()='odorless']");
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		$this->click("toggle_wpTextbox1");
                $text = 'Helium is the chemical element with atomic number 2';
		for ($second = 0; ; $second++) {
			if ($second >= 60) $this->fail("Text not present: " . $text);
			try {
				if ($this->isTextPresent($text)) 
                                    break;
			} catch (Exception $e) {}
			sleep(1);
		}

		try {
                    $expectedContent = "Helium is the chemical element with atomic number 2, which is represented by the symbol He.It is a [[has color::none|colorless]], [[smells like::odorless]], tasteless, non-toxic, inert monatomic gas that heads the noble gas group in the periodic table.";
                    $actualContent = $this->getValue("wpTextbox1");
                    $this->assertEquals($expectedContent, $actualContent, "wpTextbox1: actual textarea content:\n" . $expectedContent ."\ndoesn't match the expected content:\n" . $actualContent);
                } catch (PHPUnit_Framework_AssertionFailedError $e) {
                    array_push($this->verificationErrors, $e->toString());
                }  
           }
        
}
?>
