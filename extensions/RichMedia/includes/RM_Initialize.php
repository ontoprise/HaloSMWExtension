<?php
/*
 * Created on 24.03.2009
 *
 * Author: Benjamin
 */

//this extension does only work if the Halo extension is enabled
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the RichMedia extension. It is not a valid entry point.\n" );
}

define('SMW_RM_VERSION', '1.1-for-SMW-1.4.x');

global $smwgRMIP, $wgHooks; 
$smwgRMIP = $IP . '/extensions/RichMedia';
$smwgRMScriptPath = $wgScriptPath . '/extensions/RichMedia';

include_once('RM_AdditionalMIMETypes.php');
global $smwgRMFormByNamespace;

$smwgRMFormByNamespace = array(
	NS_IMAGE => 'RMImage',
	NS_PDF => 'RMPdf',
	NS_DOCUMENT => 'RMDocument',
	NS_AUDIO => 'RMAudio',
	NS_VIDEO => 'RMVideo',
	'RMUpload' => 'RMUpload'
);

/**
 * Configures Rich Media Extension for initialization.
 * (Must be called *AFTER* SMWHalo is intialized.)
 */
function enableRichMediaExtension() {
	//tell SMW to call this function during initialization
	global $wgExtensionFunctions, $smwgRMIP, $wgHooks, $wgAutoloadClasses, $wgSpecialPages;
				
	$wgExtensionFunctions[] = 'smwfRMSetupExtension';
	
	$wgHooks['ParserBeforeStrip'][] = 'smwfRegisterRMForm';
	$wgHooks['ParserBeforeStrip'][] = 'smwfRegisterRMLink';

	//Add a hook to initialise the magic word for the {{#rmf:}} Syntax Parser
	$wgHooks['LanguageGetMagic'][] = 'RMFormUsage_Magic';
	//Add a hook to initialise the magic word for the {{#rml:}} Syntax Parser
	$wgHooks['LanguageGetMagic'][] = 'RMLinkUsage_Magic';
	//Add a hook to initialise the magic word for the additional image attribute preview
	$wgHooks['LanguageGetMagic'][] = 'RMImagePreviewUsage_Magic';
	// workaround: because the necessary scripts has been only loaded by the parser function, when action=purge.
	$wgHooks['BeforePageDisplay'][] = 'smwRMFormAddHTMLHeader';
	
	//EmbedWindow
	$wgSpecialPages['EmbedWindow'] = 'RMEmbedWindow';
	$wgAutoloadClasses['RMEmbedWindow'] = $smwgRMIP . '/includes/RM_EmbedWindow.php';
	
	// Conversion of documents (PDF, MS Office)
	global $smwgEnableUploadConverter;
	if ($smwgEnableUploadConverter) {
		global $wgExtensionMessagesFiles;
		$wgAutoloadClasses['UploadConverter'] = $smwgRMIP . '/specials/SMWUploadConverter/SMW_UploadConverter.php';
		$wgExtensionMessagesFiles['UploadConverter'] = $smwgRMIP . '/specials/SMWUploadConverter/SMW_UploadConverterMessages.php';

		$wgHooks['UploadComplete'][] = 'UploadConverter::convertUpload';
	}
}

/**
 * Intializes Rich Media Extension.
 * Called from SMW during initialization.
 */
function smwfRMSetupExtension() {
	global $wgHooks, $wgExtensionCredits, $wgAutoloadClasses, $wgSpecialPages; 
	global $smwgRMIP, $wgSpecialPageGroups, $wgRequest, $wgContLang;

	smwfRMInitMessages();

	$wgAutoloadClasses['RMForm'] = $smwgRMIP . '/includes/RM_Form.php';	
		
	// Register Credits
	$wgExtensionCredits['parserhook'][]=array('name'=>'Rich&nbsp;Media&nbsp;Extension', 'version'=>SMW_RM_VERSION,
		'author'=>"Benjamin&nbsp;Langguth, Sascha&nbsp;Wagner and Daniel&nbsp;Hansch. Maintained by [http://www.ontoprise.de Ontoprise].", 
		'url'=>'https://sourceforge.net/projects/halo-extension', 
		'description' => 'The Rich Media Extension provides an ontology to allow easy handling of media such as documents, images, doc, pdf etc. The ontology comprises templates and forms and examples. It enhances a one-click media upload of files and enables annotation of media in a simple way.');
	
	return true;
}


function smwfRegisterRMForm( &$parser ) {
	
	$parser->setFunctionHook( 'RMFormUsage', 'smwfProcessRMFormParserFunction' );

	return true; // always return true, in order not to stop MW's hook processing!	
}
function smwfRegisterRMLink( &$parser ) {
	
	$parser->setFunctionHook( 'RMLinkUsage', 'smwfProcessRMLinkParserFunction' );

	return true; // always return true, in order not to stop MW's hook processing!	
}

/**
 * The {{#rmf }} parser function processing part.
 */
function smwfProcessRMFormParserFunction(&$parser) {
	$params = func_get_args();
	array_shift( $params ); // we already know the $parser ...
	
	// now we need the css and scripts. so add them
	global $wgHooks; 
	//$wgHooks['BeforePageDisplay'][] = 'smwRMFormAddHTMLHeader';

	return RMForm::createRichMediaForm($params);
}

/**
 * The {{#rml }} parser function processing part.
 */
