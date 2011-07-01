<?php
/**
 *
 * Created on 13.09.2007
 *
 * @defgroup SMWHalo Halo extension
 * @defgroup SMWHaloSpecials Halo special pages
 *
 * Author: kai
 */
if ( !defined( 'MEDIAWIKI' ) ) die;

define('SMW_HALO_VERSION', '{{$VERSION}}-for-SMW-1.5.6 [B{{$BUILDNUMBER}}]');

// constant for special schema properties
define('SMW_SSP_HAS_DOMAIN_AND_RANGE_HINT', 1);
define('SMW_SSP_HAS_MAX_CARD', 2);
define('SMW_SSP_HAS_MIN_CARD', 3);
define('SMW_SSP_IS_INVERSE_OF', 4);
define('SMW_SSP_IS_EQUAL_TO', 5);
define('SMW_SSP_ONTOLOGY_URI', 6);

// constants for special categories
define('SMW_SC_TRANSITIVE_RELATIONS', 0);
define('SMW_SC_SYMMETRICAL_RELATIONS', 1);

// default cardinalities
define('CARDINALITY_MIN',0);
define('CARDINALITY_UNLIMITED', 2147483647); // MAXINT

// max depth of category graph
define('SMW_MAX_CATEGORY_GRAPH_DEPTH', 10);

$smwgHaloIP = $IP . '/extensions/SMWHalo';
$smwgHaloScriptPath = $wgScriptPath . '/extensions/SMWHalo';
$smwgHaloAAMParser = null;
$smwgDisableAAMParser = false;
$smwgProcessedAnnotations = null;
$wgCustomVariables = array('CURRENTUSER', 'CURRENTUSERNS', 'NOW', 'TODAY');
$smwgHaloStyleVersion = preg_replace('/[^\d]/', '', '{{$BUILDNUMBER}}' );
if (strlen($smwgHaloStyleVersion) > 0)
    $smwgHaloStyleVersion= '?'.$smwgHaloStyleVersion;

//Disable default mediawiki autocompletion, so it does not interfere with the mw one
$wgEnableMWSuggest = false;

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
	global $smwghConvertColoumns;
	if (!isset($smwghConvertColoumns)) $smwghConvertColoumns="utf8";

	// Register the triple store as source for a query with the alias "tsc"
	global $smwgQuerySources;
	$smwgQuerySources += array("tsc" => "SMWTripleStore");

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

	//init is ExtensionInstalled PF
	$wgHooks['LanguageGetMagic'][] = 'smwfAddIsExtensionInstalledMagic';

	global $smgJSLibs, $sfgFancyBoxIncluded;
	$smgJSLibs[] = 'prototype';
	$smgJSLibs[] = 'qtip';

	if ( !$sfgFancyBoxIncluded ) {
		// fancybox isn't already provided by SF
		$smgJSLibs[] = 'jquery';
		$smgJSLibs[] = 'fancybox';
		$sfgFancyBoxIncluded = true;
	}

	//initialize query management
	global $smwgHaloIP;
	require_once( "$smwgHaloIP/includes/QueryManagement/SMW_QM_QueryManagementHandler.php" );

	global $wgAutoloadClasses;
	$wgAutoloadClasses['SMWQueryCallMetadataValue'] =
		"$smwgHaloIP/includes/QueryManagement/SMW_QM_DV_QueryCallMetadata.php";

	$wgHooks['smwInitDatatypes'][] = 'SMWQMQueryManagementHandler::initQRCDataTypes';
	$wgHooks['smwInitProperties'][] = 'SMWQMQueryManagementHandler::initProperties';

}

/**
 * Intializes SMW Halo Extension.
 * Called from SMW during initialization.
 */
