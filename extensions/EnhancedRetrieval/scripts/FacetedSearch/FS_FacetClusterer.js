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

/**
 * @class FacetClusterer
 * This class clusters the values of a facet.
 * 
 */
FacetedSearch.classes.FacetClusterer = function (facetName) {
	var $ = jQuery;
	
	//--- Constants ---
	// The field with this name is used on SOLR queries
	var NUM_CLUSTERS = 5;
	
	
	//--- Private members ---

	// AjaxSolr.Manager - The manager from the AjaxSolr library. It is used for
	// asking the statistics and the clusters for a facet.
	var mAjaxSolrManager;
	
	// string - Name of the facet whose clusters are retrieved.
	var mFacetName;
	
	// The instance of this object
	var that = {};
	

	/**
	 * Constructor for the FacetedSearch class. Clusters can be created for 
	 * numerical values and dates.
	 * The name of the facet contains the type as suffix.
	 * 
	 * @param string facetName
	 * 		The full name of the facet whose values are clustered. 
	 */
	function construct(facetName) {
		mAjaxSolrManager = new AjaxSolr.Manager();
		mAjaxSolrManager.init();
		
		mFacetName = facetName;
		
	};
	that.construct = construct;
	
	
	/**
	 * Retrieves the clusters for the facet of this instance
	 */
	that.retrieveClusters = function () {
		mAjaxSolrManager.store.addByValue('q', '*:*');
		mAjaxSolrManager.store.addByValue('stats', 'true');
		mAjaxSolrManager.store.addByValue('stats.field', mFacetName);
		var handleResponse = mAjaxSolrManager.handleResponse;

		mAjaxSolrManager.handleResponse = function (data) {
			// Restore the original response handler
			mAjaxSolrManager.handleResponse = handleResponse;
			var min = data.stats.stats_fields[mFacetName].min;
			var max = data.stats.stats_fields[mFacetName].max;
			var clusters = that.makeClusters(min, max);
			
			// Remove the statistic parameters
			mAjaxSolrManager.store.remove('stats');
			mAjaxSolrManager.store.remove('stats.field');
			var clusterCounts = retrieveClusterCounts(clusters);
		};
		
		mAjaxSolrManager.doRequest();
		
	}
	
	/**
	 * This function generates clusters for values between min and max.
	 * 
	 * @param {int} min
	 * 		The minimal value of the value range.
	 * @param {int} max
	 * 		The maximal value of the value range.
	 */
	that.makeClusters = function makeClusters(min, max) {
		var diff = max - min;
		var values = [];
		var currVal = min;
		var incr = diff / NUM_CLUSTERS;
		
		for (var i = 0; i < NUM_CLUSTERS; ++i) {
			values[i] = Math.round(currVal);
			currVal += incr;
		}
		values[i] = max;
		return values;
	}
	
	/**
	 * Retrieves the number of objects in the given clusters of the facet of this
	 * instance.
	 * 
	 * @param {array(int)} clusters
	 * 		This array of integers contains the boundaries of the clusters.
	 */
	function retrieveClusterCounts(clusters) {
		mAjaxSolrManager.store.addByValue('facet', 'true');
		var min = clusters[0];
		for (var i = 1; i < clusters.length; ++i) {
			var max = clusters[i];
			mAjaxSolrManager.store.addByValue('facet.query', 
				mFacetName+':[' + min + ' TO ' + max + ']');
			min = max + 1;	
		}
		
		mAjaxSolrManager.addWidget(new FacetedSearch.classes.ClusterWidget({
				id: 'fsc'+mFacetName,
				target: '#property_' + mFacetName + '_values',
				facetName: mFacetName
			}));
		
		mAjaxSolrManager.doRequest();
		
	}
	
	construct(facetName);
	return that;
	
}
