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
 * @class FSLanguageDe
 * This class contains the german language string for the faceted search UI
 * 
 */
FacetedSearch.classes.FSLanguageDe = function () {
	
	// The instance of this object
	var that = FacetedSearch.classes.FSLanguage();
	
	that.mMessages = {
'more' 				: 'mehr',
'less' 				: 'weniger',
'noFacetFilter'		: '(Keine Facetten ausgewählt.)',
'removeFilter'		: 'Filter enfernen',
'removeRestriction'	: 'Einschränkung entfernen',
'removeAllFilters'	: 'Alle Filter entfernen',
'pagerPrevious'		: '&lt; Vorherige',
'pagerNext'			: 'Nächste &gt;',
'results'			: 'Resultate',
'to'				: 'bis',
'of'				: 'von',
'inCategory'		: 'ist in Kategorie',
'hasProperties'		: 'hat Eigenschaften',
'show'				: 'zeigen',
'hide'				: 'ausblenden',
'showDetails'		: 'Zeige Details',
'hideDetails'		: 'Details ausblenden',
'lastChange'		: 'Letzte Änderung'
	};
	
	return that;
	
}

jQuery(document).ready(function() {
	if (!FacetedSearch.singleton) {
		FacetedSearch.singleton = {};
	}
	FacetedSearch.singleton.Language = FacetedSearch.classes.FSLanguageDe();
});