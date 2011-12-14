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
require_once($srefgIP.'/includes/operations/SRF_RenameCategory.php');
require_once($srefgIP.'/tests/resources/SRF_ArticleManager.php');

class SRFTestRenameCategory extends PHPUnit_Framework_TestCase {


	protected $backupGlobals = FALSE;

    static function setUpBeforeClass() {
        global $srfRenameCategoryArticles;
        $articleManager = new ArticleManager();
        $articleManager->createArticles($srfRenameCategoryArticles);
    }
	

	function tearDown() {

	}

	function testRenameCategory() {
		$r = new SRFRenameCategoryOperation("Person", "Citizen");
		$logMessages=array();
        $r->refactor(false, $logMessages);
        $log = reset($logMessages['Category:Man']);
        $this->assertContains('[[Category:Citizen]]', $log->getWikiText());
	}

	function testRenameCategoryAsValue() {
		$r = new SRFRenameCategoryOperation("Person", "Citizen");
        $logMessages=array();
        $r->refactor(false, $logMessages);
        $log = reset($logMessages['Property:Has employee']);
        $this->assertContains('Category:Citizen', $log->getWikiText());
	}

	

	function testRenameCategoryAsLink() {
		$r = new SRFRenameCategoryOperation("Man", "Citizen");
        $logMessages=array();
        $r->refactor(false, $logMessages);
      
        $log = reset($logMessages['Testlink']);
        $this->assertContains('[[:Category:Citizen]]', $log->getWikiText());
	}

	function testRenameCategoryInQuery() {
		$r = new SRFRenameCategoryOperation("Man", "Citizen");
        $logMessages=array();
        $r->refactor(false, $logMessages);
    
        $log = reset($logMessages['All men']);
        $this->assertContains('[[Category:Citizen]]', $log->getWikiText());

	}

	function testRenameCategoryInQuery2() {
		$r = new SRFRenameCategoryOperation("Man", "Citizen");
        $logMessages=array();
        $r->refactor(false, $logMessages);
      
        $log = reset($logMessages['Property:Is human']);
        $this->assertContains('::Category:Citizen', $log->getWikiText());

	}

	function testRenameCategoryInQuery3() {
		$r = new SRFRenameCategoryOperation("Man", "Citizen");
        $logMessages=array();
        $r->refactor(false, $logMessages);
      
        $log = reset($logMessages['Category info']);
        $this->assertContains(':Category:Citizen', $log->getWikiText());

	}



}