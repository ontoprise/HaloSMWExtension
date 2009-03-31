<?php
/*
 * Created on 24.03.2009
 *
 * Author: Benjamin
 */

//this extension does only work if the Halo extension is enabled
if ( !defined( 'SMW_HALO_VERSION' ) ) die;

define('SMW_RM_VERSION', '1.0-for-SMW-1.4');

global $smwgRMIP, $wgHooks; 
$smwgRMIP = $IP . '/extensions/RichMedia';
$smwgRMScriptPath = $wgScriptPath . '/extensions/RichMedia';

include_once('extensions/SMWHalo/includes/SMW_MIME_settings.php');
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
	global $wgExtensionFunctions, $smwgRMIP, $wgHooks;
	
	//require the Semantic Forms!?!
	//require_once($smwgDIIP. '/specials/WebServices/SMW_WebServiceManager.php');
			
	$wgExtensionFunctions[] = 'smwfRMSetupExtension';
	
	$wgHooks['ParserBeforeStrip'][] = 'smwfRegisterRMForm';

	//Add a hook to initialise the magic word for the {{#rmf:}} Syntax Parser
	$wgHooks['LanguageGetMagic'][] = 'RMFormUsage_Magic';
	// workaround: because the script are only loaded by the parser function, when action=purge.
	$wgHooks['BeforePageDisplay'][] = 'smwRMFormAddHTMLHeader';
}

/**
 * Intializes Rich Media Extension.
 * Called from SMW during initialization.
 */
function smwfRMSetupExtension() {
	global $wgHooks, $wgExtensionCredits, $wgAutoloadClasses, $wgSpecialPages; 
	global $smwgRMIP, $wgSpecialPageGroups, $wgRequest, $wgContLang;

	smwfRMInitMessages();

	// add some AJAX calls
	$action = $wgRequest->getVal('action');
	if ($action == 'ajax') {
		// Do not install the extension for ajax calls
		return;
				
	} else { // otherwise register special pages

		$wgAutoloadClasses['RMForm'] = $smwgRMIP . '/includes/RM_Form.php';	
		
		// Register Credits
		$wgExtensionCredits['parserhook'][]=array('name'=>'Rich&nbsp;Media&nbsp;Extension', 'version'=>SMW_RM_VERSION,
			'author'=>"Benjamin&nbsp;Langguth, Sascha&nbsp;Wagner and Daniel&nbsp;Hansch. Maintained by [http://www.ontoprise.de Ontoprise].", 
			'url'=>'https://sourceforge.net/projects/halo-extension', 
			'description' => '...');
	}
	return true;
}


function smwfRegisterRMForm( &$parser ) {
	
	$parser->setFunctionHook( 'RMFormUsage', 'smwfProcessRMFormParserFunction' );

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
	$wgHooks['BeforePageDisplay'][] = 'smwRMFormAddHTMLHeader';

	return RMForm::createRichMediaForm($params);
}


function RMFormUsage_Magic(&$magicWords, $langCode){
	$magicWords['RMFormUsage'] = array( 0, 'rmf' );
	return true;
}

function smwRMFormAddHTMLHeader(&$out){
	global $wgOut, $smwgRMScriptPath;
	
	//add the scripts for Semantic Forms
	SFUtils::addJavascriptAndCSS();
	$wgOut->addScript('<script type="text/javascript" src="' . $smwgRMScriptPath . '/scripts/richmedia.js"></script>' . "\n");	
	$jsm = SMWResourceManager::SINGLETON();
	//css file:
	$jsm->addCSSIf($smwgRMScriptPath . '/skins/richmedia.css');
	$jsm->serializeScripts($out);
	$jsm->serializeCSS($out);
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