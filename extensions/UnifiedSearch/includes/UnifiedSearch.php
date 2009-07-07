<?php
/**
 * @author: Kai Kï¿½hn
 *
 * Created on: 27.01.2009
 */
if( !defined( 'MEDIAWIKI' ) ) {
	echo("This file is an extension to the MediaWiki software and cannot be used standalone.\n");
	die(1);
}
define('US_SEARCH_EXTENSION_VERSION', '1.1');

define('US_HIGH_TOLERANCE', 0);
define('US_LOWTOLERANCE', 1);
define('US_EXACTMATCH', 2);


$wgExtensionCredits['other'][] = array(
        'name' => 'Enhanced Retrieval extension v'.US_SEARCH_EXTENSION_VERSION,
        'author' => 'Kai Kühn',
        'url' => 'http://sourceforge.net/projects/halo-extension/',
        'description' => 'Provides access to a Lucene backend.',
);

global $wgExtensionFunctions, $wgHooks, $wgAjaxExportList;
$wgAjaxExportList[] = 'smwf_ca_GetHTMLBody';

// use SMW_AddScripts hook from SMWHalo to make sure that Prototype is available.

$wgExtensionFunctions[] = 'wfUSSetupExtension';



// enable path search if set in LocalSettings.php
if (isset($wgUSPathSearch) && $wgUSPathSearch) {
	require_once($IP."/extensions/UnifiedSearch/PathSearch/PathSearch.php");
	require_once($IP."/extensions/UnifiedSearch/PathSearch/doPathSearch.php");
}

/**
 * Add javascripts and css files
 *
 * @param unknown_type $out
 * @return unknown
 */
function wfUSAddHeader(& $out) {
	global $wgScriptPath, $wgServer;

	$out->addLink(array(
                    'rel'   => 'stylesheet',
                    'type'  => 'text/css',
                    'media' => 'screen, projection',
                    'href'  => $wgScriptPath . '/extensions/UnifiedSearch/skin/unified_search.css'
                    ));
                    if (!defined("SMW_HALO_VERSION")) {
                    	$out->addScript('<script type="text/javascript" src="'.$wgScriptPath . '/extensions/UnifiedSearch/scripts/prototype.js"></script>');
                    }
                    $out->addScript('<script type="text/javascript" src="'.$wgScriptPath . '/extensions/UnifiedSearch/scripts/unified_search.js"></script>');
                    // add GreyBox
                    $out->addLink(array(
                    'rel'   => 'stylesheet',
                    'type'  => 'text/css',
                    'media' => 'screen, projection',
                    'href'  => $wgScriptPath . '/extensions/UnifiedSearch/scripts/GreyBox/gb_styles.css'
                    ));
                    $out->addScript('<script type="text/javascript">var GB_ROOT_DIR = "'.$wgServer.$wgScriptPath.'/extensions/UnifiedSearch/scripts/GreyBox/";</script>'."\n");
                    $out->addScript('<script type="text/javascript" src="'.$wgScriptPath . '/extensions/UnifiedSearch/scripts/GreyBox/AJS.js"></script>');
                    $out->addScript('<script type="text/javascript" src="'.$wgScriptPath . '/extensions/UnifiedSearch/scripts/GreyBox/AJS_fx.js"></script>');
                    $out->addScript('<script type="text/javascript" src="'.$wgScriptPath . '/extensions/UnifiedSearch/scripts/GreyBox/gb_scripts.js"></script>');
                    // add GreyBox
                    return true;
}

/**
 * Initializes PermissionACL extension
 *
 * @return unknown
 */
