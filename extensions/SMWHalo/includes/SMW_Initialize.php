<?php
/*
 * Created on 13.09.2007
 *
 * Author: kai
 */
if ( !defined( 'MEDIAWIKI' ) ) die;

define('SMW_HALO_VERSION', '1.4.3-for-SMW-1.4.x');

// constant for special schema properties
define('SMW_SSP_HAS_DOMAIN_AND_RANGE_HINT', 1);
define('SMW_SSP_HAS_MAX_CARD', 2);
define('SMW_SSP_HAS_MIN_CARD', 3);
define('SMW_SSP_IS_INVERSE_OF', 4);
define('SMW_SSP_IS_EQUAL_TO', 5);

// constants for special categories
define('SMW_SC_TRANSITIVE_RELATIONS', 0);
define('SMW_SC_SYMMETRICAL_RELATIONS', 1);

// default cardinalities
define('CARDINALITY_MIN',0);
define('CARDINALITY_UNLIMITED', 2147483647); // MAXINT


$smwgHaloIP = $IP . '/extensions/SMWHalo';
$smwgHaloScriptPath = $wgScriptPath . '/extensions/SMWHalo';
$smwgHaloAAMParser = null;
$smwgDisableAAMParser = false;
$smwgProcessedAnnotations = null;
$wgCustomVariables = array('CURRENTUSER');
//global $smwgEnableWikiWebServices;

//if ($smwgEnableWikiWebServices) {
//	require_once($smwgHaloIP. '/specials/SMWWebService/SMW_WebServiceManager.php');
//}
//if (!defined('SMW_NS_WEB_SERVICE')
//    && (!isset($smwgEnableWikiWebServices) || $smwgEnableWikiWebServices === false)) {
//	// Suppress warnings if web services are not enabled.
//	define('SMW_NS_WEB_SERVICE',      200);
//	define('SMW_NS_WEB_SERVICE_TALK', 201);
//};


require_once($smwgHaloIP."/includes/SMW_ResourceManager.php");
/**
 * Configures SMW Halo Extension for initialization.
 * Must be called *AFTER* SMW is intialized.
 *
 * @param String $store SMWHaloStore (old) or SMWHaloStore2 (new). Uses old by default.
 */
function enableSMWHalo($store = 'SMWHaloStore2', $tripleStore = NULL, $tripleStoreGraph = NULL) {
	global $wgExtensionFunctions, $smwgOWLFullExport, $smwgDefaultStore, $smwgBaseStore,
	$smwgSemanticDataClass, $wgHooks, $smwgTripleStoreGraph, $smwgIgnoreSchema;
	if ($store == 'SMWHaloStore') {
		trigger_error("Old 'SMWHaloStore' is not supported anymore. Please upgrade to 'SMWHaloStore2'");
		die();
	}


	$smwgIgnoreSchema = !isset($smwgIgnoreSchema) ? true : $smwgIgnoreSchema;
	$smwgTripleStoreGraph = $tripleStoreGraph !== NULL ? $tripleStoreGraph : 'http://mywiki';
	$wgExtensionFunctions[] = 'smwgHaloSetupExtension';
	$smwgOWLFullExport = true;
	$smwgDefaultStore = $tripleStore !== NULL ? $tripleStore : $store;
	$smwgBaseStore = $store;
	$smwgSemanticDataClass = $tripleStore !== NULL ? 'SMWFullSemanticData' : 'SMWSemanticData';
	$wgHooks['MagicWordMagicWords'][]          = 'wfAddCustomVariable';
	$wgHooks['MagicWordwgVariableIDs'][]       = 'wfAddCustomVariableID';
	$wgHooks['LanguageGetMagic'][]             = 'wfAddCustomVariableLang';
	$wgHooks['LanguageGetMagic'][]             = 'smwfAddHaloMagicWords';
	$wgHooks['ParserGetVariableValueSwitch'][] = 'wfGetCustomVariable';
}

/**
 * Intializes SMW Halo Extension.
 * Called from SMW during initialization.
 */
