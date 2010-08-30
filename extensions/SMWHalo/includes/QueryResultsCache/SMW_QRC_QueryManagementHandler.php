<?php

/**
 *
  * @ingroup SMWHaloQueryResultsCache
 *
 * @author Ingo Steinbauer
 *
 */

define('QRC_UQC_LABEL','QRCUsesQueryCall');
define('QRC_HQID_LABEL','QRCHasQueryId');
define('QRC_HQS_LABEL','QRCHasQueryString');
define('QRC_HQL_LABEL','QRCHasQueryLimit');
define('QRC_HQO_LABEL','QRCHasQueryOffset');

define('QRC_HEPP_LABEL','QRCHasExtraPropertyPrintouts');
define('QRC_HECP_LABEL','QRCHasExtraCategoryPrintout');
define('QRC_ISQ_LABEL','QRCIsSPARQLQuery');

define('QRC_DOP_LABEL','QRCDependsOnProperty');
define('QRC_DOC_LABEL','QRCDependsOnCategory');

/*
 * This class is responsible for the Query Results Cache related
 * Query Management metadata
 */
class SMWQRCQueryManagementHandler {
	
	private static $instance;
	
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
	 * Called by the smwInitProperties Hook. Registers some queries
	 */
	public static function initProperties(){
		SMWPropertyValue::registerProperty('___QRC_UQC', '_wpg', QRC_UQC_LABEL , false);
		SMWPropertyValue::registerProperty('___QRC_HQID', '_str', QRC_HQID_LABEL , false);
		SMWPropertyValue::registerProperty('___QRC_HQS', '_str', QRC_HQS_LABEL , false);
		SMWPropertyValue::registerProperty('___QRC_HQL', '_num', QRC_HQL_LABEL , false);
		SMWPropertyValue::registerProperty('___QRC_HQO', '_num', QRC_HQO_LABEL , false);
		
		SMWPropertyValue::registerProperty('___QRC_DOP', '_str', QRC_DOP_LABEL , false);
		SMWPropertyValue::registerProperty('___QRC_DOC', '_str', QRC_DOC_LABEL , false);
		
		SMWPropertyValue::registerProperty('___QRC_HEPP', '_str', QRC_HEPP_LABEL , false);
		SMWPropertyValue::registerProperty('___QRC_HECP', '_boo', QRC_HECP_LABEL , false);
		SMWPropertyValue::registerProperty('___QRC_ISQ', '_boo', QRC_ISQ_LABEL , false);
		
		return true;
	}
	
	/*
	 * Called by the 'smwInitDatatypes' hook. Initializes the Query Management Data Type.
	 */
	public static function initQRCDataTypes(){
		global $wgAutoloadClasses, $smwgHaloIP, $smwgHaloContLang;
		$wgAutoloadClasses['SMWQueryCallMetadataValue'] = 
			"$smwgHaloIP/includes/QueryResultsCache/SMW_QRC_DV_QueryCallMetadata.php";
		SMWDataValueFactory::registerDatatype('_qcm', 'SMWQueryCallMetadataValue');	
	
		return true;
	}
	
	/*
	 * This method is called by SMWQRCQueryResultsCache if a query is executed.
	 * It appends query related metadata to the article which contains the query.
	 */
	public function storeQueryMetadata($title, $query){
		global $wgParser;
			
		// initialize a new semdata object and append it to parser output if this was not yet done.
		// the semdata object will then be stored to the db by smw at the end of the parse process
		if (!isset($wgParser->getOutput()->mSMWData)) {
			$wgParser->getOutput()->mSMWData = new SMWSemanticData(SMWWikiPageValue::makePageFromTitle($title));
		}
		$semanticData = $wgParser->getOutput()->mSMWData;

		$propertyValue = SMWPropertyValue::makeProperty('___QRC_UQC');
		$dataValue = SMWDataValueFactory::newTypeIDValue('_qcm');
		$dataValue->setQueryId($this->getQueryId($query));
		$dataValue->setQueryString($query->getQueryString());
		if($query->getLimit()) $dataValue->setQueryLimit($query->getLimit());
		if($query->getOffset()) $dataValue->setQueryOffset($query->getOffset());
		
		if ($query instanceof SMWSPARQLQuery){
			$prProperties = $this->getPrintRequestsProperties($query->getExtraPrintouts());
			$dataValue->setExtraPropertyPrintouts(implode(';', array_keys($prProperties)));
			
			$dataValue->setExtraCategoryPrintouts($this->isCategoryRequestedInPrintRequests($query->getExtraPrintouts()));
			$dataValue->setIsSPQRQLQuery(true);
		} else {
			$dataValue->setIsSPQRQLQuery(false);
		}
			
		$properties = array();
		$categories = array();
		
		if ($query instanceof SMWSPARQLQuery){
			list($properties, $categories) = $this->getSPARQLQueryParts($query);
		} else  if($query instanceof SMWQuery){
			list($properties, $categories) = $this->getQueryParts($query->getDescription());
		}
		
		foreach($properties as $p => $dontCare){
			$dataValue->addPropertyDependency($p);
		}
		
		foreach($categories as $c => $dontCare){
			$dataValue->addCategoryDependency($c);
		}
		
		$semanticData->addPropertyObjectValue($propertyValue, $dataValue);
		$wgParser->getOutput()->mSMWData = $semanticData;
	}
	
