<?php
/*  Copyright 2008, ontoprise GmbH
 *  This file is part of the Data Import-Extension.
 *
 *   The Data Import-Extension is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The Data Import-Extension is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @file
 * @ingroup DIWebServices
 *
 * This file provides the access to the MediaWiki SQL database tables that are
 * used by the web service extension.
 *
 * @author Thomas Schweitzer, Ingo Steinbauer
 *
 */
if ( !defined( 'MEDIAWIKI' ) ) die;
global $smwgHaloIP;
require_once $smwgHaloIP . '/includes/SMW_DBHelper.php';

/**
 * This class encapsulates all methods that care about the database tables of
 * the web service extension.
 *
 */
class WSStorageSQL {
	
	/*
	 * Check if all database tables already have been initialized
	 */
	public function isInitialized(){
		$isInitialized = true;
		
		$db =& wfGetDB( DB_SLAVE );

		$wwsdTable = $db->tableName('smw_ws_wwsd');
		$isInitialized = $isInitialized && $db->tableExists($wwsdTable);
		
		$cacheTable = $db->tableName('smw_ws_cache');
		$isInitialized = $isInitialized && $db->tableExists($cacheTable);
		
		$paramTable = $db->tableName('smw_ws_parameters');
		$isInitialized = $isInitialized && $db->tableExists($paramTable);
		
		$articlesTable = $db->tableName('smw_ws_articles');
		$isInitialized = $isInitialized && $db->tableExists($articlesTable);
		
		return $isInitialized;
	}

