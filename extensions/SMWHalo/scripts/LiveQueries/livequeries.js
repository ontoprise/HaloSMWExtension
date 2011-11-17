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