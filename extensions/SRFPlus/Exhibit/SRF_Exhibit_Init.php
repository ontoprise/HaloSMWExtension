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
$wgExtensionFunctions[] = 'smwgSRFExhibitSetupExtension';

function smwgSRFExhibitSetupExtension() {
	global $srfgFormats;
	$srfgFormats[] = 'exhibit';
	
	global $smwgResultFormats, $wgAutoloadClasses, $srfpgIP;
	$smwgResultFormats['exhibit'] = 'SRFExhibit';
	$wgAutoloadClasses['SRFExhibit'] = $srfpgIP . '/Exhibit/SRF_Exhibit.php';

	SRFExhibit::registerResourceModules();
}
