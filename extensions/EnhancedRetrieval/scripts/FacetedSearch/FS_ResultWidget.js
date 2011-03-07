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

FacetedSearch.classes.ResultWidget = AjaxSolr.AbstractWidget.extend({
	
	
	beforeRequest: function () {
		var $ = jQuery;
		$(this.target).html($('<img/>').attr('src', 'images/ajax-loader.gif'));
	},

	facetLinks: function (facet_field, facet_values) {
		var links = [];
		if (facet_values) {
			for (var i = 0, l = facet_values.length; i < l; i++) {
				links.push(AjaxSolr.theme('facet_link', facet_values[i], this.facetHandler(facet_field, facet_values[i])));
			}
		}
		return links;
	},
	
	facetHandler: function (facet_field, facet_value) {
		var self = this;
		return function () {
			self.manager.store.remove('fq');
			self.manager.store.addByValue('fq', facet_field + ':' + facet_value);
			self.manager.doRequest(0);
			return false;
		};
	},
	
	afterRequest: function () {
		var $ = jQuery;
		$(this.target).empty();
		for (var i = 0, l = this.manager.response.response.docs.length; i < l; i++) {
			var doc = this.manager.response.response.docs[i];
			$(this.target).append(AjaxSolr.theme('article', doc, AjaxSolr.theme('data', doc)));
		}
	},
	
	init: function () {
		var $ = jQuery;
/*
		$('a.more').livequery(function () {
			$(this).toggle(function () {
				$(this).parent().find('span').show();
				$(this).text('less');
				return false;
			}, function () {
				$(this).parent().find('span').hide();
				$(this).text('more');
				return false;
			});
		});
*/		
	}
});

