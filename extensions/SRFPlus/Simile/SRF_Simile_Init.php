<?php

if ( !defined( 'MEDIAWIKI' ) ) die;

global $wgExtensionFunctions;
$wgExtensionFunctions[] = 'smwgSRFSimileSetupExtension';

function smwgSRFSimileSetupExtension() {
	global $srfpgIP, $smwgResultFormats, $wgAutoloadClasses, $smwgQueryAggregateIDs;
	$smwgResultFormats['timeplot'] = 'SMWSimileTimeplotResultPrinter';
	$smwgResultFormats['runway'] = 'SMWSimileRunwayResultPrinter';
	
	$wgAutoloadClasses['SMWSimileTimeplotResultPrinter'] = $srfpgIP . '/Simile/SMW_QP_Simile.php';
	$wgAutoloadClasses['SMWSimileRunwayResultPrinter'] = $srfpgIP . '/Simile/SMW_QP_Simile.php';
	
	SMWSimileTimeplotResultPrinter::registerResourceModules();
	SMWSimileRunwayResultPrinter::registerResourceModules();
}