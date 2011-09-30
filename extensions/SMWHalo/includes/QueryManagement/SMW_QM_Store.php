<?php

/**
 * QMStore implementation
 * 
 * @author kuehn
 *
 */
class SMWQMStore extends SMWStoreAdapter {
		
	
	function __construct($basestore) {
		$this->smwstore = $basestore;
	}
	
	function getStore() {
		return $this->smwstore;
	}

	
	/*
	 * Stores query metadata for QueryManagement
	 */
	public function getQueryResult(SMWQuery $query){
		SMWQMQueryManagementHandler::getInstance()->storeQueryMetadata($query);
		return $this->smwstore->getQueryResult($query);
	}
	
}



