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

global $wgAjaxExportList;
$wgAjaxExportList[] = 'asff_getNewForm';
$wgAjaxExportList[] = 'asff_getNewFormRow';
$wgAjaxExportList[] = 'asff_convertDateString';

/*
 * returns the new form HTML according to thechanged category annotations
 */
function asff_getNewForm($categories, $existingAnnotations){

	//init params
	$categories = explode('<span>,</span> ', $categories);
	foreach($categories as $key => $cat){
		if(strlen(trim($cat)) == 0){
			unset($categories[$key]);
		}
	}
	
	$existingAnnotations = explode('<<<', $existingAnnotations);
	unset($existingAnnotations[0]);
	
	$annotationsToKeep = array();
	foreach($existingAnnotations as $anno){
		$annotationsToKeep[trim($anno)] =
			array('values' => array());
	}
	
	
	
	//create the form definition
	ASFFormGenerator::getInstance()->generateFormForCategories($categories, null, true);
	
	ASFFormGenerator::getInstance()->getFormDefinition()
		->updateDueToExistingAnnotations($annotationsToKeep);
	
	//this is necessary since the semantic form inputs input types are otherwise initialized too late
	if(function_exists('wfSFISetup')){
		wfSFISetup();
	}
		
	//Get the form HTML
	global $asfDummyFormName;
	$errors = ASFFormGeneratorUtils::createFormDummyIfNecessary();
	$title = Title::newFromText('Form:'.$asfDummyFormName);
	$article = new Article($title);
	
	global $wgTitle;
	$wgTitle = $title;
			
	$formPrinter = new ASFFormPrinter(); 
	$html = $formPrinter->formHTML(
		$article->getRawText(), false, false, $article->getID(), '', $asfDummyFormName, null, true);
	$html = $html[0];
	
	//Do post processing
	$startMarker = '<div id="asf_formfield_container';
	$html = substr($html, strpos($html, $startMarker) + strlen($startMarker));
	$html = substr($html, strpos($html, '>') + 1);
	$endmarker = '</div><div id="asf_formfield_container2';
	$html = substr($html, 0, strpos($html, $endmarker));
	$html = trim($html);
	
	$scripts = asff_getScriptsString();
	
	//return result
	$result = array('html' => $html, 'scripts' => $scripts);
	$result = json_encode($result);
	return '--##starttf##--' . $result . '--##endtf##--';
}

function asff_getNewFormRow($propertyName){
	
	
	//first check if thisproperty has a 'no automatic formedit'annotation
	global $wgLang;
	$noASF = ASFFormGeneratorUtils::getPropertyValue(
		ASFFormGeneratorUtils::getSemanticData(Title::newFromText($propertyName, SMW_NS_PROPERTY)), 
		ASF_PROP_NO_AUTOMATIC_FORMEDIT, false, false);
	
	if(strtolower($noASF) != 'true'){
		//create the form definition
		ASFFormGenerator::getInstance()->generateFormForCategories(array(), null, true);
		
		ASFFormGenerator::getInstance()->getFormDefinition()
			->updateDueToExistingAnnotations(array($propertyName => array('values' => array(
				array('insync' => true, 'editable' => true)))));
			
		ASFFormGenerator::getInstance()->getFormDefinition()
			->setInAjaxUpdateMode();
		
		//Get the form HTML
		global $asfDummyFormName;
		$errors = ASFFormGeneratorUtils::createFormDummyIfNecessary();
		$title = Title::newFromText('Form:'.$asfDummyFormName);
		$article = new Article($title);
		
		global $wgTitle;
		$wgTitle = $title;
				
		//this is necessary since the semantic form inputs input types are otherwise initialized too late
		if(function_exists('wfSFISetup')){
			wfSFISetup();
		} 
		
		$formPrinter = new ASFFormPrinter(); 
		$html = $formPrinter->formHTML(
			$article->getRawText(), false, false, $article->getID(), '', $asfDummyFormName, null, true);
		$html = $html[0];
		
		$scripts = asff_getScriptsString();
		
		//Do post processing
		$startMarker = 'asf-unresolved-sectio';
		$html = substr($html, strpos($html, $startMarker) + strlen($startMarker));
		$endMarker = 'asf_formfield_container2';
		$html = substr($html, 0, strpos($html, $endMarker));
		
		
		//get the row
		$html = substr($html, strpos($html, '<tr') + 3);
		//the first one is the headline
		$html = substr($html, strpos($html, '<tr'));
		$html = substr($html, 0, strrpos($html, '</tr'));
		$html .= '</tr>';
		
		$result = array('html' => $html, 'scripts' => $scripts, 'noasf' => false);
	} else {
		$result = array('noasf' => true);
	}
	
	//return result
	$result = json_encode($result);
	return '--##starttf##--' . $result . '--##endtf##--';
}


function asff_convertDateString($dateString){
	
	//$date = date('M d, yy', strtotime($dateString));
	$date = new SMWTimeValue();
	$date->setUserValue($dateString);
	$date = date('m/d/Y H:i:s', strtotime($date->getISO8601Date()));
	
	//return result
	$result = array('date' => $date);
	$result = json_encode($result);
	return '--##starttf##--' . $result . '--##endtf##--';
}

function asff_getScriptsString(){
	global $wgOut;
	$scripts = $wgOut->getScript();
	
	return $scripts;
}