function smwgHaloSetupExtension() {
	global $smwgIP, $smwgHaloIP, $wgHooks, $smwgMasterGeneralStore, $wgFileExtensions, $wgJobClasses, $wgExtensionCredits;
	global $smwgHaloContLang, $wgAutoloadClasses, $wgSpecialPages, $wgAjaxExportList, $wgGroupPermissions;
	global $mediaWiki, $wgSpecialPageGroups;
	global $smwgWebserviceEndpoint, $smwgMessageBroker, $smwgDefaultStore;
	if (is_array($smwgWebserviceEndpoint) && count($smwgWebserviceEndpoint) > 1 && !isset($smwgMessageBroker)) {
		trigger_error("Multiple webservice endpoints require a messagebroker to handle triplestore updates.");
		die();
	}

	if (smwfIsTripleStoreConfigured() && !isset($smwgWebserviceEndpoint)) {
		trigger_error('$smwgWebserviceEndpoint is required but not set. Example: $smwgWebserviceEndpoint="localhost:8080";');
		die();
	}
	global $smwgWebserviceProtocol;
	$smwgWebserviceProtocol="rest";
	$smwgMasterGeneralStore = NULL;

	// Autoloading. Use it for everything! No include_once or require_once please!
	$wgAutoloadClasses['SMWQueryProcessor'] = $smwgHaloIP . '/includes/SMW_QueryProcessor.php';
	$wgAutoloadClasses['SMWHaloStore2'] = $smwgHaloIP . '/includes/storage/SMW_HaloStore2.php';
	$wgAutoloadClasses['SMWAdvRequestOptions'] = $smwgHaloIP . '/includes/SMW_AdvRequestOptions.php';

	$wgAutoloadClasses['TSConnection']            = $smwgHaloIP . '/includes/storage/SMW_TSConnection.php';
	$wgAutoloadClasses['TSNamespaces']            = $smwgHaloIP . '/includes/storage/SMW_TS_Helper.php';
	$wgAutoloadClasses['TSHelper']            = $smwgHaloIP . '/includes/storage/SMW_TS_Helper.php';
	$wgAutoloadClasses['WikiTypeToXSD']            = $smwgHaloIP . '/includes/storage/SMW_TS_Helper.php';
	$wgAutoloadClasses['SMWTripleStore']            = $smwgHaloIP . '/includes/storage/SMW_TripleStore.php';
	$wgAutoloadClasses['SMWTripleStoreQuad']            = $smwgHaloIP . '/includes/storage/SMW_TripleStoreQuad.php';
	$wgAutoloadClasses['SMWSPARQLQueryProcessor']            = $smwgHaloIP . '/includes/SMW_SPARQLQueryProcessor.php';
	$wgAutoloadClasses['SMWSPARQLQueryParser']            = $smwgHaloIP . '/includes/SMW_SPARQLQueryParser.php';
	$wgAutoloadClasses['SMWFullSemanticData']            = $smwgHaloIP . '/includes/SMW_FullSemanticData.php';
	$wgAutoloadClasses['SMWAggregationResultPrinter'] = $smwgHaloIP . '/includes/queryprinters/SMW_QP_Aggregation.php';
	$wgAutoloadClasses['SMWExcelResultPrinter'] = $smwgHaloIP . '/includes/queryprinters/SMW_QP_Excel.php';
	$wgAutoloadClasses['SMWSPARQLQuery'] = $smwgHaloIP . '/includes/SMW_SPARQLQueryParser.php';
	$wgAutoloadClasses['SMWChemicalFormulaTypeHandler'] = $smwgHaloIP . '/includes/SMW_DV_ChemFormula.php';
	$wgAutoloadClasses['SMWChemicalEquationTypeHandler'] = $smwgHaloIP . '/includes/SMW_DV_ChemEquation.php';
	$wgAutoloadClasses['SMWMathematicalEquationTypeHandler'] = $smwgHaloIP . '/includes/SMW_DV_MathEquation.php';
	$wgAutoloadClasses['SMWURIIntegrationValue'] = $smwgHaloIP . '/includes/storage/SMW_DV_IntegrationLink.php';
	$wgAutoloadClasses['SMWIsExtensionInstalledPF'] = $smwgHaloIP . '/includes/SMW_IsExtensionInstalledPF.php';
	$wgAutoloadClasses['SMWQMSpecialBrowse'] = $smwgHaloIP.'/specials/SearchTriple/SMW_QM_SpecialBrowse.php';
	$wgAutoloadClasses['LODNonExistingPage'] = $smwgHaloIP . '/includes/articlepages/LOD_NonExistingPage.php';
	$wgAutoloadClasses['LODNonExistingPageHandler'] = $smwgHaloIP . '/includes/articlepages/LOD_NonExistingPageHandler.php';
	$wgAutoloadClasses['SMWQueryList'] = $smwgHaloIP . '/specials/SMWQueryList/SMW_QueryList.php';
	$wgAutoloadClasses['SMWArticleBuiltinProperties'] = $smwgHaloIP . '/includes/SMW_ArticleBuiltinProperties.php';
	$wgAutoloadClasses['SMWPivotTableResultPrinter'] = $smwgHaloIP . '/includes/queryprinters/SMW_QP_PivotTable.php';
	

	//patch Special:Browse in order to hide special Query Management Property
	$wgSpecialPages['Browse']  = array( 'SMWQMSpecialBrowse' );

	require_once $smwgHaloIP.'/includes/queryprinters/SMW_QP_Halo.php';
	require_once $smwgHaloIP . '/includes/SMW_CreateNewArticle.php';

	global $smwgResultFormats;


	$smwgResultFormats['exceltable'] = 'SMWExcelResultPrinter';
	$smwgResultFormats['aggregation'] = 'SMWAggregationResultPrinter';
	$smwgResultFormats['csv'] = 'SMWHaloCsvResultPrinter';
	$smwgResultFormats['pivottable'] = 'SMWPivotTableResultPrinter';

	//Set up the IsExtensionInstalled PG
	$wgHooks['ParserFirstCallInit'][] = 'SMWIsExtensionInstalledPF::registerFunctions';

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
    $wgHooks['ArticleSaveComplete'][] = 'smwfSavesNamespaceMappings';
    
	global $smwgDefaultStore, $smwgShowDerivedFacts, $wgRequest;
	if ($smwgShowDerivedFacts === true) {
		$wgHooks['smwShowFactbox'][] = 'smwfAddDerivedFacts';
	}

	// Remove the existing smwfSaveHook and replace it with the
	// new and functionally enhanced smwfHaloSaveHook
	$wgHooks['ParserBeforeStrip'][] = 'smwfRegisterSPARQLInlineQueries';
	$wgHooks['InternalParseBeforeLinks'][] = 'smwfRegisterIntegrationLink';

	global $lodgNEPEnabled;
	if ($lodgNEPEnabled) {
		$wgHooks['ArticleFromTitle'][]      = 'LODNonExistingPageHandler::onArticleFromTitle';
		$wgHooks['EditFormPreloadText'][]   = 'LODNonExistingPageHandler::onEditFormPreloadText';

		global $lodgNEPGenericTemplate, $lodgNEPPropertyPageTemplate, $lodgNEPCategoryPageTemplate, $lodgNEPUseGenericTemplateIfCategoryMember, $lodgNEPCategoryTemplatePattern;
		####
		# string - Article name of the generic template for all non-existing pages but
		# properties and categories.
		$lodgNEPGenericTemplate = "MediaWiki:NEP/Generic";

		####
		# string - Article name of the template for property pages
		$lodgNEPPropertyPageTemplate = "MediaWiki:NEP/Property";

		####
		# string - Article name of the template for category pages
		$lodgNEPCategoryPageTemplate = "MediaWiki:NEP/Category";

		####
		# boolean - If <true>, the generic NEP template is used, even if the Linked Data
		# item has a type.
		$lodgNEPUseGenericTemplateIfCategoryMember = false;

		####
		# string - The Linked Data item can have several types which are mapped to wiki
		# categories. A template can be used for each category according to the template
		# pattern. The variable {cat} is replaced by the category that is associated with
		# a type.
		$lodgNEPCategoryTemplatePattern = "MediaWiki:NEP/Category/{cat}";
	}

	$wgHooks['SkinTemplateToolboxEnd'][] = 'smwfOntoSkinTemplateToolboxEnd';

	$wgHooks['sfSetTargetName'][]     		= 'smwfOnSfSetTargetName';


	global $wgRequest;

	// register AddHTMLHeader functions for special pages
	// to include javascript and css files (only on special page requests).
	$title = Title::newFromText($wgRequest->getVal('title'));
	if (!is_null($title) && $title->getNamespace() == NS_SPECIAL) {

		$wgHooks['BeforePageDisplay'][]='smwOBAddHTMLHeader';
		$wgHooks['BeforePageDisplay'][]='smwfQIAddHTMLHeader';
		$wgHooks['BeforePageDisplay'][]='smwPRAddHTMLHeader';

	}

	// Provide a Linked Data Interface based on the following URI schemata (@see http://www4.wiwiss.fu-berlin.de/bizer/pub/LinkedDataTutorial/):
	//
	// Resource URI: http://mywiki/resource/Prius
	// -> 303 forward to:
	// 		Information resource (HTML): http://mywiki/index.php/Prius
	// 		Information resource (RDF): http://mywiki/index.php/Prius?format=rdf
	//      Information resource (RDF): http://mywiki/index.php/Prius (when requested MIME type is application=rdf/xml)
	//
	// Requires a mod_rewrite configuration as follows:
	// 	RewriteEngine on
	// 	RewriteBase /HaloSMWExtension
	// 	RewriteRule ^resource/(.*) index.php?action=ldnegotiate&title=$1 [PT,L,QSA]

	// Perform content negotiation when invoked with action=ldnegotiate
	if (array_key_exists('action', $_REQUEST) && $_REQUEST['action'] == 'ldnegotiate' ) {
		global $smwgTripleStoreGraph;
		// title parameter contains the URI fragement: property/HasName, a/Prius, category/Automobile
		$uri = $smwgTripleStoreGraph."/".$wgRequest->getVal('title');
		$title = TSHelper::getTitleFromURI($uri);
		$location = $title->getLocalURL() . (array_key_exists('HTTP_ACCEPT', $_SERVER) && strpos($_SERVER['HTTP_ACCEPT'], 'application/rdf+xml') !== false ? "?format=rdf" : "");

		header("HTTP/1.1 303 See Other");
		header("Location: $location");
		header("Vary: Accept");
		exit; // stop any processing here
	}

	// Answer format=rdf queries using the external query interface
	if (array_key_exists('format', $_REQUEST) && $_REQUEST['format'] == 'rdf' ) {
		global $IP;
		require_once( $IP . '/extensions/SMWHalo/includes/webservices/SMW_EQI.php' );
		header ( "Content-Type: application/rdf+xml" );
		echo smwhRDFRequest($title->getPrefixedText());
		exit; // stop any processing here
	}

	// special handling: application/rdf+xml requests are redirected to
	// the external query interface
	if (array_key_exists('HTTP_ACCEPT', $_SERVER) && $_SERVER['HTTP_ACCEPT'] == 'application/rdf+xml') {
		global $IP;
		require_once( $IP . '/extensions/SMWHalo/includes/webservices/SMW_EQI.php' );
		header ( "Content-Type: application/rdf+xml" );
		echo smwhRDFRequest($title->getPrefixedText());
		exit; // stop any processing here
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
	// Allow annotating by default for all
	$wgGroupPermissions['*']['annotate'] = true;

	// autocompletion option registration
	$wgHooks['GetPreferences'][] = 'smwfAutoCompletionToggles';
	$wgHooks['UserSaveSettings'][] = 'smwfSetUserDefinedCookies';

	//parser function for multiple template annotations
	$wgHooks['ParserBeforeStrip'][] = 'smwfRegisterCommaAnnotation';

	// register AC icons
	$wgHooks['smwhACNamespaceMappings'][] = 'smwfRegisterAutocompletionIcons';
	
	// register hook for additional builtin properties
	$wgHooks['NewRevisionFromEditComplete'][] = 'SMWArticleBuiltinProperties::onNewRevisionFromEditComplete'; // fetch some MediaWiki data for replication in SMW's store
	

	// add triple store hooks if necessary
	global $smwgDefaultStore,$smwgIgnoreSchema;
	if (smwfIsTripleStoreConfigured()) {
		if (!isset($smwgIgnoreSchema) || $smwgIgnoreSchema === false) {
			require_once('storage/SMW_TS_SchemaContributor.php');
			$wgHooks['TripleStorePropertyUpdate'][] = 'smwfTripleStorePropertyUpdate';
		} else {
			require_once('storage/SMW_TS_SimpleContributor.php');
			$wgHooks['TripleStorePropertyUpdate'][] = 'smwfTripleStorePropertyUpdate';
		}
		$wgHooks['TripleStoreCategoryUpdate'][] = 'smwfTripleStoreCategoryUpdate';
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

			case '_ws_' :  smwfHaloInitMessages();
			require_once($smwgHaloIP . '/includes/SMW_WebInterfaces.php');
			break;

			case '_qc_' :  smwfHaloInitMessages();
			require_once($smwgHaloIP . '/includes/QueryResultsCache/SMW_QRC_AjaxAPI.php');
			break;

			case '_ts_' :
				smwfHaloInitMessages();
				break; // contained in this file

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


		$wgAutoloadClasses['SMWQueryInterface'] = $smwgHaloIP . '/specials/SMWQueryInterface/SMWQueryInterface.php';
		$wgSpecialPages['QueryInterface'] = array('SMWQueryInterface');
		$wgSpecialPageGroups['QueryInterface'] = 'smwplus_group';

		$wgSpecialPages['Properties'] = array('SpecialPage','Properties', '', true, 'smwfDoSpecialProperties', $smwgHaloIP . '/specials/SMWQuery/SMWAdvSpecialProperties.php');
		$wgSpecialPageGroups['Properties'] = 'smwplus_group';


		$wgAutoloadClasses['SMWTripleStoreAdmin'] = $smwgHaloIP . '/specials/SMWTripleStoreAdmin/SMW_TripleStoreAdmin.php';
		$wgSpecialPages['TSA'] = array('SMWTripleStoreAdmin');
		$wgSpecialPageGroups['TSA'] = 'smwplus_group';
		
		$wgAutoloadClasses['SMWHaloAdmin'] = $smwgHaloIP . '/specials/SMWHaloAdmin/SMW_HaloAdmin.php';
		$wgSpecialPages['SMWHaloAdmin'] = array('SMWHaloAdmin');
		$wgSpecialPageGroups['SMWHaloAdmin'] = 'smwplus_group';

		$wgSpecialPages['QueryList'] = array('SMWQueryList');
		$wgSpecialPageGroups['QueryList'] = 'smwplus_group';
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
	// new right for annotation mode
	global $wgAvailableRights;
	$wgAvailableRights[] = 'annotate';


	// Register Credits
	$wgExtensionCredits['semantic'][] = array(
		'name'=>'SMWHalo&nbsp;Extension', 
		'version'=>SMW_HALO_VERSION,
		'author'=>"Thomas&nbsp;Schweitzer, Kai&nbsp;K&uuml;hn, Markus&nbsp;Nitsche, J&ouml;rg Heizmann, Frederik&nbsp;Pfisterer, Robert Ulrich, Daniel Hansch, Moritz Weiten and Michael Erdmann. Owned by [http://www.ontoprise.de ontoprise GmbH].", 
		'url'=>'http://smwforum.ontoprise.com/smwforum/index.php/Help:Halo_Extension_User_Manual',
		'description' => 'Facilitate the use of Semantic Mediawiki for a large community of non-tech-savvy users. [http://smwforum.ontoprise.com/smwforum/index.php/Help:SMW%2B_User_Manual View feature description.]'
	);

	global $smwgDefaultStore;
	if (smwfIsTripleStoreConfigured()) {
		$wgHooks['InternalParseBeforeLinks'][] = 'smwfTripleStoreParserHook';
	}
	$wgAjaxExportList[] = 'smwf_ts_getSyncCommands';
	$wgAjaxExportList[] = 'smwf_ts_getWikiNamespaces';
	$wgAjaxExportList[] = 'smwf_ts_getWikiSpecialProperties';
	$wgAjaxExportList[] = 'smwf_ts_triggerAsynchronousLoading';

	// make hook for red links if $lodgNEPEnabled is disabled (see above)
    global $smwgRedLinkWithCreateNewPage;
    if ($smwgRedLinkWithCreateNewPage && !$lodgNEPEnabled)
        $wgHooks['LinkEnd'][] = 'smwfBrokenLinkForPage';

	// make hook for RichMedia
	$wgHooks['CheckNamespaceForImage'][] = 'smwfRichMediaIsImage';

	// add the 'halo' form input type, if Semantic Forms is installed
	if ( defined('SF_VERSION') ) {
		global $sfgFormPrinter;
		if (isset($sfgFormPrinter)) {
			$sfgFormPrinter->setInputTypeHook('haloACtext', 'smwfHaloFormInput', array());
			$sfgFormPrinter->setInputTypeHook('haloACtextarea', 'smwfHaloFormInputTextarea', array());
		}
	}

	//Initialize Tabular Forms
	require_once($smwgHaloIP.'/includes/TabularForms/TF_AjaxAccess.php');
	$wgAutoloadClasses['TFTabularFormQueryPrinter'] =
	$smwgHaloIP.'/includes/TabularForms/TF_QP_TabularForm.php';
	$wgAutoloadClasses['TFDataAPIAccess'] =
	$smwgHaloIP.'/includes/TabularForms/TF_DataAPIAccess.php';
	$wgAutoloadClasses['TFAnnotationData'] =
	$smwgHaloIP.'/includes/TabularForms/TF_DataAPIAccess.php';
	$wgAutoloadClasses['TFAnnotationData'] =
	$smwgHaloIP.'/includes/TabularForms/TF_DataAPIAccess.php';
	$wgAutoloadClasses['TFAnnotationDataCollection'] =
	$smwgHaloIP.'/includes/TabularForms/TF_DataAPIAccess.php';
	$wgAutoloadClasses['TFTemplateParameterCollection'] =
	$smwgHaloIP.'/includes/TabularForms/TF_DataAPIAccess.php';
	$wgAutoloadClasses['TFQueryAnalyser'] =
	$smwgHaloIP.'/includes/TabularForms/TF_QueryAnalyser.php';
	$smwgResultFormats['tabularform'] = 'TFTabularFormQueryPrinter';
	
	define('TF_IS_QC_CMP', 'qc_');
	define('TF_IS_EXISTS_CMP', 'plus_');
	define('TF_CATEGORY_KEYWORD', '__Category__');
	
	return true;
}

function smwfRegisterAutocompletionIcons(& $namespaceMappings) {

	$namespaceMappings[NS_CATEGORY]="/extensions/SMWHalo/skins/concept.gif";
	$namespaceMappings[SMW_NS_PROPERTY]="/extensions/SMWHalo/skins/property.gif";
	$namespaceMappings[NS_MAIN]= "/extensions/SMWHalo/skins/instance.gif";
	$namespaceMappings[NS_TEMPLATE]="/extensions/SMWHalo/skins/template.gif";
	$namespaceMappings[SMW_NS_TYPE]= "/extensions/SMWHalo/skins/type.gif";
	$namespaceMappings[NS_HELP]= "/extensions/SMWHalo/skins/help.gif";
	$namespaceMappings[NS_IMAGE]= "/extensions/SMWHalo/skins/image.gif";
	$namespaceMappings[NS_USER]= "/extensions/SMWHalo/skins/user.gif";

	// special value 500 for enums
	$namespaceMappings[500]= "/extensions/SMWHalo/skins/enum.gif";

	//XXX: this should not be defined here but in the SemanticForms extension
	if (defined('SF_NS_FORM')) $namespaceMappings[SF_NS_FORM]= "/extensions/SMWHalo/skins/form.gif";
	return true;
}

/**
 * Checks if the triplestore driver is configured.
 *
 * @return boolean
 */
function smwfIsTripleStoreConfigured() {
	global $smwgDefaultStore;
	return ($smwgDefaultStore == 'SMWTripleStore' || $smwgDefaultStore == 'SMWTripleStoreQuad');
}

function smwfRegisterSPARQLInlineQueries( &$parser, &$text, &$stripstate ) {

	$parser->setFunctionHook( 'sparql', 'smwfProcessSPARQLInlineQueryParserFunction');

	return true; // always return true, in order not to stop MW's hook processing!
}

function smwfRegisterIntegrationLink(&$parser, &$text, &$strip_state = null) {
	$ilinkPattern = '/&lt;ilink(.*?&gt;)(.*?.)&lt;\/ilink&gt;/ixus';
	preg_match_all($ilinkPattern, trim($text), $matches);

	// at least one parameter and content?
	for($i = 0; $i < count($matches[0]); $i++) {
		$header = trim($matches[1][$i]);
		$uri = trim($matches[2][$i]);


		// parse header parameters
		$parameterPattern = "/([^=]+)=\"([^\"]*)\"/ixus";
		preg_match_all($parameterPattern, $header, $matchesheader);

		$caption = '';
		for ($j = 0; $j < count($matchesheader[0]); $j++) {
			if (trim($matchesheader[1][$j]) == 'caption') {
				$caption = trim($matchesheader[2][$j]);
			}
		}
		$text = str_replace($matches[0][$i], '<a class="new" href="'.$uri.'">'.$caption.'</a>', $text);
			
	}
	return true;
}


/**
 * The {{#sparql }} parser function processing part.
 */
function smwfProcessSPARQLInlineQueryParserFunction(&$parser) {
	global $smwgDefaultStore;
	if (smwfIsTripleStoreConfigured()) {
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
	SMWDataValueFactory::registerDatatype('_chf', 'SMWChemicalFormulaTypeHandler',
	$smwgHaloContLang->getHaloDatatype('smw_hdt_chemical_formula'));
	SMWDataValueFactory::registerDatatype('_che', 'SMWChemicalEquationTypeHandler',
	$smwgHaloContLang->getHaloDatatype('smw_hdt_chemical_equation'));
	SMWDataValueFactory::registerDatatype('_meq', 'SMWMathematicalEquationTypeHandler',
	$smwgHaloContLang->getHaloDatatype('smw_hdt_mathematical_equation'));
	SMWDataValueFactory::registerDatatype('_ili', 'SMWURIIntegrationValue',
	$smwgHaloContLang->getHaloDatatype('smw_integration_link'));

	return true;
}

/**
 * Returns a list of SPARUL commands which are required to sync
 * with the TSC.
 *
 * @return string
 */
function smwf_ts_getSyncCommands() {
	global $smwgMessageBroker, $smwgTripleStoreGraph, $wgDBtype, $wgDBport,
	$wgDBserver, $wgDBname, $wgDBuser, $wgDBpassword, $wgDBprefix, $wgLanguageCode,
	$smwgBaseStore, $smwgIgnoreSchema, $smwgNamespaceIndex;

	$sparulCommands = array();

	// sync wiki module
	$sparulCommands[] = "DROP SILENT GRAPH <$smwgTripleStoreGraph>"; // drop may fail. don't worry
	$sparulCommands[] = "CREATE SILENT GRAPH <$smwgTripleStoreGraph>";
	$sparulCommands[] = "LOAD <smw://".urlencode($wgDBuser).":".urlencode($wgDBpassword).
	"@$wgDBserver:$wgDBport/$wgDBname?lang=$wgLanguageCode&smwstore=$smwgBaseStore".
	"&smwnsindex=$smwgNamespaceIndex#".urlencode($wgDBprefix).
	"> INTO <$smwgTripleStoreGraph>";

	// sync external modules (only if DF is installed)
	if (defined('DF_VERSION')) {

		$externalArtifacts = DFBundleTools::getExternalArtifacts();

		foreach($externalArtifacts as $extArt) {
			list($fileTitle, $uri) = $extArt;
			$sparulCommands[] = "DROP SILENT GRAPH <$uri>"; // drop may fail. don't worry
			$sparulCommands[] = "CREATE SILENT GRAPH <$uri>";

			$localFile = wfLocalFile($fileTitle);
			$format = DFBundleTools::guessOntologyFileType($fileTitle->getText());
			$fileURL = $localFile->getFullUrl();
			$sparulCommands[] = "LOAD <$fileURL?format=$format> INTO <$uri>";
			$sparulCommands[] = "IMPORT ONTOLOGY <$uri> INTO <$smwgTripleStoreGraph>";
		}


	}

	return implode("\n", $sparulCommands);
}



/**
 * Returns a list of namespace mappings.
 * Exported as ajax call.
 *
 * Need by TSC to get extra namespaces (besides the default of MW + SMW + SF) and required
 * to support other content languages than english.
 *
 * nsText(content language) => nsKey
 *
 * @return string
 */
function smwf_ts_getWikiNamespaces() {
	global $wgExtraNamespaces, $wgContLang;

	$allNS = array(NS_CATEGORY, SMW_NS_PROPERTY,SF_NS_FORM, SMW_NS_CONCEPT, NS_MAIN ,
	SMW_NS_TYPE,NS_FILE, NS_HELP, NS_TEMPLATE, NS_USER, NS_MEDIAWIKI, NS_PROJECT,	SMW_NS_PROPERTY_TALK,
	SF_NS_FORM_TALK,NS_TALK, NS_USER_TALK, NS_PROJECT_TALK, NS_FILE_TALK, NS_MEDIAWIKI_TALK,
	NS_TEMPLATE_TALK, NS_HELP_TALK, NS_CATEGORY_TALK, SMW_NS_CONCEPT_TALK, SMW_NS_TYPE_TALK);

	$extraNamespaces = array_diff(array_keys($wgExtraNamespaces), $allNS);
	$allNS = array_merge($allNS, $extraNamespaces);
	$result = "";
	$first = true;
	foreach($allNS as $nsKey) {

		$nsText = $wgContLang->getNSText($nsKey);
		if (empty($nsText) && $nsKey !== NS_MAIN) continue;
		$result .= (!$first ? "," : "").$nsText."=".$nsKey;
		$first = false;
	}
	return $result;
}

/**
 * Maps language constants of special properties/categories to content language.
 * Exported as ajax call.
 *
 * Need by TSC.
 *
 * Language constant representing a special property/category = Name in wiki's content language
 *
 * @return string
 */
function smwf_ts_getWikiSpecialProperties() {

	global $wgContLang, $smwgHaloContLang, $smwgContLang;
	$specialProperties = $smwgHaloContLang->getSpecialSchemaPropertyArray();
	$specialCategories = $smwgHaloContLang->getSpecialCategoryArray();
	$specialPropertiesSMW = $smwgContLang->getPropertyLabels();

	$result = "HAS_DOMAIN_AND_RANGE=".$specialProperties[SMW_SSP_HAS_DOMAIN_AND_RANGE_HINT].",".
				"HAS_MIN_CARDINALITY=".$specialProperties[SMW_SSP_HAS_MIN_CARD].",".
				"HAS_MAX_CARDINALITY=".$specialProperties[SMW_SSP_HAS_MAX_CARD].",".
				"IS_INVERSE_OF=".$specialProperties[SMW_SSP_IS_INVERSE_OF].",".
				"TRANSITIVE_PROPERTIES=".$specialCategories[SMW_SC_TRANSITIVE_RELATIONS].",".
				"SYMETRICAL_PROPERTIES=".$specialCategories[SMW_SC_SYMMETRICAL_RELATIONS].",".
				"CORRESPONDS_TO=".$specialPropertiesSMW['_CONV'].",".
				"HAS_TYPE=".$specialPropertiesSMW['_TYPE'].",".
				"HAS_FIELDS=".$specialPropertiesSMW['_LIST'].",".
				"MODIFICATION_DATE=".$specialPropertiesSMW['_MDAT'].",".
				"EQUIVALENT_URI=".$specialPropertiesSMW['_URI'].",".
				"DISPLAY_UNITS=".$specialPropertiesSMW['_UNIT'].",".
				"IMPORTED_FROM=".$specialPropertiesSMW['_IMPO'].",".
				"PROVIDES_SERVICE=".$specialPropertiesSMW['_SERV'].",".
				"ALLOWS_VALUE=".$specialPropertiesSMW['_PVAL'].",".
				"HAS_IMPROPER_VALUE_FOR=".$specialPropertiesSMW['_ERRP'].",";

	// these two namespaces are required for ASK queries
	$result .= "CATEGORY=".$wgContLang->getNSText(NS_CATEGORY).",";
	$result .= "CONCEPT=".$wgContLang->getNSText(SMW_NS_CONCEPT);

	return $result;
}

/**
 * Trigger asynchronous loading operations. Usually called when TSC comes up.
 *
 * @return AjaxRespone object containing JSON encoded data.
 */
function smwf_ts_triggerAsynchronousLoading() {
	global $smwgTripleStoreGraph;
	$result = array();
	$result['components'] = array();
	$result['errors'] = array();
	wfRunHooks("SMWHalo_AsynchronousLoading", array ($smwgTripleStoreGraph, & $result));

	$json = json_encode($result);
	$response = new AjaxResponse($json);
	$response->setContentType( "application/json" );
	return $response;
}
/**
 * function for parser hook in Semantic Forms
 *
 * @param string $cur_value
 * @param string $input_name
 * @param string $is_mandatory
 * @param boolean $is_disabled
 * @param array $other_args
 * @param string $method
 * @return array(string, null)
 */
function smwfHaloFormInput($cur_value, $input_name, $is_mandatory, $is_disabled, $other_args, $method = "textEntryHTML") {
	// for semantic autocompletion set class="wickEnabled" - this is neccessary
	if (array_key_exists('class', $other_args))
	$other_args['class'].= ' wickEnabled';
	else
	$other_args['class'] = 'wickEnabled';
	// we do not use the autocomplete feature of SF, if set ignore it by removing
	if (array_key_exists('autocompletion source', $other_args))
	unset($other_args['autocompletion source']);
	// this will be a normal textfield, possible values that turn the input field
	// into a drop down list must be ignored.
	if (array_key_exists('possible_values', $other_args))
	unset($other_args['possible_values']);
	// create the two field constraint and typeHint, also pipes had to be
	// as ; in the parser function params constraints and typeHint parameter
	$constraints = '';

	if (array_key_exists('constraints', $other_args))
	$constraints = 'constraints="'.str_replace(';', '|', $other_args['constraints']).'" ';

	// pasteNS attribute prints out namespaces too
	$pasteNS = 'pasteNS="true"';
	if (array_key_exists('pasteNS', $other_args) && $other_args['pasteNS'] == 'false') $pasteNS = '';

	// replace 'current user' by username
	if ($cur_value == 'current user') {
		global $wgUser;
		$cur_value = !is_null($wgUser) ? $wgUser->getName() : "anonymous";
	}
	// call now the general function of SF that creates the <input> field
	//	$html = SFFormInput::$method($cur_value, $input_name, $is_mandatory, $is_disabled, $other_args);
	if($method == 'textEntryHTML') {
		$html = SFTextInput::getHTML($cur_value, $input_name, $is_mandatory, $is_disabled, $other_args);
	} else {
		$html = SFTextAreaInput::getHTML($cur_value, $input_name, $is_mandatory, $is_disabled, $other_args);
	}

	// add the constraints in the result output html. Either in input field or a textarea
	for($i = 0; $i < count($html); $i++) {
		if (strpos($html[$i], "/>") !== false) {
			$html[$i] = str_replace('/>', " $constraints $pasteNS/>", $html[$i]);
		} else {
			$html[$i] = preg_replace('/(<textarea\s+[^>]*)(>.*)/','$1 '." $constraints $pasteNS ".' $2', $html[$i]);
		}
	}

	return $html;
}

function smwfHaloFormInputTextarea($cur_value, $input_name, $is_mandatory, $is_disabled, $other_args) {
	return smwfHaloFormInput($cur_value, $input_name, $is_mandatory, $is_disabled, $other_args, "textAreaHTML");
}

/**
 * Registers special pages for some namespaces
 */
function smwfHaloShowListPage(&$title, &$article){
	global $smwgHaloIP;
	if ( $title->getNamespace() == NS_CATEGORY ) {
		require_once($smwgHaloIP . '/includes/articlepages/SMW_CategoryPage.php');
		$article = new SMWCategoryPage($title);
	} elseif ( $title->getNamespace() == SMW_NS_PROPERTY ) {
		global $smwgPropertyPageFromTSC;
		if (!isset($smwgPropertyPageFromTSC) || $smwgPropertyPageFromTSC === false) return true;
		require_once($smwgHaloIP . '/includes/articlepages/SMW_TS_PropertyPage.php');
		$article = new SMWTSPropertyPage($title);
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
 * Creates a new instance of the base store and gives other extensions the
 * a chance to modify the store.
 *
 * @return
 * 		A new base store or NULL if $smwgBaseStore is not defined.
 */
function &smwfNewBaseStore() {
	global $smwgBaseStore;
	if (!isset($smwgBaseStore)) {
		return NULL;
	}
	$store = new $smwgBaseStore();
	wfRunHooks('SmwhNewBaseStore', array(&$store));

	return $store;
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
	global $wgOut;

	global $wgRequest;
	$allowStbOnSubmit = true;
	$action = $wgRequest->getText('action');
	$title = $wgRequest->getText('title');

	if (!empty($action) && !empty($title)) {
		$allowStbOnSubmit = ($action == 'submit');
		$title = Title::newFromText($title);
		if ($allowStbOnSubmit && $title->getNamespace() == NS_SPECIAL) {
			// Don't use the STB when special pages are submitted
			$allowStbOnSubmit = false;
		}
	}

	if (smwfIsTripleStoreConfigured()) {
		global $smwgTripleStoreGraph;
		$wgOut->addScript('<script type="text/javascript">var smwghTripleStoreGraph="'.$smwgTripleStoreGraph.'"</script>');
	}

	$skin = $wgUser->getSkin();
	$skinName = $wgUser !== NULL ? $wgUser->getSkin()->getSkinName() : $wgDefaultSkin;
	$jsm = SMWResourceManager::SINGLETON();
	$jsm->addCSSIf($smwgHaloScriptPath . '/skins/smwhalo.css');
	$jsm->addCSSIf($smwgHaloScriptPath . '/skins/Autocompletion/wick.css');

	$jsm->addCSSIf($smwgHaloScriptPath . '/skins/semantictoolbar.css', "edit");
	$jsm->addCSSIf($smwgHaloScriptPath . '/skins/semantictoolbar.css', "annotate");
	$jsm->addCSSIf($smwgHaloScriptPath . '/skins/semantictoolbar.css', "formedit");
	if ($allowStbOnSubmit) $jsm->addCSSIf($smwgHaloScriptPath . '/skins/semantictoolbar.css', "submit");
	$jsm->addCSSIf($smwgHaloScriptPath . '/skins/semantictoolbar.css', "all", NS_SPECIAL, array(NS_SPECIAL.':AddData', NS_SPECIAL.':EditData', NS_SPECIAL.':FormEdit'));

	$jsm->addCSSIf($wgStylePath .'/'.$skin->getSkinName().'/lightbulb.css');

	$jsm->addCSSIf($smwgHaloScriptPath . '/skins/Annotation/annotation.css', "annotate");
	$jsm->addCSSIf($smwgHaloScriptPath . '/skins/Annotation/annotation.css', "edit");
	$jsm->addCSSIf($smwgHaloScriptPath . '/skins/Annotation/annotation.css', "formedit");
	$jsm->addCSSIf($smwgHaloScriptPath . '/skins/Annotation/annotation.css', "submit");
	$jsm->addCSSIf($smwgHaloScriptPath . '/skins/Annotation/annotation.css', "all", NS_SPECIAL, array(NS_SPECIAL.':AddData', NS_SPECIAL.':EditData', NS_SPECIAL.':FormEdit'));

	$jsm->addCSSIf($smwgHaloScriptPath . '/skins/derivedFactsTab.css');
	//create new article css
	$jsm->addCSSIf($smwgHaloScriptPath . '/skins/CreateNewArticle/createNewArticle.css');

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
	//XXX: don't use deploy version for the query interface since there are issue with MS ExcelBridge
	global $wgRequest,$wgContLang;
	$pagetitle = $wgRequest->getVal("title");
	$spec_ns = $wgContLang->getNsText(NS_SPECIAL);
	$isQIF = ($pagetitle == "$spec_ns:QueryInterface");
	// end of hack

	if (!isset($smwgDeployVersion) || $smwgDeployVersion === false || $isQIF) {

		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/ajaxhalo.js');
		//$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/prototype.js');
		//$jsm->setScriptID($smwgHaloScriptPath .  '/scripts/prototype.js', 'Prototype_script_inclusion');

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

		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/GeneralGUI/breadcrumb.js');
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/GeneralGUI/contentSlider.js');
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/GeneralGUI/generalGUI.js');

		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/Language/SMW_Language.js');

		smwfHaloAddJSLanguageScripts($jsm);

		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/GeneralGUI/STB_Framework.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/GeneralGUI/STB_Framework.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/GeneralGUI/STB_Framework.js', "formedit");
		if ($allowStbOnSubmit) $jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/GeneralGUI/STB_Framework.js', "submit");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/GeneralGUI/STB_Framework.js', "all", NS_SPECIAL, array(NS_SPECIAL.':AddData', NS_SPECIAL.':EditData', NS_SPECIAL.':FormEdit'));



		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/GeneralGUI/STB_Divcontainer.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/GeneralGUI/STB_Divcontainer.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/GeneralGUI/STB_Divcontainer.js', "formedit");
		if ($allowStbOnSubmit) $jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/GeneralGUI/STB_Divcontainer.js', "submit");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/GeneralGUI/STB_Divcontainer.js', "all", NS_SPECIAL, array(NS_SPECIAL.':AddData', NS_SPECIAL.':EditData', NS_SPECIAL.':FormEdit'));



		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/Autocompletion/wick.js');

		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Links.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Links.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Links.js', "formedit");
		if ($allowStbOnSubmit) $jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Links.js', "submit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Links.js', "all", NS_SPECIAL, array(NS_SPECIAL.':AddData', NS_SPECIAL.':EditData', NS_SPECIAL.':FormEdit'));
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/WikiTextParser/Annotation.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/WikiTextParser/Annotation.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/WikiTextParser/Annotation.js', "formedit");
		if ($allowStbOnSubmit) $jsm->addScriptIf($smwgHaloScriptPath . '/scripts/WikiTextParser/Annotation.js', "submit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/WikiTextParser/Annotation.js', "all", NS_SPECIAL, array(NS_SPECIAL.':AddData', NS_SPECIAL.':EditData', NS_SPECIAL.':FormEdit'));
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/WikiTextParser/WikiTextParser.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/WikiTextParser/WikiTextParser.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/WikiTextParser/WikiTextParser.js', "formedit");
		if ($allowStbOnSubmit) $jsm->addScriptIf($smwgHaloScriptPath . '/scripts/WikiTextParser/WikiTextParser.js', "submit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/WikiTextParser/WikiTextParser.js', "all", NS_SPECIAL, array(NS_SPECIAL.':AddData', NS_SPECIAL.':EditData', NS_SPECIAL.':FormEdit'));
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Ontology.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Ontology.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Ontology.js', "formedit");
		if ($allowStbOnSubmit) $jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Ontology.js', "submit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Ontology.js', "all", NS_SPECIAL, array(NS_SPECIAL.':AddData', NS_SPECIAL.':EditData', NS_SPECIAL.':FormEdit'));
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_DataTypes.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_DataTypes.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_DataTypes.js', "formedit");
		if ($allowStbOnSubmit) $jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_DataTypes.js', "submit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_DataTypes.js', "all", NS_SPECIAL, array(NS_SPECIAL.':AddData', NS_SPECIAL.':EditData', NS_SPECIAL.':FormEdit'));
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_GenericToolbarFunctions.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_GenericToolbarFunctions.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_GenericToolbarFunctions.js', "formedit");
		if ($allowStbOnSubmit) $jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_GenericToolbarFunctions.js', "submit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_GenericToolbarFunctions.js', "all", NS_SPECIAL, array(NS_SPECIAL.':AddData', NS_SPECIAL.':EditData', NS_SPECIAL.':FormEdit'));
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Container.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Container.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Container.js', "formedit");
		if ($allowStbOnSubmit) $jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Container.js', "submit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Container.js', "all", NS_SPECIAL, array(NS_SPECIAL.':AddData', NS_SPECIAL.':EditData', NS_SPECIAL.':FormEdit'));
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Marker.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Marker.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Marker.js', "formedit");
		if ($allowStbOnSubmit) $jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Marker.js', "submit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Marker.js', "all", NS_SPECIAL, array(NS_SPECIAL.':AddData', NS_SPECIAL.':EditData', NS_SPECIAL.':FormEdit'));
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Category.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Category.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Category.js', "formedit");
		if ($allowStbOnSubmit) $jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Category.js', "submit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Category.js', "all", NS_SPECIAL, array(NS_SPECIAL.':AddData', NS_SPECIAL.':EditData', NS_SPECIAL.':FormEdit'));
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_ASKQuery.js', "all", NS_SPECIAL, array(NS_SPECIAL.':AddData', NS_SPECIAL.':EditData', NS_SPECIAL.':FormEdit'));
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_ASKQuery.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_ASKQuery.js', "formedit");
		if ($allowStbOnSubmit) $jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_ASKQuery.js', "submit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/AdvancedAnnotation/SMW_AnnotationHints.js', "all", NS_SPECIAL, array(NS_SPECIAL.':AddData', NS_SPECIAL.':EditData', NS_SPECIAL.':FormEdit'));
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/AdvancedAnnotation/SMW_AnnotationHints.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/AdvancedAnnotation/SMW_AnnotationHints.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/AdvancedAnnotation/SMW_AnnotationHints.js', "formedit");
		if ($allowStbOnSubmit) $jsm->addScriptIf($smwgHaloScriptPath . '/scripts/AdvancedAnnotation/SMW_AnnotationHints.js', "submit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/AdvancedAnnotation/SMW_GardeningHints.js', "all", NS_SPECIAL, array(NS_SPECIAL.':AddData', NS_SPECIAL.':EditData', NS_SPECIAL.':FormEdit'));
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/AdvancedAnnotation/SMW_GardeningHints.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/AdvancedAnnotation/SMW_GardeningHints.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/AdvancedAnnotation/SMW_GardeningHints.js', "formedit");
		if ($allowStbOnSubmit) $jsm->addScriptIf($smwgHaloScriptPath . '/scripts/AdvancedAnnotation/SMW_GardeningHints.js', "submit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/AdvancedAnnotation/SMW_GardeningHints.js', "all", NS_SPECIAL, array(NS_SPECIAL.':AddData', NS_SPECIAL.':EditData', NS_SPECIAL.':FormEdit'));
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/AdvancedAnnotation/SMW_SaveAnnotations.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Relation.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Relation.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Relation.js', "formedit");
		if ($allowStbOnSubmit) $jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Relation.js', "submit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Relation.js', "all", NS_SPECIAL, array(NS_SPECIAL.':AddData', NS_SPECIAL.':EditData', NS_SPECIAL.':FormEdit'));
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Properties.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Properties.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Properties.js', "formedit");
		if ($allowStbOnSubmit) $jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Properties.js', "submit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Properties.js', "all", NS_SPECIAL, array(NS_SPECIAL.':AddData', NS_SPECIAL.':EditData', NS_SPECIAL.':FormEdit'));

		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Refresh.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Refresh.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Refresh.js', "formedit");
		if ($allowStbOnSubmit) $jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Refresh.js', "submit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Refresh.js', "all", NS_SPECIAL, array(NS_SPECIAL.':AddData', NS_SPECIAL.':EditData', NS_SPECIAL.':FormEdit'));
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_DragAndResize.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_DragAndResize.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_DragAndResize.js', "formedit");
		if ($allowStbOnSubmit) $jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_DragAndResize.js', "submit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_DragAndResize.js', "all", NS_SPECIAL, array(NS_SPECIAL.':AddData', NS_SPECIAL.':EditData', NS_SPECIAL.':FormEdit'));
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_ContextMenu.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_ContextMenu.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_ContextMenu.js', "formedit");
		if ($allowStbOnSubmit) $jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_ContextMenu.js', "submit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_ContextMenu.js', "all", NS_SPECIAL, array(NS_SPECIAL.':AddData', NS_SPECIAL.':EditData', NS_SPECIAL.':FormEdit'));

		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMWEditInterface.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMWEditInterface.js', "formedit");
		if ($allowStbOnSubmit) $jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMWEditInterface.js', "submit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMWEditInterface.js', "all", NS_SPECIAL, array(NS_SPECIAL.':AddData', NS_SPECIAL.':EditData', NS_SPECIAL.':FormEdit'));
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/OntologyBrowser/obSemToolContribution.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/OntologyBrowser/obSemToolContribution.js', "formedit");
		if ($allowStbOnSubmit) $jsm->addScriptIf($smwgHaloScriptPath . '/scripts/OntologyBrowser/obSemToolContribution.js', "submit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/OntologyBrowser/obSemToolContribution.js', "all", NS_SPECIAL, array(NS_SPECIAL.':AddData', NS_SPECIAL.':EditData', NS_SPECIAL.':FormEdit'));

		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/AdvancedAnnotation/SMW_AdvancedAnnotation.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/AdvancedAnnotation/SMW_AdvancedAnnotation.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/AdvancedAnnotation/SMW_AdvancedAnnotation.js', "formedit");
		if ($allowStbOnSubmit) $jsm->addScriptIf($smwgHaloScriptPath . '/scripts/AdvancedAnnotation/SMW_AdvancedAnnotation.js', "submit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/AdvancedAnnotation/SMW_AdvancedAnnotation.js', "all", NS_SPECIAL, array(NS_SPECIAL.':AddData', NS_SPECIAL.':EditData', NS_SPECIAL.':FormEdit'));

		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/SMW_DerivedFactsTab.js');

	} else {
		//$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/prototype.js');
		//$jsm->setScriptID($smwgHaloScriptPath .  '/scripts/prototype.js', 'Prototype_script_inclusion');

		smwfHaloAddJSLanguageScripts($jsm);
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/deployGeneralTools.js');
		if ($wgRequest->getText('action') != 'submit' || $allowStbOnSubmit) {
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/GeneralGUI/STB_Framework.js');
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/GeneralGUI/STB_Divcontainer.js');
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMWEditInterface.js');
			$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/OntologyBrowser/obSemToolContribution.js');
		}
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/deployGeneralScripts.js');


	}
	//create new article scripts
	$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/CreateNewArticle/createNewArticle.js');
	$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/CreateNewArticle/jquery.query-2.1.7.js');


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
		foreach ($wikipagesToUpdate as $dv) {
			$title = $dv->getTitle();
			if ($title !== NULL) $jobs[] = new SMW_UpdatePropertiesAfterMoveJob($title, $params);
		}
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
	global $wgUser, $wgTitle,  $wgRequest;
	global $wgTitle;

	$allowed = $wgUser->isAllowed('annotate');
	if ($allowed) {
		// Other extensions may prohibit the annotate action
		wfRunHooks('userCan', array(&$wgTitle, &$wgUser, "annotate", &$allowed));
	}
	if (!$allowed) {
		return true;
	}
	if ($wgTitle->getNamespace() == NS_SPECIAL) return true; // Special page
	//Check if edit tab is present, if not don't at annote tab
	//if (!array_key_exists('edit',$content_actions) )
	//return true;
	$action = $wgRequest->getText( 'action' );
	//Build annotate tab
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
		//$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/prototype.js', "all", -1, NS_SPECIAL.":OntologyBrowser");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/scriptaculous/effects.js', "all", -1, NS_SPECIAL.":OntologyBrowser");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/scriptaculous/dragdrop.js', "all", -1, NS_SPECIAL.":OntologyBrowser");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/OntologyBrowser/generalTools.js', "all", -1, NS_SPECIAL.":OntologyBrowser");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/Language/SMW_Language.js', "all", -1, NS_SPECIAL.":OntologyBrowser");

		smwfHaloAddJSLanguageScripts($jsm, "all", -1, NS_SPECIAL.":OntologyBrowser");

		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/OntologyBrowser/ontologytools.js', "all", -1, NS_SPECIAL.":OntologyBrowser");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/OntologyBrowser/treeview.js', "all", -1, NS_SPECIAL.":OntologyBrowser");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/OntologyBrowser/treeviewActions.js', "all", -1, NS_SPECIAL.":OntologyBrowser");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/OntologyBrowser/treeviewData.js', "all", -1, NS_SPECIAL.":OntologyBrowser");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/OntologyBrowser/advancedOptions.js', "all", -1, NS_SPECIAL.":OntologyBrowser");
		$jsm->addScriptIf($smwgScriptPath .  '/skins/SMW_tooltip.js', "all", -1, NS_SPECIAL.":OntologyBrowser");
	} else {
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/ajaxhalo.js');
		//$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/prototype.js', "all", -1, NS_SPECIAL.":OntologyBrowser");
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
		//$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/prototype.js', "all", -1, NS_SPECIAL.":Properties");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/deployGeneralTools.js', "all", -1, NS_SPECIAL.":Properties");

	}

	$jsm->addCSSIf($smwgScriptPath . '/skins/SMW_custom.css', "all", -1, NS_SPECIAL.":Properties");

	return true;
}



