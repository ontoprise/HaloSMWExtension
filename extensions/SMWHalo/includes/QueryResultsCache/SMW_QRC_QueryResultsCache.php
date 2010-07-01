<?php 

/**
 * This group contains all parts of the Query Results Cache.
 * @defgroup SMWHaloQueryResultsCache
 * @ingroup SMWHalo
 */

/**
 *
  * @ingroup SMWHaloQueryResultsCache
 *
 * @author Ingo Steinbauer
 *
 */

global $smwgHaloIP;
require_once( "$smwgHaloIP/includes/QueryResultsCache/SMW_QRC_Store.php" );
require_once( "$smwgHaloIP/includes/QueryResultsCache/SMW_QRC_QueryManagementHandler.php" );
require_once( "$smwgHaloIP/includes/QueryResultsCache/SMW_QRC_Settings.php" );
require_once( "$smwgHaloIP/includes/QueryResultsCache/SMW_QRC_PriorityCalculator.php" );


/*
 * Main class of the Query Results Cache. 
 */
class SMWQRCQueryResultsCache {
	
	/*
	 * Answers ASK and SPARQL queries. Query result is taken from
	 * the cache if an appropriate cache entry exists. The query result
	 * is retrieved from the appropriate endpoint and stored into
	 * the cache otherwise.
	 */
	public function getQueryResult(SMWQuery $query, $force=false, $cacheThis=true){
		//get title of article in which query was executed 
		//or set title to false if query was executed somehow else 
		global $wgParser;
		$title = false;
		if($wgParser && $wgParser->getTitle()){
			$title = $wgParser->getTitle();
		}
		
		global $smwgDefaultStore;
		$defaultStore = new $smwgDefaultStore();
		
		// update the semdata object for this title, respectively 
		// the query management annotations
		if($title !== false && $cacheThis){
			SMWQRCQueryManagementHandler::getInstance()->storeQueryMetadata($title, $query);
		}
		
		//delegate query processing to the responsible store
		if ($query instanceof SMWSPARQLQuery) {
			$store = $defaultStore;
		} else {
			global $smwgBaseStore;
			$store = new $smwgBaseStore();
		}
		
		$queryData = $this->getQueryData($query);
		
		// execute the query if no valid cache entry is available, if force was 
		// set to true (e.g. by the update process) or if the query is executed because 
		// of an edit or a purge action
		if($force || !$this->isReadAccess() || !$this->hasValidCacheEntry($queryData)){
			$queryResult = $store->doGetQueryResult($query);
			
			if($cacheThis){
				//add the serialized query result to the database
				$qrcStore = SMWQRCStore::getInstance()->getDB();
				
				$queryId = SMWQRCQueryManagementHandler::getInstance()->getQueryId($query);
				$lastUpdate = time();
				$dirty = false;
				
				if($queryData){ //results for this query already have been stored in the cache
					if($force){ //this query result update was not triggered by a Wiki user action
						$accessFrequency = SMWQRCPriorityCalculator::getInstance()
							->computeNewAccessFrequency($queryData['accessFrequency']);
						$invalidationFrequency = SMWQRCPriorityCalculator::getInstance()
							->computeNewInvalidationFrequency($queryData['invalidationFrequency']);
					} else {
						$accessFrequency = $queryData['accessFrequency'] + 1;
						$invalidationFrequency = $queryData['invalidationFrequency'] + 1;
					}
					
					$priority = SMWQRCPriorityCalculator::getInstance()
						->computeQueryUpdatePriority($lastUpdate, $accessFrequency, $invalidationFrequency);
					
					$qrcStore->updateQueryData($queryId, serialize($queryResult), $lastUpdate, 
						$accessFrequency, $invalidationFrequency, $dirty, $priority);
				} else {
					$priority = SMWQRCPriorityCalculator::getInstance()
						->computeQueryUpdatePriority($lastUpdate, 1, 0);
					
					$qrcStore->addQueryData($queryId, serialize($queryResult), $lastUpdate, 
						1, 0, $dirty, $priority);
				}
								
			}
		} else {
			$qrcStore = SMWQRCStore::getInstance()->getDB();
			$queryResult = unserialize($queryData['queryResult']);
			
			//update access frequency and query priority
			$priority = SMWQRCPriorityCalculator::getInstance()
				->computeQueryUpdatePriority($queryData['lastUpdate'], $queryData['accessFrequency'] + 1, $queryData['invalidationFrequency']);
			
			$qrcStore->updateQueryData($queryData['queryId'], $queryData['queryResult'], $queryData['lastUpdate'], 
				$queryData['accessFrequency']+1, $queryData['invalidationFrequency'], $queryData['dirty'], $priority);
			
			if($query instanceof SMWQueryResult){
				$query->addErrors($queryResult->getErrors());
				$queryResult = 
					new SMWQueryResult($query->getDescription()->getPrintRequests(), $query, $queryResult->getResults(), $store, $queryResult->hasFurtherResults());
			}
		}
		return $queryResult;
	}
	
