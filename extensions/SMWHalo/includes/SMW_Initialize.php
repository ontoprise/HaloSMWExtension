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
define('SMW_SSP_HAS_DOMAIN', 7);
define('SMW_SSP_HAS_RANGE', 8);

// constants for special categories
define('SMW_SC_TRANSITIVE_RELATIONS', 0);
define('SMW_SC_SYMMETRICAL_RELATIONS', 1);

// default cardinalities
define('CARDINALITY_MIN',0);
define('CARDINALITY_UNLIMITED', 2147483647); // MAXINT

// max depth of category graph
define('SMW_MAX_CATEGORY_GRAPH_DEPTH', 10);
define( 'MAG_LINEFEED', 'mycustomlinefeed' );
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

// include SMWTSC (which is actually a real separate extension)
require_once("$smwgHaloIP/smwtsc/SMWTSC.php");
/**
 * Configures SMW Halo Extension for initialization.
 * Must be called *AFTER* SMW is intialized.
 *
 * @param String $store SMWHaloStore (old) or SMWHaloStore2 (new). Uses old by default.
 */
function enableSMWHalo() {
	global $wgExtensionFunctions, $smwgOWLFullExport,
	$smwgSemanticDataClass, $wgHooks, $smwgHaloTripleStoreGraph, $smwgIgnoreSchema;

	global $smwghConvertColoumns;
	if (!isset($smwghConvertColoumns)) $smwghConvertColoumns="utf8";

	// Register the triple store as source for a query with the alias "tsc"


	$smwgIgnoreSchema = !isset($smwgIgnoreSchema) ? true : $smwgIgnoreSchema;

	$wgExtensionFunctions[] = 'smwgHaloSetupExtension';
	$smwgOWLFullExport = true;

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

	global $smwgQRCEnabled;
	if (isset($smwgQRCEnabled) && $smwgQRCEnabled === true) {
		global $wgAutoloadClasses;
		$wgAutoloadClasses['SMWQueryCallMetadataValue'] =
		"$smwgHaloIP/includes/QueryManagement/SMW_QM_DV_QueryCallMetadata.php";
		$wgAutoloadClasses['SMWQMStore'] =
        "$smwgHaloIP/includes/QueryManagement/SMW_QM_Store.php";

		$wgHooks['smwInitDatatypes'][] = 'SMWQMQueryManagementHandler::initQRCDataTypes';
		$wgHooks['smwInitProperties'][] = 'SMWQMQueryManagementHandler::initProperties';

		smwfAddStore('SMWQMStore');
	}
    
	// declare a magic word (LINEFEED)
	$wgHooks['LanguageGetMagic'][] = 'smwfHaloWikiWords';
	$wgHooks['ParserGetVariableValueSwitch'][] = 'smwfHaloAssignAValue';
	$wgHooks['MagicWordwgVariableIDs'][] = 'smwfHaloDeclareVarIds';
}

/**
 * Intializes SMW Halo Extension.
 * Called from SMW during initialization.
 */
