<?php
/*
 * Copyright (C) Vulcan Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program.If not, see <http://www.gnu.org/licenses/>.
 *
 */


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
define('QRC_UAS_LABEL','QRCUsesASKSyntax');

define('QRC_DOP_LABEL','QRCDependsOnProperty');
define('QRC_DOC_LABEL','QRCDependsOnCategory');
define('QRC_DOI_LABEL','QRCDependsOnInstance');

define('QM_UIA_LABEL','QMUsedInArticle');
define('QM_UQP_LABEL','QMUsedQueryPrinter');
define('QM_HQN_LABEL','QMHasQueryName');

global $smwgHaloIP;
require_once( "$smwgHaloIP/includes/QueryManagement/SMW_QM_QueryMetadata.php" ); 
/*
 * This class is responsible for the Query Results Cache related
 * Query Management metadata
 */
class SMWQMQueryManagementHandler {
	
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
		global $wgTitle, $wgLang;
		
		if($wgTitle instanceof Title
				&& $wgTitle->getText() == 'Browse' && $wgTitle->getNamespace() == NS_SPECIAL){
			SMWDIProperty::registerProperty('___QRC_UQC', '_qcm', false, false);
		} else {
			SMWDIProperty::registerProperty('___QRC_UQC', '_wpg', QRC_UQC_LABEL , false);
		}
		SMWPropertyValue::registerProperty('___QRC_HQID', '_str', QRC_HQID_LABEL , false);
		SMWPropertyValue::registerProperty('___QRC_HQS', '_str', QRC_HQS_LABEL , false);
		SMWPropertyValue::registerProperty('___QRC_HQL', '_num', QRC_HQL_LABEL , false);
		SMWPropertyValue::registerProperty('___QRC_HQO', '_num', QRC_HQO_LABEL , false);
		
		SMWPropertyValue::registerProperty('___QRC_DOP', '_str', QRC_DOP_LABEL , false);
		SMWPropertyValue::registerProperty('___QRC_DOC', '_str', QRC_DOC_LABEL , false);
		SMWPropertyValue::registerProperty('___QRC_DOI', '_str', QRC_DOI_LABEL , false);
		
		SMWPropertyValue::registerProperty('___QRC_HEPP', '_str', QRC_HEPP_LABEL , false);
		SMWPropertyValue::registerProperty('___QRC_HECP', '_boo', QRC_HECP_LABEL , false);
		SMWPropertyValue::registerProperty('___QRC_ISQ', '_boo', QRC_ISQ_LABEL , false);
		SMWPropertyValue::registerProperty('___QRC_UAS', '_boo', QRC_UAS_LABEL , false);
		
		SMWPropertyValue::registerProperty('___QM_UIA', '_str', QM_UIA_LABEL , false);
		SMWPropertyValue::registerProperty('___QM_UQP', '_str', QM_UQP_LABEL , false);
		SMWPropertyValue::registerProperty('___QM_HQN', '_str', QM_HQN_LABEL , false);
		
