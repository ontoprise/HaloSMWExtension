<?php
/**
 * A query printer for pie charts using the Google Chart API
 *
 * @note AUTOLOADED
 */

if( !defined( 'MEDIAWIKI' ) ) {
	die( 'Not an entry point.' );
}

global $srfgFormats;
$srfgFormats[] = 'ofc';
$srfgFormats[] = 'ofc-pie';
$srfgFormats[] = 'ofc-bar';
$srfgFormats[] = 'ofc-bar_3d';
$srfgFormats[] = 'ofc-line';
//$srfgFormats[] = 'ofc-scatter_line';

// include jquery
global $smgJSLibs; $smgJSLibs[] = 'jquery';

global $smwgResultFormats, $wgAutoloadClasses, $srfgIP;
$smwgResultFormats['ofc'] = 'SRFOFC';
$wgAutoloadClasses['SRFOFC'] = $srfgIP . '/ofc/SRF_OFC2.php';

$smwgResultFormats['ofc-pie'] = 'SRFOFC';
$smwgResultFormats['ofc-bar'] = 'SRFOFC';
$smwgResultFormats['ofc-bar_3d'] = 'SRFOFC';
$smwgResultFormats['ofc-line'] = 'SRFOFC';
//$smwgResultFormats['ofc-scatter_line'] = 'SRFOFC';


SRFOFC::registerResourceModules();