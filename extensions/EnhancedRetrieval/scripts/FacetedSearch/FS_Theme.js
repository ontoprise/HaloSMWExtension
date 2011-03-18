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
	var RELATION_REGEX = /^smwh_(.*)_(.*)$/;
	var ATTRIBUTE_REGEX = /smwh_(.*)_xsdvalue_(.*)/;

	var IMAGE_PATH = '/extensions/EnhancedRetrieval/skin/images/';
	var NS_ICON = {
		// TODO add missing mappings
		0 : ['Instance', wgScriptPath + IMAGE_PATH + 'smw_plus_instances_icon_16x16.png'],
		6 : ['Image', wgScriptPath + IMAGE_PATH + 'smw_plus_image_icon_16x16.png'],
		10 : ['Template', wgScriptPath + IMAGE_PATH + 'smw_plus_template_icon_16x16.png'],
		14: ['Category', wgScriptPath + IMAGE_PATH + 'smw_plus_category_icon_16x16.png'],
		102 : ['Property', wgScriptPath + IMAGE_PATH + 'smw_plus_property_icon_16x16.png'],
		700 : ['Comment', wgScriptPath + IMAGE_PATH + 'smw_plus_comment_icon_16x16.png']
	};

	var NS_CAT_ID = 14;
	var NS_PROP_ID = 102;
	
	/**
	 * Removes all underscores.
	 */
	function noUnderscore(string) {
		return string.replace(/_/g, ' ');
	}

	/**
	 * Gets icon-URL for a specific namespace ID.
	 */
	function getIconForNSID(id) {
		var iconData = NS_ICON[id];
		if (iconData === undefined) {
			return '<!-- unknown namespace ID: ' + id + ' -->'; 
		}
		return '<img src="' + iconData[1] + '" title="' + iconData[0] + '"/>';
	}
	
	/**
	 * Constructs a relative URL from namespace and page name.
	 */
	function getLink(namespaceId, page) {
		var ns = wgFormattedNamespaces[String(namespaceId)];
		if (ns.length > 0) {
			ns = noUnderscore(ns) + ':';
		}
		return wgArticlePath.replace('$1', ns + page);
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
		plainName = property.match(RELATION_REGEX);
		if (plainName) {
			return noUnderscore(plainName[1]);
		}
		// Neither attribute nor relation => return the given name
		return noUnderscore(property);
	}
	
	/**
	 * Checks if the given name is a name for an attribute or relation.
	 * 
	 * @param {string} name
	 * 		The name to examine
	 * @return {bool}
	 * 		true, if name is a property name
	 */
	function isProperty(name) {
		return name.match(ATTRIBUTE_REGEX)|| name.match(RELATION_REGEX);
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
		var output = '<div class="xfsResult"><a class="xfsResultTitle" href="' + getLink(doc.smwh_namespace_id, doc.smwh_title) + '">';
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
				vals.push('<a href="' + getLink(NS_CAT_ID, cats[i]) + '">' + noUnderscore(cats[i]) + '</a>');
			}
			output += vals.join(CAT_SEP);
			if (count < cats.length) {
				vals = [];
				for (var i=count; i<cats.length; i++) {
					vals.push('<a href="' + getLink(NS_CAT_ID, cats[i]) + '">' + noUnderscore(cats[i]) + '</a>');
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
					// TODO check link namespace, has to be extracetd from value, e.g. Namespace:Page_Title
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
		
		if (doc[MOD_ATT]) {
			output += '<div class="xfsResultModified"><p>Last changed: ' + String(doc[MOD_ATT]).replace('T', ' ').substring(0, 16) + '</p></div>';
		}
		
		return output;
	};


	/**
	 * This function generates the HTML for a facet which may be a category or
	 * a property. Properties have details e.g. clusters of values or lists of
	 * values.
	 * 
	 * @param {string} facet
	 * 		Name of the facet
	 * @param {int} count
	 * 		Number of documents that match the facet
	 * @param {Function} handler
	 * 		Click handler for the facet.
	 * @param {Function} showPropertyDetailsHandler
	 * 		This function is called when the details of a property are to be
	 * 		shown.
	 * 		
	 */
	AjaxSolr.theme.prototype.facet = function(facet, count, handler, showPropertyDetailsHandler, isRemove) {
		var html;
		if (isRemove) {
			html = $('<span>')
				.append(extractPlainName(facet))
				.append($('<img src="' + wgScriptPath + '/extensions/SMWHalo/skins/QueryInterface/images/delete.png" title="Remove filter"/>').click(handler));
		} else {
			html = $('<span>')
				.append($('<a href="#">' + extractPlainName(facet) + '</a>').click(handler))
				.append(' ')
				.append('<span class="xfsMinor">(' + count + ')</span>');
		}
		var path = wgScriptPath + IMAGE_PATH;
		if (isProperty(facet)) {
			var divID = 'property_' + facet + '_values';
			var img1ID = 'show_details' + divID;
			var img2ID = 'hide_details' + divID;
			var toggleFunc = function () {
				if ($('#' + divID).is(':visible')) {
					$('#' + divID).hide();
				} else {
					$('#' + divID).show();
					showPropertyDetailsHandler(facet);
				} 
				$('#' + img1ID).toggle();
				$('#' + img2ID).toggle();
			};
			var img1 = 
				$('<img src="'+ path + 'right.png" title="Show details" id="'+img1ID+'"/>')
				.click(toggleFunc);
			var img2 = 
				$('<img src="'+ path + 'down.png" title="Hide details" style="display:none" id="'+img2ID+'"/>')
				.click(toggleFunc);
			html = img1.add(img2).add(html);
			html = html.add($('<div id="' + divID + '" style="display:none"></div>'));
		} else {
			var img = $('<img src="' + path + 'item.png">');
			html = img.add(html);
		}
		return html;
	};

	AjaxSolr.theme.prototype.facet_link = function(value, handler) {
		return $('<a href="#"/>'+ value + '</a>').click(handler);
	};

	AjaxSolr.theme.prototype.no_items_found = function() {
		return 'no items found in current selection';
	};

	AjaxSolr.theme.prototype.no_facet_filter_set = function() {
		return $('<div class="xfsMinor">').text('(no facet filter set)');
	};
	
	AjaxSolr.theme.prototype.remove_all_filters = function(handler) {
		return $('<a href="#"/>').text('remove all').click(handler);
	};
	
	AjaxSolr.theme.prototype.cluster_remove_range_filter = function(handler) {
		return $('<a href="#" class="xfsClusterEntry"/>').text('remove range').click(handler);
	};
	
	AjaxSolr.theme.prototype.filter_debug = function(filters) {
		var list = $('<ul id="xfsFilterDebug>');
		$.each(filters, function(index, value) {
			$(list).append($('<li>').text(value));
		});
		return list;
	};

	/**
	 * Creates the HTML for a cluster of values of an attribute. A cluster is 
	 * a range of values and the number of elements within this range e.g.
	 * 10 - 30 (5).
	 * 
	 * @param {double} from 
	 * 		Start value of the range
	 * @param {double} to
	 * 		End value of the range
	 * @param {int} count
	 * 		Number of elements in this range
	 * @param {function} handler
	 * 		This function is called when the cluster is clicked.
	 */
	AjaxSolr.theme.prototype.cluster = function(from, to, count, handler) {
		return $('<a href="#" class="xfsClusterEntry">'
				+ from + ' - ' + to + ' (' + count + ')'
				+ '</a>')
			.click(handler)
			.add('<br />');
	};
	
})(jQuery);

