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

window.LiveQuery = {
    helper : {
    	
    	initMethods : Array(),
    	
        getResultPrinter:function(id, query, frequency){
        	
        	var target = function(x) {
        		if (x.status == 200){
        			jQuery('#'+id).html(x.responseText);
        		} else {
        			jQuery('#'+id).html("<div class='error'>Error: " + x.status + " " + x.statusText + " (" + x.responseText + ")</div>");
        		}

        		if(frequency*1 > 0){
                	window.setTimeout('LiveQuery.helper.getResultPrinter("' + id + '", "' + query + '", "' + frequency + '")', frequency*1000);
                }
             };
             
             window.jQuery.fn.ready = LiveQuery.helper.documentReady;
             addOnloadHook = LiveQuery.helper.documentReady;
             sajax_do_call('smwf_lq_refresh', [id, query], target);
        },

		documentReady : function(initMethod){
			LiveQuery.helper.initMethods.push(initMethod);
		},
		
		executeInitMethods : function(){
			window.setTimeout(LiveQuery.helper.reallyExecuteInitMethods, 10);
		},
		
		reallyExecuteInitMethods : function(){
			for(var i=0; i < LiveQuery.helper.initMethods.length; i++){
	           	LiveQuery.helper.initMethods[i]();
	      	}
		}
	}
};

jQuery(document).ready( function($) {
	jQuery('.lq-container').each(function(){
		LiveQuery.helper.getResultPrinter(
			jQuery(this).attr('id'),
			jQuery('.lq-query', this).html(),
			jQuery(this).attr('lq-frequency'));
	});
});
