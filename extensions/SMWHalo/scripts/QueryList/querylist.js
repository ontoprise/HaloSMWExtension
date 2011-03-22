


var queryList_filter = function(){
	
	var filterCol = jQuery('#ql_filtercol').attr('value');
	
	var filterStringId = '#ql_filterstring-';
	if(filterCol == 0 || filterCol ==2){
		filterStringId += '0'
	} else {
		filterStringId += filterCol;
	}
	
	if(jQuery(filterStringId).attr('value') != jQuery(filterStringId).attr('currentValue')
				|| filterCol != jQuery('#ql_filtercol').attr('currentValue')){ 
	
		var filterString = jQuery(filterStringId).attr('value');
		jQuery(filterStringId).attr('currentValue', filterString); 	
		jQuery('#ql_filtercol').attr('currentValue', filterCol);
		
		var colSelector = '#ql_list tr td span:first-child';
		if(filterCol != 0){
			colSelector = '#ql_list tr td:nth-child(' + filterCol + ') span:first-child';
		}
		
		jQuery('#ql_list tr').css('display', 'none');
		jQuery('#ql_list tr:first-child').css('display', 'table-row');
		
		jQuery(colSelector).each(function(){
			
			if(jQuery(this).html().indexOf(filterString) != -1){
				jQuery(this).parent().parent().css('display', 'table-row');
			}
		});
	}
}


var queryList_updateAC = function(){
	var col = jQuery('#ql_filtercol').attr('value');
	var filterStringId = '#ql_filterstring-';
	if(col == 2) col = 0;
	
	var cols = new Array(0, 1, 3, 4);
	var originalValue = '';
	var currentOrginalValue = '';
	for(var i=0; i < cols.length; i++){
		if(jQuery(filterStringId + cols[i]).css('display') == 'inline'){
			originalValue = jQuery(filterStringId + cols[i]).attr('value');
			currentOrginalValue = jQuery(filterStringId + cols[i]).attr('currentValue');
		}
		if(cols[i] != col){
			jQuery(filterStringId + cols[i]).css('display', 'none');
		}
	}
	
	jQuery(filterStringId + col).css('display', 'inline');
	jQuery(filterStringId + col).attr('value', originalValue);
	jQuery(filterStringId + col).attr('currentValue', currentOrginalValue);
}








