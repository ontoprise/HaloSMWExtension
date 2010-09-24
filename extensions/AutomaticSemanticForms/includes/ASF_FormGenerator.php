<?php


define('ASF_PROP_HAS_TYPE', 'Has_type');
define('ASF_PROP_HAS_DOMAIN_AND_RANGE', 'Has_domain_and_range');
define('ASF_PROP_HAS_MIN_CARDINALITY', 'Has_min_cardinality');
define('ASF_PROP_HAS_MAX_CARDINALITY', 'Has_max_cardinality');
define('ASF_PROP_FORM_INPUT_LABEL', 'Form_input_label');
define('ASF_PROP_USE_INPUT_TYPE', 'Use_input_type');
define('ASF_PROP_IS_UPLOADABLE', 'Is_uploadable');
define('ASF_PROP_FORM_INPUT_HELP', 'Form_input_help');
define('ASF_PROP_VALIDATOR', 'Validator');
define('ASF_PROP_USE_CLASS', 'Use_class');
define('ASF_PROP_DELIMITER', 'Delimiter');
define('ASF_PROP_FIELD_SEQUENCE_NUMBER', 'Field_sequence_number');



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
	public function generateFromTitle(Title $title){
		
	}
	
/*
	 * Generates a Semantic Form based on a given
	 * title object and its category annotations
	 */
	public function generateFromCategory($category){
		
		$category = Category::newFromName($category);
		
		$superCategoryTitles = $this->getSuperCategories($category->getTitle());
		
		$propertyTitles = $this->getPropertiesWithDomain($superCategoryTitles);
		
		$propertiesFormData = array();
		foreach($propertyTitles as $pT){
			$propertiesFormData[] = new ASFPropertyFormData($pT);
		}
		$categotyFormData = new ASFCategoryFormData($category->getTitle(), $propertiesFormData);
		
		echo('<pre>'.print_r($categotyFormData->getCategorySection(), true).'</pre>');
		
		error();
		
	}
	
	/*
	 * Get all supercategories of a given category
	 */
	private function getSuperCategories($categoryTitle, $superCategoryTitles = array()){
		
		$directSuperCatgeoryTitles = smwfGetSemanticStore()->getDirectSuperCategories($categoryTitle);
		
		foreach($directSuperCatgeoryTitles as $dSCT){
			$superCategoryTitles[$dSCT->getText()] = $dSCT;
			$superCategoryTitles = $this->getSuperCategories($dSCT, $superCategoryTitles);
		}
		
		return $superCategoryTitles;
	}
	
	/*
	 * get all properties that use at least one of the given categories as domain
	 */
	private function getPropertiesWithDomain($categoryTitles){
		$properties = array();
		
		foreach($categoryTitles as $cT){
			foreach(smwfGetSemanticStore()->getPropertiesWithDomain($cT) as $p){
				$properties[$p->getText()] = $p;
			} 
		}
		
		return $properties;
	}
	
	private function getFormFields($propertyTitles){
		$formFields = array();
		
		foreach($propertyTitles as $p){
			
		}
	}
}

class ASFCategoryFormData {
	private $titleObject;
	
	private $propertiesFormData;
	
	private $categorySectionIntro;
	private $categorySectionOutro;
	
	public function __construct($categoryTitleObject, $propertiesFormData){
		$this->titleObject = $categoryTitleObject;
		$this->propertiesFormData = $propertiesFormData;
	}
	
	public function getCategorySectionIntro(){
		if(!is_null($this->categorySectionIntro)) return $this->categorySectionIntro;
		
		//todo: use language file
		
		$intro = "\n{{{for template| ASFSilentAnnotationsTemplate";
		
		$intro .= " |label=Enter ";
		$intro .= $this->titleObject->getText();
		$intro .= " data:";
		$intro .= "}}}";
		
		$intro .= "\n\n{|\n";		
		
		$this->categorySectionIntro = $intro;
		return $this->categorySectionIntro;
	}
	
	public function getCategorySectionOutro(){
		if(!is_null($this->categorySectionOutro)) return $this->categorySectionOutro;
		
		$outro = "\n|}\n";
		
		$outro .= "\n\n{{{end template}}}";
				
		$this->categorySectionOutro = $outro;
		return $this->categorySectionOutro;
	}
	
