<?php
/**
 * @file
  * @ingroup DataImport
  * This file contains methods for initializing the Data Import extension.
  * @author Ingo Steinbauer
 */

/**
 * This group contains all parts of the Data Import extension.
 * @defgroup DataImport
 */


//this extension does only work if the Halo extension is enabled
if ( !defined( 'SMW_HALO_VERSION' ) ) die("The Data Import extension requires the Halo extension.");
if ( !defined( 'SGA_GARDENING_EXTENSION_VERSION' ) ) die("The Data Import extension requires the Semantic Gardening extension.");

define('SMW_DI_VERSION', '{{$VERSION}} [B{{$BUILDNUMBER}}]');

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
	global $wgExtensionFunctions, $smwgDIIP;
	
	$wgExtensionFunctions[] = 'smwfDISetupExtension';
	
	global $smgJSLibs; 
	$smgJSLibs[] = 'prototype';
	
	// Register additional namespaces
	global $smwgWWSNamespaceIndex;
	if (!isset($smwgWWSNamespaceIndex)) {
		difInitWWSNamespaces(200);
	} else {
		difInitWWSNamespaces();
	}
	
	// Register additional namespaces
	if (!isset($smwgTINamespaceIndex)) {
		difInitTINamespaces(202);
	} else {
		difInitTINamespaces();
	}
	
	require_once($smwgDIIP. '/specials/TermImport/SMW_ImportedTermsNamespaces.php');
	
	global $smwgNamespacesWithSemanticLinks;
	$smwgNamespacesWithSemanticLinks[SMW_NS_TERM_IMPORT] = true;
	
	//register namespaces
	global $wgLanguageCode;
	smwfDIInitContentLanguage($wgLanguageCode);
	//WebServiceManager::registerWWSNamespaces();
	difRegisterWWSNamespaces();	
	diRegisterTermImportNamespaces();
	
	global $wgHooks;
	$wgHooks['LanguageGetMagic'][] = 'difSetupMagic';
	
	global $wgAutoloadClasses;
	$wgAutoloadClasses['SMWWSSMWAskPage']  = 
		$smwgDIIP.'/specials/WebServices/smwstoragelayer/SMW_WSSMWAskPage.php';

	$wgAutoloadClasses['SMWQPWSSimpleTable'] = $smwgDIIP . '/specials/WebServices/resultprinters/SMW_QP_WSSimpleTable.php';
	$wgAutoloadClasses['SMWQPWSTransposed'] = $smwgDIIP . '/specials/WebServices/resultprinters/SMW_QP_WSTransposed.php';
	$wgAutoloadClasses['SMWQPWSTIXML'] = $smwgDIIP . '/specials/WebServices/resultprinters/SMW_QP_WSTIXML.php';

	global $smwgResultFormats;
	$smwgResultFormats['simpletable'] = 'SMWQPWSSimpleTable'; 
	$smwgResultFormats['transposed'] = 'SMWQPWSTransposed';
	$smwgResultFormats['tixml'] = 'SMWQPWSTIXML';
}

function enableMaterializationFeature(){
	global $enableMaterialization, $smwgDIIP;;
	$enableMaterialization = true;
	
	//require the materialize parser function
	require_once("$smwgDIIP/specials/Materialization/SMW_MaterializeParserFunction.php");
}

/**
 * Intializes Data Import Extension.
 * Called from SMW during initialization.
 */
