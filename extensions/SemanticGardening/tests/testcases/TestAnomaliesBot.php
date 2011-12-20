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
require_once  $sgagIP . '/includes/bots/SGA_AnomaliesBot.php';
require_once 'Util.php';
class TestAnomaliesBot extends PHPUnit_Framework_TestCase {
	 

	function setUp() {
		$cd = isWindows() ? "" : "sh ";
		exec($cd.'runBots smw_anomaliesbot -p "CATEGORY_NUMBER_ANOMALY=Check%20number%20of%20sub%20categories,CATEGORY_LEAF_ANOMALY=Check%20for%20category%20leafs,CATEGORY_RESTRICTION="');
	}

	function tearDown() {
		 
	}

	function testCategoryLeaf() {
		$db = wfGetDB(DB_SLAVE);
		$res = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_anomaliesbot', 'gi_type'=>SMW_GARDISSUE_CATEGORY_LEAF, 'p1_title'=>'Transitive_properties_Duplicate'));
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
