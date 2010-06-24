<?php

class SMWQueryCallMetadataValue extends SMWContainerValue {
	
	protected $m_data;
	
	public function setQueryId($queryId){
		$propertyValue = SMWPropertyValue::makeUserProperty('HasQueryId');
		$dataValue = SMWDataValueFactory::newPropertyObjectValue($propertyValue, $queryId);
		$this->m_data->addPropertyObjectValue($propertyValue, $dataValue);
	}
	
	public function setQueryString($queryString){
		$propertyValue = SMWPropertyValue::makeUserProperty('HasQueryString');
		$dataValue = SMWDataValueFactory::newPropertyObjectValue($propertyValue, $queryString);
		$this->m_data->addPropertyObjectValue($propertyValue, $dataValue);
	}
	
	public function parseUserValue($value){
		$this->m_value = $value;
		$this->m_caption = $value;
		
		return true;
	} 
	
	public function getShortWikiText($linked = null){
		$this->unstub();
		return $this->m_value;
	}
	
	
	public function getShortHTMLText($linked = null){
		$this->unstub();
		return $this->m_value;
	}
	
	public function  getLongWikiText($linker = null){
		$this->unstub();
		return $this->m_value;
	}
	
	public function getLongHTMLText($linker = null){
		$this->unstub();
		return $this->m_value;
	}
	
	public function getWikiValue(){
		$this->unstub();
		return $this->m_value;
	}
	
	public function isValid(){
		return true;
	}
	
	function getSignature(){
		//return 'c';
		return 'tnwt';
	}
	
	public function setUserValue($value, $caption = false) {
		$this->parseUserValue($value);
	}
	
}