function smwgHaloSetupExtension() {
	global $smwgIP, $smwgHaloIP, $wgHooks, $smwgMasterGeneralStore, $wgFileExtensions, $wgJobClasses, $wgExtensionCredits;
	global $smwgHaloContLang, $wgAutoloadClasses, $wgSpecialPages, $wgAjaxExportList, $wgGroupPermissions;
	global $mediaWiki, $wgSpecialPageGroups;
	global $smwgHaloWebserviceEndpoint, $smwgMessageBroker;


	// check if dependant extensions are installed
	if (!defined('DF_VERSION')) {
		$msg = "Deployment framework is not installed.";
		trigger_error($msg);
	}

	if (!defined('SCM_VERSION')) {
		$msg = "ScriptManager is not installed.";
		trigger_error($msg);
	}

	if (!defined('SMW_VERSION')) {
		$msg = 'SMW is not installed.';
		trigger_error($msg);
	}

	if (!defined('ARCLIB_ARCLIBRARY_VERSION')) {
		$msg = 'ArcLibrary is not installed.';
		trigger_error($msg);
	}




	$smwgMasterGeneralStore = NULL;

	// Autoloading. Use it for everything! No include_once or require_once please!

	$wgAutoloadClasses['SMWAdvRequestOptions'] = $smwgHaloIP . '/includes/SMW_AdvRequestOptions.php';
	$wgAutoloadClasses['SMWAggregationResultPrinter'] = $smwgHaloIP . '/includes/queryprinters/SMW_QP_Aggregation.php';
	$wgAutoloadClasses['SMWFancyTableResultPrinter'] = $smwgHaloIP . '/includes/queryprinters/SMW_QP_FancyTable.php';
	$wgAutoloadClasses['SMWExcelResultPrinter'] = $smwgHaloIP . '/includes/queryprinters/SMW_QP_Excel.php';
	$wgAutoloadClasses['SMWIsExtensionInstalledPF'] = $smwgHaloIP . '/includes/SMW_IsExtensionInstalledPF.php';
	$wgAutoloadClasses['SMWQMSpecialBrowse'] = $smwgHaloIP.'/specials/SearchTriple/SMW_QM_SpecialBrowse.php';
	$wgAutoloadClasses['SMWQueryList'] = $smwgHaloIP . '/specials/SMWQueryList/SMW_QueryList.php';
	$wgAutoloadClasses['SMWArticleBuiltinProperties'] = $smwgHaloIP . '/includes/SMW_ArticleBuiltinProperties.php';
	$wgAutoloadClasses['SMWPredefinitions'] = $smwgHaloIP . '/includes/SMW_Predefinitions.php';
	$wgAutoloadClasses['SMWHaloPredefinedPages'] = $smwgHaloIP . '/includes/SMW_Predefinitions.php';


	//patch Special:Browse in order to hide special Query Management Property
	$wgSpecialPages['Browse']  = array( 'SMWQMSpecialBrowse' );

	require_once $smwgHaloIP.'/includes/queryprinters/SMW_QP_Halo.php';


	global $smwgResultFormats;


	$smwgResultFormats['exceltable'] = 'SMWExcelResultPrinter';
	$smwgResultFormats['aggregation'] = 'SMWAggregationResultPrinter';
	$smwgResultFormats['csv'] = 'SMWHaloCsvResultPrinter';
	$smwgResultFormats['fancytable'] = 'SMWFancyTableResultPrinter';


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
	$wgHooks['smwInitProperties'][] = 'smwfInitSpecialPropertyOfSMWHalo';

	$wgHooks['ArticleSaveComplete'][] = 'smwfSavesNamespaceMappings';
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

			case '_na_' :   //create new article feature.
				smwfHaloInitMessages();
				require_once $smwgHaloIP . '/includes/SMW_CreateNewArticle.php';
				require_once($smwgHaloIP . '/includes/SMW_OntologyManipulator.php');
				break;		//we have to make sure SMW_Autocomplete.php is not included for this ajax call

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



		$wgAutoloadClasses['SMWHaloAdmin'] = $smwgHaloIP . '/specials/SMWHaloAdmin/SMW_HaloAdmin.php';
		$wgSpecialPages['SMWHaloAdmin'] = array('SMWHaloAdmin');
		$wgSpecialPageGroups['SMWHaloAdmin'] = 'smwplus_group';

		$wgSpecialPages['QueryList'] = array('SMWQueryList');
		$wgSpecialPageGroups['QueryList'] = 'smwplus_group';
	}


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





		// make hook for red links if $smwgHaloNEPEnabled is disabled (see above)
		global $smwgHaloRedLinkWithCreateNewPage;
		if ($smwgHaloRedLinkWithCreateNewPage && !$smwgHaloNEPEnabled)
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

		global $wgResourceModules, $smwgHaloIP;
		$commonProperties = array(
			'localBasePath' => $smwgHaloIP,
			'remoteExtPath' => 'SMWHalo'
			);

			$wgResourceModules['ext.tabularforms.main'] =
			$commonProperties +
			array(
				'scripts' => array('scripts/TabularForms/tabularforms.js'),
				'styles' => array('skins/TabularForms/tabularforms.css'),
			);

			define('TF_IS_QC_CMP', 'qc_');
			define('TF_IS_EXISTS_CMP', 'plus_');
			define('TF_CATEGORY_KEYWORD', '__Category__');

			// Check if qi is called via an curl call and if a token is set
			if (!is_null($title) && $title->getText() == 'QueryInterface') {
				global $smwgHaloQueryInterfaceSecret;
				if (isset($smwgHaloQueryInterfaceSecret)) {
					global $wgRequest;
					$token = $wgRequest->getText('s');
					$hash = $wgRequest->getText('t');
					require_once $smwgHaloIP.'/specials/SMWQueryInterface/SMW_QIAjaxAccess.php';
					if (!empty ($token) && !empty($hash) && qiCheckHash( $token, $hash)) {
						global $wgWhitelistRead;
						$wgWhitelistRead[]= MWNamespace::getCanonicalName(-1).':QueryInterface';
					}
				}
			}

			$wgHooks['ResourceLoaderRegisterModules'][]='smwhfRegisterResourceLoaderModules';

			// initialize static members of SMWHaloPredefinedPages
			new SMWHaloPredefinedPages();

				


			return true;
}

