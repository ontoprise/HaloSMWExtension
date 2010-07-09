<?php

/**
 *
  * @ingroup SMWHaloQueryResultsCache
 *
 * @author Ingo Steinbauer
 *
 */

/*
 * Implementations of this class provide access to 
 * the Query Results Cache DB storage layer.
 */
class SMWQRCStore {
	
	private static $instance;
	
	private static $storeImplementations = array();
	
	/*
	 * singleton
	 */
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/*
	 * register implementations of the SMWQRCStoreInterface
	 */
	public static function registerStoreImplementation($type, $class){
		self::$storeImplementations[$type] = $class; 
	}
	
	private $mDB;
	
	/*
	 * singleton
	 */
	public function __construct(){
		global $smwgHaloIP;
		require_once( "$smwgHaloIP/includes/QueryResultsCache/SMW_QRC_SQLStore.php" );
		self::registerStoreImplementation('mysql', 'SMWQRCSQLStore');
		
		global $wgDBtype;
		if(array_key_exists($wgDBtype, self::$storeImplementations)){
			$this->mDB = new self::$storeImplementations[$wgDBtype]();
		} else {
			die('The Query Results Cache does not support the '.$wgDBtype.' database type.');
		}
			
		return $this;
	}
	
	/*
	 * get concrete storage implementation
	 */
	public function getDB(){
		return $this->mDB;
	}
	
	
}

interface SMWQRCStoreInterface {
	/*
	 * Initialize required database tables
	 */
	public function initDatabaseTables();
	
	/*
	 * drop database tables
	 */
	public function dropTables();
	
	/*
	 * get all query ids which are stored in the db
	 */
	public function getQueryIds($limit = null, $offset = null);
		
	/*
	 * update query data
	 */
	public function updateQueryData($queryId, $queryResult, $lastUpdate, $accesFrequency, $invalidationFrequency, $dirty, $priority);
	
	/*
	 * add query data
	 */
	public function addQueryData($queryId, $queryResult, $lastUpdate, $accesFrequency, $invalidationFrequency, $dirty, $priority);
	
	/*
	 * Get a query result from the cache
	 */
	public function getQueryData($queryId);
	
	/*
	 * Remove a query result with a given query id from the cache
	 */
	public function deleteQueryData($queryId);
	
	/*
	 * Invalidate a query result and update its score
	 */
	public function invalidateQueryData($queryIds);
}