	/*
	 * get query data from the cache
	 */
	private function getQueryData(SMWQuery $query){
		$qrcStore = SMWQRCStore::getInstance()->getDB();
		return $qrcStore->getQueryData(SMWQRCQueryManagementHandler::getInstance()->getQueryId($query));
	}
	
	/*
	 * Checks whether a valid cache entry exists for this query.
	 */
	private function hasValidCacheEntry($queryData){
		if($queryData){
			if(!$queryData['dirty']){
				return true;
			}
		}
		return false;
	}
	
	/*
	 * Check whether this is a read access
	 */
	private function isReadAccess(){
		global $wgRequest;
		$action = $wgRequest->getVal('action');
		$isReadAccess = true;
		if($wgRequest->wasposted() || $action == 'purge' || $action == 'submit'){
			$isReadAccess = false;	
		}
		return $isReadAccess;
	}
	
	/*
	 * Called by ajax api to get all query ids
	 */
	public function getQueryIds($limit, $offset){
		$qrcStore = SMWQRCStore::getInstance()->getDB();
		return $qrcStore->getQueryIds($limit, $offset);	
	}
	
	/*
	 * called by ajax api to update a query result
	 */
	public function updateQueryResult($queryId){
		$queryString = SMWQRCQueryManagementHandler::getInstance()->getSearchMetadataQueryString($queryId);
//		
		SMWQueryProcessor::processFunctionParams(array($queryString) 
			,$queryString,$params,$printouts);
		$query = 
			SMWQueryProcessor::createQuery($queryString,$params);
		$queryResults = $this->getQueryResult($query, true, false)->getResults();
		
		if(count($queryResults) > 0){ //this query is still in use
			global $smwgDefaultStore;
			$defaultStore = new $smwgDefaultStore();
			$title = $queryResults[0]->getTitle();
			$semanticData = $defaultStore->getsemanticData($title);

			$metadata = 
				SMWQRCQueryManagementHandler::getInstance()->getQueryCallMetadata($semanticData, $queryId);
			
			$queryParams = array ($metadata['queryString']);
			if($metadata['limit']) $queryParams[] = 'limit='.$metadata['limit'];
			if($metadata['offset']) $queryParams[] = 'offset='.$metadata['offset'];
			
			SMWQueryProcessor::processFunctionParams($queryParams,$querystring,$params,$printouts);
			$query = 
				SMWQueryProcessor::createQuery($querystring,$params);
			$this->getQueryResult($query, true);
			
			//invalidate parser caches
			foreach($queryResults as $qR){
				$title = $qR->getTitle();
				$title->invalidateCache();
				// wfGetParserCacheStorage()->delete(
				//		ParserCache::singleton()->getKey(Article::newFromID($title->getArticleID()), new ParserOptions()));
			}
		} else {
			$qrcStore = SMWQRCStore::getInstance()->getDB();
			$qrcStore->deleteQueryData($queryId);		
		}
		return true;
	}
	
}