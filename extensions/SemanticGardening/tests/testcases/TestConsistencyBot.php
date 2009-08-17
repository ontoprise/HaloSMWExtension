<?php
require_once '../includes/bots/consistency_bot/SGA_ConsistencyBot.php';
require_once 'Util.php';
class TestConsistencyBot extends PHPUnit_Framework_TestCase {


	function setUp() {
		$cd = isWindows() ? "" : "./"; 
		exec($cd.'runBots smw_consistencybot');
	}

	function tearDown() {
			
	}

	function testCheckCategoryCycle() {
		$db = wfGetDB(DB_SLAVE);
		$res = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_consistencybot', 'gi_type'=>SMW_GARD_ISSUE_CYCLE, 'p1_title'=>'Person'));
		$this->assertNotEquals($res, false);
	}

	function testCheckPropertyCycle() {
		$db = wfGetDB(DB_SLAVE);
		$res = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_consistencybot', 'gi_type'=>SMW_GARD_ISSUE_CYCLE, 'p1_title'=>'Has_Ancestor'));
		$this->assertNotEquals($res, false);
	}

	function testDomainsNotCovariant() {
		$db = wfGetDB(DB_SLAVE);
		$res = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_consistencybot', 'gi_type'=>SMW_GARDISSUE_DOMAINS_NOT_COVARIANT, 'p1_title'=>'Has_Electric_engine'));
		$this->assertNotEquals($res, false);
	}

	function testRangeNotCovariant() {
		$db = wfGetDB(DB_SLAVE);
		$res = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_consistencybot', 'gi_type'=>SMW_GARDISSUE_RANGES_NOT_COVARIANT, 'p1_title'=>'Has_Electric_engine'));
		$this->assertNotEquals($res, false);
	}

	function testTypesNotCovariant() {
		$db = wfGetDB(DB_SLAVE);
		$res = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_consistencybot', 'gi_type'=>SMW_GARDISSUE_TYPES_NOT_COVARIANT, 'p1_title'=>'Has_Daughter'));
		$this->assertNotEquals($res, false);
	}

	function testMinCardNotCovariant() {
		$db = wfGetDB(DB_SLAVE);
		$res = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_consistencybot', 'gi_type'=>SMW_GARDISSUE_MINCARD_NOT_COVARIANT, 'p1_title'=>'Has_Son'));
		$res2 = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_consistencybot', 'gi_type'=>SMW_GARDISSUE_MINCARD_NOT_COVARIANT, 'p1_title'=>'Has_Electric_engine'));
		$this->assertNotEquals($res, false);
		$this->assertNotEquals($res2, false);
	}

	function testMaxCardNotCovariant() {
		$db = wfGetDB(DB_SLAVE);
		$res = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_consistencybot', 'gi_type'=>SMW_GARDISSUE_MAXCARD_NOT_COVARIANT, 'p1_title'=>'Has_Electric_engine'));
		$res2 = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_consistencybot', 'gi_type'=>SMW_GARDISSUE_MAXCARD_NOT_COVARIANT, 'p1_title'=>'Has_Son'));
		$this->assertNotEquals($res, false);
		$this->assertNotEquals($res2, false);
	}

	function testDomainIsNotDefined() {
		$db = wfGetDB(DB_SLAVE);
		$res = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_consistencybot', 'gi_type'=>SMW_GARDISSUE_DOMAINS_NOT_DEFINED, 'p1_title'=>'Question'));
		$res2 = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_consistencybot', 'gi_type'=>SMW_GARDISSUE_DOMAINS_NOT_DEFINED, 'p1_title'=>'Description'));
		$res3 = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_consistencybot', 'gi_type'=>SMW_GARDISSUE_DOMAINS_NOT_DEFINED, 'p1_title'=>'Glossary'));
		$res4 = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_consistencybot', 'gi_type'=>SMW_GARDISSUE_DOMAINS_NOT_DEFINED, 'p1_title'=>'Has_adress'));
		$res5 = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_consistencybot', 'gi_type'=>SMW_GARDISSUE_DOMAINS_NOT_DEFINED, 'p1_title'=>'DiscourseState'));
		$res6 = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_consistencybot', 'gi_type'=>SMW_GARDISSUE_DOMAINS_NOT_DEFINED, 'p1_title'=>'Has_Daughter'));
		$res7 = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_consistencybot', 'gi_type'=>SMW_GARDISSUE_DOMAINS_NOT_DEFINED, 'p1_title'=>'Has_name'));
		$this->assertNotEquals($res, false);
		$this->assertNotEquals($res2, false);
		$this->assertNotEquals($res3, false);
		$this->assertNotEquals($res4, false);
		$this->assertNotEquals($res5, false);
		$this->assertNotEquals($res6, false);
		$this->assertNotEquals($res7, false);
	}

