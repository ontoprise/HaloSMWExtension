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

global $IP;
require_once($IP."/extensions/DataImport/specials/WebServices/SMW_WebServiceUsage.php");
/*
 * test using web services in articles
 */
class TestWSUsage extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = false;

	function tearDown() {
		di_utils_truncateWSTables();
	}

	/*
	 * adding of web service usages and test if the database is updated correctly
	 * (number of ws usages, number of cache values, number of parameter sets)
	 * 		1. test with one ws usage
	 * 		2.add ws usage with same ws and same parameter
	 * 		3. add ws usage with same ws but different parameter
	 * 		4. add ws usage with other ws and same parameter
	 * 		5. add ws call that does not use a parameter
	 */
	function testAddWSUsages() {
		global $wgScriptPath;
		di_utils_setupWebServices(array("TimeTestWSUse"));
		$wsId = di_utils_getWSId("TimeTestWSUse");
		$pId = di_utils_getPageId("TimeTestWSUse");
		di_utils_setupWSUsages(array("TimeTestWSUse"));

		$this->assertNotEquals($wsId, null);
		$this->assertEquals(count(WSStorage::getDatabase()->getWSUsages($wsId)), 1);
		$this->assertEquals(count(WSStorage::getDatabase()->getResultsFromCache($wsId)), 1);
		$this->assertEquals(count(WSStorage::getDatabase()->getWSsUsedInArticle($pId)), 1);
		$this->assertEquals($this->getParameterSetIdCount(), 1);

		//add a second ws call
		$text = smwf_om_GetWikiText('TimeTestWSUse');
		$text .= " {{#ws:TimeTestWSUse"
		."| dontCare=something"
		."| ?result.complete"
		."}}";
		smwf_om_EditArticle('TimeTestWSUse', 'PHPUnit', $text, '');

		$this->assertEquals(count(WSStorage::getDatabase()->getWSUsages($wsId)), 1);
		$this->assertEquals(count(WSStorage::getDatabase()->getResultsFromCache($wsId)), 1);
		$this->assertEquals(count(WSStorage::getDatabase()->getWSsUsedInArticle($pId)), 1);
		$this->assertEquals($this->getParameterSetIdCount(), 1);

		//add a third ws call that uses another parameter value
		$text = smwf_om_GetWikiText('TimeTestWSUse');
		$text .= " {{#ws:TimeTestWSUse"
		."| dontCare=something else"
		."| ?result.complete"
		."}}";
		smwf_om_EditArticle('TimeTestWSUse', 'PHPUnit', $text, '');

		$this->assertEquals(count(WSStorage::getDatabase()->getWSUsages($wsId)), 2);
		$this->assertEquals(count(WSStorage::getDatabase()->getResultsFromCache($wsId)), 2);
		$this->assertEquals(count(WSStorage::getDatabase()->getWSsUsedInArticle($pId)), 2);
		$this->assertEquals($this->getParameterSetIdCount(), 2);

		// add a forth web service that uses the same parameter but different ws
		di_utils_setupWebServices(array("TimeTestWSUse2"));
		$wsId2 = di_utils_getWSId("TimeTestWSUse2");

		$text = smwf_om_GetWikiText('TimeTestWSUse');
		$text .= " {{#ws:TimeTestWSUse2"
		."| dontCare=something else"
		."| ?result.complete"
		."}}";
		smwf_om_EditArticle('TimeTestWSUse', 'PHPUnit', $text, '');

		$this->assertEquals(count(WSStorage::getDatabase()->getWSUsages($wsId)), 2);
		$this->assertEquals(count(WSStorage::getDatabase()->getResultsFromCache($wsId)), 2);
		$this->assertEquals(count(WSStorage::getDatabase()->getWSUsages($wsId2)), 1);
		$this->assertEquals(count(WSStorage::getDatabase()->getResultsFromCache($wsId2)), 1);
		$this->assertEquals(count(WSStorage::getDatabase()->getWSsUsedInArticle($pId)), 3);
		$this->assertEquals($this->getParameterSetIdCount(), 2);


		// add a fiftg web service that does not use a parameter
		$text = smwf_om_GetWikiText('TimeTestWSUse');
		$text .= " {{#ws:TimeTestWSUse2"
		."| ?result.complete"
		."}}";
		smwf_om_EditArticle('TimeTestWSUse', 'PHPUnit', $text, '');

		$this->assertEquals(count(WSStorage::getDatabase()->getWSUsages($wsId)), 2);
		$this->assertEquals(count(WSStorage::getDatabase()->getResultsFromCache($wsId)), 2);
		$this->assertEquals(count(WSStorage::getDatabase()->getWSUsages($wsId2)), 2);
		$this->assertEquals(count(WSStorage::getDatabase()->getResultsFromCache($wsId2)), 2);
		$this->assertEquals(count(WSStorage::getDatabase()->getWSsUsedInArticle($pId)), 4);
		$this->assertEquals($this->getParameterSetIdCount(), 2);
	}


	/*
	 * adding of web service usages and test if the database is updated correctly
	 * (number of ws usages, number of cache values, number of parameter sets)
	 * 		1. test with one ws usage
	 * 		2.add ws usage with same ws and same parameter
	 * 		3. add ws usage with same ws but different parameter
	 * 		4. add ws usage with other ws and same parameter
	 * 		5. add ws call that does not use a parameter
	 */
	function testRemoveWSUsages() {
		global $wgScriptPath;
		di_utils_setupWebServices(array("TimeTestWSUse", "TimeTestWSUse2"));
		$wsId = di_utils_getWSId("TimeTestWSUse");
		$wsId2 = di_utils_getWSId("TimeTestWSUse2");
		$pId = di_utils_getPageId("TimeTestWSUse");
		di_utils_setupWSUsages(array("TimeTestWSUse"));

		$this->assertNotEquals($wsId, null);
		$this->assertNotEquals($wsId2, null);
		$this->assertEquals(count(WSStorage::getDatabase()->getWSUsages($wsId)), 2);
		$this->assertEquals(count(WSStorage::getDatabase()->getResultsFromCache($wsId)), 2);
		$this->assertEquals(count(WSStorage::getDatabase()->getWSUsages($wsId2)), 2);
		$this->assertEquals(count(WSStorage::getDatabase()->getResultsFromCache($wsId2)), 2);
		$this->assertEquals(count(WSStorage::getDatabase()->getWSsUsedInArticle($pId)), 4);
		$this->assertEquals($this->getParameterSetIdCount(), 2);

		//remove first ws call which exists two times
		$text = smwf_om_GetWikiText('TimeTestWSUse');
		$text = str_replace(" {{#ws:TimeTestWSUse"
		."| dontCare=something"
		."| ?result.complete"
		."}}", "", $text);
		smwf_om_EditArticle('TimeTestWSUse', 'PHPUnit', $text, '');

		$this->assertEquals(count(WSStorage::getDatabase()->getWSUsages($wsId)), 2);
		$this->assertEquals(count(WSStorage::getDatabase()->getResultsFromCache($wsId)), 2);
		$this->assertEquals(count(WSStorage::getDatabase()->getWSUsages($wsId2)), 2);
		$this->assertEquals(count(WSStorage::getDatabase()->getResultsFromCache($wsId2)), 2);
		$this->assertEquals(count(WSStorage::getDatabase()->getWSsUsedInArticle($pId)), 4);
		$this->assertEquals($this->getParameterSetIdCount(), 2);

		//remove second ws call that uses another parameter value
		$text = smwf_om_GetWikiText('TimeTestWSUse');
		$text = str_replace("{{#ws:TimeTestWSUse"
		."| dontCare=something else"
		."| ?result.complete"
		."}}", "", $text);
		smwf_om_EditArticle('TimeTestWSUse', 'PHPUnit', $text, '');

		$this->assertEquals(count(WSStorage::getDatabase()->getWSUsages($wsId)), 1);
		$this->assertEquals(count(WSStorage::getDatabase()->getResultsFromCache($wsId)), 1);
		$this->assertEquals(count(WSStorage::getDatabase()->getWSUsages($wsId2)), 2);
		$this->assertEquals(count(WSStorage::getDatabase()->getResultsFromCache($wsId2)), 2);
		$this->assertEquals(count(WSStorage::getDatabase()->getWSsUsedInArticle($pId)), 3);
		$this->assertEquals($this->getParameterSetIdCount(), 2);

		// remove third ws call that uses the same parameter but different ws
		$text = smwf_om_GetWikiText('TimeTestWSUse');
		$text = str_replace("{{#ws:TimeTestWSUse2"
		."| dontCare=something else"
		."| ?result.complete"
		."}}", "", $text);
		smwf_om_EditArticle('TimeTestWSUse', 'PHPUnit', $text, '');

		$this->assertEquals(count(WSStorage::getDatabase()->getWSUsages($wsId)), 1);
		$this->assertEquals(count(WSStorage::getDatabase()->getResultsFromCache($wsId)), 1);
		$this->assertEquals(count(WSStorage::getDatabase()->getWSUsages($wsId2)), 1);
		$this->assertEquals(count(WSStorage::getDatabase()->getResultsFromCache($wsId2)), 1);
		$this->assertEquals(count(WSStorage::getDatabase()->getWSsUsedInArticle($pId)), 2);
		$this->assertEquals($this->getParameterSetIdCount(), 1);


		// remove fourth web service that does not use a parameter
		$text = smwf_om_GetWikiText('TimeTestWSUse');
		$text = str_replace("{{#ws:TimeTestWSUse2"
		."| ?result.complete"
		."}}", "", $text);
		smwf_om_EditArticle('TimeTestWSUse', 'PHPUnit', $text, '');

		$this->assertEquals(count(WSStorage::getDatabase()->getWSUsages($wsId)), 1);
		$this->assertEquals(count(WSStorage::getDatabase()->getResultsFromCache($wsId)), 1);
		$this->assertEquals(count(WSStorage::getDatabase()->getWSUsages($wsId2)), 0);
		$this->assertEquals(count(WSStorage::getDatabase()->getResultsFromCache($wsId2)), 0);
		$this->assertEquals(count(WSStorage::getDatabase()->getWSsUsedInArticle($pId)), 1);
		$this->assertEquals($this->getParameterSetIdCount(), 1);

		// remove last web service call
		$text = "no web service is used";
		smwf_om_EditArticle('TimeTestWSUse', 'PHPUnit', $text, '');

		$this->assertEquals(count(WSStorage::getDatabase()->getWSUsages($wsId)), 0);
		$this->assertEquals(count(WSStorage::getDatabase()->getResultsFromCache($wsId)), 0);
		$this->assertEquals(count(WSStorage::getDatabase()->getWSUsages($wsId2)), 0);
		$this->assertEquals(count(WSStorage::getDatabase()->getResultsFromCache($wsId2)), 0);
		$this->assertEquals(count(WSStorage::getDatabase()->getWSsUsedInArticle($pId)), 0);
		$this->assertEquals($this->getParameterSetIdCount(), 0);

	}

	public function testValidateWSUsageNotExistingResultPart(){
		di_utils_setupWebServices(array("ValidateWSUsage"));
		$wsId = di_utils_getWSId("ValidateWSUsage");

		$wsReturnValues = array("result.doesNotExist" => "");
		$wsParameters = array("nonOptionalParameter" => "value",
			"parameterWithSubParameters.nonOptionalSubParameter" => "value");

		$response = SMWWebServiceUsage::validateWSUsage($wsId, $wsReturnValues, $wsParameters);
		$this->assertEquals($response[0][0],
			"The result-part \"result.doesNotExist\" does not exist in the Wiki Web Service Definition.");
	}

	public function testValidateWSUsageNotExistingResult(){
		di_utils_setupWebServices(array("ValidateWSUsage"));
		$wsId = di_utils_getWSId("ValidateWSUsage");

		$wsReturnValues = array("doesNotExist.result" => "");
		$wsParameters = array("nonOptionalParameter" => "value",
			"parameterWithSubParameters.nonOptionalSubParameter" => "value");

		$response = SMWWebServiceUsage::validateWSUsage($wsId, $wsReturnValues, $wsParameters);
		$this->assertEquals($response[0][0],
			"The result-part \"doesNotExist.result\" does not exist in the Wiki Web Service Definition.");
	}

	public function testValidateMissingParameter(){
		di_utils_setupWebServices(array("ValidateWSUsage"));
		$wsId = di_utils_getWSId("ValidateWSUsage");

		$wsReturnValues = array("result" => "");
		$wsParameters = array("parameterWithSubParameters.nonOptionalSubParameter" => "value");

		$response = SMWWebServiceUsage::validateWSUsage($wsId, $wsReturnValues, $wsParameters);

		$this->assertEquals($response[0][0],
			"The parameter \"nonOptionalParameter\" is not optional and no default value was provided by the Wiki Web Service Definition.");
	}

	public function testValidateMissingSubParameter(){
		di_utils_setupWebServices(array("ValidateWSUsage"));
		$wsId = di_utils_getWSId("ValidateWSUsage");

		$wsReturnValues = array("result" => "");
		$wsParameters = array("nonOptionalParameter" => "value");

		$response = SMWWebServiceUsage::validateWSUsage($wsId, $wsReturnValues, $wsParameters);

		$this->assertEquals($response[0][0],
			"The parameter \"parameterWithSubParameters.nonOptionalSubParameter\" is not optional and no default value was provided by the Wiki Web Service Definition.");
	}

	public function testSubParameterCreation(){
		di_utils_setupWebServices(array("ValidateWSUsage"));
		$wsId = di_utils_getWSId("ValidateWSUsage");

		$wsReturnValues = array("result" => "");
		$wsParameters = array("nonOptionalParameter" => "value",
							"parameterWithSubParameters" => "dontCare",
							"parameterWithSubParameters.nonOptionalSubParameter" => "nopt");

		$response = SMWWebServiceUsage::validateWSUsage($wsId, $wsReturnValues, $wsParameters);

		$this->assertEquals($response[1]["nonOptionalParameter"], "value");
		$this->assertEquals($response[1]["parameterWithSubParameters"], "some<thingnoptelsedefault");
	}

	private function getParameterSetIdCount(){
		$db =& wfGetDB( DB_SLAVE );
		$tbn = $db->tableName('smw_ws_parameters');
		$sql = "SELECT DISTINCT param_set_id FROM ".$tbn;
		$result = $db->query($sql);

		$count = 0;
		while($row = $db->fetchObject($result)){
			$count += 1;
		}
		return $count;
	}


}
?>
