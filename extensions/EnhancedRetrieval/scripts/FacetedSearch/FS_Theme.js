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
	var FS_PROPERTIES = 'smwh_properties'; // relations
	var MOD_ATT = 'smwh_Modification_date_xsdvalue_dt';
	var CAT_MAX = 4;
	var CAT_SEP = ' | ';
	var PROPERTY_REGEX = /^smwh_(.*)_(.*)$/;
	var ATTRIBUTE_REGEX = /smwh_(.*)_xsdvalue_(.*)/;

	var SEARCH_PATH = '/extensions/EnhancedRetrieval/skin/images/';
	var NS_ICON = {
		// TODO add missing mappings
		0 : ['Instance', wgScriptPath + SEARCH_PATH + 'smw_plus_instances_icon_16x16.png'],
		6 : ['Image', wgScriptPath + SEARCH_PATH + 'smw_plus_image_icon_16x16.png'],
		102 : ['Property', wgScriptPath + SEARCH_PATH + 'smw_plus_property_icon_16x16.png'],
		700 : ['Comment', wgScriptPath + SEARCH_PATH + 'smw_plus_comment_icon_16x16.png']
	};
	
	function noUnderscore(string) {
		return string.replace(/_/g, ' ');
	}
	
	function getIconForNSID(id) {
		var iconData = NS_ICON[id];
		return '<img src="' + iconData[1] + '" title="' + iconData[0] + '"/>';
	}
	
	/**
	 * Attributes and relations that are delivered as facets always have a prefix
	 * and a suffix that indicates the type. This function retrieves the original
	 * name of an attribute or relation.
	 * @param string property
	 * 		The decorated name of an attribute or property.
	 * @return string
	 * 		The plain name of the property.
	 */
	function extractPlainName(property) {
		// Try attribute
		var plainName = property.match(ATTRIBUTE_REGEX);
		if (plainName) {
			return noUnderscore(plainName[1]);
		}
		// Try relation
		plainName = property.match(PROPERTY_REGEX);
		if (plainName) {
			return noUnderscore(plainName[1]);
		}
		// Neither attribute nor relation => return the given name
		return noUnderscore(property);
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
		
		if (typeof cats !== 'undefined') {
			// Show CAT_MAX categories
			output += '<div class="xfsResultCategory"><p>is in category: ';
			var count = Math.min(cats.length, CAT_MAX);
			var vals = [];
			for ( var i = 0; i < count; i++) {
				// TODO check link
				vals.push('<a href="' + cats[i] + '">' + noUnderscore(cats[i]) + '</a>');
			}
			output += vals.join(CAT_SEP);
			if (count < cats.length) {
				vals = [];
				for (var i=count; i<cats.length; i++) {
					// TODO check link
					vals.push('<a href="' + cats[i] + '">' + noUnderscore(cats[i]) + '</a>');
				}
				output += CAT_SEP;
				output += '<span class="xfsToggle" style="display: none">' + vals.join(CAT_SEP) + '</span>';
				output += ' (<a class="xfsMore">more</a>)';
			}
			output += '</p></div>';
		}
		
		if (props.length + attr.length > 0) {
			// Properties or attributes are present 
			// => add a table header
			output += '<div class="xfsResultTable">has properties: (<a class="xfsShow">show</a>)<table>';
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
				var plainName = extractPlainName(property);
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
		
		if (attr.length > 0) {
			// The property array may contain duplicates 
			// => create an object without duplicates
			var attrMap = {};
			for (var i = 0; i < attr.length; i++) {
				attrMap[attr[i]] = true;
			}
			
			for (var attribute in attrMap) {
				// Get the property name without prefix, suffix and type
				var plainName = extractPlainName(attribute);
				output += '<tr class="s' + (row % 2) + '">';
				row += 1;
				output += '<td>'+plainName+'</td>';
				output += '<td>'+doc[attribute].join(', ')+'</td>';
				output += '</tr>';
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
		output += '<div class="xfsResultModified"><p>Last changed: ' + String(doc[MOD_ATT]).replace('T', ' ').substring(0, 16) + '</p></div>';
		
		return output;
	};

	AjaxSolr.theme.prototype.facet = function(value, weight, handler) {
		return $('<a href="#" class="tagcloud_item"/>')
			.text(extractPlainName(value))
			.addClass('tagcloud_size_' + weight)
			.click(handler)
			.add($('<span>')
			.text(' (' + weight + ')'));
	};

	AjaxSolr.theme.prototype.facet_link = function(value, handler) {
		return $('<a href="#"/>').text(value).click(handler);
	};

	AjaxSolr.theme.prototype.no_items_found = function() {
		return 'no items found in current selection';
	};

})(jQuery);

