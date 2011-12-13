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
 * @class SolrTreeViewManager
 * 
 * This class sends requests for the tree to SOLR and processes the results.
 * 
 */
TreeView.classes.SolrTreeViewManager = function(){
	var $ = jQuery;
	
	//--- Constants ---
	
	var PROPERTY_NAME_PATTERN = "smwh_{property}_t";

	
	//--- Private members ---
	// {String} The name of the property that is used to generate the hierarchical
	//          structure of the tree.
	var mTreeProperty;
	// {String} The name of the solr field that corresponds to the tree property
	var mTreePropertyField;
	
	// {AjaxSolr.FSManager}
	// This SOLR manager retrieves all pages the match the property for the
	// tree structure.
	var mSolrManagerAllTreePages;
	
	// {AjaxSolr.FSManager}
	// This SOLR manager retrieves all pages the match the property for the
	// tree structure and additional filters.
	var mSolrManagerForFilter;
	
	// {AjaxSolr.ParameterStore}
	// This store is used to get further filter constraints and the query
	// that is applied on the elements of the tree.
	var mFilterStore;
	
	// {Object}
	// This object maps for all pages to their parent node in the tree 
	var mParentMap;
	
	// The instance of this object
	var that = {};

	//--- Getter / setter
	
	/**
	 * Sets the property that defines the hierarchical structure of the tree.
	 * Spaces are replaced by underscores. The property name is stored as
	 * SOLR field with the corresponding naming convention.
	 * @param {String} tp
	 * 		The name of the tree property as used in the wiki without 'Property:'
	 * 		prefix.
	 */
	function setTreeProperty(tp) {
		mTreeProperty = tp;
		tp = tp.replace(/ /g, '_');
		mTreePropertyField = PROPERTY_NAME_PATTERN.replace(/{property}/g, tp);
	}
	that.setTreeProperty = setTreeProperty;
	
	/**
	 * Initializes the filter store with the given serialized SOLR query.
	 * 
	 * @param {String} query
	 * 		The SOLR query string
	 */
	function setSolrQuery(query) {
		mFilterStore = new AjaxSolr.ParameterStore();
		mFilterStore.parseString(query);
	}
	that.setSolrQuery = setSolrQuery;
	
	function getTreeProperty() { return mTreeProperty; }
	that.getTreeProperty = getTreeProperty;
	
	function setFilterStore(fs) {mFilterStore = fs; }
	that.setFilterStore = setFilterStore;
	
	//--- Constructor ---
	
	/**
	 * Constructor of this class.
	 * 
	 */
	function construct() {
		setupManagers();
	}
	
	//--- Public Methods ---
	
	/**
	 * Adds the given widget to this manager. 
	 * An object with a tree structure is passed to the method 'afterRequest'
	 * of this widget.
	 * 
	 * @param {Object} widget
	 * 		The widget to add.
	 */
	function addWidget(widget) {
		mSolrManagerForFilter.addWidget(widget);
	}
	that.addWidget = addWidget;
	
	/**
	 * @public
	 * Updates the tree according to the current query settings and the property
	 * the defines the hierarchical structure of the tree.
	 * You must set a property before this method is called. See setTreeProperty().
	 */
	function updateTree() {
		requestAllMatchingTreePages();
	}
	that.updateTree = updateTree;
	
	
	//--- Private Methods ---
	
	
	/**
	 * Initializes the SOLR manager for requesting the elements of the tree
	 */
	function setupManagers() {
		// Create the manager that retrieves all matching pages
		mSolrManagerAllTreePages = new AjaxSolr.FSManager({
			solrUrl: wgFSSolrURL,
			servlet: wgFSSolrServlet
		});
		mSolrManagerAllTreePages.init();
		mSolrManagerAllTreePages.store.addByValue('wt', 'json');
		mSolrManagerAllTreePages.store.addByValue('rows', 10000); // TODO set high limit/no limit
		mSolrManagerAllTreePages.handleResponse = handleAllMatchingTreePagesResponse;
		
		// Create the manager that creates the tree structure
		mSolrManagerForFilter = new AjaxSolr.FSManager({
			solrUrl: wgFSSolrURL,
			servlet: wgFSSolrServlet
		});
		mSolrManagerForFilter.init();
		mSolrManagerForFilter.handleResponse = handleAllFilteredPagesResponse;
	}
	
	function requestAllMatchingTreePages() {
		setQueryForAllMatchingTreePages();
		mSolrManagerAllTreePages.doRequest();
	}
	
	function setQueryForAllMatchingTreePages() {
		// Prepare for first request: All pages using the property, without any 
		// further restrictions from the current faceted search

		mSolrManagerAllTreePages.store.remove('fq');
		
		mSolrManagerAllTreePages.store.addByValue('fq', 'smwh_properties:' + mTreePropertyField);
		mSolrManagerAllTreePages.store.addByValue('fl', ['id', 'smwh_title', 'smwh_namespace_id', mTreePropertyField]);
		mSolrManagerAllTreePages.store.addByValue('q', '*:*');
	}
	
	function setQueryForFilteredPages() {
		// Prepare for second request: Retrieve all pages which match the current 
		// search filter        
		// add current search restrictions from faceted search instance
		mSolrManagerForFilter.store.remove('fq');
		
		mSolrManagerForFilter.store.parseString(mSolrManagerAllTreePages.store.string());
		
		// Copy the constraints from the external filterStore
		if (mFilterStore) {
			jQuery.each(mFilterStore.values('q'), function(index, value){
				mSolrManagerForFilter.store.addByValue('q', value);
			});
			jQuery.each(mFilterStore.values('fq'), function(index, value){
				mSolrManagerForFilter.store.addByValue('fq', value);
			});
		}
		
		
	}
	
	function handleAllMatchingTreePagesResponse(data) {
		mParentMap = {};
		
		// create inverse outer "graph" (adjacency matrix) based on the first request
		jQuery.each(data.response.docs, function(index, value){
			// TODO take namespaces into account (for IDs)
			var id0 = value['smwh_title'];
			if (value[mTreePropertyField]) {
				// TODO handle multiple values
				var id1 = value[mTreePropertyField];
				if (!mParentMap[id0]) {
					mParentMap[id0] = {};
				}
				mParentMap[id0][id1] = true;
			}
		});
		
		setQueryForFilteredPages();
		mSolrManagerForFilter.doRequest();
		
	}
	
	function handleAllFilteredPagesResponse(data) {
		
		var destinationsChildren = {};
		var sourcesChildren = {};
		var childrenMap = {};
		
		// create a map from parent to children (adjacency matrix)
		jQuery.each(data.response.docs, function(index, value){
			// TODO take namespaces into account (for IDs)
			var id0 = value.smwh_title;
			if (value[mTreePropertyField]) {
				// TODO handle multiple values
				var id1 = value[mTreePropertyField];
				sourcesChildren[id0] = true;
				destinationsChildren[id1] = true;
				if (!childrenMap[id1]) {
					childrenMap[id1] = {};
				}
				childrenMap[id1][id0] = true;
			}
		});
		
		// find root of children graphs
		jQuery.each(sourcesChildren, function(key, value){
			if (destinationsChildren[key]) {
				delete destinationsChildren[key];
			}
		});
		var rootsChildren = destinationsChildren;
		
		// "merge" children and parent graph: 
		// Let the children graph grow from the children-roots to the parent-roots 
		var finalRoots = {};
		seekRoot = function(id){
			if (mParentMap[id]) {
				jQuery.each(mParentMap[id], function(key, value){
					if (!childrenMap[key]) {
						childrenMap[key] = {};
					}
					childrenMap[key][id] = true;
					// TODO check for cycles
					seekRoot(key);
				});
			}
			else {
				finalRoots[id] = true;
			}
		};
		jQuery.each(rootsChildren, function(key, value){
			seekRoot(key);
		});
		
		// build JSON tree structure for jsTree
		buildTree = function(nodes, nodeId){
			var label = nodeId.replace(/_/g, ' ');
			var node = {
				'data': {
					'title': label,
					'attr': {
						'href': nodeId
					}
				}
			};
			if (childrenMap[nodeId]) {
				// display all nodes with children as opened
				node['state'] = 'open';
				var children = [];
				node['children'] = children;
				// process children of the node
				// TODO check for cycles
				jQuery.each(childrenMap[nodeId], function(key, value){
					buildTree(children, key);
				});
			}
			nodes.push(node);
		};
		var top = [];
		var json = {
			'data': top
		};
		jQuery.each(finalRoots, function(key, value){
			buildTree(top, key);
		});
		
		// finally render the JSON structure with the registered widgets
	    for (var widgetId in this.widgets) {
	      this.widgets[widgetId].afterRequest(json);
    	}
		
	}
	
	//--- Class initialization ---
	construct();
	
	return that;
}