function wfUSSetupExtension() {
	global $wgAutoloadClasses, $wgSpecialPages, $wgScriptPath, $wgHooks, $wgSpecialPageGroups,
	$usgAllNamespaces;
	if (!isset($wgAdvancedSearchHighlighting)) $wgAdvancedSearchHighlighting = true;
	$wgHooks['BeforePageDisplay'][]='wfUSAddHeader';
	wfUSInitUserMessages();
	wfUSInitContentMessages();
	$dir = 'extensions/UnifiedSearch/';
	global $smwgHaloIP;
	$wgAutoloadClasses['USDBHelper'] = $dir . 'storage/US_DBHelper.php';
	$wgAutoloadClasses['USStore'] = $dir . 'storage/US_Store.php';
	$wgAutoloadClasses['SmithWaterman'] = $dir . 'includes/SmithWaterman.php';
	$wgAutoloadClasses['SKOSVocabulary'] = $dir . 'includes/SKOSVocabulary.php';
	$wgAutoloadClasses['USSpecialPage'] = $dir . 'includes/UnifiedSearchSpecialPage.php';
	$wgAutoloadClasses['UnifiedSearchResultPrinter'] = $dir . 'includes/UnifiedSearchResultPrinter.php';
	$wgAutoloadClasses['UnifiedSearchResult'] = $dir . 'includes/UnifiedSearchResultPrinter.php';
	$wgAutoloadClasses['UnifiedSearchStatistics'] = $dir . 'includes/UnifiedSearchStatistics.php';

	if (file_exists($dir . 'SKOSExpander.php')) {
		$wgAutoloadClasses['SKOSExpander'] = $dir . 'includes/SKOSExpander.php';
	}
	$wgAutoloadClasses['QueryExpander'] = $dir . 'includes/QueryExpander.php';
	$wgAutoloadClasses['LuceneSearch'] = $dir . 'MWSearch/MWSearch_body.php';
	$wgAutoloadClasses['LuceneResult'] = $dir . 'MWSearch/MWSearch_body.php';
	$wgAutoloadClasses['LuceneSearchSet'] = $dir . 'MWSearch/MWSearch_body.php';

	$wgSpecialPages['UnifiedSearchStatistics'] = array('SMWSpecialPage','UnifiedSearchStatistics', 'smwfDoSpecialUSSearch', $dir . 'includes/UnifiedSearchStatistics.php');
	//$wgSpecialPageGroups['UnifiedSearchStatistics'] = 'maintenance';

	$wgSpecialPages['Search'] = array('USSpecialPage');

	// use default namespaces unless explicitly specified
	if (!isset($usgAllNamespaces)) {
		$usgAllNamespaces = array(NS_MAIN => "smw_plus_instances_icon_16x16.png",
		NS_CATEGORY => "smw_plus_category_icon_16x16.png",
		SMW_NS_PROPERTY => "smw_plus_property_icon_16x16.png",
		NS_TEMPLATE => "smw_plus_template_icon_16x16.png",
		NS_HELP => "smw_plus_help_icon_16x16.png",
		NS_IMAGE => "smw_plus_image_icon_16x16.png");

		// check Multimedia namespaces from MIME-type extension and add if existing
		if (defined("NS_AUDIO")) $usgAllNamespaces[NS_AUDIO] = "smw_plus_music_icon_16x16.png";
		if (defined("NS_VIDEO")) $usgAllNamespaces[NS_VIDEO] = "smw_plus_video_icon_16x16.png";
		if (defined("NS_PDF")) $usgAllNamespaces[NS_PDF] = "smw_plus_pdf_icon_16x16.png";
		if (defined("NS_DOCUMENT")) $usgAllNamespaces[NS_DOCUMENT] = "smw_plus_document_icon_16x16.png";
	}
	return true;
}

/**
 * Registers ACL messages.
 */
function wfUSInitUserMessages() {
	global $wgMessageCache, $wgLang, $IP;

	$usLangClass = 'US_Language' . str_replace( '-', '_', ucfirst( $wgLang->getCode() ) );

	if (file_exists($IP.'/extensions/UnifiedSearch/languages/'. $usLangClass . '.php')) {
		include_once('extensions/UnifiedSearch/languages/'. $usLangClass . '.php' );
	}
	// fallback if language not supported
	if ( !class_exists($usLangClass)) {
		include_once('extensions/UnifiedSearch/languages/US_LanguageEn.php' );
		$aclgHaloLang = new US_LanguageEn();
	} else {
		$aclgHaloLang = new $usLangClass();
	}
	$wgMessageCache->addMessages($aclgHaloLang->us_userMessages, $wgLang->getCode());


}

function wfUSInitContentMessages() {
	global $wgMessageCache, $wgLanguageCode, $IP;
	$usLangClass = 'US_Language' . str_replace( '-', '_', ucfirst( $wgLanguageCode) );
	if (file_exists($IP.'/extensions/UnifiedSearch/languages/'. $usLangClass . '.php')) {
		include_once($IP.'/extensions/UnifiedSearch/languages/'. $usLangClass . '.php' );
	}
	// fallback if language not supported
	if ( !class_exists($usLangClass)) {
		include_once('extensions/UnifiedSearch/languages/US_LanguageEn.php' );
		$aclgHaloLang = new US_LanguageEn();
	} else {
		$aclgHaloLang = new $usLangClass();
	}

	$wgMessageCache->addMessages($aclgHaloLang->us_contentMessages, $wgLanguageCode);

}

/**
 * Creates necessary ontology elements (SKOS)
 *
 */
function wfUSInitialize($onlyTables) {
	global $usgSKOSExpansion;
	if (!$onlyTables && isset($usgSKOSExpansion) && $usgSKOSExpansion === true) {
		wfUSInitializeSKOSOntology();
	}
	wfUSInitializeTables();
	return true;
}

function wfUSInitializeTables() {
	global $IP;
	require_once "$IP/extensions/UnifiedSearch/storage/US_StoreSQL.php";
	USStore::getStore()->setup(true);
}

