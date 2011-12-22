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
 * @file
 * @ingroup TreeView
 *
 * This file contains the class TVFacetedSearchExtension that extends the
 * Faceted Search special page.
 * 
 * @author Thomas Schweitzer
 * Date: 07.12.2011
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the TreeView extension. It is not a valid entry point.\n" );
}

 //--- Includes ---

/**
 * The class extends the user interface on the Faceted Search special page.
 * It adds a tree view that reacts to the current filters of the faceted search.
 * 
 * @author Thomas Schweitzer
 * 
 */
class TVFacetedSearchExtension  {
	
	//--- Constants ---
	
	const FS_TREE_VIEW_DEFINITION_HTML = <<<HTML
<div id="tv_treeview_definition_toolbox" style="display:none">
	<div>
		<!-- Header with toggle buttons -->
		<img class="tvToggleSectionButton"
			 id="tv_show_define_tree" 
			 title="{{tv_define_tree}}" 
			 src="{tvgScriptPath}/skin/images/right.png" 
			 style="display: none;">
		<img class="tvToggleSectionButton"
			 style="display: inline;" 
			 id="tv_hide_define_tree" 
			 title="{{tv_hide_define_tree}}" 
			 src="{tvgScriptPath}/skin/images/down.png">
		{{tv_define_tree}}
		<hr class="xfsSeparatorLine">		
	</div>	
	<div id="tree_view_widget">
		<div class="tvDefineTreeView">
			<!-- Property input field -->
			{{tv_property}}
			<input id="treeViewProperty" 
					name="treeViewProperty" 
					type="text" 
					title="{{tv_property_help}}"/>
			<button id="treeViewPropertyButton" 
					type="button">{{tv_property_apply}}</button>
			
			<!-- The treeview -->
			<div id="treeViewTree" class="tvDefineTreeViewInner"></div>
			
			<!-- The parser function that will generate the current tree -->
			<hr class="xfsSeparatorLine">		
			<!-- Toggle buttons -->
			<img class="tvToggleSectionButton"
				 id="tv_show_parser_function" 
				 title="{{tv_show_parser_function}}" 
				 src="{tvgScriptPath}/skin/images/right.png" 
				 style="display: inline;">
			<img class="tvToggleSectionButton"
				 style="display: none;" 
				 id="tv_hide_parser_function" 
				 title="{{tv_hide_parser_function}}" 
				 src="{tvgScriptPath}/skin/images/down.png">
			{{tv_parser_function}}
			
			<div id="treeViewParserFunction" 
				class="tvParserFunctionBox" 
				style="display: none;"
				title="{{tv_copy_parser_function}}">
			</div>
		</div>	
		<hr class="xfsSeparatorLine">	
	</div>	
</div>
HTML;

	const FS_BOTTOM_MENU_HTML = <<<HTML
<span>
	&nbsp;|&nbsp;
	<a id="tv_define_tree_link" style="cursor: pointer">{{tv_define_tree}}</a>
	<a id="tv_hide_treeview_toolbox_link" style="display:none; cursor: pointer">{{tv_hide_treeview_toolbox}}</a>
</span>
HTML;
		
	//--- Private fields ---
	
	/**
	 * Constructor for TVFacetedSearchExtension
	 *
	 * @param type $param
	 * 		Name of the notification
	 */		
	function __construct() {
	}
	

	//--- getter/setter ---
	
	//--- Public methods ---
	
	/**
	 * Adds HTML to the top area of the Faceted Search special page.
	 * 
	 * @param {String} $html
	 * 		This string is augmented with HTML
	 */
	public static function injectTreeViewDefinition(&$html) {
		// Augment the HTML
		$html .= self::FS_TREE_VIEW_DEFINITION_HTML;
		// replace the global variable {tvgScriptPath} in the HTML
		global $tvgScriptPath;
		$html = str_replace("{tvgScriptPath}", $tvgScriptPath, $html);
		// Replace the language strings
		$html = TreeViewExtension::replaceLanguageStrings($html);

		return true;
	}	
	
	/**
	 * Adds HTML to the bootom menu of the Faceted Search special page.
	 * 
	 * @param {String} $html
	 * 		This string is augmented with HTML
	 */
	public static function injectBottomMenu(&$html) {
		// Augment the HTML
		$html .= self::FS_BOTTOM_MENU_HTML;
		// replace the global variable {tvgScriptPath} in the HTML
		global $tvgScriptPath;
		$html = str_replace("{tvgScriptPath}", $tvgScriptPath, $html);
		// Replace the language strings
		$html = TreeViewExtension::replaceLanguageStrings($html);
			
		return true;
	}	
	
	/**
	 * This function is called from the hook FacetedSearchExtensionAddResources.
	 * It adds the resource modules for this extension.
	 */
	public static function addResources() {
		// Add the JavaScript modules
		global $wgOut;
		$wgOut->addModules('ext.FacetedSearchTreeView.tree');
		
		return true;
	}

	//--- Private methods ---
	
}