	function testDomainAndRangeAreNotDefined() {
		$db = wfGetDB(DB_SLAVE);

		$res = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_consistencybot', 'gi_type'=>SMW_GARDISSUE_DOMAINS_AND_RANGES_NOT_DEFINED, 'p1_title'=>'Has_Ancestor'));
		$res2 = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_consistencybot', 'gi_type'=>SMW_GARDISSUE_DOMAINS_AND_RANGES_NOT_DEFINED, 'p1_title'=>'Is_equal_to'));
		$res3 = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_consistencybot', 'gi_type'=>SMW_GARDISSUE_DOMAINS_AND_RANGES_NOT_DEFINED, 'p1_title'=>'Has_Gears'));
		$res4 = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_consistencybot', 'gi_type'=>SMW_GARDISSUE_DOMAINS_AND_RANGES_NOT_DEFINED, 'p1_title'=>'Has_Father'));
		$res5 = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_consistencybot', 'gi_type'=>SMW_GARDISSUE_DOMAINS_AND_RANGES_NOT_DEFINED, 'p1_title'=>'Has_Son'));
		$this->assertNotEquals($res, false);
		$this->assertNotEquals($res2, false);
		$this->assertNotEquals($res3, false);
		$this->assertNotEquals($res4, false);
		$this->assertNotEquals($res5, false);
	}

	function testRangeNotDefined() {
		$db = wfGetDB(DB_SLAVE);
		$res = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_consistencybot', 'gi_type'=>SMW_GARDISSUE_RANGES_NOT_DEFINED, 'p1_title'=>'Body_Form'));
		$this->assertNotEquals($res, false);
	}

	function testTypeNotDefined() {
		$db = wfGetDB(DB_SLAVE);
		$res = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_consistencybot', 'gi_type'=>SMW_GARDISSUE_TYPES_NOT_DEFINED, 'p1_title'=>'Is_equal_to'));
		$res2 = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_consistencybot', 'gi_type'=>SMW_GARDISSUE_TYPES_NOT_DEFINED, 'p1_title'=>'Has_Gears'));
		$res3 = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_consistencybot', 'gi_type'=>SMW_GARDISSUE_TYPES_NOT_DEFINED, 'p1_title'=>'Has_Son'));
		$this->assertNotEquals($res, false);
		$this->assertNotEquals($res2, false);
		$this->assertNotEquals($res3, false);
	}

	function testDoubleType() {
		$db = wfGetDB(DB_SLAVE);
		$res = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_consistencybot', 'gi_type'=>SMW_GARDISSUE_DOUBLE_TYPE, 'p1_title'=>'Has_name'));
		$this->assertNotEquals($res, false);
	}

	function testDoubleMaxCard() {
		$db = wfGetDB(DB_SLAVE);
		$res = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_consistencybot', 'gi_type'=>SMW_GARDISSUE_DOUBLE_MAX_CARD, 'p1_title'=>'Has_name'));
		$this->assertNotEquals($res, false);
	}

	function testDoubleMinCard() {
		$db = wfGetDB(DB_SLAVE);
		$res = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_consistencybot', 'gi_type'=>SMW_GARDISSUE_DOUBLE_MIN_CARD, 'p1_title'=>'Has_name'));
		$this->assertNotEquals($res, false);
	}
	function testMaxCardNotNull() {
		$db = wfGetDB(DB_SLAVE);
		$res = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_consistencybot', 'gi_type'=>SMW_GARDISSUE_MAXCARD_NOT_NULL, 'p1_title'=>'Has_Daughter'));
		$this->assertNotEquals($res, false);
	}
	function testMinCardBelowNull() {
		$db = wfGetDB(DB_SLAVE);
		$res = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_consistencybot', 'gi_type'=>SMW_GARDISSUE_MINCARD_BELOW_NULL, 'p1_title'=>'Has_Daughter'));
		$this->assertNotEquals($res, false);
	}
	function testWrongMinCardValue() {
		$db = wfGetDB(DB_SLAVE);
		$res = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_consistencybot', 'gi_type'=>SMW_GARDISSUE_WRONG_MINCARD_VALUE, 'p1_title'=>'Has_Daughter'));
		$this->assertNotEquals($res, false);
	}
	function testWrongMaxCardValue() {
		// TODO: add test data
		$this->assertEquals(true, true);
	}
	function testWrongTargetValue() {
		$db = wfGetDB(DB_SLAVE);
		$res = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_consistencybot', 'gi_type'=>SMW_GARDISSUE_WRONG_TARGET_VALUE, 'p1_title'=>'Peugeot', 'p2_title'=>'Has_Engine'));
		$this->assertNotEquals($res, false);
	}
	function testWrongDomainValue() {
		$db = wfGetDB(DB_SLAVE);
		$res = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_consistencybot', 'gi_type'=>SMW_GARDISSUE_WRONG_DOMAIN_VALUE, 'p1_title'=>'Kai', 'p2_title'=>'Has_Engine'));
		$this->assertNotEquals($res, false);
	}

