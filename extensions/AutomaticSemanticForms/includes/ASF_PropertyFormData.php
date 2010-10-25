<?php

/*
 * Represents the form input field for a property
 * 
 * Provides the underlying metadata and creates the
 * form field input syntax
 */
class ASFPropertyFormData {
	
	public $titleObject;
	public $semanticData;
		
	private $formFieldSyntax;
	private $formFieldIntro;
	private $formFieldOutro;
	
	public $objectType; 						//value of 'has type' property
	public $autocompletionRange; 	//value of range from 'has domain and range' property 
	public $minCardinality; 				//value of 'has min cardinality' property
	public $maxCardinality; 				//value of 'has max cardinality' property
	public $allowsValues;			 	//value of 'allows values' property
	public $inputLabel; 						//value of 'Form_input_label' property
	public $explicitInputType; 			//value of  'Use_input_type' property
	public $isUploadable;  				//value of  'Is_uploadable' property
	public $helpText; 							//value of  'Form_input_help' property
	public $validator; 							//value of  'Validator' property
	public $cssClass; 							//value of  'Use_class' property
	public $delimiter; 							//value of  'Delimiter' property
	public $fieldSequenceNumber; 	//value of  'Field_sequence_number' property
	public $defaultValue;					//use a default value
	
	
	/*
	 * Constructor extracts metadata from property annotations
	 * and fills fields of this object
	 */
	public function __construct($propertyTitleObject){
		$this->titleObject = $propertyTitleObject;
		
		global $smwgBaseStore;
		$store = new $smwgBaseStore();
		$this->semanticData = $store->getSemanticData($this->titleObject);
		
		$this->initializeFormCreationMetadata();
	}
	
	/*
	 * Extracts metadata from the ontology and sets the fields of this form input field
	 */
	private function initializeFormCreationMetadata(){
		$this->objectType = 
			ASFFormGeneratorUtils::getPropertyValue($this->semanticData, ASF_PROP_HAS_TYPE);
		$this->autocompletionRange = 
			ASFFormGeneratorUtils::getPropertyValueOfTypeRecord($this->semanticData, ASF_PROP_HAS_DOMAIN_AND_RANGE,1);
		$this->minCardinality = 
			ASFFormGeneratorUtils::getPropertyValue($this->semanticData, ASF_PROP_HAS_MIN_CARDINALITY);
		$this->maxCardinality = 
			ASFFormGeneratorUtils::getPropertyValue($this->semanticData, ASF_PROP_HAS_MAX_CARDINALITY);
		$this->allowsValues = 
			ASFFormGeneratorUtils::getPropertyValue($this->semanticData, ASF_PROP_ALLOWS_VALUES, true);
		$this->inputLabel = 
			ASFFormGeneratorUtils::getPropertyValue($this->semanticData, ASF_PROP_FORM_INPUT_LABEL, false, $this->titleObject->getText());
		$this->explicitInputType = 
			ASFFormGeneratorUtils::getPropertyValue($this->semanticData, ASF_PROP_USE_INPUT_TYPE);
		$this->isUploadable = 
			ASFFormGeneratorUtils::getPropertyValue($this->semanticData, ASF_PROP_IS_UPLOADABLE);
		$this->helpText = 
			ASFFormGeneratorUtils::getPropertyValue($this->semanticData, ASF_PROP_FORM_INPUT_HELP);
		$this->validator = 
			ASFFormGeneratorUtils::getPropertyValue($this->semanticData, ASF_PROP_VALIDATOR);
		$this->cssClass = 
			ASFFormGeneratorUtils::getPropertyValue($this->semanticData, ASF_PROP_USE_CLASS);
		$this->delimiter = 
			ASFFormGeneratorUtils::getPropertyValue($this->semanticData, ASF_PROP_DELIMITER);
		$this->fieldSequenceNumber = 
			ASFFormGeneratorUtils::getPropertyValue($this->semanticData, ASF_PROP_FIELD_SEQUENCE_NUMBER);
		$this->defaultValue = 
			ASFFormGeneratorUtils::getPropertyValue($this->semanticData, ASF_PROP_DEFAULT_VALUE);
	}
	
	
	/*
	 * Generates the form field syntax if it has not yet been set
	 * or generated
	 */
	public function getFormFieldSyntax(){
		if(!is_null($this->formFieldSyntax)) return $this->formFieldSyntax;
		
		$syntax = "{{{field";
		
		$syntax .= '|'.$this->titleObject->getText();
		
		//deal with input type
		list($inputType, $size, $rows, $cols, $autocompletion, $values) = 
			$this->getFormFieldInputTypeMetadata();
		$syntax .= ' |input type='.$inputType;
		if($size) $syntax .= ' |size='.$size;
		if($rows) $syntax .= ' |rows='.$rows;
		if($cols) $syntax .= ' |cols='.$cols;
		if($values) $syntax .= ' |values='.$values;
		
		//deal with autocompletion
		if($this->autocompletionRange && $autocompletion == 'category'){
			$this->autocompletionRange = substr($this->autocompletionRange, strpos($this->autocompletionRange, ':')+1);
			$syntax .= ' |autocomplete on category='.$this->autocompletionRange;
		} else if ($autocompletion == 'values'){
			$syntax .= ' |autocomplete';
		}
		
		//deal with uploadable
		if($this->isUploadable){
			$syntax .= ' |uploadable';
		}
		
		//deal with validator
		global $asfUseSemanticFormsInputsFeatures;
		if($this->validator && $asfUseSemanticFormsInputsFeatures){
			$syntax .= ' |regexp='.$this->validator;
		}

		//deal with CSS classes
		if($this->cssClass){
			$syntax .= ' |class='.$this->cssClass;
		}
		
		//deal with delimiters
		if($this->delimiter){
			$syntax .= ' |delimiter='.$this->delimiter;
		}
		
		//deal with multi value input fields
		if($this->minCardinality > 1 || $this->maxCardinality > 1 || $this->delimiter){
			$syntax .= ' |list';	
		}
		
		//deal with mandatory input fields
		if($this->minCardinality){
			$syntax .= ' |mandatory';
		}
		
		//deal with default values
		if($this->defaultValue){
			if($this->defaultValue == "Now") $this->defaultValue = "now";
			$syntax .= ' |default='.$this->defaultValue;
		}
		
		$syntax .= '}}}';
		
		//deal with form input help
		if($this->helpText){
			//TODO: Use a help icon instead
			$syntax .= ' <img src="ASF_HELP_ICON" title="'.$this->helpText.'"></img>';	
		}
		
		$this->formFieldSyntax = $syntax;
		return $this->formFieldSyntax;
	}
	