	/**
	 * Initializes the database tables of the web service extensions. These are:
	 * - Wiki Web Service Definition: smw_ws_wwsd
	 * - Cache for values: smw_ws_cache
	 * - actual parameters: smw_ws_parameters
	 * - articles with web services: smw_ws_articles
	 *
	 */
	public function initDatabaseTables() {

		$db =& wfGetDB( DB_MASTER );

		$verbose = true;
		DBHelper::reportProgress("Setting up web services ...\n",$verbose);

		DBHelper::reportProgress("   ... Creating WWSD table \n",$verbose);
		$wwsdTable = $db->tableName('smw_ws_wwsd');

		DBHelper::setupTable($wwsdTable, array(
				  'web_service_id'		 =>  'INT(8) UNSIGNED NOT NULL PRIMARY KEY',
				  'uri'  	             =>  'VARCHAR(1024) NOT NULL' ,
				  'protocol'             =>  'VARCHAR(20) NOT NULL' ,
				  'method'               =>  'VARCHAR(64) NOT NULL' ,
				  'parameters'           =>  'MEDIUMTEXT NOT NULL' ,
				  'result'               =>  'MEDIUMTEXT NOT NULL' ,
				  'display_policy'       =>  'INT(8) UNSIGNED NOT NULL' ,
				  'query_policy'         =>  'INT(8) UNSIGNED NOT NULL' ,
				  'update_delay'         =>  'INT(8) UNSIGNED NOT NULL' ,
				  'span_of_life'         =>  'INT(8) UNSIGNED NOT NULL' ,
				  'expires_after_update' =>  'ENUM(\'true\', \'false\') DEFAULT \'false\' NOT NULL' ,
				  'confirmed'            =>  'ENUM(\'true\', \'false\', \'once\') DEFAULT \'false\' NOT NULL',
		 		 'authentication_type'       =>  'VARCHAR(10) NOT NULL' ,
				'authentication_login'       =>  'VARCHAR(50) NOT NULL' ,
				'authentication_password'    =>  'VARCHAR(50) NOT NULL' ),
		$db, $verbose);

		$query = "ALTER TABLE ".$wwsdTable." ENGINE=MyISAM; ";
		$db->query($query);

		$query = "ALTER TABLE ".$wwsdTable." MODIFY COLUMN protocol VARCHAR(20) NOT NULL; ";
		$db->query($query);

		$query = "ALTER TABLE ".$wwsdTable." MODIFY COLUMN authentication_login VARCHAR(50) NOT NULL; ";
		$db->query($query);

		$query = "ALTER TABLE ".$wwsdTable." MODIFY COLUMN authentication_password VARCHAR(60) NOT NULL; ";
		$db->query($query);

		DBHelper::reportProgress("   ... done!\n",$verbose);

		// create ws value cache table
		DBHelper::reportProgress("   ... Creating web service cache table \n",$verbose);
		$cacheTable = $db->tableName('smw_ws_cache');
		DBHelper::setupTable($cacheTable, array(
				  'web_service_id'  =>  'INT(8) UNSIGNED NOT NULL',
				  'param_set_id'  	=>  'INT(8) UNSIGNED NOT NULL' ,
				  'result'          =>  'LONGTEXT NOT NULL' ,
				  'last_update'    	=>  'VARCHAR(14) NOT NULL' ,
				  'last_access'    	=>  'VARCHAR(14) NOT NULL'), 
		$db, $verbose, 'web_service_id,param_set_id');

		$query = "ALTER TABLE ".$cacheTable." ENGINE=MyISAM; ";
		$db->query($query);

		$query = "ALTER TABLE ".$cacheTable." MODIFY result LONGTEXT NOT NULL";
		$db->query($query);
		
		$query = "ALTER TABLE ".$cacheTable." MODIFY param_set_id VARCHAR(32) NOT NULL";
		$db->query($query);

		DBHelper::reportProgress("   ... done!\n",$verbose);

		// create parameter table
		DBHelper::reportProgress("   ... Creating parameter table \n",$verbose);
		$paramTable = $db->tableName('smw_ws_parameters');
		DBHelper::setupTable($paramTable, array(
				  'name'		    =>  'VARCHAR(255) NOT NULL',
				  'param_set_id'  	=>  'INT(8) UNSIGNED NOT NULL' ,
				  'value'      	    =>  'LONGTEXT NOT NULL'), $db, $verbose);

		$query = "ALTER TABLE ".$paramTable." MODIFY value LONGTEXT NOT NULL";
		$db->query($query);

		$query = "ALTER TABLE ".$paramTable." ENGINE=MyISAM; ";
		$db->query($query);
		
		$query = "ALTER TABLE ".$paramTable." MODIFY param_set_id VARCHAR(32) NOT NULL";
		$db->query($query);

		DBHelper::reportProgress("   ... done!\n",$verbose);

		// create articles table
		DBHelper::reportProgress("   ... Creating article table \n",$verbose);
		$articlesTable = $db->tableName('smw_ws_articles');
		DBHelper::setupTable($articlesTable, array(
				  'web_service_id'  =>  'INT(8) UNSIGNED NOT NULL',
				  'param_set_id'  	=>  'INT(8) UNSIGNED NOT NULL' ,
				  'page_id'      	=>  'INT(8) UNSIGNED NOT NULL'), 
		$db, $verbose, 'web_service_id,param_set_id,page_id');

		$query = "ALTER TABLE ".$articlesTable." ENGINE=MyISAM; ";
		$db->query($query);
		
		$query = "ALTER TABLE ".$articlesTable." MODIFY param_set_id VARCHAR(32) NOT NULL";
		$db->query($query);

		DBHelper::reportProgress("   ... done!\n",$verbose);

	}

	public function deleteDatabaseTables() {
		global $wgDBtype;
		
		$db =& wfGetDB( DB_MASTER );
		$verbose = true;
		DBHelper::reportProgress("Dropping web service tables ...\n",$verbose);

		$tables = array('smw_ws_wwsd', 'smw_ws_cache', 'smw_ws_parameters', 'smw_ws_articles');
		foreach ($tables as $table) {
			$name = $db->tableName($table);
			$db->query('DROP TABLE' . ($wgDBtype=='postgres'?'':' IF EXISTS'). $name, 'WSStorageSQL::drop');
			DBHelper::reportProgress(" ... dropped table $name.\n", $verbose);
		}

		DBHelper::reportProgress("   ... done!\n",$verbose);
	}


