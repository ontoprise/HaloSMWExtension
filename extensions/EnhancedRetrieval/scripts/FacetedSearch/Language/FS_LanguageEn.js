/*
 * Copyright (C) Vulcan Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program.If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 * @file
 * @ingroup FacetedSearchScripts
 * @author: Thomas Schweitzer
 */

if (typeof window.FacetedSearch == "undefined") {
// Define the FacetedSearch  module	
	window.FacetedSearch  = { 
		classes : {}
	};
}

/**
 * @class FSLanguageEn
 * This class contains the english language string for the faceted search UI
 * 
 */
FacetedSearch.classes.FSLanguageEn = function () {
	
	// The instance of this object
	var that = FacetedSearch.classes.FSLanguage();
	
	that.mMessages = {
'solrNotFound'		: 'Cannot connect to SOLR Server. Faceted Search will not work. '+
					  'Expecting to find SOLR Server at ' + wgFSSolrURL + wgFSSolrServlet + '. ' +
					  'Possibly your firewall is blocking the SOLR port.',
'more' 				: 'more',
'less' 				: 'less',
'noFacetFilter'		: '(no facets selected)',
'underspecifiedSearch' : 'Your current search may match too many results. Please refine it!',
'removeFilter'		: 'Remove this facet',
'removeRestriction'	: 'Remove restriction',
'removeAllFilters'	: 'Remove all facets',
'pagerPrevious'		: '&lt; Previous',
'pagerNext'			: 'Next &gt;',
'results'			: 'Results',
'to'				: 'to',
'of'				: 'of',
'inCategory'		: 'is in category',
'show'				: 'Show properties',
'hide'				: 'Hide properties',
'showDetails'		: 'Show details',
'hideDetails'		: 'Hide details',
'lastChange'		: 'Last change',
'addFacetOrQuery'	: 'Please enter a search term or select a facet!',
'mainNamespace'		: 'Main',
'namespaceTooltip'  : '$1 article(s) in this namespace match the selection.',
'allNamespaces'		: 'All namespaces',
'nonexArticle'		: 'The article does not exist. Click here to create it:',
'searchLink' 		: 'Link to this search',
'searchLinkTT'		: 'Right click to copy or bookmark this search'
	};
	
	return that;
	
}

jQuery(document).ready(function() {
	if (!FacetedSearch.singleton) {
		FacetedSearch.singleton = {};
	}
	FacetedSearch.singleton.Language = FacetedSearch.classes.FSLanguageEn();
});
