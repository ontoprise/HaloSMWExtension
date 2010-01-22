<?php
/*
 * Created on 13.09.2007
 *
 * Author: kai
 */
if ( !defined( 'MEDIAWIKI' ) ) die;

define('SMW_HALO_VERSION', '{{$VERSION}}-for-SMW-1.4.3');

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
$wgCustomVariables = array('CURRENTUSER', 'CURRENTUSERNS', 'NOW', 'TODAY');



require_once($smwgHaloIP."/includes/SMW_ResourceManager.php");
/**
 * Configures SMW Halo Extension for initialization.
 * Must be called *AFTER* SMW is intialized.
 *
 * @param String $store SMWHaloStore (old) or SMWHaloStore2 (new). Uses old by default.
 */
function enableSMWHalo($store = 'SMWHaloStore2', $tripleStore = NULL, $tripleStoreGraph = NULL) {
	global $wgExtensionFunctions, $smwgOWLFullExport, $smwgDefaultStore, $smwgBaseStore,
	$smwgSemanticDataClass, $wgHooks, $smwgTripleStoreGraph, $smwgIgnoreSchema, $smwgUseLocalhostForWSDL;
	if ($store == 'SMWHaloStore') {
		trigger_error("Old 'SMWHaloStore' is not supported anymore. Please upgrade to 'SMWHaloStore2'");
		die();
	}


	$smwgIgnoreSchema = !isset($smwgIgnoreSchema) ? true : $smwgIgnoreSchema;
	$smwgTripleStoreGraph = $tripleStoreGraph !== NULL ? $tripleStoreGraph : 'http://mywiki';
	$smwgUseLocalhostForWSDL=true;
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
	
	global $smgJSLibs;
	$smgJSLibs[] = 'prototype';
	
}

/**
 * Intializes SMW Halo Extension.
 * Called from SMW during initialization.
 */