	/**
	 * Stores a web service in the database.
	 *
	 * @param WebService $webService
	 *
	 * @return bool
	 * 	  <true>, if successful
	 *    <false>, otherwise
	 *
	 */
	public function storeWS(WebService &$webService) {
		$db =& wfGetDB( DB_MASTER );
		try {
			$db->replace($db->tableName('smw_ws_wwsd'), null, array(
					  'web_service_id'	=>  $webService->getArticleID(),
					  'uri'  	        =>  $webService->getURI() ,
					  'protocol'        =>  $webService->getProtocol() ,
					  'method'          =>  $webService->getMethod(),
					  'parameters'      =>  $webService->getParameters() ,
					  'result'          =>  $webService->getResult() ,
					  'display_policy'  =>  $webService->getDisplayPolicy() ,
					  'query_policy'    =>  $webService->getQueryPolicy() ,
					  'update_delay'    =>  $webService->getUpdateDelay(),
					  'span_of_life'    =>  $webService->getSpanOfLife() ,
					  'expires_after_update'
					  =>  $webService->doesExpireAfterUpdate() == "true" ? 'true' : 'false',
					  'confirmed'       =>  $webService->getConfirmationStatus(),
					  'authentication_type' => $webService->getAuthenticationType(),
					  'authentication_login' => $webService->getAuthenticationLogin(),
					  'authentication_password' => $webService->getAuthenticationPassword()));
		} catch (Exception $e) {
			$temp = $e->getMessage();
			$this->$temp();
			echo $e->getMessage();
			return false;
		}
		return true;

	}

	/**
	 * Retrieves the wiki web service definition (WWSD) of the web service with
	 * the page id <$id> from the database.
	 *
	 * @param int $id
	 * 		The unique page ID of the web service's WWSD
	 *
	 * @return WebService
	 * 		An instance of class WebService or
	 * 		<null>, if the requested WWSD does not exist.
	 */
	public function getWS($id) {
		$db =& wfGetDB( DB_SLAVE );
		$wwsd = $db->tableName('smw_ws_wwsd');
		$page = $db->tableName('page');
		$sql = "SELECT wwsd.*, p.page_title FROM ".$wwsd." wwsd ".
		          "JOIN ".$page." p ON wwsd.web_service_id = p.page_id ".
		          "WHERE web_service_id = ".$id.";";
		$ws = null;

		$res = $db->query($sql);

		if ($db->numRows($res) == 1) {
			$row = $db->fetchObject($res);
			$ws = new WebService($row->web_service_id, $row->uri, $row->protocol,
			$row->method, $row->authentication_type, $row->authentication_login,
			$row->authentication_password, $row->parameters, $row->result,
			$row->display_policy, $row->query_policy,
			$row->update_delay, $row->span_of_life,
			$row->expires_after_update,
			$row->confirmed);
		}
		$db->freeResult($res);
		return $ws;

	}

	/**
	 * The database stores which web services with which parameter set IDs are
	 * used in which article. This method adds such a usage to the DB.
	 *
	 * @param int $wsPageID
	 * 		The unique page ID of the web service's WWSD.
	 * @param int $paramSetID
	 * 		The unique parameter set ID.
	 * @param int $pageID
	 * 		The ID of the article that uses the web service.
	 * @return bool
	 * 		<true>, if the DB entry was successfully added
	 * 		<false>, otherwise
	 */
	public function addWSArticle($wsPageID, $paramSetID, $pageID) {
		$db =& wfGetDB( DB_MASTER );
		try {
			$db->delete($db->tableName('smw_ws_articles'), array(
					  'web_service_id' => $wsPageID,
					  'param_set_id'   => $paramSetID,
					  'page_id'        => $pageID));
			$db->insert($db->tableName('smw_ws_articles'), array(
					  'web_service_id' => $wsPageID,
					  'param_set_id'   => $paramSetID,
					  'page_id'        => $pageID));
		} catch (Exception $e) {
			echo $e->getMessage();
			return false;
		}
	}

	/**
	 * Retrieves the articles that use the given web service.
	 *
	 * @param int $wsPageID
	 * 		The unique page ID of the web service's WWSD.
	 *
	 * @return array:
	 * 		An array of page ids of articles that use the web service.
	 */
	public function getWSArticles($wsPageID, SMWRequestOptions $options) {
		$db =& wfGetDB( DB_SLAVE );
		$atbl = $db->tableName('smw_ws_articles');
		$page = $db->tableName('page');
		$sql = "SELECT DISTINCT art.page_id, p.page_title FROM ".$atbl." art ".
		          "JOIN ".$page." p ON art.page_id = p.page_id ".
		          "WHERE art.web_service_id='".$wsPageID."' ";
		$cond = DBHelper::getSQLConditions($options, 'p.page_title');
		$opt = DBHelper::getSQLOptionsAsString($options, 'p.page_title');

		$articles = array();

		$res = $db->query($sql.$cond.' '.$opt);

		if ($db->numRows($res) > 0) {
			while ($row = $db->fetchObject($res)) {
				$articles[] = $row->page_id;
			}
		}
		$db->freeResult($res);
		return $articles;

	}


