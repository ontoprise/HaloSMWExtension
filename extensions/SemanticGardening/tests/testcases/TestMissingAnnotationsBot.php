<?php
global $sgagIP;
require_once( $sgagIP . '/includes/SGA_GardeningIssues.php');
require_once '../includes/bots/SGA_MissingAnnotationsBot.php';
require_once 'Util.php';
class TestMissingAnnotationsBot extends PHPUnit_Framework_TestCase {
    

    function setUp() {
    	$cd = isWindows() ? "" : "./"; 
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
