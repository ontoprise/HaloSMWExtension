<?php
 
if( !defined( 'MEDIAWIKI' ) ) {
    echo("This file is an extension to the MediaWiki software and cannot be used standalone.\n");
    die(1);
}


$wgExtensionCredits['other'][] = array(
        'name' => 'Unified search',
        'author' => 'Kai Kühn',
        'url' => 'http://sourceforge.net/projects/halo-extension/',
        'description' => 'Unifies the search using a lucene backend',
);

global $wgExtensionFunctions, $wgHooks;

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
    global $wgScriptPath;
    $out->addLink(array(
                    'rel'   => 'stylesheet',
                    'type'  => 'text/css',
                    'media' => 'screen, projection',
                    'href'  => $wgScriptPath . '/extensions/UnifiedSearch/skin/unified_search.css'
                ));
    $out->addScript('<script type="text/javascript" src="'.$wgScriptPath . '/extensions/UnifiedSearch/scripts/unified_search.js"></script>');
    return true;
}

/**
 * Initializes PermissionACL extension 
 *
 * @return unknown
 */
function wfUSSetupExtension() {
    global $wgAutoloadClasses, $wgSpecialPages, $wgScriptPath;
    wfUSInitMessages();
    $dir = 'extensions/UnifiedSearch/MWSearch';
    $wgAutoloadClasses['USSpecialPage'] = $dir . 'UnifiedSearchSpecialPage.php';
    $wgAutoloadClasses['LuceneSearch'] = $dir . 'MWSearch_body.php';
    $wgAutoloadClasses['LuceneResult'] = $dir . 'MWSearch_body.php';
    $wgAutoloadClasses['LuceneSearchSet'] = $dir . 'MWSearch_body.php';
    $wgSpecialPages['Search'] = array('USSpecialPage');
    return true;
}

/**
 * Registers ACL messages.
 */
function wfUSInitMessages() {
        global $wgMessageCache, $wgLang;

        $usLangClass = 'US_Language' . str_replace( '-', '_', ucfirst( $wgLang->getCode() ) );
    
        if (file_exists('extensions/UnifiedSearch/languages/'. $usLangClass . '.php')) {
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


?>