function smwfProcessRMLinkParserFunction(&$parser) {
	$params = func_get_args();
	array_shift( $params ); // we already know the $parser ...
	
	// now we need the css and scripts. so add them
	global $wgHooks; 
	//$wgHooks['BeforePageDisplay'][] = 'smwRMFormAddHTMLHeader';

	return RMForm::createRichMediaLink($params);
}

function RMFormUsage_Magic(&$magicWords, $langCode){
	$magicWords['RMFormUsage'] = array( 0, 'rmf' );
	return true;
}

function RMLinkUsage_Magic(&$magicWords, $langCode){
	$magicWords['RMLinkUsage'] = array( 0, 'rml' );
	return true;
}

function RMImagePreviewUsage_Magic(&$magicWords, $langCode){
	$magicWords['img_preview'] = array( 0, 'preview' );
	return true;
}

function smwRMFormAddHTMLHeader(&$out){
	global $smwgRMScriptPath, $smwgHaloScriptPath, $sfgScriptPath;
	
	static $rmScriptLoaded = false;
	
	if(!$rmScriptLoaded){
		$jsm = SMWResourceManager::SINGLETON();
		# Prototype needed!
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/prototype.js');
		$jsm->addScriptIf($smwgRMScriptPath. '/scripts/richmedia.js');
		# Floatbox needed!
		$jsm->addScriptIf($sfgScriptPath .  '/libs/floatbox.js');
		$jsm->serializeScripts($out);
	
		#Floatbox css file:
		$jsm->addCSSIf($sfgScriptPath . '/skins/floatbox.css');
		$jsm->serializeCSS($out);
		$rmScriptLoaded = true;
	}
	return true;
}


#TODO: international content messages! 
function smwfRMInitMessages() {
	global $smwgRMMessagesInitialized;
	if (isset($smwgRMMessagesInitialized)) return; // prevent double init
	
	smwfRMInitUserMessages(); // lazy init for ajax calls
	
	$smwgRMMessagesInitialized = true;
}

/**
 * Registers SMW Rich Media Content messages.
 */
function smwfRMInitContentLanguage($langcode) {
	global $smwgRMIP, $smwgRMContLang;
	if (!empty($smwgRMContLang)) { return; }

	$smwContLangClass = 'SMW_RMLanguage' . str_replace( '-', '_', ucfirst( $langcode ) );

	if (file_exists($smwgRMIP . '/languages/'. $smwContLangClass . '.php')) {
		include_once( $smwgRMIP . '/languages/'. $smwContLangClass . '.php' );
	}

	// fallback if language not supported
	if ( !class_exists($smwContLangClass)) {
		include_once($smwgRMIP . '/languages/SMW_RMLanguageEn.php');
		$smwContLangClass = 'SMW_RMLanguageEn';
	}
	$smwgRMContLang = new $smwContLangClass();
}

/**
 * Registers Rich Media extension User messages.
 */
function smwfRMInitUserMessages() {
	global $wgMessageCache, $smwgRMContLang, $wgLanguageCode;
	smwfRMInitContentLanguage($wgLanguageCode);

	global $smwgRMIP, $smwgRMLang;
	if (!empty($smwgRMLang)) { return; }
	global $wgMessageCache, $wgLang;
	$smwLangClass = 'SMW_RMLanguage' . str_replace( '-', '_', ucfirst( $wgLang->getCode() ) );

	if (file_exists($smwgRMIP . '/languages/'. $smwLangClass . '.php')) {
		include_once( $smwgRMIP . '/languages/'. $smwLangClass . '.php' );
	}
	// fallback if language not supported
	if ( !class_exists($smwLangClass)) {
		global $smwgRMContLang;
		$smwgRMLang = $smwgRMContLang;
	} else {
		$smwgRMLang = new $smwLangClass();
	}

	$wgMessageCache->addMessages($smwgRMLang->getUserMsgArray(), $wgLang->getCode());
}

/**
 * Add appropriate JS language script
 */
function smwfRMAddJSLanguageScripts(& $jsm, $mode = "all", $namespace = -1, $pages = array()) {
	global $wgLanguageCode, $smwgDIScriptPath, $wgUser;
	
	// content language file
	$lng = '/scripts/Language/SMWDI_Language';
	
	$jsm->addScriptIf($smwgDIScriptPath . $lng.".js", $mode, $namespace, $pages);
	
	if (!empty($wgLanguageCode)) {
		$lng .= ucfirst($wgLanguageCode).'.js';
		if (file_exists($smwgDIScriptPath . $lng)) {
			$jsm->addScriptIf($smwgDIScriptPath . $lng, $mode, $namespace, $pages);
		} else {
			$jsm->addScriptIf($smwgDIScriptPath . '/scripts/Language/SMWDI_LanguageEn.js', $mode, $namespace, $pages);
		}
	} else {
		$jsm->addScriptIf($smwgDIScriptPath . '/scripts/Language/SMWDI_LanguageEn.js', $mode, $namespace, $pages);
	}

	// user language file
	$lng = '/scripts/Language/SMWDI_Language';
	if (isset($wgUser)) {
		$lng .= "User".ucfirst($wgUser->getOption('language')).'.js';
		if (file_exists($smwgDIScriptPath . $lng)) {
			$jsm->addScriptIf($smwgDIScriptPath . $lng, $mode, $namespace, $pages);
		} else {
			$jsm->addScriptIf($smwgDIScriptPath . '/scripts/Language/SMWDI_LanguageUserEn.js', $mode, $namespace, $pages);
		}
	} else {
		$jsm->addScriptIf($smwgDIScriptPath . '/scripts/Language/SMWDI_LanguageUserEn.js', $mode, $namespace, $pages);
	}
}

?>