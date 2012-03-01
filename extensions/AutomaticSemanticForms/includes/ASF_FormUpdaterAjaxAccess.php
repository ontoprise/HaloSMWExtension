<?php

global $wgAjaxExportList;
$wgAjaxExportList[] = 'asff_getNewForm';
$wgAjaxExportList[] = 'asff_getNewFormRow';

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
			array('values' => array(true));
	}
	
	//create the form definition
	ASFFormGenerator::getInstance()->generateFormForCategories($categories, null, true);
	
	ASFFormGenerator::getInstance()->getFormDefinition()
		->updateDueToExistingAnnotations($annotationsToKeep);
	
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
	
	//return result
	$result = array('html' => $html);
	$result = json_encode($result);
	return '--##starttf##--' . $result . '--##endtf##--';
}

function asff_getNewFormRow($propertyName){

	//create the form definition
	ASFFormGenerator::getInstance()->generateFormForCategories(array(), null, true);
	
	ASFFormGenerator::getInstance()->getFormDefinition()
		->updateDueToExistingAnnotations(array($propertyName => array('values' => array(true))));
	
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
	$html = substr($html, strpos($html, '">')+2);
	
	$endmarker = '</div><div id="asf_formfield_container2';
	$html = substr($html, 0, strpos($html, $endmarker));
	
	//get the needed row
	$html = substr($html, strpos($html, '<tr'));
	$html = substr($html, 0, strrpos($html, '</tr'));
	$html .= '</tr>';
	
	//return result
	$result = array('html' => $html);
	$result = json_encode($result);
	return '--##starttf##--' . $result . '--##endtf##--';
}
