<?php
/*
 * Created on 04.02.2008
 *
 * Author: kai
 */
 global $wgAjaxExportList;
 
 $wgAjaxExportList[] = 'smwf_fw_SendAnnotationRatings';
  
 /**
 * TODO: need JSON decode implementation
 * Receives rating from FindWork special page.
 * 
 * @param $json string: Array of tuples (subject, predicate, object, rating)
 * 
 * @return true
 */
function smwf_fw_SendAnnotationRatings($json) {
	// TODO: has to be replaced by *real* JSON decoding. >= PHP 5.2.0
	$ratings = array();
	$listOFRatings = substr($json, 1, strlen($json) - 2);
	preg_match_all("/\[([^]]*)\]/", $listOFRatings, $ratings);
	foreach($ratings[1] as $r) {
		list($subject, $predicate, $object, $rating) = split(",", $r);
		smwfGetSemanticStore()->rateAnnotation(trim(str_replace("\"", "", $subject)),
											   trim(str_replace("\"", "", $predicate)), 
											   trim(str_replace("\"", "", $object)),
											   intval(str_replace("\"", "", $rating)));
	}
	return "true";
}
 
 
?>
