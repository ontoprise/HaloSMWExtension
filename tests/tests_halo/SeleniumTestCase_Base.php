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

require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class SeleniumTestCase_Base extends PHPUnit_Extensions_SeleniumTestCase
{

	public static $browsers = array(
	array(
        'name'    => 'Firefox 3.6 on Windows',
		'browser' => '*firefox',
		'host' => 'localhost'
	),
	array(
        'name'    => 'Internet Explorer 8',
		'browser' => '*iexplore',
		'host' => 'localhost'
	)
	,
	array(
        'name'    => 'Firefox 4 on Windows',
		'browser' => '*firefox C:\Programme\Mozilla Firefox4\firefox.exe',
		'host' => 'localhost'
	)
	);

	protected function setUp()
	{
		$this->setBrowserUrl("http://localhost/");
	}

	protected function login()
	{
		$this->open("/mediawiki/index.php?title=Special:UserLogin");
		$this->type("wpName1", "WikiSysop");
		$this->type("wpPassword1", "root");
		$this->click("wpLoginAttempt");
		$this->waitForPageToLoad("30000");
	}

	protected function logout()
	{
		$this->open("/mediawiki/index.php/Special:UserLogout");
	}

}

?>