function smwgHaloSetupExtension() {
	global $smwgIP, $smwgHaloIP, $wgHooks, $smwgMasterGeneralStore, $wgFileExtensions, $wgJobClasses, $wgExtensionCredits;
	global $smwgHaloContLang, $wgAutoloadClasses, $wgSpecialPages, $wgAjaxExportList, $wgGroupPermissions;
	global $mediaWiki, $smwgRuleRewriter, $smwgEnableFlogicRules, $wgSpecialPageGroups;
	global $smwgWebserviceEndpoint, $smwgMessageBroker;
	if (is_array($smwgWebserviceEndpoint) && count($smwgWebserviceEndpoint) > 1 && !isset($smwgMessageBroker)) {
		trigger_error("Multiple webservice endpoints require a messagebroker to handle triplestore updates.");
		die();
	}
	$smwgMasterGeneralStore = NULL;

	// Autoloading. Use it for everything! No include_once or require_once please!

	$wgAutoloadClasses['SMWHaloStore2'] = $smwgHaloIP . '/includes/storage/SMW_HaloStore2.php';

	$wgAutoloadClasses['SMWTripleStore']            = $smwgHaloIP . '/includes/storage/SMW_TripleStore.php';
	$wgAutoloadClasses['SMWSPARQLQueryProcessor']            = $smwgHaloIP . '/includes/SMW_SPARQLQueryProcessor.php';
	$wgAutoloadClasses['SMWSPARQLQueryParser']            = $smwgHaloIP . '/includes/SMW_SPARQLQueryParser.php';
	$wgAutoloadClasses['SMWFullSemanticData']            = $smwgHaloIP . '/includes/SMW_FullSemanticData.php';
	$wgAutoloadClasses['SMWAggregationResultPrinter'] = $smwgHaloIP . '/includes/SMW_QP_Aggregation.php';
	$wgAutoloadClasses['SMWExcelResultPrinter'] = $smwgHaloIP . '/includes/SMW_QP_Excel.php';
	$wgAutoloadClasses['SMWSPARQLQuery'] = $smwgHaloIP . '/includes/SMW_SPARQLQueryParser.php';

	if (property_exists('SMWQueryProcessor','formats')) { // registration up to SMW 1.2.*

		SMWQueryProcessor::$formats['exceltable'] = 'SMWExcelResultPrinter';
		SMWQueryProcessor::$formats['aggregation'] = 'SMWAggregationResultPrinter';
	} else { // registration since SMW 1.3.*
		global $smwgResultFormats;

		$smwgResultFormats['exceltable'] = 'SMWExcelResultPrinter';
		$smwgResultFormats['aggregation'] = 'SMWAggregationResultPrinter';
	}

	#
	# Handle webservice calls.
	#   wsmethod URL parameter indicates a SOAP webservice call. All such calls are handeled by
	#   /webservices/SMW_Webservices.php
	#
	if (array_key_exists('action', $_REQUEST) && $_REQUEST['action'] == 'wsmethod' ) {
		global $IP;
		require_once( $IP . '/extensions/SMWHalo/includes/webservices/SMW_Webservices.php' );
		exit; // stop immediately
	}



	// register SMW hooks
	
	$wgHooks['smwNewSpecialValue'][] = 'smwfHaloSpecialValues';
	$wgHooks['smwInitDatatypes'][] = 'smwfHaloInitDatatypes';
	$wgHooks['smwInitProperties'][] = 'smwfInitSpecialPropertyOfSMWHalo';

	global $smwgWebserviceEndpoint, $smwgShowDerivedFacts, $wgRequest;
	if (isset($smwgWebserviceEndpoint)) {
		if ($smwgShowDerivedFacts === true
		|| strtolower($wgRequest->getVal("showDerived", "false")) === 'true') {
			$wgHooks['smwShowFactbox'][] = 'smwfAddDerivedFacts';
		}
	}

	// Remove the existing smwfSaveHook and replace it with the
	// new and functionally enhanced smwfHaloSaveHook
	$wgHooks['ParserBeforeStrip'][] = 'smwfRegisterSPARQLInlineQueries';



	$wgHooks['OntoSkinTemplateToolboxEnd'][] = 'smwfOntoSkinTemplateToolboxEnd';
	$wgHooks['OntoSkinTemplateNavigationEnd'][] = 'smwfOntoSkinTemplateNavigationEnd';
	$wgHooks['OntoSkinInsertTreeNavigation'][] = 'smwfNavTree';
	

	global $wgRequest, $wgContLang, $wgCommandLineMode;

	$spns_text = $wgContLang->getNsText(NS_SPECIAL);
	$tyns_text = $wgContLang->getNsText(SMW_NS_TYPE);
	$sp_aliases = $wgContLang->getSpecialPageAliases();

	// register AddHTMLHeader functions for special pages
	// to include javascript and css files (only on special page requests).
	if (stripos($wgRequest->getRequestURL(), $spns_text.":") !== false
	|| stripos($wgRequest->getRequestURL(), $spns_text."%3A") !== false) {

		$wgHooks['BeforePageDisplay'][]='smwOBAddHTMLHeader';
		$wgHooks['BeforePageDisplay'][]='smwfQIAddHTMLHeader';
		$wgHooks['BeforePageDisplay'][]='smwPRAddHTMLHeader';

	}
	// Register parser hooks for advanced annotation mode

	$action = $wgRequest->getVal('action');
	if ($action == 'annotate') {
		$wgHooks['ParserBeforeStrip'][] = 'smwfAAMBeforeStrip';
		$wgHooks['ParserAfterStrip'][] = 'smwfAAMAfterStrip';
		$wgHooks['InternalParseBeforeLinks'][] = 'smwfAAMBeforeLinks';
		$wgHooks['ParserBeforeTidy'][] = 'smwfAAMBeforeTidy';
		$wgHooks['ParserAfterTidy'][] = 'smwfAAMAfterTidy';
		$wgHooks['OutputPageBeforeHTML'][] = 'smwfAAMBeforeHTML';
	}
	$wgHooks['UnknownAction'][] = 'smwfAnnotateAction';

	// autocompletion option registration
	$wgHooks['UserToggles'][] = 'smwfAutoCompletionToggles';
	$wgHooks['UserSaveSettings'][] = 'smwfSetUserDefinedCookies';

	//parser function for multiple template annotations
	$wgHooks['ParserBeforeStrip'][] = 'smwfRegisterCommaAnnotation';

	// add triple store hooks if necessary
	global $smwgWebserviceEndpoint,$smwgIgnoreSchema;
	if (isset($smwgWebserviceEndpoint)) {
		if (!isset($smwgIgnoreSchema) || $smwgIgnoreSchema === false) {
			require_once('storage/SMW_TS_SchemaContributor.php');
			$wgHooks['TripleStorePropertyUpdate'][] = 'smwfTripleStorePropertyUpdate';
		} else {
			require_once('storage/SMW_TS_SimpleContributor.php');
			$wgHooks['TripleStorePropertyUpdate'][] = 'smwfTripleStorePropertyUpdate';
		}
		$wgHooks['TripleStoreCategoryUpdate'][] = 'smwfTripleStoreCategoryUpdate';
			
	}

	// register flogic rule rewriter if flogic rules are enabled
	if (isset($smwgEnableFlogicRules) && $smwgEnableFlogicRules === true) {
		require_once('rules/SMW_FlogicRuleRewriter.php');
		$smwgRuleRewriter = new FlogicRuleRewriter();
	}

	// register file extensions for upload
	$wgFileExtensions[] = 'owl'; // for ontology import

	$wgJobClasses['SMW_UpdateLinksAfterMoveJob'] = 'SMW_UpdateLinksAfterMoveJob';
	$wgJobClasses['SMW_UpdateCategoriesAfterMoveJob'] = 'SMW_UpdateCategoriesAfterMoveJob';
	$wgJobClasses['SMW_UpdatePropertiesAfterMoveJob'] = 'SMW_UpdatePropertiesAfterMoveJob';



	// register message system (not for ajax, only by demand)
	if ($action != 'ajax') {
		smwfHaloInitMessages();
	}

	//require_once($smwgHaloIP . '/includes/SMW_WYSIWYGTab.php');


	// add some AJAX calls
	if ($action == 'ajax') {
		$method_prefix = smwfGetAjaxMethodPrefix();

		// decide according to ajax method prefix which script(s) to import
		switch($method_prefix) {
			case '_ac_' : smwfHaloInitMessages();
			require_once($smwgHaloIP . '/includes/SMW_Autocomplete.php');
			break;

			case '_ob_' : smwfHaloInitMessages();
			require_once($smwgHaloIP . '/specials/SMWOntologyBrowser/SMW_OntologyBrowserAjaxAccess.php');
			break;


			case '_qi_' : smwfHaloInitMessages();
			require_once($smwgHaloIP . '/specials/SMWQueryInterface/SMW_QIAjaxAccess.php' );
			break;
			case '_tb_' : smwfHaloInitMessages();
			require_once($smwgHaloIP . '/includes/SemanticToolbar/SMW_ToolbarFunctions.php');
			break;
			case '_om_' : smwfHaloInitMessages();
			require_once($smwgHaloIP . '/includes/SMW_OntologyManipulator.php');
			break;
			//case '_ti_' : smwfHaloInitMessages();
			//require_once($smwgHaloIP . '/specials/SMWTermImport/SMW_CL.php');
			//break;
			case '_sr_' : smwfHaloInitMessages();
			require_once($smwgHaloIP . '/includes/rules/SMW_RulesAjax.php');
			break;
			case '_ws_' :  smwfHaloInitMessages();
			require_once($smwgHaloIP . '/includes/SMW_WebInterfaces.php');
			break;

			default: // default case just imports everything (should be avoided)
				smwfHaloInitMessages();
				require_once($smwgHaloIP . '/includes/SMW_Autocomplete.php');


				require_once($smwgHaloIP . '/specials/SMWQueryInterface/SMW_QIAjaxAccess.php' );


				require_once($smwgHaloIP . '/specials/SMWOntologyBrowser/SMW_OntologyBrowserAjaxAccess.php');
				require_once($smwgHaloIP . '/includes/SemanticToolbar/SMW_ToolbarFunctions.php');
				require_once($smwgHaloIP . '/includes/SMW_OntologyManipulator.php');
				//require_once($smwgHaloIP . '/specials/SMWTermImport/SMW_TermImportSpecial.php');
		}
	} else { // otherwise register special pages

		// Register new or overwrite existing special pages
		$wgAutoloadClasses['SMW_OntologyBrowser'] = $smwgHaloIP . '/specials/SMWOntologyBrowser/SMW_OntologyBrowser.php';
		$wgSpecialPages['OntologyBrowser'] = array('SMW_OntologyBrowser');
		$wgSpecialPageGroups['OntologyBrowser'] = 'smwplus_group';




		$wgAutoloadClasses['SMWHelpSpecial'] = $smwgHaloIP . '/specials/SMWHelpSpecial/SMWHelpSpecial.php';
		$wgSpecialPages['ContextSensitiveHelp'] = array('SMWHelpSpecial');
		$wgSpecialPageGroups['ContextSensitiveHelp'] = 'smwplus_group';

		$wgAutoloadClasses['SMWQueryInterface'] = $smwgHaloIP . '/specials/SMWQueryInterface/SMWQueryInterface.php';
		$wgSpecialPages['QueryInterface'] = array('SMWQueryInterface');
		$wgSpecialPageGroups['QueryInterface'] = 'smwplus_group';

		$wgAutoloadClasses['SMWExplanations'] = $smwgHaloIP . '/specials/SMWExplanations/SMWExplanations.php';
		$wgSpecialPages['Explanations'] = array('SMWExplanations');
		$wgSpecialPageGroups['Explanations'] = 'smwplus_group';

		$wgSpecialPages['Properties'] = array('SMWSpecialPage','Properties', 'smwfDoSpecialProperties', $smwgHaloIP . '/specials/SMWQuery/SMWAdvSpecialProperties.php');
		$wgSpecialPageGroups['Properties'] = 'smwplus_group';


		if (isset($smwgWebserviceEndpoint)) {
			$wgAutoloadClasses['SMWTripleStoreAdmin'] = $smwgHaloIP . '/specials/SMWTripleStoreAdmin/SMW_TripleStoreAdmin.php';
			$wgSpecialPages['TSA'] = array('SMWTripleStoreAdmin');
			$wgSpecialPageGroups['TSA'] = 'smwplus_group';
		}


	}

	// include SMW logger (exported as ajax function but also used locally)
	require_once($smwgHaloIP . '/includes/SMW_Logger.php');



	require_once($smwgHaloIP . '/includes/Jobs/SMW_UpdateLinksAfterMoveJob.php');
	require_once($smwgHaloIP . '/includes/Jobs/SMW_UpdatePropertiesAfterMoveJob.php');
	require_once($smwgHaloIP . '/includes/Jobs/SMW_UpdateCategoriesAfterMoveJob.php');


	// Register MW hooks
	$wgHooks['ArticleFromTitle'][] = 'smwfHaloShowListPage';
	$wgHooks['BeforePageDisplay'][]='smwfHaloAddHTMLHeader';
	$wgHooks['SpecialMovepageAfterMove'][] = 'smwfGenerateUpdateAfterMoveJob';

	// Register Annotate-Tab
	$wgHooks['SkinTemplateContentActions'][] = 'smwfAnnotateTab';


	// Register Credits
	$wgExtensionCredits['parserhook'][]= array('name'=>'SMWHalo&nbsp;Extension', 'version'=>SMW_HALO_VERSION,
			'author'=>"Thomas&nbsp;Schweitzer, Kai&nbsp;K&uuml;hn, Markus&nbsp;Nitsche, J&ouml;rg Heizmann, Frederik&nbsp;Pfisterer, Robert Ulrich, Daniel Hansch, Moritz Weiten and Michael Erdmann. Maintained by [http://www.ontoprise.de Ontoprise].", 
			'url'=>'https://sourceforge.net/projects/halo-extension', 
			'description' => 'Facilitate the use of Semantic Mediawiki for a large community of non-tech-savvy users. [http://smwforum.ontoprise.com/smwforum/index.php/Help:SMW%2B_User_Manual View feature description.]');

	global $smwgWebserviceEndpoint;
	if (isset($smwgWebserviceEndpoint)) {
		$wgHooks['InternalParseBeforeLinks'][] = 'smwfTripleStoreParserHook';
	}

	// make hook for red links
	$wgHooks['BrokenLink'][] = 'smwfBrokenLinkForPage';

	return true;
}

