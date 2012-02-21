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
            at: 'top left'
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

window.ASFFormSyncer = {

	init : function(){
		
		jQuery('#asf_category_annotations').click(this.updateForm);
		jQuery('#asf_category_annotations').css('cursor', 'pointer');
		
		this.wtp = new WikiTextParser();
		window.asfIsShown = true;
		this.currentCategoryAnnotations = false;
		this.sync();
	},
	
	sync : function(){
		this.wtp.initialize();
		
		ASFFormUpdater.handleCategoryAnnotationUpdates(
				this.wtp.getCategories());
		
		var relations = this.wtp.getRelations();
		for (var i = 0; i < relations.length; ++i) {
			//alert('name' + relations[i].getName());
			//alert('name' + relations[i].getValue());
		}	
	}
};

window.ASFFormUpdater = {
	
	handleCategoryAnnotationUpdates : function(newCategoryAnnotations){
		var updateNecessary = false;
		var initPhase = false;
		if(!this.currentCategoryAnnotations){
			//we are in initialization phase
			updateNecessary = true;	
			initPhase = true;
		} else if(this.currentCategoryAnnotations.length != newCategoryAnnotations.length){
			updateNecessary = true;
		}
		
		this.currentCategoryString = "";
		for(var i=0; i<newCategoryAnnotations.length; i++){
			this.currentCategoryString += '<span>,</span> ' + newCategoryAnnotations[i].getName();
			if(!updateNecessary){
				var found = false;
				for(var k=0; k<this.currentCategoryAnnotations.length; k++){
					if(newCategoryAnnotations[i].getName()
							== this.currentCategoryAnnotations[k].getName()){
						found = true;
						break;
					}
				}
				if(!found){
					updateNecessary = true;
				}
			}
		}
		
		//remove first comma
		this.currentCategoryString = this.currentCategoryString.substring(
				'<span>,</span> '.length);
		
		this.currentCategoryAnnotations = newCategoryAnnotations;
		
		jQuery('#asf_category_string').html(this.currentCategoryString);
		
		//do updates if necessary
		if(updateNecessary && !initPhase){
			var currentValue = this.currentCategoryString;
			window.setTimeout(function() {
				if(currentValue == jQuery('#asf_category_string').html()){
					ASFFormUpdater.updateForm();
				}
			}, 1200);
		}
	},
	
	updateForm : function(){
		
		ASFMultiInputFieldHandler.resetInputFieldNames();
		
		var currentContainer = '#asf_formfield_container';
		if(jQuery('#asf_formfield_container').html() == ''){
			currentContainer = '#asf_formfield_container2';
		}
		
		var inputFieldIds = '';
		jQuery(currentContainer  +' input').each(function(){
			if(jQuery(this).attr('value')){
				if(jQuery(this).attr('type') == 'checkbox'){
					if(!jQuery(this).attr('checked')){
						return;
					}
				}
				
				if(jQuery(this).attr('type') == 'radio'){
					if(!jQuery(this).attr('checked')){
						return;
					} else {
						jQuery(this).attr('originally-checked', 'true');
					}
				}
				
				inputFieldIds += '<<<' + jQuery(this).attr('name');
			}
		});
		
		jQuery(currentContainer  +' select').each(function(){
			if(jQuery(this).attr('value')){
				inputFieldIds += '<<<' + jQuery(this).attr('name');
			}
		});
		
		jQuery(currentContainer  +' textarea').each(function(){
			if(jQuery(this).attr('value')){
				inputFieldIds += '<<<' + jQuery(this).attr('name');
			}
		});
		
		var url = wgServer + wgScriptPath + "/index.php";
		jQuery.ajax({ url:  url, 
			data: {
				'action' : 'ajax',
				'rs' : 'asff_getNewForm',
				'rsargs[]' : [ASFFormUpdater.currentCategoryString, inputFieldIds]
			},
			success: ASFFormUpdater.updateFormCallBack			
		});
	},
	
	updateFormCallBack : function(data){
		data = data.substr(data.indexOf('--##starttf##--') + 15, data.indexOf('--##endtf##--') - data.indexOf('--##starttf##--') - 15); 
		data = jQuery.parseJSON(data);
		
		var currentContainer = '#asf_formfield_container';
		var newContainer = '#asf_formfield_container2';
		if(jQuery('#asf_formfield_container').html() == ''){
			currentContainer = '#asf_formfield_container2';
			newContainer = '#asf_formfield_container';
		}
		
		jQuery(newContainer).html(data.html);
		
		jQuery(currentContainer + ' input').each(function(){
			if(jQuery(this).attr('value')){
				if(jQuery(this).attr('type') == 'checkbox'){
					jQuery(newContainer + ' input[name="' +
						jQuery(this).attr('name') + '"]').attr('checked', jQuery(this).attr('checked'));
				} else if(jQuery(this).attr('type') == 'radio'){
					if(jQuery(this).attr('originally-checked')){
						var value = jQuery(this).attr('value');
						jQuery(newContainer + ' input[name="' +
								jQuery(this).attr('name') + '"]').each(function (){
							if(jQuery(this).attr('value') == value){
								jQuery(this).attr('checked', 'checked');
							}		
						});
					}
				} else {
					jQuery(newContainer + ' input[name="' +
						jQuery(this).attr('name') + '"]').attr('value', jQuery(this).attr('value'));
				}
			}
		});
		
		jQuery('#asf_formfield_container select').each(function(){
			if(jQuery(this).attr('value')){
				jQuery(newContainer + ' select[name="' +
						jQuery(this).attr('name') + '"]').attr('value', jQuery(this).attr('value'));
			}
		});
		
		jQuery('#asf_formfield_container textarea').each(function(){
			if(jQuery(this).attr('value')){
				jQuery(newContainer + ' textarea[name="' +
					jQuery(this).attr('name') + '"]').attr('value', jQuery(this).attr('value'));
			}
		});
		
		jQuery(currentContainer).html('');
		
		//run init methods
		initializeNiceASFTooltips();
		asf_makeReadOnly();		
	}
};

