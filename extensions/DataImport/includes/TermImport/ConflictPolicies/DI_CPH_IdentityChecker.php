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

/**
 * @file
 * @ingroup DITermImport
 * 
 * @author Ingo Steinbauer
 */

/*
 * Does identity checks for two instances based on some
 * property or template parameter values
 */
class DICPHIdentityChecker {
	
	private static $instance;
	
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	private function __construct(){
	}
	
	/*
	 * Checks if an instance has the same property values as the 
	 * given ones. 
	 */
	public function doExactCheckForProperties($title, $properties){
		foreach($properties as $propertyName => $values){
			$currentValues = DICPHArticleAccess::getInstance()->getPropertyValues($title, $propertyName);
			
			if(!$currentValues) return false;

			if(count($currentValues) != count($values)){
				return false;
			}
			
			foreach($currentValues as $key => $value){
				$currentValues[$key] = ucfirst($value);
			}
			
			foreach($values as $value){
				if(!in_array(ucfirst($value), $currentValues)){
					return false;		
				}
			}
		}
		
		return true;
	}
	
	/*
	 * Checks if an instance has the same template parameter values  
	 * as the given ones. 
	 */
	public function doExactCheckForTemplateParams($title, $template, $params){
		foreach($params as $paramName => $param){
			$currentValue = DICPHArticleAccess::getInstance()->getTemplateParameterValue($title, $template, $paramName);
			if($currentValue !== $param) return false;
		}
		
		return true;
	}
	
	/*
	 * Returns an array that contains all attribute names and values, that must
	 * be considered during identity check
	 */
	public function getAttributesForIdentityCheck($term, $template, $damId, $delimiter = ''){
		global $ditigAttributesForIdentityCheck;
		$attributesForIdentityCheck = array();
		
		if(trim($template) == ''){ //silent annotations mode
			$attributes = $term->getAttributes(true);
			foreach($ditigAttributesForIdentityCheck[$damId] as $attributeName){
				if(array_key_exists($attributeName, $attributes)){
					$attributesForIdentityCheck[$attributeName] = DICPHArticleAccess::getInstance()->
						applyDelimiterOnPropertyValues($attributeName, $attributes[$attributeName]);
				} else {
					$attributesForIdentityCheck[$attributesName] = array();
				}
			}
		} else {
			$attributes = $term->getAttributes(false);
			foreach($ditigAttributesForIdentityCheck[$damId] as $attributeName){
				if(array_key_exists($attributeName, $attributes)){
					$attributesForIdentityCheck[$attributeName] = 
						implode($delimiter, $attributes[$attributeName]);
				} else {
					$attributesForIdentityCheck[$propertyName] = '';
				}
			}
		}
		
		return $attributesForIdentityCheck;
	}
	
}