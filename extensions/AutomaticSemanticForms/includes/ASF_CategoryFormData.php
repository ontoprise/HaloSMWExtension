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
	
	public $noAutomaticFormEdit; 	//value of 'no automatic formedit' property
	public $useDisplayTemplate; 		//Value of 'use display template' property
	public $useCSSClass; 					//value of 'use_class' property
	public $notDisjointWith; 				//value of 'not_disjoint_with' property value
	
	
	
	public function __construct($categoryTitleObject, $includedCategories = null){
		$this->titleObject = $categoryTitleObject;

		global $smwgBaseStore;
		$store = new $smwgBaseStore();
		$this->semanticData = $store->getSemanticData($this->titleObject);
		
		$this->initializeFormCreationMetadata();
		
		$this->initializePropertiesFormData($includedCategories);
		
		$this->sortProperties();
	}
	
	/*
	 * Initializes the propertiesFormData field
	 */
	private function initializePropertiesFormData($includedCategories){
		if(is_null($includedCategories)){
			//create property input fields for all super categories
			
			//get super categories
			$superCategoryTitles = ASFFormGeneratorUtils::getSuperCategories($this->titleObject);
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
		$this->useDisplayTemplate = 
			ASFFormGeneratorUtils::getPropertyValue($this->semanticData, ASF_PROP_USE_DISPLAY_TEMPLATE);
		$this->useCSSClass = 
			ASFFormGeneratorUtils::getPropertyValue($this->semanticData, ASF_PROP_USE_CLASS);
		$this->notDisjointWith = 
			ASFFormGeneratorUtils::getPropertyValue($this->semanticData, ASF_PROP_NOT_DISJOINT_WITH, true);
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
		
		if(count($this->propertiesFormData) == 0){
			return '';
		}
		
		global $asfDisplayPropertiesAndCategoriesAsLinks;
		if($asfDisplayPropertiesAndCategoriesAsLinks){
			$categoryLabel = '[[:'.$this->titleObject->getFullText().'|'.$this->titleObject->getText().']]';
		} else {
			$categoryLabel = "<i>".$this->titleObject->getText()."</i>";
		}
		
		$intro = "<fieldset>\n\n";
		$intro .= "<legend>";
		$intro .= wfMsg('asf_category_section_label', $categoryLabel);
		$intro .= "</legend>";
		
		$intro .= "\n\n{|";

		if($this->useCSSClass){
			$intro .= ' class="'.$this->useCSSClass.'"';
		}
		
		$intro .= "\n";
		
		$this->categorySectionIntro = $intro;
		return $this->categorySectionIntro;
	}
	
	
	/*
	 * Generates the category section outro if has not
	 * already been set or created
	 */
	public function getCategorySectionOutro(){
		if(!is_null($this->categorySectionOutro)) return $this->categorySectionOutro;
		
		if(count($this->propertiesFormData) == 0){
			return '';
		}
		
		$outro = "\n|}\n";
		
		$outro .= "</fieldset>\n\n";
		
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
		
		$appendix = "";
		
		if($this->useDisplayTemplate){
			$appendix .= "\n\n{{{for template| ".$this->useDisplayTemplate."}}} {{{end template}}}";
		}

		$this->categorySectionAppendix = $appendix;
		
		return $appendix;		
	}
}