function smwfRegisterSPARQLInlineQueries( &$parser, &$text, &$stripstate ) {

	$parser->setFunctionHook( 'sparql', 'smwfProcessSPARQLInlineQueryParserFunction' );

	return true; // always return true, in order not to stop MW's hook processing!
}

/**
 * The {{#sparql }} parser function processing part.
 */
function smwfProcessSPARQLInlineQueryParserFunction(&$parser) {
	global $smwgWebserviceEndpoint;
	if (isset($smwgWebserviceEndpoint)) {
		global $smwgIQRunningNumber;
		$smwgIQRunningNumber++;
		$params = func_get_args();
		array_shift( $params ); // we already know the $parser ...
		return SMWSPARQLQueryProcessor::getResultFromFunctionParams($params,SMW_OUTPUT_WIKI);
	} else {
		return smwfEncodeMessages(array(wfMsgForContent('smw_sparql_disabled')));
	}
}


function smwfHaloInitMessages() {
	global $smwgHaloContLang, $smwgMessagesInitialized;
	if (isset($smwgMessagesInitialized)) return; // prevent double init
	smwfHaloInitContentMessages();
	smwfHaloInitUserMessages(); // lazy init for ajax calls
	$smwgMessagesInitialized = true;

}

function smwfInitSpecialPropertyOfSMWHalo() {
	global $smwgHaloContLang;
	// add additional special properties to SMW
	$smwgHaloContLang->registerSpecialProperties();
	return true;
}
/**
 * Registeres SMW Halo Datatypes. Called from SMW.
 */
function smwfHaloInitDatatypes() {
	global $wgAutoloadClasses, $smwgHaloIP, $smwgHaloContLang;
	$wgAutoloadClasses['SMWChemicalFormulaTypeHandler'] = $smwgHaloIP . '/includes/SMW_DV_ChemFormula.php';
	SMWDataValueFactory::registerDatatype('_chf', 'SMWChemicalFormulaTypeHandler',
	$smwgHaloContLang->getHaloDatatype('smw_hdt_chemical_formula'));
	$wgAutoloadClasses['SMWChemicalEquationTypeHandler'] = $smwgHaloIP . '/includes/SMW_DV_ChemEquation.php';
	SMWDataValueFactory::registerDatatype('_che', 'SMWChemicalEquationTypeHandler',
	$smwgHaloContLang->getHaloDatatype('smw_hdt_chemical_equation'));
	$wgAutoloadClasses['SMWMathematicalEquationTypeHandler'] = $smwgHaloIP . '/includes/SMW_DV_MathEquation.php';
	SMWDataValueFactory::registerDatatype('_meq', 'SMWMathematicalEquationTypeHandler',
	$smwgHaloContLang->getHaloDatatype('smw_hdt_mathematical_equation'));
	$wgAutoloadClasses['SMWSIUnitTypeHandler'] = $smwgHaloIP . '/includes/SMW_DV_SI.php';
	SMWDataValueFactory::registerDatatype('_siu', 'SMWSIUnitTypeHandler',
	$smwgHaloContLang->getSpecialPropertyLabel("___cfsi"));

	return true;
}


/**
 * Registers special pages for some namespaces
 */
function smwfHaloShowListPage(&$title, &$article){
	global $smwgHaloIP;
	if ( $title->getNamespace() == NS_CATEGORY ) {
		require_once($smwgHaloIP . '/includes/articlepages/SMW_CategoryPage.php');
		$article = new SMWCategoryPage($title);
	}
	return true;
}


/**
 * Registers SMW Halo Content messages.
 */
function smwfHaloInitContentLanguage($langcode) {
	global $smwgHaloIP, $smwgHaloContLang;
	if (!empty($smwgHaloContLang)) { return; }

	$smwContLangClass = 'SMW_HaloLanguage' . str_replace( '-', '_', ucfirst( $langcode ) );

	if (file_exists($smwgHaloIP . '/languages/'. $smwContLangClass . '.php')) {
		include_once( $smwgHaloIP . '/languages/'. $smwContLangClass . '.php' );
	}

	// fallback if language not supported
	if ( !class_exists($smwContLangClass)) {
		include_once($smwgHaloIP . '/languages/SMW_HaloLanguageEn.php');
		$smwContLangClass = 'SMW_HaloLanguageEn';
	}
	$smwgHaloContLang = new $smwContLangClass();


}

/**
 * Registers SMW Halo User messages.
 */
function smwfHaloInitUserMessages() {
	global $smwgHaloIP, $smwgHaloLang;
	if (!empty($smwgHaloLang)) { return; }

	global $wgMessageCache, $wgLang;

	$smwLangClass = 'SMW_HaloLanguage' . str_replace( '-', '_', ucfirst( $wgLang->getCode() ) );

	if (file_exists($smwgHaloIP . '/languages/'. $smwLangClass . '.php')) {
		include_once( $smwgHaloIP . '/languages/'. $smwLangClass . '.php' );
	}
	// fallback if language not supported
	if ( !class_exists($smwLangClass)) {
		global $smwgHaloContLang;
		$smwgHaloLang = $smwgHaloContLang;
	} else {
		$smwgHaloLang = new $smwLangClass();
	}

	$wgMessageCache->addMessages($smwgHaloLang->getUserMsgArray(), $wgLang->getCode());

}

function smwfHaloInitContentMessages() {
	global $smwgHaloContMessagesInPlace;
	if ($smwgHaloContMessagesInPlace) { return; }

	global $wgMessageCache, $smwgHaloContLang, $wgLanguageCode;
	smwfHaloInitContentLanguage($wgLanguageCode);

	$wgMessageCache->addMessages($smwgHaloContLang->getContentMsgArray(), $wgLanguageCode);
	$smwgHaloContMessagesInPlace = true;

}

/**
 * Returns GeneralStore
 */
