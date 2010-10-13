<?php

global $wgAjaxExportList;
global $smwgConnectorIP;

require_once($smwgConnectorIP . '/includes/SC_Processor.php');
$wgAjaxExportList[] = 'smwf_sc_Access';


function smwf_sc_Access($method, $params) {
	global $smwgConnectorEnabled;

	$result="Semantic connector disabled.";
	if($method == "getMappingData"){
		if ($smwgConnectorEnabled) {
			$result = SCProcessor::getMappingData($params);
		}
		return $result;
	} else if($method == "saveMappingData") {
		if ($smwgConnectorEnabled) {
			$p_array = explode("|", $params, 2);
			$result = SCProcessor::saveMappingData($p_array[0], explode(",", substr($p_array[1], 1)));
		}
		return $result;
	} else if($method == "saveEnabledForms") {
		if ($smwgConnectorEnabled) {
			$p_array = explode(",", $params, 3);
			$result = SCProcessor::saveEnabledForms($p_array[0], $p_array[1], explode("|", $p_array[2]));
		}
		return $result;
	} else {
		return "Operation failed, please retry later.";
	}
}
?>