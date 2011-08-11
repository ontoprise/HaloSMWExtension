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
 * @class ClusterWidget
 * This is the class for a widget that represents the clusters of a property. 
 * Its target selector is the ID of the property whose values are clustered.
 * 
 */
FacetedSearch.classes.ClusterWidget = AjaxSolr.AbstractWidget.extend({

	/**
	 * This is the click handler for a cluster of values for an attribute.
	 * @param {Object} cluster
	 * 		A description of the cluster with the fields 
	 * 		- from
	 * 		- to
	 * 		- count
	 * 		- facet
	 * 		- facetStatisticField
	 * @returns {Function} Sends a request to Solr if it successfully adds a
	 *   filter query with the given value.
	 */
	clickClusterHandler: function (cluster) {
		var self = this;
		return function () {
			var fsm = FacetedSearch.singleton.FacetedSearchInstance.getAjaxSolrManager();
			fsm.store.addByValue('facet', true);
			var regex = new RegExp(cluster.facetStatisticField+':\\[.*\\]');
			fsm.store.removeByValue('fq', regex);

			var field;
			var ATTRIBUTE_REGEX = /smwh_(.*)_xsdvalue_(.*)/;
			if (cluster.facet.match(ATTRIBUTE_REGEX)) {
				field = FacetedSearch.singleton.FacetedSearchInstance.FACET_FIELDS[1];
			} else {
				field = FacetedSearch.singleton.FacetedSearchInstance.FACET_FIELDS[2];
			}
			fsm.store.addByValue('fq', field + ':' + cluster.facet);

			fsm.store.addByValue('fq', 
				cluster.facetStatisticField+':[' + cluster.from + ' TO ' + cluster.to + ']');
			fsm.doRequest(0);
			return false;
		}
	},

	/**
	 * This function is called when a request to the SOLR manager returns data.
	 * The data contains the facet queries that contains ranges of values of a
	 * semantic attribute and the number of articles whose values are in these
	 * ranges.
	 * This function retrieves the ranges and numbers and passes them to the 
	 * cluster theme that adds html to the attribute facets. 
	 * 
	 */
	afterRequest: function () {
		
		var $ = jQuery;
		var self = this;
		var data = this.manager.response;
		
		// Create strings for the ranges with instance counts
		// e.g. 42 - 52 (5)
		var regex = new RegExp(this.statisticsFieldName+':\\[(\\d*) TO (\\d*)\\]'); 
		var ranges = data.facet_counts.facet_queries;
		var minValue = null;
		var maxValue = null;
		var numRanges = 0;
		// Count the ranges that actually contain articles
		for (var range in ranges) {
			if (ranges[range] > 0) {
				++numRanges;
			}
		}
		
		var target = $(this.target);
		target.empty();
		for (var range in ranges) {
			var matches = range.match(regex);
			if (matches) {
				var from = matches[1];
				var displayFrom = this.clusterer.formatBoundary(from);
				if (!minValue) {
					minValue = displayFrom;
				}
				var to = matches[2];
				var displayTo = this.clusterer.formatBoundary(to);
				maxValue = displayTo;
				var count = ranges[range];
				if (count > 0) {
					// Create the HTML for the cluster
					target.append(AjaxSolr.theme('cluster', 
						displayFrom, displayTo, count, 
						self.clickClusterHandler({
							from: from,
							to: to,
							count: count,
							facetStatisticField: this.statisticsFieldName,
							facet: this.facetName
						}),
						false, false, numRanges === 1
						));
				}
			}
		}

		// Check if the range is already restricted
		var rangeRestricted = false;
		var fq = this.manager.store.values('fq');
		for (var i = 0, l = fq.length; i < l; i++) {
			if (fq[i].indexOf(this.statisticsFieldName) == 0) {
				rangeRestricted = true;
				break;
			}
		}
				
		// Show the cluster title
		target.prepend(AjaxSolr.theme('cluster', minValue, maxValue, 
								   data.response.numFound, 
								   this.clickRemoveRangeHandler(this.statisticsFieldName),
								   true, rangeRestricted));
		
		
		// Remove the statistic parameters
		this.manager.store.remove('facet.query');
		
	},
	
	/**
	 * Removes a range restriction for a facet.
	 * @param {string} facet
	 * 		Name of the facet
	 */
	clickRemoveRangeHandler: function (facet) {
		var self = this;
		return function() {
			var fsm = FacetedSearch.singleton.FacetedSearchInstance.getAjaxSolrManager();
			var fq = fsm.store.values('fq');
			for (var i = 0, l = fq.length; i < l; i++) {
				if (fq[i].indexOf(facet) == 0) {
					fsm.store.removeByValue('fq', fq[i]);
					break;
				}
			}
			fsm.doRequest(0);
			return false;
		};
	}
});

/**
 * This function is called when the details of a property facet are to be shown.
 * @param {string} facet
 * 		Name of the facet
 * 
 */
FacetedSearch.classes.ClusterWidget.showPropertyDetailsHandler = function(facet) {
	var clusterer = FacetedSearch.factories.FacetClustererFactory(facet);
	clusterer.retrieveClusters();
};
