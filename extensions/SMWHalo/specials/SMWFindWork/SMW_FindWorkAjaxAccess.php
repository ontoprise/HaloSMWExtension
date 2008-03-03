<?php
/*
 * Created on 04.02.2008
 *
 * Author: kai
 */
 global $wgAjaxExportList;
 
 $wgAjaxExportList[] = 'smwf_fw_SendAnnotationRatings';
  
 /**
 * Receives rating from FindWork special page.
 * 
 * @param $json string: Array of tuples (subject, predicate, object, rating)
 * 
 * @return true
 */
function smwf_fw_SendAnnotationRatings($json) {
	$ratings = json_decode($json);
	foreach($ratings as $r) {
		list($subject, $predicate, $object, $rating) = $r;
		smwfGetSemanticStore()->rateAnnotation(trim($subject),
											   trim($predicate), 
											   trim($object),
											   intval($rating));
	}
	return "true";
}
 
 
?>
