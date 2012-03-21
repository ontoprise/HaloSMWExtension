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


class ASFUneditableValuesHandler {
	
	public static function getUneditableValues($articleName, $existingValues, $editableValues){
		
		//echo('<pre>'.print_r($existingValues, true).'</pre>');
		
		//first set all existing values to be editable
		foreach($existingValues as $label => $values){
			foreach($values['values'] as $key => $value){
				$existingValues[$label]['values'][$key]['editable'] = true;
			}
		}
		
		//skip this if we are creating a new article
		if(is_null($articleName) || strlen($articleName) == 0){
			return $existingValues;
		}
		
		$title = Title::newFromText($articleName);
		
		//skip if this is an invalid title
		if(is_null($title) || !($title instanceof Title) || !$title->exists()){
			return $existingValues;	
		}
		
		$semanticData = ASFFormGeneratorUtils::getSemanticData($title);
		$properties = $semanticData->getProperties();
		
		//first retrieve all property values
		$allPropertyValues = array();
		foreach($properties as $propertyName => $property){
			if(!$property->isShown() || !$property->isuserDefined()){
				continue;
			}
			
			$values = $semanticData->getPropertyValues($properties[$propertyName]);
			
			$allPropertyValues[$propertyName] = array('values' => array());
			foreach($values as $v){
				$dv = SMWDataValueFactory::newDataItemValue($v, null);
				$allPropertyValues[$propertyName]['typeid'] = $dv->getTypeID();
				$allPropertyValues[$propertyName]['values'][] = array(
					'value' => $dv->getShortWikiText(),
					'hash' => $dv->getHash());
			}
		}
		
		//echo('<pre>'.print_r($allPropertyValues, true).'</pre>');
		
		//now detect uneditable property values 
		foreach($allPropertyValues as $label => $values){
			$label = str_replace('_', ' ', $label);
			if(!array_key_exists($label, $editableValues)){
				$editableValues[$label] =array('values' => array());
			} 
				
			foreach($values['values'] as $value){
				$found = false;
				foreach($editableValues[$label]['values'] as $compareValue){
					$compareValue = $compareValue['value'];
					$compareValue = SMWDataValueFactory::newTypeIDValue(
						$values['typeid'], $compareValue);
					$compareValue = $compareValue->getHash(); 
					if($value['hash'] == $compareValue){
						$found = true;
						break;
					}
				}
				
				if(!$found){
					$existingValues[$label]['values'][] = array(
						'value' => $value['value'],
						'insync' => false,
						'editable' => false);
				} else if (!array_key_exists($label, $existingValues)){
					$existingValues[$label]['values'][] = array(
						'value' => $value['value'],
						'insync' => true,
						'editable' => true);
				}
			}
		}
		
		//echo('<pre>'.print_r($existingValues, true).'</pre>');
		
		return $existingValues;
	}
	
}