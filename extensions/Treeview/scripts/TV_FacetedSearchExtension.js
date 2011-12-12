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
 * @ingroup TreeViewScripts
 * @author: Thomas Schweitzer
 */

if (typeof window.TreeView == "undefined") {
// Define the TreeView module	
	window.TreeView = { 
		classes : {},
		singleton: {}
	};
}
if (typeof window.TreeView.classes == "undefined") {
	window.TreeView.classes = {};
}

if (typeof window.TreeView.singleton == "undefined") {
	window.TreeView.singleton = {};
}

/**
 * @class FacetedSearchExtension
 * The TreeView add additional functionality to the FacetedSearch special page.
 * This class hooks into the SOLR manager of the FacetedSearch and takes control
 * of the GUI extension for th TreeView.
 */
TreeView.classes.FacetedSearchExtension = function(facetedSearch) {
	var $ = jQuery;
	
	//--- Private members ---

	// The instance of this object
	var that = {};

	/**
	 * This function sets up the extension and bind to the event 'FSAddWidgets'
	 * of the Faceted Search singleton. This event is triggered when all widgets
	 * are registered.
	 * 
	 * @param {FacetedSearch} fs
	 * 		The instance of FacetedSearch
	 */
	function construct(fs) {
		$(fs).bind('FSAddWidgets', that.onAddWidget, fs);
		$(document).ready(function () {
			setupEvents();
		});
		
	};
	
	/**
	 * This jQuery event is triggered by the Faceted Search singleton when it
	 * requests extensions to provided their widgets. The singleton is passed
	 * as 'this'. 
	 * The function adds the TreeView-widget.
	 * @param {Object} event
	 */
	function onAddWidget(event) {
		// this is the FacetedSearch instance
		this.addWidget(new TreeView.classes.TreeViewWidget({
			id: 'treeViewWidget',
			target: '#tree_view_widget'
		}));
		
	}
	that.onAddWidget = onAddWidget;
	
	/**
	 * Initializes the jQuery events for this extension
	 */
	function setupEvents() {
		// Event for opening / closing the treeview definition toolbox
		$('#tv_define_tree_link').click(function (){
			$(this).toggle();
			$(this).next().toggle();
			$('#tv_treeview_definition_toolbox').toggle('slow');
			// Update the tree
			FacetedSearch.singleton.FacetedSearchInstance.updateSearchResults();
			return false;
		});
		$('#tv_hide_treeview_toolbox_link').click(function (){
			$(this).toggle();
			$(this).prev().toggle();
			$('#tv_treeview_definition_toolbox').toggle('slow');
			return false;
		});
		
		// Events for showing / hiding the area where the tree view is defined.
		$('#tv_show_define_tree').click(function (){
			$(this).toggle();
			$(this).next().toggle();
			$('#tree_view_widget').toggle('slow');
			return false;
		});
		$('#tv_hide_define_tree').click(function (){
			$(this).toggle();
			$(this).prev().toggle();
			$('#tree_view_widget').toggle('slow');
			return false;
		});
		
		// Events for showing / hiding the parser function
		$('#tv_show_parser_function').click(function (){
			$(this).toggle();
			$(this).next().toggle();
			$('#treeViewParserFunction').toggle('slow');
			return false;
		});
		$('#tv_hide_parser_function').click(function (){
			$(this).toggle();
			$(this).prev().toggle();
			$('#treeViewParserFunction').toggle('slow');
			return false;
		});
		
		// Event for the Apply button
		$('#treeViewPropertyButton').click(function (){
			// Update the tree
			FacetedSearch.singleton.FacetedSearchInstance.updateSearchResults();
			return false;
		});
		
		$('#treeViewProperty').keyup(function (event) {
			if (event.which === 13) {
				// Return pressed => Update the tree
				FacetedSearch.singleton.FacetedSearchInstance.updateSearchResults();
			}
		});
		
	}
	
	construct(facetedSearch);
	return that;

}

if (typeof FacetedSearch.singleton.FacetedSearchInstance !== 'undefined') {
	TreeView.singleton.FacetedSearchExtension = 
		TreeView.classes.FacetedSearchExtension(FacetedSearch.singleton.FacetedSearchInstance);
}