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
 * FacetClustererFactory
 * This function returns an instance of a FacetClusterer class for a given facet.
 * Facets can have different types like number, string, date etc. and each type
 * needs its own clusterer. 
 * 
 */
if (typeof FacetedSearch.factory == "undefined") {
	FacetedSearch.factories = {};
}

/**
 * This is a factory function for facet clusterers.
 * @param {Object} facetName
 * 
 * @return {Object}
 * 		An instance of a sub class of FacetClusterer or null.
 */
FacetedSearch.factories.FacetClustererFactory = function (facetName) {
	var ATTRIBUTE_REGEX = /smwh_(.*)_xsdvalue_(.*)/;
	var RELATION_REGEX  = /smwh_(.*)_(.*)/;

	var nameType = facetName.match(ATTRIBUTE_REGEX);
	if (!nameType) {
		// maybe a relation facet
		nameType = facetName.match(RELATION_REGEX);
		if (!nameType) {
			return null;
		}
	}
	var name = nameType[1];
	var type = nameType[2];
	switch (type) {
		case 'd':
		case 'i':
			// numeric
			return FacetedSearch.classes.NumericFacetClusterer(facetName, name);
		case 'dt':
			// date
			return FacetedSearch.classes.DateFacetClusterer(facetName, name);
		case 't':
			return FacetedSearch.classes.StringFacetClusterer(facetName, name);
		case 'b':
			return FacetedSearch.classes.BooleanFacetClusterer(facetName, name);
		default:
			return FacetedSearch.classes.StringFacetClusterer(facetName, name);
	}
	return null;
}
