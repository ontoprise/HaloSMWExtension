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

/**
 * @file
 * @ingroup DITermImport
 * 
 * @author Ingo Steinbauer
 */


/*
 * Provides some helper methods that enable conflict
 * policies to read and write article content
 */
class DICPHArticleAccess {
	
	private static $instance;
	
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	private $initializedArticles = array();
	private $computedDisplayTemplates = array();
	private $computedDelimiters = array();
	
	private function __construct(){
	}
	
	/*
	 * Returns the annotation values that can be found for the
	 * given property name in the wiki text of the instance.
	 */
	public function getPropertyValues($title, $propertyName){
		if(!array_key_exists($title->getFullText(), $this->initializedArticles)){
			if(!$this->initializeArticle($title)){
				return false;
			}
		}

		if(array_key_exists($propertyName, $this->initializedArticles[$title->getFullText()]['properties'])){
			return $this->initializedArticles[$title->getFullText()]['properties'][$propertyName];
		}
		return array();
	}
	
	/*
	 * Returns the parameter value, that can be found for
	 * the given template and parameter name in the instance's
	 * wiki text
	 */
	public function getTemplateParameterValue($title, $templateName, $parameterName){
		if(!array_key_exists($title->getFullText(), $this->initializedArticles)){
			if(!$this->initializeArticle($title)){
				return false;
			}
		}
		
		if(array_key_exists($templateName, $this->initializedArticles[$title->getFullText()]['templates'])){
			if(array_key_exists($parameterName, $this->initializedArticles[$title->getFullText()]['templates'][$templateName])){
				return $this->initializedArticles[$title->getFullText()]['templates'][$templateName][$parameterName];
			}
		}
		return false;
	}
	
	/*
	 * Parses an article via the DataAPI and stores all
	 * property and template parameter values that can
	 * be found in the Wiki text.
	 */
	private function initializeArticle($title){
		$article = new Article($title);
		if(!$article->exists()){
			return false;
		}
				
		$this->initializedArticles[$title->getFullText()] = array();
		$this->initializedArticles[$title->getFullText()]['properties'] = array();
		$this->initializedArticles[$title->getFullText()]['templates'] = array();
				
		$text = $article->getContent();
		POMElement::$elementCounter = 0;
		$pomPage = new POMPage($title->getFullText(), $text);
		
		$elements = $pomPage->getElements()->listIterator();
		while($elements->hasNext()){
			$element = $elements->getNext()->getNodeValue();
				
			if($element instanceof POMProperty){
				$this->initializedArticles[$title->getFullText()]['properties'][$element->name][] = 
					$element->value;
			} else if($element instanceof POMTemplate){
				$this->initializedArticles[$title->getFullText()]['templates'][$element->getTitle()] = array();	
					
				$number = 1;
				foreach($element->parameters as $key => $parameter){
					if($parameter instanceof POMTemplateNamedParameter) {
						$name = $parameter->getName();
					} else {
						$name = $number;
						$number++;
					}
					$this->initializedArticles[$title->getFullText()]['templates'][$element->getTitle()][$name] = 
						$parameter->getValue()->text;
				}
			}
		}
		
		return true;
	}
	
	/**
	 * returns an array that contains already existing term import annotations
	 * 
	 * @param $title
	 * @return array
	 */
	public function getExistingTIFAnnotations($title){
		$existingAnnotations = array();
		$existingAnnotations['added'] = array();
		$existingAnnotations['updated'] = array();
		$existingAnnotations['ignored'] = array();

		//todo: maybe cache semdata
		$semdata = smwfGetStore()->
			getSemanticData(SMWDIWikiPage::newFromTitle($title));

		$property = SMWDIProperty::newFromUserLabel('WasAddedDuringTermImport');
		$values = $semdata->getPropertyValues($property);
		foreach($values as $value){
			$existingAnnotations['updated'][] = 
				SMWDataValueFactory::newDataItemValue($value, null)->getShortWikiText();
		}
			
		$property = SMWDIProperty::newFromUserLabel('WasIgnoredDuringTermImport');
		$values = $semdata->getPropertyValues($property);
		foreach($values as $value){
			$existingAnnotations['ignored'][] = 
				SMWDataValueFactory::newDataItemValue($value, null)->getShortWikiText();
		}
		
		return $existingAnnotations;
	}
	
