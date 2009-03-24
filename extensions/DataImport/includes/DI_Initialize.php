<?php
/*
 * Created on 10.02.2009
 *
 * Author: ingo
 */

//this extension does only work if the Halo extension is enabled
if ( !defined( 'SMW_HALO_VERSION' ) ) die;

define('SMW_DI_VERSION', '1.4-for-SMW-1.4');

global $smwgDIIP, $wgHooks; 
$smwgDIIP = $IP . '/extensions/DataImport';
$smwgDIScriptPath = $wgScriptPath . '/extensions/DataImport';
$wgHooks['smwInitializeTables'][] = 'smwfDIInitializeTables';

/**
 * Configures Data Import Extension for initialization.
 * Must be called *AFTER* SMWHalo is intialized.
 */
function enableDataImportExtension() {
	//tell SMW to call this function during initialization
	global $wgExtensionFunctions, $smwgEnableDataImportExtension, $smwgDIIP;
	
	//so that other extensions like the gardening framework know about
	//the Data Import Extension
	$smwgEnableDataImportExtension = true;
	require_once($smwgDIIP. '/specials/WebServices/SMW_WebServiceManager.php');
			
	$wgExtensionFunctions[] = 'smwfDISetupExtension';
}

/**
 * Intializes Data Import Extension.
 * Called from SMW during initialization.
 */
function smwfDISetupExtension() {
	global $wgHooks, $wgExtensionCredits, $wgAutoloadClasses, $wgSpecialPages; 
	global $smwgDIIP, $wgSpecialPageGroups, $wgRequest, $wgContLang;

	$spns_text = $wgContLang->getNsText(NS_SPECIAL);

	// register AddHTMLHeader functions for special pages
	// to include javascript and css files (only on special page requests).
	if (stripos($wgRequest->getRequestURL(), $spns_text.":") !== false
			|| stripos($wgRequest->getRequestURL(), $spns_text."%3A") !== false) {
		$wgHooks['BeforePageDisplay'][]='smwDIWSAddHTMLHeader';
		$wgHooks['BeforePageDisplay'][]='smwDITIAddHTMLHeader';
	}
	
	$wgHooks['BeforePageDisplay'][]='smwDITBAddHTMLHeader';
	
	smwfDIInitMessages();
	WebServiceManager::registerWWSNamespaces();

	// add some AJAX calls
	$action = $wgRequest->getVal('action');
	if ($action == 'ajax') {
		$method_prefix = smwfDIGetAjaxMethodPrefix();

		// decide according to ajax method prefix which script(s) to import
		switch($method_prefix) {
			case '_ti_' : require_once($smwgDIIP . '/specials/TermImport/SMW_CL.php');
			break;
			case '_wsu_' : require_once($smwgDIIP . '/specials/WebServices/SMW_UseWebServiceAjaxAccess.php');
			break;
			case '_ws_' :  require_once($smwgDIIP . '/specials/WebServices/SMW_WebServiceRepositoryAjaxAccess.php');
				require_once($smwgDIIP . '/specials/WebServices/SMW_DefineWebServiceAjaxAccess.php');
				break;
		} 
				
	} else { // otherwise register special pages
		WebServiceManager::initWikiWebServiceExtension();
		
		$wgAutoloadClasses['SMWTermImportSpecial'] = $smwgDIIP . '/specials/TermImport/SMW_TermImportSpecial.php';
		$wgSpecialPages['TermImport'] = array('SMWTermImportSpecial');
		$wgSpecialPageGroups['TermImport'] = 'di_group';
		//todo:add to proper group

		$wgAutoloadClasses['SMWWebServiceRepositorySpecial'] = $smwgDIIP . '/specials/WebServices/SMW_WebServiceRepositorySpecial.php';
		$wgSpecialPages['WebServiceRepository'] = array('SMWWebServiceRepositorySpecial');
		$wgSpecialPageGroups['WebServiceRepository'] = 'di_group';

		$wgAutoloadClasses['SMWDefineWebServiceSpecial'] = $smwgDIIP . '/specials/WebServices/SMW_DefineWebServiceSpecial.php';
		$wgSpecialPages['DefineWebService'] = array('SMWDefineWebServiceSpecial');
		$wgSpecialPageGroups['DefineWebService'] = 'di_group';
		
		$wgAutoloadClasses['SMWUseWebServiceSpecial'] = $smwgDIIP . '/specials/WebServices/SMW_UseWebServiceSpecial.php';
		$wgSpecialPages['UseWebService'] = array('SMWUseWebServiceSpecial');
		$wgSpecialPageGroups['UseWebService'] = 'di_group';

		// Register Credits
		$wgExtensionCredits['parserhook'][]=array('name'=>'Data&nbsp;Import&nbsp;Extension', 'version'=>SMW_DI_VERSION,
			'author'=>"Thomas&nbsp;Schweitzer, Ingo&nbsp;Steinbauer, Sascha&nbsp;Wagner and Daniel&nbsp;Hansch. Maintained by [http://www.ontoprise.de Ontoprise].", 
			'url'=>'https://sourceforge.net/projects/halo-extension', 
			'description' => 'Allows to import data from a lot of different sources.');
	}

	//for initializing web service database tables
	//$wgHooks['smwInitializeTables'][] = 'smwfDIInitializeTables';

	
	//load the Gardening Bots
	
	require_once("$smwgDIIP/specials/TermImport/SMW_TermImportBot.php");
	require_once("$smwgDIIP/specials/WebServices/SMW_WSCacheBot.php");
	require_once("$smwgDIIP/specials/WebServices/SMW_WSUpdateBot.php");
	return true;
}

