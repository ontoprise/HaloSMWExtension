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


require_once dirname(__FILE__) . '/../../../../tests/tests_halo/SeleniumTestCase_Base.php';


class TestQuerySourceText extends SeleniumTestCase_Base
{

  public function testMyTestCase()
  {
    $this->open("/mediawiki/index.php/Special:QueryInterface");
    $this->click("//td[contains(@id, 'qiDefTab') and text()='Query source']");
    $this->type("fullAskText", "{{#ask: [[Category:Project]]\n[[Start date::<2010-01-01]]\n[[Has member::<q>\n[[Category:Person]]\n[[Knows::Joe Mystery]]\n</q>]]\n| format=table\n| merge=false\n|}}");
    $this->click("//button[@onclick='qihelper.loadFromSource(true)']");
    for ($second = 0; ; $second++) {
        if ($second >= 60) $this->fail("timeout");
        try {
            if ($this->isTextPresent("Your query did not return any results.")) break;
        } catch (Exception $e) {}
        sleep(1);
    }

    try {
        $this->assertTrue($this->isTextPresent("Your query did not return any results."), "Text not present: Your query did not return any results.");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("//button[@onclick='qihelper.loadFromSource(true)']");
    $this->type("fullAskText", "{{#ask: [[Category:Project]]\n[[Start date::<2010-01-01]]\n[[Project member::<q>\n[[Category:Person]]\n[[Knows::Joe Mystery]]\n</q>]] \n| format=table\n| headers=show\n| link=all\n| order=ascending\n| merge=false\n|}}");
    $this->click("//button[@onclick='qihelper.loadFromSource(true)']");
    for ($second = 0; ; $second++) {
        if ($second >= 60) $this->fail("timeout");
        try {
            if ($this->isTextPresent("Another complex project")) break;
        } catch (Exception $e) {}
        sleep(1);
    }

    try {
        $this->assertTrue($this->isTextPresent("Another complex project"), "Text not present: Another complex project");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("fullAskText", "{{#ask: [[Category:Project]]\n[[Start date::<2010-01-01]]\n[[Project member::<q>\n[[Category:Person]]\n[[Knows::Joe Mystery]]\n</q>]] \n| ?Start date = Beginning\n| format=table\n| headers=show\n| link=all\n| order=ascending\n| merge=false\n|}}");
    $this->click("//button[@onclick='qihelper.loadFromSource(true)']");
    for ($second = 0; ; $second++) {
        if ($second >= 60) $this->fail("timeout");
        try {
            if ($this->isTextPresent("1 January 2010")) break;
        } catch (Exception $e) {}
        sleep(1);
    }

    try {
        $this->assertTrue($this->isTextPresent("Another complex project"), "Text not present: Another complex project");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isTextPresent("1 January 2010"), "Text not present: 1 January 2010");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("fullAskText", "{{#ask: [[Category:Project]]\n[[Start date::<2010-01-01]]\n[[Project member::<q>\n[[Category:Person]]\n[[Knows::Joe Mystery]]\n</q>]]\n| ?End date \n| format=table\n| headers=show\n| link=all\n| order=ascending\n| merge=false\n|}}");
    $this->click("//button[@onclick='qihelper.loadFromSource(true)']");
    for ($second = 0; ; $second++) {
        if ($second >= 60) $this->fail("timeout");
        try {
            if ($this->isTextPresent("31 December 2010")) break;
        } catch (Exception $e) {}
        sleep(1);
    }

    try {
        $this->assertTrue($this->isTextPresent("Another complex project"), "Text not present: Another complex project");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isTextPresent("31 December 2010"), "Text not present: 31 December 2010");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }
}
?>
