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


function tff_updateInstanceData($updates, $articleTitle, $rowNr, $tabularFormId){
	
	//$updates = json_decode($updates, true);
	
	$result = array('success' => false, 'rowNr' => $rowNr, 'tabularFormId' => $tabularFormId);
	$result = json_encode($result);
	
	return '--##starttf##--' . $result . '--##endtf##--';
}


















