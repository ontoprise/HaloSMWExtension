AjaxAsk.helper = {
        getResultPrinter:function(id, qno, query){
             var target = function(x) {
                var node = document.getElementById(id);
                if (x.status == 200) node.innerHTML = x.responseText;
                else node.innerHTML= "<div class='error'>Error: " + x.status + " " + x.statusText + " (" + x.responseText + ")</div>";
                smw_makeSortable(node.firstChild);
                smw_tooltipInit();
             }
            sajax_do_call('smwf_aq_Access', ["ajaxAsk", "" + qno + "," + query], target);
        }
    };

AjaxSparql.helper = {
        getResultPrinter:function(id, qno, query){
        	 var target = function(x) {
        	 	var node = document.getElementById(id);
                if (x.status == 200) node.innerHTML = x.responseText;
                else node.innerHTML= "<div class='error'>Error: " + x.status + " " + x.statusText + " (" + x.responseText + ")</div>";
                smw_tooltipInit();
                smw_makeSortable(node);
             }
            sajax_do_call('smwf_aq_Access', ["ajaxSparql", "" + qno + "," + query], target);
        }
    };

function initialize_ajaxqueries(){
	for(var i=0;i<AjaxAsk.queries.length;++i) {
		AjaxAsk.helper.getResultPrinter(AjaxAsk.queries[i].id, AjaxAsk.queries[i].qno, AjaxAsk.queries[i].query)
	}
	for(var i=0;i<AjaxSparql.queries.length;++i) {
        AjaxSparql.helper.getResultPrinter(AjaxSparql.queries[i].id, AjaxSparql.queries[i].qno, AjaxSparql.queries[i].query)
    }
}

addOnloadHook(initialize_ajaxqueries);