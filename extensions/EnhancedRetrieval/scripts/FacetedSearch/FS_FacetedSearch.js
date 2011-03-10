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
	
	var FACET_FIELDS = ['smwh_categories', 'smwh_attributes', 'smwh_properties'];
	
	//--- Private members ---

	// The instance of this object
	var that = {};
	
	// AjaxSolr.Manager - The manager from the AjaxSolr library.
	var mAjaxSolrManager;
	
	// string - The current search string
	var mSearch = '';

	/**
	 * Constructor for the FacetedSearch class.
	 */
	function construct() {
		mAjaxSolrManager = new AjaxSolr.Manager();
		mAjaxSolrManager.init();
		// TEST
		mAjaxSolrManager.store.addByValue('q', '*:*');
		
		mAjaxSolrManager.addWidget(new FacetedSearch.classes.ResultWidget({
		  id: 'article',
		  target: '#docs'
		}));

		// add facets
		var params = {
			facet: true,
			'facet.field': FACET_FIELDS,
			'facet.limit': 20,
			'facet.mincount': 1,
			'f.topics.facet.limit': 50,
			'json.nl': 'map'
		};
		for (var name in params) {
			mAjaxSolrManager.store.addByValue(name, params[name]);
		}
		
		// Add the widgets for the standard facets
		for (var i = 0, l = FACET_FIELDS.length; i < l; i++) {
			var fieldName = FACET_FIELDS[i];
			mAjaxSolrManager.addWidget(new FacetedSearch.classes.FacetWidget({
				id: 'fsf'+fieldName,
				target: '#field_' + fieldName,
				field: fieldName
			}));
		}

		// paging
		mAjaxSolrManager.addWidget(new AjaxSolr.PagerWidget({
			id : 'pager',
			target : '#pager',
			prevLabel : '&lt; Previous',
			nextLabel : 'Next &gt;',
//			innerWindow : 1,
			renderHeader : function(perPage, offset, total) {
				$('#pager-header').html(
						$('<span/>').text(
								'Result ' + Math.min(total, offset + 1)
										+ ' to '
										+ Math.min(total, offset + perPage)
										+ ' of ' + total));
			}
		}));
		
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
	return that;
	
}

jQuery(document).ready(function() {
	FacetedSearch.classes.FacetedSearch();
});
