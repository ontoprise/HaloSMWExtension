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
 * @class TreeParserFunctionWidget
 * 
 * This widget extends AjaxSolr.AbstractWidget.
 * It renders the syntax of the {{#tree}} parser function for the current query.
 * This widget must be attached to a TreeView.classes.SolrTreeViewManager.
 * An instance of this class must be passed in the configuration object e.g.
 * new TreeView.classes.TreeParserFunctionWidget(
 * 	{
 * 		treeManager: solrTreeManager,
 * 		target: '#id of target'
 * 	}));
 * 
 */
(function ($) {
	
TreeView.classes.TreeParserFunctionWidget = AjaxSolr.AbstractWidget.extend({

	mStore: new AjaxSolr.ParameterStore(),
	/**
	 * This function is called before a SOLR request is sent. It generates the
	 * syntax of the {{#tree}} parser function and displays it.
	 * 
	 */
	beforeRequest: function() {
		var tp = this.treeManager.getTreeProperty();
		var store = this.mStore;
		
		// Copy the important parts of the query from the manager
		store.remove('fq');
		
		// Copy the constraints from the external filterStore
		jQuery.each(this.manager.store.values('q'), function(index, value){
			store.addByValue('q', value);
		});
		jQuery.each(this.manager.store.values('fq'), function(index, value){
			store.addByValue('fq', value);
		});
		
		// Remove the query for the property
		store.removeByValue('fq', 'smwh_properties:smwh_Subsection_of_t');
		
		var query = this.mStore.string();
		var tree = mw.msg('tv_treepf_template', tp, query);
		
		$(this.target).empty();
		$(this.target).append(AjaxSolr.theme('treeParserFunction', tree));
		
	}
	
});

})(jQuery);
