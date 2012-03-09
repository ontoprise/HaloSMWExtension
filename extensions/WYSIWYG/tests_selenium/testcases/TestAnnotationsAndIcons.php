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

class TestAnnotationsAndIcons extends SeleniumTestCase_Base
{

	public function testMyTestCase()
	{
    $this->login();
		$this->open("/mediawiki/index.php?title=MyTestPage&action=edit");
		$this->type("//textarea[@id='wpTextbox1']", "This is Berlin located in [[located in::Germany]]. The city is also is [[is capital::Germany]] of Germany and has [[Inhabitants::3524000|3.5 Mio]].\n\n== Heading 2 ==\n\nHere follows a nowiki part <nowiki>'''Note the bold'''</nowiki>. It ends here.\n\n=== Heading 2.2 ===\n\n*List item1\n**List item11\n*List item2\n\n=== Heading 2.3 ===\n\nThis wiki contains {{#ask:[[Category:Person]]|format=list}} as a person. {{Copyright}}\n\n[[Category:City]]");
		$this->click("wpSave");
		$this->waitForPageToLoad("30000");
		$this->click("//*[@id=\"ca-edit\"]");
		$this->waitForPageToLoad("30000");
		$this->setSpeed("2000");
		$this->runScript("jQuery('iframe').attr('id', 'mytestiframe')");
		$this->selectFrame("id=mytestiframe");
		$this->setSpeed("0");
		try {
			$this->assertTrue($this->isElementPresent("//span[@property='located in'][text()='Germany']"), "Element not present: //span[@property='located in'][text()='Germany']");
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertTrue($this->isElementPresent("//span[@property='is capital'][text()='Germany']"), "Element not present: //span[@property='is capital'][text()='Germany']");
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertTrue($this->isElementPresent("//span[@property='Inhabitants::3524000'][text()='3.5 Mio']"), "Element not present: //span[@property='Inhabitants::3524000'][text()='3.5 Mio']");
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		$this->selectFrame("relative=top");
		$this->click("wpSave");
		$this->waitForPageToLoad("30000");
		try {
			$this->assertTrue($this->isTextPresent("Facts about MyNewTestPage"), "Text not present: Facts about MyNewTestPage");
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertTrue($this->isTextPresent("Inhabitants"), "Text not present: Inhabitants");
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertTrue($this->isTextPresent("3524000"), "Text not present: 3524000");
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertTrue($this->isTextPresent("Is capital"), "Text not present: Is capital");
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertTrue($this->isTextPresent("Germany"), "Text not present: Germany");
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertTrue($this->isTextPresent("Located in"), "Text not present: Located in");
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		$this->click("//*[@id=\"ca-edit\"]");
		$this->waitForPageToLoad("30000");
		$this->open("/mediawiki/index.php?title=MyTestPage&action=edit");
		for ($second = 0; ; $second++) {
			if ($second >= 60) $this->fail("Text not present: *List item1");
			try {
				if ($this->isTextPresent("*List item1")) break;
			} catch (Exception $e) {}
			sleep(1);
		}

		try {
			$this->assertTrue($this->isTextPresent("This is Berlin located in [[located in::Germany]]. The city is also is [[is capital::Germany]] of Germany and has [[Inhabitants::3524000|3.5 Mio]]."), "Text not present: This is Berlin located in [[located in::Germany]]. The city is also is [[is capital::Germany]] of Germany and has [[Inhabitants::3524000|3.5 Mio]].");
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertTrue($this->isTextPresent("== Heading 2 =="));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertTrue($this->isTextPresent("Here follows a nowiki part <nowiki>'''Note the bold'''</nowiki>. It ends here."), "Text not present: Here follows a nowiki part <nowiki>'''Note the bold'''</nowiki>. It ends here.");
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertTrue($this->isTextPresent("=== Heading 2.2 ==="), "Text not present: === Heading 2.2 ===");
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertTrue($this->isTextPresent("*List item1"), "Text not present: *List item1");
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertTrue($this->isTextPresent("**List item11"), "Text not present: **List item11");
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertTrue($this->isTextPresent("*List item2"), "Text not present: *List item2");
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertTrue($this->isTextPresent("=== Heading 2.3 ==="), "Text not present: === Heading 2.3 ===");
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertTrue($this->isTextPresent("This wiki contains {{#ask:[[Category:Person]]|format=list}} as a person. {{Copyright}}"), "Text not present: This wiki contains {{#ask:[[Category:Person]]|format=list}} as a person. {{Copyright}}");
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertTrue($this->isTextPresent("[[Category:City]]"), "Text not present: [[Category:City]]");
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
	}
}
?>