	/**
	 * This function stores a new parameter set for the given
	 * parameters if no such parameter set allready exists.
	 *
	 * @param array $parameters
	 * 		the parameters for which a new set has to be stored
	 * @return string
	 * 		the parameter set id of the new or an
	 * 		appropriate parameter set which allready exists
	 * 		(already existing parameterSets will start wirh "#"
	 * 		so that they can be detected.
	 */
	public function storeParameterset($parameters){
		if(sizeof($parameters) == 0){
			return "0";
		}

		$db =& wfGetDB( DB_SLAVE );
		$ptbl = $db->tableName('smw_ws_parameters');

		
		$parameterSetId = md5(implode(',',array_keys($parameters)).implode(',', $parameters));
		
		$sql = "SELECT parameters.param_set_id ".
			 "FROM ".$ptbl." parameters ".
			'WHERE parameters.param_set_id = "'.$parameterSetId.'"';

		$res = $db->query($sql);

		if ($db->numRows($res) >= 1){
			// an appropriate parameter set exists
			$row = $db->fetchObject($res);
			$parameterSetId = "#".$row->param_set_id;
		} else {
			$db =& wfGetDB( DB_MASTER );
			foreach($parameters as $name => $value){
				try {
					$db->insert($db->tableName('smw_ws_parameters'), array(
					  'name'    => $name,
					  'param_set_id' => $parameterSetId,
					  'value'   => $value));
				} catch (Exception $e) {
					echo $e->getMessage();
					return false;
				}
			}
		}
		return $parameterSetId;
	}

	/**
	 * The database stores which web services with which parameter set IDs are
	 * used in which article. This method removes such usage from the DB.
	 *
	 * @param int $wsPageID
	 * 		The unique page ID of the web service's WWSD.
	 * @param int $paramSetID
	 * 		The unique parameter set ID.
	 * @param int $pageID
	 * 		The ID of the article that uses the web service.
	 * @return bool
	 * 		<true>, if the DB entry was successfully added
	 * 		<false>, otherwise
	 */
	public function removeWSArticle($wsPageID, $paramSetID, $pageID) {
		$db =& wfGetDB( DB_MASTER );
		try {
			$db->delete($db->tableName('smw_ws_articles'), array(
					  'web_service_id' => $wsPageID,
					  'param_set_id'   => $paramSetID,
					  'page_id'        => $pageID));
		} catch (Exception $e) {
			echo $e->getMessage();
			return false;
		}
		return true;
	}

	/**
	 * This method deletes the given parameter set from the DB.
	 *
	 * @param string
	 * 		id of the parameter set to delete
	 * @return boolean
	 * 		true: e.th. ok
	 * 		false: errors occured
	 */
	public function removeParameterSet($parameterSetId) {
		$db =& wfGetDB( DB_MASTER );

		try {
			$db->delete($db->tableName('smw_ws_parameters'), array(
			'param_set_id' => $parameterSetId));
		} catch (Exception $e) {
			echo $e->getMessage();
			return false;
		}
		return true;
	}

	/**
	 * This function retrieves all articles that use a parameter set with the given id
	 *
	 * @param string $parameterSetId
	 * @return array
	 * 	an array of parameter set ids
	 */
	function getUsedParameterSetIds($parameterSetId){
		$db =& wfGetDB( DB_SLAVE );
		$ptb = $db->tableName('smw_ws_articles');

		$sql = "SELECT wsArticles.param_set_id"
		." FROM ".$ptb." wsArticles ".
		'WHERE wsArticles.param_set_id = "'.$parameterSetId.'"';

		$webServices = array();
		$res = $db->query($sql);

		if ($db->numRows($res) > 0) {
			while ($row = $db->fetchObject($res)) {
				array_push($webServices, array($row->param_set_id));
			}
		}
		$db->freeResult($res);
		return $webServices;
	}