	public function getCategorySection(){
		$section = $this->getCategorySectionIntro();
		
		foreach($this->propertiesFormData as $p){
			$section .= $p->getFormFieldRow();
		}
		
		$section .= $this->getCategorySectionOutro();

		return $section;
	}
}

class ASFPropertyFormData {
	
	private $titleObject;
	private $semanticData;
		
	private $formFieldSyntax;
	private $formFieldIntro;
	private $formFieldOutro;
	
	public $objectType;
	public $autocompletionRange;
	public $minCardinality;
	public $maxCardinality;
	public $inputLabel;
	public $explicitInputType;
	public $isUploadable;
	public $helpText;
	public $validator;
	public $cssClass;
	public $delimiter;
	public $fieldSequenceNumber;
	
	
	public function __construct($propertyTitleObject){
		$this->titleObject = $propertyTitleObject;
		
		global $smwgBaseStore;
		$store = new $smwgBaseStore();
		$this->semanticData = $store->getSemanticData($this->titleObject);
		
		$this->initializeFormCreationMetadata();
	}
	
	private function initializeFormCreationMetadata(){
		$this->objectType = $this->getPropertyValue(ASF_PROP_HAS_TYPE);
		$this->autocompletionRange = $this->getPropertyValueOfTypeRecord(ASF_PROP_HAS_DOMAIN_AND_RANGE,1);
		$this->minCardinality = $this->getPropertyValue(ASF_PROP_HAS_MIN_CARDINALITY);
		$this->maxCardinality = $this->getPropertyValue(ASF_PROP_HAS_MAX_CARDINALITY);
		$this->inputLabel = $this->getPropertyValue(ASF_PROP_FORM_INPUT_LABEL);
		$this->explicitInputType = $this->getPropertyValue(ASF_PROP_USE_INPUT_TYPE);
		$this->isUploadable = $this->getPropertyValue(ASF_PROP_IS_UPLOADABLE);
		$this->helpText = $this->getPropertyValue(ASF_PROP_FORM_INPUT_HELP);
		$this->validator = $this->getPropertyValue(ASF_PROP_VALIDATOR);
		$this->cssClass = $this->getPropertyValue(ASF_PROP_USE_CLASS);
		$this->delimiter = $this->getPropertyValue(ASF_PROP_DELIMITER);
		$this->fieldSequenceNumber = $this->getPropertyValue(ASF_PROP_FIELD_SEQUENCE_NUMBER);
	}
	
	private function getPropertyValue($propertyName, $defaultValue = ''){
		$result = $defaultValue;
		
		$properties = $this->semanticData->getProperties();
		
		if(array_key_exists($propertyName, $properties)){
			$values = $this->semanticData->getPropertyValues($properties[$propertyName]);
			$result = $values[0]->getShortWikiText();
		}
		
		//echo('<br/><pre>'.print_r($result, true).'</pre>');
		
		return $result;
	}
	
	private function getPropertyValueOfTypeRecord($propertyName, $index, $defaultValue = ''){
		$result = $defaultValue;
		
		$properties = $this->semanticData->getProperties();
		
		if(array_key_exists($propertyName, $properties)){
			$values = $this->semanticData->getPropertyValues($properties[$propertyName]);
			if($values[0] instanceof SMWRecordValue){
				$dVs = $values[0]->getDVs();
				if(count($dVs) >= $index+1){
					$result = $dVs[$index]->getShortWikiText();
				}
			}
		}
		
		//echo('<br/><pre>'.print_r($result, true).'</pre>');
		
		return $result;
	}
	
