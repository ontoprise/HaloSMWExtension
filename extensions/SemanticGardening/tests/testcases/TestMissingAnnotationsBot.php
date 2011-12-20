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

global $sgagIP;
require_once( $sgagIP . '/includes/SGA_GardeningIssues.php');
require_once $sgagIP . '/includes/bots/SGA_MissingAnnotationsBot.php';
require_once 'Util.php';
class TestMissingAnnotationsBot extends PHPUnit_Framework_TestCase {
    

    function setUp() {
       	$cd = isWindows() ? "" : "sh ";
         exec($cd.'runBots smw_missingannotationsbot -nolog -p "MA_PART_OF_NAME=,MA_CATEGORY_RESTRICTION="');
    }

    function tearDown() {
         
    }

 function testMissingAnnotations() {
        $db = wfGetDB(DB_SLAVE);
        $res = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_missingannotationsbot', 'gi_type'=>SMW_GARDISSUE_NOTANNOTATED_PAGE,  'p1_title'=>'NoAnnotation'));
        $this->assertNotEquals($res, false);
      
    }

   
    
}
