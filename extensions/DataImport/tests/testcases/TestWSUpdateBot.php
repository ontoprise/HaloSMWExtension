<?php

require_once 'Util.php';

class TestWSUpdateBot extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = false;

	function setUp() {
		//create wwsds and confirm them
		$titles = array('TimeTestWS', 'TimeTestWSOnce', 'TimeTestWSUnconfirmed', 'TimeTestWSUnused');
		global $wgScriptPath;
		foreach($titles as $title){
			$text = smwf_om_GetWikiText('WebService:'.$title);
			$text = str_replace("http://localhost/MashupWiki", "http://localhost".$wgScriptPath, $text);
			smwf_om_EditArticle('WebService:'.$title, 'PHPUnit', $text, '');
			$ws = WebService::newFromName($title);
			WSStorage::getDatabase()->setWWSDConfirmationStatus($ws->getArticleID(), "true");
		}
		
		$titles = array('TimeTestWSOutdated', 'TimeTestWSUpToDate', 'TimeTestWSOnce', 'TimeTestWSUnconfirmed');
		foreach($titles as $title){
			$text = smwf_om_GetWikiText($title);
			smwf_om_EditArticle($title, 'PHPUnit', $text, '');
		}
		
		//set timestamp of TimeTestWSOutdated back
		$wsId = $this->getWSId('TimeTestWS');
		$pId = $this->getPageId("TimeTestWSOutdated");
		$paramSetId = $this->getParamSetId($wsId, $pId);
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
		
		//set cache value of TimeTestWSUpToDate to phpunit2 
		$wsId = $this->getWSId('TimeTestWS');
		$pId = $this->getPageId("TimeTestWSUpToDate");
		$paramSetId = $this->getParamSetId($wsId, $pId);$db =& wfGetDB( DB_SLAVE );
		WSStorage::getDatabase()->storeCacheEntry($wsId, $paramSetId, "phpunit2");
		
		//set cache value of TimeTestWSOnce to phpunit3 
		$wsId = $this->getWSId('TimeTestWSOnce');
		$pId = $this->getPageId("TimeTestWSOnce");
		$paramSetId = $this->getParamSetId($wsId, $pId);
		WSStorage::getDatabase()->storeCacheEntry($wsId, $paramSetId, "phpunit3");
		
//		//set cache value of TimeTestWSOnce to phpunit4
		$wsId = $this->getWSId('TimeTestWSUnconfirmed');
		$pId = $this->getPageId("TimeTestWSUnconfirmed");
		$paramSetId = $this->getParamSetId($wsId, $pId);
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
		
		//run ws update bot
		$cd = isWindows() ? "" : "./";
		exec($cd.'runBots smw_wsupdatebot -nolog');
	}

	function tearDown() {
		$db =& wfGetDB( DB_SLAVE );
		$tables = array('smw_ws_articles', 'smw_ws_cache' , 'smw_ws_parameters', 'smw_ws_wwsd');
		foreach($tables as $table){
			$tn = $db->tableName($table);
			$query = "TRUNCATE TABLE ".$tn;
			$db->query($query);
		}
	}

	function testOutdatedResult() {
		$wsId = $this->getWSId('TimeTestWS');
		$pId = $this->getPageId("TimeTestWSOutdated");
		$paramSetId = $this->getParamSetId($wsId, $pId);
		
		$cacheResult = WSStorage::getDatabase()->getResultFromCache($wsId, $paramSetId);
		
		$this->assertNotEquals($cacheResult["result"], "phpunit");
	}
	
	function testUpToDateResult() {
		$wsId = $this->getWSId('TimeTestWS');
		$pId = $this->getPageId("TimeTestWSUpToDate");
		$paramSetId = $this->getParamSetId($wsId, $pId);
		
		$cacheResult = WSStorage::getDatabase()->getResultFromCache($wsId, $paramSetId);
		
		$this->assertEquals($cacheResult["result"], "phpunit2");
	}
	
	function testPolicyOnceResult() {
		$wsId = $this->getWSId('TimeTestWSOnce');
		$pId = $this->getPageId("TimeTestWSOnce");
		$paramSetId = $this->getParamSetId($wsId, $pId);
		
		$cacheResult = WSStorage::getDatabase()->getResultFromCache($wsId, $paramSetId);
		
		$this->assertEquals($cacheResult["result"], "phpunit3");
	}
	
	function testUnconfirmedWS() {
		$wsId = $this->getWSId('TimeTestWSUnconfirmed');
		$pId = $this->getPageId("TimeTestWSUnconfirmed");
		$paramSetId = $this->getParamSetId($wsId, $pId);
		
		$cacheResult = WSStorage::getDatabase()->getResultFromCache($wsId, $paramSetId);
		
		$this->assertEquals($cacheResult["result"], "phpunit4");
	}
//
	private function getParamSetId($wsId, $pId){
		$db =& wfGetDB( DB_SLAVE );
		$tn = $db->tableName("smw_ws_articles");
		$query = 'SELECT param_set_id FROM '.$tn.' WHERE web_service_id='.$wsId.' AND page_id='.$pId;
		$result = $db->query($query);
		$result = $db->fetchObject($result);
		return $result->param_set_id;
	}
	
	private function getPageId($pageName){
		$db =& wfGetDB( DB_SLAVE ); 
		$tn = $db->tableName("page");
		$query = 'SELECT page_id FROM '.$tn.' WHERE page_title="'.$pageName.'" AND page_namespace=0';
		$result = $db->query($query);
		$result = $db->fetchObject($result);
		return $result->page_id;
	}
	
	private function getWSId($wsName){
		$ws = WebService::newFromName($wsName);
		return $ws->getArticleID();
	}



}
?>