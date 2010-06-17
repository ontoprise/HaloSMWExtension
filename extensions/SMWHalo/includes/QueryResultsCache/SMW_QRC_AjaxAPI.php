<?php

$wgAjaxExportList[] = 'smwf_qrc_getQueryIds';
$wgAjaxExportList[] = 'smwf_qrc_updateQuery';

/*
 * Returns a list of the ids of all cached queries
 */
public function smwf_qrc_getQueryIds($limit = 0, $offset = 0){
	$response = array();
	$response['queryIds'] = array('12', '34', '56');
	
	$response = json_encode($response);
	$response = new AjaxResponse($response);
	$response->setContentType( "application/json" );
	return $response;
}

/*
 * Updates a query with the given id
 */
public function smwf_qrc_updateQuery($queryId){
	$response = array();
	$response['success'] = true;
	
	$response = json_encode($response);
	$response = new AjaxResponse($response);
	$response->setContentType( "application/json" );
	return $response;
}