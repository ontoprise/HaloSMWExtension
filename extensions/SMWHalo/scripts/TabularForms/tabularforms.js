

var TF = Class.create({

	init: function(){ 
		this.xyz = 'abc';
		},
	
	loadForms : function(){
		jQuery('.tabf_container').each( function (){
			tf.loadForm(this);
		});
	},
	
	loadForm : function(container){
		jQuery('.tabf_table_container', container).css('display', 'none');
		jQuery('.tabf_loader').css('display', 'table', container);
		
		var querySerialization = jQuery('.tabf_query_serialization', container).html();
		var tabularFormId = jQuery(container).attr('id');
		
		//todo:add ajax error handling
		var url = wgServer + wgScriptPath + "/index.php";
		jQuery.ajax({ url:  url, 
			data: {
				'action' : 'ajax',
				'rs' : 'tff_getTabularForm',
				'rsargs[]' : [querySerialization, tabularFormId],
			},
			success: tf.displayLoadedForm,
			
		});
	},
	
	displayLoadedForm : function(data){
		
		data = data.substr(data.indexOf('--##starttf##--') + 15, data.indexOf('--##endtf##--') - data.indexOf('--##starttf##--') - 15); 
		data = JSON.parse(data);
		
		jQuery('#' + data.tabularFormId + ' .tabf_loader').css('display', 'none');
		jQuery('#' + data.tabularFormId + ' .tabf_table_container').html(data.result);
		
		jQuery('#' + data.tabularFormId + ' .tabf_table_container').css('display', 'block');
		
		jQuery('#' + data.tabularFormId + ' .tabf_table_container td textarea').each(tf.initializeLoadedCell)
	},
	
	initializeLoadedCell : function(){
		jQuery(this).attr('originalValue', jQuery(this).attr('value'));
			
		jQuery(this).change(tf.cellChangeHandler);
		jQuery(this).keyup(tf.cellKeyUpHandler);
		jQuery(this).keydown(tf.cellKeyDownHandler);
	},
	
	cellChangeHandler : function(){
		tf.cellValueChangeHandler(this);
	},
	
	cellKeyUpHandler : function(event){
		tf.shiftKeyPressed = false;
		
		tf.navigateCells(this, event.which);
		
		tf.cellValueChangeHandler(this);
		
		tf.checkNewInstanceName(this, event.which);
	},
	
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
			
			jQuery('textarea:nth-child(' + cellNumber +')', column).focus();
			jQuery('textarea:nth-child(' + cellNumber +')', column).select();
		
		} else if(keyCode == 'shift+tab'){ 
			
			var column = tf.getPrevColumn(jQuery(cell).parent(), true);
			
			var maxCellNumber = jQuery('textarea', column).get().length;
			var cellNumber = tf.getChildNumber(cell, 1);
			if(maxCellNumber < cellNumber){
				cellNumber = maxCellNumber;
			}
			
			jQuery('textarea:nth-child(' + cellNumber +')', column).focus();
			jQuery('textarea:nth-child(' + cellNumber +')', column).select();
						
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
				jQuery('textarea:nth-child(' + cellNumber +')', column).focus();
				jQuery('textarea:nth-child(' + cellNumber +')', column).select();
			}
		} else if(keyCode == '38'){ //key down
			
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
				jQuery('textarea:nth-child(' + cellNumber +')', column).focus();
				jQuery('textarea:nth-child(' + cellNumber +')', column).select();
			}
		}
	},
	
	getChildNumber : function(node, nr){
		node = jQuery(node).prev();
		if(jQuery(node).html() == null){
			return nr;
		} else {
			nr += 1;
			return tf.getChildNumber(node, nr);
		}
	},
	
	getNextColumn : function(column, firstCall ){
		var nextColumn = jQuery(column).next();
		if(jQuery(nextColumn).html() == null){
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
	
	
	getPrevColumn : function(column, firstCall ){
		var nextColumn = jQuery(column).prev();
		if(jQuery(nextColumn).html() == null){
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

	getColumnBeyond : function(column, columnNumber){
		var row = jQuery(column).parent().next();
		if(jQuery(row).html() == null){
			return null;
		}
		
		column = jQuery('td:nth-child(' + columnNumber + ')', row);
		if(jQuery(column).html() == null){
			return null;
		}
		
		if(jQuery('textarea:first-child', column).html() == null){
			return tf.getColumnBeyond(column, columnNumber);
		} else {
			return column;
		}
	},
	
	getColumnAbove : function(column, columnNumber){
		var row = jQuery(column).parent().prev();
		if(jQuery(row).html() == null){
			return null;
		}
		
		column = jQuery('td:nth-child(' + columnNumber + ')', row);
		if(jQuery(column).html() == null){
			return null;
		}
		
		if(jQuery('textarea:first-child', column).html() == null){
			return tf.getColumnAbove(column, columnNumber);
		} else {
			return column;
		}
	},
	
	cellValueChangeHandler : function(node){
		var parentRow = jQuery(node).parent('td').parent('tr');
		
		if(jQuery(parentRow).attr('class') == 'tabf_new_row'){
			return;
		};
		
		if(jQuery(node).attr('originalValue') != jQuery.trim(jQuery(node).attr('value'))){
			jQuery(node).addClass('tabf_modified_value');
			jQuery(node).attr('isModified', true);
			
			parentRow.attr('isModified', true);
			parentRow.addClass('tabf_modified_row');
			
			jQuery('td:last-child .tabf_ok_status', parentRow).css('display', 'none');
			jQuery('td:last-child .tabf_modified_status', parentRow).css('display', 'inline');
			
			if(jQuery('..tabf_erronious_instance_name', jQuery(parentRow).parent().parent()).length == 0){
				jQuery('.tabf_save_button', jQuery(parentRow).parent().parent()).css('display', 'inline');
			}
		} else {
			jQuery(node).removeClass('tabf_modified_value');
			jQuery(node).attr('isModified', false);
			
			if(jQuery('.tabf_modified_value', parentRow).length == 0){
				parentRow.attr('isModified', false);
				parentRow.removeClass('tabf_modified_row');
				
				jQuery('td:last-child .tabf_ok_status', parentRow).css('display', 'inline');
				jQuery('td:last-child .tabf_modified_status', parentRow).css('display', 'none');
				
				if(jQuery('.tabf_modified_row', jQuery(parentRow).parent().parent()).length == 0
						&& jQuery('.tabf_new_row .tabf_valid_instance_name', jQuery(parentRow).parent().parent()).length == 0){
					jQuery('.tabf_save_button', jQuery(parentRow).parent().parent()).css('display', 'none');
				}
			}
		}
	},
	
	refreshForm : function(containerId){
		var container = jQuery('#' + containerId);
		tf.loadForm(container);
	},
	
	saveFormData : function(event, containerId){
		var container = jQuery('#' + containerId);
		
		jQuery('.tabf_table_container tr', container).each( tf.saveFormRowData);
		
		jQuery(Event.element(event)).css('display', 'none');		
	},
	
	saveFormRowData : function(rowNr){
		jQuery(this).addClass('tabf_table_row_saved');
		jQuery('textarea', this).attr('readonly', 'true');
		
		if(jQuery(this).attr('isModified') == 'true' || jQuery(this).attr('isNew') == 'true' ){
			
			jQuery('td:last-child .tabf_modified_status', this).css('display', 'none');
			jQuery('td:last-child .tabf_added_status', this).css('display', 'none');
			jQuery('td:last-child .tabf_pending_status', this).css('display', 'inline');
			
			var modifiedValues = new Array();
			
			var fields = jQuery('td', this).get();
			for(var fieldNr=0; fieldNr < fields.length; fieldNr++){
				
				var fieldValues = jQuery('textarea', jQuery(fields[fieldNr])).get();
				for(var i=0; i < fieldValues.length; i++){
					if(jQuery(fieldValues[i]).attr('isModified')){
						var modifiedValue = new Object();
						modifiedValue['newValue'] = jQuery(fieldValues[i]).attr('value');
						modifiedValue['originalValue'] = jQuery(fieldValues[i]).attr('originalValue');
						
						modifiedValue['address'] = jQuery('th:nth-child(' + (fieldNr + 1) + ')'
									, jQuery(this).parent()).attr('field-address');
						modifiedValue['isTemplateParam'] = jQuery('th:nth-child(' + (fieldNr + 1) + ')'
								, jQuery(this).parent()).attr('is-template');
						modifiedValue['templateId'] = jQuery(fieldValues[i]).attr('template-id');
						
						modifiedValues.push(modifiedValue);						
					}
				}
			}
			
			//this is uglay
			var tabularFormId = jQuery(this).parent().parent().parent().parent().attr('id');
			var revisionId = jQuery('td:first-child ',this).attr('revision-id');
			if(revisionId == '-1'){
				var articleTitle = jQuery('td:first-child ',this).attr('value');
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
	
	
	saveFormRowDataCallback : function(data){
		data = data.substr(data.indexOf('--##starttf##--') + 15, data.indexOf('--##endtf##--') - data.indexOf('--##starttf##--') - 15); 
		data = JSON.parse(data);
		
		data.rowNr = (data.rowNr*1) + 1;
		
		var row = jQuery('#' + data.tabularFormId + ' tr:nth-child(' + data.rowNr + ')');
		if(data.success == true){
			jQuery(row).addClass('tabf_table_row_saved_successfull');
			jQuery('td:last-child .tabf_pending_status', row).css('display', 'none');
			jQuery('td:last-child .tabf_saved_status', row).css('display', 'inline');
		} else {
			jQuery(row).addClass('.tabf_table_row_saved_error');
			jQuery('td:last-child .tabf_pending_status', row).css('display', 'none');
			jQuery('td:last-child .tabf_error_status', row).css('display', 'inline');
		}
	},
	
	addInstance : function(tabfId){
		jQuery('#' + tabfId + ' .tabf_table_container .smwfooter').
			before('<tr>' + jQuery('#' + tabfId + ' .tabf_table_container table tr:last-child').html() + '</tr>');
		
		var newRow = jQuery('#' + tabfId + ' .tabf_table_container .smwfooter').prev();
		jQuery(newRow).addClass('tabf_new_row');
		jQuery(newRow).attr('isNew', true);
		jQuery('td:first-child textarea', newRow).addClass('tabf_erronious_instance_name');
		jQuery('td', newRow).addClass('tabf_table_cell');
		
		jQuery('td textarea', newRow).each(tf.initializeLoadedCell)
		
		jQuery('td:first-child textarea', newRow).focus();
		
		jQuery('.tabf_save_button', jQuery(newRow).parent().parent()).css('display', 'none');
		
	},
	
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
			
	}

});

var tf = new TF();

jQuery(document).ready( function($) {
	tf.loadForms();
});
