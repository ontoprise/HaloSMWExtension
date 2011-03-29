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
// Define the FacetedSearch  module	
	var FacetedSearch  = { 
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
'more' 				: 'more',
'less' 				: 'less',
'noFacetFilter'		: '(no facets selected)',
'removeFilter'		: 'Remove filter',
'removeRestriction'	: 'Remove restriction',
'removeAllFilters'	: 'Remove all filters',
'pagerPrevious'		: '&lt; Previous',
'pagerNext'			: 'Next &gt;',
'results'			: 'Results',
'to'				: 'to',
'of'				: 'of',
'inCategory'		: 'is in category',
'hasProperties'		: 'has properties',
'show'				: 'show',
'hide'				: 'hide',
'showDetails'		: 'Show details',
'hideDetails'		: 'Hide details',
'lastChange'		: 'Last change'

		
	};
	
	return that;
	
}

jQuery(document).ready(function() {
	if (!FacetedSearch.singleton) {
		FacetedSearch.singleton = {};
	}
	FacetedSearch.singleton.Language = FacetedSearch.classes.FSLanguageEn();
});