	/**
	 * this method retrieves all webservices and the parameter sets used
	 * by those, which are used on the given article
	 *
	 * @param string articleid
	 * @return array
	 * 		an array of the webservices and the parameter sets used by them
	 */
	function getWSsUsedInArticle($articleId){
		$db =& wfGetDB( DB_SLAVE );
		$ptb = $db->tableName('smw_ws_articles');

		$sql = "SELECT wsArticles.web_service_id, wsArticles.param_set_id"
		." FROM ".$ptb." wsArticles ".
		"WHERE wsArticles.page_id =".$articleId."";

		$webServices = array();
		$res = $db->query($sql);

		if ($db->numRows($res) > 0) {
			while ($row = $db->fetchObject($res)) {
				array_push($webServices, array($row->web_service_id, $row->param_set_id));
			}
		}
		$db->freeResult($res);
		return $webServices;
	}

	/**
	 * get all parameters and their values that belong
	 * to the given parameterset
	 *
	 * @param int $parameterSetId
	 * @return array of parameters and their values
	 */
	function getParameters($parameterSetId){
		$db =& wfGetDB( DB_SLAVE );
		$ptb = $db->tableName('smw_ws_parameters');

		$sql = "SELECT param.name, param.value"
		." FROM ".$ptb." param ".
		'WHERE param.param_set_id = "'.$parameterSetId.'"';

		$parameters = array();
		$res = $db->query($sql);

		if ($db->numRows($res) > 0) {
			while ($row = $db->fetchObject($res)) {
				$parameters[$row->name] = $row->value;
			}
		}
		$db->freeResult($res);
		return $parameters;
	}

	/**
	 * Removes a single cache entry
	 *
	 * @param string $wsPageID
	 * @param string_type $paramSetID
	 * @return boolean success
	 */
	public function removeWSEntryFromCache($wsPageID, $paramSetID) {
		$db =& wfGetDB( DB_MASTER );
		try {
			$db->delete($db->tableName('smw_ws_cache'), array(
					  'web_service_id' => $wsPageID,
					  'param_set_id'   => $paramSetID));
		} catch (Exception $e) {
			echo $e->getMessage();
			return false;
		}
		return true;
	}

	/**
	 * Get all articles that use the given ws-parameterset-combination
	 *
	 * @param string $wsPageId
	 * @param string $parameterSetId
	 * @return unknown
	 */
	function getUsedWSParameterSetPairs($wsId, $parameterSetId){
		$db =& wfGetDB( DB_SLAVE );
		$result = array();
		$res = $db->select( $db->tableName("smw_ws_articles"),
		array("page_id"),
		array(	"web_service_id" => $wsId,
		             		"param_set_id" => $parameterSetId));

		$result = array();
		while($row = $db->fetchObject($res)){
			$result[] = $row->page_id;
		}
		$db->freeResult($res);
		return $result;
	}

	/**
	 * remove all entries according to the given ws-id
	 * from the cache
	 *
	 * @param string $wsPageID
	 * @return boolean success
	 */
	public function removeWSFromCache($wsPageID) {
		$db =& wfGetDB( DB_MASTER );
		try {
			$db->delete($db->tableName('smw_ws_cache'), array(
					  'web_service_id' => $wsPageID));
		} catch (Exception $e) {
			echo $e->getMessage();
			return false;
		}
		return true;
	}

	/**
	 * get results for the given web service/parameter-pair
	 * from the cache
	 *
	 * @param string $wsPageId
	 * @param string $parameterSetId
	 * @return result array
	 */
	function getResultFromCache($wsPageId, $parameterSetId){
		$db =& wfGetDB( DB_SLAVE );
		$result = array();
		$res = $db->select( $db->tableName("smw_ws_cache"),
		array("result", "last_update", "last_access"),
		array(	"web_service_id" => $wsPageId,
		             		"param_set_id" => $parameterSetId));

		if ($db->numRows($res) == 1) {
			$row = $db->fetchObject($res);
			$result["result"] = $row->result;
			$result["lastUpdate"] = $row->last_update;
			$result["lastAccess"] = $row->last_access;
		}
		$db->freeResult($res);
		return $result;
	}

	/**
	 * get results for the given web service id
	 * from the cache
	 *
	 * @param string $wsPageId
	 * @return result array
	 */
	function getResultsFromCache($wsId){
		$db =& wfGetDB( DB_SLAVE );
		$tbn = $db->tableName('smw_ws_cache');

		$sql = "SELECT cache.param_set_id ,cache.result, cache.last_update, cache.last_access";
		$sql .= " FROM ".$tbn." cache";
		$sql .= " WHERE cache.web_service_id =\"".$wsId."\"";
		$sql .= " ORDER BY cache.last_update ASC";

		$res = $db->query($sql);

		$results = array();
		$result = array();

		while ($row = $db->fetchObject($res)) {
			$result["paramSetId"] = $row->param_set_id;
			$result["result"] = $row->result;
			$result["lastUpdate"] = $row->last_update;
			$result["lastAccess"] = $row->last_access;
			$results[] = $result;
		}

		$db->freeResult($res);
		return $results;
	}



