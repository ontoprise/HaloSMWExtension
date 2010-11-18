/*  Copyright 2010, ontoprise GmbH
*  This file is part of the LinkedData-Extension.
*
*   The LinkedData-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The LinkedData-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
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
	// hide all content spans
	$("span.lodMetadataContent").hide();
	
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
					effect: { length: 500 },
					when:   { event:  'mouseover' }
				},
				hide: {
					effect: { length: 500 },
					when:   { event: 'mouseout' },
					fixed: true
				},
				position: {
					corner: {
						target: 'topLeft',
						tooltip: 'bottomLeft'
					}
				},
				style: { 
    				tip: 'bottomLeft',
					width: { max: 500 }
				}
		});
	});
});