function &smwfGetSemanticStore() {
	global $smwgMasterGeneralStore, $smwgHaloIP, $smwgBaseStore;
	if ($smwgMasterGeneralStore == NULL) {
		if ($smwgBaseStore != 'SMWHaloStore' && $smwgBaseStore != 'SMWHaloStore2') {
			trigger_error("The store '$smwgBaseStore' is not implemented for the HALO extension. Please use 'SMWHaloStore'.");
		} elseif ($smwgBaseStore == 'SMWHaloStore2') {
			require_once($smwgHaloIP . '/includes/SMW_SemanticStoreSQL2.php');
			$smwgMasterGeneralStore = new SMWSemanticStoreSQL2();
		}  else {
			require_once($smwgHaloIP . '/includes/SMW_SemanticStoreSQL.php');
			$smwgMasterGeneralStore = new SMWSemanticStoreSQL();
		}
	}
	return $smwgMasterGeneralStore;
}

/**
 * Checks if a database function is available (considers only UDF functions).
 */
function smwfDBSupportsFunction($lib) {
	global $smwgUseEditDistance;
	return isset($smwgUseEditDistance) ? $smwgUseEditDistance : false;

	// KK: this causes problems for many users since they do not
	// always have access to system tables. This is why it is better to return
	// a config variable. However, it may happen that the SimilarEntitiesBot crashes,
	// because the EDITDISTANCE function is not available.
	/*
	 $dbr =& wfGetDB( DB_SLAVE );
	 $res = $dbr->query('SELECT * FROM mysql.func WHERE dl LIKE '.$dbr->addQuotes($lib.'.%'));
	 $hasSupport = ($dbr->numRows($res) > 0);
	 $dbr->freeResult( $res );
	 return $hasSupport; */
}

/**
 * Called from MW to fill HTML Header before page is displayed.
 */
function smwfHaloAddHTMLHeader(&$out) {
	global $wgStylePath,$wgUser, $wgDefaultSkin;
	global $smwgHaloScriptPath,$smwgHaloIP, $smwgDeployVersion, $wgLanguageCode;
	global $wgOut, $smwgEnableFlogicRules;

	$rulesEnabled = isset($smwgEnableFlogicRules)
	? (($smwgEnableFlogicRules) ? 'true' : 'false')
	: 'false';

	$wgOut->addScript('<script type= "text/javascript">var smwgEnableFlogicRules='.$rulesEnabled.'</script>'."\n");

	$skin = $wgUser->getSkin();
	$skinName = $wgUser !== NULL ? $wgUser->getSkin()->getSkinName() : $wgDefaultSkin;
	$jsm = SMWResourceManager::SINGLETON();
	$jsm->addCSSIf($smwgHaloScriptPath . '/skins/Autocompletion/wick.css');


	$jsm->addCSSIf($wgStylePath .'/'.$skin->getSkinName().'/semantictoolbar.css', "edit");
	$jsm->addCSSIf($wgStylePath .'/'.$skin->getSkinName().'/semantictoolbar.css', "annotate");
	$jsm->addCSSIf($wgStylePath .'/'.$skin->getSkinName().'/lightbulb.css');
	//Remove before check in
	//print $wgStylePath.'/'.$skin->getSkinName().'/semantictoolbar.css';
	//die;
	$jsm->addCSSIf($smwgHaloScriptPath . '/skins/Annotation/annotation.css', "annotate");
	$jsm->addCSSIf($smwgHaloScriptPath . '/skins/Rules/rules.css', "edit");
	//    $jsm->addCSSIf($smwgHaloScriptPath . '/skins/Glossary/glossary.css');

	// serialize the css
	$jsm->serializeCSS($out);

	/*
	 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	 *
	 * Note: If you add new scripts to this section you have to update SMW_packscripts.php scripts too.
	 * Just add the script's name (not whole path) in the 'smw' section.
	 *
	 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	 * */
	if (!isset($smwgDeployVersion) || $smwgDeployVersion === false) {

		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/ajaxhalo.js');
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/prototype.js');
		$jsm->setScriptID($smwgHaloScriptPath .  '/scripts/prototype.js', 'Prototype_script_inclusion');

		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/scriptaculous/effects.js');
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/scriptaculous/slider.js');
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/scriptaculous/dragdrop.js');

		// The above id is essential for the JavaScript to find out the $smwgHaloScriptPath to
		// include images. Changes in the above must always be coordinated with the script!

		//global $smwgEnableLogging;
		//if($smwgEnableLogging  === true){
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/Logger/smw_logger.js', "all");
		//}
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/OntologyBrowser/generalTools.js');

		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/GeneralGUI/breadcrump.js');
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/GeneralGUI/contentSlider.js');
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/GeneralGUI/generalGUI.js');

		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/Language/SMW_Language.js');

		smwfHaloAddJSLanguageScripts($jsm);

		$jsm->addScriptIf($wgStylePath . '/'.$skinName.'/STB_Framework.js', "edit");

		$jsm->addScriptIf($wgStylePath . '/'.$skinName.'/STB_Framework.js', "annotate");



		$jsm->addScriptIf($wgStylePath . '/'.$skinName.'/STB_Divcontainer.js', "edit");

		$jsm->addScriptIf($wgStylePath . '/'.$skinName.'/STB_Divcontainer.js', "annotate");



		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/Autocompletion/wick.js');

		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Help.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Help.js', "annotate");


		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Links.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Links.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/WikiTextParser/Annotation.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/WikiTextParser/Annotation.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/WikiTextParser/WikiTextParser.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/WikiTextParser/WikiTextParser.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Ontology.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Ontology.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_DataTypes.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_DataTypes.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_GenericToolbarFunctions.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_GenericToolbarFunctions.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Container.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Container.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Marker.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Category.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Category.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/AdvancedAnnotation/SMW_AnnotationHints.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/AdvancedAnnotation/SMW_GardeningHints.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/AdvancedAnnotation/SMW_GardeningHints.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/AdvancedAnnotation/SMW_SaveAnnotations.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Relation.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Relation.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Rule.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Rule.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_CategoryRule.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_CalculationRule.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_PropertyChain.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Properties.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Properties.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Refresh.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Refresh.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_DragAndResize.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_ContextMenu.js', "annotate");

		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMWEditInterface.js', "edit");
		$jsm->addScriptIf($wgStylePath . '/'.$skinName.'/obSemToolContribution.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/AdvancedAnnotation/SMW_AdvancedAnnotation.js', "annotate");
	} else {
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/prototype.js');
		$jsm->setScriptID($smwgHaloScriptPath .  '/scripts/prototype.js', 'Prototype_script_inclusion');

		smwfHaloAddJSLanguageScripts($jsm);
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/deployGeneralTools.js');
		$jsm->addScriptIf($wgStylePath . '/'.$skinName.'/STB_Framework.js');
		$jsm->addScriptIf($wgStylePath . '/'.$skinName.'/STB_Divcontainer.js');
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMWEditInterface.js');
		$jsm->addScriptIf($wgStylePath . '/'.$skinName.'/obSemToolContribution.js');
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/deployGeneralScripts.js');


	}



	// serialize the scripts
	$jsm->serializeScripts($out);
	// for additinal scripts which are dependant of Halo scripts (e.g. ACL extension)
	wfRunHooks("SMW_AddScripts", array (& $out));

	return true; // always return true, in order not to stop MW's hook processing!
}

/**
 * Add appropriate JS language script
 */
function smwfHaloAddJSLanguageScripts(& $jsm, $mode = "all", $namespace = -1, $pages = array()) {
	global $smwgHaloIP, $wgLanguageCode, $smwgHaloScriptPath, $wgUser;

	// content language file
	$lng = '/scripts/Language/SMW_Language';
	if (!empty($wgLanguageCode)) {
		$lng .= ucfirst($wgLanguageCode).'.js';
		if (file_exists($smwgHaloIP . $lng)) {
			$jsm->addScriptIf($smwgHaloScriptPath . $lng, $mode, $namespace, $pages);
		} else {
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/Language/SMW_LanguageEn.js', $mode, $namespace, $pages);
		}
	} else {
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/Language/SMW_LanguageEn.js', $mode, $namespace, $pages);
	}

	// user language file
	$lng = '/scripts/Language/SMW_Language';
	if (isset($wgUser)) {
		$lng .= "User".ucfirst($wgUser->getOption('language')).'.js';
		if (file_exists($smwgHaloIP . $lng)) {
			$jsm->addScriptIf($smwgHaloScriptPath . $lng, $mode, $namespace, $pages);
		} else {
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/Language/SMW_LanguageUserEn.js', $mode, $namespace, $pages);
		}
	} else {
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/Language/SMW_LanguageUserEn.js', $mode, $namespace, $pages);
	}
}

