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
'solrNotFound'		: 'Es konnte keine Verbindung zum SOLR Server hergestellt werden. ' +
					  'Die facettierte Suche wird nicht funktionieren. '+
					  'Der SOLR Server wird hier gesucht: ' + wgFSSolrURL,
'more' 				: 'mehr',
'less' 				: 'weniger',
'noFacetFilter'		: '(Keine Facetten ausgewählt.)',
'removeFilter'		: 'Diese Facette enfernen',
'removeRestriction'	: 'Einschränkung entfernen',
'removeAllFilters'	: 'Alle Facetten entfernen',
'pagerPrevious'		: '&lt; Vorherige',
'pagerNext'			: 'Nächste &gt;',
'results'			: 'Resultate',
'to'				: 'bis',
'of'				: 'von',
'inCategory'		: 'ist in Kategorie',
'show'				: 'Eigenschaften zeigen',
'hide'				: 'Eigenschaften ausblenden',
'showDetails'		: 'Zeige Details',
'hideDetails'		: 'Details ausblenden',
'lastChange'		: 'Letzte Änderung',
'addFacetOrQuery'	: 'Bitte geben Sie einen Suchbegriff ein oder wählen Sie eine Facette aus!',
'mainNamespace'		: 'Main',
'namespaceTooltip'  : '$1 Artikel in diesem Namensraum passen zur Auswahl',
'allNamespaces'		: 'Alle Namensräume',
'nonexArticle'		: 'Der Artikel existiert nicht. Klicken Sie hier, um ihn zu erstellen:'

	};
	
	return that;
	
}

jQuery(document).ready(function() {
	if (!FacetedSearch.singleton) {
		FacetedSearch.singleton = {};
	}
	FacetedSearch.singleton.Language = FacetedSearch.classes.FSLanguageDe();
});