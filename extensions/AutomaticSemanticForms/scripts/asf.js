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
		
	/*
	 * Init the syncer
	 */	
	init : function(){
		
		//todo: implement better widget for triggering updates of the complete form
		jQuery('#asf_category_annotations').click(this.updateForm);
		jQuery('#asf_category_annotations').css('cursor', 'pointer');
		
		this.blockFormUpdates = false;
		this.currentCategoryAnnotations = false;
		this.wtp = null;
		this.currentRelationsCount = 0;
		
		//todo:make sure that e.th. also works if freetext is hidden
		
		//todo. make sure that e.th. also works if the editor type is toggled
		
		//init freetxt content change listeners and do first form update
		if(typeof CKEDITOR != 'undefined'){
			CKEDITOR.on("instanceReady", function(){
	        	this.instances.free_text.document.on("keyup", ASFFormSyncer.checkIfFormUpdateIsNecessary);
	        	this.instances.free_text.document.on("paste", ASFFormSyncer.checkIfFormUpdateIsNecessary);
	        	this.instances.free_text.document.on("change", ASFFormSyncer.checkIfFormUpdateIsNecessary);

	        	this.currentFreeTextContent = 
	    			this.getFreeTextContent();
	        	
		    	ASFFormSyncer.updateForm();
	        });
		} else {
			jQuery('#free_text').change(this.checkIfFormUpdateIsNecessary);
			jQuery('#free_text').keyup(this.checkIfFormUpdateIsNecessary);
		
			this.currentFreeTextContent = 
				this.getFreeTextContent();
			
			ASFFormSyncer.updateForm();
		}
	},
	
	/*
	 * This method is called by the freetext input listeners
	 * and checks if the form may need an update
	 */
	checkIfFormUpdateIsNecessary : function(){
		
		//todo: filter some keys that do not change the content in order to gain some performance improvements
		
		var ts = new Date();
		var currentTS = ts.getMilliseconds();
		ASFFormSyncer.lastFreeTextChangeTS = currentTS;
		window.setTimeout(function() {
			//only call updateForm() if first s.th. has changed 
			//and second if nothing else has changed in the meantime
			if(currentTS == ASFFormSyncer.lastFreeTextChangeTS){
				var newFreeTextContent = ASFFormSyncer.getFreeTextContent();
				if(ASFFormSyncer.currentFreeTextContent
						!= newFreeTextContent){
					ASFFormSyncer.currentFreeTextContent = newFreeTextContent;
					ASFFormSyncer.updateForm();
				}
			}
		}, 750);
	},
	
	/*
	 * Returns the content of the freetext input field
	 */
	getFreeTextContent : function(){
		if(typeof CKEDITOR != 'undefined' && CKEDITOR.instances && CKEDITOR.instances.free_text){
			return CKEDITOR.instances.free_text.getData();
		} else {
			return jQuery('#free_text').val();
		}
	},
	
	/*
	 * Executes form updates if necessary
	 */
	updateForm : function(){
		
		if(this.blockFormUpdates == true){
			//form updates are currently blocked, i.e. because a new form version 
			//is currently loaded from the server
			//form updates will be executed again after form has been loaded completely
			return;
		}
		
		//init WikiTextParser
		this.wtp = new WikiTextParser();
		this.wtp.initialize(ASFFormSyncer.currentFreeTextContent);
		
		this.checkIfFormStructureUpdateIsnecessary(
			this.wtp.getCategories());
		
		this.updateFormFields(this.wtp.getRelations());
	},
	
	/*
	 * Method is callled if content in freetext has changed and 
	 * updates the form fields if necessary	 * 
	 */
	updateFormFields : function(relations){
		
		//first mark all as must be deleted and then remove those 
		//markers aone after another again if the value is still present
		jQuery('.asf-multi_value').attr('asf-delete-this', 'true');
		
		//todo: deal with invalid values, e.g. black for a boolean property
		
		for (var i = 0; i < relations.length; ++i) {
			
			var addAnnotation = true;
			
			var propName = relations[i].getName();
			propName = jQuery.trim(propName);
			propName = propName.charAt(0).toUpperCase() + propName.substr(1);
			jQuery('.asf-multi_values[property-name = "'+ propName +'"]').each(function(){
				var type = ASFMultiInputFieldHandler.getInputFieldType(this);
				
				var lastChild = null;
				jQuery('.asf-multi_value', this).each(function(){
					if(addAnnotation){ //annotation has not yet been found
						var currentValue = ASFMultiInputFieldHandler.getInputFieldValue(this, type);
						if(ASFMultiInputFieldHandler.areValuesEqual(currentValue, relations[i].getValue(), type)){
							jQuery(this).attr('asf-delete-this', 'false');
							jQuery(this).attr('asf-wp-rel-index', i);
							addAnnotation = false;
						} 
					}
					lastChild = this;
				});
				
				if(addAnnotation){
					//add a new form input field
					var newInputField = ASFMultiInputFieldHandler.doAddInputField(jQuery('> *:first-child', lastChild));
					ASFMultiInputFieldHandler.setInputFieldValue(newInputField, type, relations[i].getValue());
					jQuery(newInputField).attr('asf-delete-this', 'false');
					jQuery(newInputField).attr('asf-wp-rel-index', i);
					addAnnotation = false;
				}
			});
			
			if(addAnnotation){
				//add a new property to the unresolved annotations section
				
				//adding the new property to unresolved annotations section
				//has to be done synchroniously because there may be also
				//other values for this property in the relations array
				var url = wgServer + wgScriptPath + "/index.php";
				jQuery.ajax({ url:  url, 
					async: false,
					data: {
						'action' : 'ajax',
						'rs' : 'asff_getNewFormRow',
						'rsargs[]' : [propName]
					},
					success: function(data){
						data = data.substr(data.indexOf('--##starttf##--') + 15, data.indexOf('--##endtf##--') - data.indexOf('--##starttf##--') - 15); 
						data = jQuery.parseJSON(data);
						
						jQuery('.asf-unresolved-section').css('display', '');
						
						jQuery('.asf-unresolved-section table').append(data.html);
						var newInputField = jQuery('.asf-unresolved-section table tr:last-child .asf-multi_value').get(0);
						var type = ASFMultiInputFieldHandler.getInputFieldType(jQuery(newInputField).parent());
						ASFMultiInputFieldHandler.doInit(jQuery(newInputField).parent());
						ASFMultiInputFieldHandler.setInputFieldValue(newInputField, type, relations[i].getValue());
						jQuery(newInputField).attr('asf-delete-this', 'false');
						jQuery(newInputField).attr('asf-wp-rel-index', i);
					}
				});
			}
		}
			
		//now delete all form fields that have not been added and that did not match one of the
		//relations in the freetext input field
		jQuery('.asf-multi_value:[asf-delete-this = "true"]').each(function(){
			ASFMultiInputFieldHandler.doDeleteInputField(jQuery('> *:first-child', this), false); 
		});
		
		this.currentRelationsCount = relations.length;
	},
	
	isUpdateFreeTextNecessary : function(){
		var ts = new Date();
		var currentTS = ts.getMilliseconds();
		var node = jQuery(this).get(0);
		jQuery(node).attr('asf-update-ts', currentTS);
		window.setTimeout(function() {
			if(currentTS == jQuery(node).attr('asf-update-ts')){
				var container = ASFMultiInputFieldHandler.getContainerOfInput(node);
				ASFFormSyncer.updateFreeText(container);
			}
		}, 750);
	},
	
	/*
	 * This method is called if s.t. has been changed in a form input field
	 * and it updates the free text if necessary
	 */
	updateFreeText : function(node){
		var type = ASFMultiInputFieldHandler.getInputFieldType(jQuery(node).parent());
		var newValue = ASFMultiInputFieldHandler.getInputFieldValue(node, type);
		
		//this is a form input field for which we currently do not support syncronisation
		if(newValue == undefined){
			return;
		}
		
		//do last check if update is necessary
		if(jQuery(node).attr('asf-last-field-value') == newValue){
			return;
		}
		jQuery(node).attr('asf-last-field-value', newValue);
		
		var relationIndex = jQuery(node).attr('asf-wp-rel-index');
		
		//init WikiTextParser
		this.wtp = new WikiTextParser();
		this.wtp.initialize(ASFFormSyncer.getFreeTextContent());

		if(relationIndex == undefined){
			
			if(newValue.length == 0){
				//nothing to do since we do not create or restore
				//a property for an empty value
				return;
			}
			
			oldStartPos = jQuery(node).attr('asf-wp-oldstartpos');
			if(oldStartPos != undefined){
				this.restorePropertyInFreeText(node, newValue);
			} else {
				this.addPropertyToFreeText(node, newValue);
			}
		} else {
			var currentRelation = this.wtp.getRelations();
			currentRelation = currentRelation[relationIndex];
			
			if(newValue.length > 0){
				this.editPropertyInFreeText(currentRelation, newValue);
			} else {
				this.deletePropertyInFreetext(currentRelation, relationIndex, node);
			}
		}
	},
	
	addPropertyToFreeText : function(container, newValue){
		var newAnnotation = '[[';
		newAnnotation += 
			jQuery(container).parent().attr('property-name');
		newAnnotation += '::' + newValue;
		newAnnotation += '| ]]';
		
		this.wtp.addAnnotation(newAnnotation, true);
		
		jQuery(container).attr('asf-wp-rel-index', this.currentRelationsCount);
		this.currentRelationsCount += 1;
	},
	
	restorePropertyInFreeText : function(container, newValue){
		
		//fix for really strange error
		if(this.wtp.relations == null){
			this.wtp.relations  = new Array();
			this.wtp.categories  = new Array();
			this.wtp.links  = new Array();
			this.wtp.rules  = new Array();
			this.wtp.askQueries = new Array();
		}
		
		var restoredAnnotation = new WtpAnnotation(
				'', oldStartPos, oldStartPos, this.wtp);
		
		restoredAnnotation.name = 
			jQuery(container).parent().attr('property-name');
		restoredAnnotation.representation = 
			jQuery(container).attr('asf-wp-oldlabel');;
		
		this.editPropertyInFreeText(restoredAnnotation, newValue)
		
		//noe restore the rel indexes and so on
		var relationIndex = jQuery(container).attr('asf-wp-temp-rel-index');
			
		jQuery('*:[asf-wp-rel-index]').each(function(){
			var currentIndex = jQuery(this).attr('asf-wp-rel-index');
			if(currentIndex * 1 >= relationIndex){
				jQuery(this).attr('asf-wp-rel-index', currentIndex*1+1);
			}
		});
		
		jQuery(container).attr('asf-wp-rel-index', relationIndex);
		jQuery(container).removeAttr('asf-wp-temp-rel-index');
		jQuery(container).removeAttr('asf-wp-oldstartpos');
		
		this.currentRelationsCount -= 1;
	},
	
	destroyFreeTextAnnotationBackup : function(){
		var container = ASFMultiInputFieldHandler.getContainerOfInput(this);
		jQuery(container).removeAttr('asf-wp-temp-rel-index');
		jQuery(container).removeAttr('asf-wp-oldstartpos');
		jQuery(container).removeAttr('asf-wp-oldlabel');
	},
	
	/*
	 * Edits a property value in the freetext input field
	 */
	editPropertyInFreeText : function(currentRelation, newValue){
		//todo:make this also work with #set
		var newRelation = '[[';
		newRelation += currentRelation.getName();
		newRelation += '::' + newValue;
		label = currentRelation.getRepresentation();
		if(label.length > 0){
			newRelation += '|' + label;
		}
		newRelation += ']]';
		
		this.wtp.replaceAnnotation(currentRelation, newRelation);
	},
	
	deletePropertyInFreetext : function(currentRelation, relationIndex, container){

		//remove and backup data for later restore
		jQuery(container).attr('asf-wp-temp-rel-index',
			jQuery(container).attr('asf-wp-rel-index'));
		jQuery(container).removeAttr('asf-wp-rel-index');
		jQuery(container).attr('asf-wp-oldstartpos', currentRelation.getStart());
		jQuery(container).attr('asf-wp-oldlabel', currentRelation.getRepresentation());
		
		this.wtp.replaceAnnotation(currentRelation, '');
		
		jQuery('*:[asf-wp-rel-index]').each(function(){
			var currentIndex = jQuery(this).attr('asf-wp-rel-index');
			if(currentIndex * 1 > relationIndex){
				jQuery(this).attr('asf-wp-rel-index', currentIndex*1-1);
			}
		});
		
		this.currentRelationsCount -= 1;
	},
	
	/*
	 * Computes the current category annotation string and triggers an update of the form structure
	 * if category annotations have changed in the freetext editor
	 */
	checkIfFormStructureUpdateIsnecessary : function(newCategoryAnnotations){
		
		var updateNecessary = false;
		var initPhase = false;
		
		if(!this.currentCategoryAnnotations){
			//we are in initialization phase
			initPhase = true;
		} 

		if(this.currentCategoryAnnotations.length != newCategoryAnnotations.length){
			//in this case we can be sure that an update is necessary and
			//we will not have to do any further checks
			updateNecessary = true;
		}
		
		this.currentCategoryString = "";
		for(var i=0; i<newCategoryAnnotations.length; i++){
			this.currentCategoryString += '<span>,</span> ' + newCategoryAnnotations[i].getName();
			
			//if we are not in initphase and if we have not yet decided to do an update, we have to check if it might be necessary
			if(!updateNecessary && !initPhase){
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
					ASFFormSyncer.blockFormUpdates = true;
					ASFFormSyncer.updateFormStructure();
				}
			}, 1000);
		}
	},

	/*
	 * Gets a new form from the server because category annotations have changed
	 */
	updateFormStructure : function(){

		var inputFieldIds = '';
		jQuery('.asf-multi_value').each(function(){
			if(ASFMultiInputFieldHandler.isInputFieldValueSet(this)){
				inputFieldIds += '<<<' + jQuery(this).parent().attr('property-name');
			}
		});
		
		var url = wgServer + wgScriptPath + "/index.php";
		jQuery.ajax({ url:  url, 
			data: {
				'action' : 'ajax',
				'rs' : 'asff_getNewForm',
				'rsargs[]' : [ASFFormSyncer.currentCategoryString, inputFieldIds]
			},
			success: this.updateFormStructureCallBack			
		});
	},
	
	/*
	 * Callback for the updateFormStructure method
	 * adds the new form to the page and initializes it
	 */
	updateFormStructureCallBack : function(data){
		data = data.substr(data.indexOf('--##starttf##--') + 15, data.indexOf('--##endtf##--') - data.indexOf('--##starttf##--') - 15); 
		data = jQuery.parseJSON(data);
		
		var currentContainer = '#asf_formfield_container';
		var newContainer = '#asf_formfield_container2';
		if(jQuery('#asf_formfield_container').html() == ''){
			currentContainer = '#asf_formfield_container2';
			newContainer = '#asf_formfield_container';
		}
		jQuery(newContainer).html(data.html);
		
		var missingAnnotations = new Array();
		jQuery(currentContainer + ' .asf-multi_value').each(function(){
			if(ASFMultiInputFieldHandler.isInputFieldValueSet(this)){
				var missingAnnotation = new Object();
				missingAnnotation.name = jQuery(this).parent().attr('property-name');
				var type = ASFMultiInputFieldHandler.getInputFieldType(jQuery(this).parent());
				missingAnnotation.value = ASFMultiInputFieldHandler.getInputFieldValue(this, type);
				missingAnnotations.push(missingAnnotation);
			}
		});
		
		jQuery(currentContainer).html('');
		
		//update form input fields which are not shown in free text
		ASFMultiInputFieldHandler.init();
		for(var i=0; i < missingAnnotations.length; i++){
			jQuery('.asf-multi_values[property-name = "' + missingAnnotations[i].name	+'"]').each(function(){
				var newInputField = ASFMultiInputFieldHandler.doAddInputField(jQuery('.asf-multi_value:last-child > *:last-child', this));
				var type = ASFMultiInputFieldHandler.getInputFieldType(this);
				ASFMultiInputFieldHandler.setInputFieldValue(newInputField, type, 
					missingAnnotations[i].value);
			});
		}
		
		//run init methods
		initializeNiceASFTooltips();
		asf_makeReadOnly();	

		//sync with freetext input field
		ASFFormSyncer.blockFormUpdates = false;
		ASFFormSyncer.updateForm();
	}
};

