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
//	Define the FacetedSearch module	
	var FacetedSearch = { 
			classes : {}
	};
}

/**
 * @class FacetPropertyValueWidget
 * This class handles the facet fields for the values of a property.
 * 
 */
FacetedSearch.classes.FacetPropertyValueWidget = FacetedSearch.classes.FacetWidget.extend({
	
	superAfterRequest: null,
	
	/**
	 * @param {String} value The value.
	 * @returns {Function} Sends a request to Solr if it successfully adds a
	 *   filter query with the given value.
	 */
	clickHandler: function(value){
		var self = this;
		return function(){
			if (self.add(value)) {
				// Do the request with the 'global' Solr Manager and not the
				// temporary one that is registered in this widget.
				var mgr = FacetedSearch.singleton.FacetedSearchInstance.getAjaxSolrManager();
				
				var field;
				var ATTRIBUTE_REGEX = /smwh_(.*)_xsdvalue_(.*)/;
				if (self.field.match(ATTRIBUTE_REGEX)) {
					// Attribute field
					field = FacetedSearch.singleton.FacetedSearchInstance.FACET_FIELDS[1];
				} else {
					// Relation field
					field = FacetedSearch.singleton.FacetedSearchInstance.FACET_FIELDS[2];
				}
				// For attributes and relations the type of the field must be
				// t (text) if type s (string) is given
				var fieldName = self.field;
				if (fieldName.match(/.*?_s$/)) {
					fieldName = fieldName.slice(0, fieldName.length-1) + 't';
				}
				mgr.store.addByValue('fq', field + ':' + fieldName);

				mgr.doRequest(0);
			}
			return false;
		}
	},
	
	fpvwAfterRequest: function(){
		var $ = jQuery;
		
		// Check if there is a restriction on property values
		var propValRestricted = false;
		var mgr = FacetedSearch.singleton.FacetedSearchInstance.getAjaxSolrManager();
		var fq = mgr.store.values('fq');
		for (var i = 0, l = fq.length; i < l; i++) {
			if (fq[i].indexOf(this.field) == 0) {
				propValRestricted = true;
				break;
			}
		}
		this.setFacetTheme('propertyValueFacet');
		this.setHideSelectedFacet(false);
		this.setRemoveFacet(propValRestricted);
		if (propValRestricted) {
			this.setClickHandler(this.clickRemoveRangeHandler(this.field));
		}
		this.superAfterRequest();
		
	},
	
	initObject: function(){
		this.superAfterRequest = this.afterRequest;
		this.afterRequest = this.fpvwAfterRequest;
	},
	
	/**
	 * Removes a range restriction for a facet.
	 * @param {string} facet
	 * 		Name of the facet
	 */
	clickRemoveRangeHandler: function (facet) {
		var self = this;
		return function() {
			var fsm = FacetedSearch.singleton.FacetedSearchInstance.getAjaxSolrManager();
			var fq = fsm.store.values('fq');
			for (var i = 0, l = fq.length; i < l; i++) {
				if (fq[i].indexOf(facet) == 0) {
					fsm.store.removeByValue('fq', fq[i]);
					break;
				}
			}
			fsm.doRequest(0);
			return false;
		};
	}
	
});