function smwgHaloSetupExtension() {
	global $smwgIP, $smwgHaloIP, $wgHooks, $smwgMasterGeneralStore, $wgFileExtensions, $wgJobClasses, $wgExtensionCredits;
	global $smwgHaloContLang, $wgAutoloadClasses, $wgSpecialPages, $wgAjaxExportList, $wgGroupPermissions;
	global $mediaWiki, $wgSpecialPageGroups;
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
	$wgAutoloadClasses['SMWAggregationResultPrinter'] = $smwgHaloIP . '/includes/queryprinters/SMW_QP_Aggregation.php';
	$wgAutoloadClasses['SMWExcelResultPrinter'] = $smwgHaloIP . '/includes/queryprinters/SMW_QP_Excel.php';
	$wgAutoloadClasses['SMWSPARQLQuery'] = $smwgHaloIP . '/includes/SMW_SPARQLQueryParser.php';

	require_once $smwgHaloIP.'/includes/queryprinters/SMW_QP_Halo.php';
	require_once $smwgHaloIP.'/includes/queryprinters/SMW_QP_Provenance.php';

	global $smwgResultFormats;

	if (!defined('SGA_GARDENING_EXTENSION_VERSION')) {
		$smwgResultFormats['table'] = 'SMWHaloTableResultPrinter';
		$smwgResultFormats['broadtable'] = 'SMWHaloTableResultPrinter';
	}
	$smwgResultFormats['exceltable'] = 'SMWExcelResultPrinter';
	$smwgResultFormats['aggregation'] = 'SMWAggregationResultPrinter';
	$smwgResultFormats['csv'] = 'SMWHaloCsvResultPrinter';
	$smwgResultFormats['embedded'] = 'SMWHaloEmbeddedResultPrinter';
	$smwgResultFormats['list'] = 'SMWHaloListResultPrinter';
	$smwgResultFormats['ol'] = 'SMWHaloListResultPrinter';
	$smwgResultFormats['ul'] = 'SMWHaloListResultPrinter';
	$smwgResultFormats['template'] = 'SMWHaloTemplateResultPrinter';
	$smwgResultFormats['count'] = 'SMWHaloCountResultPrinter';
	$smwgResultFormats['debug'] = 'SMWHaloListResultPrinter';
	$smwgResultFormats['rss'] = 'SMWHaloRSSResultPrinter';
	
	$smwgResultFormats['ul_table'] = 'SMWProvenanceResultPrinter';



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
	if (isset($smwgWebserviceEndpoint) && $smwgShowDerivedFacts === true) {
		$wgHooks['smwShowFactbox'][] = 'smwfAddDerivedFacts';
	}

	// Remove the existing smwfSaveHook and replace it with the
	// new and functionally enhanced smwfHaloSaveHook
	$wgHooks['ParserBeforeStrip'][] = 'smwfRegisterSPARQLInlineQueries';

	$wgHooks['OntoSkinTemplateToolboxEnd'][] = 'smwfOntoSkinTemplateToolboxEnd';
	$wgHooks['OntoSkinTemplateNavigationEnd'][] = 'smwfOntoSkinTemplateNavigationEnd';
	$wgHooks['OntoSkinInsertTreeNavigation'][] = 'smwfNavTree';


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
	$wgHooks['UserToggles'][] = 'smwfAutoCompletionToggles';
	$wgHooks['UserSaveSettings'][] = 'smwfSetUserDefinedCookies';

	//parser function for multiple template annotations
	$wgHooks['ParserBeforeStrip'][] = 'smwfRegisterCommaAnnotation';

	// register AC icons
	$wgHooks['smwhACNamespaceMappings'][] = 'smwfRegisterAutocompletionIcons';

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



		$wgSpecialPages['Properties'] = array('SMWSpecialPage','Properties', 'smwfDoSpecialProperties', $smwgHaloIP . '/specials/SMWQuery/SMWAdvSpecialProperties.php');
		$wgSpecialPageGroups['Properties'] = 'smwplus_group';


		if (isset($smwgWebserviceEndpoint)) {
			$wgAutoloadClasses['SMWTripleStoreAdmin'] = $smwgHaloIP . '/specials/SMWTripleStoreAdmin/SMW_TripleStoreAdmin.php';
			$wgSpecialPages['TSA'] = array('SMWTripleStoreAdmin');
			$wgSpecialPageGroups['TSA'] = 'smwplus_group';
			
			$wgAutoloadClasses['SMWAskTSCPage'] = $smwgHaloIP . '/specials/SMWTripleStoreAdmin/SMW_AskTSC.php';
            $wgSpecialPages['AskTSC'] = array('SMWAskTSCPage');
            $wgSpecialPageGroups['AskTSC'] = 'smwplus_group';
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
    // new right for annotation mode
    global $wgAvailableRights;
    $wgAvailableRights[] = 'annotate';


	// Register Credits
	$wgExtensionCredits['parserhook'][]= array('name'=>'SMWHalo&nbsp;Extension', 'version'=>SMW_HALO_VERSION,
			'author'=>"Thomas&nbsp;Schweitzer, Kai&nbsp;K&uuml;hn, Markus&nbsp;Nitsche, J&ouml;rg Heizmann, Frederik&nbsp;Pfisterer, Robert Ulrich, Daniel Hansch, Moritz Weiten and Michael Erdmann. Maintained by [http://www.ontoprise.de Ontoprise].", 
			'url'=>'https://sourceforge.net/projects/halo-extension', 
			'description' => 'Facilitate the use of Semantic Mediawiki for a large community of non-tech-savvy users. [http://smwforum.ontoprise.com/smwforum/index.php/Help:SMW%2B_User_Manual View feature description.]');

	global $smwgWebserviceEndpoint;
	if (isset($smwgWebserviceEndpoint)) {
		$wgHooks['InternalParseBeforeLinks'][] = 'smwfTripleStoreParserHook';
	}
	$wgAjaxExportList[] = 'smwf_ts_getWikiNamespaces';

	// make hook for red links
	$wgHooks['BrokenLink'][] = 'smwfBrokenLinkForPage';

	// make hook for RichMedia
	$wgHooks['CheckNamespaceForImage'][] = 'smwfRichMediaIsImage';

	// add the 'halo' form input type, if Semantic Forms is installed
	global $sfgFormPrinter;
	if ($sfgFormPrinter) {
		$sfgFormPrinter->setInputTypeHook('haloACtext', 'smwfHaloFormInput', array());
		$sfgFormPrinter->setInputTypeHook('haloACtextarea', 'smwfHaloFormInputTextarea', array());
	}

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
	return true;
}

/**
 * Returns a comma separated list of extra namespace mappings.
 * Exported as ajax call. Need by TSC to get extra namespaces (besides the default of MW + SMW)
 *
 * nsText => nsIndex
 *
 * @return string
 */
function smwf_ts_getWikiNamespaces() {
	global $wgExtraNamespaces;
	$builtinNS = array(SMW_NS_PROPERTY, SMW_NS_PROPERTY_TALK, SMW_NS_TYPE, SMW_NS_TYPE_TALK, SMW_NS_CONCEPT, SMW_NS_CONCEPT_TALK);
	$result = "";
	$first = true;
	foreach($wgExtraNamespaces as $nsIndex => $nsText) {
		if (in_array($nsIndex, $builtinNS)) continue;
		$result .= (!$first ? "," : "").$nsText."=".$nsIndex;
		$first = false;
	}
	return $result;
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
	$html = SFFormInputs::$method($cur_value, $input_name, $is_mandatory, $is_disabled, $other_args);

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
	global $wgOut;

	$skin = $wgUser->getSkin();
	$skinName = $wgUser !== NULL ? $wgUser->getSkin()->getSkinName() : $wgDefaultSkin;
	$jsm = SMWResourceManager::SINGLETON();
	$jsm->addCSSIf($smwgHaloScriptPath . '/skins/Autocompletion/wick.css');

	$jsm->addCSSIf($smwgHaloScriptPath . '/skins/semantictoolbar.css', "edit");
	$jsm->addCSSIf($smwgHaloScriptPath . '/skins/semantictoolbar.css', "annotate");
	$jsm->addCSSIf($smwgHaloScriptPath . '/skins/semantictoolbar.css', "formedit");
        $jsm->addCSSIf($smwgHaloScriptPath . '/skins/semantictoolbar.css', "submit");
	$jsm->addCSSIf($smwgHaloScriptPath . '/skins/semantictoolbar.css', "all", NS_SPECIAL, array(NS_SPECIAL.':AddData', NS_SPECIAL.':EditData'));

	$jsm->addCSSIf($wgStylePath .'/'.$skin->getSkinName().'/lightbulb.css');
	
	$jsm->addCSSIf($smwgHaloScriptPath . '/skins/Annotation/annotation.css', "annotate");
	$jsm->addCSSIf($smwgHaloScriptPath . '/skins/Annotation/annotation.css', "edit");
	$jsm->addCSSIf($smwgHaloScriptPath . '/skins/Annotation/annotation.css', "formedit");
        $jsm->addCSSIf($smwgHaloScriptPath . '/skins/Annotation/annotation.css', "submit");
	$jsm->addCSSIf($smwgHaloScriptPath . '/skins/Annotation/annotation.css', "all", NS_SPECIAL, array(NS_SPECIAL.':AddData', NS_SPECIAL.':EditData'));

	$jsm->addCSSIf($smwgHaloScriptPath . '/skins/derivedFactsTab.css');
	
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
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/GeneralGUI/STB_Framework.js', "submit");
                $jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/GeneralGUI/STB_Framework.js', "all", NS_SPECIAL, array(NS_SPECIAL.':AddData', NS_SPECIAL.':EditData'));



		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/GeneralGUI/STB_Divcontainer.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/GeneralGUI/STB_Divcontainer.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/GeneralGUI/STB_Divcontainer.js', "formedit");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/GeneralGUI/STB_Divcontainer.js', "submit");
                $jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/GeneralGUI/STB_Divcontainer.js', "all", NS_SPECIAL, array(NS_SPECIAL.':AddData', NS_SPECIAL.':EditData'));



		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/Autocompletion/wick.js');

		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Links.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Links.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Links.js', "formedit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Links.js', "submit");
                $jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Links.js', "all", NS_SPECIAL, array(NS_SPECIAL.':AddData', NS_SPECIAL.':EditData'));
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/WikiTextParser/Annotation.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/WikiTextParser/Annotation.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/WikiTextParser/Annotation.js', "formedit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/WikiTextParser/Annotation.js', "submit");
                $jsm->addScriptIf($smwgHaloScriptPath . '/scripts/WikiTextParser/Annotation.js', "all", NS_SPECIAL, array(NS_SPECIAL.':AddData', NS_SPECIAL.':EditData'));
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/WikiTextParser/WikiTextParser.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/WikiTextParser/WikiTextParser.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/WikiTextParser/WikiTextParser.js', "formedit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/WikiTextParser/WikiTextParser.js', "submit");
                $jsm->addScriptIf($smwgHaloScriptPath . '/scripts/WikiTextParser/WikiTextParser.js', "all", NS_SPECIAL, array(NS_SPECIAL.':AddData', NS_SPECIAL.':EditData'));
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Ontology.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Ontology.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Ontology.js', "formedit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Ontology.js', "submit");
                $jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Ontology.js', "all", NS_SPECIAL, array(NS_SPECIAL.':AddData', NS_SPECIAL.':EditData'));
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_DataTypes.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_DataTypes.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_DataTypes.js', "formedit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_DataTypes.js', "submit");
                $jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_DataTypes.js', "all", NS_SPECIAL, array(NS_SPECIAL.':AddData', NS_SPECIAL.':EditData'));
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_GenericToolbarFunctions.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_GenericToolbarFunctions.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_GenericToolbarFunctions.js', "formedit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_GenericToolbarFunctions.js', "submit");
                $jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_GenericToolbarFunctions.js', "all", NS_SPECIAL, array(NS_SPECIAL.':AddData', NS_SPECIAL.':EditData'));
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Container.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Container.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Container.js', "formedit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Container.js', "submit");
                $jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Container.js', "all", NS_SPECIAL, array(NS_SPECIAL.':AddData', NS_SPECIAL.':EditData'));
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Marker.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Marker.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Marker.js', "formedit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Marker.js', "submit");
                $jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Marker.js', "all", NS_SPECIAL, array(NS_SPECIAL.':AddData', NS_SPECIAL.':EditData'));
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Category.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Category.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Category.js', "formedit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Category.js', "submit");
                $jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Category.js', "all", NS_SPECIAL, array(NS_SPECIAL.':AddData', NS_SPECIAL.':EditData'));
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/AdvancedAnnotation/SMW_AnnotationHints.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/AdvancedAnnotation/SMW_AnnotationHints.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/AdvancedAnnotation/SMW_AnnotationHints.js', "formedit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/AdvancedAnnotation/SMW_AnnotationHints.js', "submit");
                $jsm->addScriptIf($smwgHaloScriptPath . '/scripts/AdvancedAnnotation/SMW_AnnotationHints.js', "all", NS_SPECIAL, array(NS_SPECIAL.':AddData', NS_SPECIAL.':EditData'));
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/AdvancedAnnotation/SMW_GardeningHints.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/AdvancedAnnotation/SMW_GardeningHints.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/AdvancedAnnotation/SMW_GardeningHints.js', "formedit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/AdvancedAnnotation/SMW_GardeningHints.js', "submit");
                $jsm->addScriptIf($smwgHaloScriptPath . '/scripts/AdvancedAnnotation/SMW_GardeningHints.js', "all", NS_SPECIAL, array(NS_SPECIAL.':AddData', NS_SPECIAL.':EditData'));
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/AdvancedAnnotation/SMW_SaveAnnotations.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Relation.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Relation.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Relation.js', "formedit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Relation.js', "submit");
                $jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Relation.js', "all", NS_SPECIAL, array(NS_SPECIAL.':AddData', NS_SPECIAL.':EditData'));
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Properties.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Properties.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Properties.js', "formedit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Properties.js', "submit");
                $jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Properties.js', "all", NS_SPECIAL, array(NS_SPECIAL.':AddData', NS_SPECIAL.':EditData'));

		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Refresh.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Refresh.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Refresh.js', "formedit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Refresh.js', "submit");
                $jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_Refresh.js', "all", NS_SPECIAL, array(NS_SPECIAL.':AddData', NS_SPECIAL.':EditData'));
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_DragAndResize.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_DragAndResize.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_DragAndResize.js', "formedit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_DragAndResize.js', "submit");
                $jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_DragAndResize.js', "all", NS_SPECIAL, array(NS_SPECIAL.':AddData', NS_SPECIAL.':EditData'));
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_ContextMenu.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_ContextMenu.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_ContextMenu.js', "formedit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_ContextMenu.js', "submit");
                $jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMW_ContextMenu.js', "all", NS_SPECIAL, array(NS_SPECIAL.':AddData', NS_SPECIAL.':EditData'));

		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMWEditInterface.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMWEditInterface.js', "formedit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMWEditInterface.js', "submit");
                $jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMWEditInterface.js', "all", NS_SPECIAL, array(NS_SPECIAL.':AddData', NS_SPECIAL.':EditData'));
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/OntologyBrowser/obSemToolContribution.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/OntologyBrowser/obSemToolContribution.js', "formedit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/OntologyBrowser/obSemToolContribution.js', "submit");
                $jsm->addScriptIf($smwgHaloScriptPath . '/scripts/OntologyBrowser/obSemToolContribution.js', "all", NS_SPECIAL, array(NS_SPECIAL.':AddData', NS_SPECIAL.':EditData'));

		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/AdvancedAnnotation/SMW_AdvancedAnnotation.js', "edit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/AdvancedAnnotation/SMW_AdvancedAnnotation.js', "annotate");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/AdvancedAnnotation/SMW_AdvancedAnnotation.js', "formedit");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/AdvancedAnnotation/SMW_AdvancedAnnotation.js', "submit");
                $jsm->addScriptIf($smwgHaloScriptPath . '/scripts/AdvancedAnnotation/SMW_AdvancedAnnotation.js', "all", NS_SPECIAL, array(NS_SPECIAL.':AddData', NS_SPECIAL.':EditData'));
                
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/SMW_DerivedFactsTab.js');
                
	} else {
		//$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/prototype.js');
		//$jsm->setScriptID($smwgHaloScriptPath .  '/scripts/prototype.js', 'Prototype_script_inclusion');

		smwfHaloAddJSLanguageScripts($jsm);
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/deployGeneralTools.js');
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/GeneralGUI/STB_Framework.js');
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/GeneralGUI/STB_Divcontainer.js');
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/SemanticToolbar/SMWEditInterface.js');
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/OntologyBrowser/obSemToolContribution.js');
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
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/QueryInterface/treeviewQI.js', "all", -1, NS_SPECIAL.":QueryInterface");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/QueryInterface/queryTree.js', "all", -1, NS_SPECIAL.":QueryInterface");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/QueryInterface/Query.js', "all", -1, NS_SPECIAL.":QueryInterface");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/QueryInterface/QIHelper.js', "all", -1, NS_SPECIAL.":QueryInterface");
		$jsm->addScriptIf($smwgScriptPath .  '/skins/SMW_tooltip.js', "all", -1, NS_SPECIAL.":QueryInterface");
		
	} else {

		//$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/prototype.js', "all", -1, NS_SPECIAL.":QueryInterface");
		smwfHaloAddJSLanguageScripts($jsm, "all", -1, NS_SPECIAL.":QueryInterface");
		$jsm->addScriptIf($smwgHaloScriptPath . '/scripts/deployGeneralTools.js', "all", -1, NS_SPECIAL.":QueryInterface");
		$jsm->addScriptIf($smwgHaloScriptPath .  '/scripts/QueryInterface/deployQueryInterface.js', "all", -1, NS_SPECIAL.":QueryInterface");

		
	}
	$jsm->addCSSIf($smwgScriptPath .  '/skins/SMW_custom.css', "all", -1, NS_SPECIAL.":QueryInterface");
	$jsm->addCSSIf($smwgHaloScriptPath . '/skins/QueryInterface/treeview.css', "all", -1, NS_SPECIAL.":QueryInterface");
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
function smwfAutoCompletionToggles(&$extraToggles) {
	$extraToggles[] = "autotriggering";
	return true;
}

function smwfSetUserDefinedCookies(& $user) {
	global $wgScriptPath;
	
    $autoTriggering = $user->getOption( "autotriggering" ) == 1 ? "autotriggering=auto" : "autotriggering=manual";
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
	'<table style="width:100%; height:100%; border-collapse:collapse;empty-cells:show">'.
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
			'<td colspan="4" style="width:100%; height:100%" class="dftTabCont">'.
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