	/**
	 * Returns new TIF annotations as string 
	 * 
	 * @param $annotations
	 * @return string
	 */
	public function createTIFAnnotationsString($annotations){
		$result = "";
		if(array_key_exists('added', $annotations)){
			foreach($annotations['added'] as $annotation){
				$result .= "[[wasAddedDuringTermImport::".$annotation."| ]] ";
			}
		}
		
		if(array_key_exists('updated', $annotations)){
			foreach($annotations['updated'] as $annotation){
				$result .= "[[wasUpdatedDuringTermImport::".$annotation."| ]] ";
			}
		}
		
		if(array_key_exists('ignored', $annotations)){
			foreach($annotations['ignored'] as $annotation){
				$result .= "[[wasIgnoredDuringTermImport::".$annotation."| ]] ";
			}
		}
		return trim($result);
	}
	
	private function getDisplayTemplateForCategory($category){
		global $wgLang;
		if(strpos($category, $wgLang->getNSText(NS_CATEGORY).':') === 0){
			$category = substr($category, strpos($category, ":") +1);
		}
		
		if(!array_key_exists($category, $this->computedDisplayTemplates)){
			$title = Title::newFromText($category, NS_CATEGORY);
			$store = smwfGetStore();
			$semanticData = $store->getSemanticData(
				SMWWikiPageValue::makePageFromTitle($title)->getDataItem());
				
			$displayTemplate = 
				$this->getInheritedPropertyValueFromSDO($semanticData, 'Use_display_template');
			
			if(count($displayTemplate) > 0){
				$displayTemplate = $displayTemplate[0];
			} else {
				$displayTemplate = '';
			}
			$this->computedDisplayTemplates[$category] = $displayTemplate; 
		}
		
		return $this->computedDisplayTemplates[$category];
	}
	
	
	private function getInheritedPropertyValueFromSDO($semanticData, $propertyName, $getAll = false, $values = array(), $processedCategories = array()){
		//todo: this has been copied from ASF, consolidate this
		
		$properties = $semanticData->getProperties();
		
		if(array_key_exists($propertyName, $properties)){
			$vals = $semanticData->getPropertyValues($properties[$propertyName]);
			if(!$getAll){
				$idx = array_keys($vals);
				$idx = $idx[0];
				$values[] = SMWDataValueFactory::newDataItemValue($vals[$idx], null)
					->getShortWikiText();
			} else {
				foreach($vals as $v){
					$values[] = SMWDataValueFactory::newDataItemValue($v, null)
						->getShortWikiText();
				}
			}
		} else {
			$title = $semanticData->getSubject()->getTitle();
			
			if(array_key_exists($title->getText(), $processedCategories)){
				//deal with cyrcles
				return $values;
			} else {
				$processedCategories[$title->getText()] = true;
			}
			
			$superCategories = $title->getParentCategories();
			if(array_key_exists($title->getFullText(), $superCategories)){
				unset($superCategories[$title->getFullText()]);
			}
			
			$store = smwfGetStore();
			foreach($superCategories as $c => $dc){
				$semanticData = $store->getSemanticData(
					SMWDIWikiPage::newFromTitle(Title::newFromText($c, NS_CATEGORY)));
				$values = $this->getInheritedPropertyValueFromSDO($semanticData, $propertyName, $getAll, $values, $processedCategories);
			}
		}
		
		return $values;
	}
	
	private function getDelimiterFromPropertyAnnotations($property){
		$property = trim($property);
		
		if(!array_key_exists($property, $this->computedDelimiters)){
			global $wgLang;
			if(ucfirst($property) == $wgLang->getNSText(NS_CATEGORY)){
				$delimiter = ',';
			} else {
				$title = Title::newFromText($property, SMW_NS_PROPERTY);
				$store = smwfGetStore();
				$semanticData = $store->getSemanticData(
					SMWWikiPageValue::makePageFromTitle($title)->getDataItem());
				
				$delimiter = $this->getPropertyValueFromSDO($semanticData, 'Delimiter');
			
				if(!$delimiter){
					global $smwgHaloContLang;
					$specialSchemaProperties = $smwgHaloContLang->getSpecialSchemaPropertyArray();
					
					$maxCardinality = $this->getPropertyValueFromSDO($semanticData, 
						$specialSchemaProperties[SMW_SSP_HAS_MAX_CARD]);
					
					if($maxCardinality !== '1'){
						$delimiter = ',';
					} 
				}
			}
			
			$this->computedDelimiters[$property] = $delimiter;
		}
		
		return $this->computedDelimiters[$property]; 
	}
	
