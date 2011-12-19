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
 * If an article with the same name already exists, this CP first check
 * if the new term and the existing article are identical based on some
 * properties. If so, some property values are appended and some others
 * are ignored. If not, a new article with a slightly differen name is created. 
 */
class DICPAppendSome extends DIConflictPolicy {
	
	public function createArticle(
			$term, $templateName, $extraCategories, $delimiter, $title, $termImportName, $log, $botId, $damId){

		$attributesForIdentityCheck = 
			DICPHIdentityChecker::getInstance()->getAttributesForIdentityCheck($term, $templateName, $damId, $delimiter);

		global $ditigUseDateAsSuffixOnConflicts;
		$originalArticleName = $title->getFullText;
		$articleNameSuffix = 0;
		
		$noIdentityConflict = false;
		while(!$noIdentityConflict){
			if(!$title->exists()){
				$noIdentityConflict = true;
			} else {
				if(trim($templateName) == ''){
					$noIdentityConflict = DICPHIdentityChecker::getInstance()-> 
						doExactCheckForProperties($title, $attributesForIdentityCheck);
				} else {
					$noIdentityConflict = DICPHIdentityChecker::getInstance()-> 
						doExactCheckForTemplateParams($title, $templateName, $attributesForIdentityCheck);
				}
				
				if(!$noIdentityConflict){
					if($ditigUseDateAsSuffixOnConflicts){
						$articleNameSuffix = time();
					} else {
						$articleNameSuffix += 1;
					}
					$title = Title::newFromText($originalArticleName.'-'.$articleNameSuffix);
				}
			}
		}
		
		$articleAccess = DICPHArticleAccess::getInstance();
		
		if($title->exists()){
			//merging has to be done
			$attributesForAppending = 
				$this->getAttributesForAppending($term, $templateName, $damId, $delimiter);
			
			if(trim($templateName) == ''){
				
				$additionalContent = '';
								
				foreach($attributesForAppending as $propertyName => $values){
					$existingValues = $articleAccess->getPropertyValues($title, $propertyName); 
					foreach($values as $key => $value){
						if(!in_array($value, $existingValues)){
							$additionalContent .= 
								'[['.$propertyName.'::'.$value.'| ]]';
						}	
					} 
				}
					
				if(trim($additionalContent) != ''){
					//the article text needs an update
					
					$existingTIFAnnotations['updated'][] = $termImportName;
					$existingTIFAnnotations = 
						"\n".$articleAccess->createTIFAnnotationsString($existingTIFAnnotations);
					
					$article = new Article($title);
					$content = $article->getContent();
					$content .= $additionalContent;
					
					
					$success = $article->doEdit($content.$existingTIFAnnotations, wfMsg('smw_ti_creationComment'));
					if (!$success) {
						$log->addGardeningIssueAboutArticle($botId, SMW_GARDISSUE_CREATION_FAILED, $title);
						return wfMsg('smw_ti_creationFailed', $title);
					}

					echo "\r\nArticle ".$title->getFullText()." updated.\n";
					$log->addGardeningIssueAboutArticle(
						$botId,
						SMW_GARDISSUE_UPDATED_ARTICLE,
						$title);
				} else {
					//article text does not need an updated. we do not add a was ignored annotation
					//in order to improve performance
					echo "\r\nArticle ".$title->getFullText()." ignored.\n";
				}
			} else { //template mode
				
				$updates = array();
				
				foreach($attributesForAppending as $attributeName => $values){
					$existingValues = $articleAccess->
						getTemplateParameterValue($title, $templateName, $attributeName); 
					
					echo("\n".$attributeName);
					echo("\n".$existingValues);
					echo("\n".$values);
						
					if($values == $existingValues){
						continue;
					}	
					
					//todo:describe that changing delimiter may have strange results
					$values = explode($delimiter,$values);	
					$existingValuesArray = explode($delimiter,$existingValues);
					
					foreach($values as $key => $value){
						if(!in_array($value, $existingValuesArray)){
							if(strlen(trim($existingValues)) > 0){
								$existingValues .= $delimiter;
							}
							$existingValues .= $value; 
						}
					}
					
					$updates[$attributeName] = $existingValues;
				}					
				
				if(count($updates) > 0){
					$existingTIFAnnotations['updated'][] = $termImportName;
					$existingTIFAnnotations = 
						"\n".$articleAccess->createTIFAnnotationsString($existingTIFAnnotations);
					
					$success = $articleAccess->updateTemplateParameterValues($title, $templateName, $updates, $existingTIFAnnotations);
				
					if (!$success) {
						$log->addGardeningIssueAboutArticle($botId, SMW_GARDISSUE_CREATION_FAILED, $title);
						return wfMsg('smw_ti_creationFailed', $title);
					}
				} else {
					//article text does not need an updated. we do not add a was ignored annotation
					//in order to improve performance
					echo "\r\nArticle ".$title->getFullText()." ignored.\n";
				}

				echo "\r\nArticle ".$title->getFullText()." updated.\n";
				$log->addGardeningIssueAboutArticle(
					$botId,
					SMW_GARDISSUE_UPDATED_ARTICLE,
					$title);
			}
			
			return true;
		} else { 
			//create a new article
			
			$existingTIFAnnotations['added'][] = $termImportName;
			$existingTIFAnnotations = 
				"\n".$articleAccess->createTIFAnnotationsString($existingTIFAnnotations);

			// Create the content of the article based on the creation pattern
			$content = 
				$articleAccess->createArticleContent($term, $templateName, $extraCategories, $delimiter);

			// Create the article
			$article = new Article($title);
			$success = $article->doEdit($content.$existingTIFAnnotations, wfMsg('smw_ti_creationComment'));
			if (!$success) {
				$log->addGardeningIssueAboutArticle($botId, SMW_GARDISSUE_CREATION_FAILED, $title);
				return wfMsg('smw_ti_creationFailed', $title);
			}

			echo "\r\nArticle ".$title->getFullText()." created.\n";
			$log->addGardeningIssueAboutArticle(
				$botId,
				SMW_GARDISSUE_ADDED_ARTICLE,
				$title);

			return true;
		}
	}
	
	protected function getAttributesForAppending($term, $template, $damId, $delimiter = ''){
		global $ditigAttributesForAppending;
		$attributesForAppending = array();
		
		if(trim($template) == ''){
			$attributes = $term->getAttributes(true);
			foreach($ditigAttributesForAppending[$damId] as $attributeName){
				if(array_key_exists($attributeName, $attributes)){
					$attributesForAppending[$attributeName] = DICPHArticleAccess::getInstance()->
						applyDelimiterOnPropertyValues($attributeName, $attributes[$attributeName]);
				} else {
					$attributesForAppending[$attributeName] = array();
				}
			}
		} else {
			$attributes = $term->getAttributes(false);
			foreach($ditigAttributesForAppending[$damId] as $attributeName){
				if(array_key_exists($attributeName, $attributes)){
					$attributesForAppending[$attributeName] = 
						implode($delimiter, $attributes[$attributeName]);
				} else {
					$attributesForAppending[$attributeName] = '';
				}
			}
		}
		
		return $attributesForAppending;
	}
	
}