// QueryInterface scripts callback
// includes necessary script and css files.
function smwfQIAddHTMLHeader(&$out){
	global $wgTitle;
	if ($wgTitle->getNamespace() != NS_SPECIAL) return true;

	global $smwgHaloScriptPath, $smwgDeployVersion, $smwgScriptPath, $srfgScriptPath;


	$jsm = SMWResourceManager::SINGLETON();

	if (!isset($smwgDeployVersion) || $smwgDeployVersion === false) {
		//$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/prototype.js', "all", -1, NS_SPECIAL.":QueryInterface");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/Language/SMW_Language.js', "all", -1, NS_SPECIAL.":QueryInterface");
		smwfHaloAddJSLanguageScripts($jsm, "all", -1, NS_SPECIAL.":QueryInterface");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/Logger/smw_logger.js', "all", -1, NS_SPECIAL.":QueryInterface");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/OntologyBrowser/generalTools.js', "all", -1, NS_SPECIAL.":QueryInterface");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/QueryInterface/Query.js', "all", -1, NS_SPECIAL.":QueryInterface");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/QueryInterface/QueryList.js', "all", -1, NS_SPECIAL.":QueryInterface");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/QueryInterface/QIHelper.js', "all", -1, NS_SPECIAL.":QueryInterface");
		$jsm->addScriptIf($smwgScriptPath .  '/skins/SMW_tooltip.js', "all", -1, NS_SPECIAL.":QueryInterface");

	} else {

		//$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/prototype.js', "all", -1, NS_SPECIAL.":QueryInterface");
		smwfHaloAddJSLanguageScripts($jsm, "all", -1, NS_SPECIAL.":QueryInterface");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/deployGeneralTools.js', "all", -1, NS_SPECIAL.":QueryInterface");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/QueryInterface/deployQueryInterface.js', "all", -1, NS_SPECIAL.":QueryInterface");


	}

	// add scripts required by query printers
	$canonicalName = SpecialPage::resolveAlias( $wgTitle->getDBkey() );
	if ($canonicalName == 'QueryInterface') {

		global $smwgResultFormats, $wgOut;
		if (isset($smwgResultFormats)) {
			$resultFormatsUnique = array_unique($smwgResultFormats);

			foreach($resultFormatsUnique as $format => $formatclass) {

				try {
					$rc = new ReflectionClass($formatclass);
					if ($rc->hasMethod("getScripts")) {
						$qp = new $formatclass($format, false);
						$scriptsToLoad = $qp->getScripts();
						foreach($scriptsToLoad as $script) $wgOut->addScript($script);
					}
					if ($rc->hasMethod("getStylesheets")) {
						$qp = new $formatclass($format, false);
						$styleSheetsToLoad = $qp->getStylesheets();
						foreach($styleSheetsToLoad as $css) $wgOut->addLink($css);
					}
				} catch(ReflectionException $e) {
					// igore
				}
			}
		}
	}
	$jsm->addCSSIf($smwgScriptPath .  '/skins/SMW_custom.css', "all", -1, NS_SPECIAL.":QueryInterface");
	$jsm->addCSSIf($smwgHaloScriptPath . '/skins/QueryInterface/qi.css', "all", -1, NS_SPECIAL.":QueryInterface");

	return true; // do not load other scripts or CSS
}




