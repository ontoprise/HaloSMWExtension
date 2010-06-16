<?php 


class SMWQRCQueryResultsCache {
	
	/*
	 * Answers ASK and SPARQL queries. Query result is taken from
	 * the cache if an appropriate cache entry exists. The query result
	 * is retrieved from the appropriate endpoint and sored into
	 * the cache otherwise.
	 */
	public function getQueryResult(SMWQuery $query, $force=false){
		//todo: who sets force = true?
		if(!$this->hasValidCacheEntry($query) || $force){
			if ($query instanceof SMWSPARQLQuery) {
				global $smwgDefaultStore;
				$store = new $smwgDefaultStore();
			} else {
				global $smwgBaseStore;
				$store = new $smwgBaseStore();
			}
			$queryResult = $store->doGetQueryResult($query);
			//todo: Do some chache updates
			return $queryResult;
		} else {
			//todo: get result from cache
			//todo: do some cache updates
			//todo: return result
		}
	}
	
	/*
	 * 
	 */
	private function hasValidCacheEntry(SMWQuery $query){
		//todo: implement this
		return false;
	}
	
} 

?>