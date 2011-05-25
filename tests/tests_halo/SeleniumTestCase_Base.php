<?php
require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class SeleniumTestCase_Base extends PHPUnit_Extensions_SeleniumTestCase
{

	public static $browsers = array(
	array(
        'name'    => 'Firefox 3.6',
        'browser' => '*firefox C:/Programme/Mozilla Firefox/firefox.exe',
		'browser' => '*firefox',
		),
	array(
        'name'    => 'Internet Explorer 8',
        'browser' => '*iexplore C:/Programme/Internet Explorer/iexplore.exe',
		'browser' => '*iexplore',
		)
	);

	protected function setUp()
	{
		$this->setBrowserUrl("http://localhost/");
	}
}

?>