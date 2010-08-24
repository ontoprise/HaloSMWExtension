<?php

require_once 'Util.php';
require_once 'DI_Utils.php';

/*
 * test wsupdatebot
 */
class TestWSUpdateBot extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = false;

	function setUp() {
		//create wwsds and confirm them
		$titles = array('TimeTestWS', 'TimeTestWSOnce', 
			'TimeTestWSUnconfirmed', 'TimeTestWSUnused', 'TimeTestWSExpires');
		di_utils_setupWebServices($titles);
		
		//use the web services
		$titles = array('TimeTestWSOutdated', 'TimeTestWSUpToDate', 
			'TimeTestWSOnce', 'TimeTestWSUnconfirmed', 'TimeTestWSExpires');
		di_utils_setupWSUsages($titles);
		
		//swtup testOutdatedResult
		$wsId = di_utils_getWSId('TimeTestWS');
		$pId = di_utils_getPageId("TimeTestWSOutdated");
		$paramSetId = di_utils_getParamSetId($wsId, $pId);
		$db =& wfGetDB( DB_SLAVE );
		$timeStamp = wfTimeStamp(TS_UNIX, $db->timestamp()) - 1000000;
		$db->delete($db->tableName('smw_ws_cache'), array(
					  'web_service_id'    => $wsId,
					  'param_set_id' => $paramSetId));
		$db->insert($db->tableName('smw_ws_cache'), array(
					  'web_service_id'    => $wsId,
					  'param_set_id' => $paramSetId,
					  'result'   => "phpunit",
						'last_update' => wfTimeStamp(TS_MW, $timeStamp),
						'last_access' => wfTimeStamp(TS_MW, $timeStamp)));
		
		//setup testUpToDateResult 
		$wsId = di_utils_getWSId('TimeTestWS');
		$pId = di_utils_getPageId("TimeTestWSUpToDate");
		$paramSetId = di_utils_getParamSetId($wsId, $pId);$db =& wfGetDB( DB_SLAVE );
		WSStorage::getDatabase()->storeCacheEntry($wsId, $paramSetId, "phpunit2");
		
		//setup testPolicyOnceResult 
		$wsId = di_utils_getWSId('TimeTestWSOnce');
		$pId = di_utils_getPageId("TimeTestWSOnce");
		$paramSetId = di_utils_getParamSetId($wsId, $pId);
		WSStorage::getDatabase()->storeCacheEntry($wsId, $paramSetId, "phpunit3");
		
		//setup testUnconfirmedWS
		$wsId = di_utils_getWSId('TimeTestWSUnconfirmed');
		$pId = di_utils_getPageId("TimeTestWSUnconfirmed");
		$paramSetId = di_utils_getParamSetId($wsId, $pId);
		WSStorage::getDatabase()->setWWSDConfirmationStatus($wsId, "once");
		$db =& wfGetDB( DB_SLAVE );
		$timeStamp = wfTimeStamp(TS_UNIX, $db->timestamp()) - 1000000;
		$db->delete($db->tableName('smw_ws_cache'), array(
					  'web_service_id'    => $wsId,
					  'param_set_id' => $paramSetId));
		$db->insert($db->tableName('smw_ws_cache'), array(
					  'web_service_id'    => $wsId,
					  'param_set_id' => $paramSetId,
					  'result'   => "phpunit4",
						'last_update' => wfTimeStamp(TS_MW, $timeStamp),
						'last_access' => wfTimeStamp(TS_MW, $timeStamp)));
		
		//setup testRemovedFromCacheResult 
		$wsId = di_utils_getWSId('TimeTestWSExpires');
		$pId = di_utils_getPageId("TimeTestWSExpires");
		$paramSetId = di_utils_getParamSetId($wsId, $pId);
		WSStorage::getDatabase()->removeWSEntryFromCache($wsId, $patamSetId);
		
		//run ws update bot
		$cd = isWindows() ? "" : "./";
		exec($cd.'runBots smw_wsupdatebot -nolog');
		sleep(10);
	}

	function tearDown() {
		//di_utils_truncateWSTables();
	}

	/*
	 * test if an outdated ws result does get updated
	 */
	function testOutdatedResult() {
		$wsId = di_utils_getWSId('TimeTestWS');
		$pId = di_utils_getPageId("TimeTestWSOutdated");
		$paramSetId = di_utils_getParamSetId($wsId, $pId);
		
		$cacheResult = WSStorage::getDatabase()->getResultFromCache($wsId, $paramSetId);
		
		$this->assertNotEquals($cacheResult["result"], "phpunit");
	}
	
	/*
	 * test if an uptodate result does not get updated
	 */
	function testUpToDateResult() {
		$wsId = di_utils_getWSId('TimeTestWS');
		$pId = di_utils_getPageId("TimeTestWSUpToDate");
		$paramSetId = di_utils_getParamSetId($wsId, $pId);
		
		$cacheResult = WSStorage::getDatabase()->getResultFromCache($wsId, $paramSetId);
		
		$this->assertEquals($cacheResult["result"], "phpunit2");
	}
	
	/*
	 * test if a ws-usage with update policy once does not get updated
	 */
	function testPolicyOnceResult() {
		$wsId = di_utils_getWSId('TimeTestWSOnce');
		$pId = di_utils_getPageId("TimeTestWSOnce");
		$paramSetId = di_utils_getParamSetId($wsId, $pId);
		
		$cacheResult = WSStorage::getDatabase()->getResultFromCache($wsId, $paramSetId);
		
		$this->assertEquals($cacheResult["result"], "phpunit3");
	}
	
	/*
	 * Test if a ws-usage of an unconfirmed ws does not get updated
	 */
	function testUnconfirmedWS() {
		$wsId = di_utils_getWSId('TimeTestWSUnconfirmed');
		$pId = di_utils_getPageId("TimeTestWSUnconfirmed");
		$paramSetId = di_utils_getParamSetId($wsId, $pId);
		
		$cacheResult = WSStorage::getDatabase()->getResultFromCache($wsId, $paramSetId);
		
		$this->assertEquals($cacheResult["result"], "phpunit4");
	}

	/*
	 * Test if a ws usage without a cached value gets updated
	 */
function testRemovedFromCacheResult() {
		$wsId = di_utils_getWSId('TimeTestWSExpires');
		$pId = di_utils_getPageId("TimeTestWSExpires");
		$paramSetId = di_utils_getParamSetId($wsId, $pId);
		
		$cacheResult = WSStorage::getDatabase()->getResultFromCache($wsId, $paramSetId);
		
		$this->assertNotEquals($cacheResult["result"], null);
	}
}
?>