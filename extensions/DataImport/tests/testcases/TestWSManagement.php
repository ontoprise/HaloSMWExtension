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


require_once 'DI_Utils.php';

/*
 * test creation, editing of web services and so on
 */
class TestWSManagement extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = false;

	function tearDown() {
		di_utils_truncateWSTables();
	}

	/*
	 * test web service creation
	 */
	function testCreateWS() {
		global $wgScriptPath;
		di_utils_setupWebServices(array("TimeTestWSCreate"), false);
		$wsId = di_utils_getWSId("TimeTestWSCreate");

		$this->assertNotEquals($wsId, null);

		$ws = WebService::newFromID($wsId);
		$this->assertEquals($ws->getURI(), "http://localhost".$wgScriptPath."/extensions/DataImport/tests/DI_TimeTestWS.php");
		$this->assertEquals($ws->getProtocol(), "REST");
		$this->assertEquals($ws->getMethod(), "get");
		$this->assertEquals($ws->getAuthenticationLogin(), "userlogin");
		$this->assertEquals($ws->getAuthenticationPassword(), "password");
		$this->assertEquals($ws->getAuthenticationType(), "http");
		$this->assertNotEquals($ws->getParameters(), null);
		$this->assertNotEquals($ws->getResult(), null);
		$this->assertEquals($ws->getDisplayPolicy(), "10");
		$this->assertEquals($ws->getQueryPolicy(), "20");
		$this->assertEquals($ws->getSpanOfLife(), "1");
		$this->assertEquals($ws->doesExpireAfterUpdate(), "true");
		$this->assertEquals($ws->getConfirmationStatus(), "false");
	}

	/*
	 * test editing a web service -> the cache must be cleaned
	 * but the data about pages that use the ws must be kept 
	 */
	function testEditWS() {
		global $wgScriptPath;
		di_utils_setupWebServices(array("TimeTestWSEdit"));
		$wsId = di_utils_getWSId("TimeTestWSEdit");
		di_utils_setupWSUsages(array("TimeTestWSEdit"));

		$this->assertNotEquals($wsId, null);
		$this->assertEquals(count(WSStorage::getDatabase()->getWSUsages($wsId)), 1);
		$this->assertEquals(count(WSStorage::getDatabase()->getResultsFromCache($wsId)), 1);
		
		$text = smwf_om_GetWikiText('WebService:TimeTestWSEdit');
		$text = str_replace("http://localhost".$wgScriptPath
			, "http://localhost/mediawiki2", $text);
		smwf_om_EditArticle('WebService:TimeTestWSEdit', 'PHPUnit', $text, '');
		
		$wsId = di_utils_getWSId("TimeTestWSEdit");
		$this->assertNotEquals($wsId, null);
		
		$this->assertEquals(count(WSStorage::getDatabase()->getWSUsages($wsId)), 1);
		$this->assertEquals(count(WSStorage::getDatabase()->getResultsFromCache($wsId)), 0);
	}


	/*
	 * Test missing <webservice> tag
	 */
	function testCreateWSFailureMissingWSTag(){
		di_utils_setupWebServices(array("TimeTestWSCreateFailure1"), false);
		
		$wsId = di_utils_getWSId("TimeTestWSCreateFailure1");

		$this->assertEquals($wsId, null);
	}

	/*
	 * Test soap ws with wrong uri
	 */
	//this test does not work due to a strange error
	function testCreateWSFailureWrongURI(){
		di_utils_setupWebServices(array("TimeTestWSCreateFailure2"), false);
		
		$wsId = di_utils_getWSId("TimeTestWSCreateFailure2");
	
		$this->assertEquals($wsId, null);
	}

	/*
	 * Test soap ws with missing uri
	 */
	function testCreateWSFailureMissingURITag(){
		di_utils_setupWebServices(array("TimeTestWSCreateFailure3"), false);
		$wsId = di_utils_getWSId("TimeTestWSCreateFailure3");

		$this->assertEquals($wsId, null);
	}

	/*
	 * Test soap ws with missing method
	 */
	function testCreateWSFailureMissingMethod(){
		di_utils_setupWebServices(array("TimeTestWSCreateFailure4"), false);
		$wsId = di_utils_getWSId("TimeTestWSCreateFailure4");

		$this->assertEquals($wsId, null);
	}

	/*
	 * Test soap rest ws with missing uri
	 */
	function testCreateWSRESTWithMissingURI(){
		di_utils_setupWebServices(array("TimeTestWSCreateRESTMissingURI"), false);
		$wsId = di_utils_getWSId("TimeTestWSCreateRESTMissingURI");

		$this->assertNotEquals($wsId, null);
	}

	/*
	 * Test deleting a wwsd
	 */
	function testDeleteWS() {
		global $wgUser;
		$wgUser->addGroup("sysop");

		di_utils_setupWebServices(array("TimeTestWSDelete"));
		$text = smwf_om_GetWikiText("WebService:TimeTestWSDelete");
		$wsId = di_utils_getWSId("TimeTestWSDelete");
		di_utils_setupWSUsages(array("TimeTestWSDelete"));

		$this->assertEquals(count(WSStorage::getDatabase()->getWSUsages($wsId)), 1);
		$this->assertEquals(count(WSStorage::getDatabase()->getResultsFromCache($wsId)), 1);

		try {
			smwf_om_DeleteArticle("WebService:TimeTestWSDelete", "phpunit", "phpunit");
		} catch (exception $e){
		}

		$ws = WebService::newFromID($wsId);
		
		$this->assertEquals($ws, null);
		$this->assertEquals(count(WSStorage::getDatabase()->getWSUsages($wsId)), 0);
		$this->assertEquals(count(WSStorage::getDatabase()->getResultsFromCache($wsId)), 0);

		$this->tearDownTestDeleteWS($text);
	}

	/*
	 * test move a wwsd article
	 * Note: Moving an rticle means that the page with the old name
	 * gets a new page_id and the page with the new name gets the old page_id.
	 * Therefore no updates to the web service tables are necessary.
	 */
	function testMoveWS(){
		global $wgUser;
		$wgUser->addGroup("sysop");

		di_utils_setupWebServices(array("TimeTestWSMove"));
		$text = smwf_om_GetWikiText("WebService:TimeTestWSMove");
		$wsId = di_utils_getWSId("TimeTestWSMove");
		di_utils_setupWSUsages(array("TimeTestWSMove"));

		$this->assertEquals(count(WSStorage::getDatabase()->getWSUsages($wsId)), 1);
		$this->assertEquals(count(WSStorage::getDatabase()->getResultsFromCache($wsId)), 1);

		smwf_om_RenameArticle("WebService:TimeTestWSMove", "WebService:TimeTestWSMove2", "phpunit", "WikiSysop");

		$ws = WebService::newFromName("TimeTestWSMove2");
		$this->assertNotEquals($ws, null);

		$wsId2 = $ws->getArticleID();
		$this->assertEquals($wsId, $wsId2);

		$this->assertEquals(count(WSStorage::getDatabase()->getWSUsages($wsId2)), 1);
		$this->assertEquals(count(WSStorage::getDatabase()->getResultsFromCache($wsId2)), 1);

		$this->tearDownTestMoveWS($text);
	}

	private function tearDownTestDeleteWS($text){
		//smwf_om_EditArticle('WebService:TimeTestWSDelete', 'PHPUnit', $text, 'comment');
	}

	private function tearDownTestMoveWS($text){
		//smwf_om_DeleteArticle("WebService:TimeTestWSMove", "phpunit", "phpunit");
		//smwf_om_DeleteArticle("WebService:TimeTestWSMove2", "phpunit", "phpunit");
		//smwf_om_EditArticle('WebService:TimeTestWSMove', 'PHPUnit', $text, '');
	}
}
?>
