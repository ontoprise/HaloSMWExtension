<?php

//define form metadata properties for properties
define('ASF_PROP_HAS_TYPE', 'Has_type');
define('ASF_PROP_HAS_DOMAIN_AND_RANGE', 'Has_domain_and_range');
define('ASF_PROP_HAS_MIN_CARDINALITY', 'Has_min_cardinality');
define('ASF_PROP_HAS_MAX_CARDINALITY', 'Has_max_cardinality');
define('ASF_PROP_ALLOWS_VALUES', 'Allows_value');
define('ASF_PROP_FORM_INPUT_LABEL', 'Form_input_label');
define('ASF_PROP_USE_INPUT_TYPE', 'Use_input_type');
define('ASF_PROP_IS_UPLOADABLE', 'Is_uploadable');
define('ASF_PROP_FORM_INPUT_HELP', 'Form_input_help');
define('ASF_PROP_VALIDATOR', 'Validator');
define('ASF_PROP_USE_CLASS', 'Use_class');
define('ASF_PROP_DELIMITER', 'Delimiter');
define('ASF_PROP_FIELD_SEQUENCE_NUMBER', 'Field_sequence_number');
define('ASF_PROP_DEFAULT_VALUE', 'Default_value');
define('ASF_PROP_HIDE_PROP', 'Hide_property');

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

	/*
	 * Generates a Semantic Form based on a given
	 * title object and its category annotations
	 */
	public function generateFromTitle(Title $title, $createInNSCategory = false, $justCheck = false){
		$categories = $title->getParentCategories();

		//Do not create forms in NS_Category if not explicitly stated
		if($title->getNamespace() == NS_CATEGORY && !$createInNSCategory){
			return false;
		}

		return $this->generateFormForCategories(array_keys($categories), $title, $justCheck);
	}

	/*
	 * Generate form for an instance based on some given category names
	 */
	public function generateFormForCategories($categories, $instanceTitle = null, $justCheck = false){

		//check if an automatic form can be created
		if(count($categories) == 0) return false;

		//eliminate categories that are super-categories of another annotated category
		$categories = $this->removeSuperCategories(array_flip($categories));

		list($categories, $categoriesWithNoProperties, $categoriesWithNoFormEdit)
		= $this->initializeCategoryFormData($categories);

		if(count($categories) == 0 && count($categoriesWithNoProperties) == 0)
		return array(false, $categoriesWithNoFormEdit);

		if($justCheck)
		return array(true, $categoriesWithNoFormEdit);

		if(!is_null($instanceTitle)){
			$unresolvedAnnotationsSection =
			new ASFUnresolvedAnnotationsFormData($instanceTitle, $categories);

			$categories[] = $unresolvedAnnotationsSection;
		}
			
		//deal with duplicate properties
		$formDefinition = $this->getFormDefinition($categories, $categoriesWithNoProperties);

		//deal with preloading
		//todo: use s.th. better than a gloabal variable
		global $asfPreloadingArticles;
		$asfPreloadingArticles = array();
		foreach($categories as $c){
			$asfPreloadingArticles = array_merge($asfPreloadingArticles, $c->getPreloadingArticles());
		}

		$this->computePageNameTemplate($categories);
		
		//echo('<pre>'.print_r($formDefinition, true).'</pre>');

		return array($formDefinition, $categoriesWithNoFormEdit);
	}
	
	
	private function computePageNameTemplate($categories){
		//todo: use s.th. better than a gloabal variable
		global $asfPageNameTemplate;	
		$asfPageNameTemplate = '';
		$useDefaultTemplate = true;
		foreach($categories as $c){
			list($isDefault, $template) = $c->getPageNameTemplate();
			if($isDefault){
				if($useDefaultTemplate){
					if(strlen($template) > 0){
						$asfPageNameTemplate .= ' '.$template;
					}			
				}
			} else {
				if($useDefaultTemplate){
					$asfPageNameTemplate = '';
				}
				if(strlen($template) > 0){
					$asfPageNameTemplate .= ' '.$template;
				}
				$useDefaultTemplate = false;
			}
		}
		
		$asfPageNameTemplate = trim($asfPageNameTemplate);
		if($useDefaultTemplate || strlen($asfPageNameTemplate) == 0){
			$asfPageNameTemplate = trim($asfPageNameTemplate.'<unique number>');			
		} else {
			$addUniqueNumber = false;
			if(strpos($asfPageNameTemplate, '<unique number>') !== false){
				$addUniqueNumber = true;
				$asfPageNameTemplate = str_replace(
					'<unique number>', '', $asfPageNameTemplate);	
			}
			
			$asfPageNameTemplate = str_replace(
				array('<', '>'), array('<CreateSilentAnnotations:[', ']>'), $asfPageNameTemplate);

			if($addUniqueNumber){
				$asfPageNameTemplate = trim($asfPageNameTemplate).' <unique number>';
			}
		}
	}


	/*
	 *
	 */
	private function initializeCategoryFormData($categories){
		
		//global $asfDoEnhancedCategorySectionProcessing;
		//if($asfDoEnhancedCategorySectionProcessing){
		//let the category section structure processor compute which
		//sections to create
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
			
		//	} else {
		//		//this is not supported in this mode
		//		$categoriesWithNoProperties = array();
		//		$categoriesWithNoFormEdit = array();
		//
		//		//create section for each category annotation and then deal with duplicate properties
		//		global $wgLang;
		//		foreach($categories as $key => $category){
		//			if(strpos($category, $wgLang->getNSText(NS_CATEGORY).":") === 0){
		//				$category = substr($category, strpos($category, ":") +1);
		//			}
		//
		//		}
		//		$categories = $this->dealWithDuplicateProperties($categories);
		//	}

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


	/*
	 * Returns the form definition
	 */
	private function getFormDefinition($categories, $categoriesWithNoProperties){
		$formDefinition = $this->getFormDefinitionIntro($categories);
		$formDefinition .= $this->getFormDefinitionSyntax($categories);
		$formDefinition .= $this->getFormDefinitionOutro($categories, $categoriesWithNoProperties);

		return $formDefinition;
	}

	/*
	 * Returns the intro of this form definition
	 */
	private function getFormDefinitionIntro($categories){

		$intro = "{{{info|add title=Add|edit title=Edit";

		global $asfWYSIWYG;
		if(defined('WYSIWYG_EDITOR_VERSION')){
			$intro .= "|WYSIWYG";
		}

		$intro .= "}}}\n\n";


		$intro .= "{{{for template| CreateSilentAnnotations:";
		$intro .= "}}}\n\r";

		return $intro;
	}

	/*
	 * Returns the syntax of this form definition
	 */
	private function getFormDefinitionSyntax($categories){
		$formDefinitionSyntax = "";

		foreach($categories as $categoty){
			$formDefinitionSyntax .= $categoty->getCategorySection();
		}

		return $formDefinitionSyntax;
	}

	/*
	 * Returns the outro of this form definition
	 */
	private function getFormDefinitionOutro($categories, $categoriesWithNoProperties){
		$outro = "\n\n{{{end template}}}";

		$appendix = array();
		foreach($categories as $categoty){
			$appendix = array_merge($appendix, $categoty->getCategorySectionAppendix());
		}
		foreach($appendix as $a){
			$outro .= $a;
		}

		$outro .=  $this->handleCategoriesWithNoProperties($categoriesWithNoProperties);

		

		global $wgUser;
		$cols = $wgUser->getIntOption('cols');
		$rows = $wgUser->getIntOption('rows');

		$showFreeText = true;
		foreach($categories as $c){
			if($c->hideFreeText()){
				$showFreeText = false;
				break;
			}
		}
		
		if($showFreeText){
			$outro .= "'''".wfMsg('asf_free_text')."'''\n";			
			$outro .= "{{{standard input|free text|rows=".$rows."|cols=".$cols."}}}";
		} else {
			$outro .= "{{{standard input|free text|rows=".$rows."|cols=".$cols."}}}";
			$outro .= '<span class="asf-hide-freetext"/>';
		}
		
		$outro .= wfMsg('asf_autogenerated_msg');

		return $outro;
	}


	/*
	 * get syntax for section with invalid categories
	 */
	private function handleCategoriesWithNoProperties($categoriesWithNoProperties){
		$syntax = '';

		if(count($categoriesWithNoProperties) > 0){
			$syntax .= "\n{{#collapsableFieldSetStart:";

			$syntax .= wfMsg('asf_categories_with_no_props_section')."| true}}\n";
				
			$syntax .= '<ul>';
			global $wgLang, $asfDisplayPropertiesAndCategoriesAsLinks;
			foreach($categoriesWithNoProperties as $c => $dc){
				if($asfDisplayPropertiesAndCategoriesAsLinks){
					$link = ASFFormGeneratorUtils::createParseSaveLink($wgLang->getNSText(NS_CATEGORY).':'.$c, $c);
					$syntax .= '<li>'.$link.'</li>';
				} else {
					$syntax .= '<li>'.$c.'</li>';
				}
			}
			$syntax .= '</ul>';
				
			$syntax .= "\n{{#collapsableFieldSetEnd:}}";
		}

		return $syntax;
	}

	/*
	 * If the category section structure processor is not used, then it is
	 * possible, that different sections want to define the same
	 * property values.  Those form input fields therefore will be replaced.
	 */
	private function dealWithDuplicateProperties($categories){
		for($i=0; $i < count($categories); $i++){
			for($k=0; $k < $i; $k++){
				foreach($categories[$k]->propertiesFormData as $propName => $dontCare){
					if(array_key_exists($propName, $categories[$i]->propertiesFormData)){
						$categories[$i]->propertiesFormData[$propName]
						->setFormFieldSyntax(wfMsg("asf_duplicate_property_placeholder"));
					}
				}
			}
		}

		return $categories;
	}

	/*
	 * Adds the javascript libraries, which are required by
	 * the Automatic Semantic Forms extension
	 */
	private function addJavascriptFiles(){
		// Tell the script manager, that we need jQuery
		global $smgJSLibs;
		$smgJSLibs[] = 'jquery';
		$smgJSLibs[] = 'qtip';

		global $wgOut, $asfScriptPath;
		$scriptFile = $asfScriptPath . "/scripts/automatic_semantic_forms.js";
		$wgOut->addScriptFile( $scriptFile );
	}

}