/**
 * Callback function for hook 'SMW_SpecialValue'. It returns a data value for
 * the special type "_siu" (SI-Units), if requested.
 */
function smwfHaloSpecialValues($typeID, $value, $caption, &$result) {
	if ($typeID == "___cfsi") {
		$result = SMWDataValueFactory::newTypeIDValue('_siu', $value, $caption);
	}
	return true;
}

/**
 * Called when an article has been moved.
 */
function smwfGenerateUpdateAfterMoveJob(& $moveform, & $oldtitle, & $newtitle) {
	$store = smwfGetStore();

	$jobs = array();
	$titlesToUpdate = $oldtitle->getLinksTo();
	$params[] = $oldtitle->getText();
	$params[] = $newtitle->getText();

	$fullparams[] = $oldtitle->getPrefixedText();
	$fullparams[] = $newtitle->getPrefixedText();

	foreach ($titlesToUpdate as $uptitle) {
		if ($uptitle !== NULL) $jobs[] = new SMW_UpdateLinksAfterMoveJob($uptitle, $fullparams);
	}

	if ($oldtitle->getNamespace()==SMW_NS_PROPERTY) {
			
		$wikipagesToUpdate = $store->getAllPropertySubjects( SMWPropertyValue::makeUserProperty($oldtitle->getDBkey()));
		foreach ($wikipagesToUpdate as $dv)
		if ($dv->getTitle() !== NULL) $jobs[] = new SMW_UpdatePropertiesAfterMoveJob($dv->getTitle(), $params);
	}

	if ($oldtitle->getNamespace()==NS_CATEGORY) {
		$wikipagesToUpdate = smwfGetSemanticStore()->getDirectInstances($oldtitle);
		foreach ($wikipagesToUpdate as $inst)
		if ($inst !== NULL) $jobs[] = new SMW_UpdateCategoriesAfterMoveJob($inst, $params);
	}

	Job :: batchInsert($jobs);
	return true;
}

function smwfAnnotateTab ($content_actions) {
	//Check if ontoskin is available
	global $wgUser, $wgTitle;
	if(!method_exists($wgUser->getSkin(),'isSemantic'))
	return true;
	if($wgUser->getSkin()->isSemantic() != true)
	return true;
	if ($wgTitle->getNamespace() == NS_SPECIAL) return true; // Special page
	//Check if edit tab is present, if not don't at annote tab
	//if (!array_key_exists('edit',$content_actions) )
	//return true;
	global $wgUser, $wgRequest;
	$action = $wgRequest->getText( 'action' );
	//Build annotate tab
	global $wgTitle;
	$main_action['main'] = array(
        	'class' => ($action == 'annotate') ? 'selected' : false,
        	'text' => wfMsg('smw_annotation_tab'), //Title of the tab
        	'href' => $wgTitle->getLocalUrl('action=annotate')   //where it links to
	);

	//Find position of edit button
	$editpos = isset($content_actions['edit'])
				? count(range(0,$content_actions['edit']))+1
				: count($content_actions);
	//Split array
	$beforeedit = array_slice($content_actions,0,$editpos-1);
	$afteredit = array_slice($content_actions,$editpos-1,count($content_actions));
	//Merge array with new action
	$content_actions = array_merge( $beforeedit, $main_action);   //add a new action
	$content_actions = array_merge( $content_actions, $afteredit);
	return true;
}

/**
 * This function is called from the parser, before <nowiki> parts have been
 * removed and before templates etc are expanded.
 *
 * @param unknown_type $parser
 * @param unknown_type $text
 * @param unknown_type $strip_stat
 */
function smwfAAMBeforeStrip(&$parser, &$text, &$strip_stat) {
	$popts = $parser->getOptions();
	if (method_exists($popts, "getParsingContext")) {
		if ($popts->getParsingContext() != "Main article") {
			return true; 
		}
	}
		
	global $smwgDisableAAMParser;
	if ($smwgDisableAAMParser) {
		return true;
	}

	global $smwgHaloIP, $smwgHaloAAMParser;
	require_once( "$smwgHaloIP/includes/SMW_AAMParser.php");

	if ($smwgHaloAAMParser == null) {
		$smwgHaloAAMParser = new SMWH_AAMParser($text);
	}
	$parser->mOptions->setEditSection(false);
	$text = $smwgHaloAAMParser->addWikiTextOffsets($text);
	return true;
}


/**
 * This function is called from the parser, after <nowiki> parts have been
 * removed, but before templates etc are expanded.
 *
 * @param unknown_type $parser
 * @param unknown_type $text
 * @param unknown_type $strip_stat
 */
function smwfAAMAfterStrip(&$parser, &$text, &$strip_stat) {
	global $smwgDisableAAMParser;
	if ($smwgDisableAAMParser) {
		return true;
	}
	global $smwgHaloAAMParser;
	if ($smwgHaloAAMParser == null) {
		return true;
	}
	$text = $smwgHaloAAMParser->highlightAnnotations($text);
	return true;
}

/**
 * This function is called from the parser, after templates etc. are
 * expanded.
 * Annotations i.e. text enclosed in [[]] is highlighted.
 *
 * @param unknown_type $parser
 * @param unknown_type $text
 */
function smwfAAMBeforeLinks(&$parser, &$text) {
	return true;
}

/**
 * This function is called from the parser, when the HTML is nearly completely
 * generated.
 *
 * @param unknown_type $parser
 * @param unknown_type $text
 */
function smwfAAMBeforeTidy(&$parser, &$text) {
	return true;
}

/**
 * This function is called from the parser, when the HTML is nearly completely
 * generated.
 * The wiki text offsets that have been introduced in a previous parsing stage
 * are replaced by their corresponding HTML.
 *
 * @param unknown_type $parser
 * @param unknown_type $text
 */
function smwfAAMAfterTidy(&$parser, &$text) {
	global $smwgDisableAAMParser;
	if ($smwgDisableAAMParser) {
		return true;
	}
	global $smwgHaloAAMParser, $wgOut, $wgTitle, $smwgDisableAAMParser;
	if ($smwgHaloAAMParser == null) {
		return true;
	}
	$text = $smwgHaloAAMParser->wikiTextOffset2HTML($text);
	$text = $smwgHaloAAMParser->highlightAnnotations2HTML($text);
	// Set the article's title
	//	$t = wfMsg( 'smw_annotating', $parser->mTitle->getPrefixedText() );
	$t = wfMsg( 'smw_annotating', $wgTitle->getPrefixedText() );

	// setPageTitle calls the parser recursively
	// => disable the parser
	$smwgDisableAAMParser = true;
	$wgOut->setPageTitle($t);

	// The parser is left disabled, as there are several parsing phases after the
	// main text that is now completed.

	return true;
}

/**
 * This function is called from the parser, when the HTML is nearly completely
 * generated.
 *
 * @param unknown_type $parser
 * @param unknown_type $text
 */
function smwfAAMBeforeHTML(&$out, &$text) {
	return true;
}

/**
 * This function is called when the annotation mode is activated. It renders
 * the article with highlighted annotations.
 *
 * @param string $action The action i.e. "annotate"
 * @param Article $article The article that will be displayed.
 * @return false => processing should continue
 */
function smwfAnnotateAction($action, $article) {
	if ($action != 'annotate') {
		return true;
	}
	$title = $article->getTitle();
	$title->invalidateCache();
	$article->view();

	// The resolution of timestamps for the cache is only in seconds. Invalidate
	// the cache by setting a timestamp 2 seconds from now.
	$now = wfTimestamp(TS_MW, time()+2);
	$dbw = wfGetDB( DB_MASTER );
	$success = $dbw->update( 'page',
	array( /* SET */
				'page_touched' => $now
	), array( /* WHERE */
				'page_namespace' => $title->getNamespace() ,
				'page_title' => $title->getDBkey()
	), 'SMW_Initialize::smwfAnnotateAction'
	);

	return false;
}

