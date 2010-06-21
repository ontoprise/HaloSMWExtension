<?php

class SMWQRCStore {
	
	private static $instance;
	
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	private $mDB;
	
	public function __construct(){
		global $wgDBtype;
		if($wgDBtype == "mysql"){
			global $smwgHaloIP;
			require_once( "$smwgHaloIP/includes/QueryResultsCache/SMW_QRC_SQLStore.php" );
			$this->mDB = new SMWQRCSQLStore();
		} else {
			die('The Query Results Cache does not support the '.$wgDBtype.' database type.');
		}
			
		return $this;
	}

	public function getDB(){
		return $this->mDB;
	}
}