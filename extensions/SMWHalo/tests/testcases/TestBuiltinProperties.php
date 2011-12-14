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
if ( isset( $_SERVER ) && array_key_exists( 'REQUEST_METHOD', $_SERVER ) ) {
	die( "This script must be run from the command line\n" );
}

/**
 * This suite tests the additional builtin properties Creation date, Creator and
 * Last modified by.
 *
 * @author thsc
 *
 */

require_once 'CommonClasses.php';

class TestBuiltinPropertiesSuite extends PHPUnit_Framework_TestSuite
{
	private $mArticleManager;

	public static function suite() {
		if (!defined('UNIT_TEST_RUNNING')) {
			define('UNIT_TEST_RUNNING', true);
		}

		$suite = new TestBuiltinPropertiesSuite();
		$suite->addTestSuite('TestBuiltinProperties');
		return $suite;
	}

	protected function setUp() {
		SMWHaloCommon::createUsers(array("U1", "U2"));
		Skin::getSkinNames();

		global $wgUser;
		$wgUser = User::newFromName("U1");
	}

	protected function tearDown() {

	}

}

/**
 * This class tests the automatic creation of additional builtin properties for
 * new articles and changing them when the article is modified.
 * This applies to the builtin properties Creation date, Creator and
 * Last modified by
 *
 *
 * @author thsc
 *
 */
class TestBuiltinProperties extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = FALSE;

	// List of articles that were added during a test.
	private static $mArticleManager;

	public static function setUpBeforeClass() {
		self::$mArticleManager = new ArticleManager();
	}

	/**
	 * Delete all articles that were created during a test.
	 */
	public static function tearDownAfterClass() {
		self::$mArticleManager->deleteArticles("U1");
	}

	/**
	 * Checks if the class SMWArticleBuiltinProperties is registered in the
	 * autoloader.
	 */
	public function testSMWArticleBuiltinPropertiesClassPresent() {
		global $wgAutoloadClasses;
		$this->assertArrayHasKey('SMWArticleBuiltinProperties', $wgAutoloadClasses);
	}

	/**
	 * The additional builtin properties need the hook "NewRevisionFromEditComplete"
	 *
	 * @depends testSMWArticleBuiltinPropertiesClassPresent
	 */
	public function testHookAttached() {
		global $wgHooks;
		 
		$this->assertArrayHasKey('NewRevisionFromEditComplete', $wgHooks);
		$this->assertContains('SMWArticleBuiltinProperties::onNewRevisionFromEditComplete',
		$wgHooks['NewRevisionFromEditComplete']);
		 
	}

	/**
	 * Creates an article and checks if the builtin properties have been created.
	 * @depends testHookAttached
	 */
	public function testCreateArticle() {

		$title = $this->createArticle("U1");
		// Verify that builtin properties exist and have correct values
		$this->assertBuiltinPropertyValues($title);
	}

	/**
	 * Edits an article and checks if the builtin properties have been changed.
	 * @depends testCreateArticle
	 */
	public function testEditArticle() {

		$title = $this->createArticle("U2");
		// Verify that builtin properties exist and have correct values
		$this->assertBuiltinPropertyValues($title);
	}

	/**
	 * Creates an arbitrary article as the given user
	 */
	private function createArticle($user) {
		static $counter = 0;
		// User U1 creates an article
		$articleName = "SomeArticle";
		 
		self::$mArticleManager->createArticles(
		array($articleName => "This is just some article $counter."), $user);
		++$counter;
		return Title::newFromText($articleName);
	}

	/**
	 * Returns all additional builtin properties of the given article
	 * @param $title
	 */
	private function getPropertiesOfArticle(Title $title) {
		$props = array();
		$pcreator = SMWPropertyValue::makeProperty('___CREA');
		$pcreationDate = SMWPropertyValue::makeProperty('___CREADT');
		$pmod = SMWPropertyValue::makeProperty('___MOD');
		$pmdat = SMWPropertyValue::makeProperty( '_MDAT' );
		 
		$props['___CREA']   = smwfGetStore()->getPropertyValues($title, $pcreator);
		$props['___CREADT'] = smwfGetStore()->getPropertyValues($title, $pcreationDate);
		$props['___MOD']    = smwfGetStore()->getPropertyValues($title, $pmod);
		$props['_MDAT']     = smwfGetStore()->getPropertyValues($title, $pmdat); // Builtin prop of SMW
		 
		return $props;
	}

	private function assertBuiltinPropertyValues($title) {
		$properties = $this->getPropertiesOfArticle($title);
		 
		$this->assertEquals(1, count($properties['___CREA']), "Expected one creator.");
		$this->assertEquals(1, count($properties['___CREADT']), "Expected one creation date.");
		$this->assertEquals(1, count($properties['___MOD']), "Expected one modificator.");
		$this->assertEquals(1, count($properties['_MDAT']), "Expected one modification date.");
		 
		$lastRevision = Revision::newFromTitle($title);
		$lastTimestamp = $lastRevision->getTimestamp();
		$modifier = $lastRevision->getUserText(Revision::RAW);
		$firstRevision = $title->getFirstRevision();
		$creator = $firstRevision->getUserText(Revision::RAW);
		$firstTimestamp = $firstRevision->getTimestamp();
		 
		 
		// Verify the values of the builtin properties
		foreach ($properties['___CREA'] as $value => $wikiPage) {
			$this->assertEquals("User:$creator", $value, "Expected creator User:$creator");
		}
		 
		foreach ($properties['___CREADT'] as $value => $timeValue) {
			$time = str_replace(':', '', $timeValue->getTimeString());
			$dt = $timeValue->getYear()
			. ($timeValue->getMonth() < 10 ? '0' : '')
			. $timeValue->getMonth()
			. ($timeValue->getDay() < 10 ? '0' : '')
			. $timeValue->getDay()
			. $time;
			$this->assertEquals($firstTimestamp, $dt, "Expected creation date $firstTimestamp");
		}
		 
		foreach ($properties['___MOD'] as $value => $wikiPage) {
			$this->assertEquals("User:$modifier", $value, "Expected last modifier User:$modifier");
		}
		 
		foreach ($properties['_MDAT'] as $value => $timeValue) {
			$time = str_replace(':', '', $timeValue->getTimeString());
			$dt = $timeValue->getYear()
			. ($timeValue->getMonth() < 10 ? '0' : '')
			. $timeValue->getMonth()
			. ($timeValue->getDay() < 10 ? '0' : '')
			. $timeValue->getDay()
			. $time;
			$this->assertEquals($lastTimestamp, $dt, "Expected modification date $firstTimestamp");
		}
	}

}
