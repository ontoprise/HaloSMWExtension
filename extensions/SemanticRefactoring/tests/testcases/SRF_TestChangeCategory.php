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

if ( isset( $_SERVER ) && array_key_exists( 'REQUEST_METHOD', $_SERVER ) ) {
    die( "This script must be run from the command line\n" );
}

global $srefgIP;
require_once($srefgIP.'/includes/SRF_RefactoringOperation.php');
require_once($srefgIP.'/includes/operations/SRF_ChangeCategoryValue.php');
require_once($srefgIP.'/tests/resources/SRF_ArticleManager.php');

class SRFTestChangeCategoryValue extends PHPUnit_Framework_TestCase {

    protected $backupGlobals = FALSE;

    static function setUpBeforeClass() {
        global $srfChangeCategoryArticles;
        $articleManager = new ArticleManager();
        $articleManager->createArticles($srfChangeCategoryArticles);
    }

    function tearDown() {

    }


    function testAddCategory() {
        $r = new SRFChangeCategoryValueOperation(array("Kai"), NULL, "Person");
        $logMessages=array();
        $r->refactor(false, $logMessages);
        $log = reset($logMessages['Kai']);
        print "\n".$log->asWikiText();
        $this->assertContains('[[Category:Person]]', $log->getWikiText());
    }

    function testRemoveCategory() {
       $r = new SRFChangeCategoryValueOperation(array("Thomas"), "Person", NULL);
        $logMessages=array();
        $r->refactor(false, $logMessages);
        $log = reset($logMessages['Thomas']);
        print "\n".$log->asWikiText();
        $this->assertNotContains('[[Category:Person]]', $log->getWikiText());
    }

    function testReplaceCategory() {
        $r = new SRFChangeCategoryValueOperation(array("Michael"), "Human", "Person");
        $logMessages=array();
        $r->refactor(false, $logMessages);
        $log = reset($logMessages['Michael']);
        print "\n".$log->asWikiText();
        $this->assertContains('[[Category:Person]]', $log->getWikiText());
    }

}