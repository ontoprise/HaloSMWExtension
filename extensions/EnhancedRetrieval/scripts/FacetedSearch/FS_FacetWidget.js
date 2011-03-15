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
			classes : {}
	};
}

/**
 * @class FacetWidget
 * This class handles the facet fields.
 * 
 */
FacetedSearch.classes.FacetWidget = AjaxSolr.AbstractFacetWidget.extend({
	
	/**
	 * This function is called when the details of a property facet are to be shown.
	 * @param {string} facet
	 * 		Name of the facet
	 * 
	 */
	showPropertyDetailsHandler: function(facet) {
		var clusterer = FacetedSearch.classes.FacetClusterer(facet);
		clusterer.retrieveClusters();
		
	},
	
	afterRequest: function () {
		
		var $ = jQuery;
		if (this.manager.response.facet_counts.facet_fields[this.field] === undefined) {
			$(this.target).html(AjaxSolr.theme('no_items_found'));
			return;
		}
		
		var maxCount = 0;
		var objectedItems = [];
		for (var facet in this.manager.response.facet_counts.facet_fields[this.field]) {
			var count = parseInt(this.manager.response.facet_counts.facet_fields[this.field][facet]);
			if (count > maxCount) {
				maxCount = count;
			}
			objectedItems.push({
				facet: facet,
				count: count
			});
		}
		
		objectedItems.sort(function(a, b){
			return a.count > b.count ? -1 : 1;
		});
		
		var self = this;
		$(this.target).empty();
		for (var i = 0, l = objectedItems.length; i < l; i++) {
			var facet = objectedItems[i].facet;
			$(this.target)
				.append(AjaxSolr.theme('facet', facet, 
				                       objectedItems[i].count, 
									   self.clickHandler(facet),
									   self.showPropertyDetailsHandler))
				.append('<br/>');
		}
	}
});

