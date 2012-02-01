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
require_once($srefgIP.'/includes/operations/SRF_ChangeTemplate.php');
require_once($srefgIP.'/includes/operations/SRF_ChangeTemplateName.php');
require_once($srefgIP.'/tests/resources/SRF_ArticleManager.php');

class SRFTestChangeTemplate extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = FALSE;

	static function setUpBeforeClass() {
		global $srfChangeTemplateArticles;
		$articleManager = new ArticleManager();
		$articleManager->createArticles($srfChangeTemplateArticles);
	}

	function tearDown() {

	}

	function testChangeParameter() {
		$r = new SRFInstanceLevelOperation(array("Testarticle1"));
        $r->addOperation(new SRFChangeTemplateOperation("Testtemplate", "param1", "newparam1"));
		
		$logMessages=array();
		$r->refactor(false, $logMessages);
		$log = reset($logMessages['Testarticle1']);
		print "\n".$log->asWikiText();
		$this->assertContains('newparam1', $log->getWikiText());
	}

	function testChangeTemplateName() {
		$r = new SRFInstanceLevelOperation(array("Testarticle1"));
        $r->addOperation(new SRFChangeTemplateNameOperation("Testtemplate", "NewTesttemplate"));
		
		$logMessages=array();
		$r->refactor(false, $logMessages);
		$log = reset($logMessages['Testarticle1']);
		print "\n".$log->asWikiText();
		$this->assertContains('NewTesttemplate', $log->getWikiText());
	}



}