	/*
	 * computes the queries hash value
	 */
	public function getQueryId($query){
		$rawId = $query->getQueryString().' limit='.$query->getLimit().' offset='.$query->getOffset();
		
		//the id of a SPARQL query also depends on the printrequests,
		//because not only the subjects but the complete query result
		//is cached
		if($query instanceof SMWSPARQLQuery){
			$prProperties = $this->getPrintRequestsProperties($query->getExtraPrintouts());
			$rawId .= ';'.implode(';', array_keys($prProperties));
			if($this->isCategoryRequestedInPrintRequests($query->getExtraPrintouts())){
				$rawId .= ';CategoryRequested';
			}
		}
		
		$id = md5($rawId);
		
		return $id;
	}
	
	/*
	 * Returns Query Call Metadata
	 */
	public function getQueryCallMetadata($semanticData, $queryId){
		$property = SMWPropertyValue::makeProperty('___QRC_UQC');
		$propVals = $semanticData->getPropertyValues($property);
		
		$metadata = array('queryString' => '', 'limit' => '', 'offset' => '');
		foreach($propVals as $pVs){
			$pVs = $pVs->getDBKeys();
			$pVs = $pVs[0];
			
			$break = false;
			foreach($pVs as $pV){
				if($pV[0] == QRC_HQID_LABEL && $pV[1][0] == $queryId) $break = true;
				if($pV[0] == QRC_HQID_LABEL) $metadata['id'] = $pV[1][0];
				if($pV[0] == QRC_HQS_LABEL) $metadata['queryString'] = $pV[1][0];
				if($pV[0] == QRC_HQL_LABEL) $metadata['limit'] = $pV[1][0];
				if($pV[0] == QRC_HQO_LABEL) $metadata['offset'] = $pV[1][0];
				
				if($pV[0] == QRC_HEPP_LABEL) $metadata['extraPropertyPrintouts'] = $pV[1][0];
				if($pV[0] == QRC_HECP_LABEL) $metadata['extraCategoryPrintouts'] = $pV[1][0];
				if($pV[0] == QRC_ISQ_LABEL) $metadata['isSPARQLQuery'] = $pV[1][0];
			}
			
			if($break) break;
			$metadata = array('queryString' => '', 'limit' => '', 'offset' => '');
		}
		return $metadata;
	}
	
	/*
	 * Get query string for searching for query metadata
	 */
	public function getSearchMetadataQueryString($queryId){
		return '[['.QRC_UQC_LABEL.'.'.QRC_HQID_LABEL."::".$queryId."]]";
	}
	
	/*
	 * Get query string for searching for articles containing queries
	 * which must be invalidated becaue of property modifications
	 */
	public function getSearchQueriesAffectedByDataModification($propertyNames, $categoryNames){
		$queryString = '[['.QRC_UQC_LABEL.'::<q>';
		
		if(count($propertyNames) > 0){
			$queryString .= '[['.QRC_DOP_LABEL.'::';
			$queryString .= implode(']] OR [['.QRC_DOP_LABEL.'::', $propertyNames);
			$queryString .= ']]';
		}
		
		if(count($categoryNames) > 0){
			if(count($propertyNames) > 0) $queryString .= ' OR ';
			$queryString .= '[['.QRC_DOC_LABEL.'::';
			$queryString .= implode(']] OR [['.QRC_DOC_LABEL.'::', $categoryNames);
			$queryString .= ']]';
		}
		
		$queryString .= '</q>]]';
		
		return $queryString;
	}
	
	/*
	 * Returns all properties and categories, which are used in a query
	 */
	private function getQueryParts($description, $properties = array(), $categories = array()){
		if($this->hasSubdescription($description)){
			foreach($description->getDescriptions() as $subDescription){
				list($properties, $categories) = $this->getQueryParts($subDescription, $properties, $categories);
			}
		}
		
		//for properties with subqueries and query chains
		if($description instanceof SMWSomeProperty && $description->getDepth() > 1){
			list($properties, $categories) = $this->getQueryParts($description->getDescription(), $properties, $categories);
		}
		
		if($description instanceof SMWSomeProperty){
			$properties[$description->getProperty()->getText()] = null;
		} else if ($description instanceof SMWClassDescription){
			foreach($description->getCategories() as $title)
			$categories[$title->getText()] = null;
		}
		
		return array($properties, $categories);
	}
	
