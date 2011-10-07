<?php

//define form metadata properties for properties
define('ASF_PROP_HAS_TYPE', '_TYPE');
define('ASF_PROP_HAS_DOMAIN_AND_RANGE', 'Has_domain_and_range');
define('ASF_PROP_HAS_RANGE', 'Has_range');
define('ASF_PROP_HAS_MIN_CARDINALITY', 'Has_min_cardinality');
define('ASF_PROP_HAS_MAX_CARDINALITY', 'Has_max_cardinality');
define('ASF_PROP_ALLOWS_VALUES', '_PVAL');
define('ASF_PROP_FORM_INPUT_LABEL', 'Form_input_label');
define('ASF_PROP_USE_INPUT_TYPE', 'Use_input_type');
define('ASF_PROP_IS_UPLOADABLE', 'Is_uploadable');
define('ASF_PROP_FORM_INPUT_HELP', 'Form_input_help');
define('ASF_PROP_VALIDATOR', 'Validator');
define('ASF_PROP_USE_CLASS', 'Use_class');
define('ASF_PROP_DELIMITER', 'Delimiter');
define('ASF_PROP_FIELD_SEQUENCE_NUMBER', 'Field_sequence_number');
define('ASF_PROP_DEFAULT_VALUE', 'Default_value');

//define form metadata properties for categories
define('ASF_PROP_NO_AUTOMATIC_FORMEDIT', 'No_automatic_formedit');
define('ASF_PROP_USE_DISPLAY_TEMPLATE', 'Use_display_template');
//define('ASF_PROP_USE_CLASS', 'Use_class');
define('ASF_PROP_NOT_DISJOINT_WITH', 'Not_disjoint_with');
define('ASF_PROP_PRELOAD', 'Use_preload_article');
define('ASF_PROP_PAGE_NAME_TEMPLATE', 'Use_page_name_template');
define('ASF_PROP_HIDE_FREE_TEXT', 'Hide_free_text');

//define dtata type form input type relations
define('TEXTDATATYPES', '-page- ');
define('LONGTEXTDATATYPES', '-url- -email- -annotation uri- -telephone number- -string-');
define('SHORTTEXTDATATYPES', '-number- -temperature-');
define('TEXTAREADATATYPES', '-text- -ccode-');
define('DATETIMEDATATYPES', '-date-');
define('CHECKBOXDATATYPES', '-boolean-');

//define sizes of form input fields
define('ASF_LONG_TEXT_SIZE', '110');
define('ASF_SHORT_TEXT_SIZE', '30');
define('ASF_TEXTAREA_ROWS', '5');
define('ASF_TEXTAREA_COLS', '78');


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

		//echo('<pre>'.print_r($categoriesWithNoProperties, true).'</pre>');
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
			$categoryTitle = Category::newFromName($categoryName)->getTitle();

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


