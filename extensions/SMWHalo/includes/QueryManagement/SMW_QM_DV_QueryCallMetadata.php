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
		$this->m_data = new SMWContainerSemanticData();
	}
	
	/*
	 * Sets the id of this query call
	 */
	public function setQueryId($queryId){
		$propertyValue = SMWPropertyValue::makeUserProperty(QRC_HQID_LABEL);
		$dataValue = SMWDataValueFactory::newPropertyObjectValue($propertyValue->getDataItem(), $queryId);
		$this->m_data->addPropertyObjectValue($propertyValue->getDataItem(), $dataValue->getDataItem());
	}
	
	/*
	 * Sets the query string of this query call
	 */
	public function setQueryString($queryString){
		$propertyValue = SMWPropertyValue::makeUserProperty(QRC_HQS_LABEL);
		$dataValue = SMWDataValueFactory::newPropertyObjectValue($propertyValue->getDataItem(), $queryString);
		$this->m_data->addPropertyObjectValue($propertyValue->getDataItem(), $dataValue->getDataItem());
	}
	
/*
	 * Sets the limit of this query call
	 */
	public function setQueryLimit($queryLimit){
		$propertyValue = SMWPropertyValue::makeUserProperty(QRC_HQL_LABEL);
		$dataValue = SMWDataValueFactory::newPropertyObjectValue($propertyValue->getDataItem(), $queryLimit);
		$this->m_data->addPropertyObjectValue($propertyValue->getDataItem(), $dataValue->getDataItem());
	}
	
/*
	 * Sets the offset of this query call
	 */
	public function setQueryOffset($queryOffset){
		$propertyValue = SMWPropertyValue::makeUserProperty(QRC_HQO_LABEL);
		$dataValue = SMWDataValueFactory::newPropertyObjectValue($propertyValue->getDataItem(), $queryOffset);
		$this->m_data->addPropertyObjectValue($propertyValue->getDataItem(), $dataValue->getDataItem());
	}
	
	/*
	 * Add dependency to a property, which is used by the query
	 */
	public function addPropertyDependency($propertyName){
		$propertyValue = SMWPropertyValue::makeUserProperty(QRC_DOP_LABEL);
		$dataValue = SMWDataValueFactory::newPropertyObjectValue($propertyValue->getDataItem(), $propertyName);
		$this->m_data->addPropertyObjectValue($propertyValue->getDataItem(), $dataValue->getDataItem());
	}
	
	/*
	 * Add dependency to a category, which is used by the query
	 */
	public function addCategoryDependency($categoryName){
		$propertyValue = SMWPropertyValue::makeUserProperty(QRC_DOC_LABEL);
		$dataValue = SMWDataValueFactory::newPropertyObjectValue($propertyValue->getDataItem(), $categoryName);
		$this->m_data->addPropertyObjectValue($propertyValue->getDataItem(), $dataValue->getDataItem());
	}
	
	public function addExtraPropertyPrintouts($epp){
		$propertyValue = SMWPropertyValue::makeUserProperty(QRC_HEPP_LABEL);
		$dataValue = SMWDataValueFactory::newPropertyObjectValue($propertyValue->getDataItem(), $epp);
		$this->m_data->addPropertyObjectValue($propertyValue->getDataItem(), $dataValue->getDataItem());
	}
	
	public function setExtraCategoryPrintouts($hasECP){
		$propertyValue = SMWPropertyValue::makeUserProperty(QRC_HECP_LABEL);
		$dataValue = SMWDataValueFactory::newPropertyObjectValue($propertyValue->getDataItem(), $hasECP);
		$this->m_data->addPropertyObjectValue($propertyValue->getDataItem(), $dataValue->getDataItem());
	}
	
	public function setIsSPQRQLQuery($isSPARQL){
		$propertyValue = SMWPropertyValue::makeUserProperty(QRC_ISQ_LABEL);
		$dataValue = SMWDataValueFactory::newPropertyObjectValue($propertyValue->getDataItem(), $isSPARQL);
		$this->m_data->addPropertyObjectValue($propertyValue->getDataItem(), $dataValue->getDataItem());
	}
	
	public function setusesASKSyntax($isASK){
		$propertyValue = SMWPropertyValue::makeUserProperty(QRC_UAS_LABEL);
		$dataValue = SMWDataValueFactory::newPropertyObjectValue($propertyValue->getDataItem(), $isASK);
		$this->m_data->addPropertyObjectValue($propertyValue->getDataItem(), $dataValue->getDataItem());
	}
	
	public function setUsedInArticle($uia){
		$propertyValue = SMWPropertyValue::makeUserProperty(QM_UIA_LABEL);
		$dataValue = SMWDataValueFactory::newPropertyObjectValue($propertyValue->getDataItem(), $uia);
		$this->m_data->addPropertyObjectValue($propertyValue->getDataItem(), $dataValue->getDataItem());
	}
	
	public function setUsedQueryPrinter($uqp){
		$propertyValue = SMWPropertyValue::makeUserProperty(QM_UQP_LABEL);
		$dataValue = SMWDataValueFactory::newPropertyObjectValue($propertyValue->getDataItem(), $uqp);
		$this->m_data->addPropertyObjectValue($propertyValue->getDataItem(), $dataValue->getDataItem());
	}
	
	public function setQueryName($hqn){
		$propertyValue = SMWPropertyValue::makeUserProperty(QM_HQN_LABEL);
		$dataValue = SMWDataValueFactory::newPropertyObjectValue($propertyValue->getDataItem(), $hqn);
		$this->m_data->addPropertyObjectValue($propertyValue->getDataItem(), $dataValue->getDataItem());
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
	
	protected function loadDataItem( SMWDataItem $dataItem ){
		$this->m_data = $dataItem->getSemanticData();
	}
	
	public function getDataItem(){
		return new SMWDIContainer($this->m_data);
	}
	
}