function smwfGetAjaxMethodPrefix() {
	$func_name = isset( $_POST["rs"] ) ? $_POST["rs"] : (isset( $_GET["rs"] ) ? $_GET["rs"] : NULL);
	if ($func_name == NULL) return NULL;
	return substr($func_name, 4, 4); // return _xx_ of smwf_xx_methodname, may return FALSE
}

/**
 * Register extra AC related options in Preferences->Misc
 */
function smwfAutoCompletionToggles( $user, &$preferences ) {
	// A checkbox
	$preferences['smwhactriggering'] = array(
        'type' => 'toggle',
        'label-message' => 'tog-autotriggering', // a system message
        'section' => 'personal/info'
    
        );

        // Required return value of a hook function.
        return true;
}


function smwfSetUserDefinedCookies($user) {
	global $wgScriptPath;

	$autoTriggering = $user->getOption( "smwhactriggering", false ) == 1 ? "smwhactriggering=manual" : "smwhactriggering=auto";
	$namespaceMappings = array();
	wfRunHooks('smwhACNamespaceMappings', array (&$namespaceMappings));
	$serializedMappings = "";
	$first = true;
	foreach($namespaceMappings as $nsIndex => $imgPath) {
		$serializedMappings .= ",$nsIndex=$imgPath";
	}
	setcookie("AC_options", $autoTriggering.$serializedMappings, 0, "$wgScriptPath/"); // cookie gets invalid at session-end.
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
	$annoValues = explode(',', $params[1]);
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
	$magicWords['ilink']  = array( 0, 'ilink' );
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
		if($wgUser->getSkin() == 'ontoskin2'){
			$result .= '<tr><td><div class="smwf_naviitem"><a href="'.Skin::makeUrl($page_title, $query).'" '.$extraAttributes[$name].'>'.$name.'</a></div></td></tr>';
		} else {
			$result .= '<li><a href="'.Skin::makeUrl($page_title, $query).'" '.$extraAttributes[$name].'>'.$name.'</a></li>';
		}
	}
	return $result;
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

		case MAG_CURRENTUSERNS:
			$parser->disableCache(); # Mark this content as uncacheable
			$ret = $GLOBALS['wgContLang']->getNsText(NS_USER).":".$GLOBALS['wgUser']->mName;
			break;
		case MAG_CURRENTUSER:
			$parser->disableCache(); # Mark this content as uncacheable
			$ret = $GLOBALS['wgUser']->mName;
			break;
		case MAG_NOW:
			$parser->disableCache(); # Mark this content as uncacheable
			$ret = date("Y-m-d\\TH:i:s");
			break;
		case MAG_TODAY:
			$parser->disableCache(); # Mark this content as uncacheable
			$ret = date("Y-m-d")."T00:00:00";
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
	global $smwgIP, $smwgTripleStoreGraph;
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

	// fallback pattern with canonical language
	$categoryLinkPattern2 = '/\[\[\s*                   # Beginning of the link
                            category\s*:      # category link (case insensitive!)
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

	if (strtolower($categoryText) !== 'category') {
		preg_match_all($categoryLinkPattern2, $text, $matches);
		if (isset($matches[1])) {
			foreach($matches[1] as $m) {
				$labelIndex = strpos($m, '|');
				$m = $labelIndex !== false ? substr($m, 0, $labelIndex) : $m;
				$categories[] = Title::newFromText(trim($m), NS_CATEGORY);
			}
		}
	}

	global $magicWords;
	list($i, $redirectText) = $magicWords['redirect'];
	$redirectText = substr($redirectText, 1); // cut off hash #

	// parse redirects
	$redirectLinkPattern = '/\#'.$redirectText.' # REDIRECT command
                            \[\[                # Beginning of the link
                            ([^]]+)               # target
                            \]\]                # End of link
                            /ixu';              # case-insensitive, ignore whitespaces, UTF-8 compatible

	// fallback pattern with canonical language
	$redirectLinkPattern2 = '/\#REDIRECT          # REDIRECT command
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
	if (strtolower($redirectText) !== 'redirect') {
		preg_match_all($redirectLinkPattern2, $text, $matches);
		if (isset($matches[1])) {
			foreach($matches[1] as $m) {
				$redirects[] = Title::newFromText($m);
			}
		}
	}

	SMWTripleStore::$fullSemanticData->setCategories($categories);

	SMWTripleStore::$fullSemanticData->setRedirects($redirects);
	return true;
}

