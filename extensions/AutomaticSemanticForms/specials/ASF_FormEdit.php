<?php

if ( !defined( 'MEDIAWIKI' ) ) die();

/*
 * This class adds Automatic Semantic Forms features
 * to the special page Special:FormEdit
 */
class ASFFormEdit extends SFFormEdit {
	
	/*
	 * This method is called if one opens Special:FormEdit
	 * 
	 * It adds some ASF features and then calls its parent method
	 */
	function execute($query) {
		//get get parameters
		global $wgRequest;
		$categoryParam = $wgRequest->getVal('categories');
		$targetName = $wgRequest->getVal('target');
		$formName = $wgRequest->getVal('form');
		
		//Initialize category names array
		$categoryNames = array();
		if($categoryParam){
			$categoryParam = str_replace('_', '', $categoryParam);
			$categoryNames = explode(',', $categoryParam);
			global $wgLang;
			foreach($categoryNames as $key => $category){
				$category = trim($category);
				if(strpos($category, $wgLang->getNSText(NS_CATEGORY).':') !== 0){
					$category = $wgLang->getNSText(NS_CATEGORY).':'.$category;
				}
				$categoryNames[$key] = $category;
			}
		}
		
		//Automatically create a new target name if category names
		//but no target name was passed
		if(count($categoryNames) > 0 && !$targetName){
			//todo: Implement this
		}
		
		if(count($categoryNames) > 0 && $targetName){
			//The given instance will be edited with forms for the given categories
			
			//TODO: What to do with non existing or empty  category names?
			
			$targetTitle = Title::newFromText($targetName);
			
			$formDefinition = ASFFormGenerator::getInstance()->generateFormForCategories($categoryNames, $targetTitle);
			if($formDefinition){
				//Set the dummy form name to trick the Semantic Forms extension
				global $asfDummyFormName;
				ASFFormGeneratorUtils::createFormDummyIfNecessary();
				$wgRequest->setVal('form', $asfDummyFormName);
			
				global $asfFormDefData;
				$asfFormDefData = array();		
				$asfFormDefData['formdef'] = $formDefinition;
				
				//deal with additional category annotations
				$categoryNames = $this->getAdditionalCategoryAnnotations($categoryNames, $targetName);
				if(count($categoryNames) > 0){
					$asfFormDefData['additional catehory annotations'] = $categoryNames;
				}
			}
		} else if(count($categoryNames) == 0 && $targetName && !$formName){
			//Automatically create a form for this instance based on its category annotations
			//if the target exists
			
			$title = Title::newFromText($targetName);
			if($title->exists()){
				$formDefinition = ASFFormGenerator::getInstance()->generateFromTitle($title, true);
		
				if($formDefinition){
					global $asfFormDefData;
					$asfFormDefData = array();
					$asfFormDefData['formdef'] = $formDefinition;

					global $asfDummyFormName;
					ASFFormGeneratorUtils::createFormDummyIfNecessary();
					$wgRequest->setVal('form', $asfDummyFormName); 
				}
			}
		}
		
		parent::execute($query);
	}
	
	/*
	 * Compute which additional category annotations to add
	 * to the free text text area
	 */
	private function getAdditionalCategoryAnnotations($categoryNames, $targetName){
		$title = Title::newFromText($targetName);
		if($title->exists()){
			$annotatedParentCategories = $title->getParentCategories();
			if(count($annotatedParentCategories) > 0){
				foreach($categoryNames as $key => $category){
					if(array_key_exists($category, $annotatedParentCategories)){
						unset($categoryNames[$key]);
					} else {
						$categoryNames[$key] = $category;
					}	
				}
			}
		} 
		return $categoryNames;
	}
}