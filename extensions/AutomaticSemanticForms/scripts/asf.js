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

function initializeNiceASFTooltips(){
	var $ = jQuery;
	
	//do form input label ttoltips
	$('.asf_use_qtip').each( function () {
	
		var ttContent = $('.asf_qtip_content', this).html();
		
		if(ttContent.length > 0){
			
			//add tooltips if form input labels are links
			$('*:first-child', this).qtip({ 			
				
				content: ttContent,
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
        style : {
            classes: 'ui-tooltip-blue ui-tooltip-shadow'
				}
			});
			
			$('a[title]', this).removeAttr('title');
			
			
		}
	});
}

/*
 * hide a form section 
 */
function asf_hide_category_section(id){
	jQuery('#' + id + '  .asf_visible_legend').hide();
	jQuery('#' + id + ' .asf_collapsed_legend').show();
	jQuery('#' + id + ' .asf_fieldset_content').hide();
	//jQuery('#' + id + '_hidden legend').focus();
}

/*
 * Display form section
 */
function asf_show_category_section(id){
	jQuery('#' + id + ' .asf_visible_legend').show();
	jQuery('#' + id + ' .asf_collapsed_legend').hide();
	jQuery('#' + id + ' .asf_fieldset_content').show();
	//jQuery('#' + id + '_visible legend').focus();
}

function asf_hit_category_section(id){
	if(jQuery('#' + id + ' .asf_visible_legend').css("display") == "none"){
		asf_show_category_section(id);
	} else {
		asf_hide_category_section(id);
	}
}

function asf_initializeCollapsableSectionsTabIndexes(){
	jQuery(".asf_legend").each( function(){
		var tabindex = jQuery("input[tabindex]", jQuery(this).parent()).attr('tabindex');
		if(tabindex != undefined){
			jQuery(this).attr("tabindex", tabindex*1-1+".5");
		}
	});
}

function asf_hideFreeText(){
	if(jQuery('.asf-hide-freetext').get().length > 0){
		window.wgHideSemanticToolbar = true;
		jQuery('#free_text').css('display', 'none');
	}
}


function asf_makeReadOnly(){
jQuery('.asf-write-protected').parent().each( function (){
		jQuery('*', this).attr('readonly', 'true');
		jQuery('*', this).attr('disabled', 'true');
	});
}


jQuery(document).ready( function($) {
	initializeNiceASFTooltips();
	asf_hideFreeText();	
	asf_makeReadOnly();
	
	window.asf_hide_category_section = asf_hide_category_section;
	window.asf_show_category_section = asf_show_category_section;
	window.asf_hit_category_section = asf_hit_category_section;
});

window.onload = asf_initializeCollapsableSectionsTabIndexes;



