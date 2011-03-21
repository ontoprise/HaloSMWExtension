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
	var FACET_FIELDS = ['smwh_categories', 'smwh_attributes', 'smwh_properties'];

	
	//--- Private members ---

	// The instance of this object
	var that = {};
	
	// AjaxSolr.Manager - The manager from the AjaxSolr library.
	var mAjaxSolrManager;
	
	// string - The current search string
	var mSearch = '';
	
	// reference to the (dummy) relation widget
	var mRelationWidget;
	 
	//--- Getters/Setters ---
	that.getAjaxSolrManager = function() {
		return mAjaxSolrManager;
	}
	
	that.getRelationWidget = function() {
		return mRelationWidget;
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
		mAjaxSolrManager.addWidget(new FacetedSearch.classes.FacetWidget({
			id : 'fsf' + categoryFacet,
			target : '#field_categories',
			field : categoryFacet
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
		mAjaxSolrManager.addWidget(new AjaxSolr.PagerWidget({
			id : 'pager',
			target : '#pager',
			prevLabel : '&lt; Previous',
			nextLabel : 'Next &gt;',
			renderHeader : function(perPage, offset, total) {
				$('#pager-header').html(
						$('<span/>').text(
								'Result ' + Math.min(total, offset + 1)
										+ ' to '
										+ Math.min(total, offset + perPage)
										+ ' of ' + total));
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
		mAjaxSolrManager.doRequest();
		
//		if (mDebug) { console.log("Filter: "+mFilter+"\n"); }
//		if (typeof mFilterTimeout !== 'undefined') {
//			if (mDebug) { console.log("Clearing timeout.\n"); }
//			clearTimeout(mFilterTimeout);
//		}
//		mFilterTimeout = setTimeout(that.filterGroupTree, 300, mFilter);

		return false;
	};
	
	
	/**
	 * Initializes the event handlers for th User Interface.
	 */
	function addEventHandlers() {
		
		// Keyup handler for the search input field
		$('#query').keyup(that.onSearchKeyup);
	}
	
	construct();
	addEventHandlers();
	mAjaxSolrManager.doRequest();
	
	that.FACET_FIELDS = FACET_FIELDS;
	return that;
	
}

jQuery(document).ready(function() {
	FacetedSearch.singleton = {};
	FacetedSearch.singleton.FacetedSearchInstance = FacetedSearch.classes.FacetedSearch();
});
