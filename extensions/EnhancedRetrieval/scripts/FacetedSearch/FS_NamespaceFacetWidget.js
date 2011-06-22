
/*  Copyright 2011, ontoprise GmbH
 *  This file is part of the FacetedSearch-Extension.
 *
 *   The FacetedSearch-Extension is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The FacetedSearch-Extension is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * @file
 * @ingroup FacetedSearchScripts
 * @author: Thomas Schweitzer
 */
if (typeof FacetedSearch == "undefined") {
	//	Define the FacetedSearch module	
	var FacetedSearch = {
		classes: {}
	};
}

/**
 * @class NamespaceFacetWidget
 *
 * This class handles the facet fields for namespaces.
 *
 */
FacetedSearch.classes.NamespaceFacetWidget = AjaxSolr.AbstractFacetWidget.extend({

	mFacetTheme: 'namespaceFacet',
	
	setFacetTheme: function(facetTheme){
		this.mFacetTheme = facetTheme;
	},
	
	mNamespaces: [],
	
	afterRequest: function(){
	
		var $ = jQuery;
		
		var field = this.field;
		var selectedNamespace = null;
		
		var fq = this.manager.store.values('fq');
		// Check if a facet query for namespaces is present
		var namespaceQueried = false; 
		var re = new RegExp(field + ':(\\d+)');
		for (var i = 0; i < fq.length; ++i) {
			var matches = fq[i].match(re);
			if (matches) {
				selectedNamespace = matches[1];
				namespaceQueried = true;
			}
		}
		
		var objectedItems = [];
		var currentNamespaces = [];
		for (var facet in this.manager.response.facet_counts.facet_fields[field]) {
			var count = parseInt(this.manager.response.facet_counts.facet_fields[field][facet]);
			// Check if a new namespace was populated
			var nsFound = false;
			for (var i = 0; i < this.mNamespaces.length; ++i) {
				if (this.mNamespaces[i] === facet) {
					nsFound = true;
					break;
				}
			}
			if (!nsFound) {
				this.mNamespaces.push(facet);
			}
			
			currentNamespaces.push(facet);
			objectedItems.push({
				field: field,
				facet: facet,
				count: count
			});
		}
		
		// Create object items for the missing namespaces of the current search
		for (var i = 0; i < this.mNamespaces.length; ++i) {
			if ($.inArray(this.mNamespaces[i], currentNamespaces) === -1) {
				objectedItems.push({
					field: field,
					facet: this.mNamespaces[i],
					count: 0
				});
			}
		}
		
		
		objectedItems.sort(function(a, b){
			return a.facet < b.facet ? -1 : 1;
		});
		
		$(this.target).empty();
		// Add the "All namespaces" link
		var entry = AjaxSolr.theme(this.mFacetTheme, "all", 
								   this.manager.response.response.numFound, 
								   this.clickHandler('all'), null, false);
		$(this.target).append(entry);
		// Add all namespaces
		for (var i = 0, l = objectedItems.length; i < l; i++) {
			var facet = objectedItems[i].facet;
			var entry = AjaxSolr.theme(this.mFacetTheme, facet, 
			                           objectedItems[i].count, 
									   this.clickHandler(facet), null, false);
			$(this.target).append(entry);
		}
		// update the selected namespace label
		// Remove selection from all namespace labels
		$(".xfsSelectedNamespace").removeClass("xfsSelectedNamespace");
		// At this point there can only be one namespace facet 
		// => mark it as selected
		var selectedNS = namespaceQueried ? selectedNamespace : 'all';
		$('[namespace="' + selectedNS + '"]').addClass("xfsSelectedNamespace");
	},
	
	init: function(){
	},
	
	/**
	 * This function is called when a namespace is clicked in the UI.
	 * 
	 * @param {String} value The value.
	 * @returns {Function} Sends a request to Solr if it successfully adds a
	 *   filter query with the given value.
	 */
	clickHandler: function(value){
		var self = this;
		return function(){
			var regex = new RegExp(self.field);
			self.manager.store.removeByValue('fq', regex);
			
			if (value !== 'all') {
				self.add(value);
			} 
			self.manager.doRequest(0);
			return false;
		}
	}
	
});

