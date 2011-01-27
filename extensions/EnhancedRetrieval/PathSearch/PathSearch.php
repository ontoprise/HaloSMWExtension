<?php
/**
 * @file
 * @ingroup EnhancedRetrievalPathSearch
 * 
 * @defgroup EnhancedRetrievalPathSearch Enhanced retrieval path search
 * @ingroup EnhancedRetrieval
 */
// Setup for PathSearch (part or the Enhanced Retrieval extension) 
 

$wgExtensionFunctions[] = 'wfUSPathSearchSetup';

/**
 * Setup for PathSearch
 * - classes must be imported
 * - user messages must be initialized
 * - css and javascript files must be included in header
 *
 * @return boolean true
 */
function wfUSPathSearchSetup() {
	global $wgAutoloadClasses, $wgHooks, 
	       $usgAllNamespaces;
    $wgHooks['BeforePageDisplay'][]='wfUSPathSearchAddHeader';
   	wfUSPathSearchInitMessages();
	$dir = dirname(__FILE__);
	$wgAutoloadClasses['PathSearchCore'] = $dir . '/PathSearchCore.php';
	$wgAutoloadClasses['PSC_Path'] = $dir . '/PSC_Path.php';
    $wgAutoloadClasses['PSC_WikiData'] = $dir . '/PSC_WikiData.php';
	return true;
}
 
/**
 * Add javascripts and css files for PathSearch (done when needed only)
 *
 * @param unknown_type $out
 * @return unknown
 */
function wfUSPathSearchAddHeader(& $out) {
	global $wgScriptPath, $wgRequest, $wgTitle;
    $action = $wgRequest->getVal('action');
    if ($action == 'ajax' || !is_null($wgTitle) && SpecialPage::getTitleFor('Search')->equals($wgTitle)) {
        $out->addStyle($wgScriptPath . '/extensions/EnhancedRetrieval/skin/pathsearch.css', 'screen, projection');
        $out->addScript('<script type="text/javascript" src="'.$wgScriptPath . '/extensions/EnhancedRetrieval/scripts/pathsearch.js"></script>'."\n");
        $out->addScript('<script type="text/javascript">var US_PATHSEARCH_DIR="'.$wgScriptPath.'/extensions/EnhancedRetrieval/PathSearch";</script>'."\n");
    }
    return true;
}

/**
 * Initialize messages for PathSearch
 *
 */
function wfUSPathSearchInitMessages() {
	global $wgMessageCache, $wgLang, $IP;
    $usPath = dirname(__FILE__);

	$usLangClass = 'US_Language' . str_replace( '-', '_', ucfirst( $wgLang->getCode() ) );

	if (file_exists($usPath.'/../languages/'. $usLangClass . '.php')) {
		include_once($usPath.'/../languages/'. $usLangClass . '.php' );
	}
	// fallback if language not supported
	if ( !class_exists($usLangClass)) {
		include_once($usPath.'/../languages/US_LanguageEn.php' );
		$lang = new US_LanguageEn();
	} else {
		$lang = new $usLangClass();
	}
	$wgMessageCache->addMessages($lang->us_pathsearchMessages, $wgLang->getCode());

}
