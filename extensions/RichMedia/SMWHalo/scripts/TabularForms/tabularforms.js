var TF = Class.create({

	init: function(){ 
	},
	
	/*
	 * Loads all Tabular Forms included in the page
	 */
	loadForms : function(){
		jQuery('.tabf_container').each( function (){
			tf.loadForm(this);
		});
	},
	
	/*
	 * Loads a particular Tabular Form via an Ajax call
	 * 
	 * Method is called on page load and when someone
	 * presses the refresh button of a tabular form.
	 */
	loadForm : function(container){
		jQuery('.tabf_table_container', container).css('display', 'none');
		jQuery('.tabf_loader', container).css('display', 'table', container);
		
		var querySerialization = jQuery('.tabf_query_serialization', container).html();
		var tabularFormId = jQuery(container).attr('id');
		var isSPARQL = jQuery('.tabf_query_serialization', container).attr('isSPARQL');
		
		var url = wgServer + wgScriptPath + "/index.php";
		jQuery.ajax({ url:  url, 
			data: {
				'action' : 'ajax',
				'rs' : 'tff_getTabularForm',
				'rsargs[]' : [querySerialization, isSPARQL, tabularFormId],
			},
			success: tf.displayLoadedForm,
			
		});
	},
	
	/*
	 * Callback function for Ajax call that loads a tabular form
	 */
	displayLoadedForm : function(data){
		
		data = data.substr(data.indexOf('--##starttf##--') + 15, data.indexOf('--##endtf##--') - data.indexOf('--##starttf##--') - 15); 
		data = JSON.parse(data);
		
		jQuery('#' + data.tabularFormId + ' .tabf_loader').css('display', 'none');
		jQuery('#' + data.tabularFormId + ' .tabf_table_container').html(data.result);
		jQuery('#' + data.tabularFormId + ' .tabf_table_container').attr('usesat', data.useSAT);
		
		jQuery('#' + data.tabularFormId + ' .tabf_table_container').css('display', 'block');
		
		jQuery('#' + data.tabularFormId + ' .tabf_table_container td textarea').each(tf.initializeLoadedCell)
		
		jQuery('#' + data.tabularFormId + ' tr td:first-child').each(tf.initializeDeleteButtons);
		
		if(jQuery('#' + data.tabularFormId + ' .tabf_new_row').get().length > 0){
			jQuery('#' + data.tabularFormId + ' .tabf_save_button').removeAttr('disabled');
		}
		
		//todo: validate subject title textarea values 
		
		//store html forr cancel feature
		data.result = jQuery('#' + data.tabularFormId + ' .tabf_table_container').html();
		data.result = data.result.replace(/</g, '##--lt--##');
		data.result = data.result.replace(/>/g, '##--gt--##');
		jQuery('#' + data.tabularFormId + ' .tabf_table_container_cache').html(data.result);
		
		//display edit mode again if this was the previous mode
		if(jQuery('#' + data.tabularFormId).attr('isInEditMode') == 'true'){
			tf.switchToEditMode(data.tabularFormId);
		}
		
		var container = jQuery('#' + data.tabularFormId);
		var lostInstances = jQuery(container).get(0).lostInstances;  
		if(lostInstances != undefined){
			for(var k=0; k < lostInstances.length; k++){
				tf.addNotification(container, 'tabf_lost_instance_warning', i + '-' + k, lostInstances[k], lostInstances[k]);
			}
		}

		//xyz
		var instancesWithSaveErrors = jQuery(container).get(0).instancesWithSaveErrors;  
		if(instancesWithSaveErrors != undefined){
			jQuery('tr td:firest-child a', container).each( function(){
				for(var k=0; k < instancesWithSaveErrors.length; k++){
					if(jQuery(this).html() == instancesWithSaveErrors[k]){
						jQuery(this).parent().parent().addClass('tabf_table_row_saved_error');
						jQuery('.tabf_error_status', jQuery(this).parent().parent()).css('display', 'inline');
					}
				}
			});
			
			for(var k=0; k < instancesWithSaveErrors.length; k++){
				tf.addNotification(container, 'tabf_save_error_warning', instancesWithSaveErrors[k], 
					instancesWithSaveErrors[k], instancesWithSaveErrors[k]);
			}
			
		} else {
			jQuery(container).get(0).instancesWithSaveErrors = new Array();
		}
		
	},
	
	
	cancelFormEdit : function(tabularFormId){
		var cachedTF = jQuery('#' + tabularFormId + ' .tabf_table_container_cache').html();
		cachedTF = cachedTF.replace(/##--lt--##/g, '<');
		cachedTF = cachedTF.replace(/##--gt--##/g, '>');
		
		jQuery('#' + tabularFormId + ' .tabf_table_container').html(cachedTF);
		
		jQuery('#' + tabularFormId).attr('isInEditMode' , 'false');
		
		//tf.switchToViewMode(tabularFormId);
	},
	
	/*
	 * This method is called after a tabular form has been loaded.
	 * 
	 * It adds required event listeners to all input foelds for modifying 
	 * instance data.
	 */
	initializeLoadedCell : function(){
		jQuery(this).attr('originalValue', jQuery(this).attr('value'));
		jQuery(this).attr('currentValue', jQuery(this).attr('value'));
			
		jQuery(this).change(tf.cellChangeHandler);
		jQuery(this).keyup(tf.cellKeyUpHandler);
		jQuery(this).keydown(tf.cellKeyDownHandler);
		jQuery(this).focus(tf.setAsCurrentlySelectedCellValue);
		jQuery(this).blur(tf.unSetAsCurrentlySelectedCellValue);
		
		if(jQuery(this).parent().parent().attr('isNew') == 'true'){
			tf.checkAnnotationValue(this);
		}
				
	},
	
	/*
	 * This method is called after a tabular form has been loaded.
	 * 
	 * It adds a listener for deleting instances to instance titles, i.e.
	 * the cell of the first column in each row.
	 */
	initializeDeleteButtons : function(){
		jQuery(this).mouseover(tf.displayDeleteButton);
		jQuery(this).mouseout(tf.hideDeleteButton);
	},
	
	/*
	 * listener for checking if cell value has been modified.
	 * 
	 * Required for paste values with mouse.
	 */
	cellChangeHandler : function(){
		tf.cellValueChangeHandler(this);
	},
	
	/*
	 * Key up listener, which is required for detecting
	 * - value changes
	 * - navigation
	 * - article name changes (detecting valid article names)
	 */
	cellKeyUpHandler : function(event){
		
		if(event.which == '16'){
			tf.shiftKeyPressed = false;
		}
		
		tf.navigateCells(this, event.which);
		
		tf.cellValueChangeHandler(this);
		
		//do checks only if values really have changed
		if(jQuery(this).attr('currentValue') != jQuery(this).attr('value')){
			jQuery(this).attr('currentValue', jQuery(this).attr('value'));
			
			tf.checkNewInstanceName(this, event.which);
				
			tf.checkAnnotationValue(this, event.which);
		}
	},
	
	/*
	 * Key down listener which is required for detecting navigation
	 * between cells
	 */
	cellKeyDownHandler : function(event){
		tf.selectionStartPosition = jQuery(this).attr('selectionStart');
		tf.selectionEndPosition = jQuery(this).attr('selectionEnd');
		
		if(event.keyCode == '16') {//shift
			tf.shiftKeyPressed = true;
		} else if(event.keyCode == '9') {//tab
			event.preventDefault();
			if(tf.shiftKeyPressed){
				tf.navigateCells(this, 'shift+tab');
			} else {
				tf.navigateCells(this, 'tab');
			}
		}
	},
	
	/*
	 * This method is called by the key listeners and
	 * implements the navigation between table cells
	 */
	navigateCells : function(cell, keyCode){
		
		if(keyCode == 'tab'){ //key right keyCode == '39' || 
			
			if(keyCode != 'tab' && tf.selectionEndPosition < jQuery(cell).attr('value').length){
				return;
			}
			
			var column = tf.getNextColumn(jQuery(cell).parent(), true);
			
			var cellNumber = tf.getChildNumber(cell, 1);
			var maxCellNumber = jQuery('textarea', column).get().length;
			if(maxCellNumber < cellNumber){
				cellNumber = maxCellNumber;
			}
			
			var newCell = jQuery('textarea', column).get(cellNumber-1);
			jQuery(newCell).focus();
			jQuery(newCell).select();
			
			jQuery(cell).removeClass('tabf_selected_value');
			jQuery(newCell).addClass('tabf_selected_value');
		
		} else if(keyCode == 'shift+tab'){ 
			
			var column = tf.getPrevColumn(jQuery(cell).parent(), true);
			
			var maxCellNumber = jQuery('textarea', column).get().length;
			var cellNumber = tf.getChildNumber(cell, 1);
			
			if(maxCellNumber < cellNumber){
				cellNumber = maxCellNumber;
			}
			
			var newCell = jQuery('textarea', column).get(cellNumber-1);
			jQuery(newCell).focus();
			jQuery(newCell).select();
			
			jQuery(cell).removeClass('tabf_selected_value');
			jQuery(newCell).addClass('tabf_selected_value');
						
		} else if(keyCode == '40'){ //key down
			
			if (autoCompleter.siw != null && autoCompleter.siw.floater.style.display && (autoCompleter.siw.floater.style.display != "none")) {
				//do not navigate, if user is currently in autocompletion-mode
				return;
			}
			
			if(!tf.isLastRow(cell)){
				return;
			}
			
			var column = jQuery(cell).parent();
			
			var maxCellNumber = jQuery('textarea', column).get().length;
			var cellNumber = tf.getChildNumber(cell, 1) + 1;
			if(maxCellNumber < cellNumber){
				cellNumber = 1;
				var columnNumber = tf.getChildNumber(column, 1);
				column = tf.getColumnBeyond(column, columnNumber);
				
			}
			
			if(column != null){
				var newCell = jQuery('textarea', column).get(cellNumber-1);
				jQuery(newCell).focus();
				jQuery(newCell).select();
				
				jQuery(cell).removeClass('tabf_selected_value');
				jQuery(newCell).addClass('tabf_selected_value');
			}
		} else if(keyCode == '38'){ //key up
			
			if (autoCompleter.siw != null && autoCompleter.siw.floater.style.display && (autoCompleter.siw.floater.style.display != "none")) {
				//do not navigate, if user is currently in autocompletion-mode
				return;
			}
			
			if(!tf.isFirstRow(cell)){
				return;
			}
			
			var column = jQuery(cell).parent();
			
			var cellNumber = tf.getChildNumber(cell, 1) - 1;
			if(cellNumber == 0){
				var columnNumber = tf.getChildNumber(column, 1);
				column = tf.getColumnAbove(column, columnNumber);
				
				if(column != null){
					cellNumber = jQuery('textarea', column).get().length;
				}
			}
			
			if(column != null){
				var newCell = jQuery('textarea', column).get(cellNumber-1);
				jQuery(newCell).focus();
				jQuery(newCell).select();
				
				jQuery(cell).removeClass('tabf_selected_value');
				jQuery(newCell).addClass('tabf_selected_value');
			}
		}
	},
	
	/*
	 * Returns the number of a node from the 
	 * perspective of its parent.
	 */
	getChildNumber : function(node, nr, tagName){
		if(!tagName){
			tagName = jQuery(node).get(0).tagName;
		}
		
		var newNode = jQuery(node).prev();
		if(jQuery(newNode).html() == null){
			return nr;
		} else {
			if(jQuery(newNode).get(0).tagName == tagName){
				nr += 1;
			}
			return tf.getChildNumber(newNode, nr, tagName);
		}
	},
	
	/*
	 * Get target of navigation right
	 */
	getNextColumn : function(column, firstCall ){
		var nextColumn = jQuery(column).next();
		if(jQuery(nextColumn).html() == null
				|| jQuery(nextColumn).parent().attr('isDeleted') == 'true'){
			var row = jQuery(column).parent().next()
			if(jQuery('td', row).get().length == 0){
				return null
			} else {
				nextColumn = jQuery('td:first-child', row);
			}
		}
		if(jQuery('textarea:first-cild', nextColumn).html() == null){
			var nextColumn = tf.getNextColumn(nextColumn, false);
			if(nextColumn == null && firstCall){
				return column;
			} else {
				return nextColumn;
			}
		} else {
			return nextColumn;
		}
	},
	
	/*
	 * Get target of navigation left
	 */
	getPrevColumn : function(column, firstCall ){
		var nextColumn = jQuery(column).prev();
		if(jQuery(nextColumn).html() == null
				|| jQuery(nextColumn).parent().attr('isDeleted') == 'true'){
			var row = jQuery(column).parent().prev()
			if(jQuery('td', row).get().length == 0){
				return null
			} else {
				nextColumn = jQuery('td:last-child', row);
			}
		}
		if(jQuery('textarea:first-cild', nextColumn).html() == null){
			var nextColumn = tf.getPrevColumn(nextColumn, false);
			if(nextColumn == null && firstCall){
				return column;
			} else {
				return nextColumn;
			}
		} else {
			return nextColumn;
		}
	},

	/*
	 * Get target of navigation down
	 */
	getColumnBeyond : function(column, columnNumber){
		var row = jQuery(column).parent().next();
		if(jQuery(row).html() == null){
			return null;
		}
		
		column = jQuery('td:nth-child(' + columnNumber + ')', row);
		if(jQuery(column).html() == null){
			return null;
		}
		
		var children = jQuery('textarea', column).get();
		if(children.length == 0 || jQuery(children[0]).html() == null
				|| jQuery(row).attr('isDeleted') == 'true'){
			return tf.getColumnBeyond(column, columnNumber);
		} else {
			return column;
		}
	},
	
	/*
	 * Get target of navigation up
	 */
	getColumnAbove : function(column, columnNumber){
		var row = jQuery(column).parent().prev();
		if(jQuery(row).html() == null){
			return null;
		}
		
		column = jQuery('td:nth-child(' + columnNumber + ')', row);
		if(jQuery(column).html() == null){
			return null;
		}
		
		var children = jQuery('textarea', column).get();
		if(children.length == 0 || jQuery(children[children.length - 1]).html() == null
				|| jQuery(row).attr('isDeleted') == 'true'){
			return tf.getColumnAbove(column, columnNumber);
		} else {
			return column;
		}
	},
	
	/*
	 * Detect if a cell value has been changed
	 */
	cellValueChangeHandler : function(node){
		
		var parentRow = jQuery(node).parent().parent();
		
		if(jQuery(node).attr('class').indexOf('tabf_erronious_instance_name') > -1
				|| jQuery(node).attr('class').indexOf('tabf_valid_instance_name') > -1){
			return;
		}
		
		if(jQuery(node).parent().parent().attr('class').indexOf('tabf_table_row_saved_error') > -1){
			jQuery(node).parent().parent().removeClass('tabf_table_row_saved_error');
			jQuery('.tabf_error_status', jQuery(node).parent().parent()).css('display', 'none');
		}
		
		if(jQuery(node).attr('originalValue') != jQuery.trim(jQuery(node).attr('value'))){
			
			jQuery(node).addClass('tabf_modified_value');
			jQuery(node).attr('isModified', 'true');
		
			if(jQuery(parentRow).attr('class').indexOf('tabf_new_row') > -1){
				return;
			}
			
			jQuery(parentRow).attr('isModified', 'true');
			jQuery(parentRow).addClass('tabf_modified_row');
			
			//jQuery('td:last-child .tabf_ok_status', parentRow).css('display', 'none');
			jQuery('td:last-child .tabf_modified_status', parentRow).css('display', 'inline');
			
			if(jQuery('.tabf_erronious_instance_name', jQuery(parentRow).parent().parent()).length == 0){
				jQuery('.tabf_save_button', jQuery(parentRow).parent().parent()).removeAttr('disabled');
			}
			
		} else {
			jQuery(node).removeClass('tabf_modified_value');
			jQuery(node).attr('isModified', false);
			
			if(jQuery(parentRow).attr('class').indexOf('tabf_new_row') > -1){
				return;
			}
			
			if(jQuery('.tabf_modified_value', parentRow).length == 0){
				parentRow.attr('isModified', false);
				parentRow.removeClass('tabf_modified_row');
				
				//jQuery('td:last-child .tabf_ok_status', parentRow).css('display', 'inline');
				jQuery('td:last-child .tabf_modified_status', parentRow).css('display', 'none');
				
				if(jQuery('.tabf_modified_row', jQuery(parentRow).parent().parent()).length == 0
						&& jQuery('.tabf_deleted_row', jQuery(parentRow).parent().parent()).length == 0
						&& jQuery('.tabf_new_row .tabf_valid_instance_name', jQuery(parentRow).parent().parent()).length == 0){
					jQuery('.tabf_save_button', jQuery(parentRow).parent().parent()).attr('disabled', 'disabled');
				}
			}
		}
	},
	
	/*
	 * Called if refresh button has been pressed.
	 * Reloads the Tabular Form.
	 */
	refreshForm : function(containerId){
		var container = jQuery('#' + containerId);
		tf.loadForm(container);
	},
	
	/*
	 * Called if Save button has been pressed.
	 */
	saveFormData : function(event, containerId){
		
		tf.updateJobs = 0;
		tf.updateErrors = 0;
		
		var container = jQuery('#' + containerId);
	
		jQuery(Event.element(event)).css('display', 'none');
		jQuery('.tabf_add_button', jQuery(Event.element(event)).parent()).css('display', 'none');
		
		jQuery('.tabf_table_container tr', container).each( tf.saveFormRowData);
	},
	
	/*
	 * Called by method saveFormData. Saves, adds or deletes 
	 * each row, respectively instance.
	 */
	saveFormRowData : function(rowNr){
		jQuery(this).addClass('tabf_table_row_saved');
		jQuery('textarea', this).attr('readonly', 'true');
		
		var tabularFormId = jQuery(this).parent().parent().parent().parent().attr('id');
		
		if(jQuery(this).attr('isDeleted') == 'true'){
			jQuery('td:last-child .tabf_deleted_status', this).css('display', 'none');
			jQuery('td:last-child .tabf_pending_status', this).css('display', 'inline');
			
			var revisionId = jQuery('td:first-child ',this).attr('revision-id');
			var articleTitle = jQuery('td:first-child ',this).attr('article-name');
			
			
			tf.updateJobs += 1;
			
			var url = wgServer + wgScriptPath + "/index.php";
			jQuery.ajax({ url:  url, 
				data: {
					'action' : 'ajax',
					'rs' : 'tff_deleteInstance',
					'rsargs[]' : [articleTitle, revisionId, rowNr, tabularFormId],
				},
				success: tf.saveFormRowDataCallback,
				
			});
			
		} else if(jQuery(this).attr('isModified') == 'true' || jQuery(this).attr('isNew') == 'true' ){
			
			jQuery('td:last-child .tabf_modified_status', this).css('display', 'none');
			jQuery('td:last-child .tabf_added_status', this).css('display', 'none');
			jQuery('td:last-child .tabf_pending_status', this).css('display', 'inline');
			
			var modifiedValues = new Array();
			
			var fields = jQuery('td', this).get();
			for(var fieldNr=0; fieldNr < fields.length; fieldNr++){
				
				var fieldValues = jQuery('textarea', jQuery(fields[fieldNr])).get();
				for(var i=0; i < fieldValues.length; i++){
					if(jQuery(fieldValues[i]).attr('isModified') == 'true'
							|| (jQuery(fieldValues[i]).attr('value').length > 0 && jQuery(this).attr('isNew') == 'true')){
						
						jQuery(fieldValues[i]).get(0).setAttribute('wrap', 'pgysical');
												
						var modifiedValue = new Object();
						modifiedValue['newValue'] = jQuery(fieldValues[i]).attr('value');
						modifiedValue['originalValue'] = jQuery(fieldValues[i]).attr('originalValue');
						
						modifiedValue['address'] = jQuery('th:nth-child(' + (fieldNr + 1) + ')'
									, jQuery(this).parent()).attr('field-address');
						modifiedValue['isTemplateParam'] = jQuery('th:nth-child(' + (fieldNr + 1) + ')'
								, jQuery(this).parent()).attr('is-template');
						modifiedValue['templateId'] = jQuery(fieldValues[i]).attr('template-id');
						modifiedValue['hash'] = jQuery(fieldValues[i]).attr('annotation-hash');
						modifiedValue['typeId'] = jQuery(fieldValues[i]).attr('annotation-type-id');
						
						modifiedValues.push(modifiedValue);						
					}
				}
			}
			
			//this is uglay
			var tabularFormId = jQuery(this).parent().parent().parent().parent().attr('id');
			
			//deal with hidden preload values
			if(jQuery(this).attr('isNew') == 'true'){
				jQuery('#tf-hidden-preload-values > div', jQuery('#' + tabularFormId)).each( function(){
					var modifiedValue = new Object();
					modifiedValue['newValue'] = jQuery(this).html();
					modifiedValue['originalValue'] = '';
					modifiedValue['address'] = jQuery(this).attr('annotationName');
					modifiedValues.push(modifiedValue);
				});
			}
			
			
			var revisionId = jQuery('td:first-child ',this).attr('revision-id');
			var useSAT = jQuery(this).parent().parent().parent().attr('usesat'); 
			if(revisionId == '-1'){
				var articleTitle = jQuery('td:first-child textarea',this).attr('value');
			} else {
				var articleTitle = jQuery('td:first-child ',this).attr('article-name');
			}
			
			tf.updateJobs += 1;
			
			var url = wgServer + wgScriptPath + "/index.php";
			jQuery.ajax({ url:  url, 
				data: {
					'action' : 'ajax',
					'rs' : 'tff_updateInstanceData',
					'rsargs[]' : [JSON.stringify(modifiedValues), articleTitle, revisionId, rowNr, tabularFormId, useSAT],
				},
				success: tf.saveFormRowDataCallback,
				
			});
		}
	},
	
	
	/*
	 * Callback for save, create or delete actions.
	 */
	saveFormRowDataCallback : function(data){
		data = data.substr(data.indexOf('--##starttf##--') + 15, data.indexOf('--##endtf##--') - data.indexOf('--##starttf##--') - 15); 
		data = JSON.parse(data);
		
		data.rowNr = (data.rowNr*1) + 1;
		
		var row = jQuery('#' + data.tabularFormId + ' tr:nth-child(' + data.rowNr + ')');
		if(data.success == true){
				// jQuery(row).addClass('tabf_table_row_saved_successfull');
				// jQuery('td:last-child .tabf_pending_status', row).css('display', 'none');
				// jQuery('td:last-child .tabf_saved_status', row).css('display', 'inline');
				// //replace article name input of new instance with textarea
				//			
				// if(jQuery('td:first-child textarea', row).attr('class') != null &&
				// jQuery('td:first-child textarea',
				// row).attr('class').indexOf('tabf_valid_instance_name') > -1){
				// var text = '<a href="' + wgServer + wgScriptPath + "/index.php" + "?title=";
				// text += encodeURI(jQuery('td:first-child textarea', row).attr('value'));
				// text += '">' + jQuery('td:first-child textarea', row).attr('value') + '</a>';
				// jQuery('td:first-child', row).html(text);
				// jQuery(row).addClass('tabf_table_row');
				// }
		} else {
			var container = jQuery('#' + data.tabularFormId);
			
			jQuery(container).get(0).instancesWithSaveErrors.push(data.title);
			
			// jQuery(row).addClass('tabf_table_row_saved_error');
			// jQuery('td:last-child .tabf_pending_status', row).css('display', 'none');
			// jQuery('td:last-child .tabf_error_status', row).attr('title', data.msg);
			// jQuery('td:last-child .tabf_error_status', row).css('display', 'inline');
			
			//tf.updateErrors += 1;
		}
		
		tf.updateJobs -= 1;
		if(tf.updateJobs == 0 && tf.updateErrors > 0){
			//alert(jQuery('.tabf_update_warning').html());
		}
		
		if(tf.updateJobs == 0){
			tf.searchForLostInstances(data.tabularFormId);
		}
	},
	
	/*
	 * Check which instances finally have been removed from the query result
	 */
	searchForLostInstances : function(containerId){
		
		var container = jQuery('#' + containerId);
		
		var instanceNames = '';
		
		var instances = jQuery('.tabf_table_row .tabf_table_cell:first-child a', container).get();		
		for(var i=0; i < instances.length; i++){
			var name = jQuery(instances[i]).html();;
			
			if(instanceNames.length > 0 && name.length > 0){
				instanceNames += ' || ';
			}
			instanceNames += name;
		}
		
		instances = jQuery('.tabf_new_row .tabf_table_cell:first-child textarea', container).get();
		for(var i=0; i < instances.length; i++){
			var name = jQuery(instances[i]).attr('value');
			
			if(instanceNames.length > 0 && name.length > 0){
				instanceNames += ' || ';
			}
			instanceNames += name;
		}
		
		var querySerialization = jQuery('.tabf_query_serialization', container).html();
		var isSPARQL = jQuery('.tabf_query_serialization', container).attr('isSPARQL');
		
		var url = wgServer + wgScriptPath + "/index.php";
		jQuery.ajax({ url:  url, 
			data: {
				'action' : 'ajax',
				'rs' : 'tff_getLostInstances',
				'rsargs[]' : [querySerialization, isSPARQL, containerId, instanceNames],
			},
			success: tf.searchForLostInstancesCallBack
			
		});
	},
	
	
	searchForLostInstancesCallBack : function(data){
		data = data.substr(data.indexOf('--##starttf##--') + 15, data.indexOf('--##endtf##--') - data.indexOf('--##starttf##--') - 15); 
		data = JSON.parse(data);
		
		var instances = jQuery(
				'#'+data.tabularFormId +' .tabf_table_row .tabf_table_cell:first-child a').get();		
		
		var container = jQuery('#'+data.tabularFormId);
		
		jQuery(container).get(0).lostInstances = data.result;
		
		tf.loadForm(container);
	},
	
	/*
	 * Called if 'Add instance' button has been pressed. Adds a new row to the
	 * Tabular Form.
	 */
	addInstance : function(tabfId){
		jQuery('#' + tabfId + ' .tabf_table_container .tabf_table_footer').
			before('<tr>' + jQuery('#' + tabfId + ' .tabf_add_instance_template').html() + '</tr>');
		
		var newRow = jQuery('#' + tabfId + ' .tabf_table_container .tabf_table_footer').prev();
		jQuery(newRow).removeClass('tabf_add_instance_template');
		jQuery(newRow).addClass('tabf_new_row');
		jQuery(newRow).attr('isNew', true);
		jQuery('td:first-child textarea', newRow).addClass('tabf_erronious_instance_name');
		jQuery('td', newRow).addClass('tabf_table_cell');
		jQuery('td:last-child', newRow).removeClass('tabf_table_cell');
		jQuery('td:last-child', newRow).addClass('tabf_status_cell');
		
		jQuery('td textarea', newRow).each(tf.initializeLoadedCell)
		jQuery('td:first-child', newRow).each(tf.initializeDeleteButtons)
		
		jQuery('td:first-child textarea', newRow).focus();
		
		jQuery('.tabf_save_button', jQuery(newRow).parent().parent()).attr('disabled', 'disabled');
		
		tf.checkNewInstanceName(jQuery('td:first-child textarea', newRow), 55);
	},
	
	/*
	 * Listener for instance name input field of new instances.
	 * 
	 * checks if instance name is valid and new via an Ajax Call.
	 */
	checkNewInstanceName : function(element, keyCode){
		
		//todo: alsi check that none of the other new instances has the same name
		
		if(jQuery(element).attr('class').indexOf('tabf_erronious_instance_name') == -1 
				&& jQuery(element).attr('class').indexOf('tabf_valid_instance_name') == -1){
			return;
		}
		
		var articleName = jQuery(element).attr('value');
		var tabularFormId = jQuery(element).parent().parent().parent().parent().parent().parent().attr('id');
		var rowNr = tf.getChildNumber(jQuery(element).parent().parent(), 1) 
		
		var url = wgServer + wgScriptPath + "/index.php";
		jQuery.ajax({ url:  url, 
			data: {
				'action' : 'ajax',
				'rs' : 'tff_checkArticleName',
				'rsargs[]' : [articleName, rowNr, tabularFormId],
			},
			success: tf.checkNewInstanceNameCallBack,
			
		});
	},
	
	
	/*
	 * Callback function for new instance name check
	 */
	checkNewInstanceNameCallBack : function(data){
		data = data.substr(data.indexOf('--##starttf##--') + 15, data.indexOf('--##endtf##--') - data.indexOf('--##starttf##--') - 15); 
		data = JSON.parse(data);
		
		var row =  jQuery('#' + data.tabularFormId + ' table tr:nth-child(' + data.rowNr + ')');
		if(data.validTitle == true){
			jQuery('td:first-child textarea', jQuery(row)).removeClass('tabf_erronious_instance_name');
			jQuery('td:first-child textarea', jQuery(row)).addClass('tabf_valid_instance_name');
			jQuery('td:first-child textarea', jQuery(row)).attr('isValidInstanceName', 'true');
			
			tf.deleteNotification(jQuery(row).parent().parent().parent(), 'tabf_add_instance_error', data.rowNr);
		} else {
			jQuery('td:first-child textarea', jQuery(row)).removeClass('tabf_valid_instance_name');
			jQuery('td:first-child textarea', jQuery(row)).addClass('tabf_erronious_instance_name');
			jQuery('td:first-child textarea', jQuery(row)).attr('isValidInstanceName', 'false');
			
			tf.addNotification(jQuery(row).parent().parent().parent(), 'tabf_add_instance_error', data.rowNr, data.rowNr, data.message);
		}
		
		if(jQuery('.tabf_new_row .tabf_erronious_instance_name', jQuery(row).parent().parent()).length == 0){
			jQuery('.tabf_save_button', jQuery(row).parent().parent()).removeAttr('disabled');
		} else {
			jQuery('.tabf_save_button', jQuery(row).parent().parent()).attr('disabled', 'disabled');
		} 
		
		//update notifications
		tf.updateInstanceNameInNotifications( jQuery('#' + data.tabularFormId)
				, data.rowNr, jQuery('td:first-child textarea', row).attr('value'));
			
	},
	
	/*
	 * Displays delete/undelete button if one moves mouse over a subject name
	 */
	displayDeleteButton : function(event){
		if(jQuery(this).parent().parent().parent().attr('class').indexOf('edit_mode') > 0){
			//todo: rempve minus 3
			var bottomPos = jQuery(this).position().top + jQuery(this).innerHeight() - jQuery('input', this).height() - 3;
			jQuery('.tabf-delete-button', this).css('top', bottomPos);
			jQuery('.tabf-delete-button', this).css('display', 'block');
		}
	},
	
	/*
	 * Hides delete/undelete button if mouse is moved out of subject name
	 */
	hideDeleteButton : function(){
		if(jQuery(this).parent().parent().parent().attr('class').indexOf('edit_mode') > 0){
			jQuery('.tabf-delete-button', this).css('display', 'none');
		}
	},
	
	/*
	 * Called if obe presses the Delete/Undelete Button
	 */
	deleteInstance : function (event){
		var input = Event.element(event);
		var row = jQuery(input).parent().parent();
		
		if(jQuery(row).attr('isNew') == 'true'){
		
			tf.hideNotificationsForInstance(jQuery(row).parent().parent().parent().parent(), tf.getChildNumber(row, 1), true);
			
			var rowParent = jQuery(row).parent().parent();
			
			jQuery(row).remove();
			
			if(jQuery('.tabf_modified_row', jQuery(rowParent)).length == 0
					&& jQuery('.tabf_deleted_row', jQuery(rowParent)).length == 0
					&& jQuery('.tabf_new_row .tabf_valid_instance_name', jQuery(rowParent)).length == 0){
				jQuery('.tabf_save_button', jQuery(rowParent)).attr('disabled', 'disabled');
			} else if ((jQuery('.tabf_modified_row', jQuery(rowParent)).length > 0
					|| jQuery('.tabf_deleted_row', jQuery(rowParent)).length > 0
					|| jQuery('.tabf_new_row .tabf_valid_instance_name', jQuery(rowParent)).length > 0)
					&& jQuery('.tabf_new_row .tabf_erronious_instance_name', jQuery(rowParent)).length == 0){
				jQuery('.tabf_save_button', jQuery(rowParent)).removeAttr('disabled');
			}
			
			
			
		} else {
			
			if(jQuery(row).attr('isDeleted') == 'true'){
				tf.restoreNotificationsForInstance(jQuery(row).parent().parent().parent().parent(), tf.getChildNumber(row, 1));
				
				jQuery(row).attr('isDeleted', false);
				jQuery(row).removeClass('tabf_deleted_row');
				jQuery(input).attr('value', 'Delete');
				
				jQuery('.tabf_deleted_status', row).css('display', 'none');
				if(jQuery(row).attr('isModified') == 'true'){
					jQuery('.tabf_modified_status', row).css('display', 'block');
				} else {
					//jQuery('.tabf_ok_status', row).css('display', 'block');
				}
				
				jQuery('td textarea', row).attr('readonly', '');
				
				if(jQuery('.tabf_modified_row', jQuery(row).parent().parent()).length == 0
						&& jQuery('.tabf_deleted_row', jQuery(row).parent().parent()).length == 0
						&& jQuery('.tabf_new_row .tabf_valid_instance_name', jQuery(row).parent().parent()).length == 0){
					jQuery('.tabf_save_button', jQuery(row).parent().parent()).attr('disabled', 'disabled');
				}
			} else {
				tf.hideNotificationsForInstance(jQuery(row).parent().parent().parent().parent(), tf.getChildNumber(row, 1), false);
				
				jQuery(row).attr('isDeleted', true);
				jQuery(row).addClass('tabf_deleted_row');
				jQuery(input).attr('value', 'Undelete');
				
				jQuery('.tabf_deleted_status', row).css('display', 'block');
				jQuery('.tabf_modified_status', row).css('display', 'none');
				//jQuery('.tabf_ok_status', row).css('display', 'none');
				
				jQuery('td textarea', row).attr('readonly', 'true');
				
				if(jQuery('..tabf_erronious_instance_name', jQuery(row).parent().parent()).length == 0){
					jQuery('.tabf_save_button', jQuery(row).parent().parent()).removeAttr('disabled');
				}
			}
		}
	},
	
	/*
	 * Starts the sorting. Called if a user clicks on one of the sort buttons
	 */
	startRowSort : function(event){
		var column = jQuery(Event.element(event)).parent().parent().parent();
		
		var table = jQuery(column).parent().parent();
		
		var sortImgPath = wgScriptPath + '/extensions/SemanticMediaWiki/skins/images/';
		
		if(jQuery('img', column).attr('src') == sortImgPath +'sort_none.gif'
					|| jQuery('img', column).attr('src') == sortImgPath +'sort_up.gif'){
			jQuery('th .sortarrow img', table).attr('src', sortImgPath +'sort_none.gif');
			jQuery('.sortarrow img', column).attr('src', sortImgPath +'sort_down.gif');
			tf.currentSortOrder = 1;
		} else {
			jQuery('th .sortarrow img', table).attr('src', sortImgPath +'sort_none.gif');
			jQuery('.sortarrow img', column).attr('src', sortImgPath +'sort_up.gif');
			tf.currentSortOrder = -1;
		}
		
		tf.currentSortColumnNr = tf.getChildNumber(column, 1);
		tf.currentLastSortColumnNumber = jQuery('th', jQuery(column).parent()).get().length;
		
		//make sure that also cell values will be sorted again
		jQuery('td[values-already-sorted=true]', table).attr('values-already-sorted', 'false');
		
		var rows = jQuery('> tr', table).get();
		jQuery(rows).attr('isSpecialRow', '');
		jQuery(rows[0]).attr('isSpecialRow', 'header');
		jQuery(rows[rows.length-1]).attr('isSpecialRow', 'add-template');
		jQuery(rows[rows.length-2]).attr('isSpecialRow', 'footer');
		rows.sort(tf.sortRows);
		
		var newTableHTML = '';
		for(var nr=0; nr < rows.length; nr++){
			jQuery(table).append(rows[nr]);
		}
	},
	
	/*
	 * Callback for sort function to detect which row
	 * to place before the other
	 */
	sortRows : function(a, b){
		
		//first deal with special rows
		if(jQuery(a).attr('isSpecialRow') == 'header'){
			return -1;
		} else if(jQuery(b).attr('isSpecialRow') == 'header'){
			return 1;
		} else if(jQuery(a).attr('isSpecialRow') == 'footer'){
			return 1;
		} else if(jQuery(b).attr('isSpecialRow') == 'footer'){
			return -1;
		} else if(jQuery(a).attr('isSpecialRow') == 'add-template'){
			return 1;
		} else if(jQuery(b).attr('isSpecialRow') == 'add-template'){
			return -1;
		}
		
		//now deal with notmal rows
		var firstKey = tf.getRowSortKey(jQuery('td:nth-child(' + tf.currentSortColumnNr + ')', a)).toLowerCase();
		var secondKey = tf.getRowSortKey(jQuery('td:nth-child(' + tf.currentSortColumnNr + ')', b)).toLowerCase();
		
		if(firstKey == secondKey) {
			return 0;
		} else if (firstKey < secondKey) {
			return -1 * tf.currentSortOrder;
		} else {
			return 1 * tf.currentSortOrder;
		}
	},
	
	/*
	 * Called by sortRow to get the sort key of a row
	 */
	getRowSortKey : function(cell){
		var sortKey = '';
		if(tf.currentSortColumnNr == 1){ //the subkect column
			if(jQuery('*:first-child', cell).get(0).tagName == 'A'){
				sortKey = jQuery('*:first-child', cell).html();
			} else {
				sortKey = jQuery('*:first-child', cell).attr('value');
			}
		} else if (tf.currentSortColumnNr == tf.currentLastSortColumnNumber){ //status row
			var images = jQuery('img', cell).get();
			var sortKey = 'z';
			for(var i=0; i < images.length; i++){
				if(jQuery(images[i]).css('display') != 'none'){
					switch (jQuery(images[i]).attr('class')) {
						case 'tabf_added_status' :
							sortKey = 'a';
							break;
						case 'tabf_deleted_status' :
							sortKey = 'b';
							break;
						case 'tabf_modified_status' :
							sortKey = 'c';
							break;
						case 'tabf_exists_not_status' :
							sortKey = 'd';
							break;
						case 'tabf_saved_status' :
							sortKey = 'e';
							break;
						case 'tabf_error_status' :
							sortKey = 'f';
							break;
						case 'tabf_pending_status' :
							sortKey = 'g';
							break;
						default :
							sortKey = 'z';
							break;
					}
					break;
				}
			}
		} else { //annotation or template column
			var isTemplateParam = jQuery('th:nth-child(' + tf.currentSortColumnNr + ')'
					, jQuery(cell).parent().parent().parent()).attr('is-template');
			
			var children = jQuery(cell).children().get();
			
			if(jQuery(cell).attr('values-already-sorted') != 'true'){
			
				children.sort(tf.sortCell);
				for(var i=0; i < children.length; i++){
					jQuery(cell).append(children[i]);
				}
				
				if(isTemplateParam == 'true'){
					
					var templateName = jQuery('th:nth-child(' + tf.currentSortColumnNr + ')', 
							jQuery(cell).parent().parent().parent()).attr('field-address');
					templateName = templateName.substr(0, templateName.indexOf('#'));
					tf.resortTemplateParamsInRow(jQuery(cell).parent(), templateName, children);
				}
				
				jQuery(cell).attr('values-already-sorted', 'true');			
			}
			
			return tf.getCellSortKey(children[0]);
		}
		
		return sortKey;
	},
	
	/*
	 * Called by getRowSortKey to also sort the values
	 * within the cells
	 */
	sortCell : function(a, b){
		var firstKey = tf.getCellSortKey(a).toLowerCase();
		var secondKey = tf.getCellSortKey(b).toLowerCase();
		
		if(firstKey == secondKey) {
			return 0;
		} else if (firstKey < secondKey) {
			return -1 * tf.currentSortOrder;
		} else {
			return 1 * tf.currentSortOrder;
		}
	},
	
	/*
	 * Callback function for sort function
	 * to detect which cell value to place
	 * before the other
	 */
	getCellSortKey : function(element){
		if(jQuery(element).get(0).tagName == 'TEXTAREA'){
			return jQuery(element).attr('value');
		} else if(jQuery(element).get(0).tagName == 'DIV'){
			return jQuery(element).html();
		}
	},
	
	/*
	 * called by getRowSortKey if the cell values of a template
	 * parameter have been sorted. This function makes sure, that
	 * other cells in the same row, that relate to the same template
	 * are reordered appropriately
	 */ 
	resortTemplateParamsInRow : function(row, templateName, pattern){
		
		var cols = jQuery('th', row.parent().parent()).get();
		
		for(var i = 0; i < cols.length; i++){
			if(jQuery(cols[i]).attr('is-template') != 'true') continue;
			
			if(jQuery(cols[i]).attr('field-address').substr(0, 
				jQuery(cols[i]).attr('field-address').indexOf('#')) != templateName) continue;
			
			var cell = jQuery('td:nth-child(' + (i + 1) + ')', row);
			var children = jQuery(cell).children().get();
			
			for(var k=0; k < pattern.length; k++){
				for(var m=0; m < children.length; m++){
					if(jQuery(pattern[k]).attr('template-id') == jQuery(children[m]).attr('template-id')){
						jQuery(cell).append(children[m]);
					}
	
				}
			}
			
			
		}

	},
	
	/*
	 * Is cursor in last row
	 */
	isLastRow : function(cell){
		var comparator = jQuery('.tabf_rowindex_comparator', jQuery(cell).parent().parent().parent().parent().parent());
		
		jQuery(comparator).css('width', jQuery(cell).innerWidth() + 'px');
		jQuery(comparator).attr('value', jQuery(cell).attr('value'));
		
		var completeScrollHeight = jQuery(comparator).attr('scrollHeight');
		
		jQuery(comparator).attr('value', jQuery(cell).attr('value').substr(0, tf.selectionEndPosition));
		
		var newScrollHeight = jQuery(comparator).attr('scrollHeight');
		
		if(newScrollHeight < completeScrollHeight){
			return false;
		} else {
			return true;
		}
	},
	
	/*
	 * Is cursor in first row
	 */
	isFirstRow : function(cell){
		var comparator = jQuery('.tabf_rowindex_comparator', jQuery(cell).parent().parent().parent().parent().parent());
		
		jQuery(comparator).css('width', jQuery(cell).innerWidth() + 'px');
		jQuery(comparator).attr('value', '');
		
		var completeScrollHeight = jQuery(comparator).attr('scrollHeight');
		
		jQuery(comparator).attr('value', jQuery(cell).attr('value').substr(0, tf.selectionStartPosition));
		
		var newScrollHeight = jQuery(comparator).attr('scrollHeight');
		
		if(newScrollHeight == completeScrollHeight){
			return true;
		} else {
			return false;
		}
	},
	
	/*
	 * Set class for currently selected cell
	 */
	setAsCurrentlySelectedCellValue : function(){
		jQuery(this).addClass('tabf_selected_value');
	},
	
	
	/*
	 * unset class for currently selected cell
	 */
	unSetAsCurrentlySelectedCellValue : function(){
		jQuery(this).removeClass('tabf_selected_value');
	},
	
	getPositionForAC : function(element){
		if(jQuery(element).parent().attr('class') == 'tabf_table_cell'){;
			var pos = new Object();
			pos.top = jQuery(element).position().top + jQuery(element).height();
			pos.left = jQuery(element).position().left;
			return pos;
		} else {
			return false;
		}
	},
	
	
	/*
	 * Check if annotation value 
	 * - is valid according to property type
	 * - matches the query conditions for htis property
	 */
	checkAnnotationValue : function(element){
		
		if(jQuery(element).attr('class').indexOf('tabf_erronious_instance_name') != -1 
				|| jQuery(element).attr('class').indexOf('tabf_valid_instance_name') != -1){
			//seems to be textarea for instance name
			return;
		}
		
		var tabularFormId = jQuery(element).parent().parent().parent().parent().parent().parent().attr('id');
		var rowNr = tf.getChildNumber(jQuery(element).parent().parent(), 1)
		var columnNr = tf.getChildNumber(jQuery(element).parent(), 1);
		var fieldNr = tf.getChildNumber(jQuery(element), 1);
		
		if( jQuery('#' + tabularFormId + ' table tr:first-child th:nth-child(' + columnNr 
				+ ')').attr('is-template') != 'true'){
		
			var cssSelector = '#' + tabularFormId + ' table tr:nth-child(' + rowNr + ') td:nth-child(' + columnNr  + ')';
			
			var annotationName = jQuery('#' + tabularFormId + ' table tr:first-child th:nth-child(' + columnNr 
					+ ')').attr('field-address');
			var annotationLabel = '';
			if(annotationName == '__Category__'){
				annotationLabel = jQuery('#' + tabularFormId + ' table tr:first-child th:nth-child(' + columnNr 
						+ ') span:nth-child(2)').html();
			} else {
				annotationLabel = jQuery('#' + tabularFormId + ' table tr:first-child th:nth-child(' + columnNr 
						+ ') span:nth-child(2) a').html();
			}
			
			var queryConditions = jQuery('#' + tabularFormId + ' table tr:first-child th:nth-child(' + columnNr 
					+ ')' ).get();
			if(queryConditions[0].childNodes.length == 4){
				queryConditions = queryConditions[0].lastChild.innerHTML;
			} else {
				queryConditions = '';
			}
			
			var annotationValue = jQuery(element).attr('value');
			
			var annotationValues = "";
			jQuery('textarea', jQuery(element).parent()).each( function (){
				annotationValues += ";" + jQuery(this).attr('value').replace(/;/g, "/;");
			});
			
			//add read-only values
			jQuery('div', jQuery(element).parent()).each( function (){
				var title = '';
				if(jQuery('a', this).html() != null){
					title = jQuery('a',this).html();
					if(jQuery('a',this).attr('title').indexOf(':' + title) > 1){
						title = jQuery('a', this).attr('title').substr(0, title.length + 1 + jQuery('a', this).attr('title').indexOf(':' + title));
					}
				} else {
					title = jQuery(this).html(); 
				}
				annotationValues += ";" + title.replace(/;/g, "/;");
			});
			
			if(queryConditions == null){
				queryConditions = false;
			}
			
			var articleName = '';
			if(jQuery('td:first-child ',jQuery(element).parent().parent()).attr('revision-id') == '-1'){
				var articleName = jQuery('td:first-child textarea',jQuery(element).parent().parent()).attr('value');
			} else {
				var articleName = jQuery('td:first-child ',jQuery(element).parent().parent()).attr('article-name');
			}
			
			var url = wgServer + wgScriptPath + "/index.php";
			jQuery.ajax({ url:  url, 
				data: {
					'action' : 'ajax',
					'rs' : 'tff_checkAnnotationValues',
					'rsargs[]' : [annotationName, annotationLabel, annotationValue, annotationValues, queryConditions, cssSelector, fieldNr, articleName],
				},
				success: tf.checkAnnotationValueCallBack,
				
			});
		}
	},
	
	/*
	 * Call back for the check annotation value ajax call
	 */
	checkAnnotationValueCallBack : function(data){
		
		data = data.substr(data.indexOf('--##starttf##--') + 15, data.indexOf('--##endtf##--') - data.indexOf('--##starttf##--') - 15); 
		data = JSON.parse(data);
		
		var container = jQuery(data.cssSelector).parent().parent().parent().parent();
		var instanceId = tf.getChildNumber(jQuery(data.cssSelector).parent(), 1);
		if(data.isValid == false){
			tf.addNotification(container, 'tabf_invalid_value_warning', data.cssSelector + '-' + data.fieldNr, instanceId, data.invalidValueMsg);
			jQuery(data.cssSelector + ' textarea:nth-child(' + data.fieldNr + ')').addClass('tabf_invalid_input_filed_value');
		} else {
			tf.deleteNotification(container, 'tabf_invalid_value_warning', data.cssSelector  + '-' + data.fieldNr);
			jQuery(data.cssSelector + ' textarea:nth-child(' + data.fieldNr + ')').removeClass('tabf_invalid_input_filed_value');
		}
		
		//uppdate row state
		if(jQuery('.tabf_invalid_input_filed_value', jQuery(data.cssSelector).parent()).get().length > 0){
			jQuery('.tabf_invalid_value_status', jQuery(data.cssSelector).parent()).css('display', '');
		} else {
			jQuery('.tabf_invalid_value_status', jQuery(data.cssSelector).parent()).css('display', 'none');
		}
		
		if(data.lost == true){
			tf.addNotification(container, 'tabf_probably_lost_instance', data.cssSelector, instanceId, data.looseWarnings);
			jQuery(data.cssSelector).addClass('tabf_probably_lost_cell');
		} else {
			tf.deleteNotification(container, 'tabf_probably_lost_instance', data.cssSelector);
			jQuery(data.cssSelector).removeClass('tabf_probably_lost_cell');
		}
		
		//uppdate row state
		if(jQuery('.tabf_probably_lost_cell', jQuery(data.cssSelector).parent()).get().length > 0){
			jQuery('.tabf_getslost_status', jQuery(data.cssSelector).parent()).css('display', '');
		} else {
			jQuery('.tabf_getslost_status', jQuery(data.cssSelector).parent()).css('display', 'none');
		}
	},
	
	addNotification : function(container, notificationClass, id, instanceId, message){
		
		jQuery("." + notificationClass, container).css('display', '');
		
		var found = false;
		jQuery("." + notificationClass + ' ul li', container).each( function(){
			if(!found && jQuery(this).attr('messageId') == id){
				found = true;
				jQuery(this).html('<li messageId="' + id + '" instanceId="' + instanceId + '">' + message + '</li>');
			}	
		});
		
		if(!found){
			jQuery("." + notificationClass + ' ul', container).append(
						'<li messageId="' + id + '" instanceId="' + instanceId + '">' + message + '</li>');
			jQuery('.tabf-warnings-number', container).html(
					jQuery('.tabf-warnings-number', container).html()*1 + 1);
		}
		
	},
	
	deleteNotification : function(container, notificationClass, id){
		
		var found = false;
		jQuery("." + notificationClass + ' ul li', container).each( function(){
			if(!found && jQuery(this).attr('messageId') == id){
				found = true;
				jQuery(this).remove();
				jQuery('.tabf-warnings-number', container).html(
						jQuery('.tabf-warnings-number', container).html()*1 - 1);
			}
		});
		
		var found = false;
		jQuery("." + notificationClass + ' ul li', container).each(function(){
			if(jQuery(this).css('display') != 'none'){
				found = true;
			}
		})
		
		if(!found){
			jQuery("." + notificationClass, container).css('display', 'none');
		}
	
	},
	
	
	updateInstanceNameInNotifications : function(container, instanceId, newInstanceName){
		
		jQuery('.tabf_notifications ol > li', container).each(function(){
			
			jQuery('li', this).each(function(){
		
				if(jQuery(this).attr('instanceId') == instanceId){
					
					var replacement = jQuery(this).html();
					
					if(replacement.indexOf('<span class="tabf_nin">') != -1){
						replacement = 
							replacement.substr(0, replacement.indexOf('<span class="tabf_nin">') + 23)
							+ newInstanceName
							+ replacement.substr(replacement.indexOf('</span>'));
						jQuery(this).html(replacement);
					}
				}
				
			});
		});
			
	},
	
	hideNotificationsForInstance : function(container, instanceId, permanently){
		
		jQuery('.tabf_notifications ol > li', container).each(function(){
			
			jQuery('li', this).each(function(){
				
				if(jQuery(this).attr('instanceId') == instanceId){
					if(permanently){
						jQuery(this).remove();
					} else {
						jQuery(this).css('display', 'none');
					}
					
					jQuery('.tabf-warnings-number', container).html(
							jQuery('.tabf-warnings-number', container).html()*1 - 1);
				}
			});
			
			var found = false;
			jQuery('li', this).each(function(){
				if(jQuery(this).css('display') != 'none'){
					found = true;
				}
			})
			
			if(!found){
				jQuery(this).css('display', 'none');
			}
				
		});
			
	},
	
	restoreNotificationsForInstance : function(container, instanceId){
		
		jQuery('.tabf_notifications ol > li', container).each(function(){
			
			var found = false;
			
			jQuery('li', this).each(function(){
				
				if(jQuery(this).attr('instanceId') == instanceId){
					jQuery(this).css('display', '');
					
					jQuery('.tabf-warnings-number', container).html(
							jQuery('.tabf-warnings-number', container).html()*1 + 1);
					
					found = true;
				}
			});
			
			if(found){
				jQuery(this).css('display', '');
			}
				
		});
			
	},
	
	expandNotificationSystem : function(event){
		var container = jQuery(Event.element(event)).parent();
		jQuery('img:nth-child(1)', container).css('display', 'none');
		jQuery('img:nth-child(2)', container).css('display', '');
		
		jQuery('.tabf_notifications', jQuery(container).parent()).css('display', '');
		
	},
	
	collapseNotificationSystem : function(event){
		var container = jQuery(Event.element(event)).parent();
		jQuery('img:nth-child(1)', container).css('display', '');
		jQuery('img:nth-child(2)', container).css('display', 'none');
		
		jQuery('.tabf_notifications', jQuery(container).parent()).css('display', 'none');
	},
	
	
	switchToEditMode : function(tabularFormId){
		var container = jQuery('#' + tabularFormId);
		jQuery(container).attr('isInEditMode' , 'true');

		
		jQuery('.tabf_in_view_mode', container).addClass('tabf_in_edit_mode');
		jQuery('.tabf_in_view_mode', container).removeClass('tabf_in_view_mode');
		
		//show butttons
		jQuery('.tabf_add_button', container).parent().css('display', '');
		jQuery('.tabf_save_button', container).parent().parent().css('display', '');
		jQuery('.tabf_cancel_button', container).css('display', '');
		jQuery('.tabf_edit_button', container).css('display', 'none');
		
		//display status column
		jQuery('.tabf_status_column_header', container).css('display', '');
		jQuery('.tabf_status_cell', container).css('display', '');
		
		//make textareas editable
		jQuery('textarea', container).removeAttr('disabled');
		
		//display notification system
		jQuery('.tabf_notification_system', container).css('display', '');
		
		//todo: instance lost messages should only be displayed in edit mode
	},
	
});




var tf = new TF();

jQuery(document).ready( function($) {
	tf.loadForms();
});