window.ASFMultiInputFieldHandler = {
	
	init : function(){
		jQuery('.asf-multi_values').each(function(){
			
			var partOfUnresolvedAnnotationsSection = false;
			if(jQuery(this).attr('class').indexOf('asf-partOfUnresolvedAnnotationsSection') > 0){
				partOfUnresolvedAnnotationsSection = true;
			}
			
			var allowsMultipleValues = false;
			if(jQuery(this).attr('class').indexOf('asf-allowsMultipleValues') > 0){
				allowsMultipleValues = true;
			}
			
			if(jQuery('.asf-multi_value', this).get().length > 1 ||
					partOfUnresolvedAnnotationsSection){
				jQuery('.asf-multi_value', this).each(function(){
					ASFMultiInputFieldHandler.addDeleteButton(this);
				});
			}
			
			if(allowsMultipleValues){
				jQuery('.asf-multi_value:last-child', this).each(function(){
					ASFMultiInputFieldHandler.addAddButton(this);
				});
			}
		});
	},
	
	addAddButton : function(node){
		var addButton = '<a class="asf-addbutton" style="cursor: pointer">&nbsp;+</a>';
		jQuery(node).append(addButton);
		jQuery('.asf-addbutton:last-child', jQuery(node)).click(ASFMultiInputFieldHandler.addInputField);
	},
	
	addDeleteButton : function(node){
		var deleteButton = '<a class="asf-deletebutton" style="cursor: pointer">&nbsp;-</a>';
		jQuery(node).append(deleteButton);
		jQuery('.asf-deletebutton:last-child', jQuery(node)).click(ASFMultiInputFieldHandler.deleteInputField);
	},
	
	deleteInputField : function(){
		var parent = jQuery(this).parent().parent();
		
		var partOfUnresolvedAnnotationsSection = false;
		if(jQuery(parent).attr('class').indexOf('asf-partOfUnresolvedAnnotationsSection') > 0){
			partOfUnresolvedAnnotationsSection = true;
		}
		
		var inputFieldCount = 
			jQuery('.asf-multi_value', parent).get().length;
		
		if(inputFieldCount == 1){
			//we are in the unresolved annotations section 
			//and the last input field, respectively the complete row
			//will be removed
		
			jQuery(parent).parent().parent().remove();
			
			if(jQuery('.asf-unresolved-section tr').get().length == 0){
				jQuery('.asf-unresolved-section').css('display', 'none');
			}
			
		} else {
			jQuery(this).parent().remove();
			
			//deal with delete buttons
			if(inputFieldCount == 2){
				//outside of the unresolved annotations section it is not possible to delete all elements
				if(!partOfUnresolvedAnnotationsSection ){
					jQuery('.asf-deletebutton', parent).remove();
				}
			}
		
			//deal with add buttons
			var allowsMultipleValues = false;
			if(jQuery(parent).attr('class').indexOf('asf-allowsMultipleValues') > 0){
				allowsMultipleValues = true;
			}
			
			if(allowsMultipleValues){
				jQuery('.asf-addbutton', jQuery(parent).parent()).remove();
				jQuery('.asf-multi_value:last-child', jQuery(parent).parent()).each(function(){
					ASFMultiInputFieldHandler.addAddButton(this);
				});
			}
		}
	},
	
	addInputField : function(){
		//copy node
		var parent = jQuery(this).parent();
		jQuery(parent).parent().append(jQuery(parent).clone());
		
		//deal with delete buttons
		jQuery('.asf-deletebutton', jQuery(parent).parent()).remove();
		jQuery('.asf-multi_value', jQuery(parent).parent()).each(function(){
			ASFMultiInputFieldHandler.addDeleteButton(this);
		});
		
		//deal with add buttons
		jQuery('.asf-addbutton', jQuery(parent).parent()).remove();
		jQuery('.asf-multi_value:last-child', jQuery(parent).parent()).each(function(){
			ASFMultiInputFieldHandler.addAddButton(this);
		});
		
		//set name
		ASFMultiInputFieldHandler.setInputFieldName(
				jQuery('.asf-multi_value:last-child', jQuery(parent).parent()),
				jQuery('.asf-multi_value', jQuery(parent).parent()).length);
		
		//reset input field value
		ASFMultiInputFieldHandler.resetInputFieldValue(
				jQuery('.asf-multi_value:last-child', jQuery(parent).parent()));
	},
	
	resetInputFieldValue : function(node){

		jQuery('input', node).each(function(){
			if(jQuery(this).attr('type') == 'checkbox'){
				jQuery(this).removeAttr('checked');
			} else if(jQuery(this).attr('type') == 'radio'){
				jQuery(this).removeAttr('checked');
			} else {
				jQuery(this).attr('value', '');
			}
		});
		
		jQuery('select option', node).each(function(){
			jQuery(this).removeAttr('selected');
		});
		
		jQuery('textarea', node).each(function(){
			jQuery(this).attr('value', '');
		});
	},
	
	resetInputFieldNames : function(){
		
		jQuery('.asf-multi_values').each(function(){
			var counter = 1;	
			jQuery('.asf-multi_value', this).each(function(){
				ASFMultiInputFieldHandler.setInputFieldName(this, counter);
				counter++;
			});
		});
	},
	
	setInputFieldName : function(node, counter){
		
		jQuery('*[name]', node).each(function(){
			var name = jQuery(this).attr('name');
			var propNameEnd = name.indexOf('---');
			if(propNameEnd == -1){
				propNameEnd = name.indexOf(']');
			}
			
			index = '';
			if(counter > 1){
				index = '---' + counter;
			} 
			
			name = name.substr(0,propNameEnd)
				+ index + name.substr(name.indexOf(']'));
			
			jQuery(this).attr('name', name);
		});
	}
	
};


jQuery(document).ready( function($) {
	initializeNiceASFTooltips();
	asf_hideFreeText();	
	asf_makeReadOnly();
	ASFFormSyncer.init();
	ASFMultiInputFieldHandler.init();
	
	window.asf_hide_category_section = asf_hide_category_section;
	window.asf_show_category_section = asf_show_category_section;
	window.asf_hit_category_section = asf_hit_category_section;
	window.initializeNiceASFTooltips = initializeNiceASFTooltips;
});

window.onload = asf_initializeCollapsableSectionsTabIndexes;

//todo: update tabindexes when adding and removeing input fields

//todo: reset names before submitting since the server expects continously numbered names

