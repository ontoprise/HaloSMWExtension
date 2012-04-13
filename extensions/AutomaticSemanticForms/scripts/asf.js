
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
		
		//asf postprocessing must be done if form is submitted
		//and sf validation succeeds 
		var validateAllTemp = window.validateAll;
		window.validateAll = function(){
			ASFMultiInputFieldHandler.dealWithMandatoryInputFields();
			if(validateAllTemp()){
				ASFFormSyncer.doPostProcessingBeforeSubmit();
				return true;
			}
			return false;
		};
		
		jQuery('#asf_category_annotations').click(this.updateForm);
		jQuery('#asf_category_annotations').css('cursor', 'pointer');
		
		this.blockFormUpdates = false;
		this.currentCategoryAnnotations = false;
		this.currentRelationsCount = 0;
		this.propertiesWithNoASF = new Array();
		this.syntaxErrorCount = 0;
		
		//init freetxt content change listeners and do first form update
		if(mw.util.getParamValue('mode') == 'wysiwyg'){
			CKEDITOR.on("instanceReady", function(){
	        	ASFFormSyncer.currentFreeTextContent = 
	        		ASFFormSyncer.getFreeTextContent();
	        	
		    	ASFFormSyncer.updateForm();
		    	
		    	ASFFormSyncer.checkIfFormUpdateIsNecessary();
	        });
		} else {
			this.currentFreeTextContent = 
				this.getFreeTextContent();
			
			ASFFormSyncer.updateForm();
			
			ASFFormSyncer.checkIfFormUpdateIsNecessary();
		}
		
		//set focus to first input field and make sure that stb does not set it to free text again
		ASFFormSyncer.scrollTop = true;
		var refreshToolBarTmp = window.refreshSTB.refreshToolBar;
		window.refreshSTB.refreshToolBar = function(){
			refreshToolBarTmp();
			if(ASFFormSyncer.scrollTop){
				jQuery('html').animate({scrollTop : 0},0);
				ASFFormSyncer.scrollTop = false;
				jQuery(jQuery('.formtable input').get(0)).focus();
			}
		}
		
		jQuery('#sfForm').mouseenter(ASFFormSyncer.resizeFormFields);
	},
	
	resizeFormFields : function(){
		//popup detection
		jQuery('.asf-complete-width').each(function(){
			if(jQuery(this).width() < 600){
				jQuery(this).css('width', '90%');
			}
		});
	},
	
	doPostProcessingBeforeSubmit : function(){
		//all form input fields whose values also van be found in freetext
		//must be removed to avoid that they are stored twice
		jQuery('.asf-multi_value[asf-wp-rel-index]').remove();
		
		//input field name indexes must be n an order for storing
		ASFMultiInputFieldHandler.resetInputFieldNames();
	},

	/*
	 * This method is called by the freetext input listeners
	 * and checks if the form may need an update
	 */
	checkIfFormUpdateIsNecessary : function(){
		
		window.setTimeout(function() {
			var newFreeTextContent = ASFFormSyncer.getFreeTextContent();
			if(ASFFormSyncer.currentFreeTextContent
					!= newFreeTextContent){
				ASFFormSyncer.currentFreeTextContent = newFreeTextContent;
				window.setTimeout(function() {
					var newFreeTextContent = ASFFormSyncer.getFreeTextContent();
					if(ASFFormSyncer.currentFreeTextContent
							== newFreeTextContent){
						ASFFormSyncer.updateForm();
					}
				}, 750);
			}
			ASFFormSyncer.checkIfFormUpdateIsNecessary();
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
	
	setFreeTextContent : function(text){
		if(typeof CKEDITOR != 'undefined' && CKEDITOR.instances && CKEDITOR.instances.free_text){
			CKEDITOR.instances.free_text.setData(text);
		}
		//this always has to be done due to strange behaviour of wysiwyg if toggled to wikitext editor
		jQuery('#free_text').val(text);
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
		this.wtp.initialize();
		this.wtp.text = this.getFreeTextContent();
		this.wtp.parseAnnotations();
		
		//update will not be done immediately if rreetext contains errors
		//and if free text has focus
		if(this.wtp.getError() > 0 && !jQuery('.formtable *:focus').length
				&& ASFFormSyncer.syntaxErrorCount < 5){
			ASFFormSyncer.currentFreeTextContent = -1;	
			ASFFormSyncer.syntaxErrorCount += 1;
			return;
		} else {
			ASFFormSyncer.syntaxErrorCount = 0;
		}
		
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
		jQuery('.asf-syncronized-value').attr('asf-delete-this', 'true');
		
		//console.log('update fields');
		for (var i = 0; i < relations.length; ++i) {
			//console.log('update fields for:' + relations[i].getName() + ' : ' + relations[i].getValue());
			
			//annotations with an empty value are considered to be deleted
			if(jQuery.trim(relations[i].getValue()).length == 0){
				continue;
			}
			
			var addAnnotation = true;
			
			var propName = relations[i].getName();
			propName = jQuery.trim(propName);
			propName = propName.charAt(0).toUpperCase() + propName.substr(1);
			jQuery('.asf-multi_values[property-name = "'+ propName +'"]').each(function(){
				var type = ASFMultiInputFieldHandler.getInputFieldType(this);
				
				var lastChild = null;
				jQuery('.asf-multi_value', this).each(function(){
					if(addAnnotation){ 
						
						//check if this is a syncronized value
						if(jQuery(this).attr('class').indexOf('asf-syncronized-value') > 0){
						
							//annotation has not yet been found
							if(jQuery(this).attr('asf-delete-this') == 'true'){ 
								//input field has not already been associated with another annoation in free text
								var currentValue = ASFMultiInputFieldHandler.getInputFieldValue(this, type);
								//console.log('update field compare with ' + currentValue);
								if(ASFMultiInputFieldHandler.areValuesEqual(currentValue, relations[i].getValue(), type)){
									jQuery(this).attr('asf-delete-this', 'false');
									jQuery(this).attr('asf-wp-rel-index', i);
									addAnnotation = false;
								}
							}
						}
					}
					lastChild = this;
				});
				
				if(addAnnotation && lastChild != null){
					//console.log('update field add new input field');
					//add a new form input field
					var newInputField = ASFMultiInputFieldHandler.doAddInputField(jQuery('> *:first-child', lastChild));
					ASFMultiInputFieldHandler.setInputFieldValue(newInputField, type, relations[i].getValue());
					jQuery(newInputField).attr('asf-delete-this', 'false');
					jQuery(newInputField).attr('asf-wp-rel-index', i);
					jQuery(newInputField).addClass('asf-syncronized-value');
					addAnnotation = false;
					
					//if last child has not value, then it should be deleted
					if(!ASFMultiInputFieldHandler.isInputFieldValueSet(
							jQuery(newInputField).prev())){
						jQuery(newInputField).prev().attr('asf-delete-this', 'true');						
					}
						
						
				}
			});
			
			if(addAnnotation){
				//add a new property to the unresolved annotations section
				
				//first check if we already cached that this prop has a no automatic formedit annotation
				for(var k=0; k < ASFFormSyncer.propertiesWithNoASF.length; k++){
					if(propName == ASFFormSyncer.propertiesWithNoASF[k]) continue;
				}
				
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
						
						//console.log('update field add new row');
						
						//the property has a no automatic formedit annotation and no formfield will be shown
						if(data.noasf){
							ASFFormSyncer.propertiesWithNoASF.push(propName);
							return;
						}
						
						jQuery('.asf-unresolved-section').css('display', '');
						
						//first check if there is already a row for that property with some uneditable values
						var uneditableSection = '';
						jQuery('.asf-uneditable_values[property-name = "' + propName + '"]').each(function(){
							uneditableSection = jQuery(this).html();
							jQuery(this).parent().parent().remove();
						});

						jQuery('.asf-unresolved-section table').append(data.html);
						
						//the new inputs may have ids that already exist
						jQuery('.asf-unresolved-section table tr:last-child input[id]').each(function (){
							//such a id cannot yet exist since then we would not have to add this new row
							var currentId = jQuery(this).attr('id');
							jQuery(this).attr('id', currentId + propName.replace(/ /g, '_'));
							
							//old id may occur in the javascript string
							regexp = new RegExp(currentId, "g");
							data.scripts = data.scripts.replace(regexp, jQuery(this).attr('id'));
							
							//may be used if this is a datepicker
							jQuery(this).attr('oldId', currentId);
						});
						
						//some input field types may have their own custm javascript, that must be executed now
						jQuery('.asf-unresolved-section table').append(data.scripts);
						
						var newInputField = jQuery('.asf-unresolved-section table tr:last-child .asf-multi_value').get(0);
						var type = ASFMultiInputFieldHandler.getInputFieldType(jQuery(newInputField).parent());
						ASFMultiInputFieldHandler.doInit(jQuery(newInputField).parent());
						ASFMultiInputFieldHandler.setInputFieldValue(newInputField, type, relations[i].getValue());
						jQuery(newInputField).attr('asf-delete-this', 'false');
						jQuery(newInputField).attr('asf-wp-rel-index', i);
						jQuery(newInputField).addClass('asf-syncronized-value');
						
						//add section again for uneditable values if one is available
						jQuery(newInputField).parent().next().html(uneditableSection);
						jQuery(newInputField).parent().next().attr('property-name', propName);
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
		
		ASFFormSyncer.resizeFormFields();
	},
	
	isUpdateFreeTextNecessary : function(){
		var ts = new Date();
		var currentTS = ts.getMilliseconds();
		var node = jQuery(this);
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
		
		//console.log('update free text');
		
		//this is a form input field for which we currently do not support syncronisation
		if(newValue == undefined){
			return;
		}
		
		//check if this input field is syncronized to free text

		if(jQuery(node).attr('class').indexOf('asf-syncronized-value') == -1
				&& jQuery(node).attr('asf-syncronized') != 'true'){
			return;
		}
		
		//do last check if update is necessary
		if(jQuery(node).attr('asf-last-field-value') == newValue){
			return;
		}
		jQuery(node).attr('asf-last-field-value', newValue);
		
		//console.log('update free text new value: ' + newValue);
		
		var relationIndex = jQuery(node).attr('asf-wp-rel-index');
		
		//init WikiTextParser
		this.wtp = new WikiTextParser();
		this.wtp.initialize();
		this.wtp.text = this.getFreeTextContent();
		this.wtp.parseAnnotations();
		
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
			//console.log('current relation ' + relationIndex);
			//for(i=0; i < currentRelation.length; i++){
			//	console.log(i + ' ' + currentRelation[i].getName());
			//}
			
			currentRelation = currentRelation[relationIndex];
			
			if(jQuery.trim(newValue).length > 0){
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
		this.setFreeTextContent(this.wtp.text);
		
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
		if(jQuery(container).attr('asf-syncronized') == 'true'){
			jQuery(container).addClass('asf-syncronized-value');
		}
		jQuery(container).removeAttr('asf-syncronized');
		
		this.currentRelationsCount -= 1;
	},
	
	destroyFreeTextAnnotationBackup : function(){
		var container = ASFMultiInputFieldHandler.getContainerOfInput(this);
		jQuery(container).removeAttr('asf-wp-temp-rel-index');
		jQuery(container).removeAttr('asf-wp-oldstartpos');
		jQuery(container).removeAttr('asf-wp-oldlabel');
		jQuery(container).removeAttr('asf-syncronized');
	},
	
	/*
	 * Edits a property value in the freetext input field
	 */
	editPropertyInFreeText : function(currentRelation, newValue){
		var newRelation = '[[';
		newRelation += currentRelation.getName();
		newRelation += '::' + newValue;
		label = currentRelation.getRepresentation();
		if(label.length > 0){
			newRelation += '|' + label;
		}
		newRelation += ']]';
		
		this.wtp.replaceAnnotation(currentRelation, newRelation);
		this.setFreeTextContent(this.wtp.text);
	},
	
	deletePropertyInFreetext : function(currentRelation, relationIndex, container){

		//remove and backup data for later restore
		jQuery(container).attr('asf-wp-temp-rel-index',
			jQuery(container).attr('asf-wp-rel-index'));
		jQuery(container).removeAttr('asf-wp-rel-index');
		jQuery(container).attr('asf-wp-oldstartpos', currentRelation.getStart());
		jQuery(container).attr('asf-wp-oldlabel', currentRelation.getRepresentation());

		if(jQuery(container).attr('class').indexOf('asf-syncronized-value') > -1){
			jQuery(container).attr('asf-syncronized', 'true');
		}
		jQuery(container).removeClass('asf-syncronized-value');
		
		this.wtp.replaceAnnotation(currentRelation, '');
		this.setFreeTextContent(this.wtp.text);
		
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
			this.currentCategoryAnnotations = new Array();
			
			this.staticCategoryAnnotations = new Array();
		} 

		if(this.currentCategoryAnnotations.length > newCategoryAnnotations.length){
			//in this case we can be sure that an update is necessary and
			//we will not have to do any further checks
			updateNecessary = true;
		}
		
		var newCurrentCategoryAnnotations = new Array();
		this.currentCategoryString = "";
		for(var i=0; i<newCategoryAnnotations.length; i++){
			
			var isSourceAnnotation = false;
			if(initPhase){
				jQuery('#asf_source_categories span').each(function(){
					if(jQuery(this).html() == jQuery.trim(newCategoryAnnotations[i].getName())){
						jQuery(this).remove();
					}
				});
			} else {
				for(var j=0; j < this.staticCategoryAnnotations.length; j++) {
					if(this.staticCategoryAnnotations[j] == jQuery.trim(newCategoryAnnotations[i].getName())){
						isSourceAnnotation = true;
					}
				}
			}
			
			if(!isSourceAnnotation && jQuery.trim(newCategoryAnnotations[i].getName()).length > 0){
				this.currentCategoryString += '<span>,</span> ' + newCategoryAnnotations[i].getName();
			}
				
			newCurrentCategoryAnnotations.push(newCategoryAnnotations[i].getName());
			
			//if we are not in initphase and if we have not yet decided to do an update, we have to check if it might be necessary
			if(!isSourceAnnotation && !updateNecessary && !initPhase){
				var found = false;
				for(var k=0; k<this.currentCategoryAnnotations.length; k++){
					if(newCategoryAnnotations[i].getName()
							== this.currentCategoryAnnotations[k]){
						found = true;
						break;
					}
				}
				if(!found){
					updateNecessary = true;
				}
			}
		}
		
		this.currentCategoryAnnotations = newCurrentCategoryAnnotations;
		if(initPhase){
			jQuery('#asf_source_categories span').each(function(){
				ASFFormSyncer.staticCategoryAnnotations.push(jQuery(this).html());
			});
		} else {
			for(var j=0; j < this.staticCategoryAnnotations.length; j++) {
				ASFFormSyncer.currentCategoryString +=
					'<span>,</span> ' + this.staticCategoryAnnotations[j];
			}
		}
		
		//remove first comma
		this.currentCategoryString = this.currentCategoryString.substring(
				'<span>,</span> '.length);
		
		jQuery('#asf_category_string').html(this.currentCategoryString);
		jQuery('#asf_category_annotations input').val(this.currentCategoryString);
		
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
		
		jQuery('.asf-uneditable_values').each(function(){
			if(jQuery('p', this).get().length > 0){
				inputFieldIds += '<<<' + jQuery(this).prev().attr('property-name');
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
		
		var missingAnnotations = new Array();
		jQuery(currentContainer + ' .asf-multi_value').each(function(){
			if(ASFMultiInputFieldHandler.isInputFieldValueSet(this)){
				var missingAnnotation = new Object();
				
				missingAnnotation.name = jQuery(this).parent().attr('property-name');
				
				var type = ASFMultiInputFieldHandler.getInputFieldType(jQuery(this).parent());
				missingAnnotation.value = ASFMultiInputFieldHandler.getInputFieldValue(this, type);
				
				var insync = false;
				if(jQuery(this).attr('class').indexOf('asf-syncronized-value') > 0){
					insync = true;
				}
				missingAnnotation.insync = insync;
				
				//console.log(missingAnnotation.name + ' ' + missingAnnotation.insync + ' ' + missingAnnotation.value);
				
				missingAnnotations.push(missingAnnotation);
			}
		});
		
		jQuery(newContainer).html(data.html);
		
		var uneditableSections = new Array();
		jQuery(currentContainer + ' .asf-uneditable_values').each(function(){
			if(jQuery(this).html().length > 0){
				var uneditableSection = new Object();
				uneditableSection.name = jQuery(this).prev().attr('property-name');
				uneditableSection.html = jQuery(this).html();
				uneditableSections.push(uneditableSection);
			}
		});
		
		jQuery(currentContainer).html('');
		
		//some input field types may have their own custm javascript, that must be executed now
		jQuery(newContainer).html(
			jQuery(newContainer).html() + data.scripts);
		
		//first set all input fields in the new form to be syncronized
		jQuery('.asf-multi_value').addClass('asf-syncronized-value');
		
		//update form input fields which are not shown in free text
		ASFMultiInputFieldHandler.init();
		for(var i=0; i < missingAnnotations.length; i++){
			jQuery('.asf-multi_values[property-name = "' + missingAnnotations[i].name	+'"]').each(function(){
				var newInputField = ASFMultiInputFieldHandler.doAddInputField(jQuery('.asf-multi_value:last-child > *:last-child', this));
				var type = ASFMultiInputFieldHandler.getInputFieldType(this);
				ASFMultiInputFieldHandler.setInputFieldValue(newInputField, type, 
					missingAnnotations[i].value);
				if(!missingAnnotations[i].insync){
					//console.log('not in sync: ' + missingAnnotations[i].name + ' ' + missingAnnotations[i].value);
					jQuery(newInputField).removeClass('asf-syncronized-value');
				} else {
					//this is necessary because if the last input field that was added was
					//not insync we will not inherit this class
					jQuery(newInputField).addClass('asf-syncronized-value');
				}
			});
		}
		
		//add sections for unediable values again
		for(var i=0; i < uneditableSections .length; i++){
			jQuery('.asf-multi_values[property-name = "' + uneditableSections [i].name	+'"]').each(function(){
				jQuery(this).next().html(uneditableSections[i].html);
			})
		}
		
		//input fields in unresolved annotations section that have no value must be removed again
		jQuery('.asf-unresolved-section .asf-multi_value').each(function(){
			if(!ASFMultiInputFieldHandler.isInputFieldValueSet(this)){
				jQuery(this).remove();
			}		
		});
		
		//now set all input fields which have only one child to be unsyncronized 
		//again if they are part of unresolved annoations section
		jQuery('.asf-multi_values').each(function(){
			if(jQuery('.asf-multi_value', this).get().length == 1 &&
					jQuery(this).attr('class').indexOf('asf-partOfUnresolvedAnnotationsSection') == -1){
				jQuery('.asf-multi_value', this).removeClass('asf-syncronized-value');
			}		
		});
		
		//run init methods
		initializeNiceASFTooltips();
		asf_makeReadOnly();	

		//sync with freetext input field
		ASFFormSyncer.blockFormUpdates = false;
		
		//make sure that this update is done immediatelly, even if
		//freetext contains errors
		ASFFormSyncer.syntaxErrorCount = 99;
		
		ASFFormSyncer.updateForm();
	}
};

window.ASFMultiInputFieldHandler = {
	
	init : function(){
		
		//the following must not be done if init is called after
		//a form structure update
		if(this.alreadyInitialized == undefined){
			this.alreadyInitialized = true;
			
			this.cachedDateStringLookups = new Array();
			
			this.overwriteSFInputInitMethod();
		}
		
		jQuery('.asf-multi_values').each(function(){
			ASFMultiInputFieldHandler.doInit(this);
		});
		
		//init uneditable values section
		jQuery('.asf-uneditable_values').each(function(){
			jQuery(this).attr('property-name', jQuery('.asf-mv_propname', this).html());
		});
	},	
	
	overwriteSFInputInitMethod : function(){
		//console.log('overwrite sf init method');
		jQuery.fn.SemanticForms_registerInputInit_temp = jQuery.fn.SemanticForms_registerInputInit;
		jQuery.fn.SemanticForms_registerInputInit = function(initFunction, param, noexecute){
			//console.log('execute sf init method');
			if(initFunction == SFI_DP_init){
				ASFMultiInputFieldHandler.overwriteDatePickerInitMethod();
				initFunction = window.SFI_DP_init;
			} else if(initFunction == SFI_DTP_init){
				ASFMultiInputFieldHandler.overwriteDateTimePickerInitMethod();
				ASFMultiInputFieldHandler.overwriteDatePickerInitMethod();
				ASFMultiInputFieldHandler.overwriteTimePickerInitMethod();
				initFunction = window.SFI_DTP_init;
			} else if(initFunction == SFI_TP_init){
				ASFMultiInputFieldHandler.overwriteTimePickerInitMethod();
				initFunction = window.SFI_TP_init;
			}
			return this.SemanticForms_registerInputInit_temp(initFunction, param, noexecute);
		}
	},
	
	overwriteDatePickerInitMethod : function(){
		if(typeof SFI_DP_init != 'undefined' && SFI_DP_init.hijacked != true){
			//console.log('overwrite dp init method');
			var temp = window.SFI_DP_init;
			window.SFI_DP_init = function(input_id, params){
				//console.log('execute dp init for ' + input_id);
				
				//check if the id of this datepicker has been changed
				jQuery('*[oldId="' + input_id + '"]').each(function(){
					input_id = jQuery(this).attr('id');
					jQuery(this).removeAttr('oldId');
				});
				
				ASFMultiInputFieldHandler.getContainerOfInput(
					jQuery('#' + input_id)).parent().get(0).datePickerParams = params;
				ASFMultiInputFieldHandler.datePickerFormat = params.dateFormat;
				
				temp(input_id, params);
			}
			window.SFI_DP_init.hijacked = true;
		}
	},
		
	overwriteDateTimePickerInitMethod : function(){
		if(typeof SFI_DTP_init != 'undefined' && SFI_DTP_init.hijacked != true){
			//console.log('overwrite dtp init method');
			var temp = window.SFI_DTP_init;
			window.SFI_DTP_init = function(input_id, params){
				
				//console.log('execute dtp init for ' + input_id);
				
				//check if the id of this datepicker has been changed
				jQuery('*[oldId="' + input_id + '"]').each(function(){
					input_id = jQuery(this).attr('id');
					jQuery(this).removeAttr('oldId');
				});
				
				//updatesubitem ids
				var currentIdPrefix = input_id.substr(0, input_id.indexOf('input'));
				var alreadyAddedPrefix = '';
				if(currentIdPrefix.length > 0){
					var subinputsInitData = new Object();
					for (var subinputId in params.subinputsInitData) {
						alreadyAddedPrefix = subinputId.substr(0, subinputId.indexOf('input'));
						subinputsInitData[currentIdPrefix + subinputId.substr(subinputId.indexOf('input'))] =
							params.subinputsInitData[subinputId];
					}
					params.subinputsInitData = subinputsInitData;
					
					currentIdPrefix = currentIdPrefix.substr(alreadyAddedPrefix.length);
					params.subinputs = params.subinputs.replace(/id="/g, 'id="' + currentIdPrefix);
				}
				
				//fix bug with prototype
				var tempParse = JSON.parse;
				JSON.parse = function(value){
					if(value != undefined){
						return tempParse(value);
					} else {
						return '';
					}
				}
				
				ASFMultiInputFieldHandler.getContainerOfInput(
						jQuery('#' + input_id)).parent().get(0).dateTimePickerParams = params;
				
				temp(input_id, params);
				
				JSON.parse = tempParse;
				
				//console.log('execute dtp init finished');
			}
			
			window.SFI_DTP_init.hijacked = true;
		}
	},
	
	
	overwriteTimePickerInitMethod : function(){
		if(typeof SFI_TP_init != 'undefined' && SFI_TP_init.hijacked != true){
			//console.log('overwrite tp init method');
			var temp = window.SFI_TP_init;
			window.SFI_TP_init = function(input_id, params){
				//console.log('execute tp init for ' + input_id);
				
				temp(input_id, params);
			}
			window.SFI_TP_init.hijacked = true;
		}
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
	
	doIdAndJSInit : function(node){
		
		//update ids
		jQuery('*[id]', node).each(function(){
			jQuery(this).attr('id', '-' + jQuery(this).attr('id'));
		});
		
		//deal with datepickers and datetimepickers
		if(jQuery(node).parent().get(0).dateTimePickerParams != undefined){
			
			jQuery('input[type="text"]', node).attr('name',
				jQuery('input[type="hidden"]', node).attr('name'));
			
			var oldId = jQuery('input[type="text"]', node).attr('id');
			oldId = oldId.substr(0, oldId.length - '_dp_show'.length);
			jQuery('input[type="text"]', node).attr('id', oldId);

			jQuery('.inputSpan *', node).each(function(){
				if(jQuery(this).attr('class').indexOf('hasDatepicker') == -1){
					jQuery(this).remove();
				}
			});
			jQuery('input:first-child', node).removeClass('hasDatepicker');
			
			SFI_DTP_init(
					jQuery('input[type="text"]', node).attr('id')
					, jQuery(node).parent().get(0).dateTimePickerParams);
		
		} else if(jQuery(node).parent().get(0).datePickerParams != undefined){
			
			jQuery('input[type="text"]', node).removeClass('hasDatepicker');
			jQuery('input[type="text"]', node).attr('name',
				jQuery('input[type="hidden"]', node).attr('name'));
			jQuery('input[type="hidden"]', node).remove();
			jQuery('button', node).remove();
			
			SFI_DP_init(
				jQuery('input[type="text"]', node).attr('id')
				, jQuery(node).parent().get(0).datePickerParams);
		}
		
		//deal with upload feature javascript
		jQuery('.sfUploadable', node).each(function(){
			jQuery(this).attr('data-input-id', '-' + jQuery(this).attr('data-input-id'));
			
			var newhref = jQuery(this).attr('href');
			newhref = 
				newhref.substr(0, newhref.indexOf('sfInputID=') + 'sfInputID='.length)
				+ '-' + newhref.substr(newhref.indexOf('sfInputID=') + 'sfInputID='.length);
			jQuery(this).attr('href', newhref);
		});
		
		jQuery(node).initializeJSElements();		
	},
	
	addAddButton : function(node){
		var addButton = '<img class="asf-addbutton" style="cursor: pointer" src="' + wgServer + wgScriptPath +
			'/extensions/AutomaticSemanticForms/skins/plus-act.gif" title="Add input field"></img>'; 
		jQuery(node).append(addButton);
		jQuery('.asf-addbutton:last-child', jQuery(node)).click(ASFMultiInputFieldHandler.addInputField);
	},
	
	addDeleteButton : function(node){
		var deleteButton = '<img class="asf-deletebutton" style="cursor: pointer" src="' + wgServer + wgScriptPath +
			'/extensions/AutomaticSemanticForms/skins/minus-act.gif" title="Remove input field"></img>'; 
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
				//and the last input field can be removed

				//check if this node has a section with visible inherited property values
				if(jQuery('p', jQuery(node).parent().parent().next()).get().length == 0){
				
					//we do not show uneditable input fields and
					//the complete row can be removed
					jQuery(parent).parent().parent().remove();
	
					if(jQuery('.asf-unresolved-section tr').get().length == 1){
						jQuery('.asf-unresolved-section').css('display', 'none');
					}
				} else {
					jQuery(node).parent().remove();
				}
			} else {
				//we are not in unresolved annotations section and 
				//instead of deleting this input field we have to set it
				//to an empty value
				var type = this.getInputFieldType(jQuery(node).parent().parent());
				this.setInputFieldValue(jQuery(node).parent(), type, '');
				jQuery(parent).removeClass('asf-syncronized-value');
				
				//also remove rel-index properties and so on
				jQuery(node).parent().removeAttr('asf-wp-rel-index');
				jQuery(node).parent().removeAttr('asf-wp-temp-rel-index');
				jQuery(node).parent().removeAttr('asf-wp-oldstartpos');
				jQuery(node).parent().removeAttr('asf-wp-oldlabel');
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
		var newNode = ASFMultiInputFieldHandler.doAddInputField(this);
		
		//this input field has been added by pressing the add button
		//and thus is not syncronized to the free text input field
		jQuery(newNode).removeClass('asf-syncronized-value');
	},
	
	doAddInputField : function(node){
		//copy node
		var parent = jQuery(node).parent();
		var newNode = jQuery(parent).clone();
		
		//set name
		ASFMultiInputFieldHandler.setInputFieldName(
				newNode,
				jQuery('.asf-multi_value', jQuery(parent).parent()).length + 1);
		
		//add new input field
		jQuery(parent).parent().append(newNode);
		
		var newInputField = jQuery('.asf-multi_value:last-child', jQuery(parent).parent()).get(0);
		
		//deal with delete buttons
		jQuery('.asf-deletebutton', jQuery(parent).parent()).remove();
		jQuery('.asf-multi_value', jQuery(parent).parent()).each(function(){
			ASFMultiInputFieldHandler.addDeleteButton(this);
		});
		
		//deal with add buttons, one has to be added to the new input field
		//if the property does not have a max cardinality of one, i.e. if there 
		//already was an add button shown
		var addAddButton = false;
		if(jQuery('.asf-addbutton', jQuery(parent).parent()).get().length > 0){
			addAddButton = true;
		}
		jQuery('.asf-addbutton', jQuery(parent).parent()).remove();
		if(addAddButton){
			jQuery(newInputField).each(function(){
				ASFMultiInputFieldHandler.addAddButton(this);
			});
		}
		
		//reset value
		var type = this.getInputFieldType(jQuery(newInputField).parent());
		this.setInputFieldValue(newInputField, type, '');
		
		//remove all old attributes
		jQuery(newInputField).removeAttr('asf-wp-rel-index');
		jQuery(newInputField).removeAttr('asf-wp-temp-rel-index');
		jQuery(newInputField).removeAttr('asf-wp-oldstartpos');
		jQuery(newInputField).removeAttr('asf-wp-oldlabel');

		ASFMultiInputFieldHandler.doIdAndJSInit(newInputField);
		
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
		var value = jQuery.trim(this.getInputFieldValue(container, type ));
		
		if(value == undefined) value = '';
		
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
		
		if(type == 'radiobutton'){
			if(value.length > 0){
				return true;
			}
		}
		
		if(type == 'date'){
			if(value.length > 0){
				return true;
			}
		}
		
		if(type == 'datetime'){
			if(value.length > 0){
				return true;
			}
		}
		
		if(type == 'datepicker'){
			if(value.length > 0){
				return true;
			}
		}
		
		if(type == 'datetimepicker'){
			if(value.length > 0){
				return true;
			}
		}
		
		return false;
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
		
		if(type == 'radiobutton'){
			value = jQuery('input:radio:checked', container).val();
			if(value == undefined){
				return '';
			} else {
				return value;
			}
		}
		
		if(type == 'date'){
			var day = jQuery.trim(jQuery('.dayInput', container).val());
			var month = jQuery.trim(jQuery('.monthInput', container).val());
			var year = jQuery.trim(jQuery('.yearInput', container).val());
			
			if(day == '') return ''; //date has not yet been set
			
			var date = new Date(year*1, month*1-1, day*1+1, 0, 0, 0);
			var dateString = date.toGMTString();
			dateString = dateString.substr(dateString.indexOf(',') + 1);
			dateString = jQuery.trim(dateString.substr(0, dateString.indexOf(':') - 2));
			return dateString;
		}
		
		
		if(type == 'date'){
			var day = jQuery.trim(jQuery('.dayInput', container).val());
			var month = jQuery.trim(jQuery('.monthInput', container).val());
			var year = jQuery.trim(jQuery('.yearInput', container).val());
			
			if(day == '') return ''; //date has not yet been set
			
			var date = new Date(year*1, month*1-1, day*1+1, 0, 0, 0);
			var dateString = date.toGMTString();
			dateString = dateString.substr(dateString.indexOf(',') + 1);
			dateString = jQuery.trim(dateString.substr(0, dateString.indexOf(':') - 2));
			return dateString;
		}
		
		if(type == 'datetime'){
			var day = jQuery.trim(jQuery('.dayInput', container).val());
			var month = jQuery.trim(jQuery('.monthInput', container).val());
			var year = jQuery.trim(jQuery('.yearInput', container).val());
			
			if(day == '') return ''; //date has not yet been set

			var yearinput = jQuery('.yearInput', container).next();
			var hours = jQuery.trim(jQuery(yearinput).next().val())
			var minutes = jQuery.trim(jQuery(yearinput).next().next().val())
			var seconds = jQuery.trim(jQuery(yearinput).next().next().next().val())
			var ampm24h = jQuery.trim(jQuery(yearinput).next().next().next().next().val())
			
			if(ampm24h == "PM") hours = hours*1 + 12;
			
			var date = new Date(year*1, month*1-1, day*1+1, hours*1, minutes*1, seconds*1);
			var dateString = date.toGMTString();
			dateString = dateString.substr(dateString.indexOf(',') + 1);
			//dateString = jQuery.trim(dateString.substr(0, dateString.indexOf(':') - 2));
			return dateString;
		}
		
		if(type == 'datepicker'){
			var date = jQuery.trim(jQuery('input[type="hidden"]', container).val());
			if(date.length == 0){
				date = jQuery.trim(jQuery('input[type="text"]', container).val());
			} else {
				date = ASFMultiInputFieldHandler.parseDateString(date);
				if(date.toDateString() != 'Invalid Date'){
					var day = date.getDate();
					var month = date.getMonth()*1 + 1;
					var year = date.getFullYear();
					var date = day + '/' + month + '/' + year;
					
					date = jQuery.datepicker.formatDate(
						ASFMultiInputFieldHandler.datePickerFormat,
						jQuery.datepicker.parseDate( "dd/mm/yy", date, null), null);
				}
			}
			return date;
		}
		
		if(type == 'datetimepicker'){
			var datetime = jQuery.trim(jQuery('input[type="hidden"]', container).val());
			if(datetime.length == 0){
				date = jQuery.trim(jQuery('input:nth-child(1)', container).val());
				time = jQuery.trim(jQuery('input:nth-child(4)', container).val());
				datetime = date + ' ' + time;
			} else {
				date = ASFMultiInputFieldHandler.parseDateString(datetime);
				if(date.toDateString() != 'Invalid Date'){
					var day = date.getDate();
					var month = date.getMonth()*1 + 1;
					var year = date.getFullYear();
					var dateString = day + '/' + month + '/' + year;
					
					dateString = jQuery.datepicker.formatDate(
							ASFMultiInputFieldHandler.datePickerFormat,
							jQuery.datepicker.parseDate( "dd/mm/yy", dateString, null), null);
					
					var hours = ''+date.getHours();
					if(hours.length == 1) hours = '0'+hours;
					var minutes = ''+(date.getMinutes()*1);
					if(minutes.length == 1) minutes = '0'+minutes;
					var timeString = hours + ':' + minutes;
					datetime = dateString + ' ' + timeString;
				}
			}
			return datetime;
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
		
		if(type == 'radiobutton'){
			value = jQuery.trim(value);
			jQuery('input:[value="' + value + '"]', container).attr('checked', 'checked');
			return true;
		}
		
		if(type == 'date'){
			
			value = jQuery.trim(value);
			
			var day = '';
			var month = 1;
			var year = '';
			if(value.length > 0){
				
				date = this.parseDateString(value);
				
				if(date.toDateString() != 'Invalid Date'){
					day = date.getDate();
					month = date.getMonth()*1 + 1;
					year = date.getFullYear();
				}
			}
			
			jQuery('.dayInput', container).val(day);
			jQuery('.yearInput', container).val(year);
			jQuery('.monthInput', container).val(
					jQuery('.monthInput option:nth-child(' + month + ')').val());
			
			return true;
		}
		
		
		if(type == 'datetime'){
			
			value = jQuery.trim(value);
			
			var day = '';
			var month = 1;
			var year = '';
			var hours = '';
			var minutes = '';
			var seconds = '';
			
			if(value.length > 0){
				date = this.parseDateString(value);
				
				if(date.toDateString() != 'Invalid Date'){
					day = date.getDate();
					month = date.getMonth()*1 + 1;
					year = date.getFullYear();
					hours = date.getHours();
					minutes = date.getMinutes();
					seconds = date.getSeconds();
				}
			}
			
			jQuery('.dayInput', container).val(day);
			jQuery('.yearInput', container).val(year);
			jQuery('.monthInput', container).val(
					jQuery('.monthInput option:nth-child(' + month + ')').val());
			jQuery('.yearInput', container).next().val(hours);
			jQuery('.yearInput', container).next().next().val(minutes);
			jQuery('.yearInput', container).next().next().next().val(seconds);
			
			return true;
		}

		
		if(type == 'datepicker'){
			value = jQuery.trim(value);
			
			var newDateString = '';
		
			if(value.length > 0){
				date = this.parseDateString(value);
			
				if(date.toDateString() != 'Invalid Date'){
					var day = date.getDate();
					var month = date.getMonth()*1 + 1;
					var year = date.getFullYear();
					newDateString = day + '/' + month + '/' + year;
					
					newDateString = jQuery.datepicker.formatDate(
							ASFMultiInputFieldHandler.datePickerFormat,
							jQuery.datepicker.parseDate( "dd/mm/yy", newDateString, null), null);
				}
			}

			jQuery('input', container).val(newDateString);
			return true;
		}
		
		if(type == 'datetimepicker'){
			value = jQuery.trim(value);
			
			var newDateString = '';
		
			if(value.length > 0){
				date = this.parseDateString(value);
			
				if(date.toDateString() != 'Invalid Date'){
					var day = date.getDate();
					var month = date.getMonth()*1 + 1;
					var year = date.getFullYear();
					var newDateString = day + '/' + month + '/' + year;
					
					newDateString = jQuery.datepicker.formatDate(
							ASFMultiInputFieldHandler.datePickerFormat,
							jQuery.datepicker.parseDate( "dd/mm/yy", newDateString, null), null);
					
					var hours = ''+date.getHours();
					if(hours.length == 1) hours = '0'+hours;
					var minutes = ''+(date.getMinutes()*1);
					if(minutes.length == 1) minutes = '0'+minutes;
					var newTimeString = hours + ':' + minutes;
				}
			}

			jQuery('input:nth-child(1)', container).val(newDateString);
			jQuery('input:nth-child(4)', container).val(newTimeString);
			jQuery('input[type="hidden"]', container).val(newDateString + " " + newTimeString);
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
		var classes = jQuery(node).attr('class');
		if(classes != undefined && classes.indexOf('asf-multi_value') >= 0){
			return node;
		} else {
			var parent = jQuery(node).parent();
			return ASFMultiInputFieldHandler.getContainerOfInput(parent);
		}
	},
	

	areValuesEqual : function(formValue, freeTextValue, type){
		formValue = this.normalizeValue(formValue, type);
		freeTextValue = this.normalizeValue(freeTextValue, type);
		//console.log('compare values' + formValue + ' ' + freeTextValue);
		
		if(formValue == freeTextValue){
			//console.log('compare result: equal');
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
			
			if(value != 'true' && value != 'yes' && value != '1'
					&& value != 'y' && value != 't'){
				value = 'false';
			} else {
				value = 'true';
			}
		}
		
		if(type == 'radiobutton'){
			value = jQuery.trim(value);
		}
		
		if(type == 'date'){
			value = jQuery.trim(value);
			date = this.parseDateString(value);
			return date.toGMTString();
		}
		
		if(type == 'datetime'){
			value = jQuery.trim(value);
			date = this.parseDateString(value);
			return date.toGMTString();
		}
		
		if(type == 'datepicker'){
			value = jQuery.trim(value);
			date = this.parseDateString(value);
			return date.toGMTString();
		}
		
		if(type == 'datetimepicker'){
			value = jQuery.trim(value);
			date = this.parseDateString(value);
			return date.toGMTString();
		}
		
		return value;
	},
	
	parseDateString : function(dateString){
		
		//first check if we already have cached this date
		var cachedValue = false;
		for(var i= 0; i < this.cachedDateStringLookups.length; i++){
				
			//console.log(i + ' ' + this.cachedDateStringLookups[i].dateString + 
			//		' ' + this.cachedDateStringLookups[i].date);
				
			if(this.cachedDateStringLookups[i].dateString == dateString){
				cachedValue = this.cachedDateStringLookups[i].date
				break;
			}
		}
			
		if(cachedValue != false){
			ASFMultiInputFieldHandler .lookedupDate = cachedValue;
			//console.log('cached value');
		} else {
			var url = wgServer + wgScriptPath + "/index.php";
			jQuery.ajax({ url:  url, 
				async: false,
				data: {
					'action' : 'ajax',
					'rs' : 'asff_convertDateString',
					'rsargs[]' : [dateString]
				},
				success: function(data){
					data = data.substr(data.indexOf('--##starttf##--') + 15, data.indexOf('--##endtf##--') - data.indexOf('--##starttf##--') - 15); 
					data = jQuery.parseJSON(data);
						
					ASFMultiInputFieldHandler .lookedupDate = data.date;
						
					var cachedDate = new Object();
					cachedDate.dateString = dateString;
					cachedDate.date = data.date;
					ASFMultiInputFieldHandler.cachedDateStringLookups.push(cachedDate);
				}
			});
		}

		//console.log('retrieved date: ' + ASFMultiInputFieldHandler .lookedupDate);
			
		var date = new Date(Date.parse(
			ASFMultiInputFieldHandler .lookedupDate));
		
		return date;
	},
	
	dealWithMandatoryInputFields : function(){
		//delete all empty input fields except for the last in order to avoid
		//invalid behaviour of the mandatory field feature
		jQuery('.asf-multi_value').each(function(){
			if(!ASFMultiInputFieldHandler.isInputFieldValueSet(this)){
				ASFMultiInputFieldHandler.doDeleteInputField(jQuery('> *:first-child', this), false);
			}
		});
	}
};




jQuery(document).ready( function($) {
	initializeNiceASFTooltips();
	asf_hideFreeText();	
	asf_makeReadOnly();
	if(window.inASFMode){
		ASFMultiInputFieldHandler.init();
		ASFFormSyncer.init();
	}	
	
	window.asf_hide_category_section = asf_hide_category_section;
	window.asf_show_category_section = asf_show_category_section;
	window.asf_hit_category_section = asf_hit_category_section;
	window.initializeNiceASFTooltips = initializeNiceASFTooltips;
});

window.onload = asf_initializeCollapsableSectionsTabIndexes;

