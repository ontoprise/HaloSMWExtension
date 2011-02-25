<?php

define('TF_SHOW_AJAX_LOADER_HTML_PARAM', '__tf_show_ajax_loader');
define('TF_TABULAR_FORM_ID_PARAM', '__tf_tabular_form_id');

global $wgAjaxExportList;
$wgAjaxExportList[] = 'tff_getTabularForm';
$wgAjaxExportList[] = 'tff_updateInstanceData';
$wgAjaxExportList[] = 'tff_checkArticleName';
$wgAjaxExportList[] = 'tff_deleteInstance';

/*
 * Called by UI in order to load a tabular form
 */
function tff_getTabularForm($querySerialization, $isSPARQL, $tabularFormId){
	$querySerialization = json_decode($querySerialization, true);
	
	$queryString = '';
	$queryParams = array();
	$printRequests = array();

	if($isSPARQL){
		SMWSPARQLQueryProcessor::processFunctionParams( 
			$querySerialization, $queryString, $queryParams, $printRequests);
		
		//todo: avoid this
		$queryString = str_replace('&nbsp;', ' ', $queryString);	
			
		$queryParams[TF_SHOW_AJAX_LOADER_HTML_PARAM] = 'false';
		$queryParams[TF_TABULAR_FORM_ID_PARAM] = $tabularFormId;
			
		$result = SMWSPARQLQueryProcessor::getResultFromQueryString
			( $queryString, $queryParams, $printRequests, 0);	
	} else {
		$querySerialization[] = TF_SHOW_AJAX_LOADER_HTML_PARAM.'=false';
		$querySerialization[] = TF_TABULAR_FORM_ID_PARAM.'='.$tabularFormId;
		
		SMWQueryProcessor::processFunctionParams( 
			$querySerialization, $queryString, $queryParams, $printRequests);
		
		$result = SMWQueryProcessor::getResultFromQueryString
			( $queryString, $queryParams, $printRequests, 0);
	}
	
	$result = array('result' => $result, 'tabularFormId' => $tabularFormId);
	$result = json_encode($result);
	
	return '--##starttf##--' . $result . '--##endtf##--';
}


/*
 * Called by UI in order to add or modify a particular insatnce
 */
function tff_updateInstanceData($updates, $articleTitle, $revisionId, $rowNr, $tabularFormId){
	
	//dom't know why, but this has to be done twice
	$updates = json_decode(print_r($updates, true), true);
	if(!is_array($updates)){
		$updates = json_decode(print_r($updates, true), true);
	}
	
	$annotations = new TFAnnotationDataCollection();
	$params = array();
	foreach($updates as $update){
		if($update['isTemplateParam'] == 'true'){
			if(strlen($update['originalValue']) == 0) $update['originalValue'] = null;
			$params[$update['address']]['originalValues'][$update['templateId']] = $update['originalValue'];
			$params[$update['address']]['newValues'][$update['templateId']] = $update['newValue'];
		} else {
			$annotations->addAnnotation(new TFAnnotationData(
				$update['address'], $update['originalValue'], null, $update['hash'], $update['typeId'], $update['newValue']));
		}
	}
	
	$parameters = new TFTemplateParameterCollection();
	foreach($params as $param => $values){
		$parameters->addTemplateParameter(new 
			TFTemplateParameter(	$param, $values['originalValues'], $values['newValues']));
	}
	
	$title = Title::newFromText($articleTitle);
	
	//todo: add meaningful error messages
	if($revisionId == '-1'){
		//add instance
		$result = TFDataAPIAccess::getInstance($title)->createInstance($annotations, $parameters);
	} else {
		//edit instance
		$result = TFDataAPIAccess::getInstance($title)->updateValues($annotations, $parameters, $revisionId);
	}
	
	//a error msg is returnd if not successfull
	if(is_string($result)){
		$msg = $result;
		$result = false;
	} else {
		$msg = '';
	}
	
	$result = array('success' => $result, 'msg' => $msg, 'rowNr' => $rowNr, 'tabularFormId' => $tabularFormId, $revisionId);
	$result = json_encode($result);
	
	return '--##starttf##--' . $result . '--##endtf##--';
}

/*
 * Called by UI in order to cecck if article name is new and valid
 */
function tff_checkArticleName($articleName, $rowNr, $tabularFormId){
	$articleName = trim($articleName);
	$articleName = explode(':', $articleName, 2);
	foreach($articleName as $key => $value){
		$articleName[$key] = ucfirst($value);
	}
	$articleName = implode(':', $articleName);
	
	$validTitle = false;
	$title = Title::newFromText($articleName);
	if($title){
		if($title->getFullText() == $articleName){
			if(!$title->exists()){
				$validTitle = true;
			}
		}
	}  
	
	$result = array('validTitle' => $validTitle, 'rowNr' => $rowNr, 'tabularFormId' => $tabularFormId);
	$result = json_encode($result);
	
	return '--##starttf##--' . $result . '--##endtf##--';	
}


/*
 * This method is called by the UI in order to delete an instance
 */
function tff_deleteInstance($articleTitle, $revisionId, $rowNr, $tabularFormId){
	
	//todo: add meaningful error messages
	
	$title = Title::newFromText($articleTitle);
	$result = TFDataAPIAccess::getInstance($title)->deleteInstance($revisionId);
	
	$result = array('success' => $result, 'rowNr' => $rowNr, 'tabularFormId' => $tabularFormId, $revisionId);
	$result = json_encode($result);
	
	return '--##starttf##--' . $result . '--##endtf##--';
}



