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



/*
 * This class provides a red-link handler for ASF
 */
class ASFRedLinkHandler {
	
	
	public static function handleRedLinks( $linker, $target, $options, $text, &$attribs, &$ret ) {
		
		if(in_array('broken', $options)){
			if(strpos($attribs['href'], 'Special:FormEdit') === false){
				$link = self::createLinkedPage($target);
			 	if($link){
					$attribs['href'] = $link; 	
			 	}
			}
		}
		
		return true;	
	}
	
	
	static function createLinkedPage( $title ) {
		
		//do not create ASFs for instances in NS category
		if($title->getNamespace() == NS_CATEGORY){
			return false;
		}
		
		$store = smwfGetStore();
		
		//first compute all category annotations of the new instance
		$title_text = self::titleString( $title );
		$value = SMWDataValueFactory::newTypeIDValue( '_wpg', $title_text );
		$incoming_properties = $store->getInProperties( $value->getDataItem() );
		$categories = array();
		foreach ( $incoming_properties as $property ) {
			$property_title = $property->getLabel();
			$property_title = Title::newFromText($property_title, SMW_NS_PROPERTY);
			
			if($property_title && $property_title->exists()){
				$semanticData = ASFFormGeneratorUtils::getSemanticData($property_title);
				
				$range = ASFFormGeneratorUtils::getPropertyValueOfTypeRecord(
					$semanticData, ASF_PROP_HAS_DOMAIN_AND_RANGE,1);
				
				if($range) $categories[$range] = true;
			}
		}
		
		//Check if categories have default forms defined and if they have 'No automeatic formedit' annotations
		$catWithNoNoASFEditFound = false;
		foreach(array_keys($categories) as $category){
			$categoryTitle = Title::newFromText($category);

			$semanticData = ASFFormGeneratorUtils::getSemanticData($categoryTitle);
				
			$defaultForm = ASFFormGeneratorUtils::getPropertyValue(
				$semanticData, 'Has_default_form');
				
			//do not create ASFs for instances with a default form
			if($defaultForm) return false;
			
			//Check if ASF has a 'No automatic formedit' annotation
			if(ASFFormGeneratorUtils::getPropertyValue($semanticData, ASF_PROP_NO_AUTOMATIC_FORMEDIT) != 'true'){
				$catWithNoNoASFEditFound = true;					
			}
		}
		
		//No Cat with no 'No automatic formedit' annotation found and ASF cannot be created
		if(!$catWithNoNoASFEditFound){
			return false;
		}
		
		//Create the link
		$link = SpecialPage::getPage( 'FormEdit' );
		$link = $link->getTitle()->getLocalURL();
			
		if(strpos($link, '?') > 0){
			$link .= '&';
		} else {
			$link .= '?';
		}
			
		$link .= 'categories=';
		$first = true;
		foreach(array_keys($categories) as $category){
			if(!$first) $link .= ',';
			$link .= urlencode($category);
			$first = false;
		}
		$link .= '&target='.$title_text;
		return $link;
	}
	
	
	static function titleString( $title ) {
		global $wgCapitalLinks;

		$namespace = $title->getNsText();
		if ( $namespace != '' ) {
			$namespace .= ':';
		}
		if ( $wgCapitalLinks ) {
			global $wgContLang;
			return $namespace . $wgContLang->ucfirst( $title->getText() );
		} else {
			return $namespace . $title->getText();
		}
	}
	
}
