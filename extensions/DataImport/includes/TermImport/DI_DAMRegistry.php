<?php

class DIDAMRegistry {
	
	private static $registeredDAMs = array();
	
	public static function registerDAM($className, $label, $description){
		self::$registeredDAMs[$className] = 
			new DIDAMConfiguration($className, $label, $description);  
	}
	
	public static function getDAMsHTML(){
		$html = "";
		foreach(self::$registeredDAMs as $dam){
			$html .= $dam->getHTML();
		}
		return $html;
	}
	
	public static function getDAM($className){
		if(class_exists($className)){
			return new $className();
		} else {
			return false;
		}
	}
	
	public static function getDAMDesc($className){
		if(array_key_exists($className, self::$registeredDAMs)){
			return self::$registeredDAMs[$className]->getDescription();
		} else {
			return false;
		}
	}
}

class DIDAMConfiguration {
	
	private $className;
	private $label;
	private $description;
	
	public function __construct($className, $label, $description){
		$this->className = $className;
		$this->label = $label;
		$this->description = $description;
	}
	
	public function getHTML(){
		return "<div class=\"entry\" onMouseOver=\"this.className='entry-over';\"".
			" onMouseOut=\"termImportPage.showRightDAM(event, this)\"".
			" onClick=\"termImportPage.getDAL(event, this, '$this->className')\">".
			" <a>$this->label</a></div>";
	}
	
	public function getDescription(){
		return $this->description;
	}
}