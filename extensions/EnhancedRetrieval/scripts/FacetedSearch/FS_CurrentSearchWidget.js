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

		var DEBUG = true;
		var FIELD_PREFIX_REGEX = /([^:]+):(.*)/;
		
		var self = this;
		var links = [];

		var fq = this.manager.store.values('fq');
		for ( var i = 0, l = fq.length; i < l; i++) {
			var match = fq[i].match(FIELD_PREFIX_REGEX);
			if ($.inArray(match[1], FacetedSearch.singleton.FacetedSearchInstance.FACET_FIELDS) >= 0) {
				links.push(AjaxSolr.theme('facet', match[2], -1, self.removeFacet(fq[i]), FacetedSearch.classes.ClusterWidget.showPropertyDetailsHandler, true));
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
			if (DEBUG) {
				$(self.target).append(AjaxSolr.theme('filter_debug', self.manager.store.values('fq')));
			}
			$.each(links, function() {
				$(self.target)
					.append(this)
					.append('<br>');
			});
		} else {
			$(this.target).html(AjaxSolr.theme('no_facet_filter_set'));
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
			var fq = self.manager.store.values('fq');
			var FIELD_PREFIX_REGEX = /([^:]+):(.*)/;
			var match = facet.match(FIELD_PREFIX_REGEX);
			if ($.inArray(match[1], FacetedSearch.singleton.FacetedSearchInstance.FACET_FIELDS) >= 0) {
				var remove = [];
				$.each(fq, function(index, value) {
					if (value.indexOf(match[2]) == 0) {
						remove.push(value);
					}
				});
				$.each(remove, function(index, value) {
					self.manager.store.removeByValue('fq', value);
				});
			}
			if (self.manager.store.removeByValue('fq', facet)) {
				self.manager.doRequest(0);
			}
			return false;
		};
	}
});
