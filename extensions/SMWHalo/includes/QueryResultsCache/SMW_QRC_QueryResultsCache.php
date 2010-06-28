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
		
		// execute the query if no valid cache entry is available, if force was 
		// set to true (e.g. by the update process) or if the query is executed because 
		// of an edit or a purge action
		if($force || !$this->isReadAccess() || !$this->hasValidCacheEntry($query)){
			$queryResult = $store->doGetQueryResult($query);
			
			if($cacheThis){
				//add the serialized query result to the database
				$qrcStore = SMWQRCStore::getInstance()->getDB();
				$qrcStore->updateQueryResult(SMWQRCQueryManagementHandler::getInstance()->getQueryId($query), serialize($queryResult));
			}
		} else {
			$qrcStore = SMWQRCStore::getInstance()->getDB();
			$queryResult = unserialize($qrcStore->getQueryResult(SMWQRCQueryManagementHandler::getInstance()->getQueryId($query)));
		
			$query->addErrors($queryResult->getErrors());
			$queryResult = new SMWQueryResult($query->getDescription()->getPrintRequests(), $query, $queryResult->getResults(), $store, $queryResult->hasFurtherResults());
		}
		return $queryResult;
	}
	
	/*
	 * Checks whether a valid cache entry exists for this query.
	 */
	private function hasValidCacheEntry(SMWQuery $query){
		//$usedProperties = $this->getQueryParts($query->getDescription());
		//echo("<pre>".print_r($usedProperties, true)."</pre>");
			
		$qrcStore = SMWQRCStore::getInstance()->getDB();
		if($qrcStore->getQueryResult(SMWQRCQueryManagementHandler::getInstance()->getQueryId($query))){
			return true;
		} else {
			return false;
		}
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
		global $smwgBaseStore;
		$store = new $smwgBaseStore();
		
		$property = SMWPropertyValue::makeProperty('___QRC_UQCWID');
		$dataValue = SMWDataValueFactory::newTypeIDValue('_txt', 'sdfs');
		$queryResults = $store->getPropertySubjects($property, $dataValue);//, '239ddfde7ff63e389d7c0b0c1ceae174');
		
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
		} else {
			$qrcStore = SMWQRCStore::getInstance()->getDB();
			$qrcStore->deleteQueryResult($queryId);		
		}
		
		return true;
	}
	
}

?>