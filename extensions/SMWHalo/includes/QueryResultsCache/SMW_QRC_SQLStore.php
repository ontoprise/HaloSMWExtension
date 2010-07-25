<?php

/**
 *
  * @ingroup SMWHaloQueryResultsCache
 *
 * @author Ingo Steinbauer
 *
 */


global $smwgHaloIP;
require_once $smwgHaloIP . '/includes/SMW_DBHelper.php';

/**
 * Implementation of the QueryResultsCache storage for MySQL DB's 
 */
class SMWQRCSQLStore implements SMWQRCStoreInterface{
	
	/*
	 * Initialize required database tables
	 */
	public function initDatabaseTables() {
		$db =& wfGetDB( DB_MASTER );

		$verbose = true;
		DBHelper::reportProgress("Setting up qury results cache ...\n",$verbose);

		DBHelper::reportProgress("   ... Creating query results cache table \n",$verbose);
		$qrcTable = $db->tableName('smw_qrc_cache');

		DBHelper::setupTable($qrcTable, array(
				'query_id' => 'VARCHAR(36) NOT NULL PRIMARY KEY',
				'query_result' => 'LONGTEXT',
				'last_update' =>  'int(20) NOT NULL' ,
				'access_frequency' => 'INT(8) NOT NULL',
				'invalidation_frequency' => 'INT(8) NOT NULL',
				'dirty' => 'BOOLEAN NOT NULL',
				'priority' => 'INT(20) NOT NULL',),
		
			$db, $verbose);
		
		DBHelper::reportProgress("   ... done!\n",$verbose);
	}
	
	/*
	 * drop database tables
	 */
	public function dropTables(){
		$db =& wfGetDB( DB_MASTER );
		$verbose = true;
		DBHelper::reportProgress("Dropping query results cache tables ...\n",$verbose);

		$tables = array('smw_qrc_cache');
		foreach ($tables as $table) {
			$name = $db->tableName($table);
			$db->query('DROP TABLE' . ($wgDBtype=='postgres'?'':' IF EXISTS'). $name, 'SMWQRCSQLStore::drop');
			DBHelper::reportProgress(" ... dropped table $name.\n", $verbose);
		}
		
		DBHelper::reportProgress("   ... done!\n",$verbose);
	}
	
	/*
	 * get all query ids which are stored in the db
	 */
	public function getQueryIds($limit = null, $offset = null){
		$db =& wfGetDB( DB_SLAVE );
		
		$qrcTable = $db->tableName('smw_qrc_cache');
		
		global $cacheEntryAgeWeight, $accessFrequencyWeight, $invalidationFrequencyWeight, $invalidWeight;
		
		$currentTime = time();
		
		//this is necessary for the PHPUnitTests
		global $qrcLastCurrentTimePHPUnit;
		$qrcLastCurrentTimePHPUnit = $currentTime; 
		
		$sql = "SELECT query_id, "; 
		$sql .= " ($currentTime - last_update) * $cacheEntryAgeWeight";
		$sql .= " + access_frequency * $accessFrequencyWeight";
		$sql .= " + invalidation_frequency * $invalidationFrequencyWeight";
		$sql .= " + dirty*$invalidWeight";
		$sql .= " AS priority FROM $qrcTable";
		
		$sql .= " ORDER BY priority DESC";
		
		if(!is_null($limit)){
			$sql .= " LIMIT ".$limit;
		}
		
		if(!is_null($offset)){
			$sql .= " OFFSET ".$offset;
		}
		
		$res = $db->query($sql);
		
		$ids = array();
		while ($row = $db->fetchObject($res)) {
			$ids[] = $row->query_id;
		}

		$db->freeResult($res);
		return $ids;
	}
	
	/*
	 * add or update a query result in the cache
	 */
	public function updateQueryData($queryId, $queryResult, $lastUpdate, $accessFrequency, $invalidationFrequency, $dirty, $priority){
		$db =& wfGetDB( DB_MASTER );
		
		$db->update( $db->tableName("smw_qrc_cache"), 
			array(	
				'query_result' => $queryResult,
				'last_update' => $lastUpdate,
				'access_frequency' => $accessFrequency,
				'invalidation_frequency' => $invalidationFrequency,
				'dirty' => false,
				'priority' => $priority),
			array(	'query_id' => $queryId));
	}
	
	public function addQueryData($queryId, $queryResult, $lastUpdate, $accessFrequency, $invalidationFrequency, $dirty, $priority){
		$db =& wfGetDB( DB_MASTER );
		
		$db->insert( $db->tableName("smw_qrc_cache"), 
				array(	
					'query_id' => $queryId, 
					'query_result' => $queryResult, 
					'last_update' => $lastUpdate,
					'access_frequency' => $accessFrequency,
					'invalidation_frequency' => $invalidationFrequency,
					'dirty' => false,
					'priority' => $priority));
	}
	
	/*
	 * Get a query data
	 */
	public function getQueryData($queryId){
		$db =& wfGetDB( DB_SLAVE );
		
		$res = $db->select($db->tableName("smw_qrc_cache"), 
			array('query_id', 'query_result', 'last_update', 'access_frequency', 'invalidation_frequency', 'dirty', 'priority'), 
			array('query_id' => $queryId));
		
			if($db->numRows($res) > 0){
				$res = $db->fetchObject($res);
				$response = array(
					'queryId' => $res->query_id,
					'queryResult' => $res->query_result,
					'lastUpdate' => $res->last_update,
					'accessFrequency' => $res->access_frequency,
					'invalidationFrequency' => $res->invalidation_frequency,
					'dirty' => $res->dirty,
					'priority' => $res->priority);
				
				return $response;				
			} else {
				return false;
			}
	}
	
	/*
	 * Remove a query result with a given query id from the cache
	 */
	public function deleteQueryData($queryId){
		$db =& wfGetDB( DB_MASTER );
		
		$db->delete($db->tableName("smw_qrc_cache"), 
				array(	'query_id' => $queryId));
	}

	public function invalidateQueryData($queryIds){
		$db =& wfGetDB( DB_MASTER );

		$qrcTable = $db->tableName('smw_qrc_cache');
		
		//not good that the store knows about the priority algorithm, but faster than first querying for all data
		global $cacheEntryAgeWeight, $accessFrequencyWeight, $invalidationFrequencyWeight;
		
		$query = "UPDATE $qrcTable SET invalidation_frequency = invalidation_frequency + 1, dirty=true, "; 
		$query .= "priority = 0"; 
		$query .= " WHERE query_id=\"";
		$query .= implode('" OR query_id="', array_keys($queryIds));
		$query .= '"';

		$db->query($query, 'Database::update');

	}
}