	public function getFormFieldSyntax(){
		if(!is_null($this->formFieldSyntax)) return $this->formFieldSyntax;
		
		$syntax = "{{{field";
		
		$syntax .= '|'.$this->titleObject->getText();
		
		//deal with input type
		list($inputType, $size, $rows, $cols, $autocompletion) = $this->getFormFieldInputType();
		$syntax .= ' |input type='.$inputType;
		if($size) $syntax .= ' |size='.$size;
		if($rows) $syntax .= ' |rows='.$rows;
		if($cols) $syntax .= ' |cols='.$cols;
		
		//deal with autocompletion
		if($this->autocompletionRange && $autocompletion == 'category'){
			$this->autocompletionRange = substr($this->autocompletionRange, strpos(':'.$this->autocompletionRange)+1);
			$syntax .= ' |autocomplete on category='.$this->autocompletionRange;
		} else if ($autocompletion == 'values'){
			$syntax .= ' |autocomplete';
		}
		
		//deal with uploadable
		if($this->isUploadable){
			$syntax .= ' |uploadable';
		}
		
		//TODO: Deal with the validator regexp input type

		//deal with CSS classes
		if($this->cssClass){
			$syntax .= ' |class='.$this->cssClass;
		}
		
		//deal with delimiters
		if($this->delimiter){
			$syntax .= ' |delimiter='.$this->delimiter;
		}
		
		//deal with multi value input fields
		if($this->minCardinality || $this->maxCardinality || $this->delimiter){
			//todo: Check if this is the right one to denote a field with multiple values
			$syntax .= ' |list';	
		}
		
		//deal with mandatory input fields
		if($this->minCardinality){
			$syntax .= ' |mandatory';
		}
		
		//todo: deal with the field sequence number
		
		$syntax .= '}}}';
		
		//deal with form input help
		if($this->helpText){
			//TODO: Use a help icon instead
			$syntax .= ' <span title="'.$this->helpText.'">Help</span>';	
		}
		
		$this->formFieldSyntax = $syntax;
		return $this->formFieldSyntax;
	}
	
	public function getFormFieldIntro(){
		if(!is_null($this->formFieldIntro)) return $this->formFieldIntro;
		
		$intro = "\n|-";
		
		//add form field label
		if(!$this->inputLabel){
			$intro .= "\n|" . $this->titleObject->getText() . ':';
		} else {
			$intro .= "\n|" . $this->inputLabel . ':';
		}
		$intro .= "\n|";
		
		$this->formFieldIntro = $intro;
		return $this->formFieldIntro;
	}
	
	public function getFormFieldOutro(){
		if(!is_null($this->formFieldOutro)) return $this->formFieldOutro;
		
		$outro = "\n";
				
		$this->formFieldOutro = $outro;
		return $this->formFieldOutro;
	}
	
	public function getFormFieldRow(){
		$formFieldRow = $this->getFormFieldIntro();
		$formFieldRow .= $this->getFormFieldSyntax();
		$formFieldRow .= $this->getFormFieldOutro();
		return $formFieldRow;
	}
	
	public function setFormFieldSyntax($formFieldSyntax){
		$this->formFieldSyntax = $formFieldSyntax;
	}
	
	public function setFormFieldintro($formFieldIntro){
		$this->formFieldIntro = $formFieldIntro;
	}
	
	public function setFormFieldOutro($formFieldOutro){
		$this->formFieldOutro = $formFieldOutro;
	}
	
	private function getFormFieldInputType(){
		//TODO: DEAL WITH ENUMERATION DATATYPE
		
		$inputType = '';
		$size = false;
		$rows = false;
		$cols = false;
		$autocompletion = false;
		
		
		$text35Types = array('page', 'string');
		$text100Types = array('url', 'email', 'annotation URI', 'telephone number');
		$text10Types = array('number', 'temperature');
		$textAreaTypes = array('text', 'code');
		$dateTypes = array('date');
		$checkboxTypes = array('boolean');
		
		if($this->explicitInputType){
			$inputType = $this->explicitInputType;
		} else {
			if(array_key_exists(strtolower($this->objectType), $text100Types)){
				$inputType = 'text';
				$size = '100';
				$autocompletion = 'values';
			} else if(array_key_exists(strtolower($this->objectType), $text100Types)){
				$inputType = 'text';
				$size = '10';
				$autocompletion = 'values';
			} else if(array_key_exists(strtolower($this->objectType), $text100Types)){
				$inputType = 'textarea';
				$rows = '5';
				$cols = '30';
			} else if(array_key_exists(strtolower($this->objectType), $text100Types)){
				//TODO deal with datepicker
				$inputType = 'date';
			} else if(array_key_exists(strtolower($this->objectType), $text100Types)){
				$inputType = 'checkbox';
			} else {
				$inputType = 'text';
				$size = '35';
				$autocompletion = 'category';
			} 
		}
		
		return array($inputType, $size, $rows, $cols, $autocompletion);
	}
	
		
}

 