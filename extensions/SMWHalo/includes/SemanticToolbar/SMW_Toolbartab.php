<?php
/*
 * Copyright (C) Vulcan Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program.If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 * The semantic toolbar will only work with "ontoskin", the special MediaWiki
 * skin provided in the skin folder of this extension.
 * 
 * @file
 * @ingroup SMWHalo
 * @ingroup SMWHaloSemanticToolbar
 * 
 * @author Robert Ulrich
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