function wfUSDeInitializeTables() {
	global $IP;
	require_once "$IP/extensions/UnifiedSearch/storage/US_StoreSQL.php";
	USStore::getStore()->drop(true);
}

function wfUSInitializeSKOSOntology() {
	global $smwgContLang, $smwgHaloContLang;
	$verbose = true;
	print ("Creating predefined SKOS properties...\n");
	foreach(SKOSVocabulary::$ALL as $id => $page) {
		if ($page instanceof Title) {
			$t = $page;
			$name = $t->getText();
			$text = "";
		} else if ($page instanceof SMWPropertyValue) {
			$t = Title::newFromText($page->getXSDValue(), SMW_NS_PROPERTY);
			$name = $t->getText();
			$propertyLabels = $smwgContLang->getPropertyLabels();
			$namespaces = $smwgContLang->getNamespaces();
			$datatypeLabels = $smwgContLang->getDatatypeLabels();
			$haloSchemaProperties = $smwgHaloContLang->getSpecialSchemaPropertyArray();
			$text = "\n\n[[".$propertyLabels['_TYPE']."::".$namespaces[SMW_NS_TYPE].":".$datatypeLabels[SKOSVocabulary::$TYPES[$id]]."]]";
			$text .= "\n\n[[".$haloSchemaProperties[SMW_SSP_HAS_DOMAIN_AND_RANGE_HINT]."::".SKOSVocabulary::$ALL['us_skos_term']->getPrefixedText()."]]";
		}

		$article = new Article($t);
		if (!$t->exists()) {
			$article->insertNewArticle($text, "", false, false);
			print ("   ... Create page ".$t->getNsText().":".$t->getText()."...\n");
		} else {
			// save article again. Necessary when storage implementation has switched.
			$rev = Revision::newFromTitle($t);
			$article->doEdit($rev->getRawText(), $rev->getRawComment(), EDIT_UPDATE | EDIT_FORCE_BOT);
			print ("   ... re-saved page ".$t->getNsText().":".$t->getText().".\n");
		}
	}
}

/**
 * Get HTML body of wiki article and highlight terms if provided
 * Ajax callback function
 */
function smwf_ca_GetHTMLBody($page) {
	global $smwgHaloScriptPath, $wgStylePath, $wgUser, $wgDefaultSkin;
	global $wgServer, $wgTitle, $wgParser;

	// add colors for colorization of more terms
	$color = array("#00FF00", "#00FFFF", "#FFFF00");
	// set standard color for terms >count($color) terms.
	$wgDefaultColor = "#00FF00";

	// fetch MediaWiki page
	if (is_object($wgParser)) $psr =& $wgParser; else $psr = new Parser;
	$opt = ParserOptions::newFromUser($wgUser);
	$title = Title::newFromText($page);
	$revision = Revision::newFromTitle($title );
	if ($revision) {
		$article = new Article($title);
		$out = $psr->parse($revision->getText(),$wgTitle,$opt,true,true);
	} else {
		return "Error: Could not fetch revision";
	}

	// fetch current skin
	$skin = $wgUser->getSkin();
	$skinName = $wgUser !== NULL ? $wgUser->getSkin()->getSkinName() : $wgDefaultSkin;

	// add main.css to later shown html page
	$head = '<head>';
	$head .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
	$head .= '<style type="text/css" media="screen,projection"> @import "'. $wgStylePath .'/'. $skinName .'/main.css?164";</style>';
	$head .= '</head>';

	// fetch main HTML content of page
	$htmlcontent = $out->getText();

	// finally highlight search terms but leave links as they are
	$numargs = func_num_args();
	$arg_list = func_get_args();
	if ($numargs > 1) {
		for ($i = 1; $i < $numargs; $i++) {
			$currcolor = $color[$i-1] !== NULL ? $color[$i-1] : $wgDefaultColor;
			$replacement_phrase = "<span style=\'background-color: ". $currcolor . ";\'>".$arg_list[$i]."</span>";
			$htmlcontent = preg_replace("/(>|^)([^<]+)(?=<|$)/iesx", "'\\1'.str_ireplace('$arg_list[$i]',
			'$replacement_phrase', '\\2')", $htmlcontent);
		}
	}

	return $head.$htmlcontent;
}

/*
 * Creates or updates additional tables needed by the Synsets functions.
 * Called from SMW when admin re-initializes tables
 */
function smwfSynsetsInitializeTables() {

	global $IP;
	require_once($IP."/extensions/UnifiedSearch/synsets/SMW_Synsets.php");
	$s = new Synsets();
	$s->setup();

	return true;
}

function smwfSynsetsDeInitializeTables() {

    global $IP;
    require_once($IP."/extensions/UnifiedSearch/synsets/SMW_Synsets.php");
    $s = new Synsets();
    $s->drop();

    return true;
} 

?>