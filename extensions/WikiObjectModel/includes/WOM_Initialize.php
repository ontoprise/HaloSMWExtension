<?php
/*
 * Created on 22.11.2010
 *
 * Author: ning
 */
if ( !defined( 'MEDIAWIKI' ) ) die;

define( 'WOM_VERSION', '0.1' );

$wgOMIP = $IP . '/extensions/WikiObjectModel';
$wgOMScriptPath = $wgScriptPath . '/extensions/WikiObjectModel';

global $wgExtensionFunctions;
$wgExtensionFunctions[] = 'wgOMSetupExtension';

require_once( $wgOMIP . '/includes/WOM_Setup.php' );


function smwfOMInitContentLanguage( $langcode ) {
	global $wgOMIP, $wgOMContLang;
	if ( !empty( $wgOMContLang ) ) { return; }

	$mwContLangClass = 'WOMLanguage' . str_replace( '-', '_', ucfirst( $langcode ) );

	if ( file_exists( $wgOMIP . '/languages/' . $mwContLangClass . '.php' ) ) {
		include_once( $wgOMIP . '/languages/' . $mwContLangClass . '.php' );
	}

	// fallback if language not supported
	if ( !class_exists( $mwContLangClass ) ) {
		include_once( $wgOMIP . '/languages/WOMLanguageEn.php' );
		$mwContLangClass = 'WOMLanguageEn';
	}
	$wgOMContLang = new $mwContLangClass();
}

function smwfOMInitMessages() {
	global $wgOMMessagesInitialized;
	if ( isset( $wgOMMessagesInitialized ) ) return; // prevent double init

	wfOMInitUserMessages(); // lazy init for ajax calls

	$wgOMMessagesInitialized = true;
}
function wfOMInitUserMessages() {
	global $wgMessageCache, $wgOMContLang, $wgLanguageCode;
	smwfOMInitContentLanguage( $wgLanguageCode );

	global $wgOMIP, $wgOMLang;
	if ( !empty( $wgOMLang ) ) { return; }
	global $wgMessageCache, $wgLang;
	$mwLangClass = 'WOMLanguage' . str_replace( '-', '_', ucfirst( $wgLang->getCode() ) );

	if ( file_exists( $wgOMIP . '/languages/' . $mwLangClass . '.php' ) ) {
		include_once( $wgOMIP . '/languages/' . $mwLangClass . '.php' );
	}
	// fallback if language not supported
	if ( !class_exists( $mwLangClass ) ) {
		global $wgOMContLang;
		$wgOMLang = $wgOMContLang;
	} else {
		$wgOMLang = new $mwLangClass();
	}

	$wgMessageCache->addMessages( $wgOMLang->getUserMsgArray(), $wgLang->getCode() );
}


/**
 * Intializes Semantic ObjectModel Extension.
 * Called from WOM during initialization.
 */
function wgOMSetupExtension() {
	global $wgOMIP, $wgHooks, $wgExtensionCredits, $wgAvailableRights;
	global $wgAutoloadClasses, $wgSpecialPages, $wgSpecialPageGroups;

	smwfOMInitMessages();

	$wgAutoloadClasses['WOMProcessor'] = $wgOMIP . '/includes/WOM_Processor.php';

	// Register Credits
	$wgExtensionCredits['parserhook'][] = array(
	'name' => 'Wiki&#160;ObjectModel&#160;Extension', 'version' => WOM_VERSION,
			'author' => "Ning Hu, Justin Zhang, [http://smwforum.ontoprise.com/smwforum/index.php/Jesse_Wang Jesse Wang], sponsored by [http://projecthalo.com Project Halo], [http://www.vulcan.com Vulcan Inc.]",
			'url' => 'http://wiking.vulcan.com/dev',
			'description' => 'Easy Page Object Model for wiki user.' );

	return true;
}
