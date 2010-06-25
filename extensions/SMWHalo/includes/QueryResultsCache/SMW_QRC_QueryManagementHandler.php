<?php

define('QRC_UQC_LABEL','QRCUsesQueryCall');
define('QRC_HQID_LABEL','QRCHasQueryId');
define('QRC_HQS_LABEL','QRCHasQueryString');
define('QRC_HQL_LABEL','QRCHasQueryOffset');
define('QRC_HQO_LABEL','QRCHasQueryLimit');

class SMWQRCQueryManagementHandler {
	
	private static $instance;
	
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	public static function initProperties(){
		SMWPropertyValue::registerProperty('_QRC_UQC', '_wpg', QRC_UQC_LABEL , false);
		SMWPropertyValue::registerProperty('_QRC_HQID', '_txt', QRC_HQID_LABEL , false);
		SMWPropertyValue::registerProperty('_QRC_HQS', '_txt', QRC_HQS_LABEL , false);
		SMWPropertyValue::registerProperty('_QRC_HQL', '_num', QRC_HQL_LABEL , false);
		SMWPropertyValue::registerProperty('_QRC_HQO', '_num', QRC_HQO_LABEL , false);
		
		$tmp = QRC_UQC_LABEL;
		//$tmp();
		
		return true;
	}
	
	public static function initQRCDataTypes(){
		global $wgAutoloadClasses, $smwgHaloIP, $smwgHaloContLang;
		$wgAutoloadClasses['SMWQueryCallMetadataValue'] = 
			"$smwgHaloIP/includes/QueryResultsCache/SMW_QRC_DV_QueryCallMetadata.php";
		SMWDataValueFactory::registerDatatype('__qcm', 'SMWQueryCallMetadataValue');	
	
		return true;
	}
	
	public function storeQueryMetadata($title, $query){
		global $wgParser;
			
		// initialize a new semdata object and append it to parser output if this was not yet done.
		// the semdata object will then be stored to the db by smw at the end of the parse process
		if (!isset($wgParser->getOutput()->mSMWData)) {
			$wgParser->getOutput()->mSMWData = new SMWSemanticData(SMWWikiPageValue::makePageFromTitle($title));
		}
		$semanticData = $wgParser->getOutput()->mSMWData;
			
		$propertyValue = SMWPropertyValue::makeUserProperty(QRC_UQC_LABEL);
		
		$dataValue = SMWDataValueFactory::newTypeIDValue('__qcm');
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
		return md5($query->getQueryString().' limit='.$query->getLimit().' offset='.$query->getOffset());
	}
	
	public function getSearchQueryUsagesQueryString($queryId){
		return '[['.QRC_UQC_LABEL.'.'.QRC_HQID_LABEL.'::'.$queryId.']]';	
	}
	
	public function getQueryCallMetadata($semanticData){
		$property = SMWPropertyValue::makeUserProperty(QRC_UQC_LABEL);
		
		$propVal = $semanticData->getPropertyValues($property);
			$propVal = $propVal[0][0];
			
		$metadata = array('queryString' => '', 'limit' => '', 'offset' => '');
		foreach($propVal as $pV){
			if($pV[0] == QRC_HQS_LABEL) $metadata['queryString'] = $pV[1];
			if($pV[0] == QRC_HQL_LABEL) $metadata['limit'] = $pV[1];
			if($pV[0] == QRC_UQO_LABEL) $metadata['offset'] = $pV[1];
		}
		
		return $metadata;
	}
}