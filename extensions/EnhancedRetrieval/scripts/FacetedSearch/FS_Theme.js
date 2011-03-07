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

/**
 * This file defines the theme i.e. how certain elements are represented as HTML.
 */

(function ($) {

	const FS_CATEGORIES = 'smwh_categories';
	const FS_ATTRIBUTES = 'smwh_attributes';
	const FS_PROPERTIES = 'smwh_properties';
	
	/**
	 * Theme for article titles and their semantic data.
	 * 
	 * @param doc
	 * 		The article given as SOLR document
	 * @param data
	 * 		HTML representation of the semantic data
	 */
	AjaxSolr.theme.prototype.article = function (doc, data) {
		var output = '<div><b>' + doc.smwh_title + '</b>';
		output += '<p id="links_' + doc.id + '" class="links"></p>';
		output += '<p>' + data + '</p></div>';
		return output;
	};

	/**
	 * Theme for the semantic data of an article.
	 * 
	 * @param doc
	 * 		The article given as SOLR document
	 */
	AjaxSolr.theme.prototype.data = function (doc) {

		var output = '';
		var attr  = doc[FS_ATTRIBUTES] || [];
		var props = doc[FS_PROPERTIES] || [];
		var cats  = doc[FS_CATEGORIES];
		var propertyRegEx = /^smwh_(.*)_(.*)$/;
		var attributeRegEx = /smwh_(.*)_xsdvalue_(.*)/;
		
		if (typeof cats !== 'undefined') {
			// Show all categories
			output += '<p>Categories<br/><ul>';
			for ( var i = 0; i < cats.length; i++) {
				output += '<li>'+cats[i]+'</li>';
			}
			output += '</ul></p>';
		}
		
		if (props.length + attr.length > 0) {
			// Properties or attributes are present 
			// => add a table header
			output += '<p>Properties<br/><table class="property_table">';
		}
		// Show all properties
		if (props.length > 0) {
			// The property array may contain duplicates 
			// => create an object without duplicates
			var propMap = {};
			for (var i = 0; i < props.length; i++) {
				propMap[props[i]] = true;
			}
			
			// Show all properties in a table
			for (var property in propMap) {
				// Get the property name without prefix, suffix and type
				var plainName = property.match(propertyRegEx);
				if (plainName) {
					plainName = plainName[1].replace(/_/g,' ');
					output += '<tr>';
					output += '<td>'+plainName+'</td>';
					output += '<td>'+doc[property].join(', ')+'</td>';
					output += '</tr>';
				}
			}
		}
		
		if (attr.length > 0) {
			// The property array may contain duplicates 
			// => create an object without duplicates
			var attrMap = {};
			for (var i = 0; i < attr.length; i++) {
				attrMap[attr[i]] = true;
			}
			
			for (var attribute in attrMap) {
				// Get the property name without prefix, suffix and type
				var plainName = attribute.match(attributeRegEx);
				if (plainName) {
					plainName = plainName[1].replace(/_/g,' ');
					output += '<tr>';
					output += '<td>'+plainName+'</td>';
					output += '<td>'+doc[attribute].join(', ')+'</td>';
					output += '</tr>';
				}
			}
		}
		
		if (props.length + attr.length > 0) {
			// Properties or attributes are present 
			// => close the table
			output += '</table></p>';
		}
		
		return output;
	};

	AjaxSolr.theme.prototype.facet = function(value, weight, handler) {
		return $('<a href="#" class="tagcloud_item"/>').text(value).addClass(
				'tagcloud_size_' + weight).click(handler);
	};

	AjaxSolr.theme.prototype.facet_link = function(value, handler) {
		return $('<a href="#"/>').text(value).click(handler);
	};

	AjaxSolr.theme.prototype.no_items_found = function() {
		return 'no items found in current selection';
	};

})(jQuery);

