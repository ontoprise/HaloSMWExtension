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
		
		sleep(10);
	}

	function tearDown() {
		di_utils_truncateWSTables();
	}

	function testOutdatedResult() {
		$wsId = di_utils_getWSId('TimeTestWS');
		$pId = di_utils_getPageId("TimeTestWSOutdated");
		$paramSetId = di_utils_getParamSetId($wsId, $pId);
		
		$cacheResult = WSStorage::getDatabase()->getResultFromCache($wsId, $paramSetId);
		
		$t = null;
		if(array_key_exists("result", $cacheResult)){
			$t = $cacheResult["result"];
		}
		$this->assertEquals($t, null);
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
