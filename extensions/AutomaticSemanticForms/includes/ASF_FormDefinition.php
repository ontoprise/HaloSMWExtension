<?php

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
			$intro .= "|WYSIWYG";
		}

		$intro .= "}}}\n\n";

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

		global $wgUser;
		$cols = $wgUser->getIntOption('cols');
		$rows = $wgUser->getIntOption('rows');

		$showFreeText = true;
		foreach($this->categorySections as $c){
			if($c->hideFreeText()){
				$showFreeText = false;
				break;
			}
		}
		
		if($showFreeText){
			//$outro .= "'''".wfMsg('asf_free_text')."'''\n";			
			$outro .= "{{{standard input|free text|rows=".$rows."|cols=".$cols."}}}";
		} else {
			$outro .= "{{{standard input|free text|rows=".$rows."|cols=".$cols."}}}";
			$outro .= '<span class="asf-hide-freetext"/>';
		}
		
		$outro .= '<br/><br/>{{{standard input|summary}}}';
		$outro .= '<br/>{{{standard input|minor edit}}} {{{standard input|watch}}}';
		$outro .= '<br/>{{{standard input|save}}} {{{standard input|preview}}} {{{standard input|changes}}} {{{standard input|cancel}}}';
		$outro .= wfMsg('asf_autogenerated_msg');

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
	}
	
	private function addUnresolvedAnnotationsSection($existingAnnotations){
		$unresolvedAnnotationsSection =
			new ASFUnresolvedAnnotationsFormData($existingAnnotations, $this->categorySections);
		$this->categorySections[] = $unresolvedAnnotationsSection;
	}
	
	public function setAdditionalCategoryAnnotations($categoryNames){
		$this->additionalCategoryAnnotations = $categoryNames;
	}
	
}



