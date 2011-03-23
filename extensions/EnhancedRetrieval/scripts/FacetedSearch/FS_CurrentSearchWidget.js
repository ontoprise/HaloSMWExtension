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
	// Define the FacetedSearch module
	var FacetedSearch = {
		classes : {}
	};
}

FacetedSearch.classes.CurrentSearchWidget = AjaxSolr.AbstractWidget.extend({

	/**
	 * Updates the current search restrictions/filter view.
	 */
	afterRequest : function() {
		var $ = jQuery;

		var DEBUG = false;
		var FIELD_PREFIX_REGEX = /([^:]+):(.*)/;
		var EXTRACT_TYPE_REGEX = /(.*)_(.*)$/; 
		
		var self = this;
		var links = [];

		var fq = this.manager.store.values('fq');
		var facetQueries = {};
		for ( var i = 0, l = fq.length; i < l; i++) { 
			var match = fq[i].match(FIELD_PREFIX_REGEX);
			if ($.inArray(match[1], FacetedSearch.singleton.FacetedSearchInstance.FACET_FIELDS) >= 0) {
				var facetName = match[2];
				links.push(AjaxSolr.theme('facet', match[2], -1, self.removeFacet(fq[i]), FacetedSearch.classes.ClusterWidget.showPropertyDetailsHandler, true));
				var nameWithoutType = facetName.match(EXTRACT_TYPE_REGEX);
				if (nameWithoutType) {
					// This applies only to properties
					nameWithoutType = nameWithoutType[1];
					facetQueries[nameWithoutType] = true;
				}
			}
		}
		for ( var i = 0, l = fq.length; i < l; i++) {
			var match = fq[i].match(FIELD_PREFIX_REGEX);
			if ($.inArray(match[1], FacetedSearch.singleton.FacetedSearchInstance.FACET_FIELDS) < 0) {
				var facetName = match[1];
				// Do not include fields that end with "datevalue_l"
				if (facetName.match(/.*?_datevalue_l$/)) {
					continue;
				}
				var nameWithoutType = facetName.match(EXTRACT_TYPE_REGEX);
				nameWithoutType = nameWithoutType[1];
				if (nameWithoutType && !facetQueries[nameWithoutType]) {
					links.push(AjaxSolr.theme('facet', facetName, -1, self.removeFacet(fq[i]), FacetedSearch.classes.ClusterWidget.showPropertyDetailsHandler, true));
					facetQueries[facetName] = true;
				}
			}
		}

		if (links.length > 1) {
			links.push(AjaxSolr.theme('remove_all_filters', function() {
				self.manager.store.remove('fq');
				self.manager.doRequest(0);
				return false;
			}));
		}
		
		if (links.length) {
			$(self.target).empty();
			$.each(links, function() {
				$(self.target)
					.append(this)
					.append('<br>');
			});
		} else {
			$(this.target).html(AjaxSolr.theme('no_facet_filter_set'));
		}
		if (DEBUG) {
			$(self.target).append(AjaxSolr.theme('filter_debug', self.manager.store.values('fq')));
		}
	},

	/**
	 * Removes a facet from current filter, including all related ranges.
	 * @param {string} facet
	 * 		Name of the facet
	 */
	removeFacet : function(facet) {
		var self = this;
		var $ = jQuery;
		return function() {
			var fs = FacetedSearch.singleton.FacetedSearchInstance;
			var fsfields = fs.FACET_FIELDS; 
			var FIELD_PREFIX_REGEX = /([^:]+):(.*)/;
			var CATEGORY_FACET_REGEX = new RegExp(fsfields[0] + ':.*');
			var store = self.manager.store;
			
			var fq = self.manager.store.values('fq');
			var split = facet.match(FIELD_PREFIX_REGEX);
			if ($.inArray(split[1], fsfields) >= 0) {
				// Remove filter queries for category, relation or attribute 
				// facets that begin with the facet name e.g. someProperty:propertyValue
				
				// Is it a category facet
				if (facet.match(CATEGORY_FACET_REGEX)) {
					store.removeByValue('fq', split[0]);
				} else {
					// The type suffix of the property name may be wrong 
					// i.e. string and are equivalent => ignore the suffix
					var nameWithoutType = split[2].match(/^(.*_).*$/); 
					store.removeByValue('fq', new RegExp('^' + nameWithoutType[1] + '.*?:.*'));
				}
				
			}
			var attrFacetClass = fsfields[1];
			var ATTRIBUTE_REGEX = new RegExp(attrFacetClass + ':(smwh_.*)_xsdvalue_.*');
			var ps = facet.match(ATTRIBUTE_REGEX);
			if (ps) {
				var removeRegex = new RegExp('^' + ps[1] + '_.*?value_.*?:.*$');
				store.removeByValue('fq', removeRegex);
			}
			var relationFacetClass = fsfields[2];
			var RELATION_REGEX = new RegExp('^' + relationFacetClass + ':(smwh_.*)_.*$');
			var ps = facet.match(RELATION_REGEX);
			if (ps) {
				var removeRegex = new RegExp('^' + relationFacetClass + ':' + ps[1] + '_.*$');
				store.removeByValue('fq', removeRegex);
			}
			store.removeByValue('fq', facet);
			self.manager.doRequest(0);
			return false;
		};
	}
});
