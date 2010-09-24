<?php

if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the AutomaticSemanticForms extension. It is not a valid entry point.\n" );
}

if( !defined( 'SF_VERSION' ) ) {
		die( "The extension 'Automatic Semantic Forms' requires the extension 'Semantic Forms'.\n".
			"Please read 'extensions/AutomaticSemanticForms/INSTALL' for further information.\n");
	}

define('ASF_VERSION', '{{$VERSION}}');

global $asfIP; 
$asfIP = $IP . '/extensions/AutomaticSemanticForms';

function enableAutomaticSemanticForms() {
	global $wgExtensionFunctions, $asfEnableAutomaticSemanticForms;
	
	$asfEnableAutomaticSemanticForms = true;
	
	$wgExtensionFunctions[] = 'asfSetupExtension';
}

function asfSetupExtension(){
	global $wgHooks, $wgExtensionCredits; 
	
	$wgHooks['BeforePageDisplay'][]='smwDITBAddHTMLHeader';
	
	asfInitMessages();
	
	$wgExtensionCredits['parserhook'][]=array(
			'name'=>'Automatic&nbsp;Semantic&nbsp;Forms', 
			'version'=>ASF_VERSION,
			'author'=>"Ingo&nbsp;Steinbauer, Sascha&nbsp;Wagner and Stephan&nbsp;Robotta. Maintained by [http://www.ontoprise.de Ontoprise].", 
			'url'=>'https://sourceforge.net/projects/halo-extension', 
			'description' => 'Automatically creates Semantic Forms based on the Wiki ontology.');
	
	global $asfIP;
	require_once($asfIP.'/includes/ASF_FormGenerator.php');
	//ASFFormGenerator::getInstance()->generateFromCategory('FemaleTeacher');
	
	return true;
}

function asfInitMessages() {
	global $asfMessagesInitialized;
	if (isset($asfMessagesInitialized)) return;
	
	asfInitUserMessages();
	
	$asfMessagesInitialized = true;
}

function asfInitUserMessages() {
	global $wgMessageCache, $asfContLang, $wgLanguageCode;
	
	asfInitContentLanguage($wgLanguageCode);
	
	global $asfIP, $asfLang;
	
	if (!empty($asfLang)) { return; }
	
	global $wgMessageCache, $wgLang;
	$langClass = 'ASF_Language' . str_replace( '-', '_', ucfirst( $wgLang->getCode() ) );

	if (file_exists($asfIP . '/languages/'. $langClass . '.php')) {
		include_once( $asfIP . '/languages/'. $langClass . '.php' );
	}
	
	$langClass = str_replace('_', '', $langClass);
	
	if ( !class_exists($langClass)) {
		global $asfContLang;
		$asfLang = $asfContLang;
	} else {
		$asfLang = new $langClass();
	}

	$wgMessageCache->addMessages($asfLang->getUserMsgArray(), $wgLang->getCode());
}

function asfInitContentLanguage($langcode) {
	global $asfIP, $asfContLang;
	if (!empty($asfContLang)) return;

	$langClass = 'ASF_Language' . str_replace( '-', '_', ucfirst( $langcode ) );

	if (file_exists($asfIP . '/languages/'. $langClass . '.php')) {
		include_once( $asfIP . '/languages/'. $langClass . '.php' );
	}
	
	$langClass = str_replace('_', '', $langClass);
	
	// fallback if language not supported
	if ( !class_exists($langClass)) {
		include_once($asfIP . '/languages/ASF_LanguageEn.php');
		$langClass = 'ASFLanguageEn';
	}
	$asfContLang = new $langClass();
}
