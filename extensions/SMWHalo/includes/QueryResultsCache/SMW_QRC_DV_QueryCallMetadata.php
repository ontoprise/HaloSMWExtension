<?php

class SMWQueryCallMetadataValue extends SMWContainerValue {

	protected $m_data;
	
	public function setQueryId($queryId){
		$propertyValue = SMWPropertyValue::makeUserProperty('UsesQuery');
		$dataValue = SMWDataValueFactory::newPropertyObjectValue($propertyValue, $queryId);
		$this->m_data->addPropertyObjectValue($propertyValue, $dataValue);
	}
	
	public function setQueryString($queryString){
		$propertyValue = SMWPropertyValue::makeUserProperty('HasQueryString');
		$dataValue = SMWDataValueFactory::newPropertyObjectValue($propertyValue, $queryString);
		$this->m_data->addPropertyObjectValue($propertyValue, $dataValue);
	}
	
	public function parseUserValue($value){
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
	 
	
	

}
