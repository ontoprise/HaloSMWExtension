<?php

/*
 * This class provides some helper methods
 * for the form generation process
 */
class ASFFormGeneratorUtils {
	
	/*
	 * Helper method for initializeFormCreationMetadata
	 */
	public static function getPropertyValue($semanticData, $propertyName, $getAll = false, $defaultValue = ''){
		$result = $defaultValue;
		
		$properties = $semanticData->getProperties();
		
		if(array_key_exists($propertyName, $properties)){
			$values = $semanticData->getPropertyValues($properties[$propertyName]);
			if(!$getAll){
				$idx = array_keys($values);
				$idx = $idx[0];
				$result = $values[$idx]->getShortWikiText();
			} else {
				$result = array();
				foreach($values as $v){
					$result[] = $v->getShortWikiText();
				}
			}
		}
		
		return $result;
	}
	
	/*
	 * Helper method for initializeFormCreationMetadata
	 */
	public static function getPropertyValueOfTypeRecord($semanticData, $propertyName, $index, $defaultValue = ''){
		$result = $defaultValue;
		
		$properties = $semanticData->getProperties();
		
		if(array_key_exists($propertyName, $properties)){
			$values = $semanticData->getPropertyValues($properties[$propertyName]);
			if(is_array($values) && array_key_exists(0, $values)){	
				if($values[0] instanceof SMWRecordValue){
					$dVs = $values[0]->getDVs();
					if(count($dVs) >= $index+1){
						$result = $dVs[$index]->getShortWikiText();
					}
				}
			}
		}
		
		return $result;
	}

	/*
	 * Get all supercategories of a given category
	 */
	public static function getSuperCategories($categoryTitle, $asTree = false, $superCategoryTitles = array()){
		$directSuperCatgeoryTitles = smwfGetSemanticStore()->getDirectSuperCategories($categoryTitle);
		
		if($asTree){
			$superCategoryTitles[$categoryTitle->getText()] = array();
		}
		
		foreach($directSuperCatgeoryTitles as $dSCT){
			if($asTree){
				$superCategoryTitles[$categoryTitle->getText()] = 
					self::getSuperCategories($dSCT, $asTree, $superCategoryTitles[$categoryTitle->getText()]);
			} else {
				$superCategoryTitles[$dSCT->getText()] = $dSCT;
				$superCategoryTitles = self::getSuperCategories($dSCT, $asTree, $superCategoryTitles);
			}
		}
		
		return $superCategoryTitles;
	}
	
/*
	 * Checks if automatic semantic forms can be created.
	 * for this article
	 */
	public static function canFormForArticleBeCreated(Title $title, $createInNSCategory = false){
		list($response, $dC)
			= ASFFormGenerator::getInstance()->generateFromTitle($title, false, true);
		return $response;
	}
	
	/*
	 * Returns a string of long property values
	 */
	public static function getLongPropertyValues($semanticData, $propertyName, $linked=true){
		$result = '';
		
		$properties = $semanticData->getProperties();
		
		if(array_key_exists($propertyName, $properties)){
			$values = $semanticData->getPropertyValues($properties[$propertyName]);
			
			$result = array();
			foreach($values as $v){
				$result[] = $v->getLongText(SMW_OUTPUT_WIKI, $linked);
			}
			$result = implode(',', $result);
			
		}
		
		return $result;
	}
	
	/*
	 * Create the form dummy which is used to trick
	 * the Semantic Forms extension
	 */
	public static function createFormDummyIfNecessary(){
		global $asfDummyFormName, $sfgContLang, $wgUser, $smwgNamespacesWithSemanticLinks, $smwgHaloIP;
		
		require_once($smwgHaloIP . '/includes/SMW_OntologyManipulator.php');
		
		//Make sure that annotations in SF_NS_FORM are possible
		$smwgNamespacesWithSemanticLinks[SF_NS_FORM] = true;
		
		//create default form annotation text
		$annotation = '[[';
		$annotation .= $sfgContLang->m_SpecialProperties[SF_SP_PAGE_HAS_DEFAULT_FORM];
		$annotation .= '::';
		$annotation .= $asfDummyFormName;
		$annotation .= '| ]]';
		
		//check dummy title
		$dummyTitle = Title::newFromText($asfDummyFormName, SF_NS_FORM);
		if(!$dummyTitle->exists()){
			//dummy article must be created
			
			$dummyContent = wfMsg('asf_dummy_article_content');
			$dummyContent .= $annotation;
			
			$article = new Article($dummyTitle);
			$article->doEdit($dummyContent, wfMsg('asf_dummy_article_edit_comment'));
		} else {
			//check if page has default form annotation is there
			$rawText = Article::newFromID($dummyTitle->getArticleID())->getRawText();
			
			if(strpos($rawText, $annotation) === false){
				//annotation must be added to dummy article
				$rawText .= "\n\n".$annotation;
				
				$article = new Article($dummyTitle);
				$res = $article->doEdit($dummyContent, wfMsg('asf_dummy_article_edit_comment'));
			}
		}
	}
	
/*
	 * get all properties that use at least one of the given categories as domain
	 */
	public static function getPropertiesWithDomain($categoryTitles){
		$properties = array();
		
		foreach($categoryTitles as $cT){
			foreach(smwfGetSemanticStore()->getPropertiesWithDomain($cT) as $p){
				$properties[] = $p;
			} 
		}
		
		return $properties;
	}
	
} 