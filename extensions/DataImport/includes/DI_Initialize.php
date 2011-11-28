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
	
	global $wgAutoloadClasses;
	$wgAutoloadClasses['SMWWSSMWAskPage']  = 
		$smwgDIIP.'/specials/WebServices/smwstoragelayer/SMW_WSSMWAskPage.php';
	
	//WS result printers	
	$wgAutoloadClasses['SMWQPWSSimpleTable'] = $smwgDIIP . '/specials/WebServices/resultprinters/SMW_QP_WSSimpleTable.php';
	$wgAutoloadClasses['SMWQPWSTransposed'] = $smwgDIIP . '/specials/WebServices/resultprinters/SMW_QP_WSTransposed.php';
	$wgAutoloadClasses['SMWQPWSTIXML'] = $smwgDIIP . '/specials/WebServices/resultprinters/SMW_QP_WSTIXML.php';

	global $smwgResultFormats;
	$smwgResultFormats['simpletable'] = 'SMWQPWSSimpleTable'; 
	$smwgResultFormats['transposed'] = 'SMWQPWSTransposed';
	$smwgResultFormats['tixml'] = 'SMWQPWSTIXML';
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
		require_once($smwgDIIP . '/specials/WebServices/SMW_WSStorage.php');
		if(!WSStorage::getDatabase()->isInitialized()){
			return true;
		}
	}
	
	//enable webservice als src in ask queries
	global $smwgQuerySources;
	$smwgQuerySources['webservice'] = 'SMWWSSMWStore';
	require_once($smwgDIIP. '/specials/WebServices/smwstoragelayer/SMW_WSSMWStore.php');
	
	//autoload classes
	//Term IMport Framework	
	$wgAutoloadClasses['DICL']  = 
		$smwgDIIP.'/includes/TermImport/DI_CL.php';
	$wgAutoloadClasses['DITermCollection']  = 
		$smwgDIIP.'/includes/TermImport/DI_Termcollection.php';
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
	
	//todo:remove this
	require_once($smwgDIIP. '/specials/WebServices/SMW_WebServiceManager.php');
	WebServiceManager::initWikiWebServiceExtension();
	
	global $wgParser;
	$wgParser->setFunctionHook( 'webServiceUsage', 'wsuf_Render' );
	$wgParser->setHook('ImportSettings', 'DITermImportPage::renderTermImportDefinition');
	
	//introduce resource modules
	global $wgResourceModules;
	$commonProperties = array(
		'localBasePath' => $smwgDIIP,
		'remoteExtPath' => 'DataImport'
	);
	
	$wgResourceModules['ext.dataimport.ti'] = 
		$commonProperties + 
		array(
			'scripts' => array('scripts/TermImport/termimport.js'),
			'styles' => array('skins/TermImport/termimport.css'),
			'dependencies' => array('ext.ScriptManager.prototype'),
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
			case '_wsu_' : 
				require_once($smwgDIIP . '/specials/WebServices/SMW_UseWebServiceAjaxAccess.php');
				break;
			case '_ws_' :  require_once($smwgDIIP . '/specials/WebServices/SMW_WebServiceRepositoryAjaxAccess.php');
				require_once($smwgDIIP . '/specials/WebServices/SMW_DefineWebServiceAjaxAccess.php');
				break;
		} 
	} else { // otherwise register special pages
		global $wgSpecialPages, $wgSpecialPageGroups;
		$wgAutoloadClasses['DITermImportSpecial'] = 
			$smwgDIIP . '/specials/TermImport/DI_TermImportSpecial.php';
		$wgSpecialPages['TermImport'] = array('DITermImportSpecial');
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
		
		//overwrite Special:Ask (needed for ask queries with source web service)
		$wgSpecialPages['Ask'] = array('SMWWSSMWAskPage' );
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
	require_once("$smwgDIIP/specials/WebServices/SMW_WSCacheBot.php");
	require_once("$smwgDIIP/specials/WebServices/SMW_WSUpdateBot.php");
	
	
	
	//register DAMs
	//todo: use language files
	DIDAMRegistry::registerDAM('DALReadCSV', 'CSV file', 
		'Imports articles from a CSV file. You either have to pass the path to a file which is located on the server or a valid URL.');
	//todo: add description
	DIDAMRegistry::registerDAM('DALReadFeed', 'RSS feed', 
		'Imports articles from feeds in the RSS or Atom format.');
	DIDAMRegistry::registerDAM('DALReadPOP3', 'POP3 server', 
		'Imports mails from a POP3 server.');
	DIDAMRegistry::registerDAM('DALReadTIXML', 'Web Service result', 
		'Imports the results of a web service call in the TIXML result format. You have to enter the name of the article, that contains the web service result in the TIXML format.');
	DIDAMRegistry::registerDAM('DALReadSPARQLXML', 'SPARQL endpoint', 
		'Imports results of a SELECT query to a SPARQL endpoint.');
	
	global $wgHooks;
	$wgHooks['smwhACNamespaceMappings'][] = 'difRegisterAutocompletionIcons';
	
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
