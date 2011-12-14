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
require_once($srefgIP.'/includes/operations/SRF_RenameProperty.php');
require_once($srefgIP.'/tests/resources/SRF_ArticleManager.php');

class SRFTestRenameProperty extends PHPUnit_Framework_TestCase {


	protected $backupGlobals = FALSE;

	static function setUpBeforeClass() {
		global $srfRenamePropertyArticles;
		$articleManager = new ArticleManager();
		$articleManager->createArticles($srfRenamePropertyArticles);
	}

	function tearDown() {

	}

	function testRenameProperty() {
		$r = new SRFRenamePropertyOperation("Has son", "HasSon");
        $r->refactor(false, $logMessages);
        $log = reset($logMessages['Bernd']);
		$this->assertContains('HasSon', $log->getWikiText());

	}

	function testRenamePropertyAsValue() {
		$r = new SRFRenamePropertyOperation("Has child", "HasChild");
		$logMessages=array();
		$r->refactor(false, $logMessages);
		
        $log = reset($logMessages['Property:Has kid']);
		$this->assertContains('Property:HasChild', $log->getWikiText());

	}
	
    function testRenamePropertyAsSubpropertyValue() {
        $r = new SRFRenamePropertyOperation("Has child", "HasChild");
        $logMessages=array();
        $r->refactor(false, $logMessages);
        
        $log = reset($logMessages['Property:Has son']);
        $this->assertContains('Property:HasChild', $log->getWikiText());

    }

	function testRenamePropertyAsLink() {
		$r = new SRFRenamePropertyOperation("Has son", "HasSon");
        $r->refactor(false, $logMessages);
        $log = reset($logMessages['Pages']);
        $this->assertContains('Property:HasSon', $log->getWikiText());

	}

	function testRenamePropertyInQuery() {
		$r = new SRFRenamePropertyOperation("Has son", "HasSon");
        $r->refactor(false, $logMessages);
        $log = reset($logMessages['All sons']);
        $this->assertContains('HasSon::', $log->getWikiText());

	}

	function testRenamePropertyInQuery2() {
		$r = new SRFRenamePropertyOperation("Has son", "HasSon");
        $r->refactor(false, $logMessages);
        $log = reset($logMessages['All sons']);
        $this->assertContains('?HasSon', $log->getWikiText());

	}

	
}