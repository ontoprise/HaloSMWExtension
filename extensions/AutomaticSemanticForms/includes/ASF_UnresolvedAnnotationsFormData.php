<?php

define('ASF_ALLOWED_EXPLICIT_INPUT_TYPES', '-text- -textarea-');
/*
 * This class provides the form definition syntax for the section, which
 * holds the form input fields for unresolved annotations
 */
class ASFUnresolvedAnnotationsFormData extends ASFCategoryFormData {
	
	/*
	 * Discovers unresolved annotations and initializes the corresponding
	 * property form fields
	 */
	public function __construct($instanceTitleObject, $categoryFormDataInstances = array()){
		if(!$instanceTitleObject->exists()){
			$this->propertiesFormData = array();
			return;
		}
		
		$silentAnnotations = $this->getSilentAnnotations($instanceTitleObject);
		
		$unresolvedAnnotations = 
			$this->getUnresolvedAnnotations($categoryFormDataInstances, $silentAnnotations);
		
		$this->initializePropertiesFormData($unresolvedAnnotations);
		
		$this->setPropertyFormFieldsInputTypes();
		
		$this->sortProperties();
	}
	
	/*
	 * Get all annotations made with the silent annotations parser function
	 */
	private function getSilentAnnotations($instanceTitleObject){
		global $asfSilentAnnotations, $wgParser;
		$asfSilentAnnotations = array();
		
		$wgParser->startExternalParse($title, new ParserOptions(), Parser::OT_HTML);
		$wgParser->replaceVariables(Article::newFromID($instanceTitleObject->getArticleID())->getContent());
		
		return $asfSilentAnnotations;
	}
	
	
	/*
	 * Find all silent annotations which are not covered by one
	 * of the other category form data sections
	 */
	private function getUnresolvedAnnotations($categoryFormDataInstances, $silentAnnotations){
		foreach($categoryFormDataInstances as $categoryData){
			foreach($categoryData->propertiesFormData as $propertyFormData){
				$propertyName = $propertyFormData->titleObject->getText();
				if(array_key_exists($propertyName, $silentAnnotations)){
					unset($silentAnnotations[$propertyName]);
				}
			}
		}
		
		return $silentAnnotations;
	}
	
	/*
	 * Initialize the property form data for unresolved annotations
	 */
	private function initializePropertiesFormData($unresolvedAnnotations){
		
		$this->propertiesFormData = array();
		foreach($unresolvedAnnotations as $name => $dontCare){
			$this->propertiesFormData[$name] = 
				new ASFPropertyFormData(Title::newFromText($name, SMW_NS_PROPERTY));
		}
		
	}
	
	/*
	 * Create the section intro
	 */
	public function getCategorySectionIntro(){
		if(!is_null($this->categorySectionIntro)) return $this->categorySectionIntro;
		
		if(count($this->propertiesFormData) == 0){
			return '';
		}
		
		//create collapsed version of section
		$sectionId =  'fieldset_instance_annotations';
		$intro = "<fieldset id=\"".$sectionId."_hidden\" style=\"display: none\">";
		$intro .= "<legend>";
		$intro .= '<img src="ASF_PLUS_ICON" onclick="asf_show_category_section(\''.$sectionId.'\')"></img> ';
		$intro .= wfMsg('asf_unresolved_annotations');
		$intro .= "</legend>";
		$intro .= "</fieldset>";
		
		//create expanded version of section
		$intro .= "<fieldset id=\"".$sectionId."_visible\">";
		$intro .= "<legend>";
		$intro .= '<img src="ASF_MINUS_ICON" onclick="asf_hide_category_section(\''.$sectionId.'\')"></img> ';
		$intro .= wfMsg('asf_unresolved_annotations');
		$intro .= "</legend>";
		
		$intro .= "\n\n{|";

		$intro .= "\n";
		
		$this->categorySectionIntro = $intro;
		return $this->categorySectionIntro;
	}
	
	/*
	 * Some form field input types are not allowed in this section,
	 * since they are not suitable for removing an annotation, i.e. checkbox
	 */
	private function setPropertyFormFieldsInputTypes(){
		foreach($this->propertiesFormData as $name => $data){
			
			$setExplicitly = false;
			
			if(!$data->explicitInputType){
				$objectType = '-'.strtolower($data->objectType).'-';
				if(strpos(DATETIMEDATATYPES, $objectType) !== false 
						|| strpos(CHECKBOXDATATYPES, $objectType) !== false){
					
					$setExplicitly = true;
				} 
			} else {
				if(strpos(ASF_ALLOWED_EXPLICIT_INPUT_TYPES, '-'.strtolower($data->explicitInputType).'-')){
					$setExplicitly = true;
				}				
			}
			
			if($setExplicitly){
				$this->propertiesFormData[$name]->explicitInputType = "text";
			}
		}
	}
	
}