<?php
/*
 * Copyright (C) Vulcan Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program.If not, see <http://www.gnu.org/licenses/>.
 *
 */


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
	public $hideProperty;
	private $forceList;
	private $userAutocompletionConstraint;
	private $useAutogrow;
	
	
	
	/*
	 * Constructor extracts metadata from property annotations
	 * and fills fields of this object
	 */
	public function __construct($propertyTitleObject){
		$this->titleObject = $propertyTitleObject;
		
		$store = smwfGetStore();
		$this->semanticData = ASFFormGeneratorUtils::getSemanticData($this->titleObject);
		
		$this->initializeFormCreationMetadata();
	}
	
	/*
	 * Extracts metadata from the ontology and sets the fields of this form input field
	 */
	private function initializeFormCreationMetadata(){
		$this->objectType = 
			ASFFormGeneratorUtils::getPropertyValue($this->semanticData, ASF_PROP_HAS_TYPE);
		if(strlen($this->objectType) > 0){	
			$this->objectType = SMWDataValueFactory::findTypeLabel(
				SMWDIUri::doUnserialize($this->objectType)->getFragment());	
		}
			
		$this->autocompletionRange = 
			ASFFormGeneratorUtils::getPropertyValueOfTypeRecord($this->semanticData, ASF_PROP_HAS_DOMAIN_AND_RANGE,ASF_PROP_HAS_RANGE);
		
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
		$this->hideProperty = 
			ASFFormGeneratorUtils::getPropertyValue($this->semanticData, ASF_PROP_NO_AUTOMATIC_FORMEDIT, false, false);
		$this->userAutocompletionConstraint = 
			ASFFormGeneratorUtils::getPropertyValue($this->semanticData, ASF_PROP_AUTOCOMPLETE_ON, false, false);
		$this->useAutogrow = 
			ASFFormGeneratorUtils::getPropertyValue($this->semanticData, ASF_PROP_USE_AUTOGROW, false, false);
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
			
		//add special class, so that width=100% can be applied in JS
		$useClass = '';
		if($size == ASF_LONG_TEXT_SIZE && $inputType =='text'
				|| $inputType == 'textarea'){
			$useClass = '| class=asf-complete-width';
		}
			
		//deal with autocompletion
		global $asfUseHaloAutocompletion;
		if($this->autocompletionRange && $autocompletion == 'category'){
			$this->autocompletionRange = substr($this->autocompletionRange, strpos($this->autocompletionRange, ':')+1);
			if($asfUseHaloAutocompletion){
				if($inputType == 'text') $inputType = 'haloACtext';
				if($inputType == 'textarea') $inputType = 'haloACtextarea';
				$autocompletion = ' |constraints=instance-property-range: '.$this->titleObject->getFullText();
			} else {
				$autocompletion = ' |autocomplete on category='.$this->autocompletionRange;
			}
		} else if ($autocompletion == 'values'){
			if($asfUseHaloAutocompletion){
				if($inputType == 'text') $inputType = 'haloACtext';
				if($inputType == 'textarea') $inputType = 'haloACtextarea';
				$autocompletion = ' |constraints=annotation-value: '.$this->titleObject->getFullText();
			} else {
				$autocompletion = ' |autocomplete';
			}
		} else if($autocompletion == 'category'){
			if($asfUseHaloAutocompletion){
				if($inputType == 'text') $inputType = 'haloACtext';
				if($inputType == 'textarea') $inputType = 'haloACtextarea';
				$autocompletion = ' |constraints=all';
			} else {
				$autocompletion = ' |autocomplete';
			}
		} else {
			$autocompletion = '';
		}
		
		//overwrite computed AC with user constraint
		if($asfUseHaloAutocompletion && $this->userAutocompletionConstraint){
			$this->userAutocompletionConstraint = strtolower(substr($this->userAutocompletionConstraint, 0, 1)).
				substr($this->userAutocompletionConstraint, 1);
			$this->userAutocompletionConstraint = str_replace(
				array('((((', '))))'),
				array('[[', ']]'),
				$this->userAutocompletionConstraint);
			$autocompletion = ' |constraints='.$this->userAutocompletionConstraint;
		}
		
		//deal with validator
		global $asfUseSemanticFormsInputsFeatures;
		$regexp = '';
		if($this->validator && class_exists('SFIInputs') && $asfUseSemanticFormsInputsFeatures){
			$inputType = 'regexp';
			$regexp = ' |regexp='.$this->validator;
		}
		
		$autocompletion .= '|pasteNS=true';
		
			
		$syntax .= ' |input type='.$inputType;
		$syntax .= $useClass;
		if($size) $syntax .= ' |size='.$size;
		if($rows) $syntax .= ' |rows='.$rows;
		if($cols) $syntax .= ' |cols='.$cols;
		if($values) $syntax .= ' |values='.$values;
		if($this->useAutogrow) $syntax .= ' |autogrow';
		
		//deal with autocompletion
		$syntax .= $autocompletion;
		
		//deal with uploadable
		if($this->isUploadable){
			$syntax .= ' |uploadable';
		}
		
		//deal with validator
		$syntax .= $regexp;
		

		//deal with delimiters
		if($this->delimiter){
			$syntax .= ' |delimiter='.$this->delimiter;
		}
		
		//deal with multi value input fields
		if($this->maxCardinality != 1 || $this->delimiter || $this->forceList()){
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
		
		if($this->isWriteProtected()){
			$syntax .= '<span class="asf-write-protected"></span>';			
		}
		
		//deal with form input help
		if($this->helpText){
			$syntax .= '{{#qTipHelp:';
			$syntax .= $this->helpText;
			$syntax .= '}}';	
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
		
		if($this->cssClass){
			$intro .= ' class="'.$this->cssClass.'" ';
		}
		
		$intro .= "\n".'| valign="top" -|';		
		
		global $asfShowTooltips;
		if($asfShowTooltips){
			$intro .= '{{#qTip:';
		}
		
		//add form field label
		global $asfDisplayPropertiesAndCategoriesAsLinks;
		if($asfDisplayPropertiesAndCategoriesAsLinks){
			$intro .= ASFFormGeneratorUtils::createParseSaveLink(
				$this->titleObject->getFullText(), $this->inputLabel);
		} else {
			$intro .= '<span class="asf_input_label">'.$this->inputLabel . ':</span>';
		}
		
		//deal with the red mandatory asterisc
		if($this->minCardinality){
			$intro .= '<span style="color: red">*</span>';
		}
		
		if($asfShowTooltips){
			//Create the tooltip:
			$intro .= "\n| ";
			$intro .= $this->getPropertyToolTip();
			$intro .= '}}';
		}
		
		$intro .= "\n".'| valign="top" |';
		
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
		if(strtolower($this->hideProperty) == 'true'){
			return '';
		}
		
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
private function getFormFieldInputTypeMetadata($forToolTip = false){
		$inputType = '';
		$size = ASF_LONG_TEXT_SIZE;
		$rows = false;
		$cols = false;
		$autocompletion = false;
		$values = false;
		
		if($this->explicitInputType){
			$inputType = strtolower($this->explicitInputType);
			if($inputType == 'datepicker' || $inputType == 'datetimepicker'){
				$size = '';
			}
			$objectType = '-'.strtolower($this->objectType).'-';
			if(strpos(LONGTEXTDATATYPES, $objectType) !== false
					|| strpos(SHORTTEXTDATATYPES, $objectType) !== false){
				$autocompletion = 'values';
			} else{
				$autocompletion = 'category';
			}
		} else {
			$objectType = '-'.strtolower($this->objectType).'-';
			if(strpos(LONGTEXTDATATYPES, $objectType) !== false){
				$inputType = 'text';
				$size = ASF_LONG_TEXT_SIZE;
				$autocompletion = 'values';
			} else if(strpos(SHORTTEXTDATATYPES, $objectType) !== false){
				$inputType = 'text';
				$size = ASF_SHORT_TEXT_SIZE;
				$autocompletion = 'values';
			} else if(strpos(TEXTAREADATATYPES, $objectType) !== false){
				$inputType = 'textarea';
				$rows = ASF_TEXTAREA_ROWS;
				$cols = ASF_TEXTAREA_COLS;
			} else if(strpos(DATETIMEDATATYPES, $objectType) !== false){
				global $asfUseSemanticFormsInputsFeatures;
				if(class_exists('SFIInputs') && $asfUseSemanticFormsInputsFeatures){
					$inputType = 'datetimepicker';
					$size = '';
				} else {
					$inputType = 'datetime';
				}
			} else if(strpos(CHECKBOXDATATYPES, $objectType) !== false){
				$inputType = 'checkbox';
			} else {
				$inputType = 'text';
				$size = ASF_LONG_TEXT_SIZE;
				$autocompletion = 'category';
			}
		}
		
		if($this->allowsValues){
			if(!$this->explicitInputType){
				$inputType = 'dropdown';
			}
			$values = implode(',', $this->allowsValues);
		}
		
		if($forToolTip){
			return $autocompletion;	
		} else {
			return array($inputType, $size, $rows, $cols, $autocompletion, $values);
		}
	}
	
	/*
	 * Returns the input field label tooltip
	 */
	private function getPropertyToolTip(){
		$result = "";
		
		global $asfDisplayPropertiesAndCategoriesAsLinks;
		if($asfDisplayPropertiesAndCategoriesAsLinks){
			$result .= wfMsg('asf_tt_intro', $this->titleObject->getFullText());
		}
		
		$additionalTips = "";
		
		if($this->objectType){
			$additionalTips .= '<li>'.wfMsg('asf_tt_type', $this->objectType).'</li>';
		}
		
		$autocompletion = $this->getFormFieldInputTypeMetadata(true);
		if($this->autocompletionRange && $autocompletion == 'category'){
			$additionalTips .= '<li>'.wfMsg('asf_tt_autocomplete', $this->autocompletionRange).'</li>';
		}

		if($this->maxCardinality != 1 || $this->delimiter){
			$delimiter = ($this->delimiter) ? $this->delimiter : ',';
			$additionalTips .= '<li>'.wfMsg('asf_tt_delimiter', trim($delimiter)).'</li>';
		}		

		if(strlen($additionalTips) > 0){
			$additionalTips = '<ul>'.$additionalTips.'</ul>';
		}
		
		$result .= $additionalTips;
		
		return $result;
	}
	
	public function isHiddenProperty(){
		return strtolower($this->hideProperty) == 'true' ? true : false;	
	}
	
	protected function isWriteProtected(){
		if(defined('HACL_HALOACL_VERSION')){
			
			global $wgUser;
				
			$propertyId = $this->titleObject->getArticleID();
				 	
			$isWriteProtected = !HACLEvaluator::hasPropertyRight($propertyId, 
				$wgUser->getId(), HACLRight::CREATE);
						
			$isEditProtected = !HACLEvaluator::hasPropertyRight($propertyId, 
				$wgUser->getId(), HACLRight::EDIT);

			if($isWriteProtected || $isEditProtected){
			return true;
		}
		
		return false;
		}
	}
	
	public function forceList(){
		$this->forceList = true;
	}

	
}
