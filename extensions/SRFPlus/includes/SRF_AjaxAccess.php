<?php

global $wgAjaxExportList;
$wgAjaxExportList[] = 'srf_AjaxAccess';

function srf_AjaxAccess($method, $params) {
	$result="";
	
	if($method == "addGeo"){
		global $srfpgIP;
		require_once( $srfpgIP . '/includes/SRF_Storage.php' );
		$srfStore = SRFStorage::getDatabase();
		$p_array = explode("|", $params);
		$idx = count($p_array);
		$srfStore->addGeo(implode('|', array_slice($p_array, 0, $idx-1)), $p_array[$idx - 1]);
		return 'success';
	}
	else {
		return "Operation failed, please retry later.";
	}
}
?>