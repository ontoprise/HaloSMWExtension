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
 * @ingroup LinkedDataScripts
 * @author: Thomas Schweitzer
 */

/**
 * When the document is completely loaded the tool-tips for all spans with the
 * class "lodMetadata" are installed. These spans contain the actual content of 
 * the tool-tips in a span with the class "lodMetadataContent". This inner span 
 * is hidden.
 * 
 */
jQuery(document).ready( function ($) {  
	// install the tool-tips
	$("span.lodMetadata").each(function () {
		// get the html of the content span
		var content = $(this).find('.lodMetadataContent').html(); 
		// install the tool-tip on the current DOM element
		if (content.length === 0) {
			return;
		}
		$(this).qtip({
				content: content,
				show: {
					when:   { event:  'mouseover' }
				},
				hide: {
					when:   { event: 'mouseout' },
					fixed: true
				},
				position: {
          my: 'bottom left',
          at: 'top left',
          target: 'mouse'
        },
				style: { 
    				classes: 'ui-tooltip-blue ui-tooltip-shadow'
				}
		});
	});
	
	// propagate background color (which represents the source) to enclosing <td> element
	$("span.lodMetadata").each(function () {
		if ($(this).hasClass("lod_background0")) $(this).parent().addClass("lod_background0");
		if ($(this).hasClass("lod_background1")) $(this).parent().addClass("lod_background1");
		if ($(this).hasClass("lod_background2")) $(this).parent().addClass("lod_background2");
		if ($(this).hasClass("lod_background3")) $(this).parent().addClass("lod_background3");
		if ($(this).hasClass("lod_background4")) $(this).parent().addClass("lod_background4");
		if ($(this).hasClass("lod_background5")) $(this).parent().addClass("lod_background5");
		if ($(this).hasClass("lod_background6")) $(this).parent().addClass("lod_background6");
		if ($(this).hasClass("lod_background7")) $(this).parent().addClass("lod_background7");
		if ($(this).hasClass("lod_background8")) $(this).parent().addClass("lod_background8");
		if ($(this).hasClass("lod_background9")) $(this).parent().addClass("lod_background9");
	});
});
