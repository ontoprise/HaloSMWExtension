<?php

global $wgAjaxExportList;
$wgAjaxExportList[] = 'smwf_ws_updateCache';
$wgAjaxExportList[] = 'smwf_ws_confirmWWSD';

function smwf_ws_updateCache($wsId){
	global $smwgHaloIP;
	require_once($smwgHaloIP . '/specials/SMWWebService/SMW_WSStorage.php');
	return "updated";
}

function smwf_ws_confirmWWSD($wsId){
	global $smwgHaloIP;
	require_once($smwgHaloIP . '/specials/SMWWebService/SMW_WSStorage.php');
	WSStorage::getDatabase()->setWWSDConfirmationStatus($wsId, "true");
	return "true";
}	

?>