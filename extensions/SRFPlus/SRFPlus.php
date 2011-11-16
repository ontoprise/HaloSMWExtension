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
