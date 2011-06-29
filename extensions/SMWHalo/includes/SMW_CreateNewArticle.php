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

//$wgHooks['UnknownAction'][] = 'cna_actionHook';


function cna_getForms() {
	$forms = SFUtils::getAllForms();
	$resultString = '';
	
	for ($i = 0; $i < count($forms); $i++) {
		$resultString .= $forms[$i];
		if($i < count($forms))
			$resultString .= ',';
	}
	
	return $resultString;
}

function cna_getCategories() {
	$categories = ASFCategoryAC::getCategories('');
	$resultString = '';
	
	for ($i = 0; $i < count($categories); $i++) {
		$resultString .= $categories[$i];
		if($i < count($categories))
			$resultString .= ',';
	}
	
	return $resultString;
}


