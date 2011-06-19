

function dapi_showRefreshdControls(event){

	var container = jQuery(Event.element(event)).parent().parent();
	
	jQuery('.dapi-refresh-controls', container).css('display', '');
	
}

function dapi_doRefresh(event){

	var container = jQuery(Event.element(event)).parent().parent();
	
	var wsParam = jQuery('.dapi-refresh-controls input:first-child', container).attr('value');
	var containerId = jQuery(container).attr('id');
	var dapiId = jQuery('.dapi-dpid', container).html();
	
	var selectedIds = new Array();
	jQuery('.dapi-choose-value-controls option', container).each(function (){
		if(jQuery(this).get(0).selected){
			selectedIds.push(jQuery(this).attr('value'));
		}
	});
	selectedIds = JSON.stringify(selectedIds);
	
	var url = wgServer + wgScriptPath + "/index.php";
	jQuery.ajax({ url:  url, 
		data: {
			'action' : 'ajax',
			'rs' : 'dapi_refreshData',
			'rsargs[]' : [wsParam, dapiId, selectedIds, containerId],
		},
		success: dapi_doRefreshCallBack,
	});
}

function dapi_doRefreshCallBack(data){

	data = data.substr(data.indexOf('--##starttf##--') + 15, data.indexOf('--##endtf##--') - data.indexOf('--##starttf##--') - 15); 
	data = JSON.parse(data);
	
	var html = '';
	for(var i=0; i < data.results.length; i++){
		
		var selected = '';
		if(data.results[i].selected == 'true'){
			selected = ' selected="selected" ';
		}
		
		html += '<option value="' + data.results[i].id + '" ' + selected +'>' + data.results[i].label + '</option>';
	}
	html += '<option style="display: none" value="fritz" selected="selected">fl</option>';
	
	jQuery('#' + data.containerId + ' .dapi-choose-value-controls select:first-child').html(html);
	
	jQuery('#' + data.containerId + ' .dapi-refresh-controls').css('display', 'none');
}


function dapi_createsubmitvalues(){
	
	jQuery('.dapi-choose-value-controls select:first-child').each(function(){
		
		var values = new Array();
		jQuery('option', this).each(function (){
			var o = new Object();
			o.value = jQuery(this).attr('value');
			o.label = jQuery(this).html();
			
			if(jQuery(this).get(0).selected){
				o.selected = 'true';
			} else {
				o.selected = 'false';
			}
			
			values.push(o);
		});
		
		var json = new Array();
		json.push(values);
		
		var delimiter = jQuery('.dapi-delimiter', jQuery(this).parent().parent()).html();
		json.push(delimiter);
		
		var wsParam = jQuery('.dapi-refresh-controls input:first-child', 
				jQuery(this).parent().parent()).attr('value');
		json.push(wsParam);
		
		json = dapi_encodeJSON(json);
		
		json = '{{#DataPickerValues:' + json + '}}';
			
		jQuery(this).html('<option selected="selected" style="display: none">' +  json + '</option>');
	});
	
}

function dapi_encodeJSON(object){
	
	var json = JSON.stringify(object);
	json = json.replace("{{", '##dlcb##');
	json = json.replace("}}", '##drcb##');
	json = json.replace("|", '##pipe##');
	json = json.replace("[[", '##dlsb##');
	json = json.replace("]]", '##drsb##');
	
	return json;
}


jQuery(document).ready( function($) {
	jQuery('#sfForm').submit( function() { return dapi_createsubmitvalues(); } );
});















