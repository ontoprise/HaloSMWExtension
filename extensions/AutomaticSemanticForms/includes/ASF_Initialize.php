<?php

if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the AutomaticSemanticForms extension. It is not a valid entry point.\n" );
}

if( !defined( 'SF_VERSION' ) ) {
		die( "The extension 'Automatic Semantic Forms' requires the extension 'Semantic Forms'.\n".
			"Please read 'extensions/AutomaticSemanticForms/INSTALL' for further information.\n");
	}

global $asfIP, $asfScriptPath;
$asfIP = $IP . '/extensions/AutomaticSemanticForms';
$asfScriptPath = $wgScriptPath . '/extensions/AutomaticSemanticForms';

	
/*
 * This method must be called in Local Settings
 * 
 * It sets up the Automatic Semantic Forms Extension
 */
function enableAutomaticSemanticForms() {
	global $asfIP, $wgExtensionFunctions, $asfEnableAutomaticSemanticForms;

	define('ASF_VERSION', '{{$VERSION}} [B{{$BUILDNUMBER}}]');
	
	define('automaticsemanticforms', 'true}]');
	
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
	$wgAutoloadClasses['ASFRedLinkHandler'] = $asfIP . '/includes/ASF_RedLinkHandler.php';
	$wgAutoloadClasses['ASFAdminSpecial'] = $asfIP . '/specials/ASF_AdminSpecial.php';
	$wgAutoloadClasses['ASFCategoryAC'] = $asfIP . '/includes/ASF_CategoryAC.php';
	$wgAutoloadClasses['ASFWikiTextManipulator'] = $asfIP . '/includes/ASF_WikiTextManipulator.php';
	$wgAutoloadClasses['ASFFormDefinition'] = $asfIP . '/includes/ASF_FormDefinition.php';
	
	require_once($asfIP . '/specials/ASF_AdminSpecialAjaxAccess.php');
	
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
	global $wgSpecialPages, $wgSpecialPageGroups;
	$wgSpecialPages['FormEdit'] = 'ASFFormEdit';
	$wgSpecialPages['AutomaticSemanticForms'] = array('ASFAdminSpecial');
	$wgSpecialPageGroups['AutomaticSemanticForms'] = 'smwplus_group';
	
	//load form generator in order to initialize constants
	ASFFormGenerator::getInstance();
	
	//deal with red links
	global $asfEnableRedLinkHandler;
	if($asfEnableRedLinkHandler){
		$wgHooks['LinkEnd'][] = 'ASFRedLinkHandler::handleRedLinks';
	}
	
	//initialize input type ajac access poiints
	require_once($asfIP . '/includes/inputtypes/ASF_DataPickerInputType.php');
	require_once($asfIP . '/includes/inputtypes/ASF_DataPickerSettings.php');
	
	//introduce resource modules
	global $wgResourceModules, $asfScriptPath;
	
	$commonProperties = array(
		'localBasePath' => $asfIP,
		'remoteExtPath' => 'AutomaticsemanticForms'
	);
	
	$wgResourceModules['ext.automaticsemanticforms.main'] = 
		$commonProperties + 
		array(
			'scripts' => array('/scripts/asf.js'),
			'styles' => array('/skins/asf.css'),
			'dependencies' => array('ext.jquery.qtip'),
		);
	
	$wgResourceModules['ext.automaticsemanticforms.admin'] = 
		$commonProperties + 
		array(
			'scripts' => array('/scripts/asf-admin.js'),
			'styles' => array('/skins/asf.css'),
			'dependencies' => array('ext.automaticsemanticforms.main'),
		);	
		
		
	$wgResourceModules['ext.automaticsemanticforms.datapicker'] = 
		$commonProperties + 
		array(
			'scripts' => array('/scripts/datapicker.js'),
			'dependencies' => array('ext.automaticsemanticforms.main')
		);
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
			'author'=>"Ingo&nbsp;Steinbauer, Sascha&nbsp;Wagner and Stephan&nbsp;Robotta. Owned by [http://www.ontoprise.de ontoprise GmbH].", 
			'url'=>'http://smwforum.ontoprise.com/smwforum/index.php/Help:Automatic_Semantic_Forms_extension',
			'description' => 'Automatically creates Semantic Forms based on the wiki ontology.');
	
	//replace SFFormPrinter with its ASF implementation
	global $sfgFormPrinter;
	$sfgFormPrinter = new ASFFormPrinter();
	
	ASFFormGeneratorUtils::setupSchemaPropertyConstants();
	
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








