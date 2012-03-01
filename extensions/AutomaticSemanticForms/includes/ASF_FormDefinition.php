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


////todo: add documentation

class ASFFormDefinition {
	
	private $categorySections;
	private $categoriesWithNoProperties;
	private $additionalCategoryAnnotations = array();
	
	public function __construct($categorySections, $categoriesWithNoProperties){
		$this->categorySections = $categorySections;
		$this->categoriesWithNoProperties = $categoriesWithNoProperties;
	}
	
	public function getFormDefinition(){
		$formDefinition = $this->getFormDefinitionIntro();
		$formDefinition .= $this->getFormDefinitionSyntax();
		$formDefinition .= $this->getFormDefinitionOutro();

		//echo('<pre>'.$formDefinition.'</pre>');
		
		return $formDefinition;
	}
	
	private function getFormDefinitionSyntax(){
		$formDefinitionSyntax = "";

		foreach($this->categorySections as $categoty){
			$formDefinitionSyntax .= $categoty->getCategorySection();
		}

		return $formDefinitionSyntax;
	}
	
	
	private function getFormDefinitionIntro(){

		$intro = "{{{info|add title=Add|edit title=Edit";

		global $asfWYSIWYG;
		if(defined('WYSIWYG_EDITOR_VERSION')){
			//todo: activate wysiwyg again
			//$intro .= "|WYSIWYG";
		}

		$intro .= "}}}\n\n";

		//this is needed for the form field updater so that
		//he knows which part of the form to replace
		$intro .= '<div id="asf_formfield_container">';
		
		$intro .= "{{{for template| CreateSilentAnnotations:";
		$intro .= "}}}\n\r";

		return $intro;
	}
	
	
	
	private function getFormDefinitionOutro(){
		$outro = "\n\n{{{end template}}}";

		$appendix = array();
		foreach($this->categorySections as $categoty){
			$appendix = array_merge($appendix, $categoty->getCategorySectionAppendix());
		}
		foreach($appendix as $a){
			$outro .= $a;
		}

		$outro .=  $this->getCategoriesWithNoPropertiesSectionSyntax();
		
		//todo: check if this has to be removed in special page for asf creation
		
		//end of asf_formfield_container 
		$outro .= '</div>';
		$outro .= '<div id="asf_formfield_container2" style="display: " ></div>';

		//todo:style this
		$outro .= '<div id="asf_category_annotations">Categories:<span id="asf_category_string"></span></div><br/>';
		
		global $wgUser;
		$cols = $wgUser->getIntOption('cols');
		$rows = $wgUser->getIntOption('rows');

		$standardInputs = array();
		$showFreeText = true;
		foreach($this->categorySections as $c){
			if($c->hideFreeText()){
				$showFreeText = false;
			}
			
			$standardInputs = array_merge($standardInputs, $c->standardInputs);
		}
		
		if($showFreeText){
			//$outro .= "'''".wfMsg('asf_free_text')."'''\n";			
			$outro .= "{{{standard input|free text|rows=".$rows."|cols=".$cols."}}}";
		} else {
			$outro .= "{{{standard input|free text|rows=".$rows."|cols=".$cols."}}}";
			$outro .= '<span class="asf-hide-freetext"/>';
		}
		
		$outro .= '<div class="asf-standard-inputs">';
		if(count($standardInputs) == 0){
			$outro .= '<br/>{{{standard input|summary}}}';	
			$outro .= '<br/>{{{standard input|minor edit}}} {{{standard input|watch}}}';
			$outro .= '<br/>{{{standard input|save}}} {{{standard input|preview}}} {{{standard input|changes}}} {{{standard input|cancel}}}';
			$outro .= wfMsg('asf_autogenerated_msg');
		} else {
			if(array_key_exists('summary', $standardInputs)){
				$outro .= '<br/>{{{standard input|summary}}}';	
			}
			
			$secondLine = '';
			if(array_key_exists('minor edit', $standardInputs)){
				$secondLine .= '{{{standard input|minor edit}}}';
			}
			if(array_key_exists('watch', $standardInputs)){
				$secondLine .= ' {{{standard input|watch}}}';
			}
			if(strlen($secondLine) > 0){
				$outro .= '<br/>'.trim($secondLine);
			}
			
			$thirdLine = '';
			if(array_key_exists('save', $standardInputs)){
				$thirdLine .= '{{{standard input|save}}}';
			}
			if(array_key_exists('save and continue', $standardInputs)){
				$thirdLine .= '{{{standard input|save and continue}}}';
			}
			if(array_key_exists('preview', $standardInputs)){
				$thirdLine .= ' {{{standard input|preview}}}';' {{{standard input|changes}}} {{{standard input|cancel}}}';
			}
			if(array_key_exists('changes', $standardInputs)){
				$thirdLine .= ' {{{standard input|changes}}}';' {{{standard input|cancel}}}';
			}
			if(array_key_exists('cancel', $standardInputs)){
				$thirdLine .= ' {{{standard input|cancel}}}';
			}
			if(strlen($thirdLine) > 0){
				$outro .= '<br/>'.trim($thirdLine);
			}
			
			if(array_key_exists('asf message', $standardInputs)){
				$outro .= wfMsg('asf_autogenerated_msg');		
			}
		}
		
		$outro .= "</div>";

		return $outro;
	}
	
	
	
