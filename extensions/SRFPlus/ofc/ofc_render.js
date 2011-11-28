if(typeof(ofc_data_objs)=="undefined") window.ofc_data_objs = {data:[],tabs:[],showhide:[]};
window.ofc_render = { js:{} };
(function($) {
 //$.noConflict(); EVIL!!!

	var flash_chart_path = (mw?mw.config.get( 'wgScriptPath' ) : wgScriptPath) + "/extensions/SRFPlus/ofc/open-flash-chart.swf";

	//default setup
	var ofc_h='100%';
	var ofc_w='100%';
	var ofc_bg='#ffffff';
	var	ofc_y_grid='#E2E2E2';
	var	ofc_y_axis='#000066';
	var	ofc_x_axis='#F65327';
	var	ofc_x_grid='#E2E2E2';

	ofc_render.js = {
		showHide : function(ofclnk, _ofc, isShow) {
		    if (_ofc.is(":hidden") && isShow) {
		    	_ofc.show();
		    	$(ofclnk).html("<b>"+$(ofclnk).text()+"</b>");
		    } else if (!_ofc.is(":hidden") && !isShow) {
		    	_ofc.hide();
		    	$(ofclnk).html($(ofclnk).text());
		    }
		},
		showHideChart : function(event){
		  	event.preventDefault();
		    var the_id=$(this).attr("id").substr(16);
		    var _ofc=$('#'+the_id);
		   	ofc_render.js.showHide(this, _ofc, _ofc.is(":hidden"));
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
			    var _ofc=$('#'+div_pre+i);
		    	if(lnk_id==(lnk_pre+i)) {
				   	ofc_render.js.showHide(lnk, _ofc, true);
		    	} else {
				   	ofc_render.js.showHide(lnk, _ofc, false);
		    	}
		    	i++;
		    }
		},
		ofc_data : function(passed_string){
			var table_id=passed_string;
			var obj = null;
			for(var i=0;i<ofc_data_objs.data.length;++i) {
				obj = ofc_data_objs.data[i][table_id];
				if(typeof(obj)=='object')
					break;
			}
			return (typeof(Prototype)=='undefined') ? JSON.stringify(obj.data) : Object.toJSON(obj.data);
		},
		resetOfc : function(){
		    for(var i=0;i<ofc_data_objs.data.length;++i) {
		        var ofc_data_obj = ofc_data_objs.data[i];
		        for(var ofc_id in ofc_data_obj) {
		            var pass_string=ofc_id;
		            var flashvars = {"get-data":"ofc_render.js.ofc_data","id":pass_string};
		            var params = false;
		            var attributes = {wmode: "Opaque",salign: "l",AllowScriptAccess:"always"};
		            var div = $("#div_" + ofc_id);
		            div.resizable();
		            if(!ofc_data_obj[ofc_id].show)
		                div.hide();
		            swfobject.embedSWF(flash_chart_path, ofc_id, ofc_w,ofc_h, "9.0.0", "expressInstall.swf", flashvars, params, attributes );
		        }
		    }
		    
			$("a.ofc_pie_link").click(ofc_render.js.showHideChart);
			$("a.ofc_bar_link").click(ofc_render.js.showHideChart);
			$("a.ofc_bar_3d_link").click(ofc_render.js.showHideChart);
			$("a.ofc_line_link").click(ofc_render.js.showHideChart);
			$("a.ofc_scatter_line_link").click(ofc_render.js.showHideChart);
		    
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
		},
		renderOfc : function(){
			for(var i=0;i<ofc_data_objs.data.length;++i) {
				var ofc_data_obj = ofc_data_objs.data[i];
				for(var ofc_id in ofc_data_obj) {
					var pass_string=ofc_id;
					var flashvars = {"get-data":"ofc_render.js.ofc_data","id":pass_string};
					var params = false;
					var attributes = {wmode: "Opaque",salign: "l",AllowScriptAccess:"always"};
					var div = $("#div_" + ofc_id);
					div.resizable();
					if(!ofc_data_obj[ofc_id].show)
						div.hide();
					swfobject.embedSWF(flash_chart_path, ofc_id, ofc_w,ofc_h, "9.0.0", "expressInstall.swf", flashvars, params, attributes );
				}
			}
			for(var i=0;i<ofc_data_objs.tabs.length;++i) {
				$(ofc_data_objs.tabs[i]).click(ofc_render.js.tabChart);
			}
			for(var i=0;i<ofc_data_objs.showhide.length;++i) {
				$(ofc_data_objs.showhide[i]).click(ofc_render.js.showHideChart);
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
		}
	};
	$(document).ready(function(){
		ofc_render.js.renderOfc();
	});
})(jQuery);