function smwfHaloWikiWords( &$magicWords, $langCode ) {
	
	$magicWords[MAG_LINEFEED] = array( 0, 'Linefeed' );

	// must do this or you will silence every LanguageGetMagic hook after this!
	return true;
}

function smwfHaloAssignAValue( &$parser, &$cache, &$magicWordId, &$ret ) {
	if ( MAG_LINEFEED == $magicWordId ) {
		// We found a value, return a linefeed
		$ret = "\n";
	}
	return true;
}

function smwfHaloDeclareVarIds( &$customVariableIds ) {
	// $customVariableIds is where MediaWiki wants to store its list of custom
	// variable IDs. We oblige by adding ours:
	$customVariableIds[] = MAG_LINEFEED;

	// must do this or you will silence every MagicWordwgVariableIds hook
	// registered after this!
	return true;
}

function smwfRegisterAutocompletionIcons(& $namespaceMappings) {

	$namespaceMappings[NS_CATEGORY]="/extensions/SMWHalo/skins/concept.gif";
	$namespaceMappings[SMW_NS_PROPERTY]="/extensions/SMWHalo/skins/property.gif";
	$namespaceMappings[NS_MAIN]= "/extensions/SMWHalo/skins/instance.gif";
	$namespaceMappings[NS_TEMPLATE]="/extensions/SMWHalo/skins/template.gif";
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
	global $smwgHaloWebserviceEndpoint;
	return isset($smwgHaloWebserviceEndpoint);
}




/**
 * The {{#sparql }} parser function processing part.
 */
