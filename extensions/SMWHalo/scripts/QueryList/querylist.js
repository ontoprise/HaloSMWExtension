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

window.queryList_filter = queryList_filter;
window.queryList_updateAC = queryList_updateAC;







