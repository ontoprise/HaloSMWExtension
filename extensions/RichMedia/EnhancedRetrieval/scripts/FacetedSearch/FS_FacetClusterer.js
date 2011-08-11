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
FacetedSearch.classes.FacetClusterer = function (facetName, plainName) {
	var $ = jQuery;
	
	// The instance of this object
	var that = {};
	
	//--- Constants ---
	// The numer of clusters that are generated for a facet.
	that.NUM_CLUSTERS = 5;
	
	
	//--- Private members ---

	// AjaxSolr.Manager - The manager from the AjaxSolr library. It is used for
	// asking the statistics and the clusters for a facet.
	var mAjaxSolrManager;
	
	// string - Name of the facet whose clusters are retrieved.
	var mFacetName;
	
	// string - Plain name of the facet without prefix and suffix
	var mPlainName;
	
	//--- Getters/Setters ---
	that.getAjaxSolrManager = function () { return mAjaxSolrManager; }
	that.getFacetName       = function () { return mFacetName; }
	
	//--- Public methods ---

	/**
	 * Constructor for the FacetClusterer class. Clusters can be created for 
	 * numerical values, dates etc.
	 * The name of the facet contains the type as suffix.
	 * 
	 * @param string facetName
	 * 		The full name of the facet whose values are clustered. 
	 * @param string plainName
	 * 		The plain name without prefix and suffix of the facet. 
	 */
	function construct(facetName, plainName) {
		mAjaxSolrManager = new AjaxSolr.Manager({
			solrUrl : wgFSSolrURL
		});
		mAjaxSolrManager.init();
		fsm = FacetedSearch.singleton.FacetedSearchInstance.getAjaxSolrManager();
		mAjaxSolrManager.store = fsm.store;
		
		mFacetName = facetName;
		mPlainName = plainName;
		
	};
	that.construct = construct;
	
	
	/**
	 * Retrieves the clusters for the facet of this instance
	 */
	that.retrieveClusters = function () {
		mAjaxSolrManager.store.addByValue('stats', 'true');
		mAjaxSolrManager.store.addByValue('stats.field', that.getStatisticsField());
		var handleResponse = mAjaxSolrManager.handleResponse;

		mAjaxSolrManager.handleResponse = function (data) {
			// Restore the original response handler
			mAjaxSolrManager.handleResponse = handleResponse;
			
			facet = that.getStatisticsField();
			var min = data.stats.stats_fields[facet].min;
			var max = data.stats.stats_fields[facet].max;
			var clusters = that.makeClusters(min, max);
			
			// Remove the statistic parameters
			mAjaxSolrManager.store.remove('stats');
			mAjaxSolrManager.store.remove('stats.field');
			var clusterCounts = retrieveClusterCounts(clusters);
		};
		
		mAjaxSolrManager.doRequest(0);
		
	}
	
	/**
	 * For clustering the statistics of a facet have to be retrieved to find its
	 * min and max values. Normally this is the field stored in mFacetName. Sub 
	 * classes can overwrite this method it a different field is to be used.
	 */
	that.getStatisticsField = function () {
		return mFacetName;
	}
	
	/**
	 * This function generates clusters for numeric values between min and max.
	 * 
	 * @param {int} min
	 * 		The minimal value of the value range.
	 * @param {int} max
	 * 		The maximal value of the value range.
	 */
	that.makeClusters = function makeClusters(min, max) {
		alert("The function FacetClusterer.makeClusters must be implemented by derived classes.")
	}
	
	/**
	 * Formats a boundary value of a cluster for display in the UI.
	 * @param {Object} value
	 * 		The value to format
	 * @return string
	 * 		The formated value
	 */
	that.formatBoundary = function (value) {
		return value;
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
		var facet = that.getStatisticsField();
		for (var i = 0; i < clusters.length; ++i) {
			var min = clusters[i][0];
			var max = clusters[i][1];
			mAjaxSolrManager.store.addByValue('facet.query', 
				facet+':[' + min + ' TO ' + max + ']');
		}
		
		mAjaxSolrManager.addWidget(new FacetedSearch.classes.ClusterWidget({
				id: 'fsc'+mFacetName,
				target: '#'+AjaxSolr.theme.prototype.getPropertyValueHTMLID(mFacetName),
				facetName: mFacetName,
				statisticsFieldName: facet,
				clusterer: that
			}));
		
		mAjaxSolrManager.doRequest(0);
		
	}
	
	construct(facetName, plainName);
	return that;
	
}
