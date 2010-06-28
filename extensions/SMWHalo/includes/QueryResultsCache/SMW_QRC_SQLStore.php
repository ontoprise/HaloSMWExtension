<?php

/**
 *
  * @ingroup SMWHaloQueryResultsCache
 *
 * @author Ingo Steinbauer
 *
 */

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
				'last_update' =>  'VARCHAR(14) NOT NULL' ,),
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
		//todo: add limit and offset parameter
		
		$db =& wfGetDB( DB_SLAVE );
		
		$qrcTable = $db->tableName('smw_qrc_cache');
		
		$sql = "SELECT query_id FROM ".$qrcTable;
		
		if(!is_null($limit)){
			$sql .= " LIMIT ".$limit;
		}
		
		if(!is_null($offset)){
			$sql .= " OFFSET ".$offset;
		}
		
		$sql .= ";";
		
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
	public function updateQueryResult($queryId, $queryResult){
		$db =& wfGetDB( DB_MASTER );
		
		$res = $db->select($db->tableName("smw_qrc_cache"), 
			array('query_id'), array('query_id' => $queryId));
		
		if($db->numRows($res) > 0){
			$db->update( $db->tableName("smw_qrc_cache"), 
				array(	'query_result' => $queryResult),
				array(	'query_id' => $queryId));
		} else {
			$db->insert( $db->tableName("smw_qrc_cache"), 
				array(	'query_id' => $queryId, 
					'query_result' => $queryResult));
		}
	}
	
	/*
	 * Get a query result from the cache
	 */
	public function getQueryResult($queryId){
		$db =& wfGetDB( DB_SLAVE );
		
		$res = $db->select($db->tableName("smw_qrc_cache"), 
			array('query_result'), array('query_id' => $queryId));
		
			if($db->numRows($res) > 0){
				return $db->fetchObject($res)->query_result;
			} else {
				return false;
			}
	}
	
	/*
	 * Remove a query result with a given query id from the cache
	 */
	public function deleteQueryResult($queryId){
		$db =& wfGetDB( DB_MASTER );
		
		$db->delete($db->tableName("smw_qrc_cache"), 
				array(	'query_id' => $queryId));
	}	
}