// OntologyBrowser scripts callback
// includes necessary script and css files.
function smwOBAddHTMLHeader(&$out) {
	global $wgTitle;
	if ($wgTitle->getNamespace() != NS_SPECIAL) return true;

	global $smwgHaloScriptPath, $smwgDeployVersion, $smwgHaloIP, $wgLanguageCode, $smwgScriptPath;

	$jsm = SMWResourceManager::SINGLETON();

	if (!isset($smwgDeployVersion) || $smwgDeployVersion === false) {
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/ajaxhalo.js');
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/prototype.js', "all", -1, NS_SPECIAL.":OntologyBrowser");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/scriptaculous/effects.js', "all", -1, NS_SPECIAL.":OntologyBrowser");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/scriptaculous/dragdrop.js', "all", -1, NS_SPECIAL.":OntologyBrowser");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/OntologyBrowser/generalTools.js', "all", -1, NS_SPECIAL.":OntologyBrowser");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/Language/SMW_Language.js', "all", -1, NS_SPECIAL.":OntologyBrowser");

		smwfHaloAddJSLanguageScripts($jsm, "all", -1, NS_SPECIAL.":OntologyBrowser");

		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/OntologyBrowser/ontologytools.js', "all", -1, NS_SPECIAL.":OntologyBrowser");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/OntologyBrowser/treeview.js', "all", -1, NS_SPECIAL.":OntologyBrowser");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/OntologyBrowser/treeviewActions.js', "all", -1, NS_SPECIAL.":OntologyBrowser");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/OntologyBrowser/treeviewData.js', "all", -1, NS_SPECIAL.":OntologyBrowser");
		$jsm->addScriptIf($smwgScriptPath .  '/skins/SMW_tooltip.js', "all", -1, NS_SPECIAL.":OntologyBrowser");
	} else {
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/ajaxhalo.js');
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/prototype.js', "all", -1, NS_SPECIAL.":OntologyBrowser");
		smwfHaloAddJSLanguageScripts($jsm, "all", -1, NS_SPECIAL.":OntologyBrowser");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/deployGeneralTools.js', "all", -1, NS_SPECIAL.":OntologyBrowser");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/OntologyBrowser/deployOntologyBrowser.js', "all", -1, NS_SPECIAL.":OntologyBrowser");
	}

	$jsm->addCSSIf($smwgHaloScriptPath . '/skins/OntologyBrowser/treeview.css', "all", -1, NS_SPECIAL.":OntologyBrowser");
	$jsm->addCSSIf($smwgScriptPath . '/skins/SMW_custom.css', "all", -1, NS_SPECIAL.":OntologyBrowser");

	return true;
}

function smwPRAddHTMLHeader(&$out) {
	global $wgTitle;
	if ($wgTitle->getNamespace() != NS_SPECIAL) return true;

	global $smwgHaloScriptPath, $smwgDeployVersion, $smwgHaloIP, $wgLanguageCode, $smwgScriptPath;

	$jsm = SMWResourceManager::SINGLETON();

	if (!isset($smwgDeployVersion) || $smwgDeployVersion === false) {
		$jsm->addScriptIf($smwgScriptPath .  '/skins/SMW_tooltip.js', "all", -1, NS_SPECIAL.":Properties");
	} else {
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/prototype.js', "all", -1, NS_SPECIAL.":Properties");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/deployGeneralTools.js', "all", -1, NS_SPECIAL.":Properties");

	}

	$jsm->addCSSIf($smwgScriptPath . '/skins/SMW_custom.css', "all", -1, NS_SPECIAL.":Properties");

	return true;
}





//function smwWSAddHTMLHeader(&$out) {
//	global $wgTitle;
//	if ($wgTitle->getNamespace() != NS_SPECIAL) return true;
//
//	global $smwgHaloScriptPath, $smwgDeployVersion;
//
//	$jsm = SMWResourceManager::SINGLETON();
//
//	$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/prototype.js', "all", -1, array(NS_SPECIAL.":WebServicerepository", NS_SPECIAL.":DefineWebService"));
//	$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/WebServices/webservices-rep.js', "all", -1, array(NS_SPECIAL.":WebServicerepository"));
//	$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/WebServices/def-webservices.js', "all", -1, array(NS_SPECIAL.":DefineWebService"));
//
//
//	$jsm->addCSSIf($smwgHaloScriptPath . '/skins/webservices/webservices.css', "all", -1, NS_SPECIAL.":DefineWebService");
//
//
//
//	return true;
//}


// QueryInterface scripts callback
// includes necessary script and css files.
function smwfQIAddHTMLHeader(&$out){
	global $wgTitle;
	if ($wgTitle->getNamespace() != NS_SPECIAL) return true;

	global $smwgHaloScriptPath, $smwgDeployVersion, $smwgScriptPath, $srfgScriptPath;


	$jsm = SMWResourceManager::SINGLETON();

	if (!isset($smwgDeployVersion) || $smwgDeployVersion === false) {
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/prototype.js', "all", -1, NS_SPECIAL.":QueryInterface");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/Language/SMW_Language.js', "all", -1, NS_SPECIAL.":QueryInterface");
		smwfHaloAddJSLanguageScripts($jsm, "all", -1, NS_SPECIAL.":QueryInterface");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/Logger/smw_logger.js', "all", -1, NS_SPECIAL.":QueryInterface");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/OntologyBrowser/generalTools.js', "all", -1, NS_SPECIAL.":QueryInterface");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/QueryInterface/treeviewQI.js', "all", -1, NS_SPECIAL.":QueryInterface");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/QueryInterface/queryTree.js', "all", -1, NS_SPECIAL.":QueryInterface");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/QueryInterface/Query.js', "all", -1, NS_SPECIAL.":QueryInterface");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/QueryInterface/QIHelper.js', "all", -1, NS_SPECIAL.":QueryInterface");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/SemanticToolbar/SMW_Help.js', "all", -1, NS_SPECIAL.":QueryInterface");
		$jsm->addScriptIf($smwgScriptPath .  '/skins/SMW_tooltip.js', "all", -1, NS_SPECIAL.":QueryInterface");
		// add result format javascripts for query interface
		if ($srfgScriptPath != null || $srfgScriptPath != "") {
			// timeline
			$jsm->addScriptIf($srfgScriptPath .  '/Timeline/SRF_timeline.js', "all", -1, array(NS_SPECIAL.":QueryInterface"));
			$jsm->addScriptIf($srfgScriptPath .  '/Timeline/SimileTimeline/timeline-api.js', "all", -1, array(NS_SPECIAL.":QueryInterface"));

			// exhibit
			//      $jsm->addScriptIf($srfgScriptPath .  '/Exhibit/includes/src/webapp/api/exhibit-api.js?autoCreate=false&safe=true', "all", -1, array("-1:QueryInterface"));
			//      $jsm->addScriptIf($srfgScriptPath .  '/Exhibit/SRF_Exhibit.js', "all", -1, array("-1:QueryInterface"));
		}
	} else {

		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/prototype.js', "all", -1, NS_SPECIAL.":QueryInterface");
		smwfHaloAddJSLanguageScripts($jsm, "all", -1, NS_SPECIAL.":QueryInterface");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/deployGeneralTools.js', "all", -1, NS_SPECIAL.":QueryInterface");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/QueryInterface/deployQueryInterface.js', "all", -1, NS_SPECIAL.":QueryInterface");
		
		// add result format javascripts for query interface
		if ($srfgScriptPath != null || $srfgScriptPath != "") {
			// timeline
			$jsm->addScriptIf($srfgScriptPath .  '/Timeline/SRF_timeline.js', "all", -1, array(NS_SPECIAL.":QueryInterface"));
			$jsm->addScriptIf($srfgScriptPath .  '/Timeline/SimileTimeline/timeline-api.js', "all", -1, array(NS_SPECIAL.":QueryInterface"));

			// exhibit
			//      $jsm->addScriptIf($srfgScriptPath .  '/Exhibit/includes/src/webapp/api/exhibit-api.js?autoCreate=false&safe=true', "all", -1, array("-1:QueryInterface"));
			//      $jsm->addScriptIf($srfgScriptPath .  '/Exhibit/SRF_Exhibit.js', "all", -1, array("-1:QueryInterface"));
		}
	}
	$jsm->addCSSIf($smwgScriptPath .  '/skins/SMW_custom.css', "all", -1, NS_SPECIAL.":QueryInterface");
	$jsm->addCSSIf($smwgHaloScriptPath . '/skins/QueryInterface/treeview.css', "all", -1, NS_SPECIAL.":QueryInterface");
	$jsm->addCSSIf($smwgHaloScriptPath . '/skins/QueryInterface/qi.css', "all", -1, NS_SPECIAL.":QueryInterface");

	return true; // do not load other scripts or CSS
}