	/**
	 * stores the result of a ws-call in the cache
	 *
	 * @param string_type $wsId
	 * @param string $parameterSetId
	 * @param string $result (the serialized result of the ws-call)
	 */
	function storeCacheEntry($wsId, $parameterSetId, $result){
		$db =& wfGetDB( DB_MASTER );
		$ptb = $db->tableName('smw_ws_cache');

		try {
			$db->delete($db->tableName('smw_ws_cache'), array(
					  'web_service_id'    => $wsId,
					  'param_set_id' => $parameterSetId));
			$db->insert($db->tableName('smw_ws_cache'), array(
					  'web_service_id'    => $wsId,
					  'param_set_id' => $parameterSetId,
					  'result'   => $result,
						'last_update' => $db->timestamp(),
						'last_access' => $db->timestamp()));
		} catch (Exception $e) {
			//echo $e->getMessage();
			return false;
		}
	}


	/**
	 * remove a wwsd
	 *
	 * @param string $wsPageID
	 * @return boolean success
	 */
	public function removeWS($wsPageID) {
		$db =& wfGetDB( DB_MASTER );
		try {
			$db->delete($db->tableName('smw_ws_wwsd'), array(
					  'web_service_id' => $wsPageID));
		} catch (Exception $e) {
			echo $e->getMessage();
			return false;
		}
		return true;
	}



	/**
	 * sets the timestamp of the last access of a cache entry.
	 *
	 * @param unknown_type $wsId
	 * @param unknown_type $parameterSetId
	 */
	function updateCacheLastAccess($wsId, $parameterSetId){
		$db =& wfGetDB( DB_MASTER );
		try {
			$res = $db->update( $db->tableName("smw_ws_cache"), array(  'last_access' => $db->timestamp()),
			array(	'web_service_id'    => $wsId,
					'param_set_id' => $parameterSetId)
			);
		}catch (Exception $e) {
			echo $e->getMessage();
		}
	}

	/**
	 * this method returns an array of all defined web services
	 *
	 * @return array of webservices
	 */
	public function getWebServices() {
		$db =& wfGetDB( DB_SLAVE );
		$ptb = $db->tableName("smw_ws_wwsd");
		$sql = "SELECT * FROM ".$ptb;

		$res = $db->query($sql);

		$webServices = array();

		while($row = $db->fetchObject($res)){
			$ws = new WebService($row->web_service_id, $row->uri, $row->protocol,
			$row->method, $row->authentication_type, $row->authentication_login,
			$row->authentication_password, $row->parameters, $row->result,
			$row->display_policy, $row->query_policy,
			$row->update_delay, $row->span_of_life,
			$row->expires_after_update,
			$row->confirmed);

			$webServices[$ws->getName()] = $ws;
		}

		$db->freeResult($res);
		return $webServices;

	}

	/**
	 * this method changes the confirmation status of a wwsd to
	 * the given value.
	 *
	 * @param string $wsId
	 * @param string $status possible values are: "false", "once" and "true"
	 */
	function setWWSDConfirmationStatus($wsId, $status){
		$db =& wfGetDB( DB_MASTER );
		try {
			$res = $db->update( $db->tableName("smw_ws_wwsd"),
			array(  'confirmed' => $status),
			array(	'web_service_id'    => $wsId));
		}catch (Exception $e) {
			echo $e->getMessage();
		}
	}

	/**
	 * get all web service usages
	 *
	 * @param string $wsId
	 * @return array
	 */
	function getWSUsages($wsId){
		$db =& wfGetDB( DB_SLAVE );
		$result = array();
		$tbn = $db->tableName('smw_ws_articles');

		$sql = "SELECT DISTINCT articles.param_set_id FROM ".$tbn. " articles
		WHERE articles.web_service_id = \"".$wsId."\"";
			
		$res = $db->query($sql);

		$result = array();

		while($row = $db->fetchObject($res)){
			$result[] = array("paramSetId" => $row->param_set_id);
		}
		return $result;
	}
}