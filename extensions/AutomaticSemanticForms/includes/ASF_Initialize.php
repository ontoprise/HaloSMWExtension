<?php

if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the AutomaticSemanticForms extension. It is not a valid entry point.\n" );
}

if( !defined( 'SF_VERSION' ) ) {
		die( "The extension 'Automatic Semantic Forms' requires the extension 'Semantic Forms'.\n".
			"Please read 'extensions/AutomaticSemanticForms/INSTALL' for further information.\n");
	}

global $asfIP;
$asfIP = $IP . '/extensions/AutomaticSemanticForms';

	
/*
 * This method must be called in Local Settings
 * 
 * It sets up the Automatic Semantic Forms Extension
 */
	function enableAutomaticSemanticForms() {
	global $asfIP, $wgExtensionFunctions, $asfEnableAutomaticSemanticForms;

	define('ASF_VERSION', '{{$VERSION}}');
	
	$asfEnableAutomaticSemanticForms = true;
	
	$wgExtensionFunctions[] = 'asfSetupExtension';
	
	require_once($asfIP . '/includes/ASF_Settings.php');
	
	//autoload classes
	global $wgAutoloadClasses;
	$wgAutoloadClasses['ASFFormEditTab'] = $asfIP . '/includes/ASF_FormEditTab.php';
	$wgAutoloadClasses['ASFFormGenerator'] = $asfIP . '/includes/ASF_FormGenerator.php';
	$wgAutoloadClasses['ASFFormGeneratorUtils'] = $asfIP . '/includes/ASF_FormGeneratorUtils.php';
	$wgAutoloadClasses['ASFPropertyFormData'] = $asfIP . '/includes/ASF_PropertyFormData.php';
	$wgAutoloadClasses['ASFCategoryFormData'] = $asfIP . '/includes/ASF_CategoryFormData.php';
	$wgAutoloadClasses['ASFFormPrinter'] = $asfIP . '/includes/ASF_FormPrinter.php';
	$wgAutoloadClasses['ASFParserFunctions'] = $asfIP . '/includes/ASF_ParserFunctions.php';
	$wgAutoloadClasses['ASFFormEdit'] = $asfIP . '/specials/ASF_FormEdit.php';
	$wgAutoloadClasses['ASFCategorySectionStructureProcessor'] = $asfIP . '/includes/ASF_CategorySectionStructureProcessor.php';
	$wgAutoloadClasses['ASFUnresolvedAnnotationsFormData'] = $asfIP . '/includes/ASF_UnresolvedAnnotationsFormData.php';
	
	global $wgHooks;
	//create edit with form tab
	$wgHooks['SkinTemplateTabs'][] = 'ASFFormEditTab::displayTab';
	$wgHooks['SkinTemplateNavigation'][] = 'ASFFormEditTab::displayTab2';
	
	//add handler for the formedit action
	$wgHooks['UnknownAction'][] = 'ASFFormEditTab::displayForm';
	
	//Setup parser functions
	$wgHooks['ParserFirstCallInit'][] = 'ASFParserFunctions::registerFunctions';
	$wgHooks['LanguageGetMagic'][] = 'ASFParserFunctions::languageGetMagic';
	
	//Register special pages
	global $wgSpecialPages;
	$wgSpecialPages['FormEdit'] = 'ASFFormEdit';
	
	//load form generator in order to initialize constants
	ASFFormGenerator::getInstance();
}

/*
 * Called by MW to setup this extension
 */
function asfSetupExtension(){
	global $wgHooks, $wgExtensionCredits; 
	
	asfInitMessages();
	
	$wgExtensionCredits['parserhook'][]=array(
			'name'=>'Automatic&nbsp;Semantic&nbsp;Forms', 
			'version'=>ASF_VERSION,
			'author'=>"Ingo&nbsp;Steinbauer, Sascha&nbsp;Wagner and Stephan&nbsp;Robotta. Maintained by [http://www.ontoprise.de Ontoprise].", 
			'url'=>'https://sourceforge.net/projects/halo-extension', 
			'description' => 'Automatically creates Semantic Forms based on the Wiki ontology.');
	
	//replace SFFormPrinter with its ASF implementation
	global $sfgFormPrinter;
	$sfgFormPrinter = new ASFFormPrinter();
	
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