/**
 * Callback function for the hook 'smwShowFactbox'. It is called when SMW creates
 * the factbox for an article.
 * This method replaces the whole factbox with a tabbed version that contains
 * the original factbox in one tab and the derived facts in another.
 *
 * @param string $text
 * 		The HTML for the tabbed factbox is returned in this parameter
 * @param SMWSemanticData $semdata
 * 		All static facts for the article
 * @return bool
 * 		<false> : This means that SMW's factbox is completely replaced.
 */
function smwfAddDerivedFacts(& $text, $semdata) {
	global $smwgHaloScriptPath, $wgContLang;

	wfLoadExtensionMessages('SemanticMediaWiki');
	SMWOutputs::requireHeadItem(SMW_HEADER_STYLE);
	$rdflink = SMWInfolink::newInternalLink(wfMsgForContent('smw_viewasrdf'), $wgContLang->getNsText(NS_SPECIAL) . ':ExportRDF/' . $semdata->getSubject()->getWikiValue(), 'rdflink');

	$browselink = SMWInfolink::newBrowsingLink($semdata->getSubject()->getText(), $semdata->getSubject()->getWikiValue(), 'swmfactboxheadbrowse');
	$fbText = '<div class="smwfact">' .
						'<span class="smwfactboxhead">' . wfMsgForContent('smw_factbox_head', $browselink->getWikiText() ) . '</span>' .
					'<span class="smwrdflink">' . $rdflink->getWikiText() . '</span>' .
					'<table class="smwfacttable">' . "\n";
	foreach($semdata->getProperties() as $property) {
		if (!$property->isShown()) { // showing this is not desired, hide
			continue;
		} elseif ($property->isUserDefined()) { // user defined property
			$property->setCaption(preg_replace('/[ ]/u','&nbsp;',$property->getWikiValue(),2));
			/// NOTE: the preg_replace is a slight hack to ensure that the left column does not get too narrow
			$fbText .= '<tr><td class="smwpropname">' . $property->getLongWikiText(true) . '</td><td class="smwprops">';
		} elseif ($property->isVisible()) { // predefined property
			$fbText .= '<tr><td class="smwspecname">' . $property->getLongWikiText(true) . '</td><td class="smwspecs">';
		} else { // predefined, internal property
			continue;
		}

		$propvalues = $semdata->getPropertyValues($property);
		$l = count($propvalues);
		$i=0;
		foreach ($propvalues as $propvalue) {
			if ($i!=0) {
				if ($i>$l-2) {
					$fbText .= wfMsgForContent('smw_finallistconjunct') . ' ';
				} else {
					$fbText .= ', ';
				}
			}
			$i+=1;
			$fbText .= $propvalue->getLongWikiText(true) . $propvalue->getInfolinkText(SMW_OUTPUT_WIKI);
		}
		$fbText .= '</td></tr>';
	}
	$fbText .= '</table></div>';


	$text =
'<div id="smw_dft_rendered_boxcontent"> <br />'.
	'<table>'.
		'<tr>'.
			'<td id="dftTab1" class="dftTabActive">'.
	str_replace(' ', '&nbsp;', wfMsg('smw_df_static_tab')).
			'</td>'.
			'<td class="dftTabSpacer">&nbsp;</td>'.
			'<td id="dftTab2" class="dftTabInactive">'.
	str_replace(' ', '&nbsp;', wfMsg('smw_df_derived_tab')).
			'</td>'.
			'<td class="dftTabSpacer" width="100%"></td>'.
		'</tr>'.
		'<tr>'.
			'<td colspan="4" class="dftTabCont">'.
				'<div id="dftTab1Content" >'.
	$fbText.
				'</div>'.
				'<div id="dftTab2Content" style="display:none">'.
					'<div id="dftTab2ContentInnerDiv">'.wfMsg('smw_df_loading_df').'</div>'.
				'</div>'.
			'</td>'.
		'</tr>'.
	'</table>'.
'</div>';

	return false;
}

