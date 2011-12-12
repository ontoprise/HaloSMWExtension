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
		classes : {}
	};
}
if (typeof window.TreeView.classes == "undefined") {
	window.TreeView.classes = {};
}

/**
 * @class TreeViewWidget
 * This widget captures the request of its SOLR manager and updates the
 * TreeView accordingly. It is used on the special page of the Faceted Search.
 * 
 */
(function ($) {
	
TreeView.classes.TreeViewWidget = AjaxSolr.AbstractWidget.extend({

	// Create a SOLR manager for updating the tree
	solrManager : TreeView.classes.SolrTreeViewManager(),
	
	/**
	 * Capture the request of the search page's SOLR manager and update the
	 * the tree accordingly but only if the tree definition toolbox is visible.
	 */
	beforeRequest: function() {
		if ($('#tv_treeview_definition_toolbox').is(':visible')) {
			this.solrManager.setTreeProperty($('#treeViewProperty').val());
			this.solrManager.updateTree();
		}
	},
	
	init: function (param) {
		this.solrManager.addWidget(
			new TreeView.classes.JSTreeWidget(
					{
						id : 'tv_tree',
						treeDomID: '#treeViewTree'
					}));
		this.solrManager.addWidget(
			new TreeView.classes.TreeParserFunctionWidget(
					{
						id : 'tv_parser_function',
						target: '#treeViewParserFunction',
						treeManager: this.solrManager
					}));
		this.solrManager.setFilterStore(this.manager.store);
	}
	
});

})(jQuery);
