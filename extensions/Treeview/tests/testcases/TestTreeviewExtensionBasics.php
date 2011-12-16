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
 * @file
 * @ingroup TreeView
 *
 * Tests the basic setup of the TreeView extension
 * 
 * @author Thomas Schweitzer
 * Date: 02.12.2011
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the TreeView extension. It is not a valid entry point.\n" );
}

class TestTreeviewExtensionBasicsSuite extends PHPUnit_Framework_TestSuite
{
	
	public static function suite() {
		
		$suite = new TestTreeviewExtensionBasicsSuite();
		$suite->addTestSuite('TestTreeviewExtensionBasics');
		return $suite;
	}
	
	protected function setUp() {
   	}
	
	protected function tearDown() {
	}
	
}

/**
 * This class tests the basic setup of the treeview extension
 * 
 * @author thsc
 *
 */
class TestTreeviewExtensionBasics extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = FALSE;

	/**
     * Checks if the extension is set up correctly.
     * 
     */
    public function testExtensionBasics() {
    	
    	// Check if the version is defined
    	$this->assertTrue(defined('TV_TREEVIEW_VERSION'), 
    	                  "The treeview extension is not included in LocalSettings.php.");
    	
    	// Check if message file is loaded
    	$this->assertEquals('Treeview', wfMsg('tv_treeview'), 
    						"The message file is not loaded.");
    	
    	// Check if the Resource Loader modules are defined
		global $wgResourceModules;
		$this->assertArrayHasKey('ext.TreeView.tree', $wgResourceModules,
							"The resource loader module 'ext.TreeView.tree' is missing.");
		
		// Check the existence of the language class
    	$this->assertTrue(class_exists(('TVLanguage')), 
    	                  "The class 'TVLanguage' is not defined.");
    	$this->assertTrue(class_exists(('TVLanguageEn')), 
    	                  "The class 'TVLanguageEn' is not defined.");
    	$this->assertTrue(class_exists(('TVLanguageDe')), 
    	                  "The class 'TVLanguageDe' is not defined.");
		
    	
    }
    
}

