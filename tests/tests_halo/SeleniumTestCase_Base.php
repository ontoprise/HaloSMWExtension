<?php
require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class SeleniumTestCase_Base extends PHPUnit_Extensions_SeleniumTestCase
{

	public static $browsers = array(
	array(
        'name'    => 'Firefox 3.6',
	//        'browser' => '*firefox C:/Programme/Mozilla Firefox/firefox.exe',
		'browser' => '*firefox',
	),
	array(
        'name'    => 'Internet Explorer 8',
	//        'browser' => '*iexplore C:/Programme/Internet Explorer/iexplore.exe',
		'browser' => '*iexplore',
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