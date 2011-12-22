<?php
/*
 * Copyright (C) Vulcan Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program.If not, see <http://www.gnu.org/licenses/>.
 *
 */

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

global $smwgDIStyleVersion;
$smwgDIStyleVersion = preg_replace('/[^\d]/', '', '{{$BUILDNUMBER}}' );
if (strlen($smwgDIStyleVersion) > 0)
    $smwgDIStyleVersion = '?'.$smwgDIStyleVersion;
    
    
/**
 * Configures Data Import Extension for initialization.
 * Must be called *AFTER* SMWHalo is intialized.
 */
function enableDataImportExtension() {
	global $wgExtensionFunctions, $smwgDIIP;

	require_once($smwgDIIP.'/includes/DI_Settings.php');
	
	$wgExtensionFunctions[] = 'smwfDISetupExtension';
	
	// define NS constants
	difInitWWSNamespaces();
	difInitTINamespaces();
	
	global $smwgNamespacesWithSemanticLinks;
	$smwgNamespacesWithSemanticLinks[SMW_NS_TERM_IMPORT] = true;
	$smwgNamespacesWithSemanticLinks[NS_TI_EMAIL] = true;
	
	//register namespaces
	global $wgLanguageCode;
	smwfDIInitContentLanguage($wgLanguageCode);
	difRegisterNamespaces();	
	
	global $wgHooks;
	$wgHooks['LanguageGetMagic'][] = 'difSetupMagic';
}

/**
 * Intializes Data Import Extension.
 * Called from SMW during initialization.
 */
