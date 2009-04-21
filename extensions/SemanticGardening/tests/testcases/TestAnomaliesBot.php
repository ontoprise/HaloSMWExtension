<?php
global $sgagIP;
require_once( $sgagIP . '/includes/SGA_GardeningIssues.php');
require_once '../includes/bots/SGA_AnomaliesBot.php';
class TestAnomaliesBot extends PHPUnit_Framework_TestCase {
	 

	function setUp() {
		exec('runBots smw_anomaliesbot -p "CATEGORY_NUMBER_ANOMALY=Check%20number%20of%20sub%20categories,CATEGORY_LEAF_ANOMALY=Check%20for%20category%20leafs,CATEGORY_RESTRICTION="');
	}

	function tearDown() {
		 
	}

	function testCategoryLeaf() {
		$db = wfGetDB(DB_SLAVE);
		$res = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_anomaliesbot', 'gi_type'=>SMW_GARDISSUE_CATEGORY_LEAF, 'p1_title'=>'Transitive_properties'));
		$res2 = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_anomaliesbot', 'gi_type'=>SMW_GARDISSUE_CATEGORY_LEAF, 'p1_title'=>'Symmetrical_properties'));
		$res3 = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_anomaliesbot', 'gi_type'=>SMW_GARDISSUE_CATEGORY_LEAF, 'p1_title'=>'GardeningLog'));
		$this->assertNotEquals($res, false);
		$this->assertNotEquals($res2, false);
		$this->assertNotEquals($res3, false);
	}

	function testSubCategoryAnomaly() {
		$db = wfGetDB(DB_SLAVE);
		$res = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_anomaliesbot', 'gi_type'=>SMW_GARDISSUE_SUBCATEGORY_ANOMALY, 'p1_title'=>'Person'));
		$res2 = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_anomaliesbot', 'gi_type'=>SMW_GARDISSUE_SUBCATEGORY_ANOMALY, 'p1_title'=>'Man'));
		$res3 = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_anomaliesbot', 'gi_type'=>SMW_GARDISSUE_SUBCATEGORY_ANOMALY, 'p1_title'=>'Young_man'));
		$this->assertNotEquals($res, false);
		$this->assertNotEquals($res2, false);
		$this->assertNotEquals($res3, false);
	}

}
?>