window.ASFMultiInputFieldHandler = {
	
	init : function(){
		jQuery('.asf-multi_values').each(function(){
			ASFMultiInputFieldHandler.doInit(this);
		});
	},	
		
	doInit : function(node){
		
		jQuery(node).attr('property-name',
			jQuery('.asf-mv_propname', node).html());
			
		var partOfUnresolvedAnnotationsSection = false;
		if(jQuery(node).attr('class').indexOf('asf-partOfUnresolvedAnnotationsSection') > 0){
			partOfUnresolvedAnnotationsSection = true;
		}
			
		var allowsMultipleValues = false;
		if(jQuery(node).attr('class').indexOf('asf-allowsMultipleValues') > 0){
			allowsMultipleValues = true;
		}
			
		if(jQuery('.asf-multi_value', node).get().length > 1 ||
				partOfUnresolvedAnnotationsSection){
			jQuery('.asf-multi_value', node).each(function(){
				ASFMultiInputFieldHandler.addDeleteButton(this);
			});
		}
			
		if(allowsMultipleValues){
			jQuery('.asf-multi_value:last-child', node).each(function(){
				ASFMultiInputFieldHandler.addAddButton(this);
			});
		}
		
		jQuery('.asf-multi_value', node).each(function(){
			ASFMultiInputFieldHandler.addValueChangeListeners(this);
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
		ASFMultiInputFieldHandler.doDeleteInputField(this, true);
	},
	
	doDeleteInputField : function(node, updateFreeText){
		var parent = jQuery(node).parent().parent();
		
		//the deletion of this input field has been triggered
		//by pressing one of the delete buttons and thus the
		//freetext must be updated
		if(updateFreeText){
			var type = this.getInputFieldType(jQuery(node).parent().parent());
			this.setInputFieldValue(jQuery(node).parent(), type, '');
			ASFFormSyncer.updateFreeText(jQuery(node).parent());
		}
		
		var partOfUnresolvedAnnotationsSection = false;
		if(jQuery(parent).attr('class').indexOf('asf-partOfUnresolvedAnnotationsSection') > 0){
			partOfUnresolvedAnnotationsSection = true;
		}
		
		var inputFieldCount = 
			jQuery('.asf-multi_value', parent).get().length;
		
		
		if(inputFieldCount == 1){
			if(partOfUnresolvedAnnotationsSection){
				//we are in the unresolved annotations section 
				//and the last input field, respectively the complete row
				//will be removed
			
				jQuery(parent).parent().parent().remove();

				if(jQuery('.asf-unresolved-section tr').get().length == 1){
					jQuery('.asf-unresolved-section').css('display', 'none');
				}
			} else {
				//we are not in unresolved annotations section and 
				//instead of deleting this input field we have to set it
				//to an empty value
			}
		} else {
			jQuery(node).parent().remove();
			
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
		ASFMultiInputFieldHandler.doAddInputField(this);
	},
	
	doAddInputField : function(node){
		//copy node
		var parent = jQuery(node).parent();
		jQuery(parent).parent().append(jQuery(parent).clone());
		
		var newInputField = jQuery('.asf-multi_value:last-child', jQuery(parent).parent()).get(0);
		
		//deal with delete buttons
		jQuery('.asf-deletebutton', jQuery(parent).parent()).remove();
		jQuery('.asf-multi_value', jQuery(parent).parent()).each(function(){
			ASFMultiInputFieldHandler.addDeleteButton(this);
		});
		
		//deal with add buttons
		jQuery('.asf-addbutton', jQuery(parent).parent()).remove();
		jQuery(newInputField).each(function(){
			ASFMultiInputFieldHandler.addAddButton(this);
		});
		
		//set name
		ASFMultiInputFieldHandler.setInputFieldName(
				newInputField,
				jQuery('.asf-multi_value', jQuery(parent).parent()).length);
		
		//reset value
		var type = this.getInputFieldType(jQuery(newInputField).parent());
		this.setInputFieldValue(newInputField, type, '');
		
		//remove all old attributes
		jQuery(newInputField).removeAttr('asf-wp-rel-index');
		jQuery(newInputField).removeAttr('asf-wp-temp-rel-index');
		jQuery(newInputField).removeAttr('asf-wp-oldstartpos');
		jQuery(newInputField).removeAttr('asf-wp-oldlabel');
		
		ASFMultiInputFieldHandler.addValueChangeListeners(newInputField);
		
		return newInputField;
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
	},
	
	getInputFieldType : function(container){
		return jQuery('.asf-mv_inputtype', container).html();
	},
	
	isInputFieldValueSet : function(container){
		var type = this.getInputFieldType(jQuery(container).parent());
		var value = this.getInputFieldValue(container, type );
		
		if(type == 'text' || type == 'haloactext'){
			if(value.length > 0){
				return true;
			}
		}
		
		if(type == 'textarea' || type == 'haloactextarea'){
			if(value.length > 0){
				return true;
			}
		}
		
		if(type == 'dropdown'){
			if(value.length > 0){
				return true;
			}
		}
		
		if(type == 'checkbox'){
			if(value == 'true'){
				//todo: value might have been set explicitly to false
				return true;
			}
		}
		
	},
	
	getInputFieldValue : function(container, type){
		if(type == 'text' || type == 'haloactext'){
			return jQuery('input', container).val();
		}
		
		if(type == 'textarea' || type == 'haloactextarea'){
			return jQuery('textarea', container).val();
		}
		
		if(type == 'dropdown'){
			return jQuery('select', container).val();
		}
		
		if(type == 'checkbox'){
			if(jQuery('input:nth-child(2)', container).attr('checked')){
				return 'true';
			} else {
				return 'false';
			}
		}
		
		return undefined;
	},
	
	setInputFieldValue : function(container, type, value){
		if(type == 'text' || type == 'haloactext'){
			value = jQuery.trim(value);
			jQuery('input', container).val(value);
			return true;
		}
		
		if(type == 'textarea' || type == 'haloactextarea'){
			value = jQuery.trim(value);
			jQuery('textarea', container).val(value);
			return true;
		}
		
		if(type == 'dropdown'){
			value = jQuery.trim(value);
			jQuery('select', container).val(value);
			return true;
		}
		
		if(type == 'checkbox'){
			if(this.normalizeValue(value, type) == 'true'){
				jQuery('input:nth-child(2)', container).attr('checked', 'true');
			} else {
				jQuery('input:nth-child(2)', container).removeAttr('checked');
			}
			return true;
		}
		
		return false;
	},
	
	addValueChangeListeners : function(node){
		jQuery('input', node).each(function(){
			jQuery(this).keyup(ASFFormSyncer.isUpdateFreeTextNecessary);
			jQuery(this).change(ASFFormSyncer.isUpdateFreeTextNecessary);
			jQuery(this).blur(ASFFormSyncer.destroyFreeTextAnnotationBackup);
		});
		
		jQuery('textarea', node).each(function(){
			jQuery(this).keyup(ASFFormSyncer.isUpdateFreeTextNecessary);
			jQuery(this).change(ASFFormSyncer.isUpdateFreeTextNecessary);
			jQuery(this).blur(ASFFormSyncer.destroyFreeTextAnnotationBackup);
		});
		
		jQuery('select', node).each(function(){
			jQuery(this).keyup(ASFFormSyncer.isUpdateFreeTextNecessary);
			jQuery(this).change(ASFFormSyncer.isUpdateFreeTextNecessary);
			jQuery(this).blur(ASFFormSyncer.destroyFreeTextAnnotationBackup);
		});
	},
	
	getContainerOfInput : function(node){
		var parent = jQuery(node).parent();
		var classes = jQuery(parent).attr('class');
		if(classes != undefined && classes.indexOf('asf-multi_value') >= 0){
			return parent;
		} else {
			return ASFMultiInputFieldHandler.getContainerOfInput(parent);
		}
	},
	
	areValuesEqual : function(formValue, freeTextValue, type){
		formValue = this.normalizeValue(formValue, type);
		freeTextValue = this.normalizeValue(freeTextValue, type);
		
		if(formValue == freeTextValue){
			return true;
		}
		
		return false;
	},
	
	normalizeValue : function(value, type){
		if(type == 'text' || type == 'haloactext'){
			value = jQuery.trim(value);
			value = value.charAt(0).toUpperCase() + value.substr(1);
		}
		
		if(type == 'textarea' || type == 'haloactextarea'){
			value = jQuery.trim(value);
			value = value.charAt(0).toUpperCase() + value.substr(1);;
		}
		
		if(type == 'dropdown'){
			value = jQuery.trim(value);
		}
		
		if(type == 'checkbox'){
			value = jQuery.trim(value);
			value = value.charAt(0).toLowerCase() + value.substr(1);;
			
			if(value != 'false' && value != 'no' && value != '0'
					&& value != 'n' && value != 'f'){
				value = 'true';
			} else {
				value = 'false';
			}
		}
		
		return value;
	}
};


jQuery(document).ready( function($) {
	initializeNiceASFTooltips();
	asf_hideFreeText();	
	asf_makeReadOnly();
	ASFMultiInputFieldHandler.init();
	ASFFormSyncer.init();
	
	
	window.asf_hide_category_section = asf_hide_category_section;
	window.asf_show_category_section = asf_show_category_section;
	window.asf_hit_category_section = asf_hit_category_section;
	window.initializeNiceASFTooltips = initializeNiceASFTooltips;
});

window.onload = asf_initializeCollapsableSectionsTabIndexes;

//todo: update tabindexes when adding and removeing input fields

//todo: reset names before submitting since the server expects continously numbered names

//todo: add type validation to input field values, i.e. validate if input field for property of type number really contains a number