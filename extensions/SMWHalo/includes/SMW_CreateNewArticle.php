<?php
/*  Copyright 2010, ontoprise GmbH
 *  This file is part of the LinkedData-Extension.
 *
 *   The LinkedData-Extension is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The LinkedData-Extension is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * "Create New Article" feature script. 
 * Contains functions invoked via ajax from the client.
 *  
 *
 * @author dmitry
 * Date: 27.06.2011
 *
 */

$wgAjaxExportList[] = "smwf_na_getForms";
$wgAjaxExportList[] = "smwf_na_getCategories";
$wgAjaxExportList[] = "smwf_na_getPropertyValue";
$wgAjaxExportList[] = "smwf_na_articleExists";

/**
 * Get all forms which can be used for creating a new article
 * 
 */
function smwf_na_getForms() {
	$resultString = '';

	//search for forms only if SF installed
	if (defined('SF_VERSION')) {
		$forms = SFUtils::getAllForms();
		
		$filters = array();
		if(defined('ASF_VERSION')){
			//the dummy form should not be used for creating new articles
			global $asfDummyFormName;
			$filters[$asfDummyFormName] = true;
		}
		
		for ($i = 0; $i < count($forms); $i++) {
			if(array_key_exists($forms[$i], $filters))
				continue;
			$resultString .= $forms[$i];
			if($i < count($forms))
				$resultString .= ',';
		}
	}
	
	return $resultString;
}

/**
 * Get all categories which can be used for creating a new article
 * 
 */
function smwf_na_getCategories() {
	$resultString = '';

	//search for categories only if ASF installed
	if(defined('ASF_VERSION')){
		$categories = ASFCategoryAC::getCategories('', 99999); 
		
		for ($i = 0; $i < count($categories); $i++) {
			$resultString .= $categories[$i];
		}
	}
	
	return $resultString;
}


/**
 * Get specific property value
 * @param string $titleName article title
 * @param string $propertyName property name
 */
function smwf_na_getPropertyValue($titleName, $propertyName){
	$propertyValue = 'no description available';
	$title = SMWDIWikiPage::newFromTitle(Title::newFromText($titleName));
	$prop = SMWDIProperty::newFromUserLabel($propertyName);
	$propValues = smwfGetStore()->getPropertyValues($title, $prop);
	if($propValues && count($propValues) > 0){
		$propertyValue = $propValues[0]->getWikiValue();
	}
	return $propertyValue . ';' . $titleName;
}

/**
 * Check if an article with specified title already exists 
 * @param string $titleName
 */
function smwf_na_articleExists($titleName) {
  if(preg_match(Title::getTitleInvalidRegex(), $titleName)){
    return 'invalid_title';
  }
	return smwf_om_ExistsArticle($titleName) . ';' .$titleName;
}