	private function getPropertyValueFromSDO($semanticData, $propertyName, $defaultValue = false){
		$propertyName = str_replace(' ', '_', $propertyName);
		
		$result = $defaultValue;
		
		$properties = $semanticData->getProperties();
		
		if(array_key_exists($propertyName, $properties)){
			$values = $semanticData->getPropertyValues($properties[$propertyName]);
			$idx = array_keys($values);
			$idx = $idx[0];
			if(!is_null($values[$idx])){						
				$result = SMWDataValueFactory::newDataItemValue($values[$idx], null)
					->getShortWikiText();
			}
		}
		
		return $result;
	}
	
	/**
	 * Creates the content of an article based on the description of the term and
	 * the creation pattern
	*/
	public function createArticleContent($term, $template, $extraCategories, $delimiter){
		$result = '';
		
		$addedCategories = array();
		
		global $wgLang;
		
		if(trim($template) == ''){
			foreach($term->getAttributes(true) as $property => $values){
				$values = $this->applyDelimiterOnPropertyValues($property, $values);
				
				foreach($values as $value){
					//todo: document this
					if(ucfirst(trim($property)) == $wgLang->getNSText(NS_CATEGORY)){
						if(strpos($value, $wgLang->getNSText(NS_CATEGORY).':') !== 0 ){
							$addedCategories[$value] = true;
							$value = $wgLang->getNSText(NS_CATEGORY).':'.$value;
						} else {
							$addedCategories[$value] = true;
						}
						$result .= '[['.$value."]]";
					} else {	
						$result .= '[['.$property.'::'.$value."| ]]";
					}
				}
			}
		} else {
			$result = '{{'.$template;
			foreach($term->getAttributes(false) as $attribute => $values){
				$result .= "\n".'|'.$attribute.' = '. implode($delimiter, $values);
			}
			$result .= "\n"."}}";
		}
		
		if(strlen($extraCategories) > 0){
			global $wgLang;
			
			$extraCategories = explode(',', $extraCategories);
			foreach($extraCategories as $eC){
				$eC = trim($eC);
				if(strpos($eC, $wgLang->getNSText(NS_CATEGORY).':') !== 0 ){
					$addedCategories[$eC] = true;
					$eC = $wgLang->getNSText(NS_CATEGORY).':'.$eC;
				} else {
					$addedCategories[$eC] = true;
				}
				
				$result .= '[['.$eC.']]';
			}
		}
		
		$addedDisplayTemplates = array();
		foreach(array_keys($addedCategories) as$aC){
			$displayTemplate = $this->getDisplayTemplateForCategory($aC);
			if(strlen($displayTemplate) > 0){
				if(!array_key_exists($displayTemplate, $addedDisplayTemplates)){
					$addedDisplayTemplates[$displayTemplate] = true;
					$result .= '{{'.$displayTemplate.'}}';
				}
			}
		}
		
		return $result;
	}
	
	/*
	 * Property values may contain delimiters. Property values, that contain
	 * delimiters must be split into several property values 
	 */
	public function applyDelimiterOnPropertyValues($propertyName, $propertyValues){
		
		$resultValues = array();
		$delimiter = $this->getDelimiterFromPropertyAnnotations($propertyName);
				
		foreach($propertyValues as $v){
			//todo: document this 
			if($delimiter === false){
				$resultValues[] = $v;
			} else {
				$vs = explode($delimiter, $v);
				foreach($vs as $v){
					$resultValues[] = trim($v);
				}
			}
		}

		return $resultValues;
	}
	
	public function updateTemplateParameterValues($title, $templateName, $updates, $extraContent){
		$article = new Article($title);
		
		$text = $article->getContent();
		POMElement::$elementCounter = 0;
		$pomPage = new POMPage($title->getFullText(), $text);
		
		$elements = $pomPage->getElements()->listIterator();
		
		while($elements->hasNext()){
			$element = $elements->getNextNodeValueByReference();
				
			if($element instanceof POMTemplate){
				if($element->getTitle() == $templateName){
					foreach($updates as $name => $value){
						$element->setParameter($name, $value);
					}
				}
			}
		}	
		
		$pomPage->sync();
			
		$text = $pomPage->text;
		
		$text .= $extraContent;
			
		$res = $article->doEdit($text, 'tabular forms');
		
		return $res;
	}
}