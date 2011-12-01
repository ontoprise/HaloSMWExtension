<?php
/**
 * A query printer for pie charts using the Google Chart API
 *
 * @note AUTOLOADED
 */

if( !defined( 'MEDIAWIKI' ) ) {
	die( 'Not an entry point.' );
}

global $wgExtensionFunctions;
$wgExtensionFunctions[] = 'smwgSRFGroupSetupExtension';

function smwgSRFGroupSetupExtension() {
	global $srfgFormats;
	$srfgFormats[] = 'group table';
	$srfgFormats[] = 'group broadtable';
	$srfgFormats[] = 'group list';
	$srfgFormats[] = 'group ol';
	$srfgFormats[] = 'group ul';
	$srfgFormats[] = 'group ofc';
	$srfgFormats[] = 'group ofc-pie';
	$srfgFormats[] = 'group ofc-bar';
	$srfgFormats[] = 'group ofc-bar_3d';
	$srfgFormats[] = 'group ofc-line';
	$srfgFormats[] = 'group ofc-scatter_line';
	
	global $smwgResultFormats, $wgAutoloadClasses, $srfpgIP;
	$smwgResultFormats['group table'] = 'SRFGroupTable';
	$smwgResultFormats['group broadtable'] = 'SRFGroupTable';
	$wgAutoloadClasses['SRFGroupTable'] = $srfpgIP . '/Group/SRF_GroupTable.php';
	
	$smwgResultFormats['group list'] = 'SRFGroupList';
	$smwgResultFormats['group ol'] = 'SRFGroupList';
	$smwgResultFormats['group ul'] = 'SRFGroupList';
	$wgAutoloadClasses['SRFGroupList'] = $srfpgIP . '/Group/SRF_GroupList.php';
	
	$smwgResultFormats['group ofc'] = 'SRFGroupOFC';
	$smwgResultFormats['group ofc-pie'] = 'SRFGroupOFC';
	$smwgResultFormats['group ofc-bar'] = 'SRFGroupOFC';
	$smwgResultFormats['group ofc-bar_3d'] = 'SRFGroupOFC';
	$smwgResultFormats['group ofc-line'] = 'SRFGroupOFC';
	$smwgResultFormats['group ofc-scatter_line'] = 'SRFGroupOFC';
	$wgAutoloadClasses['SRFGroupOFC'] = $srfpgIP . '/Group/SRF_GroupOFC.php';
}