// TermImport scripts callback
// includes necessary css files.
//function smwTIAddHTMLHeader(&$out){
//	global $wgTitle;
//	if ($wgTitle->getNamespace() != NS_SPECIAL) return true;
//
//	global $smwgHaloScriptPath, $smwgDeployVersion, $smwgScriptPath;
//
//
//	$jsm = SMWResourceManager::SINGLETON();
//
//	$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/prototype.js', "all", -1, NS_SPECIAL.":TermImport");
//	smwfHaloAddJSLanguageScripts($jsm, "all", -1, NS_SPECIAL.":TermImport");
//	$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/TermImport/termImport.js', "all", -1, NS_SPECIAL.":TermImport");
//
//	$jsm->addCSSIf($smwgScriptPath .  '/skins/SMW_custom.css', "all", -1, NS_SPECIAL.":TermImport");
//	$jsm->addCSSIf($smwgHaloScriptPath . '/skins/TermImport/termimport.css', "all", -1, NS_SPECIAL.":TermImport");
//
//	return true; // do not load other scripts or CSS
//}


function smwfGetAjaxMethodPrefix() {
	$func_name = isset( $_POST["rs"] ) ? $_POST["rs"] : (isset( $_GET["rs"] ) ? $_GET["rs"] : NULL);
	if ($func_name == NULL) return NULL;
	return substr($func_name, 4, 4); // return _xx_ of smwf_xx_methodname, may return FALSE
}

/**
 * Register extra AC related options in Preferences->Misc
 */
function smwfAutoCompletionToggles(&$extraToggles) {
	$extraToggles[] = "autotriggering";
	return true;
}

function smwfSetUserDefinedCookies(& $user) {
	global $wgScriptPath;
	$triggerMode = $user->getOption( "autotriggering" ) == 1 ? "auto" : "manual";
	setcookie("AC_mode", $triggerMode, 0, "$wgScriptPath/"); // cookie gets invalid at session-end.
	return true;
}

function smwfRegisterCommaAnnotation( &$parser, &$text, &$stripstate ) {
	$parser->setFunctionHook( 'annotateList', 'smwfCommaAnnotation' );
	return true; // always return true, in order not to stop MW's hook processing!
}

function smwfCommaAnnotation(&$parser){
	$params = func_get_args();
	array_shift( $params ); // we already know the $parser ...
	$annoName = $params[0];
	$annoValues = split(',', $params[1]);
	$ret = '';
	for ($i=0; $i<sizeof($annoValues); $i++){
		$val = $annoValues[$i];
		$val = trim($val);
		if ($i == 0)
		$ret .= "[[$annoName::$val]]";
		else
		$ret .= ", [[$annoName::$val]]";
	}
	return $ret;
}

function smwfAddHaloMagicWords(&$magicWords, $langCode){
	$magicWords['annotateList'] = array( 0, 'annotateList' );
	$magicWords['sparql']  = array( 0, 'sparql' );
	return true;
}

/**
 * Hook which populates Toolbox toolbar
 *
 * @param $template SkinTemplate class
 */
function smwfOntoSkinTemplateToolboxEnd(& $template) {
	echo smwfCreateLinks('Toolbox');
	return true;
}

/**
 * Hook which populates Navigation toolbar
 *
 * @param $template SkinTemplate class
 */
function smwfOntoSkinTemplateNavigationEnd(& $template) {
	echo smwfCreateLinks('Navigation');
	return true;
}

/**
 * Creates links for different groups by accessing group link pages.
 * Name of page is: $name_$group
 *
 * @return HTML
 */
function smwfCreateLinks($name) {
	global $wgUser, $wgTitle;
	$groups = $wgUser->getGroups();
	$links = array();
	foreach($groups as $g) {
		$nav = new Article(Title::newFromText($name.'_'.$g, NS_MEDIAWIKI));
		$content = $nav->fetchContent(0,false,false);
		$matches = array();
		preg_match_all('/\*\s*([^|]+)\|\s*([^|\n]*)(\|.*)?/', $content, $matches);
		for($i = 0; $i < count($matches[0]); $i++) {
			$links[$matches[2][$i]] = $matches[1][$i];
			$extraAttributes[$matches[2][$i]] = isset($matches[3][$i]) ? substr(trim($matches[3][$i]),1): "";
		}
	}
	$links = array_unique($links);
	$result = "";
	foreach($links as $name => $page_title) {
		$name = Sanitizer::stripAllTags($name);
		$page_title = Sanitizer::stripAllTags($page_title);
		$query = "";
		if (stripos($page_title, "?") !== false) {
			$query = substr($page_title, stripos($page_title, "?")+1);
			$page_title = substr($page_title, 0, stripos($page_title, "?"));
		}

		// Replace some variables:
		// PAGE_TITLE : Page title WITH namespace
		// PAGE_TITLE_WNS : Page title WITHOUT namespace
		// PAGE_NS : Page namespace as text
		$query = str_replace("{{{PAGE_TITLE}}}", $wgTitle->getPrefixedDBkey(), $query);
		$query = str_replace("{{{PAGE_NS}}}", $wgTitle->getNsText(), $query);
		$query = str_replace("{{{PAGE_TITLE_WNS}}}", $wgTitle->getDBkey(), $query);
		$page_title = str_replace("{{{PAGE_TITLE}}}", $wgTitle->getPrefixedDBkey(), $page_title);

		//Check if ontoskin is available else return code for new skins
		global $wgUser;
		if($wgUser->getSkin() == 'ontoskin'){
			$result .= '<li><a href="'.Skin::makeUrl($page_title, $query).'" '.$extraAttributes[$name].'>'.$name.'</a></li>';
		} else {
			$result .= '<tr><td><div class="smwf_naviitem"><a href="'.Skin::makeUrl($page_title, $query).'" '.$extraAttributes[$name].'>'.$name.'</a></div></td></tr>';
		}
	}
	return $result;
}

/**
 * Includes Navigation tree in sidebar
 */
function smwfNavTree() {
	global $wgUser,$wgTitle,$wgParser;
	if (is_object($wgParser)) $psr =& $wgParser; else $psr = new Parser;
	$opt = ParserOptions::newFromUser($wgUser);
	$nav_title = Title::newFromText('NavTree', NS_MEDIAWIKI);
	if (!$nav_title->exists()) return true;
	$nav = new Article($nav_title);
	$out = $psr->parse($nav->fetchContent(0,false,false),$wgTitle,$opt,true,true);
	echo $out->getText();
	$groups = $wgUser->getGroups();
	foreach($groups as $g) {
		$title = Title::newFromText('NavTree_'.$g, NS_MEDIAWIKI);
		if ($title->exists()) {
			$nav = new Article($title);
			$out = $psr->parse($nav->fetchContent(0,false,false),$wgTitle,$opt,true,true);
			echo '<br/>'.$out->getText();
		}
	}
	return true;
}

function wfAddCustomVariable(&$magicWords) {
	foreach($GLOBALS['wgCustomVariables'] as $var) $magicWords[] = "MAG_$var";
	return true;
}

function wfAddCustomVariableID(&$variables) {
	foreach($GLOBALS['wgCustomVariables'] as $var) $variables[] = constant("MAG_$var");
	return true;
}

