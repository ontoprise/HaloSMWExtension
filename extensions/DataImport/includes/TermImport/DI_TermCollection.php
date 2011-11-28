<?php

//todo:document this

class DITermCollection {
	
	private $terms = array();
	
	public function addTerm($term){
		$this->terms[] = $term;
	}
	
	public function getTerms(){
		return $this->terms;
	}
}


class DITerm {

	private $articleName = '';
	private $props = array();
	private $callbacks = array();
	private $isAnnonymousCallbackTerm = false;

	public function setArticleName($articleName){
		$articleName = strip_tags($articleName);
		$this->articleName = $articleName; 
	}
	
	public function getArticleName(){
		return $this->articleName; 
	}
	
	public function addProperty($prop, $value){
		$value = trim($value);
		if (strlen($value) > 0) {
			$this->props[$prop] = $value;
		}
	}
	
	public function getProperties(){
		return $this->props;
	}
	
	public function getPropertyValue($propertyName){
		if(array_key_exists($propertyName, $this->props)){
			return $this->props[$propertyName];
		}
		return false;
	}
	
	public function getCallbacks(){
		return $this->callbacks;
	}
	
	public function addCallback(DITermImportCallback $callback){
		$this->callbacks[] = $callback;
	}
	
	public function isAnnonymousCallbackTerm(){
		return $this->isAnnonymousCallbackTerm;
	}
	
	public function setAnnonymousCallbackTerm($anonymousCallbackTerm){
		$this->isAnnonymousCallbackTerm = $anonymousCallbackTerm;
	}
	
}

class DITermImportCallback{
	
	private $methodName;
	private $params;
	
	public function __construct($methodName, $params){
		$this->methodName = $methodName;
		$this->params = $params;
	}
	
	public function getMethodName(){
		return $this->methodName;
	}
	
	public function getParams(){
		return $this->params;
	}
	
}