	/*
	 * determines whether a description has a subdescription
	 */
	private function hasSubdescription($description){
		if($description instanceof SMWDisjunction ||	$description instanceof SMWConjunction){
			return true;
		}
		return false;
	}
	
	
	public function getIdsOfQueriesUsingProperty($semanticData, $properties){
		$queryIds = array();
		
		$property = SMWPropertyValue::makeProperty('___QRC_UQC');
		$propVals = $semanticData->getPropertyValues($property);
			
		foreach($propVals as $pVs){
			$pVs = $pVs->getDBKeys();
			$pVs = $pVs[0];
			
			$break = false;
			$queryId = '';
			foreach($pVs as $pV){
				if($pV[0] == QRC_DOP_LABEL && array_key_exists($pV[1][0], $properties)) $break = true;
				if($pV[0] == QRC_HQID_LABEL) $queryId = $pV[1][0];
				
				if($break && strlen($queryId) > 0){
					$queryIds[$queryId] = true;
					break;
				}
			}
		}
		return $queryIds;	
	}
	
	public function getIdsOfQueriesUsingCategory($semanticData, $categories){
		$queryIds = array();
		
		$property = SMWPropertyValue::makeProperty('___QRC_UQC');
		$propVals = $semanticData->getPropertyValues($property);
			
		foreach($propVals as $pVs){
			$pVs = $pVs->getDBKeys();
			$pVs = $pVs[0];
			
			$break = false;
			$queryId = '';
			foreach($pVs as $pV){
				if($pV[0] == QRC_DOC_LABEL && array_key_exists($pV[1][0], $categories)) $break = true;
				if($pV[0] == QRC_HQID_LABEL) $queryId = $pV[1][0];
				
				if($break && strlen($queryId) > 0){
					$queryIds[$queryId] = true;
					break;
				}
			}
		}
		return $queryIds;	
	}
	
	private function getSPARQLQueryParts($query){
		//todo:deal with categories
		
		$properties = array();
		$categories = array();
		
		$description = $query->getDescription();
		$extraPrintOuts = $query->getExtraPrintouts();
		
		if (!($description instanceof SMWSPARQLDescription)) {
			//echo('<pre>'.print_r($description, true).'</pre>');
			//echo('<pre>'.print_r($extraPrintOuts, true).'</pre>');
			
			list($properties, $categories) = $this->getQueryParts($description);
		} else {
			if(!class_exists('ARC2')){
				global $smwgHaloIP;
				require_once( "$smwgHaloIP/libs/arc/ARC2.php" );
			}
			
			$prefixes = str_replace(':<', ': <', TSNamespaces::getAllPrefixes());
			$queryString = $prefixes . $query->getQueryString();
			
			$parser = ARC2::getSPARQLParser();
			$parser->parse($queryString);
			$queryInfo = $parser->getQueryInfos();
			
			if (array_key_exists('query', $queryInfo)) {
				if ($queryInfo['query']['type'] == 'select') {
					list($properties, $categories) = $this->getPropertiesInSPARQLPattern(
						$queryInfo['query']['pattern'], $queryInfo['prefixes']['prop:'], $queryInfo['prefixes']['rdf:'].'type', $queryInfo['prefixes']['cat:']);
				}
			}
		}
		
		//deal with extra printouts
		$properties = array_merge($properties, $this->getPrintRequestsProperties($extraPrintOuts));
		
		return array($properties, $categories);
	}
	
	private function getPropertiesInSPARQLPattern($pattern, $propertyNS, $categoryPred, $categoryNS, 
			$properties = array(), $categories = array()){
		switch ($pattern['type']) {
			case 'group':
			case 'union':
			case 'optional':
				foreach($pattern['patterns'] as $p) {
					list($properties, $categories) = 
						$this->getPropertiesInSPARQLPattern($p, $propertyNS, $categoryPred, $categoryNS, $properties, $categories);
				}
				break;
			case 'triples':
				foreach ($pattern['patterns'] as $triple) {
					if ($triple['p_type'] == 'uri') {
						if(strpos($triple['p'], $propertyNS) === 0){
							$properties[substr($triple['p'], strlen($propertyNS))] = true;
						} else if (strpos($triple['p'], $categoryPred) === 0){
							if ($triple['o_type'] == 'uri') {
								if(strpos($triple['o'], $categoryNS) === 0){
									$categories[substr($triple['o'], strlen($categoryNS))] = true;
								}
							}
						}
					} 
				}
				break;
			default:
				break;
		}
		
		return array($properties, $categories);
	}
	
	private function getPrintRequestsProperties($printRequests){
		$properties = array();
		
		foreach ($printRequests as $printRequest) {
			if ($printRequest->getMode() == SMWPrintRequest::PRINT_THIS) {
				$propertyName = $printRequest->getData()->getText();
				$properties[$propertyName] = true;  
			} else if ($printRequest->getMode() == SMWPrintRequest::PRINT_PROP){
				$propertyName = $printRequest->getData()->getWikiPageValue()->getText();
				$properties[$propertyName] = true;
			}
		}
		
		return $properties;	
	}
	
	private function isCategoryRequestedInPrintRequests($printRequests){
		foreach ($printRequests as $printRequest) {
			if ($printRequest->getMode() == SMWPrintRequest::PRINT_CATS) {
				return true;  
			}
		}
		return false;
	}
}