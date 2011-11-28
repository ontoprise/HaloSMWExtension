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


if ( !defined( 'MEDIAWIKI' ) ) die();

/*
 * This class adds Automatic Semantic Forms features
 * to the special page Special:FormEdit
 */
class ASFFormEdit extends SFFormEdit {
	
	/*
	 * This method is called if one opens Special:FormEdit
	 * 
	 * It adds some ASF features and then calls its parent method
	 */
	function execute($query, $redirectOnError = true) {
	
		if($this->doRedirect()){
			return true;
		}
	
		//get get parameters
		global $wgRequest;
		
		$categoryParam = $wgRequest->getVal('categories');
		
		$targetName = $wgRequest->getVal('target');
		
		if(!$categoryParam && !$targetName){
			$queryparts = str_replace('//','--asf-slash-slash--', $query);
			$queryparts = explode( '/', $queryparts, 2 );
			
			if(isset($queryparts[0]) && strpos($queryparts[0], 'categories=') === 0){
				$categoryParam = substr($queryparts[0], strlen('categories='));
				$targetName = isset( $queryparts[1] ) ? $queryparts[1] : '';
			}
		}
		
		if(!is_null($categoryParam)){
			$categoryParam = str_replace('--asf-slash-slash--', '/', $categoryParam);
		}
		
		$targetName = str_replace('--asf-slash-slash--', '/', $targetName);
		
		$formName = $wgRequest->getVal('form');
		
		if(is_null($categoryParam)){
			$requestURL = $wgRequest->getRequestURL();
			$requestURL = explode('/', $requestURL);
			
			if(strpos($requestURL[count($requestURL)-2], 'categories') === 0){
				$categoryParam = $requestURL[count($requestURL)-2];
				$categoryParam = substr($categoryParam, strlen('categories'));
				$targetName = $requestURL[count($requestURL)-1];
			}
		}
		
		//Initialize category names array
		$categoryNames = array();
		if($categoryParam){
			$categoryParam = str_replace('_', ' ', $categoryParam);
			$categoryNames = explode(',', $categoryParam);
			global $wgLang;
			foreach($categoryNames as $key => $category){
				$category = trim($category);
				if(strpos($category, $wgLang->getNSText(NS_CATEGORY).':') !== 0){
					$category = $wgLang->getNSText(NS_CATEGORY).':'.$category;
				}
				$categoryNames[$key] = $category;
			}
		}
		
		if(count($categoryNames) == 0 && !is_null($categoryParam)){
			global $wgOut;
			$wgOut->addHTML( '<p><b>Error:</b> No category name was passed for automatic form creation</p>');
			return;
		}
		
		if(count($categoryNames) > 0){ 
			//The given instance will be edited with forms for the given categories
			
			//first deal with preloading form input fields
			if(is_array($wgRequest->getArray('Property', null))){
				$wgRequest->setVal('CreateSilentAnnotations:', $wgRequest->getArray('Property', null));
			}
			
			$targetTitle = Title::newFromText($targetName);
			
			$result = ASFFormGenerator::getInstance()->generateFormForCategories($categoryNames, $targetTitle);
			if($result){
				//Set the dummy form name to trick the Semantic Forms extension
				global $asfDummyFormName;
				ASFFormGeneratorUtils::createFormDummyIfNecessary();
				$wgRequest->setVal('form', $asfDummyFormName);
				$wgRequest->setVal('target', $targetName);
			
				//deal with additional category annotations
				$categoryNames = $this->getAdditionalCategoryAnnotations($categoryNames, $targetName);
				if(count($categoryNames) > 0){
					ASFFormGenerator::getInstance()->getFormDefinition()->setAdditionalCategoryAnnotations($categoryNames);
				}
			} else {
				global $wgOut;
				 
				$html = '<p><b>Error:</b> No automatic form could be created for the given categories,'.
				 	' because they all have a <i>No automatic formedit</i> annotation </p>';

				$wgOut->addHTML($html);
				
				return;
			}
		} else if(count($categoryNames) == 0 && $targetName && !$formName){
			//Automatically create a form for this instance based on its category annotations
			//if the target exists
			
			$title = Title::newFromText($targetName);
			if($title->exists()){
				$result = ASFFormGenerator::getInstance()->generateFromTitle($title, true);
		
				if($result){
					//first deal with preloading form input fields
					if(is_array($wgRequest->getArray('Property', null))){
						$wgRequest->setVal('CreateSilentAnnotations:', $wgRequest->getArray('Property', null));
					}
					
					global $asfDummyFormName;
					ASFFormGeneratorUtils::createFormDummyIfNecessary();
					$wgRequest->setVal('form', $asfDummyFormName); 
				} else {
					global $wgOut;
					$wgOut->addHTML( '<p><b>Error:</b> No automatic form could be created for given article name.</p>');
					return;
				}
			} else {
				global $wgOut;
				$wgOut->addHTML( '<p><b>Error:</b> No automatic form could be created for given article name.</p>');
				return;
			}
		}
		
		parent::execute($query);
	}
	
	/*
	 * Compute which additional category annotations to add
	 * to the free text text area
	 */
	private function getAdditionalCategoryAnnotations($categoryNames, $targetName){
		$title = Title::newFromText($targetName);
		if(!is_null($title) &&$title->exists()){
			$annotatedParentCategories = $title->getParentCategories();
			if(count($annotatedParentCategories) > 0){
				foreach($categoryNames as $key => $category){
					if(array_key_exists(str_replace(' ', '_', $category), $annotatedParentCategories)){
						unset($categoryNames[$key]);
					} else {
						$categoryNames[$key] = $category;
					}	
				}
			}
		} 
		return $categoryNames;
	}
	
private function doRedirect(){
		global $wgOut, $wgRequest;
		
		$redirect_url = $wgRequest->getRequestURL();
		
		if(strpos($redirect_url, 'redirect=necessary') > 0){
			$redirect_url = str_replace(
				array('?redirect=necessary', '&redirect=necessary'),
				array('', ''), $redirect_url);
		
			$wgOut->setArticleBodyOnly( true );
		
			global $sfgScriptPath;
			$text = <<<END
			<script type="text/javascript">
			window.onload = function() {
				window.location="$redirect_url";
			}
			</script>

END;
			$wgOut->addHTML( $text );
			
			return true;
		}  else {
			return false;
		}
	}
	
	
	
	
}
