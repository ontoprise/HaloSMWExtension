<?php 

global $smwgIP, $smwgHaloIP;
require_once( "$smwgHaloIP/includes/QueryResultsCache/SMW_QRC_DV_QueryCallMetadata.php" );

SMWDataValueFactory::registerDatatype('_qcm', 'SMWQueryCallMetadataValue');

class SMWQRCQueryResultsCache {
	
	/*
	 * Answers ASK and SPARQL queries. Query result is taken from
	 * the cache if an appropriate cache entry exists. The query result
	 * is retrieved from the appropriate endpoint and sored into
	 * the cache otherwise.
	 */
	public function getQueryResult(SMWQuery $query, $force=false){
		global $wgParser;
		
		$title = false;
		if($wgParser && $wgParser->getTitle()){
			$title = $wgParser->getTitle();
		}
		
		//always refresh if this is method is triggered by a refresh or a commit (edit) action
		$noReadAccess = true;
		if(array_key_exists('purge', $_GET) || array_key_exists('submit', $_GET)){
			$noReadAccess = true;	
		}
		
		global $smwgDefaultStore;
		$defaultStore = new $smwgDefaultStore();
		
		if($title !== false){
			if (!isset($wgParser->getOutput()->mSMWData)) {
				$wgParser->getOutput()->mSMWData = new SMWSemanticData(SMWWikiPageValue::makePageFromTitle($title));
			}
			$semanticData = $wgParser->getOutput()->mSMWData;
			
			$propertyValue = SMWPropertyValue::makeUserProperty('UsesQueryCall');
			$dataValue = SMWDataValueFactory::newTypeIDValue('_qcm');
			
			$queryId = md5($query->getQueryString());
			$dataValue->setQueryId($queryId);
			
			$dataValue->setQueryString($query->getQueryString());
			
			//$semanticData->addPropertyObjectValue($propertyValue, $dataValue);
			$semanticData->addPropertyValue('UsesQueryCall', $dataValue);
			
			$wgParser->getOutput()->mSMWData = $semanticData;
		}
		
		//todo: who sets force = true?
		if(!$this->hasValidCacheEntry($query) || $force || $noReadAccess){
			if ($query instanceof SMWSPARQLQuery) {
				$store = $defaultStore;
			} else {
				global $smwgBaseStore;
				$store = new $smwgBaseStore();
			}
			$queryResult = $store->doGetQueryResult($query);
			//todo: Do some chache updates
			
			$queryResult = serialize($queryResult);
			$queryResult = unserialize($queryResult);
			
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
		if (!$query instanceof SMWSPARQLQuery) {
			$usedProperties = $this->getQueryParts($query->getDescription());
			
			echo("<pre>".print_r($usedProperties, true)."</pre>");
		
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
	
} 

?>