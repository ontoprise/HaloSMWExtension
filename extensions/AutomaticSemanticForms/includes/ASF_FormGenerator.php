<?php

/*
 * Automatically generates Semantic Forms based on the current ontology
 */
class ASFFormGenerator {

	private static $formGenerator = null;

	/*
	 * Singleton
	 */
	public static function getInstance(){
		if(self::$formGenerator == null){
			self::$formGenerator = new self();
		}
		return self::$formGenerator;
	}
	
	private $formDefinition;

	/*
	 * Generates a Semantic Form based on a given
	 * title object and its category annotations
	 */
	public function generateFromTitle(Title $title, $createInNSCategory = false){
		$categories = $title->getParentCategories();

		//Do not create forms in NS_Category if not explicitly stated
		if($title->getNamespace() == NS_CATEGORY && !$createInNSCategory){
			return false;
		}

		return $this->generateFormForCategories(array_keys($categories), $title);
	}

	/*
	 * Generate form for an instance based on some given category names
	 */
	public function generateFormForCategories($categories, $instanceTitle = null){
		
		$this->formDefinition = false;

		//check if an automatic form can be created
		if(count($categories) == 0) return false;

		//eliminate categories that are super-categories of another annotated category
		$categories = $this->removeSuperCategories(array_flip($categories));

		list($categories, $categoriesWithNoProperties, $categoriesWithNoFormEdit)
			= $this->initializeCategoryFormData($categories);

		if(count($categories) == 0 && count($categoriesWithNoProperties) == 0){
			return false;
		} else {
			$this->formDefinition = new ASFFormDefinition($categories, $categoriesWithNoProperties);	
			return true;
		}
	}
	
	
	/*
	 *
	 */
	private function initializeCategoryFormData($categories){
		
		list($categorySections, $categoriesWithNoProperties, $categoriesWithNoFormEdit) =
			ASFCategorySectionStructureProcessor::getInstance()->getCategorySectionStructure($categories);

		if(!$categorySections)
			return array(array(), $categoriesWithNoProperties, $categoriesWithNoFormEdit);

		$categories = array();
		foreach($categorySections as $categoryName => $categorySection){
			$categoryTitle = Title::newFromText($categoryName, NS_CATEGORY);
			//$categoryTitle = Category::newFromName($categoryName)->getTitle();

			$categoryFormDataObject =
				new ASFCategoryFormData($categoryTitle, $categorySection);

			$categories[] = $categoryFormDataObject;
			
			if($categoryFormDataObject->isEmptyCategory()){
				$categoriesWithNoProperties[$categoryName] = false;
			}
		}
			
		return array($categories, $categoriesWithNoProperties, $categoriesWithNoFormEdit);
	}


	/*
	 * Removes categories that are super categories of others in
	 * the given category array
	 */
	private function removeSuperCategories($categories){
		global $wgLang;

		$categories = array_keys($categories);

		$categoryHierarchies = array();
		foreach($categories as $category){
			if(strpos($category, $wgLang->getNSText(NS_CATEGORY).':') === 0){
				$category = substr($category, strpos($category, ":") +1);
			}
			$category = Str_replace('_', ' ', $category);
			$categoryObject = Category::newFromName($category);
			$categoryHierarchies[$category] = ASFFormGeneratorUtils::getSuperCategories($categoryObject->getTitle());
		}

		foreach($categoryHierarchies as $category => $superCategories){
			foreach(array_keys($superCategories) as $superCategory){
				if(array_key_exists($superCategory, $categoryHierarchies)){
					unset($categoryHierarchies[$superCategory]);
				}
			}
		}

		$categories = array_keys($categoryHierarchies);

		//echo('<pre>'.print_r($categories, true).'</pre>');

		return $categories;
	}
	
	public function getFormDefinition(){
		return $this->formDefinition;
	}

}


