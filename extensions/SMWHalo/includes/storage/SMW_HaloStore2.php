<?php

/**
 * HaloStore which is compatible to SMWSQLStore2
 *
 */
class SMWHaloStore2 extends SMWSQLStore2 {
	
	/*
	 * This method is overwritten in order to hook in
	 * the Query Results Cache
	 */
	public function getQueryResult(SMWQuery $query){
		global $smwgQRCEnabled;
		if($smwgQRCEnabled){	
			$qrc = new SMWQRCQueryResultsCache();
			return $qrc->getQueryResult($query);
		} else {
			return $this->doGetQueryResult($query);
		}		
	}
	
	public function doGetQueryResult(SMWQuery $query){
		return parent::getQueryResult($query);
	}

}
