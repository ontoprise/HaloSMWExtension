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
 * @class FacetedSearch
 * This is the main class of the faceted search.
 * 
 */
FacetedSearch.classes.FacetedSearch = function () {
	var $ = jQuery;
	
	//--- Constants ---
	// The field with this name is used on SOLR queries
	var QUERY_FIELD = 'smwh_title';
	
	// Names of the facet classes
	var FACET_FIELDS = ['smwh_categories', 'smwh_attributes', 'smwh_properties',
						'smwh_namespace_id'];
						
	var RELATION_REGEX = /^smwh_(.*)_(.*)$/;
	var ATTRIBUTE_REGEX = /smwh_(.*)_xsdvalue_(.*)/;
	
	//--- Private members ---

	// The instance of this object
	var that = {};
	
	// AjaxSolr.Manager - The manager from the AjaxSolr library.
	var mAjaxSolrManager;
	
	// string - The current search string
	var mSearch = '';
	
	// reference to the (dummy) relation widget
	var mRelationWidget;
	
	// {Array} Array of strings. It contains the names of all facets that are
	// currently expanded in the UI.
	var mExpandedFacets = [];
	 
	//--- Getters/Setters ---
	that.getAjaxSolrManager = function() {
		return mAjaxSolrManager;
	}
	
	that.getRelationWidget = function() {
		return mRelationWidget;
	}
	
	/**
	 * Adds the given facet to the set of expanded facets in the UI, if it is a
	 * property facet.
	 * 
	 * @param {String} facet
	 * 		Name of the facet
	 */
	that.addExpandedFacet = function (facet) {
		if ($.inArray(facet, mExpandedFacets) === -1) {
			if (isProperty(facet)) {
				mExpandedFacets.push(facet);
			}
		}
	}
	
	/**
	 * Return true if the given facet is expanded in the User Interface.
	 * 
	 * @param {String} facet
	 * 		Name of the facet
	 * @return {bool}
	 * 		true, if the facet is expanded
	 */
	that.isExpandedFacet = function (facet) {
		return $.inArray(facet, mExpandedFacets) >= 0;
	}
	
	/**
	 * Removes the given facet from the set of expanded facets in the UI. If no
	 * facet name is given, all facets are removed.
	 * 
	 * @param {String} facet
	 * 		Name of the facet. If this parameter is missing, all facets are removed.
	 */
	that.removeExpandedFacet = function (facet) {
		if (typeof facet === 'undefined') {
			mExpandedFacets.length = 0;
			return;
		}
		var pos = $.inArray(facet, mExpandedFacets);
		if (pos === -1) {
			return;
		}
		// Replace the element to be removed by the last element of the array...
		var len = mExpandedFacets.length;
		mExpandedFacets[pos] = mExpandedFacets[len-1];
		// ... and reduce the array's length
		mExpandedFacets.length = len - 1;
	}
	
	/**
	 * Shows the property values of all expanded facets.
	 */
	that.showExpandedFacets = function () {
		for (var i = 0; i < mExpandedFacets.length; ++i) {
			var facet = mExpandedFacets[i];
			FacetedSearch.classes.ClusterWidget.showPropertyDetailsHandler(facet);
		}
	}
	
	/**
	 * Constructor for the FacetedSearch class.
	 */
	function construct() {
		mAjaxSolrManager = new AjaxSolr.Manager({
			solrUrl: wgFSSolrURL
			});
		
		mAjaxSolrManager.addWidget(new FacetedSearch.classes.ResultWidget({
		  id: 'article',
		  target: '#docs'
		}));
		
		// Add the widgets for the standard facets
		var categoryFacet = FACET_FIELDS[0];
		var relationFacet = FACET_FIELDS[1];
		var attributeFacet = FACET_FIELDS[2];
		var namespaceFacet = FACET_FIELDS[3];
		mAjaxSolrManager.addWidget(new FacetedSearch.classes.FacetWidget({
			id : 'fsf' + categoryFacet,
			target : '#field_categories',
			field : categoryFacet
		}));
		mAjaxSolrManager.addWidget(new FacetedSearch.classes.NamespaceFacetWidget({
			id : 'fsf' + namespaceFacet,
			target : '#field_namespaces',
			field : namespaceFacet
		}));
		mRelationWidget = new FacetedSearch.classes.FacetWidget({
			id : 'fsf' + relationFacet,
			target : '#field_dummy',
			field : relationFacet,
			noRender : true
		});
		mAjaxSolrManager.addWidget(mRelationWidget);
		mAjaxSolrManager.addWidget(new FacetedSearch.classes.FacetWidget({
			id : 'fsf' + attributeFacet,
			target : '#field_properties',
			field : attributeFacet,
			fields : [ relationFacet, attributeFacet ]
		}));

		// paging
		var lang = FacetedSearch.singleton.Language;

		mAjaxSolrManager.addWidget(new FacetedSearch.classes.PagerWidget({
			id : 'pager',
			target : '#pager',
			prevLabel : lang.getMessage('pagerPrevious'),
			nextLabel : lang.getMessage('pagerNext'),
			renderHeader : function(perPage, offset, total) {
				$('#pager-header').html(
						$('<span/>').text(
								lang.getMessage('results') + ' ' 
								+ Math.min(total, offset + 1)
								+ ' ' + lang.getMessage('to') + ' '
								+ Math.min(total, offset + perPage)
								+ ' '+ lang.getMessage('of') + ' ' + total));
			}
		}));
		
		// current search filters
		mAjaxSolrManager.addWidget(new FacetedSearch.classes.CurrentSearchWidget({
			id: 'currentsearch',
		  	target: '#selection'
		}));

		// init
		mAjaxSolrManager.init();

		// add facets
		var params = {
			facet: true,
			'facet.field': FACET_FIELDS,
			'facet.mincount': 1,
			'json.nl': 'map'
		};
		for (var name in params) {
			mAjaxSolrManager.store.addByValue(name, params[name]);
		}

		mAjaxSolrManager.store.addByValue('q', '*:*');
		
		// add facets
		for (var name in params) {
			mAjaxSolrManager.store.addByValue(name, params[name]);
		}
		
		checkSolrPresent();	
	};
	that.construct = construct;
	
	/**
	 * Keyup event handler for the search input field.
	 */
	that.onSearchKeyup = function () {
		mSearch = $('#query').val();
		var qs = '*'+mSearch+'*';
		if (mSearch.length == 0) {
			qs = '*';
		}
		mAjaxSolrManager.store.addByValue('q', QUERY_FIELD+':'+qs);
		mAjaxSolrManager.doRequest(0);
		
//		if (mDebug) { console.log("Filter: "+mFilter+"\n"); }
//		if (typeof mFilterTimeout !== 'undefined') {
//			if (mDebug) { console.log("Clearing timeout.\n"); }
//			clearTimeout(mFilterTimeout);
//		}
//		mFilterTimeout = setTimeout(that.filterGroupTree, 300, mFilter);

		return false;
	};
	
	
	/**
	 * Initializes the event handlers for the User Interface.
	 */
	function addEventHandlers() {
		
		// Keyup handler for the search input field
		$('#query').keyup(that.onSearchKeyup);
	}
	
	/**
	 * Checks if the SOLR server is responding
	 */
	function checkSolrPresent() {
		var solrPresent = false;
		var sm = new AjaxSolr.Manager({
			solrUrl : wgFSSolrURL,
			handleResponse : function (data) {
				solrPresent = true;
			}
		});
		sm.init();
		sm.store.addByValue('q', '*:*');		
		sm.doRequest(0);
		setTimeout(function () {
			if (!solrPresent) {
				var lang = FacetedSearch.singleton.Language;
				$("#results").text(lang.getMessage('solrNotFound'));
			}
		}, 2000);

	}
	
	/**
	 * Checks if the given name is a name for an attribute or relation.
	 * 
	 * @param {string} name
	 * 		The name to examine
	 * @return {bool}
	 * 		true, if name is a property name
	 */
	function isProperty(name) {
		return name.match(ATTRIBUTE_REGEX)|| name.match(RELATION_REGEX);
	}
	
	
	construct();
	addEventHandlers();
	
	// Show all results at start up
	mAjaxSolrManager.doRequest(0);
	
	that.FACET_FIELDS = FACET_FIELDS;
	return that;
	
}

jQuery(document).ready(function() {
	if (!FacetedSearch.singleton) {
		FacetedSearch.singleton = {};
	}
	FacetedSearch.singleton.FacetedSearchInstance = FacetedSearch.classes.FacetedSearch();
});