function smwfDISetupExtension() {
	global $wgAutoloadClasses, $smwgDIIP, $smwgDIScriptPath; 

	smwfDIInitMessages();
	
	//solves issue with maintenance mode and not yet initialized Di database tables
	if(defined( 'DO_MAINTENANCE' )){
		if(!difGetWSStore()->isInitialized()){
			return true;
		}
	}
	
	//autoload classes
	//Term IMport Framework	
	$wgAutoloadClasses['DICL']  = 
		$smwgDIIP.'/includes/TermImport/DI_CL.php';
	$wgAutoloadClasses['DITermCollection']  = 
		$smwgDIIP.'/includes/TermImport/DI_TermCollection.php';
	$wgAutoloadClasses['DITerm']  = 
		$smwgDIIP.'/includes/TermImport/DI_Termcollection.php';
	$wgAutoloadClasses['DIDAMRegistry']  = 
		$smwgDIIP.'/includes/TermImport/DI_DAMRegistry.php';
	$wgAutoloadClasses['DIDAMConfiguration']  = 
		$smwgDIIP.'/includes/TermImport/DI_DAMRegistry.php';
	$wgAutoloadClasses['DIDALHelper']  = 
		$smwgDIIP.'/includes/TermImport/DAL/DI_DALHelper.php';
	$wgAutoloadClasses['IDAL']  = 
		$smwgDIIP.'/includes/TermImport/DI_IDAL.php';
	$wgAutoloadClasses['DITermImportDefinitionValidator']  = 
		$smwgDIIP.'/includes/TermImport/DI_TermImportDefinitionValidator.php';
	$wgAutoloadClasses['DITermImportPage']  = 
		$smwgDIIP.'/specials/TermImport/DI_TermImportPage.php';
	$wgAutoloadClasses['DIXMLParser']  = 
		$smwgDIIP.'/includes/TermImport/DI_XMLParser.php';
		
		
	//DAMs
	$wgAutoloadClasses['DALReadCSV']  = 
		$smwgDIIP.'/includes/TermImport/DAL/DI_DALReadCSV.php';
	$wgAutoloadClasses['DALReadFeed']  = 
		$smwgDIIP.'/includes/TermImport/DAL/DI_DALReadFeed.php';
	$wgAutoloadClasses['DALReadPOP3']  = 
		$smwgDIIP.'/includes/TermImport/DAL/DI_DALReadPOP3.php';
	$wgAutoloadClasses['DALReadTIXML']  = 
		$smwgDIIP.'/includes/TermImport/DAL/DI_DALReadTIXML.php';
	$wgAutoloadClasses['DALReadSPARQLXML']  = 
		$smwgDIIP.'/includes/TermImport/DAL/DI_DALReadSPARQLXML.php';
	
	//Mail attachment parsers
	$wgAutoloadClasses['DIVCardForPOP3']  = 
		$smwgDIIP.'/includes/TermImport/DAL/MailAttachmentParsers/DI_VCardParser.php';
	$wgAutoloadClasses['DIICalParserForPOP3']  = 
		$smwgDIIP.'/includes/TermImport/DAL/MailAttachmentParsers/DI_ICalParser.php';
		
		
	//bots
	$wgAutoloadClasses['TermImportBot'] = 
		$smwgDIIP.'/includes/TermImport/Bots/DI_TermImportBot.php';
	$wgAutoloadClasses['TermImportUpdateBot'] = 
		$smwgDIIP.'/includes/TermImport/Bots/DI_TermImportUpdateBot.php';

	//conflict policies and helpers
	$wgAutoloadClasses['DIConflictPolicy'] =
		$smwgDIIP.'/includes/TermImport/ConflictPolicies/DI_ConflictPolicy.php';
	$wgAutoloadClasses['DICPOverwrite'] =
		$smwgDIIP.'/includes/TermImport/ConflictPolicies/DI_CP_Overwrite.php';
	$wgAutoloadClasses['DICPIgnore'] =
		$smwgDIIP.'/includes/TermImport/ConflictPolicies/DI_CP_Ignore.php';
	$wgAutoloadClasses['DICPAppendSome'] =
		$smwgDIIP.'/includes/TermImport/ConflictPolicies/DI_CP_AppendSome.php';	
	$wgAutoloadClasses['DICPHArticleAccess'] =
		$smwgDIIP.'/includes/TermImport/ConflictPolicies/DI_CPH_ArticleAccess.php';
	$wgAutoloadClasses['DICPHIdentityChecker'] =
		$smwgDIIP.'/includes/TermImport/ConflictPolicies/DI_CPH_IdentityChecker.php';
		
	//Web Services
	$wgAutoloadClasses['DIWebService'] =
		$smwgDIIP.'/includes/WebServices/DI_WebService.php';
	$wgAutoloadClasses['DIWebServiceUsage'] =
		$smwgDIIP.'/includes/WebServices/DI_WebServiceUsage.php';
	$wgAutoloadClasses['DIWebServiceCache'] =
		$smwgDIIP.'/includes/WebServices/DI_WebServiceCache.php';
	$wgAutoloadClasses['IDIWebServiceClient'] =
		$smwgDIIP.'/includes/WebServices/DI_IWebServiceClient.php';
	$wgAutoloadClasses['DISubParameterProcessor'] =
		$smwgDIIP.'/includes/WebServices/DI_SubParameterProcessor.php';
	$wgAutoloadClasses['DIWSTriplifier'] =
		$smwgDIIP.'/includes/WebServices/DI_WSTriplifier.php';	
		
	
	//sourceformatprocessors
	$wgAutoloadClasses['DIXPathProcessor'] =
		$smwgDIIP.'/includes/WebServices/sourceformatprocessors/DI_XPathProcessor.php';
	$wgAutoloadClasses['DIJSONProcessor'] =
		$smwgDIIP.'/includes/WebServices/sourceformatprocessors/DI_JSONProcessor.php';
	$wgAutoloadClasses['DIRDFProcessor'] =
		$smwgDIIP.'/includes/WebServices/sourceformatprocessors/DI_RDFProcessor.php';
		
	//clients
	$wgAutoloadClasses['DIRestClient'] =
		$smwgDIIP.'/includes/WebServices/wsclients/DI_RESTClient.php';	
	$wgAutoloadClasses['DILinkeddataClient'] =
		$smwgDIIP.'/includes/WebServices/wsclients/DI_LinkedDataClient.php';
	$wgAutoloadClasses['DISoapClient'] =
		$smwgDIIP.'/includes/WebServices/wsclients/DI_SOAPClient.php';
			
	//smwstorage layer	
	$wgAutoloadClasses['DIWSQueryResult'] =
		$smwgDIIP.'/includes/WebServices/smwstoragelayer/DI_WSQueryResult.php';
	$wgAutoloadClasses['DIWSResultArray'] =
		$smwgDIIP.'/includes/WebServices/smwstoragelayer/DI_WSQueryResult.php';
	$wgAutoloadClasses['DIWSSMWStore'] =
		$smwgDIIP.'/includes/WebServices/smwstoragelayer/DI_WSSMWStore.php';
			
	//Specials
	$wgAutoloadClasses['DIWebServicePage'] =
		$smwgDIIP.'/specials/WebServices/DI_WebServicePage.php';
	$wgAutoloadClasses['DIWebServicePageHooks'] =
		$smwgDIIP.'/specials/WebServices/DI_WebServicePageHooks.php';	
	$wgAutoloadClasses['DISMWAskPageReplacement']  = 
		$smwgDIIP.'/specials/WebServices/AskSpecial/DI_SMWAskPageReplacement.php';

	//WS result printers	
	$wgAutoloadClasses['DIQPWSSimpleTable'] = 
		$smwgDIIP . '/includes/WebServices/resultprinters/DI_QP_WSSimpleTable.php';
	$wgAutoloadClasses['DIQPWSTransposed'] = 
		$smwgDIIP . '/includes/WebServices/resultprinters/DI_QP_WSTransposed.php';
	$wgAutoloadClasses['DIQPWSTIXML'] = 
		$smwgDIIP . '/includes/WebServices/resultprinters/DI_QP_WSTIXML.php';

	//bots
	$wgAutoloadClasses['DIWSUpdateBot'] = 
		$smwgDIIP . '/includes/WebServices/bots/DI_WSUpdateBot.php';
	$wgAutoloadClasses['DIWSCacheBot'] = 
		$smwgDIIP . '/includes/WebServices/bots/DI_WSCacheBot.php';
	
	//register query printers
	global $smwgResultFormats;
	$smwgResultFormats['simpletable'] = 'DIQPWSSimpleTable'; 
	$smwgResultFormats['transposed'] = 'DIQPWSTransposed';
	$smwgResultFormats['tixml'] = 'DIQPWSTIXML';
		
	//register conflict policies
	global $ditigConflictPolicies;
	$ditigConflictPolicies['ignore'] = 'DICPIgnore';
	$ditigConflictPolicies['overwrite'] = 'DICPOverwrite';	
	$ditigConflictPolicies['append some'] = 'DICPAppendSome';
	
	//enable webservice als src in ask queries
	global $smwgQuerySources;
	$smwgQuerySources['webservice'] = 'DIWSSMWStore';
		
	//set all parser hooks
	global $wgParser, $wgHooks;
	//term import
	$wgParser->setHook('ImportSettings', 'DITermImportPage::renderTermImportDefinition');
	
	//ws usage
	$wgParser->setFunctionHook( 'webServiceUsage', 'DIWebServiceUsage::renderWSParserFunction' );
	$wgHooks['ArticleSaveComplete'][] = 'DIWebServiceUsage::detectEditedWSUsages';
	$wgHooks['ArticleDelete'][] = 'DIWebServiceUsage::detectDeletedWSUsages';
	
	//web service page
	$wgHooks['ArticleFromTitle'][] = 'DIWebServicePageHooks::showWebServicePage';
	$wgHooks['ArticleSaveComplete'][] = 'DIWebServicePageHooks::articleSavedHook';
	$wgHooks['ArticleDelete'][] = 'DIWebServicePageHooks::articleDeleteHook';
	$wgParser->setHook('WebService', 'DIWebServicePageHooks::wwsdParserHook');
		
	
	
	//introduce resource modules
	global $wgResourceModules;
	$commonProperties = array(
		'localBasePath' => $smwgDIIP,
		'remoteExtPath' => 'DataImport'
	);
	
	$wgResourceModules['ext.dataimport.ti'] = 
		$commonProperties + 
		array(
			'scripts' => array('scripts/TermImport/termImport.js'),
			'styles' => array('skins/TermImport/termimport.css'),
			'dependencies' => array('ext.dataimport.lang', 'ext.ScriptManager.prototype'),
		);
		
	$wgResourceModules['ext.dataimport.defws'] = 
		$commonProperties + 
		array(
			'scripts' => array('scripts/WebServices/def-webservices.js'),
			'styles' => array('skins/webservices/webservices.css'),
			'dependencies' => array('ext.dataimport.lang', 'ext.ScriptManager.prototype'),
		);
		
	$wgResourceModules['ext.dataimport.usews'] = 
		$commonProperties + 
		array(
			'scripts' => array('scripts/WebServices/use-webservice.js'),
			'styles' => array('skins/webservices/webservices.css'),
			'dependencies' => array('ext.dataimport.lang', 'ext.ScriptManager.prototype'),
		);
		
		
	$wgResourceModules['ext.dataimport.rep'] = 
		$commonProperties + 
		array(
			'scripts' => array('scripts/WebServices/webservices-rep.js'),
			'styles' => array('skins/webservices/webservices.css'),
			'dependencies' => array('ext.dataimport.lang', 'ext.ScriptManager.prototype'),
		);
		

	$langMSGScriptIntro = 'scripts/Language/DI_LanguageUser';
	$langMSGScript = $langMSGScriptIntro.'En.js';
	if (isset($wgUser)) {
		$lng .= $langMSGScriptIntro.ucfirst($wgUser->getOption('language')).'.js';
		if (file_exists($smwgDIIP.'/'.$lng)) {
			$langMSGScript = $lng;
		}
	}
	
	$wgResourceModules['ext.dataimport.lang'] = 
		$commonProperties + 
		array(
			'scripts' => array(
				$langMSGScript,
				'scripts/Language/DI_Language.js')	
		);
	
	// add some AJAX calls
	global $wgRequest, $wgContLang;
	$spns_text = $wgContLang->getNsText(NS_SPECIAL);
	$action = $wgRequest->getVal('action');
	if ($action == 'ajax') {
		$method_prefix = smwfDIGetAjaxMethodPrefix();
		//include appropriate access point
		switch($method_prefix) {
			case '_ti_' : 
				require_once($smwgDIIP . '/includes/TermImport/DI_CL.php');
				break;
			case '_wsu' :
				require_once($smwgDIIP . '/specials/WebServices/DI_UseWebServiceAjaxAccess.php');
				break;
			case '_ws_' :  
				require_once($smwgDIIP . '/specials/WebServices/DI_WebServiceRepositoryAjaxAccess.php');
				require_once($smwgDIIP . '/specials/WebServices/DI_DefineWebServiceAjaxAccess.php');
				break;
		} 
	} else { // otherwise register special pages
		global $wgSpecialPages, $wgSpecialPageGroups;
		$wgAutoloadClasses['DITermImportSpecial'] = 
			$smwgDIIP . '/specials/TermImport/DI_TermImportSpecial.php';
		$wgSpecialPages['TermImport'] = array('DITermImportSpecial');
		$wgSpecialPageGroups['TermImport'] = 'di_group';
		
		$wgAutoloadClasses['DIWebServiceRepositorySpecial'] = $smwgDIIP . '/specials/WebServices/DI_WebServiceRepositorySpecial.php';
		$wgSpecialPages['DataImportRepository'] = array('DIWebServiceRepositorySpecial');
		$wgSpecialPageGroups['DataImportRepository'] = 'di_group';

		$wgAutoloadClasses['DIDefineWebServiceSpecial'] = $smwgDIIP . '/specials/WebServices/DI_DefineWebServiceSpecial.php';
		$wgAutoloadClasses['DIDefineWebServiceSpecialAjaxAccess'] = $smwgDIIP . '/specials/WebServices/DI_DefineWebServiceAjaxAccess.php';
		$wgSpecialPages['DefineWebService'] = array('DIDefineWebServiceSpecial');
		$wgSpecialPageGroups['DefineWebService'] = 'di_group';
		
		$wgAutoloadClasses['DIUseWebServiceSpecial'] = $smwgDIIP . '/specials/WebServices/DI_UseWebServiceSpecial.php';
		$wgSpecialPages['UseWebService'] = array('DIUseWebServiceSpecial');
		$wgSpecialPageGroups['UseWebService'] = 'di_group';
		
		//overwrite Special:Ask (needed for ask queries with source web service)
		$wgSpecialPages['Ask'] = array('DISMWAskPageReplacement' );
	}
	
	// Register Credits
	global $wgExtensionCredits;
	$wgExtensionCredits['parserhook'][]=array('name'=>'Data&nbsp;Import&nbsp;Extension', 'version'=>SMW_DI_VERSION,
		'author'=>"Thomas&nbsp;Schweitzer, Ingo&nbsp;Steinbauer, Sascha&nbsp;Wagner and Daniel&nbsp;Hansch. Owned by [http://www.ontoprise.de ontoprise GmbH].", 
		'url'=>'http://smwforum.ontoprise.com/smwforum/index.php/Help:Data_Import_Extension', 
		'description' => 'Allows to import data from a lot of different sources.');

	//load the Gardening Bots
	//todo. change SGA so that bots can be registered without initializing them
	$bot = new TermImportBot();
	$bot = new TermImportUpdateBot();
	$bot = new DIWSUpdateBot();
	$bot = new DIWSCacheBot();
	
	//register DAMs
	DIDAMRegistry::registerDAM('DALReadCSV', 'CSV file', 
		wfMsg('smw_ti_damdesc_csv'));
	DIDAMRegistry::registerDAM('DALReadFeed', 'RSS feed', 
		wfMsg('smw_ti_damdesc_feed'));
	DIDAMRegistry::registerDAM('DALReadPOP3', 'POP3 server', 
		wfMsg('smw_ti_damdesc_pop3'));
	DIDAMRegistry::registerDAM('DALReadTIXML', 'Web Service result', 
		wfMsg('smw_ti_damdesc_tixml'));
	DIDAMRegistry::registerDAM('DALReadSPARQLXML', 'SPARQL endpoint', 
		wfMsg('smw_ti_damdesc_sparql'));
	
	global $wgHooks;
	$wgHooks['smwhACNamespaceMappings'][] = 'difRegisterAutocompletionIcons';
	
	//add IAI DAM if IAI is enabled
	global $iagEnabled;
	if($iagEnabled){
		$wgAutoloadClasses['DALInterwikiArticleImport']  = 
			$smwgDIIP.'/includes/TermImport/DAL/DI_DALInterwikiArticleImport.php';
		//todo:use language file
		DIDAMRegistry::registerDAM('DALInterwikiArticleImport', 'Other Wiki', 
			'Imports articles from an external Mediawiki installation.');
	}
	
	return true;
}

