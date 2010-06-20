<?php

$wgAjaxExportList[] = 'smwf_qc_getQueryIds';
$wgAjaxExportList[] = 'smwf_qc_updateQuery';

/*
 * Returns a list of the ids of all cached queries
 */
function smwf_qc_getQueryIds($paramAsJSON){
	$paramObj = json_decode($paramAsJSON);
	$limit = $paramObj->limit;
	$offset = $paramObj->offset;
	
	$response = array();
	$response['queryIds'] = array( 12, 34, 56);
	
	$response = json_encode($response);
	$response = new AjaxResponse($response);
	$response->setContentType( "application/json" );
	return $response;
}

/*
 * Updates a query with the given id
 */
function smwf_qc_updateQuery($paramAsJSON){
	$paramObj = json_decode($paramAsJSON);
    $queryId = $paramObj->queryId;
    
	$response = array();
	$response['success'] = true;
	
	$response = json_encode($response);
	$response = new AjaxResponse($response);
	$response->setContentType( "application/json" );
	return $response;
}