<?php
/*
 * Created on 04.02.2008
 *
 * Author: kai
 */

if ( !defined( 'MEDIAWIKI' ) ) die;

 global $wgAjaxExportList;
 
 $wgAjaxExportList[] = 'smwf_fw_SendAnnotationRatings';

 if (!class_exists('Services_JSON')) {
    require_once($sgagIP . '/includes/JSON.php');
 }
 
 /**
 * Receives rating from FindWork special page.
 * 
 * @param $json string: Array of tuples (subject, predicate, object, rating)
 * 
 * @return true
 */
function smwf_fw_SendAnnotationRatings($json) {
	$jsonservice = new Services_JSON();
   	$ratings = $jsonservice->decode($json);
	foreach($ratings as $r) {
		list($subject, $predicate, $object, $rating) = $r;
		SMWSuggestStatistics::getStore()->rateAnnotation(trim($subject),
											   trim($predicate), 
											   trim($object),
											   intval($rating));
	}
	return "true";
}
 
 
?>