function wfAddCustomVariableLang(&$langMagic, $langCode = 0) {
	foreach($GLOBALS['wgCustomVariables'] as $var) {
		$magic = "MAG_$var";
		$langMagic[defined($magic) ? constant($magic) : $magic] = array(0,$var);
	}
	return true;
}

function wfGetCustomVariable(&$parser,&$cache,&$index,&$ret) {
	switch ($index) {

		case MAG_CURRENTUSER:
			$parser->disableCache(); # Mark this content as uncacheable
			$ret = $GLOBALS['wgUser']->mName;
			break;

	}
	return true;
}



/**
 * Parses additinal semantic data need for a triple store:
 *
 *  1. categories
 *  2. rules (optional)
 */
function smwfTripleStoreParserHook(&$parser, &$text, &$strip_state = null) {
	global $smwgIP, $smwgTripleStoreGraph, $smwgRuleRewriter, $smwgEnableFlogicRules;
	global $wgContLang;
	include_once($smwgIP . '/includes/SMW_Factbox.php');

	SMWTripleStore::$fullSemanticData = new SMWFullSemanticData();

	$categoryText = $wgContLang->getNsText(NS_CATEGORY);
	// parse categories:
	$categoryLinkPattern = '/\[\[\s*                   # Beginning of the link
                            '.$categoryText.'\s*:      # category link (case insensitive!)
                            ([^\[\]]*)                 # category
                            \]\]                       # End of link
                            /ixu';              # case-insensitive, ignore whitespaces, UTF-8 compatible
	$categories = array();
	$matches = array();
	preg_match_all($categoryLinkPattern, $text, $matches);
	if (isset($matches[1])) {
		foreach($matches[1] as $m) {
			$labelIndex = strpos($m, '|');
			$m = $labelIndex !== false ? substr($m, 0, $labelIndex) : $m;
			$categories[] = Title::newFromText(trim($m), NS_CATEGORY);
		}
	}

	// rules
	// meant to be a hash map $ruleID => $ruleText,
	// where $ruleID has to be a URI (i.e. containing at least one colon)

	$rules = array();
	if (isset($smwgEnableFlogicRules)) {
		// search rule tags
		$ruleTagPattern = '/&lt;rule(.*?&gt;)(.*?.)&lt;\/rule&gt;/ixus';
		preg_match_all($ruleTagPattern, trim($text), $matches);

		// at least one parameter and content?
		for($i = 0; $i < count($matches[0]); $i++) {
			$header = trim($matches[1][$i]);
			$ruletext = trim($matches[2][$i]);

			// parse header parameters
			$ruleparamterPattern = "/([^=]+)=\"([^\"]*)\"/ixus";
			preg_match_all($ruleparamterPattern, $header, $matchesheader);

			$rewrite = true;
			for ($j = 0; $j < count($matchesheader[0]); $j++) {
				if (trim($matchesheader[1][$j]) == 'norewrite') {
					$rewrite = false;
				}
			}
			// fetch name of rule (ruleid) and put into rulearray
			for ($j = 0; $j < count($matchesheader[0]); $j++) {
				if (trim($matchesheader[1][$j]) == 'name') {
					$name = $matchesheader[2][$j];
					$name = $smwgTripleStoreGraph . "/" . $name;
					$ruletext = str_replace("&lt;","<", $ruletext);
					$ruletext = str_replace("&gt;",">", $ruletext);
					$rules[$name] = $smwgRuleRewriter != NULL && $rewrite ? $smwgRuleRewriter->rewrite($ruletext) : $ruletext;
				}
			}
		}
		// remove rule tags from text
		$text = preg_replace($ruleTagPattern, "", $text);
	}
	// parse redirects
	$redirectLinkPattern = '/\#REDIRECT          # REDIRECT command
                            \[\[                # Beginning of the link
                            ([^]]+)               # target
                            \]\]                # End of link
                            /ixu';              # case-insensitive, ignore whitespaces, UTF-8 compatible
	$redirects = array();
	$matches = array();
	preg_match_all($redirectLinkPattern, $text, $matches);
	if (isset($matches[1])) {
		foreach($matches[1] as $m) {
			$redirects[] = Title::newFromText($m);
		}
	}

	SMWTripleStore::$fullSemanticData->setCategories($categories);
	SMWTripleStore::$fullSemanticData->setRules($rules);
	SMWTripleStore::$fullSemanticData->setRedirects($redirects);
	return true;
}

function smwfAddDerivedFacts(& $text, $semdata) {

	wfLoadExtensionMessages('SemanticMediaWiki');
	global $wgContLang;
	$derivedFacts = SMWFullSemanticData::getDerivedProperties($semdata);
	$derivedFactsFound = false;

	$text .= '<div class="smwfact">' .
				'<span class="smwfactboxhead">' . 
	wfMsg('smw_df_derived_facts_about',
	$derivedFacts->getSubject()->getText()) .
				'</span>' .
				'<table class="smwfacttable">' . "\n";

	foreach($derivedFacts->getProperties() as $property) {
		if (!$property->isShown()) { // showing this is not desired, hide
			continue;
		}
		if (stripos($property->getShortWikiText(), 'http://www.w3.org/') === 0) {
			// Special property with W3C namespace
			continue;
		}
		if ($property->isUserDefined()) { // user defined property
			$property->setCaption(preg_replace('/[ ]/u','&nbsp;',$property->getWikiValue(),2));
			/// NOTE: the preg_replace is a slight hack to ensure that the left column does not get too narrow
			$text .= '<tr><td class="smwpropname">' . $property->getLongWikiText(true) . '</td><td class="smwprops">';
		} elseif ($property->isVisible()) { // predefined property
			$text .= '<tr><td class="smwspecname">' . $property->getLongWikiText(true) . '</td><td class="smwspecs">';
		} else { // predefined, internal property
			continue;
		}

		$propvalues = $derivedFacts->getPropertyValues($property);
		$l = count($propvalues);
		$i=0;
		foreach ($propvalues as $propvalue) {
			$derivedFactsFound = true;

			if ($i!=0) {
				if ($i>$l-2) {
					$text .= wfMsgForContent('smw_finallistconjunct') . ' ';
				} else {
					$text .= ', ';
				}
			}
			$i+=1;

			// encode the parameters in the links as
			//Special:Explanations/i:<subject>/p:<property>/v:<value>/mode:property
			//The form ?i=...&p=... is no longer possible, as the fact box is now created
			// as wikitext and special chars in links are encoded.
			$link = $special = SpecialPage::getTitleFor('Explanations')->getPrefixedText();
			$link .= '/i:'.$derivedFacts->getSubject()->getTitle()->getPrefixedDBkey();
			$link .= '/p:'.$property->getWikiPageValue()->getTitle()->getPrefixedDBkey();
			$link .= '/v:'.urlencode($propvalue->getWikiValue());
			$link .= '/mode:property';

			$propRep = $propvalue->getLongWikiText($property->getPropertyTypeID() == "_wpg") .
			      '&nbsp;'.
				  '<span class="smwexplanation">[['.$link.'|+]]</span>';

			$text .= $propRep;

		}
		$text .= '</td></tr>';
	}
	$text .= '</table></div>';

	if (!$derivedFactsFound) {
		$text = '';
	}
	return true;
}

/**
 * If a redlink is returned, change the links to use the Create_new_page in case this one exists
 * and if the page to create is within the main namespace
 * This function is a parser hook for "BrokenLink" and works with Mediawiki 1.13 and higher
 */
function smwfBrokenLinkForPage(&$linker, $title, $query, &$u, &$style, &$prefix, &$text, &$inside, &$trail) {
	// check if page Create_new_page exists in the wiki, if not quit here
	if (!Title::newFromDBkey('Create_new_page')->exists())
		return true;
	// check if this is an unmodified red link, if not, quit here
	if (strpos($u, 'action=edit&amp;redlink=1') === false)
		return true;
	// get the namespace of the new page, if it's not NS_MAIN, quit
	if ( NS_MAIN != $title->getNamespace())
		return true;
	// build title string for new page and create link to Create_new_page with target param
	$title_text = ucfirst($title->getText());
	global $wgScript;
	$u = $wgScript.'?title=Create_new_page&target='.urlencode($title_text);
	return true;
}

?>