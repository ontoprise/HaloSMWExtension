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
global $smwgResultFormats, $wgAutoloadClasses, $srfgIP;
$smwgResultFormats['ofc'] = 'SRFOFC';
$wgAutoloadClasses['SRFOFC'] = $srfgIP . '/ofc/SRF_OFC.php';
