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

	afterRequest : function() {
		var $ = jQuery;

		var FIELD_PREFIX_REGEX = /([^:]+):(.*)/;
		
		var self = this;
		var links = [];

		var fq = this.manager.store.values('fq');
		for ( var i = 0, l = fq.length; i < l; i++) {
			var match = fq[i].match(FIELD_PREFIX_REGEX);
			// TODO add property details handler
			links.push(AjaxSolr.theme('facet', match[2], -1, self.removeFacet(fq[i]), self.showPropertyDetailsHandler));
		}

		if (links.length) {
			$(this.target).empty();
			$.each(links, function() {
				$(self.target)
					.append(this)
					.append('<br>');
			});
		} else {
			$(this.target).html(AjaxSolr.theme('no_facet_filter_set'));
		}
	},

	removeFacet : function(facet) {
		var self = this;
		return function() {
			if (self.manager.store.removeByValue('fq', facet)) {
				self.manager.doRequest(0);
			}
			return false;
		};
	}
});
