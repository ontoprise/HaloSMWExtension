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
/**
 * @file
 * @ingroup SMWHaloTests
 *
 * Tests the refactoring facilities
 *
 * @author Kai Kühn
 *
 */
global $srefgIP;
require_once($srefgIP.'/includes/SRF_RefactoringOperation.php');
require_once($srefgIP.'/includes/operations/SRF_ChangeTemplateParameter.php');
require_once($srefgIP.'/tests/resources/SRF_ArticleManager.php');

class SRFTestChangeTemplateParameter extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = FALSE;

	static function setUpBeforeClass() {
		global $srfChangeTemplateArticles;
		$articleManager = new ArticleManager();
		$articleManager->createArticles($srfChangeTemplateArticles);
	}

	function tearDown() {

	}

	function testChangeValue() {
		$r = new SRFChangeTemplateParameterOperation(array("Testarticle1"), "Testtemplate", "param1", "value1", "Newvalue1");
		$logMessages=array();
		$r->refactor(false, $logMessages);
		$log = reset($logMessages['Testarticle1']);
		print "\n".$log->asWikiText();
		$this->assertContains('Newvalue1', $log->getWikiText());
	}
	
	/*function testChangeValue() {
		$r = new SRFChangeTemplateParameterOperation(array("Testinstance"), "Testtemplate", "param1", "value1", "Newvalue1");
		$wikitext = <<<ENDS
This is a test {{Testtemplate|param1=value1|param2=value2}}. No text.
ENDS;
		$wikitext = $r->changeContent($wikitext);

		//print $wikitext;
		$this->assertContains('param1=Newvalue1', $wikitext);
	}

	function testAddValue() {
		$r = new SRFChangeTemplateParameterOperation(array("Testinstance"), "Testtemplate", "param3", NULL, "value3");
		$wikitext = <<<ENDS
This is a test {{Testtemplate|param1=value1|param2=value2}}. No text.
ENDS;
		$wikitext = $r->changeContent($wikitext);

		//print $wikitext;
		$this->assertContains('param3=value3', $wikitext);
	}

	function testDeleteValue() {
		$r = new SRFChangeTemplateParameterOperation(array("Testinstance"), "Testtemplate", "param1", "value1", NULL);
		$wikitext = <<<ENDS
This is a test {{Testtemplate|param1=value1|param2=value2}}. No text.
ENDS;
		$wikitext = $r->changeContent($wikitext);


		$this->assertNotContains('value1', $wikitext);
	}*/


}