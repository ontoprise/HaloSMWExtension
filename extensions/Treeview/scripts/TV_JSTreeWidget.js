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

if (typeof TreeView == "undefined") {
// Define the TreeView module	
	TreeView = { 
		classes : {}
	};
}
if (typeof TreeView.classes == "undefined") {
	TreeView.classes = {};
}

/**
 * @class JSTreeWidget
 * 
 * This widget extends AjaxSolr.AbstractWidget.
 * It renders data given as JSON as tree with the jstree library.
 * This widget must be attached to a TreeView.classes.SolrTreeViewManager
 * 
 * The widget can be created with two kinds of configurations:
 * 1. Display the result as a whole tree:
 *    new TreeView.classes.JSTreeWidget({treeDomID: '#id of a dom element'})
 * 2. Attach the tree as a child node to an existing tree:
 *    new TreeView.classes.JSTreeWidget({tree: tree, parentNode: node}))
 *    tree is the jsTree object for the tree.
 *    parentNode is a jsTree object for the node whose children will be replaced.
 * 
 */
(function ($) {
	
TreeView.classes.JSTreeWidget = AjaxSolr.AbstractWidget.extend({

	/**
	 * This function is called after the result of a request was converted into
	 * a tree structure.
	 * 
	 * @param {Object} json
	 * 		Object structure of the tree suitable for jsTree
	 */
	afterRequest: function(json) {
		if (typeof this.treeDomID === 'string') {
			jQuery.jstree._themes = mw.config.get('tvgTreeThemes');
			jQuery(this.treeDomID).jstree({
				'json_data': json,
				'themes' : { "theme" : 'default' },
				'plugins': ['themes', 'json_data']
			});
		} else if (this.tree && this.parentNode){
			// first delete all children of the parent
			this.deleteChildrenNodes(this.tree, this.parentNode);
			// then add the new nodes
			this.addChildNode(this.tree, this.parentNode, json.data);
			this.tree.open_node(this.parentNode, false, true);
		}
		
	},
	
	/**
	 * Adds a child node with children. Requires the JSON data plugin.
	 * 
	 * @param {object} tree 
	 * 		A jsTree object, e.g. treeObj =	$.jstree._reference("demo");
	 * @param {object} parentNode 
	 * 		The DOM parent node to which the child node will be added
	 * @param {object} json 
	 * 		The new node's configuration, as defined by the JSON data plugin
	 **/
	addChildNode: function (tree, parentNode, json){
		var obj = tree._parse_json(json);
		parentNode.append(obj);
		tree.clean_node(parentNode);
	},
	
	/**
	 * Deletes the children of the parentNode
	 * 
	 * @param {object} tree 
	 * 		A jsTree object, e.g. treeObj =	$.jstree._reference("demo");
	 * @param {object} parentNode 
	 * 		The DOM parent node whose children will be deleted
	 **/
	deleteChildrenNodes: function (tree, parentNode){
		tree._get_children(parentNode)
		    .each(function(idx, child) {
				tree.delete_node(child);
			});
	}
	
});

})(jQuery);