/*
 * Creates or updates additional tables needed by the Data Import extension.
 * Called from SMW when admin re-initializes tables
 */
function smwfDIInitializeTables() {
	global $smwgDIIP;
	require_once($smwgDIIP . '/specials/WebServices/SMW_WebServiceManager.php');
	WebServiceManager::initDatabaseTables();

	return true;
}

function smwfDIInitMessages() {
	global $smwgDIMessagesInitialized;
	if (isset($smwgDIMessagesInitialized)) return; // prevent double init
	
	smwfDIInitUserMessages(); // lazy init for ajax calls
	
	$smwgDIMessagesInitialized = true;
}

/**
 * Registers SMW Data Import Content messages.
 */
function smwfDIInitContentLanguage($langcode) {
	global $smwgDIIP, $smwgDIContLang;
	if (!empty($smwgDIContLang)) { return; }

	$smwContLangClass = 'SMW_DILanguage' . str_replace( '-', '_', ucfirst( $langcode ) );

	if (file_exists($smwgDIIP . '/languages/'. $smwContLangClass . '.php')) {
		include_once( $smwgDIIP . '/languages/'. $smwContLangClass . '.php' );
	}

	// fallback if language not supported
	if ( !class_exists($smwContLangClass)) {
		include_once($smwgDIIP . '/languages/SMW_DILanguageEn.php');
		$smwContLangClass = 'SMW_DILanguageEn';
	}
	$smwgDIContLang = new $smwContLangClass();
}

/**
 * Registers Data Import extension User messages.
 */
function smwfDIInitUserMessages() {
	global $wgMessageCache, $smwgDIContLang, $wgLanguageCode;
	smwfDIInitContentLanguage($wgLanguageCode);

	global $smwgDIIP, $smwgDILang;
	if (!empty($smwgDILang)) { return; }
	global $wgMessageCache, $wgLang;
	$smwLangClass = 'SMW_DILanguage' . str_replace( '-', '_', ucfirst( $wgLang->getCode() ) );

	if (file_exists($smwgDIIP . '/languages/'. $smwLangClass . '.php')) {
		include_once( $smwgDIIP . '/languages/'. $smwLangClass . '.php' );
	}
	// fallback if language not supported
	if ( !class_exists($smwLangClass)) {
		global $smwgDIContLang;
		$smwgDILang = $smwgDIContLang;
	} else {
		$smwgDILang = new $smwLangClass();
	}

	$wgMessageCache->addMessages($smwgDILang->getUserMsgArray(), $wgLang->getCode());
}

/**
 * Add appropriate JS language script
 */
function smwfDIAddJSLanguageScripts(& $jsm, $mode = "all", $namespace = -1, $pages = array()) {
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

function smwDIWSAddHTMLHeader(&$out) {
	global $wgTitle;
	if ($wgTitle->getNamespace() != NS_SPECIAL) return true;

	global $smwgDIScriptPath;
	
	$jsm = SMWResourceManager::SINGLETON();

	$jsm->addScriptIf($smwgDIScriptPath .  '/scripts/WebServices/webservices-rep.js', "all", -1, array(NS_SPECIAL.":WebServiceRepository"));
	$jsm->addScriptIf($smwgDIScriptPath .  '/scripts/WebServices/def-webservices.js', "all", -1, array(NS_SPECIAL.":DefineWebService"));
	$jsm->addScriptIf($smwgDIScriptPath .  '/scripts/WebServices/use-webservice.js', "all", -1, array(NS_SPECIAL.":UseWebService"));

	smwfDIAddJSLanguageScripts($jsm, "all", -1, array(NS_SPECIAL.":DefineWebService", NS_SPECIAL.":DefineWebService", NS_SPECIAL.":WebServiceRepository"));
	
	$jsm->addCSSIf($smwgDIScriptPath . '/skins/webservices/webservices.css', "all", -1, array(NS_SPECIAL.":DefineWebService", NS_SPECIAL.":UseWebService"));

	$jsm->serializeScripts($out);
	$jsm->serializeCSS($out);
	
	return true;
}

// TermImport scripts callback
// includes necessary css files.
function smwDITIAddHTMLHeader(&$out){
	global $wgTitle;
	if ($wgTitle->getNamespace() != NS_SPECIAL) return true;

	global $smwgDIScriptPath;

	$jsm = SMWResourceManager::SINGLETON();

	$jsm->addScriptIf($smwgDIScriptPath .  '/scripts/TermImport/termImport.js', "all", -1, array(NS_SPECIAL.":TermImport"));

	smwfDIAddJSLanguageScripts($jsm, "all", -1, array(NS_SPECIAL.":TermImport"));
	
	$jsm->addCSSIf($smwgDIScriptPath . '/skins/TermImport/termimport.css', "all", -1, NS_SPECIAL.":TermImport");
	
	$jsm->serializeScripts($out);
	$jsm->serializeCSS($out);

	return true;
}


function smwDITBAddHTMLHeader(&$out){
	global $smwgDIScriptPath, $wgRequest;
	
	$action = $wgRequest->getVal('action');
	if ($action == 'edit') {
		$out->addScript("<script type=\"text/javascript\" src=\"".$smwgDIScriptPath .  "/scripts/WebServices/semantic-toolbar-container.js\"></script>");
	}
	
	return true;
}


function smwfDIGetAjaxMethodPrefix() {
	$func_name = isset( $_POST["rs"] ) ? $_POST["rs"] : (isset( $_GET["rs"] ) ? $_GET["rs"] : NULL);
	if ($func_name == NULL) return NULL;
	return substr($func_name, 4, 4); // return _xx_ of smwf_xx_methodname, may return FALSE
}

?>