	/*
	 * Generates the form field intro if it has not
	 * yet been set or created
	 */
	public function getFormFieldIntro(){
		if(!is_null($this->formFieldIntro)) return $this->formFieldIntro;
		
		$intro = "\n|-";
		
		//add form field label
		global $asfDisplayPropertiesAndCategoriesAsLinks;
		if($asfDisplayPropertiesAndCategoriesAsLinks){
			$intro .= "\n|[[".$this->titleObject->getFullText().'|' . $this->inputLabel . ']]:';
		} else {
			$intro .= "\n|" . $this->inputLabel . ':';
		}
		
		$intro .= "\n|";
		
		$this->formFieldIntro = $intro;
		return $this->formFieldIntro;
	}
	
	/*
	 * Generates the outro if it has not yet been
	 * generated or set
	 */
	public function getFormFieldOutro(){
		if(!is_null($this->formFieldOutro)) return $this->formFieldOutro;
		
		$outro = "\n";
				
		$this->formFieldOutro = $outro;
		return $this->formFieldOutro;
	}
	
	
	/*
	 * Get the row of the form field definition for this property
	 * 
	 * The form field row definition consists of an intro, the actual
	 * form field definition and an outro
	 */
	public function getFormFieldRow(){
		$formFieldRow = $this->getFormFieldIntro();
		$formFieldRow .= $this->getFormFieldSyntax();
		$formFieldRow .= $this->getFormFieldOutro();
		return $formFieldRow;
	}
	
	/*
	 * Setter for the form field syntax
	 */
	public function setFormFieldSyntax($formFieldSyntax){
		$this->formFieldSyntax = $formFieldSyntax;
	}
	
	/*
	 * Setter for the form field intro
	 */
	public function setFormFieldintro($formFieldIntro){
		$this->formFieldIntro = $formFieldIntro;
	}
	
	/*
	 * Setter for the form field outro
	 */
	public function setFormFieldOutro($formFieldOutro){
		$this->formFieldOutro = $formFieldOutro;
	}
	
	/*
	 * Helper method for getFormFieldSyntax
	 * Returns input type related data
	 */
	private function getFormFieldInputTypeMetadata(){
		$inputType = '';
		$size = '110';
		$rows = false;
		$cols = false;
		$autocompletion = false;
		$values = false;
		
		if($this->explicitInputType){
			//todo: what id explicit input type needs several parameters?
			$inputType = strtolower($this->explicitInputType);
		} else {
			$objectType = '-'.strtolower($this->objectType).'-';
			if(strpos(LONGTEXTDATATYPES, $objectType) !== false){
				$inputType = 'text';
				$size = '110';
				$autocompletion = 'values';
			} else if(strpos(SHORTTEXTDATATYPES, $objectType) !== false){
				$inputType = 'text';
				$size = '30';
				$autocompletion = 'values';
			} else if(strpos(TEXTAREADATATYPES, $objectType) !== false){
				$inputType = 'textarea';
				$rows = '5';
				$cols = '78';
			} else if(strpos(DATETIMEDATATYPES, $objectType) !== false){
				//TODO deal with datepicker
				global $asfUseSemanticFormsInputsFeatures;
				if(class_exists('SFITSettings') && $asfUseSemanticFormsInputsFeatures){
					$inputType = 'datepicker';
				} else {
					$inputType = 'datetime';
				}
			} else if(strpos(CHECKBOXDATATYPES, $objectType) !== false){
				$inputType = 'checkbox';
			} else {
				$inputType = 'text';
				$size = '110';
				$autocompletion = 'category';
			}
		}
		
		if($this->allowsValues){
			$values = implode(',', $this->allowsValues);
		}
		
		return array($inputType, $size, $rows, $cols, $autocompletion, $values);
	}
}