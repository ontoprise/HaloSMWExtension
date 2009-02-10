<?php
/**
 * @author: Kai Kühn
 *
 * Created on: 27.01.2009
 */
if( !defined( 'MEDIAWIKI' ) ) {
	echo("This file is an extension to the MediaWiki software and cannot be used standalone.\n");
	die(1);
}

define('US_HIGH_TOLERANCE', 0);
define('US_LOWTOLERANCE', 1);
define('US_EXACTMATCH', 2);

$wgExtensionCredits['unifiedsearch'][] = array(
        'name' => 'Unified search',
        'author' => 'Kai Kühn',
        'url' => 'http://sourceforge.net/projects/halo-extension/',
        'description' => 'Combining a Lucene backend with a title search',
);

global $wgExtensionFunctions, $wgHooks, $wgAjaxExportList;;
$wgAjaxExportList[] = 'smwf_ca_GetHTMLBody';

// use SMW_AddScripts hook from SMWHalo to make sure that Prototype is available.
$wgHooks['SMW_AddScripts'][]='wfUSAddHeader';
$wgExtensionFunctions[] = 'wfUSSetupExtension';

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
	wfUSInitUserMessages();
	wfUSInitContentMessages();
	$dir = 'extensions/UnifiedSearch/';
	global $smwgHaloIP;
	$wgAutoloadClasses['SMWAdvRequestOptions'] = $smwgHaloIP . '/includes/SMW_DBHelper.php';
	$wgAutoloadClasses['USStore'] = $dir . 'storage/US_Store.php';

	$wgAutoloadClasses['SKOSVocabulary'] = $dir . 'SKOSVocabulary.php';
	$wgAutoloadClasses['USSpecialPage'] = $dir . 'UnifiedSearchSpecialPage.php';
	$wgAutoloadClasses['UnifiedSearchResultPrinter'] = $dir . 'UnifiedSearchResultPrinter.php';
	$wgAutoloadClasses['UnifiedSearchResult'] = $dir . 'UnifiedSearchResultPrinter.php';
	$wgAutoloadClasses['UnifiedSearchStatistics'] = $dir . 'UnifiedSearchStatistics.php';

	$wgAutoloadClasses['QueryExpander'] = $dir . 'QueryExpander.php';
	$wgAutoloadClasses['LuceneSearch'] = $dir . 'MWSearch/MWSearch_body.php';
	$wgAutoloadClasses['LuceneResult'] = $dir . 'MWSearch/MWSearch_body.php';
	$wgAutoloadClasses['LuceneSearchSet'] = $dir . 'MWSearch/MWSearch_body.php';

	$wgSpecialPages['UnifiedSearchStatistics'] = array('SMWSpecialPage','UnifiedSearchStatistics', 'smwfDoSpecialUSSearch', $dir . 'UnifiedSearchStatistics.php');
	//$wgSpecialPageGroups['UnifiedSearchStatistics'] = 'maintenance';

	$wgSpecialPages['Search'] = array('USSpecialPage');

	// use default namespaces unless explicitly specified
	if (!isset($usgAllNamespaces)) {
		$usgAllNamespaces = array(NS_MAIN => "instance.gif",
		                          NS_CATEGORY => "concept.gif", 
		                          SMW_NS_PROPERTY => "property.gif", 
		                          NS_TEMPLATE => "template.gif",
		                          NS_HELP => "help.png");

		// check Multimedia namespaces from MIME-type extension and add if existing
		if (defined("NS_AUDIO")) $usgAllNamespaces[NS_AUDIO] = "audio.jpg";
		if (defined("NS_VIDEO")) $usgAllNamespaces[NS_VIDEO] = "video.jpg";
		if (defined("NS_PDF")) $usgAllNamespaces[NS_PDF] = "pdf.gif";
		if (defined("NS_DOCUMENT")) $usgAllNamespaces[NS_DOCUMENT] = "doc.gif";
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
function wfUSInitialize() {
	wfUSInitializeSKOSOntology();
	wfUSInitializeTables();
	return true;
}

function wfUSInitializeTables() {
	USStore::getStore()->setup(true);
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
 * Get HTML body of wiki article
 * Ajax callback function
 */
function smwf_ca_GetHTMLBody($page) {
	global $smwgHaloScriptPath, $wgStylePath, $wgUser, $wgDefaultSkin;
	global $wgServer, $wgParser;

	$color = array("#00FF00", "#00FFFF", "#FFFF00");
	$wgDefaultColor = "#00FF00";

	if (is_object($wgParser)) $psr =& $wgParser; else $psr = new Parser;
	$opt = ParserOptions::newFromUser($wgUser);
	$title = Title::newFromText($page);
	$revision = Revision::newFromTitle($title );
	if ($revision) {
		$article = new Article($title);
		$out = $psr->parse($revision->getText(),$wgTitle,$opt,true,true);
	} else {
		return null;
	}

	$skin = $wgUser->getSkin();
	$skinName = $wgUser !== NULL ? $wgUser->getSkin()->getSkinName() : $wgDefaultSkin;

	// add main.css
	$head = '<head>';
	$head .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
	$head .= '<style type="text/css" media="screen,projection"> @import "'. $wgStylePath .'/'. $skinName .'/main.css?164";</style>';
	$head .= '</head>';

	$htmlcontent = $out->getText();

	// highlight search terms
	$numargs = func_num_args();
	$arg_list = func_get_args();
	if ($numargs > 1) {
		for ($i = 1; $i < $numargs; $i++) {
			$currcolor = $color[$i-1] !== NULL ? $color[$i-1] : $wgDefaultColor;
			$htmlcontent = str_ireplace($arg_list[$i], "<span style='background-color: ". $currcolor . ";'>".$arg_list[$i]."</span>", $htmlcontent);
		}
	}

	return $head.$htmlcontent;
}


?>