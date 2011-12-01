<?php

/**
 * Main entry point for the SemanticResultFormats extension.
 * http://www.mediawiki.org/wiki/Extension:Semantic_Result_Formats
 * 
 * @file SemanticResultFormats.php
 * @ingroup SemanticResultFormats
 * 
 * @author Jeroen De Dauw
 */

/**
 * This documentation group collects source code files belonging to SemanticResultFormats.
 * 
 * @defgroup SemanticResultFormats SemanticResultFormats
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'Not an entry point.' );
}

define( 'SRFP_VERSION', '1.0.0' );

// FIXME: hardcoded path
$srfpgScriptPath = $wgScriptPath . '/extensions/SRFPlus';
$srfpgIP = dirname( __FILE__ );

// Require the settings file.
require $srfpgIP . '/ofc/SRF_OFC_Init.php';
require $srfpgIP . '/Simile/SRF_Simile_Init.php';
require $srfpgIP . '/Exhibit/SRF_Exhibit_Init.php';
if( defined( 'SMW_AGGREGATION_VERSION' ) ) {
	require $srfpgIP . '/Group/SRF_Group_Init.php';
}

$wgExtensionCredits[defined( 'SEMANTIC_EXTENSION_TYPE' ) ? 'semantic' : 'other'][] = array(
	'path' => __FILE__,
	'name' => 'Semantic Result Formats Plus',
	'version' => SRFP_VERSION,
	'author' => array(
		'Ning Hu',
		'[http://smwforum.ontoprise.com/smwforum/index.php/Jesse_Wang Jesse Wang]',
		'sponsored by [http://projecthalo.com Project Halo], [http://www.vulcan.com Vulcan Inc.]',
	),
	'url' => '',
	'description' => 'Semantic Result Formats Plus'
);

global $wgExtensionFunctions;
$wgExtensionFunctions[] = 'SRFPlusSetupExtension';
function SRFPlusSetupExtension() {
	global $wgHooks, $wgRequest, $srfpgIP;
	$wgHooks['smwInitializeTables'][] = 'srfpGMapInitializeTables';

	$action = $wgRequest->getVal('action');
	// add some AJAX calls
	if ($action == 'ajax') {
		$func_name = isset( $_POST["rs"] ) ? $_POST["rs"] : (isset( $_GET["rs"] ) ? $_GET["rs"] : NULL);
		if ($func_name == NULL) return NULL;
		if (substr( $func_name, 0, strlen( 'srf_' ) ) === 'srf_' ) {
			require_once($srfpgIP . '/includes/SRF_AjaxAccess.php');
		}
	}
	return true;
}
function srfpGMapInitializeTables() {
	global $srfpgIP;
	require_once( $srfpgIP . '/includes/SRF_Storage.php' );
	SRFStorage::getDatabase()->setup(true);
	
	return true;
}
