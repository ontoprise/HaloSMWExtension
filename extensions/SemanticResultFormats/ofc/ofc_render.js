 //jQuery.noConflict(); EVIL!!!

	var flash_chart_path = wgScriptPath + "/extensions/SemanticResultFormats/ofc/open-flash-chart.swf";

	var ofc_data_objs = [];
	
	//default setup
	var ofc_h='100%';
	var ofc_w='100%';
	var ofc_bg='#ffffff';
	var	ofc_y_grid='#E2E2E2';
	var	ofc_y_axis='#000066';
	var	ofc_x_axis='#F65327';
	var	ofc_x_grid='#E2E2E2';

function showHide(ofclnk, ofc, isShow){
    if (ofc.is(":hidden") && isShow) {
    	ofc.show();
    	jQuery(ofclnk).html("<b>"+jQuery(ofclnk).text()+"</b>");
    } else if (!ofc.is(":hidden") && !isShow) {
    	ofc.hide();
    	jQuery(ofclnk).html(jQuery(ofclnk).text());
    }
}
function showHideChart(event){
  	event.preventDefault();
    var the_id=jQuery(this).attr("id").substr(16);
    var ofc=jQuery('#'+the_id);
   	showHide(this, ofc, ofc.is(":hidden"));
}
function tabChart(event){
  	event.preventDefault();
  	var lnk_id = jQuery(this).attr("id");
    var lnk_pre = "show_hide_flash_div_ofc";
    var div_pre = "div_ofc";
    var chartset = lnk_id.substr(23);
    chartset = chartset.substr(0, chartset.indexOf("_"));
    lnk_pre += chartset + "_";
    div_pre += chartset + "_";
    var i=0;
    while((lnk = jQuery('#'+lnk_pre+i)).length != 0) {
	    var ofc=jQuery('#'+div_pre+i);
    	if(lnk_id==(lnk_pre+i)) {
		   	showHide(lnk, ofc, true);
    	} else {
		   	showHide(lnk, ofc, false);
    	}
    	i++;
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

function resetOfc() {
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
}

// derive from SMWUserManual/scripts/up.js, it is called with MW BeforePageDisplay hook, here we just use the instance
if (typeof uprgPopup != "undefined") {
uprgPopup.cellDataRating = function(tableIdentifier, row, col, value, cellIdent, uri) {
        this.initPopup()
        
        // provenance URI
        this.provenanceUri=uri
        this.provenanceUri+='&action=edit&redirect-after-edit='+(wgServer+wgScript).replace(/:/, '%3A').replace(/\//g, '%2F')+'%2F'+wgPageName
        // set the static html stuff
        this.popup.setHtmlContent(this.cellRatingHtml())

        document.getElementById('up_data_table_row').innerHTML = row;
        document.getElementById('up_data_table_col').innerHTML = col;
        document.getElementById('up_data_table_value').value = value;
        
        this.tableIdentifier = tableIdentifier;
        sajax_do_call('wfUpGetCellRating', [wgPageName, this.tableIdentifier, cellIdent], this.setComments.bind(this))
    };
}