	private function getCategoriesWithNoPropertiesSectionSyntax(){
		$syntax = '';

		if(count($this->categoriesWithNoProperties) > 0){
			$syntax .= "\n{{#collapsableFieldSetStart:";

			$syntax .= wfMsg('asf_categories_with_no_props_section')."| true}}\n";
				
			$syntax .= '<ul>';
			global $wgLang, $asfDisplayPropertiesAndCategoriesAsLinks;
			foreach($this->categoriesWithNoProperties as $c => $dc){
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
	
	
	public function getPageNameTemplate(){
		$asfPageNameTemplate = '';
		$useDefaultTemplate = true;
		foreach($this->categorySections as $c){
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
		
		return $asfPageNameTemplate;
	}
	
	private function getPreloadContent($titleText){
		$result = '';
		
		$title = Title::newFromText($titleText);
		if(is_null($title) || !$title->exists()){
			$preloadArticles = array();
			foreach($this->categorySections as $c){
				foreach($c->getPreloadingArticles() as $articleName => $dC){
					$preloadArticles[$articleName] = SFFormUtils::getPreloadedText($articleName);
				}
			}

			$result = implode("\n\n", $preloadArticles);
		}	
		
		return $result;
	}
	
	private function getAdditionalCategoryAnnotations(){
		return $this->additionalCategoryAnnotations;
	}
	
	public function getAdditionalFreeText($titleText){
		$additionalContent = '';
		
		//deal with preloading
		$additionalContent .= $this->getPreloadContent($titleText);
		
		//deal with addional category annotations
		foreach($this->additionalCategoryAnnotations  as $category){
			$additionalContent .= "[[".$category."]] ";
		}

		return $additionalContent;
	}
	
	
	public function updateDueToExistingAnnotations($existingAnnotations){
		$this->addUnresolvedAnnotationsSection($existingAnnotations);
		
		foreach($this->categorySections as $cs){
			$cs->updateDueToExistingAnnotations($existingAnnotations);
		}
		
		//update number of required input fields
		foreach($existingAnnotations as $label => $values){
			$existingAnnotations[$label] = count($values['values']);
		}
		foreach($this->categorySections as $categorySection){
			$categorySection->updateNumberOfRequiredInputFields($existingAnnotations);
		}
	}
	
	public function addUnresolvedAnnotationsSection($existingAnnotations){
		$unresolvedAnnotationsSection =
			new ASFUnresolvedAnnotationsFormData($existingAnnotations, $this->categorySections);
		$this->categorySections[] = $unresolvedAnnotationsSection;
	}
	
	public function setAdditionalCategoryAnnotations($categoryNames){
		$this->additionalCategoryAnnotations = $categoryNames;
	}
	
	public function updateNumberOfRequiredInputFields($requestedFormFields){
		$missingFormFields = array();
		foreach($requestedFormFields as $field => $dc){
			if($indexStart = strpos($field, '---')){
				$field = substr($field, 0, $indexStart);
			}
			if(!array_key_exists($field, $missingFormFields)){
				$missingFormFields[$field] = 1;
			} else {
				$missingFormFields[$field] += 1;
			}
		}
		
		foreach($this->categorySections as $categorySection){
			$categorySection->updateNumberOfRequiredInputFields($missingFormFields);
		}
	}
	
}