function smwfProcessSPARQLInlineQueryParserFunction(&$parser) {

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
	// as of SF 2.1.2 the method name has changed from getHTML to getText
	$sfMethodName = (method_exists('SFTextInput', 'getHTML')) ? 'getHTML' : 'getText';
	if($method == 'textEntryHTML') {
		$html = SFTextInput::$sfMethodName($cur_value, $input_name, $is_mandatory, $is_disabled, $other_args);
	} else {
		$html = SFTextAreaInput::$sfMethodName($cur_value, $input_name, $is_mandatory, $is_disabled, $other_args);
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
	if ( $title->exists() && $title->getNamespace() == NS_CATEGORY ) {
		require_once($smwgHaloIP . '/includes/articlepages/SMW_CategoryPage.php');
		$article = new SMWCategoryPage($title);
	} elseif ( $title->exists() && $title->getNamespace() == SMW_NS_PROPERTY ) {
		global $smwgHaloPropertyPageFromTSC;
		if (!isset($smwgHaloPropertyPageFromTSC) || $smwgHaloPropertyPageFromTSC === false) return true;
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
	global $smwgMasterGeneralStore, $smwgHaloIP;
	if ($smwgMasterGeneralStore == NULL) {
		require_once($smwgHaloIP . '/includes/SMW_SemanticStoreSQL2.php');
		$smwgMasterGeneralStore = new SMWSemanticStoreSQL2();
	}
	return $smwgMasterGeneralStore;
}




/**
 * Checks if a database function is available (considers only UDF functions).
 */
function smwfDBSupportsFunction($lib) {
	global $smwgHaloUseEditDistance;
	return isset($smwgHaloUseEditDistance) ? $smwgHaloUseEditDistance : false;

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
		global $smwgHaloTripleStoreGraph;
		$wgOut->addScript('<script type="text/javascript">var smwghTripleStoreGraph="'.$smwgHaloTripleStoreGraph.'"</script>');
	}

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

	// Load modules
	$wgOut->addModules('ext.smwhalo.general');
	$wgOut->addModules('ext.smwhalo.styles');
	$wgOut->addModules('ext.smwhalo.createNewArticle');

	switch ($action) {
		case 'edit':
			$wgOut->addModules('ext.smwhalo.edit');
			break;
		case 'annotate':
			$wgOut->addModules('ext.smwhalo.annotate');
			break;
		case 'formedit':
			$wgOut->addModules('ext.smwhalo.formedit');
			break;
		case 'submit':
			if ($allowStbOnSubmit) {
				$wgOut->addModules('ext.smwhalo.submit');
			}
			break;
	}

	// Load modules for special pages
	if ($pagetitle == "$spec_ns:AddData"
	|| $pagetitle == "$spec_ns:EditData"
	|| $pagetitle == "$spec_ns:FormEdit") {
		$wgOut->addModules('ext.smwhalo.sfSpecialPages');
	}

	// for additinal scripts which are dependant of Halo scripts (e.g. ACL extension)
	wfRunHooks("SMW_AddScripts", array (& $out));

	return true; // always return true, in order not to stop MW's hook processing!
}

/**
 * Add appropriate JS language script
 */
function smwfHaloAddJSLanguageScripts() {
	global $smwgHaloIP, $wgUser, $wgResourceModules;

	// content language file
	$clngScript = '/scripts/Language/SMW_LanguageEn.js';
	$lng = '/scripts/Language/SMW_Language';
	if (isset($wgUser)) {
		$lng .= ucfirst($wgUser->getOption('language')).'.js';
		if (file_exists($smwgHaloIP . $lng)) {
			$clngScript = $lng;
		}
	}

	// user language file
	$ulngScript = '/scripts/Language/SMW_LanguageUserEn.js';
	$lng = '/scripts/Language/SMW_LanguageUser';
	if (isset($wgUser)) {
		$lng .= ucfirst($wgUser->getOption('language')).'.js';
		if (file_exists($smwgHaloIP . $lng)) {
			$ulngScript = $lng;
		}
	}
	$wgResourceModules['ext.smwhalo.Language'] = array(
	// JavaScript and CSS styles. To combine multiple file, just list them as an array.
		'scripts' => array(
			"scripts/Language/SMW_Language.js",
	$clngScript,
	$ulngScript
	),

	// ResourceLoader needs to know where your files are; specify your
	// subdir relative to "/extensions" (or $wgExtensionAssetsPath)
		'localBasePath' => dirname(__FILE__).'/../',
		'remoteExtPath' => 'SMWHalo'
		);

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
	global $wgTitle, $wgOut, $wgContLang;
	if ($wgTitle->getNamespace() != NS_SPECIAL) return true;

	$spec_ns = $wgContLang->getNsText(NS_SPECIAL);

	if ($wgTitle->getFullText() == "$spec_ns:OntologyBrowser") {
		$wgOut->addModules('ext.smwhalo.ontologyBrowser');
	}

	return true;
}

function smwPRAddHTMLHeader(&$out) {
	global $wgTitle, $wgOut, $wgContLang;
	if ($wgTitle->getNamespace() != NS_SPECIAL) return true;

	$spec_ns = $wgContLang->getNsText(NS_SPECIAL);

	if ($wgTitle->getFullText() == "$spec_ns:Properties") {
		$wgOut->addModules(array('ext.smw.tooltips', 'ext.smw.style'));
	}

	return true;
}



// QueryInterface scripts callback
// includes necessary script and css files.
function smwfQIAddHTMLHeader(&$out){
	global $wgTitle, $wgOut, $wgContLang;
	if ($wgTitle->getNamespace() != NS_SPECIAL) return true;

	$spec_ns = $wgContLang->getNsText(NS_SPECIAL);
	if ($wgTitle->getFullText() == "$spec_ns:QueryInterface") {
		$wgOut->addModules(array('ext.smwhalo.queryInterface'));
	}

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
 * @param mixed SMWDIProperty/Title $property
 */
function smwfCheckIfPredefinedSMWHaloProperty($property) {
	if ($property instanceof SMWDIProperty) {
		$key = $property->getKey();
	} else {
		$key = $property->getDBkey();
	}
	$result = (SMWHaloPredefinedPages::$HAS_DOMAIN_AND_RANGE->getDBkey() == $key
	|| SMWHaloPredefinedPages::$HAS_MIN_CARDINALITY->getDBkey() == $key
	|| SMWHaloPredefinedPages::$HAS_MAX_CARDINALITY == $key
	|| SMWHaloPredefinedPages::$IS_INVERSE_OF->getDBkey() == $key);

	return $result;
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

/**
 * This function defines all modules for the resource loader.
 */
function smwhfRegisterResourceLoaderModules() {
	global $wgResourceModules, $smwgHaloIP, $smwgHaloScriptPath, $wgUser;

	$moduleTemplate = array(
		'localBasePath' => $smwgHaloIP,
		'remoteBasePath' => $smwgHaloScriptPath,
		'group' => 'ext.smwhalo'
		);

		// Scripts and styles for all actions
		$wgResourceModules['ext.smwhalo.general'] = $moduleTemplate + array(
		'scripts' => array(
				'scripts/initPrototype.js',
				'scripts/ajaxhalo.js',
				'scripts/scriptaculous/effects.js',
				'scripts/scriptaculous/slider.js',
				'scripts/scriptaculous/dragdrop.js',
				'scripts/scriptaculous/scriptaculous.binding.js',
				'scripts/Logger/smw_logger.js',
				'scripts/OntologyBrowser/generalTools.js',
				'scripts/GeneralGUI/breadcrumb.js',
				'scripts/GeneralGUI/contentSlider.js',
				'scripts/GeneralGUI/generalGUI.js',
				'scripts/Autocompletion/wick.js'
				
				),
		'styles' => array(
				'/skins/smwhalo.css',
				'/skins/Autocompletion/wick.css',
				'/skins/derivedFactsTab.css'
				),
		'dependencies' => array(
				'ext.smwhalo.Language',
				)

				);

				// Scripts and styles for the create new article feature
				$wgResourceModules['ext.smwhalo.createNewArticle'] = $moduleTemplate + array(
		'scripts' => array(
			'scripts/CreateNewArticle/createNewArticle.js',
			'scripts/CreateNewArticle/jquery.query-2.1.7.js'
			),
		'styles' => array(
			'/skins/CreateNewArticle/createNewArticle.css'
			)
			);


			global $IP,$wgStylePath, $wgDefaultSkin;
			$skinName = $wgUser !== NULL
			? $wgUser->getSkin()->getSkinName()
			: $wgDefaultSkin;
			// Scripts and styles for all actions
			$wgResourceModules['ext.smwhalo.styles'] = array(
		'styles' => array(
				"/$skinName/lightbulb.css",
			),
		'localBasePath' => $IP."/skins",
		'group' => 'ext.smwhalo'
		);

		// Scripts and styles for edit action
		$wgResourceModules['ext.smwhalo.edit'] = $moduleTemplate + array(
		'scripts' => array(
		),
		'dependencies' => array(
			'ext.smwhalo.semanticToolbar',
			'ext.smwhalo.allButAnnotate'
			)
			);

			// Scripts and styles for annotate action
			$wgResourceModules['ext.smwhalo.annotate'] = $moduleTemplate + array(
		'scripts' => array(
				'scripts/initPrototype.js',
				'scripts/AdvancedAnnotation/SMW_SaveAnnotations.js'
				),
		'styles' => array(
				),
		'dependencies' => array(
			'ext.smwhalo.semanticToolbar'
			)
			);

			// Scripts and styles for formedit action
			$wgResourceModules['ext.smwhalo.formedit'] = $moduleTemplate + array(
		'scripts' => array(
			),
		'styles' => array(
			),
		'dependencies' => array(
			'ext.smwhalo.semanticToolbar',
			'ext.smwhalo.allButAnnotate'
			)
			);

			// Scripts and styles for submit action
			$wgResourceModules['ext.smwhalo.submit'] = $moduleTemplate + array(
		'scripts' => array(
			),
		'styles' => array(
			),
		'dependencies' => array(
			'ext.smwhalo.semanticToolbar',
			'ext.smwhalo.allButAnnotate'
			)
			);

			// Scripts and styles for semantic forms special pages
			// Special:AddData, Special:EditData, Special:FormEdit
			$wgResourceModules['ext.smwhalo.sfSpecialPages'] = $moduleTemplate + array(
		'scripts' => array(
			),
		'styles' => array(
			),
		'dependencies' => array(
			'ext.smwhalo.semanticToolbar',
			'ext.smwhalo.allButAnnotate'
			)
			);

			// Scripts and styles for all modes but annotate
			$wgResourceModules['ext.smwhalo.allButAnnotate'] = $moduleTemplate + array(
		'scripts' => array(
			'scripts/initPrototype.js',
			'scripts/SemanticToolbar/SMW_ASKQuery.js',
			'scripts/SemanticToolbar/SMWEditInterface.js',
			'scripts/OntologyBrowser/obSemToolContribution.js'
			),
		'styles' => array(
			),
		'dependencies' => array(
			'ext.smwhalo.semanticToolbar'
			)
			);

			// Scripts and styles for the semantic toolbar
			$wgResourceModules['ext.smwhalo.semanticToolbar'] = $moduleTemplate + array(
		'scripts' => array(
			'scripts/initPrototype.js',
			'scripts/GeneralGUI/STB_Framework.js',
			'scripts/GeneralGUI/STB_Divcontainer.js',
			'scripts/SemanticToolbar/SMW_Links.js',
			'scripts/WikiTextParser/Annotation.js',
			'scripts/WikiTextParser/WikiTextParser.js',
			'scripts/SemanticToolbar/SMW_Ontology.js',
			'scripts/SemanticToolbar/SMW_DataTypes.js',
			'scripts/SemanticToolbar/SMW_GenericToolbarFunctions.js',
			'scripts/SemanticToolbar/SMW_Container.js',
			'scripts/SemanticToolbar/SMW_Marker.js',
			'scripts/SemanticToolbar/SMW_Category.js',                        
			'scripts/AdvancedAnnotation/SMW_AnnotationHints.js',
			'scripts/AdvancedAnnotation/SMW_GardeningHints.js',
			'scripts/SemanticToolbar/SMW_Relation.js',
			'scripts/SemanticToolbar/SMW_Properties.js',
			'scripts/SemanticToolbar/SMW_Refresh.js',
			'scripts/SemanticToolbar/SMW_DragAndResize.js',
			'scripts/SemanticToolbar/SMW_ContextMenu.js',
			'scripts/AdvancedAnnotation/SMW_AdvancedAnnotation.js'
			),
		'styles' => array(
				'/skins/semantictoolbar.css',
				'/skins/Annotation/annotation.css'
				)
				);

				// Module for the Ontology Browser
				$wgResourceModules['ext.smwhalo.ontologyBrowser'] = $moduleTemplate + array(
		'scripts' => array(
			'scripts/initPrototype.js',
			'scripts/ajaxhalo.js',
			'scripts/scriptaculous/effects.js',
			'scripts/scriptaculous/dragdrop.js',
			'scripts/OntologyBrowser/generalTools.js',
			'scripts/Language/SMW_Language.js',
			'/scripts/OntologyBrowser/ontologytools.js',
			'/scripts/OntologyBrowser/treeview.js',
			'/scripts/OntologyBrowser/treeviewActions.js',
			'/scripts/OntologyBrowser/treeviewData.js',
			'/scripts/OntologyBrowser/advancedOptions.js',
				),
		'styles' => array(
			'/skins/OntologyBrowser/treeview.css'
			),
		'dependencies' => array(
			'ext.smw.tooltips',
			'ext.smw.style',
			'ext.jquery.qtip'
			)
			);

			// Module for the Query Interface
			// The QI depends on all SemanticResultFormats
			$dependencies = array(
			'ext.smw.tooltips',
			'ext.smw.style'
			);
			// Add all modules ext.srf.*
			foreach ($wgResourceModules as $rid => $mod) {
				if (strpos($rid, 'ext.srf.') === 0) {
					$dependencies[] = $rid;
				}
			}

			$wgResourceModules['ext.smwhalo.queryInterface'] = $moduleTemplate + array(
		'scripts' => array(
			'scripts/initPrototype.js',
			'scripts/Language/SMW_Language.js',
			'scripts/Logger/smw_logger.js',
			'scripts/OntologyBrowser/generalTools.js',
			'scripts/QueryInterface/Query.js',
			'scripts/QueryInterface/QueryList.js',
			'scripts/QueryInterface/QIHelper.js',
       'scripts/QueryInterface/qi_tooltip.js',
      'scripts/QueryInterface/window.binding.js'
			),
		'styles' => array(
			'skins/QueryInterface/qi.css'
			),
		'dependencies' => $dependencies
			);


			$wgResourceModules['ext.smwhalo.queryList'] = $moduleTemplate + array(
                    'scripts' => array('scripts/QueryList/querylist.js'),
                    'dependencies' => array('ext.smw.sorttable')
			);

			smwfHaloAddJSLanguageScripts();

			return true;
}

/**
 * Adds a new SMWStore implementation and wraps it around the existing.
 *
 * @param string $store_class Classname of new SMWStore.
 *         The class must have a constructor which takes exactly a SMWStore argument.
 *         This is the child store at which everything should be delegated which is
 *         not handled by the new store itself.
 *
 * @return SMWStore The current store (also retrieved by smwfGetStore() )
 */
function smwfAddStore($store_class) {
	global $smwgMasterStore;
	$oldStore = smwfGetStore();

	$qmStorePresent = false;
	if ($oldStore instanceof HACLSMWStore) {
		$qmStorePresent = true;
		$oldStore = $oldStore->getStore();
	}
	$smwgMasterStore = new $store_class($oldStore);

	if ($qmStorePresent) {
		$smwgMasterStore = new HACLSMWStore($smwgMasterStore);
	}
	return $smwgMasterStore;
}