/*
 * Creates or updates additional tables needed by the Data Import extension.
 * Called from SMW when admin re-initializes tables
 */
function smwfDIInitializeTables() {
	difGetWSStore()->initDatabaseTables();
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


function smwfDIGetAjaxMethodPrefix() {
	$func_name = isset( $_POST["rs"] ) ? $_POST["rs"] : (isset( $_GET["rs"] ) ? $_GET["rs"] : NULL);
	if ($func_name == NULL) return NULL;
	if(substr($func_name,0, 3) == 'dif'){
		return substr($func_name, 3, 4); // return _xx_ of smwf_xx_methodname, may return FALSE
	} else {
		return substr($func_name, 4, 4); // return _xx_ of smwf_xx_methodname, may return FALSE
	}
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
function difRegisterNamespaces() {
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
function difInitTINamespaces($baseIndex = 202) {
	global $smwgTINamespaceIndex;
	if (!isset($smwgTINamespaceIndex)) {
		$smwgTINamespaceIndex = $baseIndex;
	}

	if (!defined('SMW_NS_TERM_IMPORT')) {
		define('SMW_NS_TERM_IMPORT',       $smwgTINamespaceIndex);
		define('SMW_NS_TERM_IMPORT_TALK',  $smwgTINamespaceIndex+1);
	}
	
	//this is not nice, but I cannot change it, since older versions then will not work anymore
	global $smwgWWSNamespaceIndex;
	if (!defined('NS_TI_EMAIL')) define('NS_TI_EMAIL', $smwgWWSNamespaceIndex+20);
	if (!defined('NS_TI_EMAIL_TALK')) define('NS_TI_EMAIL_TALK', $smwgWWSNamespaceIndex+21);
}
	
/*
 * Initialize magic words
 */
function difSetupMagic( &$magicWords, $langCode ) {
	$magicWords['webServiceUsage'] = array( 0, 'ws' );
	return true;
}

/*
 * Get the WS store
 */
function difGetWSStore(){
	global $diwsgStore;
	
	if(is_null($diwsgStore)){
		//autoloading has to be done because of maintenaince mode
		global $wgAutoloadClasses, $smwgDIIP;
		$wgAutoloadClasses['DIWSStorageSQL'] =
			$smwgDIIP.'/includes/WebServices/storage/DI_WSStorageSQL.php';
		$diwsgStore = new DIWSStorageSQL();	
	}
	
	return $diwsgStore;
}