	function testTooLowCard() {
		//TODO: add test data
		$this->assertEquals(true, true);
	}
	function testTooHighCard() {
		$db = wfGetDB(DB_SLAVE);
		$res = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_consistencybot', 'gi_type'=>SMW_GARDISSUE_TOO_HIGH_CARD, 'p1_title'=>'Audi_TT', 'p2_title'=>'Has_Engine'));
		$this->assertNotEquals($res, false);
	}

	function testWrongUnit() {
		$db = wfGetDB(DB_SLAVE);
		$res = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_consistencybot', 'gi_type'=>SMW_GARDISSUE_WRONG_UNIT, 'p1_title'=>'5_cylinder'));
		$this->assertNotEquals($res, false);
	}
	function testMissingParam() {
		$db = wfGetDB(DB_SLAVE);
		$res = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_consistencybot', 'gi_type'=>SMW_GARD_ISSUE_MISSING_PARAM, 'p1_title'=>'Kai', 'p2_title'=>'Has_adress', 'valueint'=>0));
		$res2 = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_consistencybot', 'gi_type'=>SMW_GARD_ISSUE_MISSING_PARAM, 'p1_title'=>'Kai', 'p2_title'=>'Has_adress', 'valueint'=>1));
		$this->assertNotEquals($res, false);
		$this->assertNotEquals($res2, false);
	}
	function testMissingAnnotation() {
		$db = wfGetDB(DB_SLAVE);
		$res = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_consistencybot', 'gi_type'=>SMW_GARDISSUE_MISSING_ANNOTATIONS, 'p1_title'=>'Kai', 'p2_title'=>'Has_Child'));
		$res2 = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_consistencybot', 'gi_type'=>SMW_GARDISSUE_MISSING_ANNOTATIONS, 'p1_title'=>'Kai', 'p2_title'=>'Has_Electric_engine'));
		$this->assertNotEquals($res, false);
		$this->assertNotEquals($res2, false);
	}
	function testDomainNotRange() {
		$db = wfGetDB(DB_SLAVE);
		$res = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_consistencybot', 'gi_type'=>SMW_GARD_ISSUE_DOMAIN_NOT_RANGE, 'p1_title'=>'Has_Child', 'p2_title'=>'Is_parent_of'));
		$res2 = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_consistencybot', 'gi_type'=>SMW_GARD_ISSUE_DOMAIN_NOT_RANGE, 'p1_title'=>'Is_parent_of', 'p2_title'=>'Has_Child'));
		$this->assertNotEquals($res, false);
		$this->assertNotEquals($res2, false);
	}
	function testIncompatibleEntity() {
		$db = wfGetDB(DB_SLAVE);
		$res = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_consistencybot', 'gi_type'=>SMW_GARD_ISSUE_INCOMPATIBLE_ENTITY, 'p1_title'=>'Is_parent_of', 'p2_title'=>'Parent_of'));
		$this->assertNotEquals($res, false);
	}

	function testPropagation() {
		$db = wfGetDB(DB_SLAVE);
		$res = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_consistencybot', 'gi_type'=>SMW_GARDISSUE_CONSISTENCY_PROPAGATION, 'p1_title'=>'Young_man'));
		$res2 = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_consistencybot', 'gi_type'=>SMW_GARDISSUE_CONSISTENCY_PROPAGATION, 'p1_title'=>'Person'));
		$res3 = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_consistencybot', 'gi_type'=>SMW_GARDISSUE_CONSISTENCY_PROPAGATION, 'p1_title'=>'Electric_car'));
		$res4 = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_consistencybot', 'gi_type'=>SMW_GARDISSUE_CONSISTENCY_PROPAGATION, 'p1_title'=>'Sports_car'));
		$res5 = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_consistencybot', 'gi_type'=>SMW_GARDISSUE_CONSISTENCY_PROPAGATION, 'p1_title'=>'Engine'));
		$res6 = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_consistencybot', 'gi_type'=>SMW_GARDISSUE_CONSISTENCY_PROPAGATION, 'p1_title'=>'Car'));
		$res7 = $db->selectRow($db->tableName('smw_gardeningissues'), "*", array('bot_id'=>'smw_consistencybot', 'gi_type'=>SMW_GARDISSUE_CONSISTENCY_PROPAGATION, 'p1_title'=>'Man'));
		$this->assertNotEquals($res, false);
		$this->assertNotEquals($res2, false);
		$this->assertNotEquals($res3, false);
		$this->assertNotEquals($res4, false);
		$this->assertNotEquals($res5, false);
		$this->assertNotEquals($res6, false);
		$this->assertNotEquals($res7, false);
	}

}
