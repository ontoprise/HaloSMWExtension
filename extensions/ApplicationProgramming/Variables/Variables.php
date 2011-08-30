<?php
/**
 * Variables extension -- define page-scoped variables
 *
 * @file
 * @ingroup Extensions
 * @version 1.3.1
 * @author Rob Adams
 * @author Tom Hempel
 * @author Daniel Werner
 * @link http://www.mediawiki.org/wiki/Extension:VariablesExtension Documentation
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This file is a MediaWiki extension, it is not a valid entry point' );
}

// Extension credits that will show up on Special:Version
$wgExtensionCredits['parserhook'][] = array(
	'name' => 'Variables',
	'version' => '1.3.1',
	'author' => array( 'Rob Adams', 'Tom Hempel', 'Daniel Werner' ),
	'description' => 'Define page-scoped variables',
	'url' => 'http://www.mediawiki.org/wiki/Extension:VariablesExtension',
);

// @todo FIXME: could this use ParserFirstCallInit hook instead of
// $wgExtensionFunctions?
$wgExtensionFunctions[] = 'wfSetupVariables';

$wgHooks['LanguageGetMagic'][] = 'wfVariablesLanguageGetMagic';

class ExtVariables {
	var $mVariables = array();

	function __construct() {
		global $wgHooks;
		$wgHooks['ParserClearState'][] = &$this;
	}

	function onParserClearState( &$parser ) {
		$this->mVariables = array(); //remove all variables to avoid conflicts with job queue or Special:Import
		return true;
	}

	function vardefine( &$parser, $expr = '', $value = '' ) {
		$this->mVariables[$expr] = $value;
		return '';
	}

	function vardefineecho( &$parser, $expr = '', $value = '' ) {
		$this->mVariables[$expr] = $value;
		return $value;
	}

	function varf( &$parser, $expr = '', $defaultVal = '' ) {
		if ( isset( $this->mVariables[$expr] ) && $this->mVariables[$expr] != '' ) {
			return $this->mVariables[$expr];
		} else {
			return $defaultVal;
		}
	}

	function varexists( &$parser, $expr = '' ) {
		return array_key_exists( $expr, $this->mVariables );
	}
}

function wfSetupVariables() {
	global $wgParser, $wgExtVariables;

	$wgExtVariables = new ExtVariables;

	$wgParser->setFunctionHook( 'vardefine', array( &$wgExtVariables, 'vardefine' ) );
	$wgParser->setFunctionHook( 'vardefineecho', array( &$wgExtVariables, 'vardefineecho' ) );
	$wgParser->setFunctionHook( 'var', array( &$wgExtVariables, 'varf' ) );
	$wgParser->setFunctionHook( 'varexists', array( &$wgExtVariables, 'varexists' ) );
}

function wfVariablesLanguageGetMagic( &$magicWords, $langCode = 0 ) {
	require_once( dirname( __FILE__ ) . '/Variables.i18n.php' );
	foreach( efVariablesWords( $langCode ) as $word => $trans ) {
		$magicWords[$word] = $trans;
	}
	return true;
}

