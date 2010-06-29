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
		$semanticData->addPropertyObjectValue($propertyValue, $dataValue);
		
		$wgParser->getOutput()->mSMWData = $semanticData;
	}
	
	/*
	 * computes the queries hash value
	 */
	public function getQueryId($query){
		$rawId = $query->getQueryString().' limit='.$query->getLimit().' offset='.$query->getOffset();
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
}