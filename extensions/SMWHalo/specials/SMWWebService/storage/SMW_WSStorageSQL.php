<?php
/*  Copyright 2008, ontoprise GmbH
*  This file is part of the halo-Extension.
*
*   The halo-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The halo-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * This file provides the access to the MediaWiki SQL database tables that are 
 * used by the web service extension.
 * 
 * @author Thomas Schweitzer
 * 
 */

global $smwgHaloIP;
require_once $smwgHaloIP . '/includes/SMW_DBHelper.php';

/**
 * This class encapsulates all methods that care about the database tables of 
 * the web service extension.
 *
 */
class WSStorageSQL {
	
	/**
	 * Initializes the database tables of the web service extensions. These are:
	 * - Wiki Web Service Definition: smw_ws_wwsd
	 * - Cache for values: smw_ws_cache
	 * - actual parameters: smw_ws_parameters
	 * - properties: smw_ws_properties
	 * - articles with web services: smw_ws_articles
	 *
	 */
	public function initDatabaseTables() {

		$db =& wfGetDB( DB_MASTER );

		// create WWSD table
		$wwsdTable = $db->tableName('smw_ws_wwsd');
		if ($db->tableExists($wwsdTable) === true) {
			return;
		}
		
		DBHelper::setupTable($wwsdTable, array(
				  'web_service_id'		 =>  'INT(8) UNSIGNED NOT NULL PRIMARY KEY',
				  'uri'  	             =>  'VARCHAR(1024) NOT NULL' ,
				  'protocol'             =>  'VARCHAR(8) NOT NULL' ,
				  'method'               =>  'VARCHAR(64) NOT NULL' ,
				  'parameters'           =>  'TEXT NOT NULL' ,
				  'result'               =>  'TEXT NOT NULL' ,
				  'display_policy'       =>  'INT(8) UNSIGNED NOT NULL' ,
				  'query_policy'         =>  'INT(8) UNSIGNED NOT NULL' ,
				  'update_delay'         =>  'INT(8) UNSIGNED NOT NULL' ,
				  'span_of_life'         =>  'INT(8) UNSIGNED NOT NULL' ,
				  'expires_after_update' =>  'ENUM(\'true\', \'false\') DEFAULT \'false\' NOT NULL' ,
				  'confirmed'            =>  'ENUM(\'true\', \'false\') DEFAULT \'false\' NOT NULL'), $db, true);

		// create ws value cache table
		$cacheTable = $db->tableName('smw_ws_cache');
		DBHelper::setupTable($cacheTable, array(
				  'web_service_id'  =>  'INT(8) UNSIGNED NOT NULL',
				  'param_set_id'  	=>  'INT(8) UNSIGNED NOT NULL' ,
				  'result'          =>  'TEXT NOT NULL' ,
				  'last_update'    	=>  'DATETIME NOT NULL' ,
				  'last_access'    	=>  'DATETIME NOT NULL' ,
				  'PRIMARY KEY(web_service_id,param_set_id)' =>  ''), $db, true);
		
		// create parameter table
		$paramTable = $db->tableName('smw_ws_parameters');
		DBHelper::setupTable($paramTable, array(
				  'name'		    =>  'VARCHAR(255) NOT NULL',
				  'param_set_id'  	=>  'INT(8) UNSIGNED NOT NULL' ,
				  'value'      	    =>  'VARCHAR(255) NOT NULL'), $db, true);

		// create properties table
		$propTable = $db->tableName('smw_ws_properties');
		DBHelper::setupTable($propTable, array(
				  'property_id'     =>  'INT(8) UNSIGNED NOT NULL' ,
				  'page_id'      	=>  'INT(8) UNSIGNED NOT NULL' ,
				  'web_service_id'	=>  'INT(8) UNSIGNED NOT NULL',
				  'param_set_id'  	=>  'INT(8) UNSIGNED NOT NULL',
				  'PRIMARY KEY(property_id,page_id,web_service_id,param_set_id)' => ''), $db, true);
		
		// create articles table
		$articlesTable = $db->tableName('smw_ws_articles');
		DBHelper::setupTable($articlesTable, array(
				  'web_service_id'  =>  'INT(8) UNSIGNED NOT NULL',
				  'param_set_id'  	=>  'INT(8) UNSIGNED NOT NULL' ,
				  'page_id'      	=>  'INT(8) UNSIGNED NOT NULL' ,
				  'PRIMARY KEY(web_service_id,param_set_id,page_id)' =>  ''), $db, true);
		
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
					                    =>  $webService->doesExpireAfterUpdate() ? 'true' : 'false',
					  'confirmed'       =>  $webService->isConfirmed() ? 'true' : 'false'));
 		} catch (Exception $e) {
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
		$sql = "SELECT wwsd.*, p.page_title FROM ".$wwsd." wwsd ".
		          "JOIN page p ON wwsd.web_service_id = p.page_id ".
		          "WHERE web_service_id = ".$id.";";
		$ws = null;
		
		$res = $db->query($sql);
		
		if ($db->numRows($res) == 1) {
			$row = $db->fetchObject($res);
			$ws = new WebService($row->page_title, $row->uri, $row->protocol,
			                     $row->method, $row->parameters, $row->result,
			                     $row->display_policy, $row->query_policy,
			                     $row->updateDelay, $row->span_of_life, 
			                     $rwow->expires_after_update == 'true',
			                     $row->confirmed == 'true');
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
			$db->insert($db->tableName('smw_ws_articles'), array(
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
		$sql = "SELECT DISTINCT art.page_id, p.page_title FROM ".$atbl." art ".
		          "JOIN page p ON art.page_id = p.page_id ".
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
	 * The database stores which web services with which parameter set IDs are
	 * used in which article. This method adds such a usage to the DB.
	 *
	 * @param int $propertyID
	 * 		The unique page ID of the property.
	 * @param int $wsPageID
	 * 		The unique page ID of the web service's WWSD.
	 * @param int $paramSetID
	 * 		The unique parameter set ID.
	 * @param int $pageID
	 * 		The ID of the article that uses the web service for the property.
	 * @return bool
	 * 		<true>, if the DB entry was successfully added
	 * 		<false>, otherwise
	 */
	public function addWSProperty($propertyID, $wsPageID, $paramSetID, $pageID) {
 		$db =& wfGetDB( DB_MASTER );
 		try {
			$db->insert($db->tableName('smw_ws_properties'), array(
					  'property_id'    => $propertyID,
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
	 * Retrieves the page IDs of properties that get their value from the given 
	 * web service. The IDs can be sorted for the names of the properties.
	 *
	 * @param string $wsPageID
	 * 		The unique page ID of the web service's WWSD.
	 * 
	 * @return array:
	 * 		An array of property page ids. 
	 */
	public function getWSProperties($wsPageID, SMWRequestOptions $options) {
		$db =& wfGetDB( DB_SLAVE );
		$ptbl = $db->tableName('smw_ws_properties');
		$sql = "SELECT DISTINCT prop.page_id, p.page_title FROM ".$ptbl." prop ".
		          "JOIN page p ON prop.page_id = p.page_id ".
		          "WHERE prop.web_service_id='".$wsPageID."' ";
		$cond = DBHelper::getSQLConditions($options, 'p.page_title');
		$opt = DBHelper::getSQLOptionsAsString($options, 'p.page_title');
		
		$properties = array();
		
		$res = $db->query($sql.$cond.' '.$opt);
		
		if ($db->numRows($res) > 0) {
			while ($row = $db->fetchObject($res)) {
				$properties[] = $row->page_id;
			}
		}
		$db->freeResult($res);
		return $properties;
		
	}
	
}
?>