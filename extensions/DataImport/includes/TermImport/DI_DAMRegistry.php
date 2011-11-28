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