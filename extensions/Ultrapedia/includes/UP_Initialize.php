<?php
/*
 * Created on 01.09.2009
 *
 * Author: Ning
 */
if ( !defined( 'MEDIAWIKI' ) ) die;

define('SMW_ULTRAPEDIA_VERSION', '0.5');

$smwgUltraPediaIP = $IP . '/extensions/UltraPedia';
$smwgUltraPediaScriptPath = $wgScriptPath . '/extensions/UltraPedia';
$smwgUltraPediaEnabled = true;

global $wgExtensionFunctions, $wgHooks, $wgAutoloadClasses;
$wgExtensionFunctions[] = 'smwgUltraPediaSetupExtension';
$wgHooks['LanguageGetMagic'][] = 'UPParserFunctions::languageGetMagic';
$wgAutoloadClasses['UPParserFunctions'] = $smwgUltraPediaIP . '/includes/UP_ParserFunctions.php';

function smwfUltraPediaInitMessages() {
	global $smwgUltraPediaMessagesInitialized;
	if (isset($smwgUltraPediaMessagesInitialized)) return; // prevent double init

	smwfUltraPediaInitUserMessages(); // lazy init for ajax calls

	$smwgUltraPediaMessagesInitialized = true;
}
function smwfUltraPediaInitUserMessages() {
	global $wgMessageCache, $smwgUltraPediaContLang, $wgLanguageCode;
	smwfUltraPediaInitContentLanguage($wgLanguageCode);

	global $smwgUltraPediaIP, $smwgUltraPediaLang;
	if (!empty($smwgUltraPediaLang)) { return; }
	global $wgMessageCache, $wgLang;
	$smwLangClass = 'UP_Language' . str_replace( '-', '_', ucfirst( $wgLang->getCode() ) );

	if (file_exists($smwgUltraPediaIP . '/languages/'. $smwLangClass . '.php')) {
		include_once( $smwgUltraPediaIP . '/languages/'. $smwLangClass . '.php' );
	}
	// fallback if language not supported
	if ( !class_exists($smwLangClass)) {
		global $smwgUltraPediaContLang;
		$smwgUltraPediaLang = $smwgUltraPediaContLang;
	} else {
		$smwgUltraPediaLang = new $smwLangClass();
	}

	$wgMessageCache->addMessages($smwgUltraPediaLang->getUserMsgArray(), $wgLang->getCode());
}
function smwfUltraPediaInitContentLanguage($langcode) {
	global $smwgUltraPediaIP, $smwgUltraPediaContLang;
	if (!empty($smwgUltraPediaContLang)) { return; }

	$smwContLangClass = 'UP_Language' . str_replace( '-', '_', ucfirst( $langcode ) );

	if (file_exists($smwgUltraPediaIP . '/languages/'. $smwContLangClass . '.php')) {
		include_once( $smwgUltraPediaIP . '/languages/'. $smwContLangClass . '.php' );
	}

	// fallback if language not supported
	if ( !class_exists($smwContLangClass)) {
		include_once($smwgUltraPediaIP . '/languages/UP_LanguageEn.php');
		$smwContLangClass = 'UP_LanguageEn';
	}
	$smwgUltraPediaContLang = new $smwContLangClass();
}

function smwfUltraPediaGetAjaxMethodPrefix() {
	$func_name = isset( $_POST["rs"] ) ? $_POST["rs"] : (isset( $_GET["rs"] ) ? $_GET["rs"] : NULL);
	if ($func_name == NULL) return NULL;
	return substr($func_name, 4, 4); // return _xx_ of smwf_xx_methodname, may return FALSE
}

/**
 * Intializes Semantic UltraPedia Extension.
 * Called from UP during initialization.
 */
function smwgUltraPediaSetupExtension() {
	global $smwgUltraPediaIP, $wgExtensionCredits;
	global $wgParser, $wgHooks, $wgAutoloadClasses;

	smwfUltraPediaInitMessages();

	// register hooks
	if( defined( 'MW_SUPPORTS_PARSERFIRSTCALLINIT' ) ) {
		$wgHooks['ParserFirstCallInit'][] = 'UPParserFunctions::registerFunctions';
	} else {
		if ( class_exists( 'StubObject' ) && !StubObject::isRealObject( $wgParser ) ) {
			$wgParser->_unstub();
		}
		UPParserFunctions::registerFunctions( $wgParser );
	}

	global $wgRequest;

	$action = $wgRequest->getVal('action');
	// add some AJAX calls
	if ($action == 'ajax') {
		$method_prefix = smwfUltraPediaGetAjaxMethodPrefix();

		// decide according to ajax method prefix which script(s) to import
		switch($method_prefix) {
			case '_up_' :
				require_once($smwgUltraPediaIP . '/includes/UP_AjaxAccess.php');
				break;
		}
	}

	// Register Credits
	$wgExtensionCredits['parserhook'][]= array(
	'name'=>'Semantic&nbsp;UltraPedia&nbsp;Extension', 'version'=>SMW_ULTRAPEDIA_VERSION,
			'author'=>"Ning Hu, Justin Zhang, [http://smwforum.ontoprise.com/smwforum/index.php/Jesse_Wang Jesse Wang], sponsored by [http://projecthalo.com Project Halo], [http://www.vulcan.com Vulcan Inc.]", 
			'url'=>'http://wiking.vulcan.com/dev', 
			'description' => 'Utilities for UltraPedia.');

	return true;
}

function smwfUltraPediaGetJSLanguageScripts(&$pathlng, &$userpathlng) {
	global $smwgUltraPediaIP, $wgLanguageCode, $smwgUltraPediaScriptPath, $wgUser;

	// content language file
	$lng = '/scripts/Language/UP_Language';
	if (!empty($wgLanguageCode)) {
		$lng .= ucfirst($wgLanguageCode).'.js';
		if (file_exists($smwgUltraPediaIP . $lng)) {
			$pathlng = $smwgUltraPediaScriptPath . $lng;
		} else {
			$pathlng = $smwgUltraPediaScriptPath . '/scripts/Language/UP_LanguageEn.js';
		}
	} else {
		$pathlng = $smwgUltraPediaScriptPath . '/scripts/Language/UP_LanguageEn.js';
	}

	// user language file
	$lng = '/scripts/Language/UP_Language';
	if (isset($wgUser)) {
		$lng .= "User".ucfirst($wgUser->getOption('language')).'.js';
		if (file_exists($smwgUltraPediaIP . $lng)) {
			$userpathlng = $smwgUltraPediaScriptPath . $lng;
		} else {
			$userpathlng = $smwgUltraPediaScriptPath . '/scripts/Language/UP_LanguageUserEn.js';
		}
	} else {
		$userpathlng = $smwgUltraPediaScriptPath . '/scripts/Language/UP_LanguageUserEn.js';
	}
}
?>