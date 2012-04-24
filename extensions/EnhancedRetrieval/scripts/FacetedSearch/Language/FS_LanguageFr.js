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
 * @author: Patrick Barret
 */

if (typeof window.FacetedSearch == "undefined") {
// Define the FacetedSearch  module	
	window.FacetedSearch  = { 
		classes : {}
	};
}

/**
 * @class FSLanguageFr
 * This class contains the french language string for the faceted search UI
 * 
 */
FacetedSearch.classes.FSLanguageFr = function () {
	
	// The instance of this object
	var that = FacetedSearch.classes.FSLanguage();
	
	that.mMessages = {
'solrNotFound'		: 'Impossible de se connecter au serveur SOLR. La recherche à facettes ne fonctionnera pas. '+
					  'Le serveur SOLR devrait se trouver à l\'URL ' + wgFSSolrURL + wgFSSolrServlet + '. ' +
					  'Peut-être que votre pare-feu bloque le port SOLR.',
'tryConnectSOLR'	: 'Trying to connect to the search engine...',
'more' 				: 'plus',
'less' 				: 'moins',
'noFacetFilter'		: '(pas de facettes sélectionnées)',
'underspecifiedSearch' : 'Votre recherche actuelle comprend trop de résultats. Veuillez l\'améliorer !',
'removeFilter'		: 'Retirer cette facette',
'removeRestriction'	: 'Enlever les restrictions',
'removeAllFilters'	: 'Retirez toutes les facettes',
'pagerPrevious'		: '&lt; Précédent',
'pagerNext'			: 'Suivant &gt;',
'results'			: 'Résultats',
'to'				: 'à',
'of'				: 'sur',
'ofapprox'			: 'sur',
'inCategory'		: 'est dans la catégorie',
'show'				: 'Afficher les attributs',
'hide'				: 'Masquer les attributs',
'showDetails'		: 'Afficher les détails',
'hideDetails'		: 'Masquer  les détails',
'lastChange'		: 'Dernier changement',
'addFacetOrQuery'	: 'Veuillez saisir un terme de recherche ou sélectionner une facette !',
'mainNamespace'		: 'Principal',
'namespaceTooltip'  : '$1 article(s) dans cet espace de noms correspondant à la sélection.',
'allNamespaces'		: 'Tous les espaces de noms',
'nonexArticle'		: 'Cet article n\'existe pas. Cliquez ici pour le créer:',
'searchLink' 		: 'Lien de cette recherche',
'searchLinkTT'		: 'Faites un clic droit pour copier cette recherche ou la mettre dans vos favoris'
	};
	
	return that;
	
}

jQuery(document).ready(function() {
	if (!FacetedSearch.singleton) {
		FacetedSearch.singleton = {};
	}
	FacetedSearch.singleton.Language = FacetedSearch.classes.FSLanguageFr();
});