/**
 * If a redlink is returned, change the links to use the Create_new_page in case this one exists
 * and if the page to create is within the main namespace
 * This function is a parser hook for "BrokenLink" and works with Mediawiki 1.13 and higher
 */
function smwfBrokenLinkForPage( $skin, $target, $options, &$text, &$attribs, &$ret ) {

	// check if page Create_new_page exists in the wiki, if not quit here
	if (!Title::newFromDBkey('Create_new_page')->exists())
	return true;
	// check if this is an unmodified red link, if not, quit here
	if (!(isset($attribs['href']) &&
	strpos($attribs['href'], 'action=edit&redlink=1') !== false))
	return true;
	// get the namespace of the new page, if it's not NS_MAIN, quit
	if ( NS_MAIN != $target->getNamespace())
	return true;
	// build title string for new page and create link to Create_new_page with target param
	$title_text = ucfirst($target->getText());
	global $wgScript;
	$attribs['href'] = $wgScript.'?title=Create_new_page&target='.urlencode($title_text);
	return true;
}
/**
 * Returns a randomly webservice endpoint.
 */
function smwfgetWebserviceEndpoint($endpoints) {
	if (!is_array($endpoints)) return $endpoints;
	return $endpoints[mt_rand(0, count($endpoints)-1)];
}
/**
 * Is the namespace one of the (new) image-namespaces?
 * created for AdditionalMIMETypes
 *
 * @param int $index
 * @return bool
 */

