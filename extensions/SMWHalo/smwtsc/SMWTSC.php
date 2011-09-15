<?php

$wgExtensionFunctions[] = 'tscSetupExtension';
$tscgIP = $IP . '/extensions/SMWHalo/smwtsc';
$tscgScriptPath = $wgScriptPath . '/extensions/SMWHalo/smwtsc';

require_once( "$tscgIP/includes/TSC_ParserFunctions.php" );


$wgHooks['LanguageGetMagic'][] = 'tscfAddMagicWords';
$wgExtensionMessagesFiles['smwtsc'] = $tscgIP . '/languages/TSC_Messages.php'; // register messages (requires MW=>1.11)

global $wgLanguageCode;
tscfInitNamespaces();

global $smwgQuerySources;
$smwgQuerySources += array("tsc" => "SMWTripleStore");

global $smwgSemanticDataClass;
$smwgSemanticDataClass = smwfIsTripleStoreConfigured() ? 'SMWFullSemanticData' : 'SMWSemanticData';
$smwgTripleStoreGraph = isset($smwgTripleStoreGraph) ? $smwgTripleStoreGraph : 'http://mywiki';

function tscSetupExtension() {
	// init TSC
	global $tscgIP, $wgAutoloadClasses, $wgExtensionMessagesFiles, $smwgWebserviceEndpoint, $wgHooks;

	$wgAutoloadClasses['TSCAdministrationStore'] = $tscgIP . '/includes/storage/TSC_AdministrationStore.php';
	$wgAutoloadClasses['TSCPersistentTripleStoreAccess'] = $tscgIP . '/includes/storage/TSC_PersistentTripleStoreAccess.php';
	$wgAutoloadClasses['TSCSourceDefinition'] = $tscgIP . '/includes/TSC_SourceDefinition.php';
	$wgAutoloadClasses['TSCSparqlQueryResult'] = $tscgIP . '/includes/triplestore_api/TSC_SparqlQueryResult.php';
	$wgAutoloadClasses['TSCTriple'] = $tscgIP . '/includes/triplestore_api/TSC_Triple.php';
	$wgAutoloadClasses['TSCTripleStoreAccess'] = $tscgIP . '/includes/triplestore_api/TSC_TripleStoreAccess.php';
	$wgAutoloadClasses['TSCParserFunctions'] = $tscgIP . '/includes/TSC_ParserFunctions.php';
	$wgAutoloadClasses['TSCDBHelper'] = $tscgIP . '/includes/TSC_DBHelper.php';
	$wgAutoloadClasses['TSCPrefixManager'] = $tscgIP . '/includes/TSC_PrefixManager.php';
	$wgAutoloadClasses['TSCSparqlQueryParser'] = $tscgIP . '/includes/sparqlparser/TSC_SparqlQueryParser.php';
	$wgAutoloadClasses['TSCSparqlQueryVisitor'] = $tscgIP . '/includes/sparqlparser/TSC_SparqlQueryVisitor.php';
	$wgAutoloadClasses['TSCSparqlSerializerVisitor'] = $tscgIP . '/includes/sparqlparser/TSC_SparqlSerializerVisitor.php';

	$wgAutoloadClasses['TSCException'] = $tscgIP . '/includes/TSCSparql/TSC_Exception.php';
	$wgAutoloadClasses['TSCPrefixManagerException'] = $tscgIP . '/includes/TSCSparql/TSC_PrefixManagerException.php';
	$wgAutoloadClasses['TSCTSAException'] = $tscgIP . '/includes/TSCSparql/TSC_TSAException.php';

	$wgAutoloadClasses['TSCStorageSQL'] = $tscgIP . '/includes/storage/TSC_StorageSQL.php';
	$wgAutoloadClasses['TSCStorage'] = $tscgIP . '/includes/storage/TSC_Storage.php';

	$wgHooks['ArticleSave'][] = 'TSCParserFunctions::onArticleSave';
	$wgHooks['ArticleDelete'][] = 'TSCParserFunctions::articleDelete';

	// TSC client


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

	$wgAutoloadClasses['SMWQueryProcessor'] = $tscgIP . '/includes/query_processor/SMW_QueryProcessor.php';
	$wgAutoloadClasses['SMWSPARQLQueryProcessor']            = $tscgIP . '/includes/query_processor/TSC_SPARQLQueryProcessor.php';
	$wgAutoloadClasses['SMWSPARQLQueryParser']            = $tscgIP . '/includes/query_processor/TSC_SPARQLQueryParser.php';

	$wgAutoloadClasses['OB_StorageTS'] = $tscgIP . '/includes/storage/TSC_OB_Store.php';
	$wgAutoloadClasses['OB_StorageTSQuad'] = $tscgIP . '/includes/storage/TSC_OB_Store.php';
	$wgAutoloadClasses['AutoCompletionStorageTSCQuad'] = $tscgIP . '/includes/storage/TSC_AC_Store.php';

	$wgAutoloadClasses['SMWHaloStore2'] = $tscgIP . '/includes/triplestore_client/TSC_HaloStore2.php';
	$wgAutoloadClasses['TSConnection']            = $tscgIP . '/includes/triplestore_client/TSC_Connection.php';
	$wgAutoloadClasses['TSNamespaces']            = $tscgIP . '/includes/triplestore_client/TSC_Helper.php';
	$wgAutoloadClasses['TSHelper']            = $tscgIP . '/includes/triplestore_client/TSC_Helper.php';
	$wgAutoloadClasses['WikiTypeToXSD']            = $tscgIP . '/includes/triplestore_client/TSC_Helper.php';
	$wgAutoloadClasses['SMWTripleStore']            = $tscgIP . '/includes/triplestore_client/TSC_TripleStore.php';
	$wgAutoloadClasses['SMWTripleStoreQuad']            = $tscgIP . '/includes/triplestore_client/TSC_TripleStoreQuad.php';
	$wgAutoloadClasses['SMWFullSemanticData']            = $tscgIP . '/includes/triplestore_client/TSC_FullSemanticData.php';
	$wgAutoloadClasses['SMWSPARQLQuery'] = $tscgIP . '/includes/queryprocessor/TSC_SPARQLQueryParser.php';
	$wgAutoloadClasses['SMWURIIntegrationValue'] = $tscgIP . '/includes/datavalues/TSC_DV_IntegrationURI.php';
	$wgAutoloadClasses['SMWDIIntegrationUri'] = $tscgIP . '/includes/dataitems/TSC_DI_IntegrationURI.php';
	$wgAutoloadClasses['LODNonExistingPage'] = $tscgIP . '/includes/articlepages/TSC_NonExistingPage.php';
	$wgAutoloadClasses['LODNonExistingPageHandler'] = $tscgIP . '/includes/articlepages/TSC_NonExistingPageHandler.php';


	global $smwgMasterStore, $smwgQuadMode;
	$oldStore = smwfGetStore();
	
	$qmStorePresent = false;
	if ($oldStore instanceof SMWQMStore) {
		$qmStorePresent = true;
		$oldStore = $oldStore->getStore();
	}
	$halostore = new SMWHaloStore2($oldStore);
	if (isset($smwgWebserviceEndpoint) && $smwgQuadMode === true) {
		$smwgMasterStore = new SMWTripleStoreQuad($halostore);
	} else if (isset($smwgWebserviceEndpoint)) {
		$smwgMasterStore = new SMWTripleStore($halostore);
	} else {
		$smwgMasterStore = $halostore;
	}
	if ($qmStorePresent) {
		$smwgMasterStore = new SMWQMStore($smwgMasterStore);
	}

	global $smwgResultFormats;
	$smwgResultFormats['fancytable'] = 'SMWFancyTableResultPrinter';

	$wgHooks['smwInitDatatypes'][] = 'smwfHaloInitDatatypes';

	global $smwgShowDerivedFacts, $wgRequest;
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
		$wgHooks['sfEditFormPreloadText'][]   = 'LODNonExistingPageHandler::onEditFormPreloadText';

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


	// Provide a Linked Data Interface based on the following URI schemata (@see http://www4.wiwiss.fu-berlin.de/bizer/pub/LinkedDataTutorial/):
	//
	// Resource URI: http://mywiki/resource/Prius
	// -> 303 forward to:
	//      Information resource (HTML): http://mywiki/index.php/Prius
	//      Information resource (RDF): http://mywiki/index.php/Prius?format=rdf
	//      Information resource (RDF): http://mywiki/index.php/Prius (when requested MIME type is application=rdf/xml)
	//
	// Requires a mod_rewrite configuration as follows:
	//  RewriteEngine on
	//  RewriteBase /HaloSMWExtension
	//  RewriteRule ^resource/(.*) index.php?action=ldnegotiate&title=$1 [PT,L,QSA]

	// Perform content negotiation when invoked with action=ldnegotiate
	if (array_key_exists('action', $_REQUEST) && $_REQUEST['action'] == 'ldnegotiate' ) {
		global $smwgTripleStoreGraph;
		$title = Title::newFromText($wgRequest->getVal('title'));
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
		$title = Title::newFromText($wgRequest->getVal('title'));
		require_once( $IP . '/extensions/SMWHalo/includes/webservices/SMW_EQI.php' );
		header ( "Content-Type: application/rdf+xml" );
		echo smwhRDFRequest($title->getPrefixedText());
		exit; // stop any processing here
	}

	// special handling: application/rdf+xml requests are redirected to
	// the external query interface
	if (array_key_exists('HTTP_ACCEPT', $_SERVER) && $_SERVER['HTTP_ACCEPT'] == 'application/rdf+xml') {
		global $IP;
		$title = Title::newFromText($wgRequest->getVal('title'));
		require_once( $IP . '/extensions/SMWHalo/includes/webservices/SMW_EQI.php' );
		header ( "Content-Type: application/rdf+xml" );
		echo smwhRDFRequest($title->getPrefixedText());
		exit; // stop any processing here
	}

	// add triple store hooks if necessary
	global $smwgIgnoreSchema;
	if (smwfIsTripleStoreConfigured()) {

		require_once("$tscgIP/includes/triplestore_client/TSC_SimpleContributor.php");
		$wgHooks['TripleStorePropertyUpdate'][] = 'smwfTripleStorePropertyUpdate';

		$wgHooks['TripleStoreCategoryUpdate'][] = 'smwfTripleStoreCategoryUpdate';
	}

	if (smwfIsTripleStoreConfigured()) {
		$wgHooks['InternalParseBeforeLinks'][] = 'smwfTripleStoreParserHook';
	}

	global $wgAjaxExportList;
	$wgAjaxExportList[] = 'smwf_ts_getSyncCommands';
	$wgAjaxExportList[] = 'smwf_ts_getWikiNamespaces';
	$wgAjaxExportList[] = 'smwf_ts_getWikiSpecialProperties';
	$wgAjaxExportList[] = 'smwf_ts_triggerAsynchronousLoading';
	$wgAjaxExportList[] = 'smwf_om_GetDerivedFacts';

	global $wgSpecialPages, $wgSpecialPageGroups;
	$wgAutoloadClasses['TSCTripleStoreAdmin'] = $tscgIP . '/specials/TripleStoreAdmin/TSC_TripleStoreAdmin.php';
	$wgSpecialPages['TSA'] = array('TSCTripleStoreAdmin');
	$wgSpecialPageGroups['TSA'] = 'smwplus_group';
	
	$wgHooks['ResourceLoaderRegisterModules'][]='tscfRegisterResourceLoaderModules';
    $wgHooks['BeforePageDisplay'][]='tscfAddHTMLHeader';
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

		$label = '';
		$wikititle = '';
		for ($j = 0; $j < count($matchesheader[0]); $j++) {
			if (trim($matchesheader[1][$j]) == 'label') {
				$label = trim($matchesheader[2][$j]);
			} else if (trim($matchesheader[1][$j]) == 'wikititle') {
				$wikititle = trim($matchesheader[2][$j]);
			}
		}
		$text = str_replace($matches[0][$i], '<a class="new" href="'.$uri.'">'.$label.'</a>', $text);

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
 * Registeres SMW Halo Datatypes. Called from SMW.
 */
function smwfHaloInitDatatypes() {
	global $wgAutoloadClasses, $smwgHaloIP, $smwgHaloContLang;
	SMWDataValueFactory::registerDatatype('_ili', 'SMWURIIntegrationValue', SMWDIIntegrationUri::TYPE_INTEGRATIONURI,
	$smwgHaloContLang->getHaloDatatype('smw_integration_link'));

	return true;
}

function tscfAddMagicWords(&$magicWords, $langCode) {
	//  $magicWords['ask']     = array( 0, 'ask' );
	return true;
}

/**
 * Init the additional namespaces used by LinkedData. The
 * parameter denotes the least unused even namespace ID that is
 * greater or equal to 100.
 */
function tscfInitNamespaces() {

	global $lodgNamespaceIndex, $wgExtraNamespaces, $wgNamespaceAliases,
	$wgNamespacesWithSubpages, $wgLanguageCode, $lodgContLang;

	if (!isset($lodgNamespaceIndex)) {
		$lodgNamespaceIndex = 500;
	}

	// Constants for namespace "TSC"
	define('TSC_NS_TSC',       $lodgNamespaceIndex);
	define('TSC_NS_TSC_TALK',  $lodgNamespaceIndex+1);

	// Constants for namespace "Mapping"
	define('TSC_NS_MAPPING',       $lodgNamespaceIndex+2);
	define('TSC_NS_MAPPING_TALK',  $lodgNamespaceIndex+3);

	tscfInitContentLanguage($wgLanguageCode);

	// Register namespace identifiers
	if (!is_array($wgExtraNamespaces)) {
		$wgExtraNamespaces=array();
	}
	$namespaces = $lodgContLang->getNamespaces();
	$namespacealiases = $lodgContLang->getNamespaceAliases();
	$wgExtraNamespaces = $wgExtraNamespaces + $namespaces;
	$wgNamespaceAliases = $wgNamespaceAliases + $namespacealiases;

	// Support subpages for the namespace ACL
	$wgNamespacesWithSubpages = $wgNamespacesWithSubpages + array(
	TSC_NS_TSC => true,
	TSC_NS_TSC_TALK => true
	);
}

/**
 * Initialise a global language object for content language. This
 * must happen early on, even before user language is known, to
 * determine labels for additional namespaces. In contrast, messages
 * can be initialised much later when they are actually needed.
 */
function tscfInitContentLanguage($langcode) {
	global $tscgIP, $lodgContLang;
	if (!empty($lodgContLang)) {
		return;
	}
	wfProfileIn('tscfInitContentLanguage');

	$lodContLangFile = 'TSC_Language' . str_replace( '-', '_', ucfirst( $langcode ) );
	$lodContLangClass = 'TSCLanguage' . str_replace( '-', '_', ucfirst( $langcode ) );
	if (file_exists($tscgIP . '/languages/'. $lodContLangFile . '.php')) {
		include_once( $tscgIP . '/languages/'. $lodContLangFile . '.php' );
	}

	// fallback if language not supported
	if ( !class_exists($lodContLangClass)) {
		include_once($tscgIP . '/languages/TSC_LanguageEn.php');
		$lodContLangClass = 'TSCLanguageEn';
	}
	$lodgContLang = new $lodContLangClass();

	wfProfileOut('tscfInitContentLanguage');
}

/**
 * Callback function for the hook 'smwShowFactbox'. It is called when SMW creates
 * the factbox for an article.
 * This method replaces the whole factbox with a tabbed version that contains
 * the original factbox in one tab and the derived facts in another.
 *
 * @param string $text
 *      The HTML for the tabbed factbox is returned in this parameter
 * @param SMWSemanticData $semdata
 *      All static facts for the article
 * @return bool
 *      <false> : This means that SMW's factbox is completely replaced.
 */
function smwfAddDerivedFacts(& $text, $semdata) {
	global $smwgHaloScriptPath, $wgContLang;

	wfLoadExtensionMessages('SemanticMediaWiki');
	SMWOutputs::requireHeadItem(SMW_HEADER_STYLE);
	$rdflink = SMWInfolink::newInternalLink(wfMsgForContent('smw_viewasrdf'), $wgContLang->getNsText(NS_SPECIAL) . ':ExportRDF/' . $semdata->getSubject()->getTitle()->getDBkey(), 'rdflink');

	$browselink = SMWInfolink::newBrowsingLink($semdata->getSubject()->getTitle()->getText(), $semdata->getSubject()->getTitle()->getDBkey(), 'swmfactboxheadbrowse');
	$fbText = '<div class="smwfact">' .
                        '<span class="smwfactboxhead">' . wfMsgForContent('smw_factbox_head', $browselink->getWikiText() ) . '</span>' .
                    '<span class="smwrdflink">' . $rdflink->getWikiText() . '</span>' .
                    '<table class="smwfacttable">' . "\n";

	foreach($semdata->getProperties() as $propertyDi) {
		$propertyDv = SMWDataValueFactory::newDataItemValue( $propertyDi, null );
		if ( !$propertyDi->isShown() ) { // showing this is not desired, hide
			continue;
		} elseif ( $propertyDi->isUserDefined() ) { // user defined property
			$propertyDv->setCaption( preg_replace( '/[ ]/u', '&#160;', $propertyDv->getWikiValue(), 2 ) );
			/// NOTE: the preg_replace is a slight hack to ensure that the left column does not get too narrow
			$fbText .= '<tr><td class="smwpropname">' . $propertyDv->getLongWikiText( true ) . '</td><td class="smwprops">';
		} elseif ( $propertyDv->isVisible() ) { // predefined property
			$fbText .= '<tr><td class="smwspecname">' . $propertyDv->getLongWikiText( true ) . '</td><td class="smwspecs">';
		} else { // predefined, internal property
			continue;
		}

		$propvalues = $semdata->getPropertyValues( $propertyDi );

		$valuesHtml = array();

		foreach ( $propvalues as $dataItem ) {
			$dataValue = SMWDataValueFactory::newDataItemValue( $dataItem, $propertyDi );

			if ( $dataValue->isValid() ) {
				$valuesHtml[] = $dataValue->getLongWikiText( true ) . $dataValue->getInfolinkText( SMW_OUTPUT_WIKI );
			}
		}

		$fbText .= $GLOBALS['wgLang']->listToText( $valuesHtml );

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
 * Returns a list of SPARUL commands which are required to sync
 * with the TSC.
 *
 * @return string
 */
function smwf_ts_getSyncCommands() {
	global $smwgMessageBroker, $smwgTripleStoreGraph, $wgDBtype, $wgDBport,
	$wgDBserver, $wgDBname, $wgDBuser, $wgDBpassword, $wgDBprefix, $wgLanguageCode,
	$smwgIgnoreSchema, $smwgNamespaceIndex;

	$sparulCommands = array();

	// sync wiki module
	$sparulCommands[] = "DROP SILENT GRAPH <$smwgTripleStoreGraph>"; // drop may fail. don't worry
	$sparulCommands[] = "CREATE SILENT GRAPH <$smwgTripleStoreGraph>";
	$sparulCommands[] = "LOAD <smw://".urlencode($wgDBuser).":".urlencode($wgDBpassword).
    "@$wgDBserver:$wgDBport/$wgDBname?lang=$wgLanguageCode&smwstore=SMWHaloStore2".
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
	NS_FILE, NS_HELP, NS_TEMPLATE, NS_USER, NS_MEDIAWIKI, NS_PROJECT,   SMW_NS_PROPERTY_TALK,
	SF_NS_FORM_TALK,NS_TALK, NS_USER_TALK, NS_PROJECT_TALK, NS_FILE_TALK, NS_MEDIAWIKI_TALK,
	NS_TEMPLATE_TALK, NS_HELP_TALK, NS_CATEGORY_TALK, SMW_NS_CONCEPT_TALK);

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
 * This function retrieves the derived facts of the article with the name
 * $titleName.
 *
 * @param string $titleName
 *
 * @return string
 *      The derived facts as HTML
 */
function smwf_om_GetDerivedFacts($titleName) {
	$linker = new Linker();

	$t = Title::newFromText($titleName);
	if ($t == null) {
		// invalid title
		return wfMsg('smw_df_invalid_title');
	}

	if (!smwfIsTripleStoreConfigured()) {
		global $wgParser;
		$parserOutput = $wgParser->parse( wfMsg('smw_df_tsc_advertisment'), $t, new ParserOptions,
		true, true, 0 );
		return $parserOutput->getText();
	}

	$semdata = smwfGetStore()->getSemanticData(new SMWDIWikiPage($t->getDBkey(), $t->getNamespace(), ""));
	wfLoadExtensionMessages('SemanticMediaWiki');
	global $wgContLang;
	list($derivedFacts, $derivedCategories) = SMWFullSemanticData::getDerivedProperties($semdata);
	$derivedFactsFound = false;

	$text = '<div class="smwfact">' .
                '<span class="smwfactboxhead">' . 
	wfMsg('smw_df_derived_facts_about',
	$derivedFacts->getSubject()->getTitle()->getText()) .
                '</span>' .
                '<table class="smwfacttable">' . "\n";

	foreach($derivedFacts->getProperties() as $propertyDi) {
		$propertyDv = SMWDataValueFactory::newDataItemValue($propertyDi, null);

		if ( !$propertyDi->isShown() ) { // showing this is not desired, hide
			continue;
		} elseif ( $propertyDi->isUserDefined() ) { // user defined property
			$propertyDv->setCaption( preg_replace( '/[ ]/u', '&#160;', $propertyDv->getWikiValue(), 2 ) );
			/// NOTE: the preg_replace is a slight hack to ensure that the left column does not get too narrow
			$text .= '<tr><td class="smwpropname">' . $linker->makeLink($propertyDi->getDiWikiPage()->getTitle()->getPrefixedText()) . '</td><td class="smwprops">';
		} elseif ( $propertyDv->isVisible() ) { // predefined property
			$text .= '<tr><td class="smwspecname">' . $linker->makeLink($propertyDi->getDiWikiPage()->getTitle()->getPrefixedText()) . '</td><td class="smwspecs">';
		} else { // predefined, internal property
			continue;
		}

		$propvalues = $derivedFacts->getPropertyValues($propertyDi);

		$valuesHtml = array();

		foreach ( $propvalues as $dataItem ) {
			$dataValue = SMWDataValueFactory::newDataItemValue( $dataItem, $propertyDi );

			if ( $dataValue->isValid() ) {
				$derivedFactsFound = true;
				$valuesHtml[] = $dataValue->getLongHTMLText(  );
			}
		}

		$text .= $GLOBALS['wgLang']->listToText( $valuesHtml );
		$text .= '</td></tr>';
	}
	$text .= '</table>';

	$categoryLinks=array();
	foreach($derivedCategories as $c) {
		$derivedFactsFound=True;
		$categoryLinks[] = $linker->link($c);
	}
	$text .= '<br>'.implode(", ", $categoryLinks);
	$text .= '</div>';

	if (!$derivedFactsFound) {
		$text = wfMsg('smw_df_no_df_found');
	}
	return $text;
}

function tscfRegisterResourceLoaderModules() {
	global $wgResourceModules, $tscgIP, $tscgScriptPath, $wgUser;

	$moduleTemplate = array(
        'localBasePath' => $tscgIP,
        'remoteBasePath' => $tscgScriptPath,
        'group' => 'ext.smwtsc'
        );

        // Scripts and styles for all actions
        $wgResourceModules['ext.smwtsc.general'] = $moduleTemplate + array(
        'scripts' => array(
               'scripts/TSC_DerivedFactsTab.js'
               ),
         'messages' => array( 'tsc_derivedfacts_request_failed'),       
        'styles' => array(),
        'dependencies' => array()

               );

  return true;
}

function tscfAddHTMLHeader(&$out) {
	$out->addModules('ext.smwtsc.general');
	return true;
}
