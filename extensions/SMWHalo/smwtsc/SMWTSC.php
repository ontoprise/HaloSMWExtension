<?php

// uncomment this if it becomes a real extension
// define('TSC_EXTENSION_VERSION', '{{$VERSION}} [B{{$BUILDNUMBER}}]');

$wgExtensionFunctions[] = 'tscSetupExtension';
$tscgIP = $IP . '/extensions/SMWHalo/smwtsc';
$tscgScriptPath = $wgScriptPath . '/extensions/SMWHalo/smwtsc';

require_once( "$tscgIP/includes/TSC_ParserFunctions.php" );


$wgHooks['LanguageGetMagic'][] = 'tscfAddMagicWords';
$wgExtensionMessagesFiles['smwtsc'] = $tscgIP . '/languages/TSC_Messages.php'; // register messages (requires MW=>1.11)

global $wgLanguageCode;
tscfInitContentLanguage($wgLanguageCode);

global $smwgQuerySources;
$smwgQuerySources += array("tsc" => "SMWTripleStore");

global $smwgSemanticDataClass;
$smwgSemanticDataClass = smwfIsTripleStoreConfigured() ? 'SMWFullSemanticData' : 'SMWSemanticData';
$smwgHaloTripleStoreGraph = isset($smwgHaloTripleStoreGraph) ? $smwgHaloTripleStoreGraph : 'http://mywiki';

