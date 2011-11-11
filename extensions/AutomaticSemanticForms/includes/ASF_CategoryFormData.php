<?php

/*
 * Represents the form input fields for a category
 * 
 * Provides the underlying metadata and creates the
 * form field input syntax for the properties related to this category
 */
class ASFCategoryFormData {
	public $titleObject;
	public $semanticData;
	
	public $propertiesFormData;
	
	protected $categorySectionIntro;
	protected $categorySectionOutro;
	protected $categorySectionAppendix;
	protected $preloadArticles;
	
	public $noAutomaticFormEdit; 	//value of 'no automatic formedit' property
	public $useDisplayTemplate; 		//Value of 'use display template' property
	public $useCSSClass; 					//value of 'use_class' property
	public $notDisjointWith; 				//value of 'not_disjoint_with' property value
	protected $usePreloadArticles;
	protected $usePageNameTemplate;
	protected $hideFreeText;
	
	public $isLeafCategory; //is this one of the original instance annotations
	
	public $standardInputs = array();
	
	
	
	public function __construct($categoryTitleObject, $categorySectionStructure){
		$this->titleObject = $categoryTitleObject;
		
		$this->isLeafCategory = count($categorySectionStructure->children) == 0 ? true : false;

		$store = smwfGetStore();
		$this->semanticData = ASFFormGeneratorUtils::getSemanticData($this->titleObject);
		
		$this->initializeFormCreationMetadata();
		
		$this->initializePropertiesFormData($categorySectionStructure->includesCategories);
		
		$this->sortProperties();
	}
	
	/*
	 * Initializes the propertiesFormData field
	 */
	private function initializePropertiesFormData($includedCategories){
		
		if(is_null($includedCategories)){
			//this category has no super categories or they all already have their own section
			$superCategoryTitles = array();
		} else {
			//create property input fields for selected categories
			
			$superCategoryTitles = array();
			foreach($includedCategories as $categoryName => $dontCare){
				$superCategoryTitles[$categoryName] = Title::newFromText($categoryName, NS_CATEGORY);
			}
		}
		
		$propertyTitles = 
			ASFFormGeneratorUtils::getPropertiesWithDomain(array_merge($superCategoryTitles, 
			array($this->titleObject->getText() => $this->titleObject)));
		
		$this->propertiesFormData = array();
		foreach($propertyTitles as $pT){
			$this->propertiesFormData[$pT->getText()] = new ASFPropertyFormData($pT);
		}
	}
	
	/*
	 * Extracts metadata from the ontology and sets the fields of this form input field
	 */
	private function initializeFormCreationMetadata(){
		$this->noAutomaticFormEdit = 
			ASFFormGeneratorUtils::getPropertyValue($this->semanticData, ASF_PROP_NO_AUTOMATIC_FORMEDIT);
		$this->useCSSClass = 
			ASFFormGeneratorUtils::getPropertyValue($this->semanticData, ASF_PROP_USE_CLASS);
		// $this->notDisjointWith = 
		//	ASFFormGeneratorUtils::getPropertyValue($this->semanticData, ASF_PROP_NOT_DISJOINT_WITH, true);
			
		if($this->isLeafCategory){
			$this->useDisplayTemplate = 
				ASFFormGeneratorUtils::getInheritedPropertyValue($this->semanticData, ASF_PROP_USE_DISPLAY_TEMPLATE);
			$this->usePreloadArticles = 
				ASFFormGeneratorUtils::getInheritedPropertyValue($this->semanticData, ASF_PROP_PRELOAD);
			$this->usePageNameTemplate = 
				ASFFormGeneratorUtils::getInheritedPropertyValue($this->semanticData, ASF_PROP_PAGE_NAME_TEMPLATE);
			$this->hideFreeText = 
				ASFFormGeneratorUtils::getInheritedPropertyValue($this->semanticData, ASF_PROP_HIDE_FREE_TEXT);
			
			$standardInputs = 
				ASFFormGeneratorUtils::getInheritedPropertyValue($this->semanticData, ASF_PROP_USE_STANDARD_INPUT, true);
			foreach($standardInputs as $sI){
				$this->standardInputs[lcfirst($sI)] = true;
			}
				
		} else {
			$this->useDisplayTemplate = array();
			$this->usePreloadArticles = array();
			$this->usePageNameTemplate = array();
			$this->hideFreeText = array();
		}
	}
	