function smwfRichMediaIsImage( &$index, &$rMresult ) {
	$rMresult |= ($index == NS_IMAGE);
	return true;
}

/*
 * Call this method in LocalSettings in order to enable the Query Results Cache
 */
function enableQueryResultsCache(){
	global $smwgHaloIP, $smwgQRCEnabled, $wgHooks;
	require_once( "$smwgHaloIP/includes/QueryResultsCache/SMW_QRC_QueryResultsCache.php" );
	require_once( "$smwgHaloIP/includes/QueryResultsCache/SMW_QRC_AjaxAPI.php" );

	$smwgQRCEnabled = true;

	$wgHooks['smwInitializeTables'][] = 'smwfQRCInitializeTables';
}

/**
 * Checks if the given property is predefined by SMWHalo
 * @param SMWPropertyValue $property
 */
function smwfCheckIfPredefinedSMWHaloProperty(SMWPropertyValue $property) {
	if (smwfGetSemanticStore()->domainRangeHintRelation->getDBkey() == $property->getDBkey()
	|| smwfGetSemanticStore()->minCard->getDBkey() == $property->getDBkey()
	|| smwfGetSemanticStore()->maxCard->getDBkey() == $property->getDBkey()
	|| smwfGetSemanticStore()->inverseOf->getDBkey() == $property->getDBkey()) {
		return true;
	}
	return false;
}

