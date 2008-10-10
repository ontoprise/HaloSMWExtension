<?php

/**
 * Include this script for WYSIWYG support
 */
if ( !defined( 'MEDIAWIKI' ) ) die;

global $wgHooks;
$wgHooks[ 'SkinTemplateTabs' ][] = 'smwfAddWYSIWYGTab';

if (array_key_exists('mode', $_REQUEST) || (array_key_exists('action', $_REQUEST) && array_key_exists('rs', $_REQUEST))) {
    if ($_REQUEST['mode'] == 'wysiwyg' || ($_REQUEST['action'] == 'ajax' && stripos($_REQUEST['rs'], 'wfSajax') === 0)) {
        require_once $IP . "/extensions/FCKeditor/FCKeditor.php";
    }
}
/**
 * Adds an action that refreshes the article, i.e. it purges the article from
 * the cache and thus refreshes the inline queries.
 */
function smwfAddWYSIWYGTab($obj, $content_actions) {
    global $wgUser, $wgTitle;
        $wwactive = $_REQUEST['action'] == 'edit' && $_REQUEST['mode'] == 'wysiwyg' ? 'selected' : false;
        $content_actions['wysiwyg'] = array(
            'class' => $wwactive,
            'text' => wfMsg('smw_wysiwyg'),
            'href' => $wgTitle->getLocalUrl( 'action=edit&mode=wysiwyg' )
        );
        
        // adjust edit tab
        $editactive = $_REQUEST['action'] == 'edit' && $_REQUEST['mode'] != 'wysiwyg' ? 'selected' : false;
        $content_actions['edit']['class'] = $editactive;
       
    
    return true; // always return true, in order not to stop MW's hook processing!
}
?>