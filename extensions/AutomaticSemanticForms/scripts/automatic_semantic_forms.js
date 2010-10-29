/*  Copyright 2010, ontoprise GmbH
*  This file is part of the Automatic Semantic Forms Extension.
*
*   The Automatic Semantic Forms Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The Automatic Semantic Forms Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * @file
 * @ingroup AutomaticSemanticFormsScripts
 * @author: Ingo Steinbauer
 */

/*
 * Add tooltips
 */
jQuery(document).ready( function ($) {
	
	//do form input label ttoltips
	$('span.asf_property_link').each( function () {
		var ttContent = $('span.asf_tooltip_content', this).html();
		
		if(ttContent.length > 0){
			
			//add tooltips if form input labels are links
			$('a[title]', this).qtip({ 
				content: ttContent,
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
			$('a[title]', this).removeAttr('title');
			
			//add tooltips if form input labels are no links
			$('span.asf_input_label', this).qtip({ 
				content: ttContent,
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
		}
		
	});
	
	//do additional help messages tooltips
	$('span.asf_additional_help').each( function () {
		
		var ttContent = $('span.asf_additional_help_content', this).html();
		
		$('img', this).qtip({ 
			content: ttContent,
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

	//do tooltips for category links
	$('span.asf_category_tooltip').each( function () {
		
		var ttContent = $('span.asf_category_tooltip_content', this).html();
		
		$('a', this).qtip({ 
			content: ttContent,
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
		$('a[title]', this).removeAttr('title');
	});
});

/*
 * hide a form section 
 */
function asf_hide_category_section(id){
	jQuery('#' + id + '_visible').hide();
	jQuery('#' + id + '_hidden').show();
}

/*
 * Display form section
 */
function asf_show_category_section(id){
	jQuery('#' + id + '_visible').show();
	jQuery('#' + id + '_hidden').hide();
}


