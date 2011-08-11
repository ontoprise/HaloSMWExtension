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
 * @class StringFacetClusterer
 * This class shows the values of a facet with type "string".
 * 
 */
FacetedSearch.classes.StringFacetClusterer = function (facetName, plainName) {

	//--- Constants ---
	
	//--- Private members ---

	
	// Call the constructor of the super class
	var that = FacetedSearch.classes.FacetClusterer(facetName, plainName);
	

	/**
	 * Constructor for the StringFacetClusterer class.
	 * 
	 * @param string facetName
	 * 		The full name of the facet whose values are clustered. 
	 */
	function construct(facetName, plainName) {
	};
	that.construct = construct;
	
	/**
	 * Retrieves the clusters for the facet of this instance
	 */
	that.retrieveClusters = function () {
		var asm = that.getAjaxSolrManager();
		var facet = that.getFacetName();
		
		// If the type of the facet is t (text) the corresponding field with 
		// type s (string) has to be queried
		var queryFacet = facet;
		if (facet.charAt(facet.length-1) == 't') {
			queryFacet = facet.slice(0, facet.length-1) + 's';
		}
		
		var fpvw = new FacetedSearch.classes.FacetPropertyValueWidget({
			id : 'fsf' + facet,
			target : '#'+AjaxSolr.theme.prototype.getPropertyValueHTMLID(facet),
			field : queryFacet
		});
		fpvw.initObject();

		asm.addWidget(fpvw);
		asm.store.addByValue('facet.field', queryFacet);

		asm.doRequest(0);
		
	}
			
	construct(facetName, plainName);
	return that;
	
}
