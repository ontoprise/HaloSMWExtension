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

	var FS_CATEGORIES = 'smwh_categories';
	var FS_ATTRIBUTES = 'smwh_attributes';
	var FS_PROPERTIES = 'smwh_properties';
	var MOD_ATT = 'smwh_Modification_date_xsdvalue_dt';
	var MAX_CAT = 5;

	var SEARCH_PATH = '/extensions/EnhancedRetrieval/skin/images/';
	var NS_ICON = {
		// TODO add missing mappings
		0 : wgScriptPath + SEARCH_PATH + 'smw_plus_instances_icon_16x16.png',
		6 : wgScriptPath + SEARCH_PATH + 'smw_plus_image_icon_16x16.png',
		102 : wgScriptPath + SEARCH_PATH + 'smw_plus_property_icon_16x16.png',
		700 : wgScriptPath + SEARCH_PATH + 'smw_plus_comment_icon_16x16.png'
	};
	
	function noUnderscore(string) {
		return string.replace(/_/g, ' ');
	}
	
	function getIconForNSID(id) {
		return '<img src="' + NS_ICON[id] + '"/>';
	}
	
	/**
	 * Theme for article titles and their semantic data.
	 * 
	 * @param doc
	 * 		The article given as SOLR document
	 * @param data
	 * 		HTML representation of the semantic data
	 */
	AjaxSolr.theme.prototype.article = function (doc, data) {
		// TODO check link
		var output = '<div class="xfsResult"><a class="xfsResultTitle" href="' + doc.smwh_title + '">';
		output += noUnderscore(doc.smwh_title) + '</a>';
		output += getIconForNSID(doc.smwh_namespace_id);
		// output += '<p id="links_' + doc.id + '" class="links"></p>';
		output += '<div>' + data + '</div></div>';
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
			// Show MAX_CAT categories
			output += '<div class="xfsResultCategory"><p>is in category: ';
			var count = Math.min(cats.length, MAX_CAT);
			var vals = [];
			for ( var i = 0; i < count; i++) {
				// TODO check link
				vals.push('<a href="' + cats[i] + '">' + noUnderscore(cats[i]) + '</a>');
			}
			if (count < cats.length) {
				vals.push('... (' + (cats.length - count) + ' more)');
			}
			output += vals.join(' | ');
			output += '</p></div>';
		}
		
		if (props.length + attr.length > 0) {
			// Properties or attributes are present 
			// => add a table header
			output += '<div class="xfsResultTable"><table>';
		}
		var row = 0;
		
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
					plainName = noUnderscore(plainName[1]);
					output += '<tr class="s' + (row % 2) + '">';
					row += 1;
					output += '<td>' + plainName + '</td>';
					var vals = [];
					$.each(doc[property], function() {
						// TODO check link
						vals.push('<a href="' + this + '">' + noUnderscore(this) + '</a>');
					});
					output += '<td>' + vals.join(', ') + '</td>';
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
					plainName = noUnderscore(plainName[1]);
					output += '<tr class="s' + (row % 2) + '">';
					row += 1;
					output += '<td>'+plainName+'</td>';
					output += '<td>'+doc[attribute].join(', ')+'</td>';
					output += '</tr>';
				}
			}
		}
		
		if (props.length + attr.length > 0) {
			// Properties or attributes are present 
			// => close the table
			output += '</table></div>';
		}
		
		// TODO check if field is set
		// TODO remove property from previous listing?
		// TODO handling of timezone, date formatting?
		output += '<div class="xfsResultModified"><p>Last changed: ' + String(doc[MOD_ATT]).replace('T', ' ') + '</p></div>';
		
		return output;
	};

	AjaxSolr.theme.prototype.facet = function(value, weight, handler) {
		return $('<a href="#" class="tagcloud_item"/>').text(noUnderscore(value)).addClass(
				'tagcloud_size_' + weight).click(handler);
	};

	AjaxSolr.theme.prototype.facet_link = function(value, handler) {
		return $('<a href="#"/>').text(value).click(handler);
	};

	AjaxSolr.theme.prototype.no_items_found = function() {
		return 'no items found in current selection';
	};

})(jQuery);

