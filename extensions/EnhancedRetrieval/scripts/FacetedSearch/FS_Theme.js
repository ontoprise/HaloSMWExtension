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
	
	var REMOVE_ICON = wgScriptPath + IMAGE_PATH + 'delete.png';

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
		if (!ns) {
			ns = "";
		}
		if (ns.length > 0) {
			ns = noUnderscore(ns) + ':';
		}
		return wgArticlePath.replace('$1', ns + page);
	}
	
	/**
	 * Attributes and relations that are delivered as facets always have a prefix
	 * and a suffix that indicates the type. This function retrieves the original
	 * name of an attribute or relation.
	 * @param {String} property
	 * 		The decorated name of an attribute or property.
	 * @return {String}
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
	 * A text that is displayed in the UI may contain HTML or script code which
	 * may enable cross-site-scripting. This function escapes special HTML
	 * characters. 
	 * Find further information at:
	 * https://www.owasp.org/index.php/XSS_%28Cross_Site_Scripting%29_Prevention_Cheat_Sheet
	 * 
	 * @param {string} text
	 * 		The string to be escaped.
	 * @return {string}
	 * 		The escaped string
	 */
	function escapeHTML(text) {
		var escText = text.replace(/&/g, '&amp;')
						  .replace(/</g, '&lt;')
		                  .replace(/>/g, '&gt;')
		                  .replace(/"/g, '&quot;')
		                  .replace(/'/g, '&#x27;')
		                  .replace(/\//g, '&#x2F;');
		return escText;
	}
	
	/**
	 * Generates an HTML-ID for a facet. The HTML-IDs are used as IDs for HTML 
	 * elements and in jQuery selectors. Some characters like / or % are not valid
	 * for use in jQuery selectors. So all characters in the facet are converted
	 * to a hexadecimal string.
	 * 
	 * @param {String} facet
	 * 		Name of the facet
	 * @return {String}
	 * 		ID for the given facet.
	 */
	function facet2HTMLID(facet) {
		var f = "";
		for (var i = 0, l = facet.length; i < l; ++i) {
			f += facet.charCodeAt(i).toString(16);
		}
		return f;
	}
	
	/**
	 * Some strings are too long for displaying them in the UI. This function
	 * shortens them and appends an ellipsis (...) .
	 * @param {String} longName
	 * 		The name that is shortened
	 * @return {String/bool} short name
	 * 		The short name. If the longName is already short enough the boolean
	 * 		"false" is returned.
	 */
	function makeShortName(longName, width, className) {
		
// Fast version that does not consider the actual rendered width of the text
//		var maxLength = 25;
//		if (longName.length > maxLength) {
//			return longName.substr(0, maxLength-3) + '&hellip;';
//		}
//		return false;
		var className = className ? ' class="' + classname + '" ' : "";
		var tempItem = '<span id="textWitdhTempItem" ' + className + 'style="display:none;">'+ longName +'</span>';
	    $(tempItem).appendTo('body');
		tempItem = $('#textWitdhTempItem');
	    var itemWidth = tempItem.width();
	    var shortName = longName;
	
	    if (itemWidth < width){
		    tempItem.remove();
			return false;
		}
		
		var minLen = 0;
		var maxLen = shortName.length;
		while (maxLen - minLen > 1 && Math.abs(itemWidth - width) > 5) {
			var currLen = (maxLen - minLen) / 2 + minLen;
            shortName = longName.substr(0, currLen);
            tempItem[0].innerHTML = shortName + '&hellip;';
            itemWidth = tempItem.width();
//            itemWidth = tempItem.html(shortName + '&hellip;').width();
			if (itemWidth > width) {
				// Reduce the upper bound
				maxLen = currLen;
			} else {
				// Increase the lower bound
				minLen = currLen;
			}
		}
	    tempItem.remove();
	    return shortName + '&hellip;';
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
	 * Checks if the given name is a name for a relation.
	 * 
	 * @param {string} name
	 * 		The name to examine
	 * @return {bool}
	 * 		true, if name is a relation name
	 */
	function isRelation(name) {
		return name.match(RELATION_REGEX) && !name.match(ATTRIBUTE_REGEX);
	}
	
	/**
	 * Generates an HTML ID for a property value facet with the name {facet}.
	 * 
	 * @param {String} facet
	 * 		Name of the facet
	 * @return {String} 
	 * 		HTML ID for the given facet.
	 */
	AjaxSolr.theme.prototype.getPropertyValueHTMLID = function (facet) {
		return 'property_' + facet2HTMLID(facet) + "_value";	
	}
	
	/**
	 * Theme for article titles and their semantic data with highlighting.
	 * 
	 * @param doc
	 * 		The article given as SOLR document
	 * @param data
	 * 		HTML representation of the semantic data
	 * @param highlight
	 * 		HTML representation of the semantic data
	 */
	AjaxSolr.theme.prototype.article = function (doc, data, highlight, showDataHandler) {
		var output = '<div class="xfsResult"><a class="xfsResultTitle" href="' + getLink(doc.smwh_namespace_id, doc.smwh_title) + '">';
		output += noUnderscore(doc.smwh_title) + '</a>';
		output += getIconForNSID(doc.smwh_namespace_id);
		// output += '<p id="links_' + doc.id + '" class="links"></p>';
		output += '<div class="xfsHighlightSearch">' + highlight + '</div>';
		output += '<div>' + data + '</div>';
		// Add the modification date
		if (doc[MOD_ATT]) {
			var lang = FacetedSearch.singleton.Language;
			output += 
				'<div class="xfsResultModified">' + 
					'<p>'+ lang.getMessage('lastChange') +': ' + 
						String(doc[MOD_ATT])
							.replace('T', ' ')
							.substring(0, 16) + 
					'</p>' + 
				'</div>';
		}
		output += '</div>';
		
		output = $(output);
		output.find('.xfsShow').data('documentData', doc).click(showDataHandler);
		
		return output;
	};
	
	/**
	 * Theme for rendering a highlighted text.
	 * 
	 * @param highlight
	 * 		The highlighted text
	 */
	AjaxSolr.theme.prototype.highlight = function (highlight) {
		return '&hellip;' + highlight + '&hellip;';
	};
	
	/**
	 * Theme for the semantic data of an article. Only categories are displayed
	 * to the full extent. If there are properties for the article, a link to
	 * show them is displayed.
	 * 
	 * @param doc
	 * 		The article given as SOLR document
	 */
	AjaxSolr.theme.prototype.data = function (doc) {
		var lang = FacetedSearch.singleton.Language;

		var output = '';
		var attr  = doc[FS_ATTRIBUTES] || [];
		var props = doc[FS_PROPERTIES] || [];
		var cats  = doc[FS_CATEGORIES];
		
		if (typeof cats !== 'undefined') {
			// Show CAT_MAX categories
			output += 
				'<div class="xfsResultCategory">' +
				'<p>'+ lang.getMessage('inCategory') +': ';
			var count = Math.min(cats.length, CAT_MAX);
			var vals = [];
			for ( var i = 0; i < count; i++) {
				vals.push('<a href="' + getLink(NS_CAT_ID, cats[i]) + '">' 
				          + noUnderscore(cats[i]) 
						  + '</a>');
			}
			output += vals.join(CAT_SEP);
			if (count < cats.length) {
				vals = [];
				for (var i=count; i<cats.length; i++) {
					vals.push('<a href="' + getLink(NS_CAT_ID, cats[i]) + '">' 
					          + noUnderscore(cats[i]) 
							  + '</a>');
				}
				output += CAT_SEP;
				output += '<span class="xfsToggle" style="display: none">' + vals.join(CAT_SEP) + '</span>';
				output += ' (<a class="xfsMore">'+lang.getMessage('more')+'</a>)';
			}
			output += '</p></div>';
		}
		
		if (props.length + attr.length > 0) {
			// Properties or attributes are present 
			// => add a table header
			output += 
				'<div>' +
					'(<a class="xfsShow">' +
						lang.getMessage('show') +
					'</a>)' +
					'<div class="xfsResultTable"></div>' +
				'</div>';
		}
		return output;
	};

	/**
	 * Theme for the semantic properties of an article.
	 * 
	 * @param doc
	 * 		The article given as SOLR document
	 */
	AjaxSolr.theme.prototype.articleProperties = function(doc) {
		
		var lang = FacetedSearch.singleton.Language;
		
 		var output = '<table>';
		var row = 0;
		
		// Show all relations and attributes in a table
		for (var property in doc) {
			// Get the property name without prefix, suffix and type
			var plainName = extractPlainName(property);
			output += '<tr class="s' + (row % 2) + '">';
			row += 1;
			output += '<td>' + plainName + '</td>';
			if (isRelation(property)) {
				// Relation values are rendered as link
				var vals = [];
				$.each(doc[property], function() {
					// TODO check link namespace, has to be extracetd from value, e.g. Namespace:Page_Title
					vals.push('<a href="' + this + '">' + noUnderscore(this) + '</a>');
				});
				output += '<td>' + vals.join(', ') + '</td>';
			} else {
				// Attribute values are rendered as normal text
				output += '<td>'+doc[property].join(', ')+'</td>';
			}
			output += '</tr>';
		}

		output += '</table>';
		return output;						
	};

	/**
	 * This function generates the HTML for a namespace facet. The namespace is
	 * given as the namespace number. The namespace name is retrieved and returned
	 * as HTML.
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
	AjaxSolr.theme.prototype.namespaceFacet = function(facet, count, handler, showPropertyDetailsHandler, isRemove){
		var lang = FacetedSearch.singleton.Language;
		var name = facet === 'all' 
					? lang.getMessage('allNamespaces')
					: wgFormattedNamespaces[facet];
		if (name === '') {
			// Main namespace
			name = lang.getMessage('mainNamespace');
		}
		
		if (typeof name === 'undefined') {
			// The namespace may be undefined if the extension that defines it
			// was disabled after the creation of the index.
			return '';
		}
		var tooltip = 'title="' + lang.getMessage('namespaceTooltip', count) + '" ';
		name = name.replace(/ /g, '&nbsp;')
		var emptyNamespace = count === 0 ? " xfsEmptyNamespace" : "";
		html = $('<span namespace="' + facet + '" class="xfsNamespace' + emptyNamespace + '"/>')
				.append('&nbsp;')
				.append($('<span ' + tooltip + '>' + name + '</span>'))
				.append(' ');
		html.find("span").click(handler)
		return html;				
	}
	
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
		var lang = FacetedSearch.singleton.Language;
		var plainName = extractPlainName(facet);
		var maxWidth = $('.facets').width() * 0.7;
		var shortName = makeShortName(plainName, maxWidth);
		var tooltip = shortName === false ? "" : ' title="' + plainName + '" ';
		var name = shortName === false ? plainName : shortName;
		
		if (isRemove) {
			html =	
				'<span' + tooltip + '>' +
					name +
					'<img class="xfsRemoveFacet" src="' + REMOVE_ICON +'" ' +
						'title="'+ lang.getMessage('removeFilter') +'"/>' +
				'</span>';
		} else {
			html =
				'<span class="addFacet">' +
					'<a href="#"' + tooltip + '>' + name + '</a> ' +
					'<span class="xfsMinor">(' + count + ')</span>' +
				'</span>';
		}
		var path = wgScriptPath + IMAGE_PATH;
		if (isProperty(facet)) {
			var facetsExpanded = FacetedSearch.singleton.FacetedSearchInstance.isExpandedFacet(facet);
			var img1Visible = facetsExpanded ? ' style="display:none" ' : '';
			var img2Visible = facetsExpanded ? '' : ' style="display:none" ';
			var divID = AjaxSolr.theme.prototype.getPropertyValueHTMLID(facet);
			var img1ID = 'show_details' + divID;
			var img2ID = 'hide_details' + divID;
			
			var toggleFunc = function () {
				if ($('#' + divID).is(':visible')) {
					$('#' + divID).hide();
					FacetedSearch.singleton.FacetedSearchInstance
						.removeExpandedFacet(facet);
				} else {
					$('#' + divID).show();
					FacetedSearch.singleton.FacetedSearchInstance
						.addExpandedFacet(facet);
					showPropertyDetailsHandler(facet);
				} 
				$('#' + img1ID).toggle();
				$('#' + img2ID).toggle();
			};
			
			var img1 = 
				'<img src="'+ path + 'right.png" ' +
					'title="'+ lang.getMessage('showDetails') +
					'" id="'+img1ID+'"'+img1Visible+' class="detailsImage"/>';
			var img2 = 
				'<img src="'+ path + 'down.png" ' +
					'title="'+ lang.getMessage('hideDetails') +
					'" id="'+img2ID+'"'+img2Visible+'" class="detailsImage"/>';
			html = img1 + img2 + html;
			html += '<div id="' + divID +'"'+ img2Visible + '></div>';
		} else {
			var img = '<img src="' + path + 'item.png">';
			html = img + html;
		}
		html = $('<div>' + html + '</div>');
		// Attach the event handlers
		html.find('.addFacet').click(handler);
		html.find('.xfsRemoveFacet').click(handler);
		if (isProperty(facet)) {
			html.find('.detailsImage').click(toggleFunc);
		}
		return html;
	};

	AjaxSolr.theme.prototype.propertyValueFacet = function(facet, count, handler, showPropertyDetailsHandler, isRemove){
		var html = AjaxSolr.theme('facet', facet, count, handler, showPropertyDetailsHandler, isRemove);
		html = $('<div class="xfsClusterEntry" />').append(html);
		return html;	
	};
	
	AjaxSolr.theme.prototype.facet_link = function(value, handler) {
		return $('<a href="#"/>'+ value + '</a>').click(handler);
	};
	
	AjaxSolr.theme.prototype.moreLessLink = function(moreHandler, lessHandler) {
		var lang = FacetedSearch.singleton.Language;
		var $ = jQuery;
		
		var html = 
			'<div>' +
				'<a class="xfsFMore">' +
					lang.getMessage('more') +
				'</a>' +
				'<span style="display: none">' +
				' &#124; ' + 
				'</span>' +
				'<a class="xfsFLess" style="display: none">' +
					lang.getMessage('less') +
				'</a>' +
			'</div>';
		html = $(html);
		html.find('.xfsFMore').click(moreHandler);
		html.find('.xfsFLess').click(lessHandler);
		return html;
	};

	AjaxSolr.theme.prototype.no_items_found = function() {
		return 'no items found in current selection';
	};

	AjaxSolr.theme.prototype.no_facet_filter_set = function() {
		var lang = FacetedSearch.singleton.Language;
		return $('<div class="xfsMinor">').text(lang.getMessage('noFacetFilter'));
	};
	
	AjaxSolr.theme.prototype.remove_all_filters = function(handler) {
		var lang = FacetedSearch.singleton.Language;
		return $('<a href="#"/>')
				.text(lang.getMessage('removeAllFilters'))
				.click(handler);
	};
	
	AjaxSolr.theme.prototype.emptyQuery = function(handler) {
		var lang = FacetedSearch.singleton.Language;
		return lang.getMessage('addFacetOrQuery');
	};
		
	AjaxSolr.theme.prototype.createArticle = function(articleName, link) {
		var lang = FacetedSearch.singleton.Language;
		link = escapeHTML(link);
		articleName = escapeHTML(articleName);
		var html = lang.getMessage('nonexArticle', '<em>'+articleName+'</em>') + 
					' <a href="' + link + '" class="xfsRedLink">' + 
						articleName + 
					'</a>';
		return html;
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
	 * @param {bool} isClusterTitle
	 * 		If true, this range is displayed as the cluster title. It shows the 
	 * 		absolute borders of all clusters it contains.
	 * @param {bool} isRangeRestricted
	 * 		If true, there is a range restriction on the facet. The icon for
	 * 		deleting the range is displayed.
	 * @param {bool} isLastRange
	 * 		If true, this is the last range. It will not be displayed as link.
	 * 
	 */
	AjaxSolr.theme.prototype.cluster = function(from, to, count, handler, 
											isClusterTitle, isRangeRestricted,
											isLastRange) {
		var html;
		
		var range = from === to 
						? from
						: from + ' - ' + to;
						
		if (isClusterTitle) {
			var lang = FacetedSearch.singleton.Language;
			var removeIcon = isRangeRestricted 
				? '<img class="xfsRemoveRange" src="' + REMOVE_ICON +'" ' +
					    'title="'+ lang.getMessage('removeRestriction') +'"/>'
				: "";
			html = 
				$('<div>' +
						'<span class="xfsClusterTitle">' +
							range + ' (' + count + ')' +
							removeIcon +
						'</span>' +
					'</div>');
			if (removeIcon) {
				html.find('img').click(handler);
			}
		} else {
			 if (isLastRange) {
			 	html =
					'<div>' +
						'<span class="xfsClusterEntry">' +
							range + ' (' + count + ')' +
						'</span>' +
					'</div>';
			 } else {
				html = 			
					$('<div>' +
						'<a href="#" class="xfsClusterEntry">' +
							range + ' (' + count + ')' +
				  		'</a>' +
				  	'</div>')
					.click(handler);
			 }
		}
		return html;
	};
	
})(jQuery);