	/*
	 * sort properties first by sequence numbers and then
	 * in alphabetic order
	 */
	protected function sortProperties(){
		$maxSequenceNumber = 0;
		foreach($this->propertiesFormData as $prop){
			if($prop->fieldSequenceNumber) {
				$maxSequenceNumber = $maxSequenceNumber > $prop->fieldSequenceNumber 
					? $maxSequenceNumber : $prop->fieldSequenceNumber; 
			}
		}
		
		//prefix property names with sequence numbers with 0s
		//and prefix other properties with 1s
		$prefixedPropertiesFormData = array();
		foreach($this->propertiesFormData as $prop){
			if($prop->fieldSequenceNumber) {
				$prefixLength = strlen($maxSequenceNumber) - strlen($prop->fieldSequenceNumber);
				$prefix = "0";
				for($i=0; $i < $prefixLength; $i++){
					$prefix .= "0";
				}
				$prefixedPropertiesFormData[$prefix.$prop->fieldSequenceNumber.'1'.$prop->inputLabel] = $prop;
			} else {
				$prefixedPropertiesFormData['1'.$prop->inputLabel] = $prop;
			}
		}
		
		$this->propertiesFormData = $prefixedPropertiesFormData;
		
		ksort($this->propertiesFormData);
		
		//echo('<pre>'.print_r(array_keys($propertiesFormData), true).'</pre>');
	}
	
	/*
	 * Generates the category section intro if has not
	 * already been set or created
	 */
	public function getCategorySectionIntro(){
		if(!is_null($this->categorySectionIntro)) return $this->categorySectionIntro;
		
		if($this->isEmptyCategory()){
			return '';
		}
		
		//create field input label text
		global $asfDisplayPropertiesAndCategoriesAsLinks;
		if($asfDisplayPropertiesAndCategoriesAsLinks){
			global $asfShowTooltips;
			$categoryLabel = "";
			if($asfShowTooltips){
				$categoryLabel .= '{{#qTip:';
			}
			$categoryLabel .= ASFFormGeneratorUtils::createParseSaveLink(
				$this->titleObject->getFullText(), $this->titleObject->getText());
			if($asfShowTooltips){
				$categoryLabel .= '| '.$this->getCategoryTooltip().'}}';
			}
		} else {
			$categoryLabel = "<i>".$this->titleObject->getText()."</i>";
		}
		
		$intro='<div';
		if($this->useCSSClass){
			$intro .= ' class="'.$this->useCSSClass.'"';
		}
		$intro.='>';
		
		//create collapsed version of section
		$intro .= "{{#collapsableFieldSetStart:";
		$intro .= "\n".wfMsg('asf_category_section_label', $categoryLabel);
		$intro .= "\n}}";
		
		$intro .= "\n\n".'{| width="100%" align="center"';

		$intro .= ' class="formtable"';
		
		$intro .= "\n";
		$intro .= "|-";
		$intro .= "\n".'| width="20%" |';
		$intro .= "\n".'| width="80%" |';
		$intro .= "\n|";
		
		
		$this->categorySectionIntro = $intro;
		return $this->categorySectionIntro;
	}
	
	
	/*
	 * Generates the category section outro if has not
	 * already been set or created
	 */
	public function getCategorySectionOutro(){
		if(!is_null($this->categorySectionOutro)) return $this->categorySectionOutro;
		
		if($this->isEmptyCategory()){
			return '';
		}
		
		$outro = "\n|}";
		
		$outro .= "{{#collapsableFieldSetEnd:}}";
		
		$outro.='</div>';
		
		$this->categorySectionOutro = $outro;
		return $this->categorySectionOutro;
	}
	
