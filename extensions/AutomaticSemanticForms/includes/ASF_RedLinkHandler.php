<?php


/*
 * This class provides a red-link handler for ASF
 */
class ASFRedLinkHandler {
	
	
	public static function handleRedLinks( $linker, $target, $options, $text, &$attribs, &$ret ) {
		
		if(in_array('broken', $options)){
			if(strpos($attribs['href'], 'Special:FormEdit') === 0){
				$link = self::createLinkedPage($target);
			 	if($link){
					$attribs['href'] = $link; 	
			 	}
			}
		}
		
		return true;	
	}
	
	
	static function createLinkedPage( $title ) {
		
		$store = smwfGetStore();
		$title_text = self::titleString( $title );
		$value = SMWDataValueFactory::newTypeIDValue( '_wpg', $title_text );
		$incoming_properties = $store->getInProperties( $value );
		$categories = array();
		foreach ( $incoming_properties as $property ) {
			$property_title = $property->getWikiValue();
			$property_title = Title::newFromText($property_title, SMW_NS_PROPERTY);
			
			if($property_title && $property_title->exists()){
				$semanticData = $store->getSemanticData($property_title);
				
				$range = ASFFormGeneratorUtils::getPropertyValueOfTypeRecord(
					$semanticData, ASF_PROP_HAS_DOMAIN_AND_RANGE,1);
				
				if($range) $categories[$range] = true;
			}
		}
		
		foreach(array_keys($categories) as $category){
			$categoryTitle = Title::newFromText($category);

			if($categoryTitle && $categoryTitle->exists()){
				$semanticData = $store->getSemanticData($categoryTitle);
				
				$defaultForm = ASFFormGeneratorUtils::getPropertyValue(
					$semanticData, 'Has_default_form');
				
				if($defaultForm) return false;
			}
		}
		
		list($createLink, $dC) = ASFFormGenerator::getInstance()
			->generateFormForCategories(array_keys($categories), null, true); 
		if($createLink){
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
		}else {
			return false;
		}
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