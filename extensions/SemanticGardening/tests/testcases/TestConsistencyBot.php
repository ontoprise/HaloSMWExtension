<?php
class TestConsistencyBot extends PHPUnit_Framework_TestCase {
	var $saveGlobals = array();

	function setUp() {
		 exec('runBots smw_consistencybot');
	}

	function tearDown() {
		 
	}

	function testCheckCategoryCycle() {
		$db = wfGetDB(DB_SLAVE);
		$res = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_consistencybot', 'gi_type'=>10501, 'p1_title'=>'Person'));
		$this->assertNotEquals($res, false);
	}

	function testCheckPropertyCycle() {
		$db = wfGetDB(DB_SLAVE);
		$res = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_consistencybot', 'gi_type'=>10501, 'p1_title'=>'Has_Ancestor'));
		$this->assertNotEquals($res, false);
	}

	
}
?>