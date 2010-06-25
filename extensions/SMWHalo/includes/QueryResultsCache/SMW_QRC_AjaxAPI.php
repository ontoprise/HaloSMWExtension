<?php

$wgAjaxExportList[] = 'smwf_qc_getQueryIds';
$wgAjaxExportList[] = 'smwf_qc_updateQuery';

/*
 * Returns a list of the ids of all cached queries.
 * Returns an empty query id array if QRC is disabled
 */
function smwf_qc_getQueryIds($paramAsJSON){
	if(!smwgQRCEnabled){
		$response['queryIds'] = array(); 
	} else {
		$paramObj = json_decode($paramAsJSON);
		@ $limit = $paramObj->limit;
		@ $offset = $paramObj->offset;
		@ $debug = $paramObj->debug;
		
		$qrc = new SMWQRCQueryResultsCache();
		$response['queryIds'] = $qrc->getQueryIds($limit, $offset);
	}
	
	$response = json_encode($response);
	if(!$debug){
		$response = new AjaxResponse($response);
		$response->setContentType( "application/json" );
	}
	
	return $response;
}

/*
 * Updates a query with the given id
 * Returns success=true if query was updated or if QRC is disabled
 */
function smwf_qc_updateQuery($paramAsJSON){
	global $smwgQRCEnabled;
	if(!$smwgQRCEnabled){
		error();
		$response['success'] = true; 
	} else {
		$paramObj = json_decode($paramAsJSON);
	    @ $queryId = $paramObj->queryId;
	    
		$qrc = new SMWQRCQueryResultsCache();
		$response['success'] = $qrc->updateQueryResult($queryId);
	}
	
	$response = json_encode($response);
	$response = new AjaxResponse($response);
	$response->setContentType( "application/json" );
	return $response;
}