/*
 * Set up the Query Results Cache Tables
 */
function smwfQRCInitializeTables(){
	global $smwgHaloIP;
	require_once( "$smwgHaloIP/includes/QueryResultsCache/SMW_QRC_Store.php" );
	SMWQRCStore::getInstance()->getDB()->initDatabaseTables();

	return true;
}

/**
 * This function is called from the hook 'sfSetTargetName' in SemanticForms. It adds a
 * JavaScript line that initializes the following variables that correspond to
 * the current title:
 * smwhgSfTargetTitle - the title of the article that is edited with SF (without namespace)
 * smwhgSfTargetPageName - the full title of the article that is edited with SF with namespace
 * smwhgSfTargetNamespace - the namespace ID of the article that is edited with SF
 *
 * @param string $titleName
 * 	Name of the article that is edited with Semantic Forms
 *
 */
function smwfOnSfSetTargetName($titleName) {
	global $wgOut, $wgJsMimeType;
	if (!empty($titleName)) {
		$t = Title::newFromText($titleName);
		$ttext = $t->getText();
		$tfulltext = $t->getFullText();
		$namespace = $t->getNamespace();
		$script = "<script type= \"$wgJsMimeType\">/*<![CDATA[*/\n";
		$script .= "smwhgSfTargetTitle = '$ttext';\n";
		$script .= "smwhgSfTargetPageName = '$tfulltext';\n";
		$script .= "smwhgSfTargetNamespace = $namespace;\n";
		$script .= "\n/*]]>*/</script>\n";
			
		$wgOut->addScript($script);
	}
	return true;
}


/*
 * initialize magic word for
 * isExtensionInstalled parser function
 */
function smwfAddIsExtensionInstalledMagic(&$magicWords, $langCode = "en"){
	$magicWords['isExtensionInstalled']	= array ( 0, 'isExtensionInstalled' );
	return true;
}

function smwfSavesNamespaceMappings(&$article, &$user, $text, $summary,
 $minoredit, $watchthis, $sectionanchor, &$flags, $revision, &$status, $baseRevId) {
    if (!defined('DF_VERSION')) return true;
    global $dfgLang;
    if ($article->getTitle()->getText() == $dfgLang->getLanguageString('df_namespace_mappings_page')
        && $article->getTitle()->getNamespace() == NS_MEDIAWIKI) {
        $namespaceMappings = DFBundleTools::parseRegisteredPrefixes($text);
        smwfGetSemanticStore()->clearNamespaceMappings();
        foreach($namespaceMappings as $prefix => $uri) {
            smwfGetSemanticStore()->addNamespaceMapping($prefix, $uri);
        }
    }
    return true;
}