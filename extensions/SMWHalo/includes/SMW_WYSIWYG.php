<?php

/**
 * Include this script for WYSIWYG support
 */

global $wgHooks;
$wgHooks[ 'SkinTemplateTabs' ][] = 'smwfAddWYSIWYGTab';

if ($_REQUEST['mode'] == 'wysiwyg' || ($_REQUEST['action'] == 'ajax' && stripos($_REQUEST['rs'], 'wfSajax') === 0)) {
    require_once $IP . "/extensions/FCKeditor/FCKeditor.php";
}
/**
 * Adds an action that refreshes the article, i.e. it purges the article from
 * the cache and thus refreshes the inline queries.
 */
function smwfAddWYSIWYGTab($obj, $content_actions) {
    global $wgUser, $wgTitle;
    
        $content_actions['wysiwyg'] = array(
            'class' => false,
            'text' => wfMsg('smw_wysiwyg'),
            'href' => $wgTitle->getLocalUrl( 'action=edit&mode=wysiwyg' )
        );
    
    return true; // always return true, in order not to stop MW's hook processing!
}
?>