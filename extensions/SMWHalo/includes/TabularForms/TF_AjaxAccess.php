<?php

define('TF_SHOW_AJAX_LOADER_HTML_PARAM', '__tf_show_ajax_loader');
define('TF_TABULAR_FORM_ID_PARAM', '__tf_tabular_form_id');

global $wgAjaxExportList;
$wgAjaxExportList[] = 'tff_getTabularForm';
$wgAjaxExportList[] = 'tff_updateInstanceData';

function tff_getTabularForm($querySerialization, $tabularFormId){
	$querySerialization = json_decode($querySerialization, true);
	
	$querySerialization[] = TF_SHOW_AJAX_LOADER_HTML_PARAM.'=false';
	$querySerialization[] = TF_TABULAR_FORM_ID_PARAM.'='.$tabularFormId;
	
	$queryString = '';
	$queryParams = array();
	$printRequests = array();
		
	SMWQueryProcessor::processFunctionParams( 
		$querySerialization, $queryString, $queryParams, $printRequests);
	
	$result = SMWQueryProcessor::getResultFromQueryString
		( $queryString, $queryParams, $printRequests, 0);
	
	$result = array('result' => $result, 'tabularFormId' => $tabularFormId);
	$result = json_encode($result);
	
	return '--##starttf##--' . $result . '--##endtf##--';
}


function tff_updateInstanceData($updates, $articleTitle, $revisionId, $rowNr, $tabularFormId){
	
	//dom't know why, but this has to be done twice
	$updates = json_decode(print_r($updates, true), true);
	if(!is_array($updates)){
		$updates = json_decode(print_r($updates, true), true);
	}
	
	//todo: aggregate template parameter values
	//todo deal with null /new value
	
	$annotations = new TFAnnotationDataCollection();
	$params = array();
	foreach($updates as $update){
		if($update['isTemplateParam'] == 'true'){
			if(strlen($update['originalValue']) == 0) $update['originalValue'] = null;
			$params[$update['address']]['originalValues'][$update['templateId']] = $update['originalValue'];
			$params[$update['address']]['newValues'][$update['templateId']] = $update['newValue'];
		} else {
			$annotations->addAnnotation(new TFAnnotationData(
				$update['address'], $update['originalValue'], null, $update['newValue']));
		}
	}
	
	$parameters = new TFTemplateParameterCollection();
	foreach($params as $param => $values){
		$parameters->addTemplateParameter(new 
			TFTemplateParameter(	$param, $values['originalValues'], $values['newValues']));
	}
	
	//file_put_contents('d://uos.rtf', print_r($parameters, true));
	
	$title = Title::newFromText($articleTitle);
	$result = TFDataAPIAccess::getInstance($title)->updateValues($annotations, $parameters, $revisionId);
	
	$result = array('success' => $result, 'rowNr' => $rowNr, 'tabularFormId' => $tabularFormId, $revisionId);
	$result = json_encode($result);
	
	return '--##starttf##--' . $result . '--##endtf##--';
}


















