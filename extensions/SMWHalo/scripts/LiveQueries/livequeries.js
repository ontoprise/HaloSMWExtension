var LiveQuery = {
    helper : {
        getResultPrinter:function(id, query, frequency){
        	var target = function(x) {
                var node = document.getElementById(id);
                if (x.status == 200) node.innerHTML = x.responseText;
                else node.innerHTML= "<div class='error'>Error: " + x.status + " " + x.statusText + " (" + x.responseText + ")</div>";
                smw_makeSortable(node.firstChild);
                smw_tooltipInit();
                
                if(frequency*1 > 0){
                	window.setTimeout('LiveQuery.helper.getResultPrinter("' + id + '", "' + query + '", "' + frequency + '")', frequency*1000);
        		}
             };
            sajax_do_call('smwf_lq_refresh', [id, query], target);
        }
    }
};

addOnloadHook( lq_init );

function lq_init(){
	jQuery('.lq-container').each(function(){
		LiveQuery.helper.getResultPrinter(
			jQuery(this).attr('id'),
			jQuery('.lq-query', this).html(),
			jQuery(this).attr('lq-frequency'));
	});
}