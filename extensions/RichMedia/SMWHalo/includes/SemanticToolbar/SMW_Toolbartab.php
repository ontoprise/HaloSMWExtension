<?php
/**
 * The semantic toolbar will only work with "ontoskin", the special MediaWiki
 * skin provided in the skin folder of this extension.
 * 
 * @file
 * @ingroup SMWHalo
 * @ingroup SMWHaloSemanticToolbar
 * 
 * @author Robert Ulrich, ontoprise
 */

global $wgAjaxExportList;

$wgAjaxExportList[] = 'setToolbarHeader';
$wgAjaxExportList[] = 'setToolbarBody';

global $wgHooks;
$wgHooks['SkinTemplateContentActions'][] = 'AddSemanticToolbarTab';

/**
 Adds
 TODO: document
 */
function AddSemanticToolbarTab ($content_actions) {
	$main_action = array();
	$main_action['main'] = array(
	   'class' => false,    //if the tab should be highlighted
	   'text' => 'semantic toolbar',     //name of the tab
	   'href' => 'javascript:smw_togglemenuvisibility()'  //show/hide semantic tool bar
	);
	$content_actions = array_merge( $content_actions, $main_action);   //add a new action
}

