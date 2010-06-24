<?php 

global $smwgHaloIP;
require_once( "$smwgHaloIP/includes/QueryResultsCache/SMW_QRC_Store.php" );
require_once( "$smwgHaloIP/includes/QueryResultsCache/SMW_QRC_QueryManagementHandler.php" );


class SMWQRCQueryResultsCache {
	
	/*
	 * Answers ASK and SPARQL queries. Query result is taken from
	 * the cache if an appropriate cache entry exists. The query result
	 * is retrieved from the appropriate endpoint and sored into
	 * the cache otherwise.
	 */
	public function getQueryResult(SMWQuery $query, $force=false, $cacheThis=true){
		global $secondRound;
		
		if(!$secondRound){
			$secondRound = true;
			//$this->updateQueryResult(SMWQRCQueryManagementHandler::getInstance()->getQueryId($query));
		} else {
			//echo("<pre>".print_r($query, true)."</pre>");
		}
		
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
		
		// execute the query if no valid cache entry is available, if force was 
		// set to true (e.g. by the update process) or if the query is executed because 
		// of an edit or a purge action
		if($force || !$this->isReadAccess() || !$this->hasValidCacheEntry($query)){
			//delegate query processing to the responsible store
			if ($query instanceof SMWSPARQLQuery) {
				$store = $defaultStore;
			} else {
				global $smwgBaseStore;
				$store = new $smwgBaseStore();
			}
			
			$queryResult = $store->doGetQueryResult($query);
			
			if($cacheThis){
				//add the serialized query result to the database
				$qrcStore = SMWQRCStore::getInstance()->getDB();
				$qrcStore->updateQueryResult(SMWQRCQueryManagementHandler::getInstance()->getQueryId($query), serialize($queryResult));
			}
		} else {
			$qrcStore = SMWQRCStore::getInstance()->getDB();
			$queryResult = unserialize($qrcStore->getQueryResult(SMWQRCQueryManagementHandler::getInstance()->getQueryId($query)));
		}
		
		error();
		
		return $queryResult;
	}
	
	/*
	 * 
	 */
	private function hasValidCacheEntry(SMWQuery $query){
		if (!$query instanceof SMWSPARQLQuery) {
			//$usedProperties = $this->getQueryParts($query->getDescription());
			//echo("<pre>".print_r($usedProperties, true)."</pre>");
			
			$qrcStore = SMWQRCStore::getInstance()->getDB();
			if($qrcStore->getQueryResult(SMWQRCQueryManagementHandler::getInstance()->getQueryId($query))){
				return true;
			} else {
				return false;
			}
		}
		return false;
	}
	
	private function getQueryParts($description, $properties = array(), $categories = array()){
		if($this->hasSubdescription($description)){
			foreach($description->getDescriptions() as $subDescription){
				list($properties, $categories) = $this->getQueryParts($subDescription, $properties, $categories);
			}
		}
		
		if($description instanceof SMWSomeProperty){
			$properties[$description->getProperty()->getText()] = null;
		} else if ($description instanceof SMWClassDescription){
			foreach($description->getCategories() as $title)
			$categories[$title->getText()] = null;
		}
		
		return array($properties, $categories);
	}
	
	private function hasSubdescription($description){
		if($description instanceof SMWDisjunction ||	$description instanceof SMWConjunction){
			return true;
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
		SMWQueryProcessor::processFunctionParams(array("[[UsesQueryCall.HasQueryId::".$queryId."]]", "limit=1") 
			,$querystring,$params,$printouts);
		$query = 
			SMWQueryProcessor::createQuery($querystring,$params);
		$queryResults = $this->getQueryResult($query, true, false)->getResults();
		
		if(count($queryResults) > 0){ //this query is still in use
			global $smwgDefaultStore;
			$defaultStore = new $smwgDefaultStore();
			
			$title = $queryResults[0]->getTitle();
			
			$semanticData = $defaultStore->getsemanticData($title); 
		
			$property = SMWPropertyValue::makeUserProperty('UsesQueryCall'); 
			
			$propVal = $semanticData->getPropertyValues($property);
			$propVal = $propVal[0][0];
			
			//todo: deal with the limit parameter and others
			
			$queryString = '';
			foreach($propVal as $pV){
				if($pV[0] == 'HasQueryString') $queryString = $pV[1];
			}
			
			
			//echo("<pre>".print_r($queryString, true)."</pre>");
			
			SMWQueryProcessor::processFunctionParams(array($queryString) 
				,$querystring,$params,$printouts);
			$query = 
				SMWQueryProcessor::createQuery($querystring,$params);
			$this->getQueryResult($query, true);
			
			
		
		} else {
			$qrcStore = SMWQRCStore::getInstance()->getDB();
			$qrcStore->deleleteQueryResult($queryId);		
		}
		
		return true;
	}
	
}

?>