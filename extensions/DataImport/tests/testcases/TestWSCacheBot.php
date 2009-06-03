<?php

require_once 'Util.php';
require_once 'DI_Utils.php';

class TestWSCacheBot extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = false;

	function setUp() {
		//create wwsds and confirm them
		$titles = array('TimeTestWS', 'TimeTestWSOnce', 'TimeTestWSExpires', 'TimeTestWSUnused');
		di_utils_setupWebServices($titles);
		
		//use the web services
		$titles = array('TimeTestWSOutdated', 'TimeTestWSUpToDate', 'TimeTestWSOnce', 'TimeTestWSUnconfirmed', 'TimeTestWSExpires');
		di_utils_setupWSUsages($titles);
		
		//setup testOutdatedResult()
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
		WSStorage::getDatabase()->updateCacheLastAccess($wsId, $paramSetId);
		
		//setup testUpToDateResult() 
		$wsId = di_utils_getWSId('TimeTestWS');
		$pId = di_utils_getPageId("TimeTestWSUpToDate");
		$paramSetId = di_utils_getParamSetId($wsId, $pId);$db =& wfGetDB( DB_SLAVE );
		WSStorage::getDatabase()->storeCacheEntry($wsId, $paramSetId, "phpunit2");
		
//		//setup testNoCleanupPolicyResult() 
		$wsId = di_utils_getWSId('TimeTestWSOnce');
		$pId = di_utils_getPageId("TimeTestWSOnce");
		$paramSetId = di_utils_getParamSetId($wsId, $pId);
		WSStorage::getDatabase()->storeCacheEntry($wsId, $paramSetId, "phpunit3");
		
		//setup testLastAccessResult()
		$wsId = di_utils_getWSId('TimeTestWSExpires');
		$pId = di_utils_getPageId("TimeTestWSExpires");
		$paramSetId = di_utils_getParamSetId($wsId, $pId);
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
		WSStorage::getDatabase()->updateCacheLastAccess($wsId, $paramSetId);
		
		//run ws update bot
		$cd = isWindows() ? "" : "./";
		exec($cd.'runBots smw_wscachebot -nolog');
	}

	function tearDown() {
		di_utils_truncateWSTables();
	}

	function testOutdatedResult() {
		$wsId = di_utils_getWSId('TimeTestWS');
		$pId = di_utils_getPageId("TimeTestWSOutdated");
		$paramSetId = di_utils_getParamSetId($wsId, $pId);
		
		$cacheResult = WSStorage::getDatabase()->getResultFromCache($wsId, $paramSetId);
		
		$this->assertEquals($cacheResult["result"], null);
	}
	
	function testUpToDateResult() {
		$wsId = di_utils_getWSId('TimeTestWS');
		$pId = di_utils_getPageId("TimeTestWSUpToDate");
		$paramSetId = di_utils_getParamSetId($wsId, $pId);
		
		$cacheResult = WSStorage::getDatabase()->getResultFromCache($wsId, $paramSetId);
		
		$this->assertEquals($cacheResult["result"], "phpunit2");
	}
	
	function testNoCleanupPolicyResult() {
		$wsId = di_utils_getWSId('TimeTestWSOnce');
		$pId = di_utils_getPageId("TimeTestWSOnce");
		$paramSetId = di_utils_getParamSetId($wsId, $pId);
		
		$cacheResult = WSStorage::getDatabase()->getResultFromCache($wsId, $paramSetId);
		
		$this->assertEquals($cacheResult["result"], "phpunit3");
	}
	
	function testLastAccessResult() {
		$wsId = di_utils_getWSId('TimeTestWSExpires');
		$pId = di_utils_getPageId("TimeTestWSExpires");
		$paramSetId = di_utils_getParamSetId($wsId, $pId);
		
		$cacheResult = WSStorage::getDatabase()->getResultFromCache($wsId, $paramSetId);
		
		$this->assertEquals($cacheResult["result"], "phpunit4");
	}
}
?>