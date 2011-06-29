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
 * Insert description here
 *
 * @author dmitry
 * Date: 27.06.2011
 *
 */

$wgAjaxExportList[] = "cna_getForms";
$wgAjaxExportList[] = "cna_getCategories";
$wgAjaxExportList[] = "cna_getPropertyValue";
$wgAjaxExportList[] = "cna_articleExists";


function cna_getForms() {
	$resultString = '';

	//search for forms only if SF installed
	if (defined('SF_VERSION')) {
		$forms = SFUtils::getAllForms();
		
		for ($i = 0; $i < count($forms); $i++) {
			$resultString .= $forms[$i];
			if($i < count($forms))
				$resultString .= ',';
		}
	}
	
	return $resultString;
}

function cna_getCategories() {
	$resultString = '';

	//search for categories only if ASF installed
	if(defined('ASF_VERSION')){
		$categories = ASFCategoryAC::getCategories('');
		
		for ($i = 0; $i < count($categories); $i++) {
			$resultString .= $categories[$i];
		}
	}
	
	return $resultString;
}


function cna_getPropertyValue($titleName, $propertyName){
	$propertyValue = 'no description available';
	$title = Title::newFromText($titleName);
	$prop = SMWPropertyValue::makeUserProperty($propertyName);
	$propValues = smwfGetStore()->getPropertyValues($title, $prop);
	if($propValues && count($propValues) > 0){
		$propertyValue = $propValues[0]->getWikiValue();
	}
	return $propertyValue;
}

function cna_articleExists($titleName) {
	return smwf_om_ExistsArticle($titleName) . ';' .$titleName;
}

