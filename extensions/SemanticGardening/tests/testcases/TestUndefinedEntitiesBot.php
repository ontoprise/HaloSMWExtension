<?php
global $sgagIP;
require_once( $sgagIP . '/includes/SGA_GardeningIssues.php');
require_once '../includes/bots/SGA_UndefinedEntitiesBot.php';
class TestUndefinedEntitiesBot extends PHPUnit_Framework_TestCase {
    

    function setUp() {
         exec('./runBots smw_undefinedentitiesbot');
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
        $res2 = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_undefinedentitiesbot', 'gi_type'=>SMW_GARDISSUE_RELATIONTARGET_UNDEFINED, 'p1_title'=>'Rintheimer_Hauptstrasse_70'));
        $res3 = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_undefinedentitiesbot', 'gi_type'=>SMW_GARDISSUE_RELATIONTARGET_UNDEFINED, 'p1_title'=>'3_cylinder'));
        $this->assertNotEquals($res, false);
        $this->assertNotEquals($res2, false);
        $this->assertNotEquals($res3, false);
      
    }
    
    
}
?>