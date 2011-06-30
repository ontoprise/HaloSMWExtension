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
	var QUERY_FIELD = 'smwh_search_field';

	// The field on which highlighting is enabled
	var HIGHLIGHT_FIELD = 'smwh_search_field';
	
	// Name of the field with the document ID
	var DOCUMENT_ID = 'id';
	
	// Name of the SOLR field that stores relation values
	var RELATION_FIELD = 'smwh_properties';
	
	// Name of the SOLR field that stores attribute values
	var ATTRIBUTE_FIELD = 'smwh_attributes';

	// Name of the SOLR field that stores the modification date of an article
	var MODIFICATION_DATE_FIELD = 'smwh_Modification_date_xsdvalue_dt';

	// Name of the SOLR field that stores the title of an article with type
	// 'wiki'
	var TITLE_FIELD = 'smwh_title';

	// Name of the SOLR field that stores the namespace id of an article
	var NAMESPACE_FIELD = 'smwh_namespace_id';

	// Name of the SOLR field that stores the title of an article as string.
	// This is used for sorting search results.
	var TITLE_STRING_FIELD = 'smwh_title_s';
	
	// Names of the facet classes
	var FACET_FIELDS = ['smwh_categories', ATTRIBUTE_FIELD, RELATION_FIELD,
						NAMESPACE_FIELD];
						
	// Names of all fields that are returned in a query for documents
	var QUERY_FIELD_LIST = [MODIFICATION_DATE_FIELD,
							'smwh_categories', 
							ATTRIBUTE_FIELD, 
							RELATION_FIELD,
							DOCUMENT_ID,
							TITLE_FIELD,
							NAMESPACE_FIELD];
						
	var RELATION_REGEX = /^smwh_(.*)_(.*)$/;
	var ATTRIBUTE_REGEX = /smwh_(.*)_xsdvalue_(.*)/;
	
	//--- Private members ---

	// The instance of this object
	var that = {};
	
	// AjaxSolr.Manager - The manager from the AjaxSolr library.
	var mAjaxSolrManager;
	
	// string - The current search string
	var mSearch = '';
	
	// {bool} If true, the current search term is an expert query (i.e. may
	//        contain logical operations and more)
	var mExpertQuery = false;
	
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
	
	that.getSearch = function () {
		return mSearch;
	}
	
	that.isExpertQuery = function () {
		return mExpertQuery;
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

		// add parametes
		var params = {
			facet: true,
			'facet.field': FACET_FIELDS,
			'facet.mincount': 1,
			'json.nl': 'map',
			fl: QUERY_FIELD_LIST,
			hl: true,
			'hl.fl': HIGHLIGHT_FIELD,
			'hl.simple.pre' : '<b>',
			'hl.simple.post': '</b>',
			'hl.fragsize': '250',
			'sort' : MODIFICATION_DATE_FIELD + ' desc'
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
		updateSearchResults();
		return false;
	};
	
	/**
	 * Event handler for the search order selection field. A new SOLR resquest is
	 * sent for the new search result order.
	 */
	that.onSearchOrderChanged = function() {
	
		var selected =  $("#search_order option:selected");
		var order = selected[0].value;
		var sort = MODIFICATION_DATE_FIELD + ' desc';
		switch (order) {
			case "relevance":
				sort = 'score desc';
				break;
			case "newest":
				sort = MODIFICATION_DATE_FIELD + ' desc, score desc';
				break;
			case "oldest":
				sort = MODIFICATION_DATE_FIELD + ' asc, score desc';
				break;
			case "ascending":
				sort = TITLE_STRING_FIELD + ' asc, score desc';
				break;
			case "descending":
				sort = TITLE_STRING_FIELD + ' desc, score desc';
				break;
		}
		mAjaxSolrManager.store.addByValue('sort', sort);
		mAjaxSolrManager.doRequest(0);
		return false;
	}
	
	/**
	 * Event handler for clicking the search button. A new SOLR request is 
	 * triggered.
	 */
	that.onSearchButtonClicked = function () {
		updateSearchResults();
	}
	
	/**
	 * Gets the search term from the input field and triggers a new SOLR request.
	 * All widgets will be updated.
	 */
	function updateSearchResults() {
		mSearch = $('#query').val();
		// trim the search term
		mSearch = mSearch.replace(/^\s*(.*?)\s*$/,'$1');
		
		var qs = mSearch;

		// If the query is enclosed in braces it is treated as expert query.
		// Expert queries may contain logical operators. Text is not converted
		// to lowercase.
		mExpertQuery = qs.charAt(0) === '(' 
					   && qs.charAt(mSearch.length-1) === ')';
		if (!mExpertQuery) {
			qs = qs.toLowerCase();
			// Escape the special characters:
			// + - && || ! ( ) { } [ ] ^ " ~ * ? : \			
			qs = qs.replace(/([\+\-!\(\)\{\}\[\]\^"~\*\?\\:])/g, '\\$1');
			qs = qs.replace(/(&&|\|\|)/g,'\\$1');
			// Match all tokens that start with the search term
			qs = qs + '*';
		} else {
			// A colon in the search term must be escaped otherwise SOLR will throw
			// a parser exception
			qs = qs.replace(/(:)/g,"\\$1");
		}
		mAjaxSolrManager.store.addByValue('q', QUERY_FIELD+':'+qs);
		mAjaxSolrManager.doRequest(0);
		
	}
	
	/**
	 * Initializes the event handlers for the User Interface.
	 */
	function addEventHandlers() {
		
		// Keyup handler for the search input field
		$('#query').keyup(that.onSearchKeyup);
		$('#search_order').change(that.onSearchOrderChanged);
		$('#search_button').click(that.onSearchButtonClicked);
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
	 * This function retrieves all namespaces that are currently populated in the
	 * wiki. The namespace widget is initialized with these namespaces.
	 */
	function initNamespaces() {
		var sm = new AjaxSolr.Manager({
			solrUrl : wgFSSolrURL,
			handleResponse : function (data) {
				var namespaces = data.facet_counts.facet_fields[NAMESPACE_FIELD];
				var ns = [];
				for (var nsid in namespaces) {
					ns.push(nsid);
				}
				mAjaxSolrManager.addWidget(new FacetedSearch.classes.NamespaceFacetWidget({
					id : 'fsf' + NAMESPACE_FIELD,
					target : '#field_namespaces',
					field : NAMESPACE_FIELD,
					mNamespaces: ns
				}));
				
			}
		});
		sm.init();
		sm.store.addByValue('q', '*:*');		
		sm.store.addByValue('fl', NAMESPACE_FIELD);		
		sm.store.addByValue('facet', true);		
		sm.store.addByValue('facet.field', NAMESPACE_FIELD);		
		sm.store.addByValue('json.nl', 'map');	
		sm.doRequest(0);

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
	
	initNamespaces();
	
	// Show all results at start up
	updateSearchResults();
	
	// Public constants
	that.FACET_FIELDS		= FACET_FIELDS;
	that.DOCUMENT_ID		= DOCUMENT_ID;
	that.HIGHLIGHT_FIELD	= HIGHLIGHT_FIELD;
	that.RELATION_FIELD		= RELATION_FIELD;
	that.ATTRIBUTE_FIELD	= ATTRIBUTE_FIELD;
	that.NAMESPACE_FIELD	= NAMESPACE_FIELD;
	that.TITLE_STRING_FIELD	= TITLE_STRING_FIELD;
	that.TITLE_FIELD		= TITLE_FIELD;
	return that;
	
}

jQuery(document).ready(function() {
	if (!FacetedSearch.singleton) {
		FacetedSearch.singleton = {};
	}
	FacetedSearch.singleton.FacetedSearchInstance = FacetedSearch.classes.FacetedSearch();
});
