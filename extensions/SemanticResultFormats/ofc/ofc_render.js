 jQuery.noConflict();

	var ofc_data_objs = [];
	
	//default setup
	var ofc_h='100%';
	var ofc_w='100%';
	var ofc_bg='#ffffff';
	var	ofc_y_grid='#E2E2E2';
	var	ofc_y_axis='#000066';
	var	ofc_x_axis='#F65327';
	var	ofc_x_grid='#E2E2E2';

function showHideChart(event){
  	event.preventDefault();
    var the_id=jQuery(this).attr("id").substr(16);
    var ofc=jQuery('#'+the_id);
    if (ofc.is(":hidden")) {
    	ofc.show();
    	jQuery(this).html("<b>"+jQuery(this).text()+"</b>");
    }else{
    	ofc.hide();
    	jQuery(this).html(jQuery(this).text());
    }
}
jQuery(function() {
	for(var i=0;i<ofc_data_objs.length;++i) {
		var ofc_data_obj = ofc_data_objs[i];
		for(var ofc_id in ofc_data_obj) {
			var pass_string=ofc_id;
			var flashvars = {"get-data":"ofc_data","id":pass_string};
			var params = false;
			var attributes = {wmode: "Opaque",salign: "l",AllowScriptAccess:"always"};
			var div = jQuery("#div_" + ofc_id);
			div.resizable();
			if(!ofc_data_obj[ofc_id].show)
				div.hide();
			swfobject.embedSWF(flash_chart_path, ofc_id, ofc_w,ofc_h, "9.0.0", "expressInstall.swf", flashvars, params, attributes );
		}
	}
	
	jQuery("a.ofc_pie_link").click(showHideChart);
	jQuery("a.ofc_bar_link").click(showHideChart);
	jQuery("a.ofc_bar_3d_link").click(showHideChart);
	jQuery("a.ofc_line_link").click(showHideChart);
	jQuery("a.ofc_scatter_line_link").click(showHideChart);
	
	jQuery("a.ofc_table_link").click(function(event){
	  	event.preventDefault();
	    var the_id=jQuery(this).attr("id").substr(16);
	    var html_table=jQuery('#'+the_id);
	    if (html_table.is(":hidden")) {
	    	html_table.show();
	    	jQuery(this).text("Hide table");
	    }else{
	    	html_table.hide();
	    	jQuery(this).text("Show table");
	    }
	});
});


function ofc_data(passed_string){
	var table_id=passed_string;
	var obj = null;
	for(var i=0;i<ofc_data_objs.length;++i) {
		obj = ofc_data_objs[i][table_id];
		if(typeof(obj)=='object')
			break;
	}
	return (typeof(Prototype)=='undefined') ? JSON.stringify(obj.data) : Object.toJSON(obj.data);
}