	/*
	 * Generates the category section from the intro,
	 * the outro and the property input field rows
	 */
	public function getCategorySection(){
		$section = $this->getCategorySectionIntro();
		
		foreach($this->propertiesFormData as $p){
			$section .= $p->getFormFieldRow();
		}
		
		$section .= $this->getCategorySectionOutro();

		return $section;
	}
	
	/*
	 * Set the category section intro
	 */
	public function setCategorySectionIntro($categorySectionIntro){
		$this->categorySectionIntro = $categorySectionIntro;
	}

	/*
	 * Set the category section outro
	 */
	public function setCategorySectionOutro($categorySectionOutro){
		$this->categorySectionOutro = $categorySectionOutro;
	}
	
	/*
	 * Set the category section appendix, which will be part of the category definition outro
	 */
	public function setCategorySectionAppendix($categorySectionAppendix){
		$this->categorySectionAppendix = $categorySectionAppendix;
	}
	
	/*
	 * Get the category section appendix, which will be part of the category definition outro
	 */
	public function getCategorySectionAppendix(){
		if(!is_null($this->categorySectionAppendix)) return $this->categorySectionAppendix;
		
		$this->categorySectionAppendix = array();
		
		if($this->isLeafCategory){
			foreach($this->useDisplayTemplate as $displayTemplate){	
				if(strtolower($displayTemplate) != 'false'){
					$appendix = "{{{for template| ".$displayTemplate."}}} ";
					$appendix .= '{{{field |categories|hidden}}}';
					$appendix .= "{{{end template}}}";
					
					$this->categorySectionAppendix[$displayTemplate] 
						= $appendix;
				}
			}
		}

		return $this->categorySectionAppendix;		
	}
	
	public function getPreloadingArticles(){
		if(!is_null($this->preloadArticles)) return $this->preloadArticles;
		
		$this->preloadArticles = array();
		
		foreach($this->usePreloadArticles as $a){
			if(strtolower($a) != 'false'){
				$this->preloadArticles[$a] = true;
			}
		}
		
		return $this->preloadArticles;
	}
	
	private function getCategoryTooltip(){
		return wfMsg('asf_tt_intro', $this->titleObject->getFullText());
	}
	
	public function isEmptyCategory(){
		$isEmpty = true;
		
		foreach($this->propertiesFormData as $prop){
			if(!$prop->isHiddenProperty()) $isEmpty = false;
			break;
		}
		
		return $isEmpty;
	}
	
	public function getPageNameTemplate(){
		$isDefaultPageNameTemplate = true;
		$pageNameTemplate = '';
		
		if($this->isLeafCategory){
			foreach($this->usePageNameTemplate as $template){
				if(strtolower($template) != 'false'){
					$isDefaultPageNameTemplate = false;
					$pageNameTemplate .= ' '.$template;
				}
			}
		
			if($isDefaultPageNameTemplate){
				$pageNameTemplate = $this->titleObject->getText();
			}
		}
		
		$pageNameTemplate = str_replace( 
			array( '&lt;', '&gt;', '&#160;', '&#x003D;', '&#x0027;', '&#58;', "<br />" ),	
			array( '<', '>', ' ', '=', "'", ':', "\n" ), 
			$pageNameTemplate );
		
		return array($isDefaultPageNameTemplate, trim($pageNameTemplate));
	}
	
	
	public function hideFreeText(){
		foreach($this->hideFreeText as $hide){
			if(strtolower($hide) == 'true'){
				return true;
			}
		}
		return false;
	}
	
	public function updateDueToExistingAnnotations($existingAnnotations){
		foreach($existingAnnotations as $propertyName => $values){
			if(count($values['values']) > 1){
				foreach($this->propertiesFormData  as $key => $propertyFormData){
					if($propertyName == $propertyFormData->titleObject->getText()){
						if(strlen(implode(' ', $values['values'])) > 80){
							$inputType = 'textarea';
						} else {
							$inputType = 'text';
						}
						$this->propertiesFormData[$key]->explicitInputType = $inputType;
						$this->propertiesFormData[$key]->forceList();	
					}
				}
			}
		}
	}	
	
	
	
	
}