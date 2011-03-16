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
	
	afterRequest: function () {
		if (this.noRender) {
			return;
		}
		
		var $ = jQuery;
		
		if (this.fields === undefined) {
			this.fields = [this.field];
		}
		
		var fq = this.manager.store.values('fq');
		
		var maxCount = 0;
		var objectedItems = [];
		for (var i = 0; i < this.fields.length; i++) {
			var field = this.fields[i];
			if (this.manager.response.facet_counts.facet_fields[field] === undefined) {
				continue;
			}
			for (var facet in this.manager.response.facet_counts.facet_fields[field]) {
				var count = parseInt(this.manager.response.facet_counts.facet_fields[field][facet]);
				if (count > maxCount) {
					maxCount = count;
				}
				var fullName = field + ':' + facet;
				if ($.inArray(fullName, fq) >= 0) {
					continue;
				}
				objectedItems.push({
					field: field,
					facet: facet,
					count: count
				});
			}
		}

		if (objectedItems.length == 0) {
			$(this.target).html(AjaxSolr.theme('no_items_found'));
			return;
		}
		
		objectedItems.sort(function(a, b) {
			return a.count > b.count ? -1 : 1;
		});
		
		// show facets using grouping
		var GROUP_SIZE = 10;
		var self = this;
		$(this.target).empty();
		for (var i = 0, l = objectedItems.length; i < l; i++) {
			if (i % GROUP_SIZE == 0) {
				var ntarget = $('<div>');
				if (i != 0) {
					$(ntarget).hide();
				}
				$(this.target).append(ntarget);
			}
			var facet = objectedItems[i].facet;
			var target;
			if (objectedItems[i].field == this.field) {
				target = self;
			} else {
				target = FacetedSearch.singleton.FacetedSearchInstance.getRelationWidget();
			}
			$(ntarget)
				.append(AjaxSolr.theme('facet', facet, objectedItems[i].count, target.clickHandler(facet), FacetedSearch.classes.ClusterWidget.showPropertyDetailsHandler))
				.append('<br>');
		}
		if (objectedItems.length > GROUP_SIZE) {
			$(this.target)
				.append('<a class="xfsFMore">more</a>')
				.append("<br />");
		}
	},
	
	init: function () {
		var $ = jQuery;
		$('a.xfsFMore').live('click', function() {
			var hidden = $(this).parent().children('div:hidden').length;
			if (hidden > 0) {
				$(this).parent().children('div:hidden:first').show();
				if (hidden == 1) {
					$(this).text('less');
				}
			} else {
				$(this).parent().children('div:gt(0)').hide();
				$(this).text('more');
			}
			return false;
		});
	}
});

