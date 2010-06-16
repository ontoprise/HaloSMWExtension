<?php

global $smwgHaloIP;
require_once( "$smwgHaloIP/includes/QueryResultsCache/SMW_QRC_QueryResultsCache.php" );


/**
 * HaloStore which is compatible to SMWSQLStore2
 *
 */
class SMWHaloStore2 extends SMWSQLStore2 {
	
	public function getQueryResult(SMWQuery $query){
		$qrc = new SMWQRCQueryResultsCache();
		return $qrc->getQueryResult($query);		
	}
	
	public function doGetQueryResult(SMWQuery $query){
		return parent::getQueryResult($query);
	}
   
}