		return true;
	}
	
	/*
	 * Called by the 'smwInitDatatypes' hook. Initializes the Query Management Data Type.
	 */
	public static function initQRCDataTypes(){
		global $smwgHaloIP, $smwgHaloContLang;
		SMWDataValueFactory::registerDatatype('_qcm', 'SMWQueryCallMetadataValue',
			SMWDataItem::TYPE_CONTAINER);	
	
		return true;
	}
	
	/*
	 * This method is called by SMWQRCQueryResultsCache if a query is executed.
	 * It appends query related metadata to the article which contains the query.
	 */
	public function storeQueryMetadata($query){
		
		if (!isset($query->params) || !is_array($query->params)) {
			// No parameters set 
			// => set an empty array to avoid errors due to E_STRICT
			$query->params = array();
		}
		
		//check if query should be stored
		if(array_key_exists('noquerymanagement', $query->params) && $query->params['noquerymanagement'] == 'true'){
			return;
		}
		
		global $wgParser;

		//get title object and skip processing if query does not stem from an article
		if($wgParser && $wgParser->getTitle()){
			$title = $wgParser->getTitle();
			if($title->getNamespace() == NS_SPECIAL){
				return true;
			}
		} else {
			return true;
		}
		
		// initialize a new semdata object and append it to parser output if this was not yet done.
		// the semdata object will then be stored to the db by smw at the end of the parse process
		if (!isset($wgParser->getOutput()->mSMWData)) {
			$wgParser->getOutput()->mSMWData = new SMWSemanticData(SMWWikiPageValue::makePageFromTitle($title)->getDataItem());
		}
		$semanticData = $wgParser->getOutput()->mSMWData;
		
		$propertyValue = SMWPropertyValue::makeProperty('___QRC_UQC');
		$dataValue = SMWDataValueFactory::newTypeIDValue('_qcm');
		$dataValue->setQueryId($this->getQueryId($query));
		$dataValue->setQueryString($query->getQueryString());
		if($query->getLimit()) $dataValue->setQueryLimit($query->getLimit());
		if($query->getOffset()) $dataValue->setQueryOffset($query->getOffset());
		
		$prProperties = $this->getPrintRequestsProperties($query->getExtraPrintouts());
		foreach($prProperties as $p => $dontCare){
			$dataValue->addExtraPropertyPrintouts($p);
		}
		
		$dataValue->setExtraCategoryPrintouts($this->isCategoryRequestedInPrintRequests($query->getExtraPrintouts()));
		
		if ($query instanceof SMWSPARQLQuery){
			$dataValue->setIsSPQRQLQuery('true');
			$tmph = $query->fromASK ? 'true' : 'false';
			$dataValue->setUsesASKSyntax($tmph);
		} else {
			if(array_key_exists('src', $query->params) && $query->params['src'] == 'tsc'){
				$dataValue->setIsSPQRQLQuery('true');
			} else {
				$dataValue->setIsSPQRQLQuery('false');
			}
			$dataValue->setUsesASKSyntax('true');
		}
			
		$properties = array();
		$categories = array();
		$instances = array();
		
		if ($query instanceof SMWSPARQLQuery){
			//todo:deal with instances
			list($properties, $categories) = $this->getSPARQLQueryParts($query);
			$instances = array();
		} else  if($query instanceof SMWQuery){
			list($properties, $categories, $instances) = $this->getQueryParts($query->getDescription());
		}
		
		foreach($properties as $p => $dontCare){
			$dataValue->addPropertyDependency($p);
		}
		
		foreach($categories as $c => $dontCare){
			$dataValue->addCategoryDependency($c);
		}
		
		foreach($instances as $i => $dontCare){
			$dataValue->addInstanceDependency($i);
		}
		
		$dataValue->setUsedInArticle($title->getFullText());
		
		if (isset($query->params) && is_array($query->params)) {
			if(array_key_exists('format', $query->params)){
				$dataValue->setUsedQueryPrinter($query->params['format']);
			}

			if(array_key_exists('queryname', $query->params)){
				$dataValue->setQueryName($query->params['queryname']);
			}
		}
		$semanticData->addPropertyObjectValue($propertyValue->getDataItem(), $dataValue->getDataItem());
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
	 * Returns all properties, categories and instances, which are used in a query
	 */
	private function getQueryParts($description, $properties = array(), $categories = array(), $instances = array()){
		if($this->hasSubdescription($description)){
			foreach($description->getDescriptions() as $subDescription){
				list($properties, $categories, $instances) = $this->getQueryParts($subDescription, $properties, $categories, $instances);
			}
		}
		
		//for properties with subqueries and query chains
		if($description instanceof SMWSomeProperty && $description->getDepth() > 1){
			list($properties, $categories, $instances) = $this->getQueryParts($description->getDescription(), $properties, $categories, $instances);
		}
		
		if($description instanceof SMWSomeProperty){
			$properties[$description->getProperty()->getLabel()] = null;
			if($description->getDescription() instanceof SMWValueDescription){
				$dataItem = $description->getDescription()->getDataItem();
				if($dataItem instanceof SMWDIWikiPage){
					$title = $dataItem->getTitle();
					if(!is_null($title)){
						$instances[$title->getFullText()] = null;
					}
				}
			}
			
		} else if ($description instanceof SMWClassDescription){
			foreach($description->getCategories() as $title)
				$categories[$title->getTitle()->getText()] = null;
		} else if($description instanceof SMWValueDescription){
			$dataItem = $description->getDataItem();
			if($dataItem instanceof SMWDIWikiPage){
				$title = $dataItem->getTitle();
				if(!is_null($title)){
					$instances[$title->getFullText()] = null;
				}
			}
		}
		
		return array($properties, $categories, $instances);
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
				$propertyName = $printRequest->getData();
				if(!is_null($propertyName)){
					$propertyName = $propertyName->getText();
					$properties[$propertyName] = true;
				}  
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
	
	
	public function searchQueries($queryMetadata, $returnInstances = false){
		
		$queryString = $queryMetadata->getMetadaSearchQueryString();
		
		SMWQueryProcessor::processFunctionParams(array($queryString) 
			,$queryString, $params, $printouts);
		$params = SMWQueryProcessor::getProcessedParams($params, $printouts);
		
		$store = smwfGetStore(); 

		$continue = true;
		$offset = 0;
		$queryResults = array();
		global $smwgQMaxInlineLimit;
		while($continue){
			$params['limit'] = $smwgQMaxInlineLimit - 1;
			$params['offset'] = $offset;
			
			$query = 
				SMWQueryProcessor::createQuery($queryString,$params, SMWQueryProcessor::INLINE_QUERY, 'table');
				
			$query->params['noquerymanagement'] = 'true';
			
			$qrT = $store->getQueryResult($query)->getResults();
			
			$queryResults = array_merge($queryResults, $qrT);
			
			if(count($qrT) == $smwgQMaxInlineLimit - 1){
				$offset += $smwgQMaxInlineLimit - 1;
			} else {
				$continue = false;
			}
		}
		
		if($returnInstances){
			return $queryResults; 
		}
		
		$queryMetadataResults = array();
		
		SMWDIProperty::registerProperty('___QRC_UQC', '_qcm', QRC_UQC_LABEL , false);
		
		foreach($queryResults as $queryResult){
			$semanticData = $store->getSemanticData($queryResult);
			
			$property = SMWPropertyValue::makeProperty('___QRC_UQC');
			
			$propVals = $semanticData->getPropertyValues($property->getDataItem());
			
			//echo('<pre>'.print_r($propVals, true).'</pre>');
			
			foreach($propVals as $pVs){

				if(!($pVs instanceof SMWDIContainer)) continue;
				
				$queryMetadataResult = new SMWQMQueryMetadata();
				$queryMetadataResult->fillFromPropertyValues($pVs);
				
				//echo('<pre>'.print_r($queryMetadataResult, true).'</pre>');
				
				if($queryMetadataResult->matchesQueryMetadataPattern($queryMetadata)){
					$queryMetadataResults[] = $queryMetadataResult; 					
				}
			}
		}
		
		//echo('<pre>'.print_r($queryMetadataResults, true).'</pre>');
		
		//set back so that another search will also work
		SMWDIProperty::registerProperty('___QRC_UQC', '_wpg', QRC_UQC_LABEL , false);
		
		return $queryMetadataResults;
	}
	
	
	
	
	
}
