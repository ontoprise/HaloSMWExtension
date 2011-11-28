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

global $sgagIP;
require_once( $sgagIP . '/includes/SGA_GardeningIssues.php');
require_once $sgagIP . '/includes/bots/SGA_UndefinedEntitiesBot.php';
require_once 'Util.php';
class TestUndefinedEntitiesBot extends PHPUnit_Framework_TestCase {
    

    function setUp() {
    	$cd = isWindows() ? "" : "sh ";
         exec($cd.'runBots smw_undefinedentitiesbot');
    }

    function tearDown() {
         
    }
function testInstanceWithoutCat() {
        $db = wfGetDB(DB_SLAVE);
        $res = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_undefinedentitiesbot', 'gi_type'=>SMW_GARDISSUE_INSTANCE_WITHOUT_CAT, 'p1_title'=>'Main_Page'));
        $this->assertNotEquals($res, false);
      
    }

function testPropertyUndefined() {
        $db = wfGetDB(DB_SLAVE);
        $res = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_undefinedentitiesbot', 'gi_type'=>SMW_GARDISSUE_PROPERTY_UNDEFINED, 'p1_title'=>'Has_owner',  'p2_title'=>'Audi_TT'));
        $this->assertNotEquals($res, false);
      
    }
    
    function testCategoryUndefined() {
        $db = wfGetDB(DB_SLAVE);
        $res = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_undefinedentitiesbot', 'gi_type'=>SMW_GARDISSUE_CATEGORY_UNDEFINED,  'p1_title'=>'Coupé',  'p2_title'=>'Audi_TT'));
        $this->assertNotEquals($res, false);
      
    }
    
    function testRelationTargetUndefined() {
        $db = wfGetDB(DB_SLAVE);
        $res = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_undefinedentitiesbot', 'gi_type'=>SMW_GARDISSUE_RELATIONTARGET_UNDEFINED, 'p1_title'=>'4_cylinder'));
        
        $res3 = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_undefinedentitiesbot', 'gi_type'=>SMW_GARDISSUE_RELATIONTARGET_UNDEFINED, 'p1_title'=>'3_cylinder'));
        $this->assertNotEquals($res, false);

        $this->assertNotEquals($res3, false);
      
    }
    
    
}