function smwfDISetupExtension() {
	global $wgHooks, $wgExtensionCredits, $wgAutoloadClasses, $wgSpecialPages; 
	global $smwgDIIP, $wgSpecialPageGroups, $wgRequest, $smwgEnableDataImportExtension, $wgContLang;

	if(defined( 'DO_MAINTENANCE' )){
		require_once($smwgDIIP . '/specials/WebServices/SMW_WSStorage.php');
		if(!WSStorage::getDatabase()->isInitialized()){
			return true;
		}
	}
	
	global $smwgQuerySources;
	$smwgQuerySources['webservice'] = 'SMWWSSMWStore';
	require_once($smwgDIIP. '/specials/WebServices/smwstoragelayer/SMW_WSSMWStore.php');
	
	//so that other extensions like the gardening framework know about
	//the Data Import Extension
	$smwgEnableDataImportExtension = true;
	
	require_once($smwgDIIP. '/specials/WebServices/SMW_WebServiceManager.php');
	require_once($smwgDIIP. '/specials/TermImport/SMW_TermImportManager.php');
	
	global $wgParser;
	$wgParser->setFunctionHook( 'webServiceUsage', 'wsuf_Render' );
	
	$spns_text = $wgContLang->getNsText(NS_SPECIAL);

	// register AddHTMLHeader functions for special pages
	// to include javascript and css files (only on special page requests).
	if (stripos($wgRequest->getRequestURL(), $spns_text.":") !== false
			|| stripos($wgRequest->getRequestURL(), $spns_text."%3A") !== false) {
		$wgHooks['BeforePageDisplay'][]='smwDITIAddHTMLHeader';
		$wgHooks['BeforePageDisplay'][]='smwDIWSAddHTMLHeader';
	}
	
	$wgHooks['BeforePageDisplay'][]='smwDITBAddHTMLHeader';
	
	global $enableMaterialization;
	if(isset($enableMaterialization) && $enableMaterialization){ 
		$wgHooks['BeforePageDisplay'][]='smwDIMAAddHTMLHeader';
	}
	
	$wgHooks['smwhACNamespaceMappings'][] = 'difRegisterAutocompletionIcons';
	
	smwfDIInitMessages();
	
	WebServiceManager::initWikiWebServiceExtension();
	TermImportManager::initTermImportFramework();
	
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
		$wgAutoloadClasses['SMWTermImportSpecial'] = $smwgDIIP . '/specials/TermImport/SMW_TermImportSpecial.php';
		$wgSpecialPages['TermImport'] = array('SMWTermImportSpecial');
		$wgSpecialPageGroups['TermImport'] = 'di_group';
		
		$wgAutoloadClasses['SMWWebServiceRepositorySpecial'] = $smwgDIIP . '/specials/WebServices/SMW_WebServiceRepositorySpecial.php';
		$wgSpecialPages['DataImportRepository'] = array('SMWWebServiceRepositorySpecial');
		$wgSpecialPageGroups['DataImportRepository'] = 'di_group';

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

	//load the Gardening Bots
	
	require_once("$smwgDIIP/specials/TermImport/SMW_TermImportBot.php");
	require_once("$smwgDIIP/specials/TermImport/SMW_TermImportUpdateBot.php");
	require_once("$smwgDIIP/specials/WebServices/SMW_WSCacheBot.php");
	require_once("$smwgDIIP/specials/WebServices/SMW_WSUpdateBot.php");
	
	$wgSpecialPages['Ask'] = array('SMWWSSMWAskPage' );
	
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
	
	require_once($smwgDIIP . '/specials/Materialization/SMW_MaterializationStorageAccess.php');
	$dbAccess = SMWMaterializationStorageAccess::getInstance();
	$db = $dbAccess->getDatabase();
	$db->setup(true);
	
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
//function smwfDIAddJSLanguageScripts(& $jsm, $mode = "all", $namespace = -1, $pages = array()) {
function smwfDIAddJSLanguageScripts(&$out, $mode = "all", $namespace = -1, $pages = array()) {
	global $wgLanguageCode, $smwgDIScriptPath, $wgUser, $smwgDIIP;
	
	// content language file
	$lng = '/scripts/Language/SMWDI_Language';
	
	$out->addScript("<script type=\"text/javascript\" src=\"".$smwgDIScriptPath.$lng.".js\"></script>");
	
	if (!empty($wgLanguageCode)) {
		$lng .= ucfirst($wgLanguageCode).'.js';
		if (file_exists($smwgDIIP . $lng)) {
			$out->addScript("<script type=\"text/javascript\" src=\"".$smwgDIScriptPath.$lng."\"></script>");
		} else {
			$out->addScript("<script type=\"text/javascript\" src=\"".$smwgDIScriptPath."/scripts/Language/SMWDI_LanguageEn.js\"></script>");
		}
	} else {
		$out->addScript("<script type=\"text/javascript\" src=\"".$smwgDIScriptPath."/scripts/Language/SMWDI_LanguageEn.js\"></script>");
	}

	// user language file
	$lng = '/scripts/Language/SMWDI_Language';
	if (isset($wgUser)) {
		$lng .= "User".ucfirst($wgUser->getOption('language')).'.js';
		//$temp = $smwgDIScriptPath . $lng;
		if (file_exists($smwgDIIP . $lng)) {
			$out->addScript("<script type=\"text/javascript\" src=\"".$smwgDIScriptPath.$lng."\"></script>");
		} else {
			$out->addScript("<script type=\"text/javascript\" src=\"".$smwgDIScriptPath."/scripts/Language/SMWDI_LanguageUserEn.js\"></script>");
		}
	} else {
		$out->addScript("<script type=\"text/javascript\" src=\"".$smwgDIScriptPath."/scripts/Language/SMWDI_LanguageUserEn.js\"></script>");
	}
}

function smwDIWSAddHTMLHeader(&$out) {
	global $wgTitle;
	if ($wgTitle->getNamespace() != NS_SPECIAL) return true;
	
	global $smwgDIScriptPath;
	$includeCSS = false;
	if($wgTitle->getText() == "DefineWebService"){
		$out->addScript("<script type=\"text/javascript\" src=\"".$smwgDIScriptPath."/scripts/WebServices/def-webservices.js\"></script>");	
		$includeCSS = true;
	}
	
	if($wgTitle->getText() == "DataImportRepository"){
		$out->addScript("<script type=\"text/javascript\" src=\"".$smwgDIScriptPath."/scripts/WebServices/webservices-rep.js\"></script>");
		$includeCSS = true;
	}
	
	if($wgTitle->getText() == "UseWebService"){
		$out->addScript("<script type=\"text/javascript\" src=\"".$smwgDIScriptPath."/scripts/WebServices/use-webservice.js\"></script>");
		$includeCSS = true;
	}
	
	if($includeCSS){
		//smwfDIAddJSLanguageScripts($jsm, "all", -1, array(NS_SPECIAL.":DefineWebService", NS_SPECIAL.":DefineWebService", NS_SPECIAL.":WebServiceRepository", NS_SPECIAL.":UseWebService"));
		smwfDIAddJSLanguageScripts($out, "all", -1, array(NS_SPECIAL.":DefineWebService", NS_SPECIAL.":DefineWebService", NS_SPECIAL.":WebServiceRepository", NS_SPECIAL.":UseWebService"));
	
		//$jsm->addCSSIf($smwgDIScriptPath . '/skins/webservices/webservices.css', "all", -1, array(NS_SPECIAL.":DefineWebService", NS_SPECIAL.":UseWebService"));

		$out->addLink(array(
                    'rel'   => 'stylesheet',
                    'type'  => 'text/css',
                    'media' => 'screen, projection',
                    'href'  => $smwgDIScriptPath . '/skins/webservices/webservices.css'
                    ));
	
		//$jsm->serializeScripts($out);
		//$jsm->serializeCSS($out);
	}
	
	return true;
}

// TermImport scripts callback
// includes necessary css files.
function smwDITIAddHTMLHeader(&$out){
	global $wgTitle;
	if ($wgTitle->getNamespace() != NS_SPECIAL || $wgTitle->getText() != "TermImport") return true;

	global $smwgDIScriptPath;

	//$jsm = SMWResourceManager::SINGLETON();
	//$jsm->addScriptIf($smwgDIScriptPath .  '/scripts/TermImport/termImport.js', "all", -1, array(NS_SPECIAL.":TermImport"));
	
	$out->addScript("<script type=\"text/javascript\" src=\"".$smwgDIScriptPath .  "/scripts/TermImport/termImport.js\"></script>");
	
	//smwfDIAddJSLanguageScripts($jsm, "all", -1, array(NS_SPECIAL.":TermImport"));
	smwfDIAddJSLanguageScripts($out, "all", -1, array(NS_SPECIAL.":TermImport"));
	
	//$jsm->addCSSIf($smwgDIScriptPath . '/skins/TermImport/termimport.css', "all", -1, NS_SPECIAL.":TermImport");
	
	$out->addLink(array(
                    'rel'   => 'stylesheet',
                    'type'  => 'text/css',
                    'media' => 'screen, projection',
                    'href'  => $smwgDIScriptPath . '/skins/TermImport/termimport.css'
                    ));
	
	//$jsm->serializeScripts($out);
	//$jsm->serializeCSS($out);

	return true;
}


function smwDITBAddHTMLHeader(&$out){
	global $smwgDIScriptPath, $wgRequest, $wgTitle;
	
	$ns = "";
	$text = "";
	if($wgTitle != null){
		$ns = $wgTitle->getNamespace();
		$text = $wgTitle->getText();
	}
	
	$action = $wgRequest->getVal('action');
	if ($action == 'edit' || $action == 'formedit' || $ns == NS_SPECIAL && $text == "FormEdit") {
		//$out->addScript("<script type=\"text/javascript\" src=\"".$smwgDIScriptPath .  "/scripts/WebServices/semantic-toolbar-container.js\"></script>");
		//$out->addScript("<script type=\"text/javascript\" src=\"".$smwgDIScriptPath."/scripts/WebServices/use-webservice.js\"></script>");
	}
	
	return true;
}

function smwDIMAAddHTMLHeader(&$out){
	global $smwgDIScriptPath, $wgRequest;
	
	$action = $wgRequest->getVal('action');
	if ($action == 'edit') {
		$out->addScript("<script type=\"text/javascript\" src=\"".$smwgDIScriptPath .  "/scripts/Materialization/materialize.js\"></script>");
	}
	
	return true;
}

function smwfDIGetAjaxMethodPrefix() {
	$func_name = isset( $_POST["rs"] ) ? $_POST["rs"] : (isset( $_GET["rs"] ) ? $_GET["rs"] : NULL);
	if ($func_name == NULL) return NULL;
	return substr($func_name, 4, 4); // return _xx_ of smwf_xx_methodname, may return FALSE
}

function difRegisterAutocompletionIcons(& $namespaceMappings) { 
	$namespaceMappings[NS_TI_EMAIL]="/extensions/DataImport/skins/TermImport/images/Image_Email.gif";
	$namespaceMappings[SMW_NS_WEB_SERVICE]="/extensions/DataImport/skins/webservices/Image Webservice.gif";
	$namespaceMappings[SMW_NS_TERM_IMPORT]="/extensions/DataImport/skins/TermImport/images/Image Termimport.gif";
	
	return true;
}

/**
	 * Initializes the namespaces that are used by the Wiki Web Service extension.
	 * Normally the base index starts at 200. It must be an even number greater than
	 * than 100. However, by default Semantic MediaWiki uses the namespace indexes
	 * from 100 upwards.
	 *
	 * @param int $baseIndex
	 * 		Optional base index for all Wiki Web Service namespaces. The default is 200.
	 */
	function difInitWWSNamespaces($baseIndex = 200) {
		global $smwgWWSNamespaceIndex;
		if (!isset($smwgWWSNamespaceIndex)) {
			$smwgWWSNamespaceIndex = $baseIndex;
		}

		if (!defined('SMW_NS_WEB_SERVICE')) {
			define('SMW_NS_WEB_SERVICE',       $smwgWWSNamespaceIndex);
			define('SMW_NS_WEB_SERVICE_TALK',  $smwgWWSNamespaceIndex+1);
		}
	}
	
	/**
	 * Registers the new namespaces. Must be called after the language dependent
	 * messages have been installed.
	 *
	 */
	function difRegisterWWSNamespaces() {
		//also registers TermImport namespace
		global $wgExtraNamespaces, $wgNamespaceAliases, $smwgDIContLang, $wgContLang;

		// Register namespace identifiers
		if (!is_array($wgExtraNamespaces)) {
			$wgExtraNamespaces = array();
		}
		$wgExtraNamespaces = $wgExtraNamespaces + $smwgDIContLang->getNamespaces();
		$wgNamespaceAliases = $wgNamespaceAliases + $smwgDIContLang->getNamespaceAliases();
	}
	
	/**
	 * Initializes the namespaces that are used by the Term Import framework
	 * Normally the base index starts at 202. It must be an even number greater than
	 * than 100. However, by default Semantic MediaWiki uses the namespace indexes
	 * from 100 upwards.
	 *
	 * @param int $baseIndex
	 * 		Optional base index for all Term Import namespaces. The default is 202.
	 */
	function difInitTINamespaces($baseIndex = 200) {
		global $smwgTINamespaceIndex;
		if (!isset($smwgTINamespaceIndex)) {
			$smwgTINamespaceIndex = $baseIndex;
		}

		if (!defined('SMW_NS_TERM_IMPORT')) {
			define('SMW_NS_TERM_IMPORT',       $smwgTINamespaceIndex);
			define('SMW_NS_TERM_IMPORT_TALK',  $smwgTINamespaceIndex+1);
		}
	}
	
/*
 * Initialize magic words
 */
	function difSetupMagic( &$magicWords, $langCode ) {
	$magicWords['webServiceUsage'] = array( 0, 'ws' );
	return true;
}