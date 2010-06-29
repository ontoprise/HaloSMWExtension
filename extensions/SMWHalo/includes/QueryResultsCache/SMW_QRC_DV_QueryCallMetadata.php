<?php

/**
 *
  * @ingroup SMWHaloQueryResultsCache
 *
 * @author Ingo Steinbauer
 *
 */

/*
 * A SMWDataValue type for storing query calls related metadata
 */
class SMWQueryCallMetadataValue extends SMWContainerValue {
	
	protected $m_data;
	
	public function __construct($typeid) {
		parent::__construct($typeid);
		$this->m_data = new SMWSemanticData(null);
	}
	
	/*
	 * Sets the id of this query call
	 */
	public function setQueryId($queryId){
		$propertyValue = SMWPropertyValue::makeUserProperty(QRC_HQID_LABEL);
		$dataValue = SMWDataValueFactory::newPropertyObjectValue($propertyValue, $queryId);
		$this->m_data->addPropertyObjectValue($propertyValue, $dataValue);
	}
	
	/*
	 * Sets the query string of this query call
	 */
	public function setQueryString($queryString){
		$propertyValue = SMWPropertyValue::makeUserProperty(QRC_HQS_LABEL);
		$dataValue = SMWDataValueFactory::newPropertyObjectValue($propertyValue, $queryString);
		$this->m_data->addPropertyObjectValue($propertyValue, $dataValue);
	}
	
/*
	 * Sets the limit of this query call
	 */
	public function setQueryLimit($queryLimit){
		$propertyValue = SMWPropertyValue::makeUserProperty(QRC_HQL_LABEL);
		$dataValue = SMWDataValueFactory::newPropertyObjectValue($propertyValue, $queryLimit);
		$this->m_data->addPropertyObjectValue($propertyValue, $dataValue);
	}
	
/*
	 * Sets the offset of this query call
	 */
	public function setQueryOffset($queryOffset){
		$propertyValue = SMWPropertyValue::makeUserProperty(QRC_HQO_LABEL);
		$dataValue = SMWDataValueFactory::newPropertyObjectValue($propertyValue, $queryOffset);
		$this->m_data->addPropertyObjectValue($propertyValue, $dataValue);
	}
	
	public function parseUserValue($value){
		return true;
	} 
	
	public function getShortWikiText($linked = null){
	}
	
	
	public function getShortHTMLText($linked = null){
	}
	
	public function  getLongWikiText($linker = null){
	}
	
	public function getLongHTMLText($linker = null){
	}
	
	public function getWikiValue(){
	}
	
	public function isValid(){
		return true;
	}
	
	public function getSignature() {
		return 'c';
	}
}
