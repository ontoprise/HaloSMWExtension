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
		jQuery('.tabf_loader').css('display', 'table', container);
		
		var querySerialization = jQuery('.tabf_query_serialization', container).html();
		var tabularFormId = jQuery(container).attr('id');
		var isSPARQL = jQuery('.tabf_query_serialization', container).attr('isSPARQL');
		
		//todo:add ajax error handling
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
		
		jQuery('#' + data.tabularFormId + ' .tabf_table_container').css('display', 'block');
		
		jQuery('#' + data.tabularFormId + ' .tabf_table_container td textarea').each(tf.initializeLoadedCell)
		
		jQuery('#' + data.tabularFormId + ' tr td:first-child').each(tf.initializeDeleteButtons);
		
		if(jQuery('#' + data.tabularFormId + ' .tabf_new_row').get().length > 0){
			jQuery('#' + data.tabularFormId + ' .tabf_save_button').css('display', 'inline');
		}
		
		//todo: validate subject title textarea values 
	},
	
	/*
	 * This method is called after a tabular form has been loaded.
	 * 
	 * It adds required event listeners to all input foelds for modifying 
	 * instance data.
	 */
	initializeLoadedCell : function(){
		jQuery(this).attr('originalValue', jQuery(this).attr('value'));
			
		jQuery(this).change(tf.cellChangeHandler);
		jQuery(this).keyup(tf.cellKeyUpHandler);
		jQuery(this).keydown(tf.cellKeyDownHandler);
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
		
		tf.checkNewInstanceName(this, event.which);
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
		
		if(keyCode == '39' || keyCode == 'tab'){ //key right
			
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
						
		} else if(keyCode == '40'){ //key down
			
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
			}
		} else if(keyCode == '38'){ //key up
			
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
		var parentRow = jQuery(node).parent('td').parent('tr');
		
		if(jQuery(node).attr('class') == 'tabf_erronious_instance_name'
				|| jQuery(node).attr('class') == 'tabf_valid_instance_name'){
			return;
		}
		
		if(jQuery(node).attr('originalValue') != jQuery.trim(jQuery(node).attr('value'))){
			jQuery(node).addClass('tabf_modified_value');
			jQuery(node).attr('isModified', true);
		
			if(jQuery(parentRow).attr('class') == 'tabf_new_row'){
				return;
			}
			
			parentRow.attr('isModified', true);
			parentRow.addClass('tabf_modified_row');
			
			//jQuery('td:last-child .tabf_ok_status', parentRow).css('display', 'none');
			jQuery('td:last-child .tabf_modified_status', parentRow).css('display', 'inline');
			
			if(jQuery('..tabf_erronious_instance_name', jQuery(parentRow).parent().parent()).length == 0){
				jQuery('.tabf_save_button', jQuery(parentRow).parent().parent()).css('display', 'inline');
			}
		} else {
			jQuery(node).removeClass('tabf_modified_value');
			jQuery(node).attr('isModified', false);
			
			if(jQuery(parentRow).attr('class') == 'tabf_new_row'){
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
					jQuery('.tabf_save_button', jQuery(parentRow).parent().parent()).css('display', 'none');
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
		
		if(jQuery(this).attr('isDeleted') == 'true'){
			jQuery('td:last-child .tabf_deleted_status', this).css('display', 'none');
			jQuery('td:last-child .tabf_pending_status', this).css('display', 'inline');
			
			var tabularFormId = jQuery(this).parent().parent().parent().parent().attr('id');
			var revisionId = jQuery('td:first-child ',this).attr('revision-id');
			var articleTitle = jQuery('td:first-child ',this).attr('article-name');
			
			
			//todo:add ajax error handling
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
			
			alert('a');
			
			jQuery('td:last-child .tabf_modified_status', this).css('display', 'none');
			jQuery('td:last-child .tabf_added_status', this).css('display', 'none');
			jQuery('td:last-child .tabf_pending_status', this).css('display', 'inline');
			
			var modifiedValues = new Array();
			
			var fields = jQuery('td', this).get();
			for(var fieldNr=0; fieldNr < fields.length; fieldNr++){
				
				var fieldValues = jQuery('textarea', jQuery(fields[fieldNr])).get();
				for(var i=0; i < fieldValues.length; i++){
					if(jQuery(fieldValues[i]).attr('isModified') == 'true'){
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
			var revisionId = jQuery('td:first-child ',this).attr('revision-id');
			if(revisionId == '-1'){
				var articleTitle = jQuery('td:first-child textarea',this).attr('value');
			} else {
				var articleTitle = jQuery('td:first-child ',this).attr('article-name');
			}
			
			
			//todo:add ajax error handling
			var url = wgServer + wgScriptPath + "/index.php";
			jQuery.ajax({ url:  url, 
				data: {
					'action' : 'ajax',
					'rs' : 'tff_updateInstanceData',
					'rsargs[]' : [JSON.stringify(modifiedValues), articleTitle, revisionId, rowNr, tabularFormId],
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
			jQuery(row).addClass('tabf_table_row_saved_successfull');
			jQuery('td:last-child .tabf_pending_status', row).css('display', 'none');
			jQuery('td:last-child .tabf_saved_status', row).css('display', 'inline');
			//replace article name input of new instance with textarea
			
			if(jQuery('td:first-child textarea', row).attr('class') == 'tabf_valid_instance_name'){
				var text = '<a href="' + wgServer + wgScriptPath + "/index.php" + "?title=";
				text += encodeURI(jQuery('td:first-child textarea', row).attr('value'));
				text += '">' + jQuery('td:first-child textarea', row).attr('value') + '</a>';
				jQuery('td:first-child', row).html(text);
			}
		} else {
			jQuery(row).addClass('.tabf_table_row_saved_error');
			jQuery('td:last-child .tabf_pending_status', row).css('display', 'none');
			jQuery('td:last-child .tabf_error_status', row).attr('title', data.msg);
			jQuery('td:last-child .tabf_error_status', row).css('display', 'inline');
		}
	},
	
	/*
	 * Called if 'Add instance' button has been pressed. Adds a new row to the
	 * Tabular Form.
	 */
	addInstance : function(tabfId){
		jQuery('#' + tabfId + ' .tabf_table_container .smwfooter').
			before('<tr>' + jQuery('#' + tabfId + ' .tabf_table_container table tr:last-child').html() + '</tr>');
		
		var newRow = jQuery('#' + tabfId + ' .tabf_table_container .smwfooter').prev();
		jQuery(newRow).addClass('tabf_new_row');
		jQuery(newRow).attr('isNew', true);
		jQuery('td:first-child textarea', newRow).addClass('tabf_erronious_instance_name');
		jQuery('td', newRow).addClass('tabf_table_cell');
		
		jQuery('td textarea', newRow).each(tf.initializeLoadedCell)
		jQuery('td:first-child', newRow).each(tf.initializeDeleteButtons)
		
		jQuery('td:first-child textarea', newRow).focus();
		
		jQuery('.tabf_save_button', jQuery(newRow).parent().parent()).css('display', 'none');
	},
	
	/*
	 * Listener for instance name input field of new instances.
	 * 
	 * checks if instance name is valid and new via an Ajax Call.
	 */
	checkNewInstanceName : function(element, keyCode){
		if(jQuery(element).attr('class') != 'tabf_erronious_instance_name' 
				&& jQuery(element).attr('class') != 'tabf_valid_instance_name'){
			return;
		}
		
		if(keyCode == 'tab' || keyCode == 'shift-tab' || (keyCode >= '38' && keyCode <= '40')){
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
		} else {
			jQuery('td:first-child textarea', jQuery(row)).removeClass('tabf_valid_instance_name');
			jQuery('td:first-child textarea', jQuery(row)).addClass('tabf_erronious_instance_name');
		}
		
		if(jQuery('.tabf_new_row .tabf_erronious_instance_name', jQuery(row).parent().parent()).length == 0){
			jQuery('.tabf_save_button', jQuery(row).parent().parent()).css('display', 'inline');
		} else {
			jQuery('.tabf_save_button', jQuery(row).parent().parent()).css('display', 'none');
		} 
			
	},
	
	/*
	 * Displays delete/undelete button if one moves mouse over a subject name
	 */
	displayDeleteButton : function(event){
		jQuery('.tabf-delete-button', this).css('position', 'absolute');
		//todo: rempve minus 3
		var bottomPos = jQuery(this).position().top + jQuery(this).innerHeight() - jQuery('input', this).height() - 3;
		jQuery('.tabf-delete-button', this).css('top', bottomPos);
		//todo: remove minus 1
		jQuery('.tabf-delete-button', this).css('width', jQuery(this).innerWidth() - 1);
		jQuery('.tabf-delete-button', this).css('display', 'block');
	},
	
	/*
	 * Hides delete/undelete button if mouse is moved out of subject name
	 */
	hideDeleteButton : function(){
		jQuery('.tabf-delete-button', this).css('display', 'none');
	},
	
	/*
	 * Called if obe presses the Delete/Undelete Button
	 */
	deleteInstance : function (event){
		var input = Event.element(event);
		var row = jQuery(input).parent().parent();
		
		if(jQuery(row).attr('isNew') == 'true'){
			
			var rowParent = jQuery(row).parent().parent();
			
			jQuery(row).remove();
			
			if(jQuery('.tabf_modified_row', jQuery(rowParent)).length == 0
					&& jQuery('.tabf_deleted_row', jQuery(rowParent)).length == 0
					&& jQuery('.tabf_new_row .tabf_valid_instance_name', jQuery(rowParent)).length == 0){
				jQuery('.tabf_save_button', jQuery(rowParent)).css('display', 'none');
			} else if ((jQuery('.tabf_modified_row', jQuery(rowParent)).length > 0
					|| jQuery('.tabf_deleted_row', jQuery(rowParent)).length > 0
					|| jQuery('.tabf_new_row .tabf_valid_instance_name', jQuery(rowParent)).length > 0)
					&& jQuery('.tabf_new_row .tabf_erronious_instance_name', jQuery(rowParent)).length == 0){
				jQuery('.tabf_save_button', jQuery(rowParent)).css('display', 'inline');
			}
			
			
			
		} else {
			if(jQuery(row).attr('isDeleted') == 'true'){
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
					jQuery('.tabf_save_button', jQuery(row).parent().parent()).css('display', 'none');
				}
			} else {
				jQuery(row).attr('isDeleted', true);
				jQuery(row).addClass('tabf_deleted_row');
				jQuery(input).attr('value', 'Undelete');
				
				jQuery('.tabf_deleted_status', row).css('display', 'block');
				jQuery('.tabf_modified_status', row).css('display', 'none');
				//jQuery('.tabf_ok_status', row).css('display', 'none');
				
				jQuery('td textarea', row).attr('readonly', 'true');
				
				if(jQuery('..tabf_erronious_instance_name', jQuery(row).parent().parent()).length == 0){
					jQuery('.tabf_save_button', jQuery(row).parent().parent()).css('display', 'inline');
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
			jQuery('th img', table).attr('src', sortImgPath +'sort_none.gif');
			jQuery('img', column).attr('src', sortImgPath +'sort_down.gif');
			tf.currentSortOrder = 1;
		} else {
			jQuery('th img', table).attr('src', sortImgPath +'sort_none.gif');
			jQuery('img', column).attr('src', sortImgPath +'sort_up.gif');
			tf.currentSortOrder = -1;
		}
		
		tf.currentSortColumnNr = tf.getChildNumber(column, 1);
		tf.currentLastSortColumnNumber = jQuery('th', jQuery(column).parent()).get().length;
		
		//make sure that also cell values will be sorted again
		jQuery('td[values-already-sorted=true]', table).attr('values-already-sorted', 'false');
		
		var rows = jQuery('tr', table).get();
		jQuery(rows[0]).attr('isSpecialRow', 'header');
		jQuery(rows[rows.length-1]).attr('isSpecialRow', 'footer');
		jQuery(rows[rows.length-2]).attr('isSpecialRow', 'add-template');
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
			for(var i=0; i < images.length; i++){
				if(jQuery(images[i]).css('display') != 'none'){
					sortKey = jQuery(images[i]).attr('class');
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
					
					//todo: make sure, that this does not happen each time the value of this cell is compared
					
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

	}
	
	
	
});




var tf = new TF();

jQuery(document).ready( function($) {
	tf.loadForms();
});
