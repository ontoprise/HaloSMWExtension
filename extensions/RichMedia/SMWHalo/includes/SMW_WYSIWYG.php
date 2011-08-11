<?php

/**
 * @file
 * @ingroup SMWHaloMiscellaneous
 * 
 * Include this script for WYSIWYG support
 */
if ( !defined( 'MEDIAWIKI' ) ) die;

define('SMWHALO_WYSIWYG_EDITOR_WRAPPER', 'Wrapper for WYSIWYG extension');

global $wgHooks, $wgAvailableRights;
$wgHooks[ 'SkinTemplateTabs' ][] = 'smwfAddWYSIWYGTab';
$wgHooks[ 'LanguageGetMagic' ][] = 'smwfAddMagigWordNoricheditor';
$wgHooks[ 'ParserBeforeInternalParse' ][] = 'smwfRemoveNoricheditor';
$wgAvailableRights[] = 'wysiwyg';

// do not load WW if and only if in plain edit mode
$plainEditmode = (array_key_exists('action', $_REQUEST) && ($_REQUEST['action'] == 'edit' || $_REQUEST['action'] == 'submit')) &&
!(array_key_exists('mode', $_REQUEST) && $_REQUEST['mode'] == 'wysiwyg');
if (!$plainEditmode) {
	require_once $IP . "/extensions/FCKeditor/FCKeditor.php";
}

// check if the Semantic formas are installed and if the request is one of these
// Special pages (request was from the template picker in the FCKeditor)
if (defined('SF_VERSION') && in_array('REQUEST_URI', $_SERVER) &&
    (strpos($_SERVER['REQUEST_URI'], ':AddDataEmbedded') !== false ||
     strpos($_SERVER['REQUEST_URI'], ':EditDataEmbedded') !== false
    )
   ) {
    require_once $IP . "/extensions/FCKeditor/specials/SF_AddDataEmbedded.php";
    require_once $IP . "/extensions/FCKeditor/specials/SF_EditDataEmbedded.php";
}

/**
 * Adds an action that refreshes the article, i.e. it purges the article from
 * the cache and thus refreshes the inline queries.
 */
function smwfAddWYSIWYGTab($obj, $content_actions) {
	global $wgUser, $wgTitle;
	
	if (!$wgUser->isAllowed('wysiwyg') || $wgUser->getSkin()->getSkinName() == "ontoskin3") return true;
	$wwactive = array_key_exists('action', $_REQUEST) && $_REQUEST['action'] == 'edit' && array_key_exists('mode', $_REQUEST) && $_REQUEST['mode'] == 'wysiwyg' ? 'selected' : false;
	$content_actions['wysiwyg'] = array(
            'class' => $wwactive,
            'text' => wfMsg('smw_wysiwyg'),
            'href' => $wgTitle->getLocalUrl( 'action=edit&mode=wysiwyg' )
	);

	// adjust edit tab
	$editactive = array_key_exists('action', $_REQUEST) && $_REQUEST['action'] == 'edit' && array_key_exists('mode', $_REQUEST) && $_REQUEST['mode'] != 'wysiwyg' ? 'selected' : false;
	$content_actions['edit']['class'] = $editactive;
	 

	return true; // always return true, in order not to stop MW's hook processing!
}

/**
 * adds __NORICHEDITOR__ to the list of magic words
 */
 function smwfAddMagigWordNoricheditor(&$magicWords, $lang) {
    $magicWords['NORICHEDITOR'] = array( 0, '__NORICHEDITOR__' );
    return true;
 }

 /**
 * removes __NORICHEDITOR__ in the wiki text for normal parsing
 */
 function smwfRemoveNoricheditor(&$parser, &$text, &$strip_state) {
    MagicWord::get( 'NORICHEDITOR' )->matchAndRemove( $text );
    return true;
 }