function tscSetupExtension() {
	// init TSC
	global $tscgIP, $wgAutoloadClasses, $wgExtensionMessagesFiles, $smwgHaloWebserviceEndpoint, $wgHooks;

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

	$wgAutoloadClasses['TSCException'] = $tscgIP . '/includes/exceptions/TSC_Exception.php';
	$wgAutoloadClasses['TSCTSAException'] = $tscgIP . '/includes/exceptions/TSC_TSAException.php';
	$wgAutoloadClasses['TSCPrefixManagerException'] = $tscgIP . '/includes/exceptions/TSC_PrefixManagerException.php';
	
	$wgAutoloadClasses['TSCStorageSQL'] = $tscgIP . '/includes/storage/TSC_StorageSQL.php';
	$wgAutoloadClasses['TSCStorage'] = $tscgIP . '/includes/storage/TSC_Storage.php';

	$wgHooks['ArticleSave'][] = 'TSCParserFunctions::onArticleSave';
	$wgHooks['ArticleDelete'][] = 'TSCParserFunctions::articleDelete';

	// TSC client


	if (is_array($smwgHaloWebserviceEndpoint) && count($smwgHaloWebserviceEndpoint) > 1 && !isset($smwgMessageBroker)) {
		trigger_error("Multiple webservice endpoints require a messagebroker to handle triplestore updates.");
		die();
	}

	if (smwfIsTripleStoreConfigured() && !isset($smwgHaloWebserviceEndpoint)) {
		trigger_error('$smwgHaloWebserviceEndpoint is required but not set. Example: $smwgHaloWebserviceEndpoint="localhost:8080";');
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
	$wgAutoloadClasses['TSCNonExistingPage'] = $tscgIP . '/includes/articlepages/TSC_NonExistingPage.php';
	$wgAutoloadClasses['TSCNonExistingPageHandler'] = $tscgIP . '/includes/articlepages/TSC_NonExistingPageHandler.php';


	global $smwgHaloQuadMode;
	
	smwfAddStore('SMWHaloStore2');
	if (isset($smwgHaloWebserviceEndpoint) && $smwgHaloQuadMode === true) {
		smwfAddStore('SMWTripleStoreQuad');
	} else if (isset($smwgHaloWebserviceEndpoint)) {
		smwfAddStore('SMWTripleStore');
	} 
	

	global $smwgResultFormats;
	$smwgResultFormats['fancytable'] = 'SMWFancyTableResultPrinter';

	$wgHooks['smwInitDatatypes'][] = 'tscfInitDatatypes';

	global $smwgHaloShowDerivedFacts, $wgRequest;
	if ($smwgHaloShowDerivedFacts === true) {
		$wgHooks['smwShowFactbox'][] = 'tscfAddDerivedFacts';
	}

	// Remove the existing smwfSaveHook and replace it with the
	// new and functionally enhanced smwfHaloSaveHook
	$wgHooks['ParserBeforeStrip'][] = 'tscfRegisterSPARQLInlineQueries';
	$wgHooks['InternalParseBeforeLinks'][] = 'tscfRegisterIntegrationLink';

	global $smwgHaloNEPEnabled;
	if ($smwgHaloNEPEnabled) {
		$wgHooks['ArticleFromTitle'][]      = 'TSCNonExistingPageHandler::onArticleFromTitle';
		$wgHooks['EditFormPreloadText'][]   = 'TSCNonExistingPageHandler::onEditFormPreloadText';
		$wgHooks['sfEditFormPreloadText'][]   = 'TSCNonExistingPageHandler::onEditFormPreloadText';

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
		global $smwgHaloTripleStoreGraph;
		$title = Title::newFromText($wgRequest->getVal('title'));
		// title parameter contains the URI fragement: property/HasName, a/Prius, category/Automobile
		$uri = $smwgHaloTripleStoreGraph."/".$wgRequest->getVal('title');
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
		$wgHooks['InternalParseBeforeLinks'][] = 'tscfTripleStoreParserHook';
	}

	
	

	require_once("$tscgIP/includes/TSC_AjaxFunctions.php");
	
	global $wgSpecialPages, $wgSpecialPageGroups;
	$wgAutoloadClasses['TSCTripleStoreAdmin'] = $tscgIP . '/specials/TripleStoreAdmin/TSC_TripleStoreAdmin.php';
	$wgSpecialPages['TSA'] = array('TSCTripleStoreAdmin');
	$wgSpecialPageGroups['TSA'] = 'smwplus_group';
	
	$wgAutoloadClasses['TSCSourcesPage']       = $tscgIP . '/specials/Datasources/TSC_Datasources.php';
    $wgSpecialPages['TSCSources']       = array( 'TSCSourcesPage' );
    $wgSpecialPageGroups['TSCSources']  = 'smwplus_group';
	
	$wgHooks['ResourceLoaderRegisterModules'][]='tscfRegisterResourceLoaderModules';
    $wgHooks['BeforePageDisplay'][]='tscfAddHTMLHeader';
}


function tscfRegisterSPARQLInlineQueries( &$parser, &$text, &$stripstate ) {

	$parser->setFunctionHook( 'sparql', 'smwfProcessSPARQLInlineQueryParserFunction');

	return true; // always return true, in order not to stop MW's hook processing!
}

function tscfRegisterIntegrationLink(&$parser, &$text, &$strip_state = null) {
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
function tscfTripleStoreParserHook(&$parser, &$text, &$strip_state = null) {
	global $smwgIP, $smwgHaloTripleStoreGraph;
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
function tscfInitDatatypes() {
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
 * Initialise a global language object for content language. This
 * must happen early on, even before user language is known, to
 * determine labels for additional namespaces. In contrast, messages
 * can be initialised much later when they are actually needed.
 */
function tscfInitContentLanguage($langcode) {
	global $tscgIP, $tscgContLang;
	if (!empty($tscgContLang)) {
		return;
	}
	wfProfileIn('tscfInitContentLanguage');

	$tscContLangFile = 'TSC_Language' . str_replace( '-', '_', ucfirst( $langcode ) );
	$tscContLangClass = 'TSCLanguage' . str_replace( '-', '_', ucfirst( $langcode ) );
	if (file_exists($tscgIP . '/languages/'. $tscContLangFile . '.php')) {
		include_once( $tscgIP . '/languages/'. $tscContLangFile . '.php' );
	}

	// fallback if language not supported
	if ( !class_exists($tscContLangClass)) {
		include_once($tscgIP . '/languages/TSC_LanguageEn.php');
		$tscContLangClass = 'TSCLanguageEn';
	}
	$tscgContLang = new $tscContLangClass();

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
function tscfAddDerivedFacts(& $text, $semdata) {
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
               'scripts/TSC_DerivedFactsTab.js',
               'scripts/TSC_Datasources.js',
               ),
         'messages' => array( 'tsc_derivedfacts_request_failed'),       
        'styles' => array('skins/datasources.css'),
        'dependencies' => array()

               );

  return true;
}

function tscfAddHTMLHeader(&$out) {
	$out->addModules('ext.smwtsc.general');
	return true;
}
