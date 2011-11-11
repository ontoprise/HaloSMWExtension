if(typeof(ofc_data_objs)=="undefined") ofc_data_objs = [];
document.ofc = { js:{} };
(function($) {
 //$.noConflict(); EVIL!!!

	var flash_chart_path = (mw?mw.config.get( 'wgScriptPath' ) : wgScriptPath) + "/extensions/SemanticResultFormats/ofc/open-flash-chart.swf";

	//default setup
	var ofc_h='100%';
	var ofc_w='100%';
	var ofc_bg='#ffffff';
	var	ofc_y_grid='#E2E2E2';
	var	ofc_y_axis='#000066';
	var	ofc_x_axis='#F65327';
	var	ofc_x_grid='#E2E2E2';

	document.ofc.js = {
		showHide : function(ofclnk, ofc, isShow) {
		    if (ofc.is(":hidden") && isShow) {
		    	ofc.show();
		    	$(ofclnk).html("<b>"+$(ofclnk).text()+"</b>");
		    } else if (!ofc.is(":hidden") && !isShow) {
		    	ofc.hide();
		    	$(ofclnk).html($(ofclnk).text());
		    }
		},
		showHideChart : function(event){
		  	event.preventDefault();
		    var the_id=$(this).attr("id").substr(16);
		    var ofc=$('#'+the_id);
		   	document.ofc.js.showHide(this, ofc, ofc.is(":hidden"));
		},
		tabChart : function(event){
		  	event.preventDefault();
		  	var lnk_id = $(this).attr("id");
		    var lnk_pre = "show_hide_flash_div_ofc";
		    var div_pre = "div_ofc";
		    var chartset = lnk_id.substr(23);
		    chartset = chartset.substr(0, chartset.indexOf("_"));
		    lnk_pre += chartset + "_";
		    div_pre += chartset + "_";
		    var i=0;
		    while((lnk = $('#'+lnk_pre+i)).length != 0) {
			    var ofc=$('#'+div_pre+i);
		    	if(lnk_id==(lnk_pre+i)) {
				   	document.ofc.js.showHide(lnk, ofc, true);
		    	} else {
				   	document.ofc.js.showHide(lnk, ofc, false);
		    	}
		    	i++;
		    }
		},
		ofc_data : function(passed_string){
			var table_id=passed_string;
			var obj = null;
			for(var i=0;i<ofc_data_objs.length;++i) {
				obj = ofc_data_objs[i][table_id];
				if(typeof(obj)=='object')
					break;
			}
			return (typeof(Prototype)=='undefined') ? JSON.stringify(obj.data) : Object.toJSON(obj.data);
		},
		resetOfc : function(){
		    for(var i=0;i<ofc_data_objs.length;++i) {
		        var ofc_data_obj = ofc_data_objs[i];
		        for(var ofc_id in ofc_data_obj) {
		            var pass_string=ofc_id;
		            var flashvars = {"get-data":"document.ofc.js.ofc_data","id":pass_string};
		            var params = false;
		            var attributes = {wmode: "Opaque",salign: "l",AllowScriptAccess:"always"};
		            var div = $("#div_" + ofc_id);
		            div.resizable();
		            if(!ofc_data_obj[ofc_id].show)
		                div.hide();
		            swfobject.embedSWF(flash_chart_path, ofc_id, ofc_w,ofc_h, "9.0.0", "expressInstall.swf", flashvars, params, attributes );
		        }
		    }
		    
			$("a.ofc_pie_link").click(document.ofc.js.showHideChart);
			$("a.ofc_bar_link").click(document.ofc.js.showHideChart);
			$("a.ofc_bar_3d_link").click(document.ofc.js.showHideChart);
			$("a.ofc_line_link").click(document.ofc.js.showHideChart);
			$("a.ofc_scatter_line_link").click(document.ofc.js.showHideChart);
		    
		    $("a.ofc_table_link").click(function(event){
		        event.preventDefault();
		        var the_id=$(this).attr("id").substr(16);
		        var html_table=$('#'+the_id);
		        if (html_table.is(":hidden")) {
		            html_table.show();
		            $(this).text("Hide table");
		        }else{
		            html_table.hide();
		            $(this).text("Show table");
		        }
		    });
		}
	};
	$(document).ready(function(){
		for(var i=0;i<ofc_data_objs.length;++i) {
			var ofc_data_obj = ofc_data_objs[i];
			for(var ofc_id in ofc_data_obj) {
				var pass_string=ofc_id;
				var flashvars = {"get-data":"document.ofc.js.ofc_data","id":pass_string};
				var params = false;
				var attributes = {wmode: "Opaque",salign: "l",AllowScriptAccess:"always"};
				var div = $("#div_" + ofc_id);
				div.resizable();
				if(!ofc_data_obj[ofc_id].show)
					div.hide();
				swfobject.embedSWF(flash_chart_path, ofc_id, ofc_w,ofc_h, "9.0.0", "expressInstall.swf", flashvars, params, attributes );
			}
		}
		
		$("a.ofc_table_link").click(function(event){
		  	event.preventDefault();
		    var the_id=$(this).attr("id").substr(16);
		    var html_table=$('#'+the_id);
		    if (html_table.is(":hidden")) {
		    	html_table.show();
		    	$(this).text("Hide table");
		    }else{
		    	html_table.hide();
		    	$(this).text("Show table");
		    }
		});
	});
})(jQuery);