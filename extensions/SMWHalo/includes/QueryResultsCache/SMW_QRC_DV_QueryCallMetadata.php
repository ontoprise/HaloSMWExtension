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
	
	/*
	 * Add dependency to a property, which is used by the query
	 */
	public function addPropertyDependency($propertyName){
		$propertyValue = SMWPropertyValue::makeUserProperty(QRC_DOP_LABEL);
		$dataValue = SMWDataValueFactory::newPropertyObjectValue($propertyValue, $propertyName);
		$this->m_data->addPropertyObjectValue($propertyValue, $dataValue);
	}
	
	/*
	 * Add dependency to a category, which is used by the query
	 */
	public function addCategoryDependency($categoryName){
		$propertyValue = SMWPropertyValue::makeUserProperty(QRC_DOC_LABEL);
		$dataValue = SMWDataValueFactory::newPropertyObjectValue($propertyValue, $categoryName);
		$this->m_data->addPropertyObjectValue($propertyValue, $dataValue);
	}
	
	public function setExtraPropertyPrintouts($epp){
		$propertyValue = SMWPropertyValue::makeUserProperty(QRC_HEPP_LABEL);
		$dataValue = SMWDataValueFactory::newPropertyObjectValue($propertyValue, $epp);
		$this->m_data->addPropertyObjectValue($propertyValue, $dataValue);
	}
	
	public function setExtraCategoryPrintouts($hasECP){
		$propertyValue = SMWPropertyValue::makeUserProperty(QRC_HECP_LABEL);
		$dataValue = SMWDataValueFactory::newPropertyObjectValue($propertyValue, $hasECP);
		$this->m_data->addPropertyObjectValue($propertyValue, $dataValue);
	}
	
	public function setIsSPQRQLQuery($isSPARQL){
		$propertyValue = SMWPropertyValue::makeUserProperty(QRC_ISQ_LABEL);
		$dataValue = SMWDataValueFactory::newPropertyObjectValue($propertyValue, $isSPARQL);
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
