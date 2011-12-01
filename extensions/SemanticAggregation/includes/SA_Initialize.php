<?php
/*
 * Created on 14.7.2009
 *
 * Author: Ning
 */
if ( !defined( 'MEDIAWIKI' ) ) die;

define('SMW_AGGREGATION_VERSION', '0.5');

$smwgAggregationIP = $IP . '/extensions/SemanticAggregation';

global $wgExtensionFunctions;
$wgExtensionFunctions[] = 'smwgAggregationSetupExtension';

$smwgQueryAggregateIDs = array(
	'SUM' => 'SMWSumQueryAggregate',
	'AVERAGE' => 'SMWAverageQueryAggregate',
	'MAX' => 'SMWMaxQueryAggregate',
	'MIN' => 'SMWMinQueryAggregate',
	'COUNT' => 'SMWCountQueryAggregate',
);

/**
 * Intializes Semantic Aggregation Extension.
 */
function smwgAggregationSetupExtension() {
	global $smwgAggregationIP, $wgExtensionCredits, $wgAutoloadClasses;
	
	$wgAutoloadClasses['SMWAggregatePrintRequest'] = $smwgAggregationIP . '/includes/SA_PrintRequest.php';
	$wgAutoloadClasses['SMWAggregateResultPrinter'] = $smwgAggregationIP . '/includes/SA_ResultPrinter.php';
	
	//// printers
	$wgAutoloadClasses['SMWAggregateTableResultPrinter']     = $smwgAggregationIP . '/includes/SA_QP_Table.php';
	
	if (property_exists('SMWQueryProcessor','formats')) { // registration up to SMW 1.2.*
		SMWQueryProcessor::$formats['aggtable'] = 'SMWAggregateTableResultPrinter';
	} else { // registration since SMW 1.3.*
		global $smwgResultFormats;
		$smwgResultFormats['aggtable'] = 'SMWAggregateTableResultPrinter';
	}
	
	/// query aggregates
	$wgAutoloadClasses['SMWQueryAggregate']         = $smwgAggregationIP . '/includes/SMW_QueryAggregate.php';
	$wgAutoloadClasses['SMWFakeQueryAggregate']     = $smwgAggregationIP . '/includes/SMW_QueryAggregate.php';
	$wgAutoloadClasses['SMWSumQueryAggregate']      = $smwgAggregationIP . '/includes/SMW_QueryAggregate.php';
	$wgAutoloadClasses['SMWAverageQueryAggregate']  = $smwgAggregationIP . '/includes/SMW_QueryAggregate.php';
	$wgAutoloadClasses['SMWMaxQueryAggregate']      = $smwgAggregationIP . '/includes/SMW_QueryAggregate.php';
	$wgAutoloadClasses['SMWMinQueryAggregate']      = $smwgAggregationIP . '/includes/SMW_QueryAggregate.php';
	$wgAutoloadClasses['SMWCountQueryAggregate']    = $smwgAggregationIP . '/includes/SMW_QueryAggregate.php';
	
	
	// Register Credits
	$wgExtensionCredits['parserhook'][]= array(
	'name'=>'Semantic&nbsp;Aggregation&nbsp;Extension', 'version'=>SMW_AGGREGATION_VERSION,
			'author'=>"Ning Hu, Justin Zhang, [http://smwforum.ontoprise.com/smwforum/index.php/Jesse_Wang Jesse Wang], sponsored by [http://projecthalo.com Project Halo], [http://www.vulcan.com Vulcan Inc.]", 
			'url'=>'http://wiking.vulcan.com/dev', 
			'description' => 'Add aggregation to semantic queries.');
	
	return true;
}
?>