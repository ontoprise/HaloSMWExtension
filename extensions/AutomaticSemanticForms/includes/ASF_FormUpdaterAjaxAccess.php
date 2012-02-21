<?php

global $wgAjaxExportList;
$wgAjaxExportList[] = 'asff_getNewForm';

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
		$anno = explode('[', $anno);
		$anno[1] = substr($anno[1], 0, strlen($anno[1]) -1);
		if(count($anno) == 2){
			$annotationsToKeep[$anno[1]] = true;
		} else {
			//date input field or so
			if(!array_key_exists($anno[1], $annotationsToKeep)){
				$annotationsToKeep[$anno[1]] = array();
			}
			$anno[2] = substr($anno[2], 0, strlen($anno[2]) -1);
			$annotationsToKeep[$anno[1]][$anno[2]] = true;
		}
	}
	
	//remove invalid required input fields
	foreach($annotationsToKeep as $anno => $fields){
		if(is_array($fields)){
			if(array_key_exists('year', $fields) && !array_key_exists('day', $fields)){
				//this is a form input field of date which has not yet been filled with values
				unset($annotationsToKeep[$anno]);
			} else if(array_key_exists('is_checkbox', $fields) && count($fields) == 1){
				//this is a checkbox, that has not been selected
				unset($annotationsToKeep[$anno]);
			} else {
				//keep this
				$annotationsToKeep[$anno] = true;
			}
		}
	}
	
	//deal with the number of required input fields
	foreach($annotationsToKeep as $anno => $fields){
		if($indexStart = strpos($anno, '---')){
			unset($annotationsToKeep[$anno]);
			$anno = substr($anno, 0, $indexStart);
		}
		
		if(!array_key_exists($anno, $annotationsToKeep)){
			$annotationsToKeep[$anno] = array();
		}
		if(!is_array($annotationsToKeep[$anno])){
			$annotationsToKeep[$anno] = array();
		}
		if(!array_key_exists('values', $annotationsToKeep[$anno])){
			$annotationsToKeep[$anno]['values'] = array();;
		}
		$annotationsToKeep[$anno]['values'][] = true;
	}
	
	echo('<pre>'.print_r($annotationsToKeep, true).'</pre>');
	
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
	
	$endmarker = '</div><div id="asf_formfield_container2';
	$html = substr($html, 0, strpos($html, $endmarker));
	
	//return result
	$result = array('html' => $html);
	$result = json_encode($result);
	return '--##starttf##--' . $result . '--##endtf##--';
}

